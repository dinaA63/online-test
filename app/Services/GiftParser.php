<?php

namespace App\Services;

use App\Models\Test;
use App\Models\Question;
use App\Models\Choice;
use App\Models\MatchingPair;
use App\Models\SequenceItem;
use Illuminate\Support\Facades\Log;

class GiftParser
{
    /**
     * Парсит строку в формате GIFT и создаёт вопросы для указанного теста.
     *
     * @param string $giftContent
     * @param int    $testId
     */
    public function parse($giftContent, $testId)
    {
        // Разбиваем на строки и обрабатываем
        $lines = explode("\n", $giftContent);
        $currentQuestion = null;
        $buffer = '';
        $type = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Начало вопроса: строка вида ::Название:: ... {
            if (preg_match('/^::(.*?)::/', $line, $matches)) {
                // Сохраняем предыдущий вопрос
                if ($currentQuestion !== null && !empty($buffer)) {
                    $this->saveQuestion($currentQuestion, $buffer, $testId);
                }
                $currentQuestion = $matches[1];
                $buffer = '';
            }

            $buffer .= $line . "\n";
        }

        // Не забываем последний вопрос
        if ($currentQuestion !== null && !empty($buffer)) {
            $this->saveQuestion($currentQuestion, $buffer, $testId);
        }
    }

    /**
     * Сохраняет вопрос в базу данных, определяя его тип.
     */
    private function saveQuestion($title, $text, $testId)
    {
        // Извлекаем содержимое внутри фигурных скобок
        if (!preg_match('/\{(.*)\}/s', $text, $bodyMatches)) {
            return; // некорректный формат
        }
        $body = trim($bodyMatches[1]);

        // Создаём базовый вопрос
        $question = new Question();
        $question->test_id = $testId;
        $question->text = $title;
        $question->points = 1;  // по умолчанию
        $question->order = 0;

        // ----- Определение типа -----

        // 1. Соответствие (содержит "->")
        if (preg_match('/->/', $body)) {
            $question->type = 'matching';
            $question->save();

            preg_match_all('/^\s*(=|~)?(.+?)\s*->\s*(.+?)\s*$/m', $body, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                MatchingPair::create([
                    'question_id' => $question->id,
                    'left_text'   => trim($match[2]),
                    'right_text'  => trim($match[3]),
                    'order'       => 0,
                ]);
            }
            return;
        }

        // 2. Последовательность (варианты начинаются с '=' и содержат цифры порядка? Упрощённо: считаем, что все варианты равны)
        // Проверяем, если каждая строка начинается с '='
        if (preg_match_all('/^\s*=\s*(.+?)\s*$/m', $body, $seqMatches, PREG_SET_ORDER) && count($seqMatches) > 1) {
            $question->type = 'sequence';
            $question->save();

            foreach ($seqMatches as $idx => $match) {
                SequenceItem::create([
                    'question_id'   => $question->id,
                    'item_text'     => trim($match[1]),
                    'correct_order' => $idx + 1,
                ]);
            }
            return;
        }

        // 3. Множественный/одиночный выбор (содержит '=' или '~')
        if (preg_match('/[=~]/', $body)) {
            // Если есть хотя бы один '~', считаем множественным (упрощение)
            $isMultiple = preg_match('/~/', $body);
            $question->type = $isMultiple ? 'multiple_choice' : 'single_choice';
            $question->save();

            preg_match_all('/(=|~)(%(-?\d+)%|)\s*(.*?)(?=\s*(=|~)|$)/s', $body, $choiceMatches, PREG_SET_ORDER);
            foreach ($choiceMatches as $cm) {
                Choice::create([
                    'question_id' => $question->id,
                    'text'        => trim($cm[4]),
                    'is_correct'  => $cm[1] === '=',
                ]);
            }
            return;
        }

        // 4. Текстовый вопрос (всё остальное)
        $question->type = 'text';
        $question->correct_text = trim($body);
        $question->save();
    }
}