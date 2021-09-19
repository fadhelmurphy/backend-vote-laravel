<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    public function creator() {
        return $this->belongsTo(User::class, 'id_user', 'id')
            ->select('id', 'name', 'email');
    }

    public function candidates()
    {
        return $this->hasMany(VoteCandidate::class, 'id_vote', 'id');
    }

    public function voters()
    {
        return $this->hasMany(UserVote::class, 'id_vote', 'id')
            ->select('id', 'name', 'email');
    }
}
