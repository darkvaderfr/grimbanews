<?php

namespace App\Ground;

use Illuminate\Support\Str;

/**
 * 8-category ownership classification for sources.
 *
 * Mirrors Ground.news:
 *   conglomerate | private_equity | individual | government |
 *   telecom | corporation | independent | other
 *
 * Inputs we have today:
 *   - news_sources.ownership_type (free-form text, sometimes the slug)
 *   - news_sources.owner_name (free-form parent name)
 *
 * Strategy: prefer ownership_type when it's already a known slug;
 * otherwise infer from owner_name keywords + a small static map of
 * well-known francophone parents (Bouygues, Bolloré/Vivendi, Niel, Drahi, etc.).
 */
class Ownership
{
    /**
     * @var array<string, string> map of normalized owner-name keyword → category slug.
     * Keys are matched case-insensitively as substrings.
     */
    /*
     * Order matters: more-specific keys MUST come before less-specific
     * substrings of themselves. We do a substring scan in insertion order
     * and the first hit wins.
     */
    private const PARENT_MAP = [
        // Specific persons (must precede their group/company forms below).
        // Order is "first match wins" via str_contains, so the more
        // specific keys go first. S319 audit caught 'bouygues' alone
        // being miscategorized as individual — the family is, the group
        // is a conglomerate. Fix: put Martin/Olivier Bouygues here, then
        // map the bare 'bouygues' to conglomerate below.
        'vincent bolloré' => 'individual',
        'bernard arnault' => 'individual',
        'xavier niel' => 'individual',
        'patrick drahi' => 'individual',
        'jeff bezos' => 'individual',
        'marc benioff' => 'individual',
        'martin bouygues' => 'individual',
        'olivier bouygues' => 'individual',
        'serge dassault' => 'individual',
        'marc ladreit de lacharrière' => 'individual',
        'arnaud lagardère' => 'individual',

        // Multi-token specific subsidiaries (must precede the bare
        // single-token group form below, otherwise "Bouygues Telecom"
        // gets caught by the "bouygues" → conglomerate rule).
        'bouygues telecom' => 'telecom',
        'tf1 group' => 'conglomerate',

        // French groups / parents (single-token forms — any name reaching
        // these has not matched a person or subsidiary above).
        'bouygues' => 'conglomerate',
        'bolloré' => 'conglomerate',
        'vivendi' => 'conglomerate',
        'lagardère' => 'conglomerate',
        'arnault' => 'individual',
        'lvmh' => 'corporation',
        'pinault' => 'individual',
        'kretinsky' => 'individual',
        'dassault' => 'conglomerate',
        'niel' => 'individual',
        'drahi' => 'individual',
        'altice' => 'telecom',
        'free' => 'telecom',
        'iliad' => 'telecom',
        'orange' => 'telecom',
        'sfr' => 'telecom',

        // Public broadcasters / government
        'france télévisions' => 'government',
        'france-télévisions' => 'government',
        'radio france' => 'government',
        'rfi' => 'government',
        'france 24' => 'government',
        'france24' => 'government',
        'tv5monde' => 'government',
        'arte' => 'government',
        'cgtn' => 'government',
        'rt ' => 'government',
        'al jazeera' => 'government',
        'aj+' => 'government',

        // International conglomerates
        'news corp' => 'conglomerate',
        'newscorp' => 'conglomerate',
        'comcast' => 'telecom',
        'nbcuniversal' => 'conglomerate',
        'paramount' => 'conglomerate',
        'fox corporation' => 'conglomerate',
        'walt disney' => 'conglomerate',
        'warner bros' => 'conglomerate',
        'condé nast' => 'corporation',
        'meredith' => 'conglomerate',
        'gannett' => 'private_equity',
        'tronc' => 'private_equity',
        'mediahuis' => 'conglomerate',

        // Public / state foreign
        'bbc' => 'government',
        'cbc' => 'government',
        'voa' => 'government',
        'voice of america' => 'government',
        'dw' => 'government',
        'deutsche welle' => 'government',

        // Tech corporations
        'amazon' => 'corporation',
        'salesforce' => 'corporation',
    ];

    public static function category(?string $ownershipType, ?string $ownerName = null): string
    {
        $type = self::canon($ownershipType);
        if (in_array($type, self::ALL, true)) {
            return $type;
        }

        $owner = strtolower((string) $ownerName);
        if ($owner !== '') {
            foreach (self::PARENT_MAP as $needle => $cat) {
                if (str_contains($owner, $needle)) {
                    return $cat;
                }
            }
        }

        // Fall back to whatever signal the free-form ownership_type gave us.
        return self::loosely($ownershipType);
    }

    private const ALL = [
        'conglomerate', 'private_equity', 'individual', 'government',
        'telecom', 'corporation', 'independent', 'other',
    ];

    private static function canon(?string $ownership): string
    {
        return Str::of((string) $ownership)
            ->lower()
            ->replace(['-', ' '], '_')
            ->squish()
            ->toString();
    }

    private static function loosely(?string $ownership): string
    {
        $n = self::canon($ownership);

        return match (true) {
            $n === '' || $n === 'unknown' => 'other',
            str_contains($n, 'government') || str_contains($n, 'state') || str_contains($n, 'public') => 'government',
            str_contains($n, 'private_equity') || str_contains($n, 'private equity') || str_contains($n, 'equity') || str_contains($n, 'fund') => 'private_equity',
            str_contains($n, 'individual') || str_contains($n, 'family') || str_contains($n, 'personal') => 'individual',
            str_contains($n, 'telecom') || str_contains($n, 'wireless') || str_contains($n, 'broadband') => 'telecom',
            str_contains($n, 'conglomerate') || str_contains($n, 'media_group') || str_contains($n, 'holding') => 'conglomerate',
            str_contains($n, 'independent') => 'independent',
            str_contains($n, 'corporation') || str_contains($n, 'corporate') || str_contains($n, 'company') => 'corporation',
            default => 'other',
        };
    }

    public static function label(string $cat): string
    {
        return match ($cat) {
            'conglomerate' => __('Conglomérat média'),
            'private_equity' => __('Private equity'),
            'individual' => __('Propriétaire individuel'),
            'government' => __('Public / gouvernement'),
            'telecom' => __('Télécom intégré'),
            'corporation' => __('Corporation'),
            'independent' => __('Indépendant'),
            default => __('Non classé'),
        };
    }

    public static function shortLabel(string $cat): string
    {
        return match ($cat) {
            'conglomerate' => __('Conglomérat'),
            'private_equity' => __('PE'),
            'individual' => __('Individuel'),
            'government' => __('Public'),
            'telecom' => __('Télécom'),
            'corporation' => __('Corp'),
            'independent' => __('Indépendant'),
            default => __('?'),
        };
    }

    public static function icon(string $cat): string
    {
        return match ($cat) {
            'conglomerate' => 'ti ti-buildings',
            'private_equity' => 'ti ti-coin',
            'individual' => 'ti ti-user',
            'government' => 'ti ti-flag',
            'telecom' => 'ti ti-broadcast',
            'corporation' => 'ti ti-building-factory-2',
            'independent' => 'ti ti-leaf',
            default => 'ti ti-help-circle',
        };
    }

    public static function color(string $cat): string
    {
        return match ($cat) {
            'independent' => '#16a34a',
            'government' => '#a16207',
            'private_equity' => '#7c3aed',
            'individual' => '#0891b2',
            'telecom' => '#0ea5e9',
            'corporation' => '#475569',
            'conglomerate' => '#374151',
            default => '#9ca3af',
        };
    }
}
