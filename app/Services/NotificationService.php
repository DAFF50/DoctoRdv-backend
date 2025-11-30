<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public static function create(int $utilisateur_id, string $type,
                                  string $title, string $message, ?array $details = null, string $icon = 'info'){
        return Notification::create([
            'utilisateur_id' => $utilisateur_id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'details' => $details,
            'icon' => $icon,
        ]);
    }
}
