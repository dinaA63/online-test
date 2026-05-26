<?php

namespace App\Services;

use App\Models\Choice;
use App\Models\MatchingPair;
use App\Models\Question;
use App\Models\SequenceItem;

class GiftParser
{
    public function parse(string $giftContent, int $testId): int
    {
        $giftContent = $this->normalizeContent($giftContent);
        $blocks = $this->extractQuestionBlocks($giftContent);
        $imported = 0;

        foreach ($blocks as $block) {
            if ($this->parseBlock($block, $testId)) {
                $imported++;
            }
        }

        return $imported;
    }

    private function normalizeContent(string $content): string
    {
        $content = str_replace("\xEF\xBB\xBF", '', $content);
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        return trim($content);
    }

    /**
     * @return array<int, array{title: string, preamble: string, body: string}>
     */
    private function extractQuestionBlocks(string $content): array
    {
        $blocks = [];
        $offset = 0;
        $length = strlen($content);

        while ($offset < $length) {
            $start = strpos($content, '::', $offset);
            if ($start === false) {
                break;
            }

            $titleEnd = strpos($content, '::', $start + 2);
            if ($titleEnd === false) {
                break;
            }

            $title = trim(substr($content, $start + 2, $titleEnd - $start - 2));
            $cursor = $titleEnd + 2;

            $bracePos = strpos($content, '{', $cursor);
            if ($bracePos === false) {
                break;
            }

            $preamble = trim(substr($content, $cursor, $bracePos - $cursor));
            $bodyEnd = $this->findClosingBrace($content, $bracePos);
            if ($bodyEnd === false) {
                break;
            }

            $body = substr($content, $bracePos + 1, $bodyEnd - $bracePos - 1);
            $blocks[] = [
                'title' => $title,
                'preamble' => $preamble,
                'body' => trim($body),
            ];

            $offset = $bodyEnd + 1;
        }

        return $blocks;
    }

    private function findClosingBrace(string $content, int $openPos): int|false
    {
        $depth = 0;
        $length = strlen($content);
        $inString = false;

        for ($i = $openPos; $i < $length; $i++) {
            $char = $content[$i];

            if ($char === '"' && ($i === 0 || $content[$i - 1] !== '\\')) {
                $inString = !$inString;
                continue;
            }

            if ($inString) {
                continue;
            }

            if ($char === '{') {
                $depth++;
            } elseif ($char === '}') {
                $depth--;
                if ($depth === 0) {
                    return $i;
                }
            }
        }

        return false;
    }

    private function parseBlock(array $block, int $testId): bool
    {
        $title = $block['title'];
        $preamble = $block['preamble'];
        $body = $block['body'];

        if ($title === '' && $body === '') {
            return false;
        }

        $questionText = $preamble !== '' ? $preamble : $title;
        if ($title !== '' && $preamble !== '') {
            $questionText = $title . "\n\n" . $preamble;
        }

        $question = new Question([
            'test_id' => $testId,
            'text' => $questionText,
            'points' => 1,
            'order' => 0,
        ]);

        if ($this->isEssayBody($body)) {
            $question->type = 'text';
            $question->correct_text = $this->extractEssaySample($body);
            $question->save();

            return true;
        }

        if ($this->isMatchingBody($body)) {
            $titleLower = mb_strtolower($title . ' ' . $preamble);
            $isSequence = $this->isSequenceQuestion($titleLower);

            $question->type = $isSequence ? 'sequence' : 'matching';
            $question->save();

            if ($isSequence) {
                $this->parseSequence($question, $body);
                if ($question->sequenceItems()->count() === 0) {
                    $question->delete();
                    return false;
                }
            } else {
                $this->parseMatching($question, $body);
                if ($question->matchingPairs()->count() === 0) {
                    $question->delete();
                    return false;
                }
            }

            return true;
        }

        if ($this->isChoiceBody($body)) {
            $question->type = $this->detectChoiceType($body);
            $question->save();
            $this->parseChoice($question, $body);

            if ($question->choices()->count() === 0) {
                $question->delete();

                return false;
            }

            return true;
        }

        $question->type = 'text';
        $question->correct_text = $body;
        $question->save();

        return true;
    }

