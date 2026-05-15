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
        'text'       => 'required|string',
        'type'       => 'required|string',
        'points'     => 'nullable|integer|min:1',
        'order'      => 'nullable|integer',
        'correct_text' => 'nullable|string', // для completion

        // для single/multiple/alternative
        'choices' => 'nullable|array',
        'choices.*.text' => 'required_with:choices|string',
        'choices.*.is_correct' => 'nullable|boolean',

        // для matching
        'pairs' => 'nullable|array',
        'pairs.*.left_text' => 'required_with:pairs|string',
        'pairs.*.right_text' => 'required_with:pairs|string',
        'pairs.*.order' => 'nullable|integer',

        // для sequence
        'sequence_items' => 'nullable|array',
        'sequence_items.*.item_text' => 'required_with:sequence_items|string',
        'sequence_items.*.correct_order' => 'required_with:sequence_items|integer',

        // для dropdown
        'dropdown_options' => 'nullable|array',
        'dropdown_options.*.option_text' => 'required_with:dropdown_options|string',
        'dropdown_options.*.is_correct' => 'nullable|boolean',

        // для drag_drop
        'drag_items' => 'nullable|array',
        'drag_items.*.item_text' => 'required_with:drag_items|string',
        'drag_items.*.target_zone' => 'required_with:drag_items|string',
        'drag_items.*.correct_zone' => 'required_with:drag_items|string',

        // для table_fill (структура)
        'table_headers' => 'nullable|array',
        'table_rows'    => 'nullable|array',
        'table_cells'   => 'nullable|array',
        'table_cells.*.row' => 'required_with:table_cells|integer',
        'table_cells.*.col' => 'required_with:table_cells|integer',
        'table_cells.*.expected_answer' => 'required_with:table_cells|string',
    ]);

    $question = $test->questions()->create([
        'text'        => $validated['text'],
        'type'        => $validated['type'],
        'points'      => $validated['points'] ?? 1,
        'order'       => $validated['order'] ?? 0,
        'correct_text'=> $validated['correct_text'] ?? null,
    ]);

    // Сохранение зависимостей в зависимости от типа
    switch ($validated['type']) {
        case 'single_choice':
        case 'multiple_choice':
        case 'alternative':
            if (isset($validated['choices'])) {
                foreach ($validated['choices'] as $choice) {
                    $question->choices()->create([
                        'text'       => $choice['text'],
                        'is_correct' => isset($choice['is_correct']) ? (bool)$choice['is_correct'] : false,
                    ]);
                }
            }
            break;

        case 'matching':
            if (isset($validated['pairs'])) {
                foreach ($validated['pairs'] as $pair) {
                    $question->matchingPairs()->create([
                        'left_text'  => $pair['left_text'],
                        'right_text' => $pair['right_text'],
                        'order'      => $pair['order'] ?? 0,
                    ]);
                }
            }
            break;

        case 'sequence':
            if (isset($validated['sequence_items'])) {
                foreach ($validated['sequence_items'] as $item) {
                    $question->sequenceItems()->create([
                        'item_text'     => $item['item_text'],
                        'correct_order' => $item['correct_order'],
                    ]);
                }
            }
            break;

        case 'dropdown':
            if (isset($validated['dropdown_options'])) {
                foreach ($validated['dropdown_options'] as $opt) {
                    $question->dropdownOptions()->create([
                        'option_text' => $opt['option_text'],
                        'is_correct'  => isset($opt['is_correct']) ? (bool)$opt['is_correct'] : false,
                    ]);
                }
            }
            break;

        case 'drag_drop':
            if (isset($validated['drag_items'])) {
                foreach ($validated['drag_items'] as $item) {
                    $question->dragDropItems()->create([
                        'item_text'   => $item['item_text'],
                        'target_zone' => $item['target_zone'],
                        'correct_zone'=> $item['correct_zone'],
                    ]);
                }
            }
            break;

        case 'table_fill':
            if (isset($validated['table_headers']) && isset($validated['table_cells'])) {
                $table = $question->tableFill()->create([
                    'headers' => json_encode($validated['table_headers']),
                    'rows'    => json_encode($validated['table_rows'] ?? []),
                ]);
                foreach ($validated['table_cells'] as $cell) {
                    $table->cells()->create([
                        'row_index'       => $cell['row'],
                        'col_index'       => $cell['col'],
                        'expected_answer' => $cell['expected_answer'],
                    ]);
                }
            }
            break;
    }

    return redirect()->route('teacher.tests.show', $test)->with('success', 'Вопрос добавлен');
}

    public function edit(Question $question)
    {
        return view('teacher.questions.edit', compact('question'));
    }

    public function update(Request $request, Question $question)
{
    $validated = $request->validate(/* те же правила, что и в store */);

    $question->update([
        'text'        => $validated['text'],
        'type'        => $validated['type'],
        'points'      => $validated['points'] ?? 1,
        'order'       => $validated['order'] ?? 0,
        'correct_text'=> $validated['correct_text'] ?? null,
    ]);

    // Удаляем старые зависимости
    $question->choices()->delete();
    $question->matchingPairs()->delete();
    $question->sequenceItems()->delete();
    $question->dropdownOptions()->delete();
    $question->dragDropItems()->delete();
    if ($question->tableFill) {
        $question->tableFill->cells()->delete();
        $question->tableFill->delete();
    }

    // Заново сохраняем (код аналогичен store)
    // ... (повторить switch как в store)

    return redirect()->route('teacher.tests.show', $question->test)->with('success', 'Вопрос обновлён');
}

    public function destroy(Question $question)
    {
        $test = $question->test;
        $question->delete();
        return redirect()->route('teacher.tests.show', $test)->with('success', 'Вопрос удалён');
    }
}