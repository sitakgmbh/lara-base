<?php

namespace Sitakgmbh\LaraBase\Mail;

class TestMail extends BaseMailable
{
    public function __construct()
    {
        $this->mailTitle  = 'Test E-Mail';
        $this->mailBody   = '<p>Dies ist eine Test-E-Mail von <strong>' . config('app.name') . '</strong>.</p><p>Der E-Mail-Versand funktioniert korrekt.</p>';
        $this->mailFooter = '';
    }

    protected function getSubject(): string
    {
        return 'Test E-Mail – ' . config('app.name');
    }
}