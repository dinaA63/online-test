<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\Question;
use App\Models\Test;
use Illuminate\Support\Collection;
use League\Csv\Writer;

class TestResultsExportService
{
    public function __construct(
        private readonly AttemptScoringService $scoring
    ) {}

    public function getAttempts(Test $test): Collection
    {
        return Attempt::where('test_id', $test->id)
            ->whereNotNull('finished_at')
            ->with([
                'user.groups',
                'answers.choice',
                'answers.question.choices',
                'answers.question.matchingPairs',
                'answers.question.sequenceItems',
            ])
            ->orderBy('finished_at')
            ->get();
    }

    public function getQuestions(Test $test): Collection
    {
        return $test->questions()
            ->with(['choices', 'matchingPairs', 'sequenceItems'])
            ->orderBy('order')
            ->orderBy('id')
            ->get();
    }

    public function toCsv(Test $test): string
    {
        $csv = Writer::createFromString('');
        $csv->setOutputBOM(Writer::BOM_UTF8);
        $csv->insertOne(['Студент', 'Email', 'Группа', 'Результат (%)', 'Ручная проверка', 'Дата завершения']);

        foreach ($this->getAttempts($test) as $attempt) {
            $csv->insertOne($this->summaryRow($attempt));
        }

        return $csv->toString();
    }

    public function toDetailedCsv(Test $test): string
    {
        $questions = $this->getQuestions($test);
        $csv = Writer::createFromString('');
        $csv->setOutputBOM(Writer::BOM_UTF8);

        $header = ['Студент', 'Email', 'Группа', 'Итого (%)', 'Дата'];
        foreach ($questions as $i => $q) {
            $header[] = 'В' . ($i + 1) . ' (' . $this->shortLabel($q) . ')';
        }
        $csv->insertOne($header);

        foreach ($this->getAttempts($test) as $attempt) {
            $row = [
                $attempt->user->name,
                $attempt->user->email,
                $attempt->user->groups->pluck('name')->join(', ') ?: '—',
                round($attempt->score, 2),
                $attempt->finished_at?->format('d.m.Y H:i') ?? '',
            ];
            $answersByQuestion = $attempt->answers->groupBy('question_id');
            foreach ($questions as $question) {
                $earned = $this->scoring->pointsEarned(
                    $question,
                    $answersByQuestion->get($question->id, collect())
                );
                $max = (float) ($question->points ?? 1);
                $row[] = $earned . ' / ' . $max;
            }
            $csv->insertOne($row);
        }

        return $csv->toString();
    }

    /** Excel (.xls) с листами: сводка, матрица, отдельный лист на каждую попытку */
    public function toExcelWorkbook(Test $test): string
    {
        $questions = $this->getQuestions($test);
        $attempts = $this->getAttempts($test);

        $sheets = [];
        $sheets[] = ['name' => 'Сводка', 'rows' => $this->summarySheetRows($attempts)];
        $sheets[] = ['name' => 'По вопросам', 'rows' => $this->matrixSheetRows($questions, $attempts)];

        foreach ($attempts as $index => $attempt) {
            $label = $this->attemptSheetName($attempt, $index + 1);
            $sheets[] = ['name' => $label, 'rows' => $this->attemptSheetRows($questions, $attempt)];
        }

        return $this->buildSpreadsheetXml($test->title, $sheets);
    }

    public function toExcelHtml(Test $test, bool $detailed = false): string
    {
        return $this->toExcelWorkbook($test);
    }

    private function summarySheetRows(Collection $attempts): array
    {
        $rows = [['Студент', 'Email', 'Группа', 'Результат (%)', 'Ручная проверка', 'Дата']];
        foreach ($attempts as $attempt) {
            $rows[] = $this->summaryRow($attempt);
        }

        return $rows;
    }

    private function matrixSheetRows(Collection $questions, Collection $attempts): array
    {
        $header = ['Студент', 'Email', 'Итого (%)'];
        foreach ($questions as $i => $q) {
            $header[] = 'В' . ($i + 1);
        }
        $rows = [$header];

        foreach ($attempts as $attempt) {
            $row = [
                $attempt->user->name,
                $attempt->user->email,
                (string) round($attempt->score, 2),
            ];
            $answersByQuestion = $attempt->answers->groupBy('question_id');
            foreach ($questions as $question) {
                $earned = $this->scoring->pointsEarned(
                    $question,
                    $answersByQuestion->get($question->id, collect())
                );
                $max = (float) ($question->points ?? 1);
                $row[] = $earned . ' / ' . $max;
            }
            $rows[] = $row;
        }

        return $rows;
    }

    private function attemptSheetRows(Collection $questions, Attempt $attempt): array
    {
        $rows = [
            ['Поле', 'Значение'],
            ['Студент', $attempt->user->name],
            ['Email', $attempt->user->email],
            ['Группа', $attempt->user->groups->pluck('name')->join(', ') ?: '—'],
            ['Результат (%)', (string) round($attempt->score, 2)],
            ['Дата', $attempt->finished_at?->format('d.m.Y H:i') ?? ''],
            ['Ручная проверка', $attempt->pending_manual_review ? 'Ожидает' : 'Завершено'],
            [],
            ['№', 'Вопрос', 'Тип', 'Ответ студента', 'Баллы', 'Макс', 'Верно'],
        ];

        $answersByQuestion = $attempt->answers->groupBy('question_id');
        foreach ($questions as $i => $question) {
            $qAnswers = $answersByQuestion->get($question->id, collect());
            $earned = $this->scoring->pointsEarned($question, $qAnswers);
            $max = (float) ($question->points ?? 1);
            $rows[] = [
                (string) ($i + 1),
                $question->text,
                $question->type_label,
                $this->formatAnswerDisplay($question, $qAnswers),
                (string) $earned,
                (string) $max,
                $this->scoring->isQuestionCorrect($question, $qAnswers) ? 'Да' : 'Нет',
            ];
        }

        return $rows;
    }

