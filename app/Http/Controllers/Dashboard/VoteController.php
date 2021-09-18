<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vote;
use App\Models\UserVote;
use App\Models\VoteCandidate;
use App\Models\VoteLink;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Utils\SharedUtils;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use OutOfBoundsException;
use OutOfRangeException;

class VoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $id_user = Auth::user()->id;
        $votes = Vote::with('creator')
            ->withCount(['candidates', 'voters'])
            ->where('id_user', $id_user)
            ->get()
            ->all();
        return response()->json(['data' => $votes], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $id_user = Auth::user()->id;
        $vote = Vote::with(['candidates', 'candidates.voters', 'creator' ])
            ->withCount('voters')
            ->where('id', $id)
            ->where('id_user', $id_user)
            ->take(1)
            ->get()
            ->all();

        if(count($vote)) {
            $vote = $vote[0];
            return response()->json([ 'data' => $vote ], 200);
        }

        return response()->json(['message' => 'Sorry, the data you tried to looking for was not found'], 404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'title'     => 'required|string',
            'candidate' => 'array',
            'candidate.*.name' => 'required|string',
            'candidate.*.image' => 'image|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $id_user = Auth::user()->id;
        $title = $data['title'];

        $newVote = Vote::create([
            'id_user' => $id_user,
            'title' => $title,
        ]);

        foreach ($data['candidate'] as $candidate) {
            $candidate['id_vote'] = $newVote->id;
            if(array_key_exists('image', $candidate))
                $candidate['image'] = SharedUtils::saveImage($candidate['image']);
            VoteCandidate::create($candidate);
        }

        return response()->json(['message' => "Vote \"$title\" successfully created"]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'candidate' => 'array',
            'candidate.*.action' => 'required|string',
        ]);


        if ($validator->fails())
            return response()->json(['error' => $validator->errors()], 400);

        $vote = Vote::where('id', $id)->get()->all();

        if(count($vote)) {
            $title = $vote[0]['title'];
            if (array_key_exists('title', $data))
                Vote::where('id', $id)
                    ->update(['title' => $data['title']]);

            foreach ($data['candidate'] as $candidate) {
                $action = $candidate['action'];
                $candidate['id_vote'] = $id;
                unset($candidate['action']);

                if (array_key_exists('image', $candidate))
                    $candidate['image'] = SharedUtils::saveImage($candidate['image']);

                if($action == 'update')
                    VoteCandidate::where('id', $candidate['id'])
                        ->update($candidate);
                else if($action == 'delete')
                    VoteCandidate::where('id', $candidate['id'])
                        ->delete();
                else
                    VoteCandidate::create($candidate);
            }

            return response()->json(['message' => "Vote \"$title\" successfully updated"]);
        }


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $message = "Sorry, the vote didn't exist in the database";
        $status = 400;

        $id_user = Auth::user()->id;
        $vote = Vote::select('title')
            ->where('id', $id)
            ->where('id_user', $id_user)
            ->pluck('title');

        if(count($vote) > 0) {
            $title = $vote[0];
            $candidates = VoteCandidate::select('image')
                ->where('id_vote', $id)
                ->get()
                ->all();
            UserVote::where('id_vote', $id)
                ->delete();
            VoteCandidate::where('id_vote', $id)
                ->delete();
            Vote::where('id', $id)
                ->where('id_user', $id_user)
                ->delete();

            foreach ($candidates as $candidate) {
                $image = $candidate['image'];
                SharedUtils::deleteImage(($image));
            }
            $message = "Vote \"$title\" successfuly deleted";
            $status = 200;
        }

        return response()->json(["message" => $message], $status);
    }

    public function bulkDestroy(Request $request)
    {
        $data = $request->all();
        $ids = $data['id'];
        $totalDeleted = 0;
        $id_user = Auth::user()->id;

        foreach ($ids as $id) {
            UserVote::where('id_vote', $id)
                ->delete();
            VoteCandidate::where('id_vote', $id)
                ->delete();
            $totalDeleted += Vote::where('id_user', $id_user)
                ->where('id', $id)
                ->delete();
        }
        $message = "${totalDeleted} votes successfuly deleted";

        return response()->json(['message' => $message], 200);
    }
}
