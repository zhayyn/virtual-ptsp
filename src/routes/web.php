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
use App\Http\Controllers\Admin\WhatsAppController;
use App\Http\Controllers\Admin\SocialMediaController;
use App\Http\Controllers\Admin\ConversationController;
use App\Http\Controllers\Admin\WidgetController;
use App\Http\Controllers\Api\WebChatController;
use App\Http\Controllers\Api\OmnichannelWebhookController;

/*
|--------------------------------------------------------------------------
| Virtual PTSP - Web Routes
| Built with ❤️ by zhayyn (+6281317361689)
|--------------------------------------------------------------------------
*/

// ============================================================
// PUBLIC ROUTES
// ============================================================

// Landing Page
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
});

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ============================================================
// PUBLIC API - WebChat Widget
// ============================================================
Route::prefix('api/v1')->group(function () {
    // WebChat Widget API
    Route::post('/widget/chat', [WebChatController::class, 'chat']);
    Route::get('/widget/session/{sessionId}', [WebChatController::class, 'getSession']);
    Route::get('/widget/messages/{sessionId}', [WebChatController::class, 'getMessages']);
    Route::get('/widget/config', [WebChatController::class, 'getWidgetConfig']);
});

// ============================================================
// WEBHOOKS (Public - with signature verification)
// ============================================================
Route::prefix('webhooks')->group(function () {
    // WhatsApp Webhook
    Route::post('/whatsapp', [WhatsAppController::class, 'webhook'])->name('webhooks.whatsapp');

    // Social Media Webhooks
    Route::post('/instagram', [SocialMediaController::class, 'instagramWebhook'])->name('webhooks.instagram');
    Route::post('/facebook', [SocialMediaController::class, 'facebookWebhook'])->name('webhooks.facebook');
    Route::post('/telegram', [SocialMediaController::class, 'telegramWebhook'])->name('webhooks.telegram');
});