    private function formatAnswerDisplay(Question $question, Collection $answers): string
    {
        if ($answers->isEmpty()) {
            return '—';
        }

        return match ($question->type) {
            'single_choice' => $answers->first()?->choice?->text ?? '—',
            'multiple_choice' => $answers->pluck('choice.text')->filter()->join('; ') ?: '—',
            'text' => $answers->first()?->answer_text ?? '—',
            'matching' => $this->formatMatchingAnswer($question, $answers->first()?->answer_text),
            'sequence' => $this->formatSequenceAnswer($question, $answers->first()?->answer_text),
            default => $answers->first()?->answer_text ?? '—',
        };
    }

    private function formatMatchingAnswer(Question $question, ?string $json): string
    {
        $map = json_decode($json ?? '', true);
        if (!is_array($map) || empty($map)) {
            return '—';
        }
        $parts = [];
        foreach ($question->matchingPairs as $pair) {
            $sel = $map[$pair->id] ?? $map[(string) $pair->id] ?? null;
            $text = is_numeric($sel)
                ? ($question->matchingPairs->firstWhere('id', (int) $sel)?->right_text ?? '—')
                : ($sel ?? '—');
            $parts[] = $pair->left_text . ' → ' . $text;
        }

        return implode(' | ', $parts);
    }

    private function formatSequenceAnswer(Question $question, ?string $json): string
    {
        $order = json_decode($json ?? '', true)['order'] ?? null;
        if (!is_array($order) || empty($order)) {
            return '—';
        }
        $items = [];
        foreach ($order as $id) {
            $items[] = $question->sequenceItems->firstWhere('id', (int) $id)?->item_text ?? '?';
        }

        return implode(' → ', $items);
    }

    private function buildSpreadsheetXml(string $title, array $sheets): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" ';
        $xml .= 'xmlns:o="urn:schemas-microsoft-com:office:office" ';
        $xml .= 'xmlns:x="urn:schemas-microsoft-com:office:excel" ';
        $xml .= 'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" ';
        $xml .= 'xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
        $xml .= '<Styles>';
        $xml .= '<Style ss:ID="Header"><Font ss:Bold="1" ss:Color="#FFFFFF"/>';
        $xml .= '<Interior ss:Color="#1E90FF" ss:Pattern="Solid"/></Style>';
        $xml .= '</Styles>' . "\n";

        $usedNames = [];
        foreach ($sheets as $sheet) {
            $name = $this->uniqueSheetName($sheet['name'], $usedNames);
            $usedNames[] = $name;
            $xml .= '<Worksheet ss:Name="' . $this->xmlEscape($name) . '"><Table>' . "\n";
            foreach ($sheet['rows'] as $rowIndex => $row) {
                $xml .= '<Row>';
                foreach ($row as $cell) {
                    $style = ($rowIndex === 0 && !empty($row)) ? ' ss:StyleID="Header"' : '';
                    $type = is_numeric($cell) && $cell !== '' ? 'Number' : 'String';
                    $xml .= '<Cell' . $style . '><Data ss:Type="' . $type . '">';
                    $xml .= $this->xmlEscape((string) $cell);
                    $xml .= '</Data></Cell>';
                }
                $xml .= '</Row>' . "\n";
            }
            $xml .= '</Table></Worksheet>' . "\n";
        }

        $xml .= '</Workbook>';

        return $xml;
    }

    private function uniqueSheetName(string $name, array $used): string
    {
        $base = mb_substr(preg_replace('/[\\\\\\/:\\?\\*\\[\\]]/', '', $name) ?: 'Лист', 0, 28);
        $candidate = $base;
        $n = 1;
        while (in_array($candidate, $used, true)) {
            $suffix = '_' . $n++;
            $candidate = mb_substr($base, 0, 31 - mb_strlen($suffix)) . $suffix;
        }

        return mb_substr($candidate, 0, 31);
    }

    private function attemptSheetName(Attempt $attempt, int $num): string
    {
        $name = mb_substr($attempt->user->name, 0, 20);

        return $name . '_' . $num;
    }

    private function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function summaryRow(Attempt $attempt): array
    {
        return [
            $attempt->user->name,
            $attempt->user->email,
            $attempt->user->groups->pluck('name')->join(', ') ?: '—',
            round($attempt->score, 2),
            $attempt->pending_manual_review ? 'Ожидает' : 'Завершено',
            $attempt->finished_at?->format('d.m.Y H:i') ?? '',
        ];
    }

    private function shortLabel(Question $question): string
    {
        $text = mb_substr(strip_tags($question->text), 0, 40);
        if (mb_strlen($question->text) > 40) {
            $text .= '…';
        }

        return $text ?: $question->type_label;
    }
}
