<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Восстановление пароля — ' . config('app.name'))
            ->greeting('Здравствуйте!')
            ->line('Вы получили это письмо, потому что для вашей учётной записи запрошено восстановление пароля.')
            ->action('Сбросить пароль', $url)
            ->line('Ссылка действительна ' . config('auth.passwords.users.expire') . ' минут.')
            ->line('Если вы не запрашивали сброс пароля, просто проигнорируйте это письмо.')
            ->salutation('С уважением, команда ' . config('app.name'));
    }
}
