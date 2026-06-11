<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Choice;
use App\Models\MatchingPair;
use App\Models\Question;
use App\Models\SequenceItem;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    public function create(Test $test)
    {
        $this->authorizeTest($test);

        return view('teacher.questions.create', compact('test'));
    }

    public function store(Request $request, Test $test)
    {
        $this->authorizeTest($test);

        $data = $this->prepareQuestionData($request);
        $validated = Validator::make($data, $this->questionRules())->validate();

        $question = $test->questions()->create([
            'text' => $validated['text'],
            'type' => $validated['type'],
            'points' => $validated['points'] ?? 1,
            'order' => $validated['order'] ?? 0,
            'correct_text' => $validated['type'] === 'text' ? ($validated['correct_text'] ?? null) : null,
        ]);

        $this->syncQuestionRelations($question, $validated);

        return redirect()->route('teacher.tests.show', $test)->with('success', 'Вопрос добавлен');
    }

    public function edit(Question $question)
    {
        $this->authorizeTest($question->test);
        $question->load('choices', 'matchingPairs', 'sequenceItems');

        return view('teacher.questions.edit', compact('question'));
    }

    public function update(Request $request, Question $question)
    {
        $this->authorizeTest($question->test);

        $data = $this->prepareQuestionData($request);
        $validated = Validator::make($data, $this->questionRules(true))->validate();

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

        $deletedPairs = $this->parseDeletedIds($validated['deleted_pairs'] ?? null);
        if (!empty($deletedPairs)) {
            MatchingPair::whereIn('id', $deletedPairs)->delete();
        }

        $deletedSequence = $this->parseDeletedIds($validated['deleted_sequence_items'] ?? null);
        if (!empty($deletedSequence)) {
            SequenceItem::whereIn('id', $deletedSequence)->delete();
        }

        $this->syncQuestionRelations($question, $validated, true);

        return redirect()->route('teacher.tests.show', $question->test)->with('success', 'Вопрос обновлён');
    }

    public function destroy(Question $question)
    {
        $this->authorizeTest($question->test);
        $test = $question->test;
        $question->delete();

        return redirect()->route('teacher.tests.show', $test)->with('success', 'Вопрос удалён');
    }

    private function questionRules(bool $isUpdate = false): array
    {
        $rules = [
            'text' => 'required|string',
            'type' => 'required|in:single_choice,multiple_choice,text,matching,sequence',
            'points' => 'nullable|integer|min:1',
            'order' => 'nullable|integer',
            'correct_text' => 'nullable|string',
            'choices' => 'required_if:type,single_choice,multiple_choice|array|min:1',
            'choices.*.text' => 'required|string',
            'choices.*.is_correct' => 'nullable',
            'pairs' => 'required_if:type,matching|array|min:2',
            'pairs.*.left_text' => 'required|string',
            'pairs.*.right_text' => 'required|string',
            'sequence_items' => 'required_if:type,sequence|array|min:2',
            'sequence_items.*.item_text' => 'required|string',
        ];

        if ($isUpdate) {
            $rules['choices.*.id'] = 'nullable|exists:choices,id';
            $rules['pairs.*.id'] = 'nullable|exists:matching_pairs,id';
            $rules['sequence_items.*.id'] = 'nullable|exists:sequence_items,id';
            $rules['deleted_choices'] = 'nullable';
            $rules['deleted_pairs'] = 'nullable';
            $rules['deleted_sequence_items'] = 'nullable';
        }

        return $rules;
    }

    private function prepareQuestionData(Request $request): array
    {
        $data = $request->all();
        $type = $data['type'] ?? '';

        if (!in_array($type, ['single_choice', 'multiple_choice'], true)) {
            unset($data['choices']);
        } else {
            $data['choices'] = array_values(array_filter(
                $data['choices'] ?? [],
                fn ($c) => trim((string) ($c['text'] ?? '')) !== ''
            ));
        }

        if ($type !== 'matching') {
            unset($data['pairs']);
        } else {
            $data['pairs'] = array_values(array_filter(
                $data['pairs'] ?? [],
                fn ($p) => trim((string) ($p['left_text'] ?? '')) !== ''
                    && trim((string) ($p['right_text'] ?? '')) !== ''
            ));
        }

        if ($type !== 'sequence') {
            unset($data['sequence_items']);
        } else {
            $data['sequence_items'] = array_values(array_filter(
                $data['sequence_items'] ?? [],
                fn ($s) => trim((string) ($s['item_text'] ?? '')) !== ''
            ));
        }

        if ($type !== 'text') {
            unset($data['correct_text']);
        }

        return $data;
    }

    private function syncQuestionRelations(Question $question, array $validated, bool $isUpdate = false): void
    {
        if (isset($validated['choices']) && in_array($validated['type'], ['single_choice', 'multiple_choice'], true)) {
            foreach ($validated['choices'] as $choiceData) {
                $isCorrect = !empty($choiceData['is_correct']);

                if ($isUpdate && !empty($choiceData['id'])) {
                    $choice = Choice::find($choiceData['id']);
                    if ($choice) {
                        $choice->update(['text' => $choiceData['text'], 'is_correct' => $isCorrect]);
                        continue;
                    }
                }

                $question->choices()->create([
                    'text' => $choiceData['text'],
                    'is_correct' => $isCorrect,
                ]);
            }
        }

        if ($validated['type'] === 'matching' && isset($validated['pairs'])) {
            foreach ($validated['pairs'] as $index => $pairData) {
                if ($isUpdate && !empty($pairData['id'])) {
                    $pair = MatchingPair::find($pairData['id']);
                    if ($pair) {
                        $pair->update([
                            'left_text' => $pairData['left_text'],
                            'right_text' => $pairData['right_text'],
                            'order' => $index,
                        ]);
                        continue;
                    }
                }

                $question->matchingPairs()->create([
                    'left_text' => $pairData['left_text'],
                    'right_text' => $pairData['right_text'],
                    'order' => $index,
                ]);
            }
        }

        if ($validated['type'] === 'sequence' && isset($validated['sequence_items'])) {
            foreach ($validated['sequence_items'] as $index => $itemData) {
                if ($isUpdate && !empty($itemData['id'])) {
                    $item = SequenceItem::find($itemData['id']);
                    if ($item) {
                        $item->update([
                            'item_text' => $itemData['item_text'],
                            'correct_order' => $index + 1,
                        ]);
                        continue;
                    }
                }

                $question->sequenceItems()->create([
                    'item_text' => $itemData['item_text'],
                    'correct_order' => $index + 1,
                ]);
            }
        }
    }

    private function parseDeletedIds(mixed $value): array
    {
        if (is_string($value)) {
            $value = array_filter(explode(',', $value));
        }

        return is_array($value) ? $value : [];
    }

    private function authorizeTest(Test $test): void
    {
        if ($test->created_by !== auth()->id()) {
            abort(403);
        }
    }
}
