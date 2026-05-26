<?php

namespace app\services;

use Yii;

class EmailService
{
    /**
     * Отправка письма подтверждения email
     */
    public function sendVerificationEmail(string $email, string $token): bool
    {
        Yii::info("=== Начало процесса верификации для email: {$email} ===", __METHOD__);

        $frontendUrl = Yii::$app->params['frontendUrl'] ?? '';
        $verificationUrl = $frontendUrl . '/verify-email?token=' . urlencode($token);

        Yii::info("Сгенерирована ссылка верификации: {$verificationUrl}", __METHOD__);

        $subject = 'Подтверждение email на Стиль Фит';

        Yii::info("Генерация HTML-шаблона письма...", __METHOD__);
        $body = $this->getVerificationEmailTemplate($verificationUrl);

        Yii::info("Передача управления в метод отправки sendEmail()...", __METHOD__);
        $result = $this->sendEmail($email, $subject, $body);

        if ($result) {
            Yii::info("=== Процесс верификации для {$email} успешно завершен ===", __METHOD__);
        } else {
            Yii::warning("=== Процесс верификации для {$email} завершился неудачей ===", __METHOD__);
        }

        return $result;
    }

    /**
     * Отправка email через SMTP.bz HTTP API
     */
    private function sendEmail(string $to, string $subject, string $htmlBody): bool
    {
        try {
            Yii::info("Отправка письма через SMTP на {$to}...", __METHOD__);
            $startTime = microtime(true);

            // Используем стандартный бесплатный компонент Yii2 Mailer
            $message = Yii::$app->mailer->compose()
                ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                ->setTo($to)
                ->setSubject($subject)
                ->setHtmlBody($htmlBody);

            $result = $message->send();
            $totalTime = round(microtime(true) - $startTime, 4);

            if ($result) {
                Yii::info("Письмо успешно отправлено через SMTP за {$totalTime} сек.", __METHOD__);
                return true;
            }

            Yii::error("Ошибка отправки: Yii Mailer вернул false.", __METHOD__);
            return false;
        } catch (\Throwable $e) {
            Yii::error('Фатальное исключение в методе sendEmail: ' . $e->getMessage() . "\n" . $e->getTraceAsString(), __METHOD__);
            return false;
        }
    }

    /**
     * Шаблон письма подтверждения
     */
    private function getVerificationEmailTemplate($verificationUrl)
    {
        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <img src="https://stil-fit.ru/static/logo.svg" alt="Стиль Фит" style="height: 48px; margin-bottom: 20px;" />
        <h2>Подтверждение email</h2>
        <p>Спасибо за регистрацию на Стиль Фит!</p>
        <p>Для завершения регистрации подтвердите ваш email адрес, нажав на кнопку ниже:</p>
        <a href="' . $verificationUrl . '" style="display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff !important; text-decoration: none; border-radius: 8px; margin: 20px 0;">Подтвердить email</a>
        <p>Или скопируйте эту ссылку в браузер:</p>
        <p style="word-break: break-all; color: #667eea;">' . $verificationUrl . '</p>
        <p>Ссылка действительна в течение 24 часов.</p>
        <div style="margin-top: 30px; font-size: 12px; color: #666;">
            <p>Если вы не регистрировались на Стиль Фит, просто проигнорируйте это письмо.</p>
        </div>
    </div>
</body>
</html>
        ';
    }

    /**
     * Проверка на временный email домен
     */
    public function isDisposableEmail($email)
    {
        $domain = substr(strrchr($email, "@"), 1);

        // Список популярных временных email доменов
        $disposableDomains = [
            '10minutemail.com',
            'guerrillamail.com',
            'mailinator.com',
            'tempmail.com',
            'throwaway.email',
            'temp-mail.org',
            'getnada.com',
            'maildrop.cc',
            'trashmail.com',
            'yopmail.com',
            'fakeinbox.com',
            'sharklasers.com',
            'grr.la',
            'guerrillamail.biz',
            'guerrillamail.de',
            'spam4.me',
            'tmpeml.info',
            'emailondeck.com',
            'yopmail.fr',
            'yopmail.net',
            'cool.fr.nf',
            'jetable.fr.nf',
            'nospam.ze.tc',
            'nomail.xl.cx',
            'mega.zik.dj',
            'speed.1s.fr',
            'courriel.fr.nf',
            'moncourrier.fr.nf',
            'monemail.fr.nf',
            'monmail.fr.nf',
            'guerrillamail.net',
            'guerrillamail.org',
            'guerrillamailblock.com',
            'tempemail.net',
            'spamfree24.org',
            'spamgourmet.com',
            'unmail.ru',
            'mailexpire.com',
            'msgos.com',
            'vmani.com',
            'trbvm.com',
            'spam.la',
            'grandmamail.com',
            'zetmail.com',
            '6paq.com',
            'landmail.co',
            'lastmail.co',
            'divermail.com',
            'flurred.com',
            'yomail.info',
            '10mail.org',
            'tryalert.com',
            'dropmail.me',
        ];

        return in_array(strtolower($domain), $disposableDomains);
    }
}
