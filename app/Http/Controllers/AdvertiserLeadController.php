<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * S-ADS-03 (Vader 2026-05-18) — sponsor lead capture endpoint.
 *
 * /advertise's CTA used to be a `mailto:` link. Sales leads either
 * never sent (no mail client) or vanished into an inbox with no
 * record. This controller persists them to `grimba_advertiser_leads`
 * (S-ADS-02), enforces honeypot + rate limit, and forwards to the
 * sales mailbox in a follow-up sprint.
 *
 * Contract:
 * - POST /advertise/leads (web-mode, CSRF protected by middleware)
 * - Accepts JSON XHR or classic form post
 * - Honeypot field: any non-empty `_hp` field aborts silently
 *   (returns success-shape so bots can't enumerate the trap)
 * - Rate-limited: 5 submissions per IP per 10 minutes
 * - Validation: email required + valid; everything else optional
 */
class AdvertiserLeadController extends Controller
{
    private const BUDGET_BANDS = ['under-1k', '1k-5k', '5k-25k', '25k-plus', 'unknown'];

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $ip = (string) $request->ip();
        $rateKey = 'advertiser-lead:' . sha1($ip);
        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            return $this->respond($request, false, __('Too many submissions. Please try again in a few minutes.'));
        }
        RateLimiter::hit($rateKey, 600);

        // Honeypot — bots fill every field including hidden ones.
        // Real users never touch `_hp`. If it's populated, we record
        // nothing and pretend success.
        if (trim((string) $request->input('_hp', '')) !== '') {
            Log::info('[AdvertiserLead] honeypot tripped', ['ip' => $ip]);
            return $this->respond($request, true, __('Thanks — we will be in touch.'));
        }

        $data = $request->validate([
            'email'       => ['required', 'email:rfc', 'max:191'],
            'company'     => ['nullable', 'string', 'max:191'],
            'budget_band' => ['nullable', 'string', 'max:32', 'in:' . implode(',', self::BUDGET_BANDS)],
            'goals'       => ['nullable', 'string', 'max:2000'],
            'source_slot' => ['nullable', 'string', 'max:64'],
            'source_pack_tier' => ['nullable', 'string', 'max:64'],
        ]);

        $locale = $request->cookie('grimba_lang') ?: app()->getLocale();
        $locale = is_string($locale) ? substr($locale, 0, 5) : null;

        $leadId = DB::table('grimba_advertiser_leads')->insertGetId([
            'email'           => strtolower(trim($data['email'])),
            'company'         => $data['company'] ?? null,
            'budget_band'     => $data['budget_band'] ?? null,
            'goals'           => $data['goals'] ?? null,
            'source_referrer' => substr((string) $request->headers->get('referer', ''), 0, 512) ?: null,
            'source_slot'     => $data['source_slot'] ?? null,
            'source_pack_tier' => $data['source_pack_tier'] ?? null,
            'locale'          => $locale,
            'ip'              => $ip,
            'status'          => 'new',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // S-ADS-10 — sales-team handoff. Queued so a slow/missing
        // mail driver doesn't block the form response. Honors a
        // `grimba_advertiser_leads_sales_mailbox` setting key; falls
        // back to a sensible env-derived default. Silent no-op when
        // no recipient is configured (so dev environments don't fail
        // loud on every form submit).
        try {
            $recipient = self::salesMailbox();
            if ($recipient !== '') {
                $detailUrl = url('/admin/grimba/advertiser-leads/' . $leadId);
                \Illuminate\Support\Facades\Mail::to($recipient)->queue(
                    new \App\Mail\GrimbaAdvertiserLeadNotification(
                        leadId: $leadId,
                        leadEmail: strtolower(trim($data['email'])),
                        leadCompany: $data['company'] ?? null,
                        leadBudgetBand: $data['budget_band'] ?? null,
                        leadGoals: $data['goals'] ?? null,
                        leadSourceSlot: $data['source_slot'] ?? null,
                        leadLocale: $locale,
                        detailUrl: $detailUrl,
                        leadSourcePackTier: $data['source_pack_tier'] ?? null,
                    )
                );
            }
        } catch (\Throwable $e) {
            // Lead persisted; the mail-dispatch failure is logged
            // and the form still returns success.
            Log::warning('[AdvertiserLead] mail dispatch failed', [
                'lead_id' => $leadId,
                'error' => $e->getMessage(),
            ]);
        }

        return $this->respond($request, true, __('Thanks — we will be in touch within one business day.'));
    }

    /**
     * Sales-team mailbox address. Reads in this order:
     *   1. Botble `setting('grimba_advertiser_leads_sales_mailbox')` —
     *      admin-form-editable in a future S-ADS sprint.
     *   2. `env('GRIMBA_ADS_SALES_MAILBOX')` — dev/staging override.
     *   3. Empty string → silent no-op (dispatch skipped).
     */
    private static function salesMailbox(): string
    {
        $fromSetting = function_exists('setting')
            ? trim((string) setting('grimba_advertiser_leads_sales_mailbox', ''))
            : '';
        if ($fromSetting !== '' && filter_var($fromSetting, FILTER_VALIDATE_EMAIL)) {
            return $fromSetting;
        }
        $fromEnv = trim((string) env('GRIMBA_ADS_SALES_MAILBOX', ''));
        return filter_var($fromEnv, FILTER_VALIDATE_EMAIL) ? $fromEnv : '';
    }

    private function respond(Request $request, bool $ok, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok'      => $ok,
                'message' => $message,
            ], $ok ? 200 : 429);
        }

        return back()->with(
            $ok ? 'advertiser_lead_success' : 'advertiser_lead_error',
            $message
        );
    }
}
