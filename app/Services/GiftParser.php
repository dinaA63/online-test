<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Choice;
use App\Models\MatchingPair;
use App\Models\SequenceItem;

class GiftParser
{
    public function parse($giftContent, $testId)
    {
        // Удаляем BOM и нормализуем переносы строк
        $giftContent = str_replace("\xEF\xBB\xBF", '', $giftContent);
        $giftContent = str_replace("\r\n", "\n", $giftContent);
        $giftContent = str_replace("\r", "\n", $giftContent);
        
        // Разбиваем на вопросы по разделителю ::вопрос:: ... { ... }
        preg_match_all('/::(.*?)::(.*?)\{(.*?)\}/s', $giftContent, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $title = trim($match[1]);
            $body = trim($match[3]);
            
            // Пропускаем пустые вопросы
            if (empty($title) && empty($body)) continue;
            
            $this->parseQuestion($title, $body, $testId);
        }
    }
    
    private function parseQuestion($title, $body, $testId)
    {
        $question = new Question();
        $question->test_id = $testId;
        $question->text = $title ?: 'Вопрос';
        $question->points = 1;
        $question->order = 0;
        
        // Определяем тип вопроса
        if (preg_match('/->/', $body)) {
            // Соответствие (matching)
            $question->type = 'matching';
            $question->save();
            $this->parseMatching($question, $body);
        } elseif (preg_match('/^[=~].*$/m', $body) && !preg_match('/~~~\s*\n?$/', $body)) {
            // Выбор (одиночный или множественный)
            $isMultiple = substr_count($body, '=') > 1 || preg_match('/~.*~/', $body);
            $question->type = $isMultiple ? 'multiple_choice' : 'single_choice';
            $question->save();
            $this->parseChoice($question, $body);
        } elseif (preg_match('/~~~/', $body)) {
            // Эссе / открытый ответ
            $question->type = 'text';
            $question->correct_text = '';
            $question->save();
        } else {
            // По умолчанию - текстовый вопрос
            $question->type = 'text';
            $question->correct_text = $body;
            $question->save();
        }
    }
    
    private function parseMatching($question, $body)
    {
        // Ищем пары: =элемент -> соответствие
        preg_match_all('/^\s*(=.*?)\s*->\s*(.*?)\s*$/m', $body, $matches, PREG_SET_ORDER);
        
        if (empty($matches)) {
            // Альтернативный формат: элемент -> соответствие (без =)
            preg_match_all('/^\s*(.+?)\s*->\s*(.+?)\s*$/m', $body, $matches, PREG_SET_ORDER);
        }
        
        foreach ($matches as $index => $match) {
            $leftText = trim($match[1], "= \t\n\r\0\x0B");
            $leftText = trim($leftText, '[]');
            $rightText = trim($match[2]);
            
            if (empty($leftText) && empty($rightText)) continue;
            
            MatchingPair::create([
                'question_id' => $question->id,
                'left_text'   => $leftText ?: 'Элемент ' . ($index + 1),
                'right_text'  => $rightText ?: 'Соответствие ' . ($index + 1),
                'order'       => $index,
            ]);
        }
        
        // Если matching пар нет, меняем тип на text
        if ($question->matchingPairs()->count() == 0) {
            $question->type = 'text';
            $question->save();
        }
    }
    
    private function parseChoice($question, $body)
    {
        // Разбиваем на строки с = или ~
        $lines = preg_split('/\n+/', $body);
        
        $hasCorrect = false;
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $isCorrect = false;
            $text = $line;
            
            // Определяем правильный/неправильный
            if (str_starts_with($line, '=')) {
                $isCorrect = true;
                $hasCorrect = true;
                $text = substr($line, 1);
            } elseif (str_starts_with($line, '~')) {
                $text = substr($line, 1);
            }
            
            $text = trim($text);
            
            // Пропускаем пустые варианты
            if (empty($text)) continue;
            
            // Ограничиваем длину текста для вариантов выбора
            if (mb_strlen($text) > 500) {
                $text = mb_substr($text, 0, 497) . '...';
            }
            
            Choice::create([
                'question_id' => $question->id,
                'text'        => $text,
                'is_correct'  => $isCorrect,
            ]);
        }
        
        // Если ни один вариант не помечен как правильный, первый делаем правильным
        if (!$hasCorrect && $question->choices()->count() > 0) {
            $firstChoice = $question->choices()->first();
            $firstChoice->is_correct = true;
            $firstChoice->save();
        }
    }
}