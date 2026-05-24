<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GiftParser;

class GiftImportController extends Controller
{
    /**
     * Показывает форму загрузки файла GIFT.
     */
    public function create()
    {
        return view('teacher.gift.import');
    }

    /**
     * Обрабатывает загрузку файла и импорт вопросов.
     */
    public function store(Request $request)
    {
        $request->validate([
            'test_id' => 'required|exists:tests,id',
            'file'    => 'required|file|mimes:txt,gift|max:2048',
        ]);

        // Читаем содержимое файла
        $content = file_get_contents($request->file('file')->getRealPath());

        $parser = new GiftParser();
        $parser->parse($content, $request->test_id);

        return redirect()->route('teacher.tests.show', $request->test_id)
                         ->with('success', 'Вопросы успешно импортированы из GIFT.');
    }
}