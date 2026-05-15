<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    // ========== НОВЫЕ КОНСТАНТЫ ТИПОВ ==========
    // Открытые типы
    public const TYPE_COMPLETION = 'completion';      // задание дополнения (вставить слово)
    public const TYPE_ESSAY = 'essay';                // свободно конструируемый ответ (проверяет преподаватель)

    // Закрытые типы
    public const TYPE_ALTERNATIVE = 'alternative';    // альтернативные ответы (да/нет, правильно/неправильно)
    public const TYPE_SINGLE_CHOICE = 'single_choice'; // одиночный выбор (уже был)
    public const TYPE_MULTIPLE_CHOICE = 'multiple_choice'; // множественный выбор (уже был)
    public const TYPE_MATCHING = 'matching';          // установление соответствия
    public const TYPE_SEQUENCE = 'sequence';          // установление правильной последовательности

    // Дополнительные форматы
    public const TYPE_DROPDOWN = 'dropdown';          // выбор из выпадающего списка
    public const TYPE_DRAG_DROP = 'drag_drop';        // перетаскивание элементов
    public const TYPE_TABLE_FILL = 'table_fill';      // заполнение пустых ячеек таблицы
    public const TYPE_FILE_UPLOAD = 'file_upload';    // загрузка файла (диктант с аудио)

    protected $fillable = ['test_id', 'text', 'type', 'points', 'order', 'correct_text'];

    // Связи
    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function choices()
    {
        return $this->hasMany(Choice::class);
    }

    // ========== НОВЫЙ МЕТОД ДЛЯ ЛЕГЕНДЫ ТИПА ==========
    public function getTypeLabelAttribute()
    {
        return match ($this->type) {
            self::TYPE_COMPLETION   => 'Задание дополнения',
            self::TYPE_ESSAY        => 'Свободно конструируемый ответ',
            self::TYPE_ALTERNATIVE  => 'Альтернативный ответ (да/нет)',
            self::TYPE_SINGLE_CHOICE => 'Одиночный выбор',
            self::TYPE_MULTIPLE_CHOICE => 'Множественный выбор',
            self::TYPE_MATCHING     => 'Установление соответствия',
            self::TYPE_SEQUENCE     => 'Правильная последовательность',
            self::TYPE_DROPDOWN     => 'Выпадающий список',
            self::TYPE_DRAG_DROP    => 'Перетаскивание элементов',
            self::TYPE_TABLE_FILL   => 'Заполнение таблицы',
            self::TYPE_FILE_UPLOAD  => 'Загрузка файла',
            default                 => 'Неизвестный тип',
        };
    }

    
public function matchingPairs() {
    return $this->hasMany(MatchingPair::class);
}
public function sequenceItems() {
    return $this->hasMany(SequenceItem::class);
}
public function dropdownOptions() {
    return $this->hasMany(DropdownOption::class);
}
public function dragDropItems() {
    return $this->hasMany(DragDropItem::class);
}
public function tableFill() {
    return $this->hasOne(TableFillTable::class);
}
}