    private function isEssayBody(string $body): bool
    {
        return (bool) preg_match('/^\s*~~~/m', $body);
    }

    private function extractEssaySample(string $body): string
    {
        $body = preg_replace('/^\s*~~~/m', '', $body);

        return trim($body ?? '');
    }

    private function isMatchingBody(string $body): bool
    {
        return (bool) preg_match('/^\s*=?.+?\s*->\s*.+?\s*$/m', $body);
    }

    private function isSequenceQuestion(string $titleLower): bool
    {
        return (bool) preg_match(
            '/последовательност|расставьте|расположите|этап|шаг|порядк|располаж|логическ/u',
            $titleLower
        );
    }

    private function parseSequence(Question $question, string $body): void
    {
        $pairs = $this->extractArrowPairs($body);

        foreach ($pairs as $index => $pair) {
            $label = $pair['left'];
            $text = $pair['right'];
            $display = $label !== '' && $label !== 'Элемент'
                ? '[' . $label . '] ' . $text
                : $text;

            SequenceItem::create([
                'question_id' => $question->id,
                'item_text' => $display,
                'correct_order' => $index + 1,
            ]);
        }
    }

    private function isChoiceBody(string $body): bool
    {
        return (bool) preg_match('/^\s*[=~]/m', $body);
    }

    private function detectChoiceType(string $body): string
    {
        preg_match_all('/^\s*=/m', $body, $correctMatches);

        return count($correctMatches[0] ?? []) > 1 ? 'multiple_choice' : 'single_choice';
    }

    private function parseMatching(Question $question, string $body): void
    {
        $pairs = $this->extractArrowPairs($body);

        foreach ($pairs as $index => $pair) {
            MatchingPair::create([
                'question_id' => $question->id,
                'left_text' => $pair['left'],
                'right_text' => $pair['right'],
                'order' => $index,
            ]);
        }
    }

    /**
     * @return array<int, array{left: string, right: string}>
     */
    private function extractArrowPairs(string $body): array
    {
        $pairs = [];
        $lines = preg_split('/\n+/', $body) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || !str_contains($line, '->')) {
                continue;
            }

            if (!preg_match('/^\s*(=?)(.+?)\s*->\s*(.+?)\s*$/u', $line, $match)) {
                continue;
            }

            $left = trim($match[2], " \t=[]{");
            $right = trim($match[3]);

            if ($left === '' && $right === '') {
                continue;
            }

            $pairs[] = [
                'left' => $left ?: 'Элемент',
                'right' => $right ?: 'Ответ',
            ];
        }

        return $pairs;
    }

    private function parseChoice(Question $question, string $body): void
    {
        $lines = preg_split('/\n+/', $body) ?: [];
        $hasCorrect = false;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_contains($line, '->')) {
                continue;
            }

            $isCorrect = false;
            $text = $line;

            if (str_starts_with($line, '=')) {
                $isCorrect = true;
                $hasCorrect = true;
                $text = substr($line, 1);
            } elseif (str_starts_with($line, '~')) {
                $text = substr($line, 1);
                if (str_starts_with($text, '=')) {
                    $isCorrect = true;
                    $hasCorrect = true;
                    $text = substr($text, 1);
                } elseif (str_starts_with($text, '~')) {
                    $text = substr($text, 1);
                }
            }

            $text = trim($text);
            if ($text === '') {
                continue;
            }

            if (mb_strlen($text) > 2000) {
                $text = mb_substr($text, 0, 1997) . '...';
            }

            Choice::create([
                'question_id' => $question->id,
                'text' => $text,
                'is_correct' => $isCorrect,
            ]);
        }

        if (!$hasCorrect && $question->choices()->count() > 0) {
            $first = $question->choices()->first();
            $first->update(['is_correct' => true]);
        }
    }
}
