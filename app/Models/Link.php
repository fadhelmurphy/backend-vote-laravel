<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    use HasFactory;

    public function creator() {
        return $this->belongsTo(User::class, 'id_user', 'id')
            ->select('id','name', 'email');
    }


    public function candidates()
    {
        return $this->hasMany(VoteCandidate::class, 'id_vote', 'id');
    }

    public function votes() {
        return $this->hasMany(VoteLink::class, 'id_link', 'id')
            ->select(
                'id_link', 'id_vote',
                'votes.title', 'votes.created_at', 'votes.updated_at'
                )
            ->join('votes', 'vote_links.id_vote', '=', 'votes.id');
    }

    public function voters()
    {
        return $this->hasMany(UserVote::class, 'id_vote', 'id')
            ->select('id', 'name', 'email');
    }
    public function voteLink(){
        return $this->belongsToMany(Vote::class,'vote_links','id_link','id_vote');
    }
}
