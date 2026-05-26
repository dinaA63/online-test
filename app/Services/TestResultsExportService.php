<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\Test;
use Illuminate\Support\Collection;
use League\Csv\Writer;

class TestResultsExportService
{
    public function getAttempts(Test $test): Collection
    {
        return Attempt::where('test_id', $test->id)
            ->whereNotNull('finished_at')
            ->with('user')
            ->orderBy('finished_at')
            ->get();
    }

    public function toCsv(Test $test): string
    {
        $csv = Writer::createFromString('');
        $csv->setOutputBOM(Writer::BOM_UTF8);
        $csv->insertOne(['Студент', 'Email', 'Результат (%)', 'Ручная проверка', 'Дата завершения']);

        foreach ($this->getAttempts($test) as $attempt) {
            $csv->insertOne([
                $attempt->user->name,
                $attempt->user->email,
                round($attempt->score, 2),
                $attempt->pending_manual_review ? 'Ожидает' : 'Завершено',
                $attempt->finished_at?->format('d.m.Y H:i') ?? '',
            ]);
        }

        return $csv->toString();
    }

    public function toExcelHtml(Test $test): string
    {
        $rows = $this->getAttempts($test);
        $html = '<html><head><meta charset="UTF-8"></head><body><table border="1">';
        $html .= '<tr><th>Студент</th><th>Email</th><th>Результат (%)</th><th>Ручная проверка</th><th>Дата</th></tr>';

        foreach ($rows as $attempt) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($attempt->user->name) . '</td>';
            $html .= '<td>' . htmlspecialchars($attempt->user->email) . '</td>';
            $html .= '<td>' . round($attempt->score, 2) . '</td>';
            $html .= '<td>' . ($attempt->pending_manual_review ? 'Ожидает' : 'Завершено') . '</td>';
            $html .= '<td>' . ($attempt->finished_at?->format('d.m.Y H:i') ?? '') . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table></body></html>';

        return $html;
    }
}
