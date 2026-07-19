<?php

namespace App\Http\Controllers\Flashcards;

use App\Domains\Flashcards\Actions\ReviewFlashcard;
use App\Domains\Flashcards\Models\Flashcard;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class ReviewFlashcardController extends Controller
{
    public function __invoke(Request $request, Flashcard $flashcard, ReviewFlashcard $reviewFlashcard): RedirectResponse
    {
        $data = $request->validate([
            'rating' => ['required', Rule::in(['again', 'hard', 'good', 'easy'])],
            'confidence' => ['nullable', 'integer', 'min:1', 'max:5'],
            'response_time_ms' => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            $reviewFlashcard->execute(
                $request->user(),
                $flashcard,
                $data['rating'],
                $data['confidence'] ?? null,
                $data['response_time_ms'] ?? null,
            );
        } catch (InvalidArgumentException) {
            abort(404);
        }

        return back()->with('status', 'Flashcard reviewed.');
    }
}
