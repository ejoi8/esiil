<?php

namespace App\Services\Mail;

use App\Settings\MailSettings;
use Illuminate\Support\Facades\Mail;

class MailSettingsConfigurator
{
    public function apply(MailSettings $settings, bool $forgetResolvedMailers = false): void
    {
        config([
            'mail.default' => $settings->mailer,
            'mail.mailers.smtp.url' => null,
            'mail.mailers.smtp.scheme' => $settings->scheme,
            'mail.mailers.smtp.host' => $settings->host,
            'mail.mailers.smtp.port' => $settings->port,
            'mail.mailers.smtp.username' => $settings->username,
            'mail.mailers.smtp.password' => $settings->password,
            'mail.from.address' => $settings->from_address,
            'mail.from.name' => $settings->from_name,
        ]);

        if ($forgetResolvedMailers) {
            Mail::forgetMailers();
        }
    }
}
