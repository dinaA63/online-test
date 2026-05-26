<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Choice;
use App\Models\MatchingPair;
use App\Models\Question;
use App\Models\SequenceItem;
use App\Models\Test;
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
            'type' => 'required|in:single_choice,multiple_choice,text,matching,sequence',
            'points' => 'nullable|integer|min:1',
            'order' => 'nullable|integer',
            'correct_text' => 'nullable|string',
            'choices' => 'required_if:type,single_choice,multiple_choice|array',
            'choices.*.text' => 'required|string',
            'choices.*.is_correct' => 'sometimes|boolean',
            'pairs' => 'required_if:type,matching|array|min:2',
            'pairs.*.left_text' => 'required|string',
            'pairs.*.right_text' => 'required|string',
            'sequence_items' => 'required_if:type,sequence|array|min:2',
            'sequence_items.*.item_text' => 'required|string',
        ]);

        $question = $test->questions()->create([
            'text' => $validated['text'],
            'type' => $validated['type'],
            'points' => $validated['points'] ?? 1,
            'order' => $validated['order'] ?? 0,
            'correct_text' => $validated['type'] === 'text' ? ($validated['correct_text'] ?? null) : null,
        ]);

        if (isset($validated['choices']) && in_array($validated['type'], ['single_choice', 'multiple_choice'], true)) {
            foreach ($validated['choices'] as $choiceData) {
                $question->choices()->create([
                    'text' => $choiceData['text'],
                    'is_correct' => isset($choiceData['is_correct']) ? (bool) $choiceData['is_correct'] : false,
                ]);
            }
        }

        if ($validated['type'] === 'matching' && isset($validated['pairs'])) {
            foreach ($validated['pairs'] as $index => $pairData) {
                $question->matchingPairs()->create([
                    'left_text' => $pairData['left_text'],
                    'right_text' => $pairData['right_text'],
                    'order' => $index,
                ]);
            }
        }

        if ($validated['type'] === 'sequence' && isset($validated['sequence_items'])) {
            foreach ($validated['sequence_items'] as $index => $itemData) {
                $question->sequenceItems()->create([
                    'item_text' => $itemData['item_text'],
                    'correct_order' => $index + 1,
                ]);
            }
        }

        return redirect()->route('teacher.tests.show', $test)->with('success', 'Вопрос добавлен');
    }

    public function edit(Question $question)
    {
        $question->load('choices', 'matchingPairs', 'sequenceItems');

        return view('teacher.questions.edit', compact('question'));
    }

    public function update(Request $request, Question $question)
    {
        $validated = $request->validate([
            'text' => 'required|string',
            'type' => 'required|in:single_choice,multiple_choice,text,matching,sequence',
            'points' => 'nullable|integer|min:1',
            'order' => 'nullable|integer',
            'correct_text' => 'nullable|string',
            'choices' => 'required_if:type,single_choice,multiple_choice|array',
            'choices.*.id' => 'nullable|exists:choices,id',
            'choices.*.text' => 'required|string',
            'choices.*.is_correct' => 'sometimes|boolean',
            'deleted_choices' => 'nullable',
            'pairs' => 'required_if:type,matching|array|min:2',
            'pairs.*.id' => 'nullable|exists:matching_pairs,id',
            'pairs.*.left_text' => 'required|string',
            'pairs.*.right_text' => 'required|string',
            'deleted_pairs' => 'nullable',
            'sequence_items' => 'required_if:type,sequence|array|min:2',
            'sequence_items.*.id' => 'nullable|exists:sequence_items,id',
            'sequence_items.*.item_text' => 'required|string',
            'deleted_sequence_items' => 'nullable',
        ]);

        $question->update([
            'text' => $validated['text'],
            'type' => $validated['type'],
            'points' => $validated['points'] ?? ($question->points ?? 1),
            'order' => $validated['order'] ?? 0,
            'correct_text' => $validated['type'] === 'text' ? ($validated['correct_text'] ?? null) : null,
        ]);

        if ($validated['type'] !== 'matching') {
            $question->matchingPairs()->delete();
        }
        if ($validated['type'] !== 'sequence') {
            $question->sequenceItems()->delete();
        }
        if (!in_array($validated['type'], ['single_choice', 'multiple_choice'], true)) {
            $question->choices()->delete();
        }

        $deletedChoices = $this->parseDeletedIds($validated['deleted_choices'] ?? null);
        if (!empty($deletedChoices)) {
            Choice::whereIn('id', $deletedChoices)->delete();
        }

        if (isset($validated['choices'])) {
            foreach ($validated['choices'] as $choiceData) {
                if (!empty($choiceData['id'])) {
                    $choice = Choice::find($choiceData['id']);
                    if ($choice) {
                        $choice->update([
                            'text' => $choiceData['text'],
                            'is_correct' => $choiceData['is_correct'] ?? false,
                        ]);
                    }
                } else {
                    $question->choices()->create([
                        'text' => $choiceData['text'],
                        'is_correct' => $choiceData['is_correct'] ?? false,
                    ]);
                }
            }
        }

        $deletedPairs = $this->parseDeletedIds($validated['deleted_pairs'] ?? null);
        if (!empty($deletedPairs)) {
            MatchingPair::whereIn('id', $deletedPairs)->delete();
        }

        if ($validated['type'] === 'matching' && isset($validated['pairs'])) {
            foreach ($validated['pairs'] as $index => $pairData) {
                if (!empty($pairData['id'])) {
                    $pair = MatchingPair::find($pairData['id']);
                    if ($pair) {
                        $pair->update([
                            'left_text' => $pairData['left_text'],
                            'right_text' => $pairData['right_text'],
                            'order' => $index,
                        ]);
                    }
                } else {
                    $question->matchingPairs()->create([
                        'left_text' => $pairData['left_text'],
                        'right_text' => $pairData['right_text'],
                        'order' => $index,
                    ]);
                }
            }
        }

        $deletedSequence = $this->parseDeletedIds($validated['deleted_sequence_items'] ?? null);
        if (!empty($deletedSequence)) {
            SequenceItem::whereIn('id', $deletedSequence)->delete();
        }

        if ($validated['type'] === 'sequence' && isset($validated['sequence_items'])) {
            foreach ($validated['sequence_items'] as $index => $itemData) {
                if (!empty($itemData['id'])) {
                    $item = SequenceItem::find($itemData['id']);
                    if ($item) {
                        $item->update([
                            'item_text' => $itemData['item_text'],
                            'correct_order' => $index + 1,
                        ]);
                    }
                } else {
                    $question->sequenceItems()->create([
                        'item_text' => $itemData['item_text'],
                        'correct_order' => $index + 1,
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

    private function parseDeletedIds(mixed $value): array
    {
        if (is_string($value)) {
            $value = array_filter(explode(',', $value));
        }

        return is_array($value) ? $value : [];
    }
}
