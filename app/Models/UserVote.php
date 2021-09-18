<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_user',
        'id_vote',
        'id_candidate',
    ];

    public function candidate() {
        return $this->belongsTo(VoteCandidate::class, 'id_candidate', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function vote() {
        return $this->belongsTo(Vote::class, 'id_vote', 'id');
    }
}
