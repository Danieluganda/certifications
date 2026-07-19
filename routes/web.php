<?php

use App\Http\Controllers\Auth\LoginUser;
use App\Http\Controllers\Auth\LogoutUser;
use App\Http\Controllers\Auth\ShowLogin;
use App\Http\Controllers\Certifications\ActivateFreeCredentialController;
use App\Http\Controllers\Certifications\SetPrimaryCertificationController;
use App\Http\Controllers\Certifications\ShowCertification;
use App\Http\Controllers\Certifications\StoreCertification;
use App\Http\Controllers\Curriculum\StoreDomain;
use App\Http\Controllers\Curriculum\StoreTopic;
use App\Http\Controllers\Dashboard\ShowDashboard;
use App\Http\Controllers\Planning\CompleteStudySession;
use App\Http\Controllers\Planning\StoreStudySession;
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
    Route::post('/certifications', StoreCertification::class)->name('certifications.store');
    Route::get('/certifications/{certificationSlug}', ShowCertification::class)->name('certifications.show');
    Route::post('/certifications/{certificationSlug}/primary', SetPrimaryCertificationController::class)->name('certifications.primary.store');
    Route::post('/certifications/{certificationSlug}/free-activation', ActivateFreeCredentialController::class)->name('certifications.free-activation.store');
    Route::post('/certifications/{certificationSlug}/domains', StoreDomain::class)->name('domains.store');
    Route::post('/certifications/{certificationSlug}/topics', StoreTopic::class)->name('topics.store');
    Route::post('/certifications/{certificationSlug}/lessons/{lesson}/completion', StoreLessonCompletion::class)->name('lessons.completions.store');
    Route::post('/certifications/{certificationSlug}/lessons/{lesson}/notes', StoreLessonNote::class)->name('lessons.notes.store');
    Route::post('/study-sessions', StoreStudySession::class)->name('study-sessions.store');
    Route::post('/study-sessions/{studySession}/complete', CompleteStudySession::class)->name('study-sessions.complete');
});
