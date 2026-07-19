<?php

use App\Http\Controllers\Auth\LoginUser;
use App\Http\Controllers\Auth\LogoutUser;
use App\Http\Controllers\Auth\ShowLogin;
use App\Http\Controllers\Budgeting\StoreSavingsTransaction;
use App\Http\Controllers\Certifications\ActivateFreeCredentialController;
use App\Http\Controllers\Certifications\SetPrimaryCertificationController;
use App\Http\Controllers\Certifications\ShowCertification;
use App\Http\Controllers\Certifications\StoreCertification;
use App\Http\Controllers\Curriculum\StoreDomain;
use App\Http\Controllers\Curriculum\StoreTopic;
use App\Http\Controllers\Dashboard\ShowDashboard;
use App\Http\Controllers\Credentials\StoreCredential;
use App\Http\Controllers\Exports\DownloadLearningBackup;
use App\Http\Controllers\Flashcards\ReviewFlashcardController;
use App\Http\Controllers\Flashcards\StoreFlashcard;
use App\Http\Controllers\Planning\CompleteStudySession;
use App\Http\Controllers\Planning\StoreStudyGoal;
use App\Http\Controllers\Planning\StoreStudySession;
use App\Http\Controllers\Practice\ShowQuizAttempt;
use App\Http\Controllers\Practice\StartQuizAttempt;
use App\Http\Controllers\Practice\SubmitQuizAttempt;
use App\Http\Controllers\Progress\CalculateReadinessController;
use App\Http\Controllers\Projects\StoreProject;
use App\Http\Controllers\Projects\StoreProjectEvidence;
use App\Http\Controllers\Resources\StoreResource;
use App\Http\Controllers\Study\StoreLessonCompletion;
use App\Http\Controllers\Study\StoreLessonNote;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', ShowLogin::class)->name('login');
    Route::post('/login', LoginUser::class)->name('login.store');
});

Route::post('/logout', LogoutUser::class)->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/', ShowDashboard::class)->name('dashboard');
    Route::get('/dashboard/{dashboardPage}', ShowDashboard::class)
        ->whereIn('dashboardPage', ['learn', 'today', 'catalogue', 'planner', 'roadmap', 'workspace', 'projects', 'resources'])
        ->name('dashboard.page');
    Route::get('/exports/learning-backup', DownloadLearningBackup::class)->name('exports.learning-backup');
    Route::post('/certifications', StoreCertification::class)->name('certifications.store');
    Route::get('/certifications/{certificationSlug}/{workspacePage?}', ShowCertification::class)
        ->whereIn('workspacePage', ['overview', 'curriculum', 'lesson', 'practice', 'readiness', 'flashcards', 'budget', 'projects', 'credentials', 'resources'])
        ->name('certifications.show');
    Route::post('/certifications/{certificationSlug}/primary', SetPrimaryCertificationController::class)->name('certifications.primary.store');
    Route::post('/certifications/{certificationSlug}/free-activation', ActivateFreeCredentialController::class)->name('certifications.free-activation.store');
    Route::post('/certifications/{certificationSlug}/domains', StoreDomain::class)->name('domains.store');
    Route::post('/certifications/{certificationSlug}/topics', StoreTopic::class)->name('topics.store');
    Route::post('/certifications/{certificationSlug}/resources', StoreResource::class)->name('resources.store');
    Route::post('/certifications/{certificationSlug}/flashcards', StoreFlashcard::class)->name('flashcards.store');
    Route::post('/certifications/{certificationSlug}/quiz-attempts', StartQuizAttempt::class)->name('quiz-attempts.store');
    Route::post('/certifications/{certificationSlug}/readiness', CalculateReadinessController::class)->name('readiness.calculate');
    Route::post('/certifications/{certificationSlug}/savings', StoreSavingsTransaction::class)->name('savings.store');
    Route::post('/certifications/{certificationSlug}/projects', StoreProject::class)->name('projects.store');
    Route::post('/projects/{project}/evidence', StoreProjectEvidence::class)->name('projects.evidence.store');
    Route::post('/certifications/{certificationSlug}/credentials', StoreCredential::class)->name('credentials.store');
    Route::get('/quiz-attempts/{quizAttempt}', ShowQuizAttempt::class)->name('quiz-attempts.show');
    Route::post('/quiz-attempts/{quizAttempt}/submit', SubmitQuizAttempt::class)->name('quiz-attempts.submit');
    Route::post('/flashcards/{flashcard}/reviews', ReviewFlashcardController::class)->name('flashcards.reviews.store');
    Route::post('/certifications/{certificationSlug}/lessons/{lesson}/completion', StoreLessonCompletion::class)->name('lessons.completions.store');
    Route::post('/certifications/{certificationSlug}/lessons/{lesson}/notes', StoreLessonNote::class)->name('lessons.notes.store');
    Route::post('/study-sessions', StoreStudySession::class)->name('study-sessions.store');
    Route::post('/study-sessions/{studySession}/complete', CompleteStudySession::class)->name('study-sessions.complete');
    Route::post('/study-goals', StoreStudyGoal::class)->name('study-goals.store');
});
