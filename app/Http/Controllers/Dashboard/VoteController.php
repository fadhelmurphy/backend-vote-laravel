<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vote;
use App\Models\UserVote;
use App\Models\VoteCandidate;
use App\Models\VoteLink;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Utils\SharedUtils;

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
            ->makeHidden('id_user')
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
        $vote = Vote::with(['candidates', 'candidates.voters', 'creator'])
            ->withCount('voters')
            ->where('id', $id)
            ->where('id_user', $id_user)
            ->take(1)
            ->get()
            ->makeHidden('id_user')
            ->all()[0] ?? false;

        if ($vote) {
            return response()->json(['data' => $vote], 200);
        }

        return $this->error404();
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
            'name.*'    => 'required|string',
            'image.*'   => 'image|max:2048'
        ]);

        Log::channel('stderr')->info($data);

        if (!$validator->fails()) {
            $newVote = new Vote();
            $newVote->id_user = Auth::user()->id;
            $newVote->title = $data['title'];
            $newVote->save();

            foreach ($data['name'] ?? [] as $index => $name) {
                $newCandidate = new VoteCandidate();
                $newCandidate->id_vote = $newVote->id;
                $newCandidate->name = $name;
                if (($data['image'] ?? [])[$index] ?? false)
                    $newCandidate->image = SharedUtils::saveImage($data['image'][$index]);
                $newCandidate->save();
            }

            return response()->json([
                'message' => "Vote \"$newVote->title\" successfully created"
            ]);
        }

        return $this->error400($validator);
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
            'title'     => 'required|string',
            'id.*'      => 'required|numeric|nullable',
            'is_delete.*'  => 'required|boolean',
            'name.*'    => 'required|string|nullable',
            'image.*'   => 'required|image|max:2048|nullable'
        ]);

        if (!$validator->fails()) {
            $vote = Vote::find($id);

            if ($vote) {
                $vote->title = $data['title'];
                $vote->save();

                foreach ($data['is_delete'] ?? [] as $index => $is_delete) {
                    $candidate = new VoteCandidate();
                    $id_candidate = $data['id'][$index] ?? false;
                    if ($id_candidate) {
                        $candidate =  VoteCandidate::find($id_candidate);
                        SharedUtils::deleteImage($candidate->image);
                        if ($is_delete) {
                            $candidate->delete();
                            continue;
                        }
                    }
                    $candidate->name = $data['name'][$index];
                    $candidate->id_vote = $id;

                    if (($data['image'] ?? [])[$index] ?? false) {
                        $candidate->image = SharedUtils::saveImage($data['image'][$index]);
                    }

                    $candidate->save();
                }

                return response()->json([
                    'message' => "Vote \"$vote->title\" successfully updated"
                ]);
            }

            return $this->error404();
        }

        return $this->error400($validator);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $id_user = Auth::user()->id;
        $vote = Vote::select('title')
            ->where('id', $id)
            ->where('id_user', $id_user)
            ->first();

        if ($vote) {
            $title = $vote->title;
            $candidates = VoteCandidate::select('image')
                ->where('id_vote', $id)
                ->get()
                ->all();
            foreach ($candidates as $candidate) {
                $image = $candidate['image'];
                SharedUtils::deleteImage(($image));
            }
            UserVote::where('id_vote', $id)
                ->delete();
            VoteCandidate::where('id_vote', $id)
                ->delete();
            VoteLink::where('id_vote', $id)
                ->delete();
            $vote->delete();

            return response()->json([
                'message' => "Vote \"$title\" successfuly deleted"
            ]);
        }

        return $this->error404();
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
            VoteLink::where('id_vote', $id)
                ->delete();
            $totalDeleted += Vote::where('id_user', $id_user)
                ->where('id', $id)
                ->delete();
        }

        return response()->json([
            'message' => "${totalDeleted} votes successfuly deleted"
        ]);
    }
}