// ============================================================
// AUTHENTICATED ROUTES
// ============================================================
Route::middleware(['auth', 'license'])->group(function () {

    // ============================================================
    // DASHBOARD
    // ============================================================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ============================================================
    // CHANNELS MANAGEMENT
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

    // ============================================================
    // WHATSAPP MANAGEMENT (Baileys/Reverb)
    // ============================================================
    Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
        Route::get('/', [WhatsAppController::class, 'index'])->name('index');            // Dashboard
        Route::get('/settings', [WhatsAppController::class, 'settings'])->name('settings'); // Settings page
        Route::post('/settings', [WhatsAppController::class, 'saveSettings'])->name('save-settings');
        Route::post('/save-settings', [WhatsAppController::class, 'saveSettings'])->name('save-settings-post');

        // Connection Management
        Route::get('/status', [WhatsAppController::class, 'status'])->name('status');           // Polling API
        Route::get('/qr', [WhatsAppController::class, 'qrCode'])->name('qr');                  // Get QR code
        Route::post('/disconnect', [WhatsAppController::class, 'disconnect'])->name('disconnect');
        Route::post('/restart', [WhatsAppController::class, 'restart'])->name('restart');

        // Messaging
        Route::post('/test', [WhatsAppController::class, 'sendTest'])->name('test');           // Send test message
        Route::get('/chats', [WhatsAppController::class, 'chats'])->name('chats');           // Get chat list
        Route::get('/chats/{phone}/history', [WhatsAppController::class, 'chatHistory'])->name('chat-history');

        // Webhook endpoint (already defined above for public)
        Route::post('/webhook', [WhatsAppController::class, 'webhook'])->name('webhook');
    });

    // ============================================================
    // SOCIAL MEDIA MANAGEMENT
    // ============================================================
    Route::prefix('social')->name('social.')->group(function () {
        Route::get('/', [SocialMediaController::class, 'index'])->name('index');           // Social media dashboard
        Route::get('/settings', [SocialMediaController::class, 'settings'])->name('settings');
        Route::post('/settings', [SocialMediaController::class, 'saveSettings'])->name('save-settings');

        // Platform-specific
        Route::prefix('instagram')->name('instagram.')->group(function () {
            Route::get('/connect', [SocialMediaController::class, 'instagramConnect'])->name('connect');
            Route::get('/callback', [SocialMediaController::class, 'instagramCallback'])->name('callback');
            Route::post('/disconnect', [SocialMediaController::class, 'instagramDisconnect'])->name('disconnect');
        });

        Route::prefix('facebook')->name('facebook.')->group(function () {
            Route::get('/connect', [SocialMediaController::class, 'facebookConnect'])->name('connect');
            Route::get('/callback', [SocialMediaController::class, 'facebookCallback'])->name('callback');
            Route::post('/disconnect', [SocialMediaController::class, 'facebookDisconnect'])->name('disconnect');
        });

        Route::prefix('telegram')->name('telegram.')->group(function () {
            Route::get('/connect', [SocialMediaController::class, 'telegramConnect'])->name('connect');
            Route::post('/disconnect', [SocialMediaController::class, 'telegramDisconnect'])->name('disconnect');
        });
    });

    // ============================================================
    // CONVERSATIONS (Unified Inbox)
    // ============================================================
    Route::prefix('conversations')->name('conversations.')->group(function () {
        Route::get('/', [ConversationController::class, 'index'])->name('index');           // Inbox list
        Route::get('/{conversation}', [ConversationController::class, 'show'])->name('show');     // Chat view
        Route::post('/{conversation}/reply', [ConversationController::class, 'reply'])->name('reply'); // Send reply
        Route::post('/{conversation}/assign', [ConversationController::class, 'assign'])->name('assign');
        Route::post('/{conversation}/close', [ConversationController::class, 'close'])->name('close');
        Route::post('/{conversation}/transfer', [ConversationController::class, 'transfer'])->name('transfer');
        Route::get('/{conversation}/messages', [ConversationController::class, 'messages'])->name('messages');
    });

    // ============================================================
    // AI CONFIGURATION
    // ============================================================
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::get('/', [AiConfigController::class, 'index'])->name('index');               // AI dashboard
        Route::get('/config', [AiConfigController::class, 'config'])->name('config');       // Config page
        Route::post('/config', [AiConfigController::class, 'saveConfig'])->name('save-config');
        Route::post('/test', [AiConfigController::class, 'test'])->name('test');           // Test AI
    });

    // ============================================================
    // KNOWLEDGE BASE
    // ============================================================
    Route::prefix('knowledge-base')->name('knowledge-base.')->group(function () {
        Route::get('/', [KnowledgeBaseController::class, 'index'])->name('index');
        Route::get('/create', [KnowledgeBaseController::class, 'create'])->name('create');
        Route::post('/', [KnowledgeBaseController::class, 'store'])->name('store');
        Route::get('/{kb}', [KnowledgeBaseController::class, 'show'])->name('show');
        Route::get('/{kb}/items', [KnowledgeBaseController::class, 'items'])->name('items');
        Route::post('/{kb}/items', [KnowledgeBaseController::class, 'addItem'])->name('add-item');
        Route::post('/{kb}/scrape', [KnowledgeBaseController::class, 'scrapeUrl'])->name('scrape');
        Route::post('/{kb}/upload', [KnowledgeBaseController::class, 'uploadFile'])->name('upload');
        Route::delete('/items/{item}', [KnowledgeBaseController::class, 'deleteItem'])->name('delete-item');
        Route::post('/{kb}/process', [KnowledgeBaseController::class, 'process'])->name('process');
    });

    // ============================================================
    // WEB CHAT WIDGET
    // ============================================================
    Route::prefix('widget')->name('widget.')->group(function () {
        Route::get('/settings', [WidgetController::class, 'settings'])->name('settings');
        Route::post('/settings', [WidgetController::class, 'saveSettings'])->name('save-settings');
        Route::get('/embed-code', [WidgetController::class, 'embedCode'])->name('embed-code');
        Route::get('/preview', [WidgetController::class, 'preview'])->name('preview');
    });

    // ============================================================
    // SETTINGS
    // ============================================================
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('index');
        Route::post('/general', [\App\Http\Controllers\Admin\SettingsController::class, 'saveGeneral'])->name('general');
        Route::post('/appearance', [\App\Http\Controllers\Admin\SettingsController::class, 'saveAppearance'])->name('appearance');
        Route::post('/notifications', [\App\Http\Controllers\Admin\SettingsController::class, 'saveNotifications'])->name('notifications');
        Route::post('/license', [\App\Http\Controllers\Admin\SettingsController::class, 'saveLicense'])->name('license');
    });

    // ============================================================
    // TEAM MANAGEMENT
    // ============================================================
    Route::prefix('team')->name('team.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\TeamController::class, 'index'])->name('index');
        Route::get('/invite', [\App\Http\Controllers\Admin\TeamController::class, 'invite'])->name('invite');
        Route::post('/invite', [\App\Http\Controllers\Admin\TeamController::class, 'sendInvite'])->name('send-invite');
        Route::delete('/{user}', [\App\Http\Controllers\Admin\TeamController::class, 'remove'])->name('remove');
        Route::put('/{user}/role', [\App\Http\Controllers\Admin\TeamController::class, 'updateRole'])->name('update-role');
    });
});

// ============================================================
// REAL-TIME EVENTS (Public - for WebSocket)
// ============================================================
Route::post('/broadcasting/auth', function () {
    // Laravel Echo authentication for private channels
    return response()->json(['success' => true]);
});

// ============================================================
// HEALTH CHECK
// ============================================================
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'version' => '1.0.0',
    ]);
});

// ============================================================
// REVERB BROADCASTING (For real-time features)
// ============================================================
Route::match(['get', 'post'], '/broadcasting/connections', function () {
    // Laravel Reverb connection endpoint
    return response()->json(['status' => 'ok']);
});