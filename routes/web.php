<?php

use App\Http\Controllers\Auth\LoginUser;
use App\Http\Controllers\Auth\LogoutUser;
use App\Http\Controllers\Auth\ShowLogin;
use App\Http\Controllers\Certifications\ShowCertification;
use App\Http\Controllers\Dashboard\ShowDashboard;
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
    Route::get('/certifications/{certificationSlug}', ShowCertification::class)->name('certifications.show');
    Route::post('/certifications/{certificationSlug}/lessons/{lesson}/completion', StoreLessonCompletion::class)->name('lessons.completions.store');
    Route::post('/certifications/{certificationSlug}/lessons/{lesson}/notes', StoreLessonNote::class)->name('lessons.notes.store');
});
