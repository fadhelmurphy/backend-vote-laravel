<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\UserVote;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserVoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($key)
    {
        $link = Link::with('votes', 'votes.candidates')
            ->where('key', $key)
            ->take(1)
            ->get()
            ->all() ?? false;

        if ($link) {
            return response()->json(['data' => $link], 200);
        }

        return $this->error404();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $key, $order)
    {
        $data = $request->all();
        $id_candidate = $data['id'];
        $id_user = Auth::user()->id;
        $order = max($order - 1, 0);
        $link = Link::with([
                    'votes',
                    'votes.candidates' => function ($query) use ($id_candidate) {
                        $query->where('id', $id_candidate);
                    }
                ])
            ->where('key', $key)
            ->take(1)
            ->get()
            ->all()[0] ?? false;
        if ($link) {
            $vote = $link['votes'][$order] ?? false;
            if ($vote && count($vote['candidates'])) {
                $exist = UserVote::where('id_candidate', $id_candidate)
                    ->where('id_user', $id_user)
                    ->first();
                if (!$exist) {
                    $userVote = new UserVote();
                    $userVote->id_vote = $vote['id_vote'];
                    $userVote->id_candidate = $id_candidate;
                    $userVote->id_user = $id_user;
                    $userVote->save();
                    return response()->json([
                        'message' => 'Thank you! Your vote have been saved successfully'
                    ]);
                }
                return response()->json([
                    'message' => 'Sorry, your vote have been used'
                ], 400);
            }
        }

        return $this->error404();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $data = $request->all();
        $userVote = UserVote::select([
            'user_votes.id',
            'users.name as user',
            'votes.title as vote',
            'vote_candidates.name as candidate'
        ])
            ->where('user_votes.id_user', $data['id_user'])
            ->where('user_votes.id_vote', $id)
            ->where('user_votes.id_candidate', $data['id_candidate'])
            ->where('votes.id_user', Auth::user()->id)
            ->join('users', 'user_votes.id_user', '=', 'users.id')
            ->join('votes', 'user_votes.id_vote', '=', 'votes.id')
            ->join('vote_candidates', 'user_votes.id_candidate', '=', 'vote_candidates.id')
            ->first();

        if ($userVote) {
            Log::channel('stderr')->info($userVote);
            $user = $userVote['user'];
            $vote = $userVote['vote'];
            $candidate = $userVote['candidate'];
            $userVote->delete();
            return response([
                'message' => "${user}'s vote on \"${candidate}\" of \"${vote}\" successfully deleted"
            ]);
        }

        return $this->error404();
    }
}
