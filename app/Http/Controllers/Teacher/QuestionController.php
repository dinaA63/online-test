<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Models\Question;
use App\Models\Choice;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function create(Test $test)
    {
        return view('teacher.questions.create', compact('test'));
    }

public function store(Request $request, Test $test)
{
    $validated = $request->validate([
        'text' => 'required|string',
        'type' => 'required|in:single_choice,multiple_choice,text',
        'points' => 'nullable|integer|min:1',
        'order' => 'nullable|integer',
        'choices' => 'required_if:type,single_choice,multiple_choice|array',
        'choices.*.text' => 'required|string',
        'choices.*.is_correct' => 'sometimes|boolean',
    ]);

    // Создаём вопрос
    $question = $test->questions()->create([
        'text' => $validated['text'],
        'type' => $validated['type'],
        'points' => $validated['points'] ?? 1,
        'order' => $validated['order'] ?? 0,
    ]);

    // Если есть варианты ответов, сохраняем их
    if (isset($validated['choices']) && in_array($validated['type'], ['single_choice', 'multiple_choice'])) {
        foreach ($validated['choices'] as $choiceData) {
            $question->choices()->create([
                'text' => $choiceData['text'],
                'is_correct' => isset($choiceData['is_correct']) ? (bool)$choiceData['is_correct'] : false,
            ]);
        }
    }

    return redirect()->route('teacher.tests.show', $test)->with('success', 'Вопрос добавлен');
}

    public function edit(Question $question)
    {
        return view('teacher.questions.edit', compact('question'));
    }

    public function update(Request $request, Question $question)
    {
        $validated = $request->validate([
            'text' => 'required|string',
            'type' => 'required|in:single_choice,multiple_choice,text',
            'order' => 'nullable|integer',
            'choices' => 'required_if:type,single_choice,multiple_choice|array',
            'choices.*.id' => 'nullable|exists:choices,id',
            'choices.*.text' => 'required|string',
            'choices.*.is_correct' => 'sometimes|boolean',
            'deleted_choices' => 'nullable|array',
            'deleted_choices.*' => 'exists:choices,id',
        ]);

        // Обновляем основные поля вопроса
        $question->update([
            'text' => $validated['text'],
            'type' => $validated['type'],
            'order' => $validated['order'] ?? 0,
        ]);

        // Удаляем отмеченные варианты
        if (!empty($validated['deleted_choices'])) {
            Choice::whereIn('id', $validated['deleted_choices'])->delete();
        }

        // Обновляем или создаём варианты
        if (isset($validated['choices'])) {
            foreach ($validated['choices'] as $choiceData) {
                if (!empty($choiceData['id'])) {
                    // Обновляем существующий вариант
                    $choice = Choice::find($choiceData['id']);
                    if ($choice) {
                        $choice->update([
                            'text' => $choiceData['text'],
                            'is_correct' => $choiceData['is_correct'] ?? false,
                        ]);
                    }
                } else {
                    // Создаём новый вариант
                    $question->choices()->create([
                        'text' => $choiceData['text'],
                        'is_correct' => $choiceData['is_correct'] ?? false,
                    ]);
                }
            }
        }

        return redirect()->route('teacher.tests.show', $question->test)->with('success', 'Вопрос обновлён');
    }

    public function destroy(Question $question)
    {
        $test = $question->test;
        $question->delete();
        return redirect()->route('teacher.tests.show', $test)->with('success', 'Вопрос удалён');
    }
}