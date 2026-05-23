<?php

namespace Sitakgmbh\LaraBase\Support;

use Illuminate\Support\Facades\Mail;
use Sitakgmbh\LaraBase\Facades\LaraLog;

class LaraMail
{
    public static function send($mailable, string|array $to, string|array $cc = [], string|array $bcc = []): bool
    {
        $testMode      = config('lara-base.mail.test_mode', false);
        $mailClassFull = is_object($mailable) ? get_class($mailable) : (string) $mailable;
        $mailClassName = class_basename($mailClassFull);

        $toList  = (array) $to;
        $ccList  = (array) $cc;
        $bccList = (array) $bcc;

        $user     = auth()->user();
        $username = $user?->username ?? 'system';
        $fullname = trim(($user?->firstname ?? '') . ' ' . ($user?->lastname ?? ''));

        if (method_exists($mailable, 'build')) {
            try {
                $mailable->build();
            } catch (\Throwable) {
                // ignorieren
            }
        }

        $subject = $mailable->subject ?? null;

        $context = [
            'to'        => $toList,
            'cc'        => $ccList,
            'bcc'       => $bccList,
            'mail'      => $mailClassFull,
            'subject'   => $subject,
            'username'  => $username,
            'fullname'  => $fullname,
        ];

        if ($testMode) {
            LaraLog::info(
                "Simulation Versand {$mailClassName} an " . implode(', ', $toList) . " durch {$username}",
                $context
            );
            return true;
        }

        try {
            $mailer = Mail::to($toList);
            if (!empty($ccList))  $mailer->cc($ccList);
            if (!empty($bccList)) $mailer->bcc($bccList);
            $mailer->send($mailable);

            LaraLog::db('email', 'info',
                "Versand {$mailClassName} an " . implode(', ', $toList) . " durch {$username}",
                $context + [
                    'ip'        => request()->ip(),
                    'userAgent' => request()->userAgent(),
                ]
            );

            return true;

        } catch (\Throwable $e) {
            LaraLog::db('email', 'error',
                "Fehler beim Mailversand ({$mailClassName}) durch {$username}: {$e->getMessage()}",
                $context + [
                    'error'     => $e->getMessage(),
                    'ip'        => request()->ip(),
                    'userAgent' => request()->userAgent(),
                ]
            );

            return false;
        }
    }
}