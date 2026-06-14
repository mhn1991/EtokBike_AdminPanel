<?php

namespace App\Support\Mobile;

use App\Models\CustomerMessage;
use App\Models\MessageDepartment;
use Illuminate\Support\Facades\Schema;

class MessagesScreenBuilder
{
    /**
     * @return array<string, mixed>
     */
    public static function build(array $fallback, ?\App\Models\User $user = null): array
    {
        if (! static::canUseDatabase()) {
            return $fallback;
        }

        $departments = MessageDepartment::query()
            ->where('is_active', true)
            ->with(['messages' => fn ($query) => $query
                ->when($user, fn ($query) => $query->where('user_id', $user->id))
                ->latest()])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        if ($departments->isEmpty()) {
            return $fallback;
        }

        $unreadCount = $departments->sum(fn (MessageDepartment $department): int => $department->messages->where('is_unread', true)->count());
        $screen = $fallback;
        $screen['version'] = static::version($fallback);

        foreach ($screen['sections'] as &$section) {
            if (($section['type'] ?? null) !== 'message_center') {
                continue;
            }

            $section['data']['defaultDepartment'] = $departments->first()->slug;
            $section['data']['summary']['title'] = number_format($unreadCount).' پیام خوانده نشده';
            $section['data']['departments'] = $departments
                ->map(fn (MessageDepartment $department): array => [
                    'id' => $department->slug,
                    'title' => $department->title,
                    'subtitle' => $department->subtitle,
                    'unreadLabel' => ($count = $department->messages->where('is_unread', true)->count()) > 0 ? number_format($count).' پیام جدید' : '',
                    'threadTitle' => $department->thread_title,
                    'composerTitle' => $department->composer_title,
                    'placeholder' => $department->placeholder,
                    'sendLabel' => $department->send_label,
                    'messages' => $department->messages
                        ->map(fn ($message): array => [
                            'sender' => $message->sender,
                            'label' => $message->label,
                            'text' => $message->text,
                            'time' => $message->time_label,
                        ])
                        ->values()
                        ->all(),
                ])
                ->values()
                ->all();
        }

        return $screen;
    }

    public static function version(array $fallback): int
    {
        if (! static::canUseDatabase()) {
            return (int) ($fallback['version'] ?? 1);
        }

        $timestamp = collect([
            MessageDepartment::query()->max('updated_at'),
            CustomerMessage::query()->max('updated_at'),
        ])->filter()->map(fn ($value): int => strtotime((string) $value) ?: 0)->max();

        return max((int) ($fallback['version'] ?? 1), $timestamp ?: 0);
    }

    private static function canUseDatabase(): bool
    {
        return Schema::hasTable('message_departments')
            && Schema::hasTable('customer_messages');
    }
}
