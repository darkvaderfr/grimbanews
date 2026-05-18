<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * S-ADS-10 (Vader 2026-05-18) — sales-team notification.
 *
 * Dispatched from `AdvertiserLeadController@store` after a lead
 * persists, so the sales mailbox receives a structured summary
 * with a one-click link to the admin detail view.
 *
 * Queued so the POST doesn't wait on the mail driver — if mail is
 * misconfigured or upstream is slow, the form still returns
 * promptly and the lead is captured.
 */
class GrimbaAdvertiserLeadNotification extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $leadId,
        public string $leadEmail,
        public ?string $leadCompany,
        public ?string $leadBudgetBand,
        public ?string $leadGoals,
        public ?string $leadSourceSlot,
        public ?string $leadLocale,
        public string $detailUrl,
    ) {
    }

    public function build(): self
    {
        $companyShort = $this->leadCompany ? ' — ' . \Illuminate\Support\Str::limit($this->leadCompany, 40, '') : '';
        $bandShort = $this->leadBudgetBand ? ' (' . $this->leadBudgetBand . ')' : '';

        return $this
            ->subject('Nouveau lead annonceur #' . $this->leadId . $companyShort . $bandShort)
            ->view('emails.advertiser-lead-notification');
    }
}
