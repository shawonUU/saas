<?php
namespace App\Http\Controllers\Public\Business\Admin\Settings\Business\Notifications;

use App\Http\Controllers\Controller;
use App\Models\BusinessUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    private function user(Request $request): BusinessUser
    {
        $business = $request->attributes->get('current_business');
        $user = $request->user('business_admin');
        abort_unless($business && $user instanceof BusinessUser && (int) $user->business_id === (int) $business->id, 403);
        return $user;
    }

    private function safeUrl(Request $request, mixed $url): ?string
    {
        if (! is_string($url) || trim($url) === '') return null;
        $url = trim($url);
        if (str_starts_with($url, '/') && ! str_starts_with($url, '//') && ! str_contains($url, '\\')) return $url;
        $host = parse_url($url, PHP_URL_HOST);
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (is_string($host) && strcasecmp($host, $request->getHost()) === 0 && in_array(strtolower((string)$scheme), ['http', 'https'], true)) return $url;
        return null;
    }

    public function latest(Request $request): JsonResponse
    {
        $user = $this->user($request);
        if (! Schema::hasTable('notifications')) {
            return response()->json(['items' => [], 'unread' => 0, 'setup_required' => true]);
        }
        $notifications = $user->notifications()->latest()->limit(12)->get();
        $items = $notifications->map(function ($notice) use ($request) {
            $data = (array) $notice->data;
            $title = (string)($data[session('portal_lang') === 'bn' ? 'title_bn' : 'title'] ?? $data['title'] ?? t('Notification', 'নোটিফিকেশন'));
            $message = (string)($data[session('portal_lang') === 'bn' ? 'message_bn' : 'message'] ?? $data['message'] ?? $data['body'] ?? '');
            return [
                'id' => $notice->id,
                'title' => Str::limit($title, 110),
                'message' => Str::limit($message, 250),
                'time' => $notice->created_at?->diffForHumans() ?? '',
                'unread' => $notice->read_at === null,
                'url' => $this->safeUrl($request, $data['url'] ?? $data['action_url'] ?? null),
                'read_url' => route('business.admin.settings.business.notifications.read', [
                    'business' => $request->route('business'), 'notification' => $notice->id,
                ]),
            ];
        })->values();

        return response()->json(['items' => $items, 'unread' => $user->unreadNotifications()->count()]);
    }

    public function read(Request $request, string $business, string $notification): JsonResponse
    {
        $user = $this->user($request);
        abort_unless(Schema::hasTable('notifications'), 404);
        $notice = $user->notifications()->whereKey($notification)->firstOrFail();
        $notice->markAsRead();
        return response()->json(['ok' => true]);
    }

    public function readAll(Request $request): JsonResponse
    {
        $user = $this->user($request);
        abort_unless(Schema::hasTable('notifications'), 404);
        $user->unreadNotifications()->update(['read_at' => now()]);
        return response()->json(['ok' => true]);
    }
}
