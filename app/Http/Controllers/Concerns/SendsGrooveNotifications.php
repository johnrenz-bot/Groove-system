<?php

namespace App\Http\Controllers\Concerns;

use App\Notifications\GrooveNotification;
use Illuminate\Support\Facades\Notification;

trait SendsGrooveNotifications
{
    /**
     * Notify one or many notifiables (single model, array, or Collection).
     * Works for both single user and iterable recipients.
     */
    protected function grooveNotify(
        $notifiable,               // single model OR iterable
        string $title,
        string $message,
        ?string $url = null
    ): void {
        Notification::send($notifiable, new GrooveNotification($title, $message, $url));
    }

    /**
     * Quick helper that builds a URL from a named route, then sends.
     */
    protected function grooveNotifyWithRoute(
        $notifiable,               // single model OR iterable
        string $title,
        string $message,
        string $routeName,
        array $routeParams = []
    ): void {
        $url = route($routeName, $routeParams);
        Notification::send($notifiable, new GrooveNotification($title, $message, $url));
    }
}
