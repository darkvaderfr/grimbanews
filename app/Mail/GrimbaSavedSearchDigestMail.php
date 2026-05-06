<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Collection;
use Illuminate\Queue\SerializesModels;

class GrimbaSavedSearchDigestMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public object $member,
        public Collection $digests,
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject('Vos alertes recherche GrimbaNews')
            ->view('emails.saved-search-digest');
    }
}
