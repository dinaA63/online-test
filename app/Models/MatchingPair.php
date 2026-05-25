<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchingPair extends Model
{
    protected $fillable = ['question_id', 'left_text', 'right_text', 'order'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}