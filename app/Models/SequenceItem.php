<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SequenceItem extends Model
{
    protected $fillable = ['question_id', 'item_text', 'correct_order'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}