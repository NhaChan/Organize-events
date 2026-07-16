<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('admin.password.reset', ['token' => $this->token, 'email' => $notifiable->email]);

        return (new MailMessage)
            ->subject('Đặt lại mật khẩu quản trị Minh Triệu Party')
            ->greeting('Xin chào '.$notifiable->full_name.',')
            ->line('Hệ thống nhận được yêu cầu đặt lại mật khẩu cho tài khoản quản trị của bạn.')
            ->action('Đặt lại mật khẩu', $url)
            ->line('Liên kết này có hiệu lực trong 30 phút và chỉ sử dụng được một lần.')
            ->line('Nếu bạn không yêu cầu đổi mật khẩu, hãy bỏ qua email này và kiểm tra lại bảo mật tài khoản.');
    }
}
