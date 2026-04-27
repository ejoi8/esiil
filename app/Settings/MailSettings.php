<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class MailSettings extends Settings
{
    public string $mailer = 'log';

    public ?string $scheme = null;

    public string $host = '127.0.0.1';

    public int $port = 2525;

    public ?string $username = null;

    public ?string $password = null;

    public string $from_address = 'hello@example.com';

    public string $from_name = 'Laravel';

    public static function encrypted(): array
    {
        return ['password'];
    }

    public static function group(): string
    {
        return 'mail';
    }
}
