<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoteCandidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_vote',
        'name',
        'image',
    ];

    public function voters() {
        return $this->hasMany(UserVote::class, 'id_candidate', 'id');
    }
}
