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
        return $this
            ->subject('Votre digest coffre GrimbaNews')
            ->view('emails.vault-digest');
    }
}
