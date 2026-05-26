<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MythController;

Route::get('/', [QuizController::class, 'index'])->name('home');
Route::get('/myths', [MythController::class, 'index'])->name('myths.index');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Leaderboard (Public)
Route::get('/leaderboard', [DashboardController::class, 'leaderboard'])->name('leaderboard');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // AI Features
    Route::get('/api/chat/history', [ChatController::class, 'getChatHistory'])->name('chat.history');
    Route::post('/api/chat', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::post('/api/explain', [ChatController::class, 'explain'])->name('chat.explain');
    
    // Quiz Routes
    Route::get('/quiz', [QuizController::class, 'showQuiz'])->name('quiz.show');
    Route::post('/quiz', [QuizController::class, 'submitQuiz'])->name('quiz.submit');
    Route::get('/results/{id}', [QuizController::class, 'showResults'])->name('results.show');

    // Admin Routes
    Route::middleware(\App\Http\Middleware\AdminMiddleware::class)->prefix('admin')->name('admin.')->group(function () {
        Route::get('/questions', [AdminController::class, 'index'])->name('questions.index');
        Route::post('/questions/generate', [AdminController::class, 'generateQuestion'])->name('questions.generate');
        Route::post('/questions', [AdminController::class, 'store'])->name('questions.store');
        Route::delete('/questions/{id}', [AdminController::class, 'destroy'])->name('questions.destroy');
    });
});
