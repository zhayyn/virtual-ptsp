<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ChannelController;
use App\Http\Controllers\Admin\KnowledgeBaseController;
use App\Http\Controllers\Admin\AiConfigController;

/*
|--------------------------------------------------------------------------
| Virtual PTSP - Web Routes
| Built with ❤️ by zhayyn (+6281317361689)
|--------------------------------------------------------------------------
*/

// ============================================================
// Public Routes
// ============================================================

// Landing Page
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

// Auth Routes
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Register
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // Google OAuth
    Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
});

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ============================================================
// Authenticated Routes
// ============================================================
Route::middleware(['auth', 'license'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ============================================================
    // Omnichannel Management
    // ============================================================
    Route::prefix('channels')->name('channels.')->group(function () {
        Route::get('/', [ChannelController::class, 'index'])->name('index');
        Route::get('/create', [ChannelController::class, 'create'])->name('create');
        Route::post('/', [ChannelController::class, 'store'])->name('store');
        Route::get('/{channel}', [ChannelController::class, 'show'])->name('show');
        Route::get('/{channel}/edit', [ChannelController::class, 'edit'])->name('edit');
        Route::put('/{channel}', [ChannelController::class, 'update'])->name('update');
        Route::delete('/{channel}', [ChannelController::class, 'destroy'])->name('destroy');
    });

    // WhatsApp Settings
    Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\WhatsAppController::class, 'index'])->name('index');
        Route::get('/settings', [\App\Http\Controllers\Admin\WhatsAppController::class, 'settings'])->name('settings');
        Route::post('/settings', [\App\Http\Controllers\Admin\WhatsAppController::class, 'saveSettings'])->name('save-settings');
        Route::get('/test', [\App\Http\Controllers\Admin\WhatsAppController::class, 'test'])->name('test');
    });

    // ============================================================
    // AI Configuration
    // ============================================================
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::get('/', [AiConfigController::class, 'index'])->name('index');
        Route::get('/config', [AiConfigController::class, 'config'])->name('config');
        Route::post('/config', [AiConfigController::class, 'saveConfig'])->name('save-config');
        Route::post('/test', [AiConfigController::class, 'test'])->name('test');
    });

    // ============================================================
    // Knowledge Base
    // ============================================================
    Route::prefix('knowledge-base')->name('knowledge-base.')->group(function () {
        Route::get('/', [KnowledgeBaseController::class, 'index'])->name('index');
        Route::get('/create', [KnowledgeBaseController::class, 'create'])->name('create');
        Route::post('/', [KnowledgeBaseController::class, 'store'])->name('store');
        Route::get('/{kb}', [KnowledgeBaseController::class, 'show'])->name('show');
        Route::get('/{kb}/items', [KnowledgeBaseController::class, 'items'])->name('items');
        Route::post('/{kb}/items', [KnowledgeBaseController::class, 'addItem'])->name('add-item');
        Route::delete('/items/{item}', [KnowledgeBaseController::class, 'deleteItem'])->name('delete-item');
        Route::post('/{kb}/process', [KnowledgeBaseController::class, 'process'])->name('process');
    });

    // ============================================================
    // Conversations (Inbox)
    // ============================================================
    Route::prefix('conversations')->name('conversations.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ConversationController::class, 'index'])->name('index');
        Route::get('/{conversation}', [\App\Http\Controllers\Admin\ConversationController::class, 'show'])->name('show');
        Route::post('/{conversation}/reply', [\App\Http\Controllers\Admin\ConversationController::class, 'reply'])->name('reply');
        Route::post('/{conversation}/assign', [\App\Http\Controllers\Admin\ConversationController::class, 'assign'])->name('assign');
        Route::post('/{conversation}/close', [\App\Http\Controllers\Admin\ConversationController::class, 'close'])->name('close');
    });

    // ============================================================
    // Web Chat Widget
    // ============================================================
    Route::prefix('widget')->name('widget.')->group(function () {
        Route::get('/settings', [\App\Http\Controllers\Admin\WidgetController::class, 'settings'])->name('settings');
        Route::post('/settings', [\App\Http\Controllers\Admin\WidgetController::class, 'saveSettings'])->name('save-settings');
        Route::get('/embed-code', [\App\Http\Controllers\Admin\WidgetController::class, 'embedCode'])->name('embed-code');
    });
});

// ============================================================
// Public Web Chat Widget Endpoint
// ============================================================
Route::post('/widget/chat', [\App\Http\Controllers\Api\WebChatController::class, 'chat'])->name('widget.chat');
Route::get('/widget/session/{sessionId}', [\App\Http\Controllers\Api\WebChatController::class, 'getSession']);

// ============================================================
// Health Check
// ============================================================
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});