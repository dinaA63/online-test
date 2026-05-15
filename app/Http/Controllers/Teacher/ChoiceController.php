<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Choice;
use Illuminate\Http\Request;

class ChoiceController extends Controller
{
    public function store(Request $request, Question $question)
    {
        $validated = $request->validate([
            'text' => 'required',
            'is_correct' => 'boolean',
        ]);
        $question->choices()->create($validated);
        return redirect()->route('teacher.questions.edit', $question)->with('success', 'Вариант ответа добавлен');
    }

    public function edit(Choice $choice)
    {
        return view('teacher.choices.edit', compact('choice'));
    }

    public function update(Request $request, Choice $choice)
    {
        $validated = $request->validate([
            'text' => 'required',
            'is_correct' => 'boolean',
        ]);
        $choice->update($validated);
        return redirect()->route('teacher.questions.edit', $choice->question)->with('success', 'Вариант ответа обновлён');
    }

    public function destroy(Choice $choice)
    {
        $question = $choice->question;
        $choice->delete();
        return redirect()->route('teacher.questions.edit', $question)->with('success', 'Вариант ответа удалён');
    }
}
