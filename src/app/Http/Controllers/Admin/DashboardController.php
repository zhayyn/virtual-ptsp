<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Channel;
use App\Models\AiChatLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

/**
 * Virtual PTSP - Dashboard Controller
 * Built with ❤️ by zhayyn (+6281317361689)
 */
class DashboardController extends Controller
{
    /**
     * Display dashboard
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $tenant = $user->tenant;

        // Get statistics
        $stats = [
            'total_conversations' => $tenant
                ? Conversation::where('tenant_id', $tenant->id)->count()
                : Conversation::count(),
            'open_conversations' => $tenant
                ? Conversation::where('tenant_id', $tenant->id)->where('status', 'open')->count()
                : Conversation::where('status', 'open')->count(),
            'total_channels' => $tenant
                ? Channel::where('tenant_id', $tenant->id)->where('is_active', true)->count()
                : Channel::where('is_active', true)->count(),
            'today_messages' => $tenant
                ? Conversation::where('tenant_id', $tenant->id)
                    ->whereDate('last_message_at', today())
                    ->count()
                : Conversation::whereDate('last_message_at', today())->count(),
        ];

        // Get recent conversations
        $recentConversations = $tenant
            ? Conversation::where('tenant_id', $tenant->id)
                ->with('channel')
                ->orderBy('last_message_at', 'desc')
                ->limit(10)
                ->get()
            : Conversation::with('channel')
                ->orderBy('last_message_at', 'desc')
                ->limit(10)
                ->get();

        // Get AI stats
        $aiStats = [
            'total_ai_chats' => $tenant
                ? AiChatLog::where('tenant_id', $tenant->id)->count()
                : AiChatLog::count(),
            'ai_today' => $tenant
                ? AiChatLog::where('tenant_id', $tenant->id)
                    ->whereDate('created_at', today())
                    ->count()
                : AiChatLog::whereDate('created_at', today())->count(),
            'avg_response_time' => $tenant
                ? AiChatLog::where('tenant_id', $tenant->id)->avg('latency_ms') ?? 0
                : AiChatLog::avg('latency_ms') ?? 0,
        ];

        // Get message trend (last 7 days)
        $messageTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = $tenant
                ? Conversation::where('tenant_id', $tenant->id)
                    ->whereDate('last_message_at', $date)
                    ->count()
                : Conversation::whereDate('last_message_at', $date)->count();
            $messageTrend[] = [
                'date' => now()->subDays($i)->format('d M'),
                'count' => $count,
            ];
        }

        return view('admin.dashboard', compact(
            'stats',
            'recentConversations',
            'aiStats',
            'messageTrend'
        ));
    }
}