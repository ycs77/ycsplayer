<?php

namespace App\PasswordlessLogin\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendPasswordlessLoginLink extends Notification
{
    /**
     * The password-less login URL.
     */
    public string $url;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('登入 '.config('app.name'))
            ->line('您收到這封 E-mail 是因為我們收到了您的登入請求，請點擊以下按鈕來登入。')
            ->action('登入', $this->url)
            ->line(sprintf('這個連結將於 %d 分鐘後失效。', (int) config('laravel-passwordless-login.login_route_expires')))
            ->line('如果您沒有執行登入此網站的操作，可以忽略此信件。');
    }
}
