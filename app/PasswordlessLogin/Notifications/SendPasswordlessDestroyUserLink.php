<?php

namespace App\PasswordlessLogin\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendPasswordlessDestroyUserLink extends Notification
{
    /**
     * The password-less destroy user URL.
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
            ->error()
            ->subject('刪除帳號')
            ->line('您收到這封 E-mail 是因為我們收到了您的刪除帳號請求，請點擊以下按鈕來刪除帳號。')
            ->action('刪除帳號', $this->url)
            ->line('請注意！此操作不可恢復，刪除帳號後所有帳號資料將被刪除！請謹慎操作。')
            ->line(sprintf('這個連結將於 %d 分鐘後失效。', (int) config('laravel-passwordless-login.login_route_expires')))
            ->line('如果您沒有執行刪除帳號的操作，可以忽略此信件。');
    }
}
