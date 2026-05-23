<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Collection;
use Illuminate\Queue\SerializesModels;

class GrimbaVaultDigestMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public object $member,
        public Collection $posts,
    ) {
    }

    public function build(): self
    {
        // Wave ZZZZZZZZZZ (Vader 2026-05-23) — subject wrapped in
        // __() so a future per-recipient `app()->setLocale()` call
        // produces locale-aware subjects without touching this
        // class. Today's default locale (FR) preserves current
        // behavior. EN translation in lang/en.json.
        return $this
            ->subject(__('Votre digest coffre GrimbaNews'))
            ->view('emails.vault-digest');
    }
}
