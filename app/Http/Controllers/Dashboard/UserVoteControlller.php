<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\UserVote;
use Illuminate\Http\Request;

class UserVoteController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $req = $request->all();
        $id_vote = $req['id_vote'];
        $id_candidate = $req['id_candidate'];
        $id_user = $req['id_user'];
        $exist = UserVote::where('id_vote', $id_vote)
            ->whereAnd('id_user', $id_user)
            ->count();
        if (!$exist) {
            $userVote = new UserVote();
            $userVote->id_vote = $id_vote;
            $userVote->id_candidate = $id_candidate;
            $userVote->id_user = $id_user;
            $userVote->save();
            return response()->json(['message' => 'Thank you! Your vote have been saved successfully'], 200);
        } else {
            return response()->json(['message' => 'Sorry, your vote have been used'], 400);
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
        $message = 'Sorry, but the user vote didn\'t exist';
        $status = 400;

        $userVote = UserVote::with('candidate', 'user', 'vote')
            ->where('id', $id)
            ->take(1)
            ->get()
            ->all();

        if (count($userVote)) {
            $user = $userVote[0]['user']['name'];
            $vote = $userVote[0]['vote']['title'];
            $candidate = $userVote[0]['candidate']['name'];
            $message = "${user} vote on ${candidate} of ${vote} successfully deleted";
            $status = 200;
            UserVote::destroy($id);
        }

        return response()->json(['message' => $message], $status);
    }

}
