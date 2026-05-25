<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = ['test_id', 'text', 'type', 'points', 'order', 'correct_text'];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function choices()
    {
        return $this->hasMany(Choice::class);
    }

public function getTypeLabelAttribute()
{
    return [
        'single_choice'   => 'Одиночный выбор',
        'multiple_choice' => 'Множественный выбор',
        'text'            => 'Текстовый ответ',
        'matching'        => 'Соответствие',
        'sequence'        => 'Последовательность',
    ][$this->type] ?? 'Неизвестный тип';
}

    public function matchingPairs()
{
    return $this->hasMany(MatchingPair::class);
}

public function sequenceItems()
{
    return $this->hasMany(SequenceItem::class);
}
}