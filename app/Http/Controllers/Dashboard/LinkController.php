<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\VoteLink;
use Illuminate\Support\Facades\Log;
use App\Models\Vote;
use App\Utils\SharedUtils;
use Illuminate\Support\Facades\Validator;;

class LinkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $votes = Link::with('creator')
            ->withCount('votes')
            ->where('id_user', Auth::user()->id)
            ->get()
            ->makeHidden('id_user')
            ->all();

        return response()->json(['data' => $votes]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $vote = Link::with(['creator', 'votes' => function ($query) {
            $query->where('id_user', Auth::user()->id);
        }])
            ->where('id_user', Auth::user()->id)
            ->where('id', $id)
            ->get()
            ->makeHidden('id_user')
            ->all()[0] ?? false;

        if ($vote)
            return response()->json(['data' => $vote]);

        return $this->error404();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            // 'description'   => 'required|string',
            'id'            => 'required|array|min:1',
            'id.*'          => 'required|numeric'
        ]);

        if (!$validator->fails()) {
            $link = new Link();
            // $link->description = $data['description'];
            $link->id_user = Auth::user()->id;
            $link->key = SharedUtils::generateId(6);
            $link->save();

            $totalAdded = 0;
            foreach ($data['id'] as $id) {
                if (Vote::find($id)) {
                    $vote = new VoteLink();
                    $vote->id_link = $link->id;
                    $vote->id_vote = $id;
                    if ($vote->save()) {
                        $totalAdded++;
                    }
                }
            }

            return response()->json([
                'message' => "$totalAdded votes sucessfully binded",
                'code' => $link->key
                //  \"$link->description\""
            ]);
        }

        return $this->error400($validator);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $data = $request->all();
        Log::info('test 1234');
        $validator = Validator::make($data, [
            // 'description' => 'string|required',
            'id' => 'required|array|min:1',
            'id.*' => 'required|numeric',
        ]);

        $link = Link::find($id);
        if ($link) {
            if (!$validator->fails()) {
                // $link->description = $data['description'];
                $link->save();

                VoteLink::where('id_link', $id)->delete();
                $totalAdded = 0;
                foreach ($data['id'] as $id_vote) {
                    $voteLink = new VoteLink();
                    $voteLink->id_link = $id;
                    $voteLink->id_vote = $id_vote;
                    if ($voteLink->save()) {
                        $totalAdded++;
                    }
                }

                return response()->json([
                    'message' => "$totalAdded votes sucessfully added to "
                    // \"$link->description\""
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
        $link = Link::where('id', $id)
            ->where('id_user', Auth::user()->id)
            ->first();
        if ($link) {
            $description = $link->description;
            VoteLink::where('id_link', $id)
                ->delete();
            $link->delete();
            return response()->json(["message" => "Link \"$description\", sucessfully deleted"]);
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
            VoteLink::where('id_link', $id)
                ->delete();
            $totalDeleted += Link::where('id_user', $id_user)
                ->where('id', $id)
                ->first()
                ->delete();
        }

        return response()->json([
            'message' => "${totalDeleted} votes successfuly deleted"
        ]);
    }
}
