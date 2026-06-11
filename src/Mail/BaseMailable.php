<?php

namespace Sitakgmbh\LaraBase\Mail;

use Illuminate\Mail\Mailable;

abstract class BaseMailable extends Mailable
{
    public string $mailTitle  = '';
    public string $mailBody   = '';
    public string $mailFooter = '';

    public function build(): static
    {
        return $this->view('lara-base::layouts.mail')
                    ->subject($this->getSubject());
    }

    abstract protected function getSubject(): string;
}