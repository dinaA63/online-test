<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Models\Attempt;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AttemptController extends Controller
{
    public function start(Test $test)
    {
        $attempt = Attempt::create([
            'user_id' => auth()->id(),
            'test_id' => $test->id,
            'started_at' => now(),
        ]);
        return redirect()->route('student.attempt.show', $attempt);
    }

    public function show(Attempt $attempt)
    {
        if ($attempt->user_id !== auth()->id()) {
            abort(403);
        }
        if ($attempt->finished_at) {
            return redirect()->route('student.results');
        }
        $test = $attempt->test;
        $questions = $test->questions()->with('choices')->get();
        return view('student.attempt.show', compact('attempt', 'test', 'questions'));
    }

    /**
     * Сохранение ответа студента (поддержка всех типов вопросов)
     */
    public function saveAnswer(Request $request, Attempt $attempt)
    {
        if ($attempt->user_id !== auth()->id() || $attempt->finished_at) {
            return response()->json(['error' => 'Недопустимая операция'], 403);
        }

        $data = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'choice_id' => 'nullable|exists:choices,id',
            'choice_ids' => 'nullable|array',
            'choice_ids.*' => 'exists:choices,id',
            'answer_text' => 'nullable|string',
            // новые поля для сложных типов
            'matches' => 'nullable|array',            // для matching
            'sequence' => 'nullable|array',           // для sequence
            'dropdown_option_id' => 'nullable|exists:dropdown_options,id',
            'drops' => 'nullable|array',              // для drag_drop
            'cells' => 'nullable|array',              // для table_fill
        ]);

        $question = Question::findOrFail($data['question_id']);

        // Удаляем старые ответы на этот вопрос, чтобы не было дублей
        Answer::where('attempt_id', $attempt->id)
              ->where('question_id', $data['question_id'])
              ->delete();

        $answerData = [
            'attempt_id' => $attempt->id,
            'question_id' => $data['question_id'],
        ];

        switch ($question->type) {
            // --- Простые типы (один choice) ---
            case 'single_choice':
            case 'alternative':       // да/нет – используем choice_id
                $answerData['choice_id'] = $data['choice_id'] ?? null;
                $answerData['answer_text'] = null;
                Answer::create($answerData);
                break;

            // --- Множественный выбор (несколько choice_id) ---
            case 'multiple_choice':
                if (isset($data['choice_ids'])) {
                    foreach ($data['choice_ids'] as $choiceId) {
                        Answer::create([
                            'attempt_id' => $attempt->id,
                            'question_id' => $data['question_id'],
                            'choice_id' => $choiceId,
                        ]);
                    }
                }
                break;

            // --- Текстовые ответы (дополнение, эссе, обычный текст) ---
            case 'text':
            case 'completion':
            case 'essay':
                $answerData['answer_text'] = $data['answer_text'] ?? '';
                $answerData['choice_id'] = null;
                Answer::create($answerData);
                break;

            // --- Выпадающий список (используем choice_id как опцию) ---
            case 'dropdown':
                $answerData['choice_id'] = $data['dropdown_option_id'] ?? null;
                $answerData['answer_text'] = null;
                Answer::create($answerData);
                break;

            // --- Сложные типы (ответ в JSON) ---
            case 'matching':
                $answerData['answer_json'] = json_encode($data['matches'] ?? []);
                Answer::create($answerData);
                break;

            case 'sequence':
                $answerData['answer_json'] = json_encode($data['sequence'] ?? []);
                Answer::create($answerData);
                break;

            case 'drag_drop':
                $answerData['answer_json'] = json_encode($data['drops'] ?? []);
                Answer::create($answerData);
                break;

            case 'table_fill':
                $answerData['answer_json'] = json_encode($data['cells'] ?? []);
                Answer::create($answerData);
                break;

            // --- Загрузка файла ---
            case 'file_upload':
                if ($request->hasFile('file')) {
                    $path = $request->file('file')->store('test_answers', 'public');
                    $answerData['answer_text'] = $path;
                    $answerData['choice_id'] = null;
                    Answer::create($answerData);
                } else {
                    return response()->json(['error' => 'Файл не загружен'], 422);
                }
                break;

            default:
                return response()->json(['error' => 'Неизвестный тип вопроса'], 400);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Завершение теста (автоматическая проверка, учёт ручных ответов)
     */
    public function submit(Attempt $attempt)
    {
        if ($attempt->finished_at) {
            return response()->json(['success' => false, 'message' => 'Тест уже завершён']);
        }

        $totalPoints = 0;
        $earnedPoints = 0;
        $requiresManualReview = false;

        foreach ($attempt->test->questions as $question) {
            $userAnswers = $attempt->answers()->where('question_id', $question->id)->get();
            if ($userAnswers->isEmpty()) {
                // Нет ответа – баллы не начисляем, но общий балл считаем
                $totalPoints += $question->points ?? 1;
                continue;
            }

            $questionPoints = $question->points ?? 1;
            $totalPoints += $questionPoints;

            // Типы, которые требуют ручной проверки (не начисляем баллы сейчас)
            if (in_array($question->type, ['essay', 'file_upload'])) {
                $requiresManualReview = true;
                continue;
            }

            $isCorrect = false;

            switch ($question->type) {
                case 'single_choice':
                    $correctChoice = $question->choices()->where('is_correct', true)->first();
                    $selectedChoiceId = $userAnswers->first()->choice_id;
                    if ($correctChoice && $selectedChoiceId == $correctChoice->id) {
                        $isCorrect = true;
                    }
                    break;

                case 'multiple_choice':
                    $correctChoiceIds = $question->choices()->where('is_correct', true)->pluck('id')->toArray();
                    $userChoiceIds = $userAnswers->pluck('choice_id')->toArray();
                    if (empty(array_diff($correctChoiceIds, $userChoiceIds)) && empty(array_diff($userChoiceIds, $correctChoiceIds))) {
                        $isCorrect = true;
                    }
                    break;

                case 'alternative':
                    $correctChoice = $question->choices()->where('is_correct', true)->first();
                    $selectedChoiceId = $userAnswers->first()->choice_id;
                    if ($correctChoice && $selectedChoiceId == $correctChoice->id) {
                        $isCorrect = true;
                    }
                    break;

                case 'text':
                case 'completion':
                    $correctText = $question->correct_text ?? '';
                    $userText = $userAnswers->first()->answer_text ?? '';
                    if (strtolower(trim($userText)) == strtolower(trim($correctText))) {
                        $isCorrect = true;
                    }
                    break;

                case 'dropdown':
                    $correctOption = $question->dropdownOptions()->where('is_correct', true)->first();
                    $selectedOptionId = $userAnswers->first()->choice_id;
                    if ($correctOption && $selectedOptionId == $correctOption->id) {
                        $isCorrect = true;
                    }
                    break;

                case 'matching':
                    $isCorrect = $this->checkMatchingAnswer($question, $userAnswers->first()->answer_json);
                    break;

                case 'sequence':
                    $isCorrect = $this->checkSequenceAnswer($question, $userAnswers->first()->answer_json);
                    break;

                case 'drag_drop':
                    $isCorrect = $this->checkDragDropAnswer($question, $userAnswers->first()->answer_json);
                    break;

                case 'table_fill':
                    $isCorrect = $this->checkTableFillAnswer($question, $userAnswers->first()->answer_json);
                    break;

                default:
                    $isCorrect = false;
            }

            if ($isCorrect) {
                $earnedPoints += $questionPoints;
            }
        }

        $percentage = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;

        $attempt->update([
            'finished_at' => now(),
            'score' => $requiresManualReview ? null : round($percentage, 2),
            'pending_manual_review' => $requiresManualReview,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Проверка ответа на соответствие (matching)
     */
    private function checkMatchingAnswer($question, $userJson)
    {
        $userPairs = json_decode($userJson, true);
        if (!is_array($userPairs)) return false;

        $correctPairs = $question->matchingPairs()->get();
        if ($correctPairs->count() !== count($userPairs)) return false;

        foreach ($userPairs as $pair) {
            $found = $correctPairs->first(function ($p) use ($pair) {
                return $p->id == ($pair['pair_id'] ?? null) && $p->right_text == ($pair['right_text'] ?? '');
            });
            if (!$found) return false;
        }
        return true;
    }

    /**
     * Проверка последовательности (sequence)
     */
    private function checkSequenceAnswer($question, $userJson)
    {
        $userOrder = json_decode($userJson, true);
        if (!is_array($userOrder)) return false;

        $correctItems = $question->sequenceItems()->orderBy('correct_order')->get();
        if ($correctItems->count() !== count($userOrder)) return false;

        foreach ($userOrder as $index => $itemId) {
            if ($correctItems[$index]->id != $itemId) return false;
        }
        return true;
    }

    /**
     * Проверка перетаскивания (drag & drop)
     */
    private function checkDragDropAnswer($question, $userJson)
    {
        $userDrops = json_decode($userJson, true);
        if (!is_array($userDrops)) return false;

        $correctDrops = $question->dragDropItems()->get();
        foreach ($userDrops as $drop) {
            $found = $correctDrops->first(function ($d) use ($drop) {
                return $d->id == ($drop['item_id'] ?? null) && $d->correct_zone == ($drop['zone'] ?? '');
            });
            if (!$found) return false;
        }
        return true;
    }

    /**
     * Проверка заполнения таблицы (table fill)
     */
    private function checkTableFillAnswer($question, $userJson)
    {
        $userCells = json_decode($userJson, true);
        if (!is_array($userCells)) return false;

        $correctCells = $question->tableFill->cells ?? collect();
        foreach ($userCells as $cell) {
            $correct = $correctCells->first(function ($c) use ($cell) {
                return $c->row_index == ($cell['row'] ?? -1) &&
                       $c->col_index == ($cell['col'] ?? -1) &&
                       $c->expected_answer == ($cell['value'] ?? '');
            });
            if (!$correct) return false;
        }
        return true;
    }

    public function history()
    {
        $attempts = Attempt::where('user_id', auth()->id())
                         ->whereNotNull('finished_at')
                         ->with('test')
                         ->orderBy('finished_at', 'desc')
                         ->get();
        return view('student.results', compact('attempts'));
    }
}