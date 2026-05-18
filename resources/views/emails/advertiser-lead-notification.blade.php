<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Nouveau lead annonceur #{{ $leadId }}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f6f1e8; padding: 24px; color: #14110d;">
<div style="max-width: 640px; margin: 0 auto; background: #fffaf1; border-radius: 12px; padding: 28px 28px 22px; border: 1px solid rgba(192, 57, 43, 0.18);">
    <p style="margin: 0 0 4px; font-size: 11px; letter-spacing: 0.18em; text-transform: uppercase; color: rgba(20, 17, 13, 0.62); font-weight: 800;">GrimbaNews · Sales pipeline</p>
    <h1 style="margin: 0 0 16px; font-family: 'Fraunces', Georgia, serif; font-weight: 800; font-size: 22px; color: #14110d;">
        Nouveau lead annonceur #{{ $leadId }}
    </h1>

    <table cellpadding="0" cellspacing="0" style="width: 100%; font-size: 14px; line-height: 1.5; color: #14110d; border-collapse: collapse;">
        <tr>
            <td style="padding: 6px 0; width: 130px; opacity: 0.62;">Email</td>
            <td style="padding: 6px 0;">
                <a href="mailto:{{ $leadEmail }}" style="color: #c0392b; text-decoration: underline;">{{ $leadEmail }}</a>
            </td>
        </tr>
        @if($leadCompany)
            <tr>
                <td style="padding: 6px 0; opacity: 0.62;">Société</td>
                <td style="padding: 6px 0; font-weight: 600;">{{ $leadCompany }}</td>
            </tr>
        @endif
        @if($leadBudgetBand)
            <tr>
                <td style="padding: 6px 0; opacity: 0.62;">Budget</td>
                <td style="padding: 6px 0;">
                    <span style="display: inline-block; padding: 2px 10px; border-radius: 999px; background: rgba(192, 57, 43, 0.14); color: #c0392b; font-weight: 700; font-size: 12px;">
                        {{ $leadBudgetBand }}
                    </span>
                </td>
            </tr>
        @endif
        @if($leadSourceSlot)
            <tr>
                <td style="padding: 6px 0; opacity: 0.62;">Slot d'origine</td>
                <td style="padding: 6px 0; font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 12px;">{{ $leadSourceSlot }}</td>
            </tr>
        @endif
        @if($leadLocale)
            <tr>
                <td style="padding: 6px 0; opacity: 0.62;">Locale</td>
                <td style="padding: 6px 0;">{{ $leadLocale }}</td>
            </tr>
        @endif
    </table>

    @if($leadGoals)
        <div style="margin-top: 16px; padding: 14px 16px; background: rgba(26, 23, 19, 0.04); border-left: 3px solid #c0392b; border-radius: 6px;">
            <div style="font-size: 11px; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase; color: rgba(20, 17, 13, 0.62); margin-bottom: 8px;">Objectifs</div>
            <div style="white-space: pre-wrap; font-size: 14px; line-height: 1.55;">{{ $leadGoals }}</div>
        </div>
    @endif

    <div style="margin-top: 22px; text-align: center;">
        <a href="{{ $detailUrl }}" style="display: inline-block; padding: 12px 24px; background: #14110d; color: #fffaf1; text-decoration: none; border-radius: 999px; font-weight: 800; font-size: 14px; letter-spacing: 0.02em;">
            Voir dans le tableau de bord →
        </a>
    </div>

    <p style="margin: 26px 0 0; font-size: 11px; color: rgba(20, 17, 13, 0.5); text-align: center;">
        Lead reçu via /advertise. Réponse attendue sous 1 jour ouvré.
    </p>
</div>
</body>
</html>
