<?php

namespace App\Http\Controllers\Practice;

use App\Domains\Practice\Models\QuizAttempt;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShowQuizAttempt extends Controller
{
    public function __invoke(Request $request, QuizAttempt $quizAttempt): View
    {
        if ($quizAttempt->user_id !== $request->user()->id) {
            abort(404);
        }

        $quizAttempt->load([
            'certification',
            'questions.version.options',
            'questions.answer.selectedOption',
            'domainScores.domain',
        ]);

        return view('practice.show', ['attempt' => $quizAttempt]);
    }
}
