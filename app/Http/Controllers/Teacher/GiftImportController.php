<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Services\GiftParser;
use Illuminate\Http\Request;

class GiftImportController extends Controller
{
    public function create(Request $request)
    {
        $tests = auth()->user()->tests()->orderBy('title')->get();
        $selectedTestId = $request->query('test_id');
        $sampleAvailable = is_readable(base_path('gift.txt'));

        return view('teacher.gift.import', compact('tests', 'selectedTestId', 'sampleAvailable'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'test_id' => 'required|exists:tests,id',
            'file' => 'required_without:use_sample|file|max:5120',
            'use_sample' => 'nullable|boolean',
        ]);

        $test = Test::findOrFail($request->test_id);
        if ($test->created_by !== auth()->id()) {
            abort(403);
        }

        if ($request->boolean('use_sample')) {
            $path = base_path('gift.txt');
            if (!is_readable($path)) {
                return back()->withErrors(['file' => 'Файл gift.txt не найден в корне проекта.'])->withInput();
            }
            $content = file_get_contents($path);
        } else {
            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, ['txt', 'gift'], true)) {
                return back()->withErrors(['file' => 'Допустимы только файлы .txt и .gift'])->withInput();
            }
            $content = file_get_contents($file->getRealPath());
        }

        if ($content === false || trim($content) === '') {
            return back()->withErrors(['file' => 'Файл пустой или не удалось прочитать.'])->withInput();
        }

        $parser = new GiftParser();
        $count = $parser->parse($content, $test->id);

        if ($count === 0) {
            return back()->withErrors(['file' => 'Не удалось распознать вопросы. Проверьте формат GIFT.'])->withInput();
        }

        return redirect()
            ->route('teacher.tests.show', $test)
            ->with('success', "Импортировано вопросов: {$count}.");
    }
}
