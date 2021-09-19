<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoteLink extends Model
{
    use HasFactory;

    public function candidates()
    {
        return $this->hasMany(VoteCandidate::class, 'id_vote', 'id_vote');
    }

}
