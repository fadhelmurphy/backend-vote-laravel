<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\UserVote;
use App\Models\Link;
use App\Models\VoteCandidate;
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
        $id_user = Auth::user()->id;
        $link=Link::with(['voteLink'
        =>function($query){
            $query->withCount('voters');
            $query->withCount('candidates');
            }
            ,
            // 'voteLink.candidates',
        'voteLink.voters'=>function($query) use ($id_user){
            $query->select('user_votes.id_vote','a.name as name','b.name as option');
            $query->join('users as a','id_user','a.id');
            $query->join('vote_candidates as b','id_candidate','b.id');
            // $query->count();
            $query->where('id_user', '=', $id_user);
        }, 
        ])
        ->where('key',$key)->first();
        
        foreach ($link['voteLink'] as $value) {
            
            // Log::channel('stderr')->info($value['voters']);
                $value['canvote'] = count($value['voters']) > 0 ? false :true;
                $value['urvote'] = count($value['voters']) > 0 ? $value['voters'][0] : null;
                unset($value['voters']);
            }
            Log::channel('stderr')->info($link);
        // $link = Link::with([
        //     'votes', 
        //     'votes.candidates',
        //     'votes.voters' => function ($query) use ($id_user) {
        //         $query->select('vote_candidates.name AS candidate', 'vote_candidates.updated_at')
        //             ->join('vote_candidates', 'user_votes.id_candidate', 'vote_candidates.id')
        //             ->where('id_user', $id_user);
        //     }
        // ])
        //     ->where('key', $key)
        //     ->get()
        //     ->first() ?? false;

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
    public function store(Request $request, $key)
    {
        $data = $request->all();
        $id_candidate = $data['id'];
        $id_user = Auth::user()->id;
        // $order = max($order - 1, 0);
        // $link = Link::with([
        //             'voteLink',
        //             'voteLink.candidates' => function ($query) use ($id_candidate) {
        //                 $query->where('vote_candidates.id', $id_candidate);
        //             }
        //         ])
        //     ->where('key', $key)->first() ?? false;
        $link = VoteCandidate::where('vote_candidates.id', $id_candidate)->select('id_vote')->first() ?? false;
        if ($link) {
            $exist = UserVote::where('id_candidate', $id_candidate)
                ->where('id_user', $id_user)
                ->first();
            if (!$exist) {
                $userVote = new UserVote();
                $userVote->id_vote = $link['id_vote'];
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
