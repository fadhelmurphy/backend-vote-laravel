<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoteCandidate extends Model
{
    use HasFactory;

    public function voters() {
        return $this->hasMany(UserVote::class, 'id_candidate', 'id')
            ->select(
                'user_votes.id_candidate',
                'users.id','name', 'email',
                'user_votes.created_at', 'user_votes.updated_at')
            ->join('users', 'user_votes.id_user', '=', 'users.id');
    }
}
