@php
    /**
     * Bias Legend — explains L/C/R badges + links to comparatif/angles-morts.
     */
@endphp

<aside class="bias-legend glass-panel p-3 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
        <strong class="text-uppercase small">Décoder les biais</strong>
        <div class="d-flex gap-3 small">
            <span style="color:#3b82f6;font-weight:600;">● Gauche</span>
            <span style="color:#22c55e;font-weight:600;">● Centre</span>
            <span style="color:#ef4444;font-weight:600;">● Droite</span>
            <span style="color:#8a2be2;font-weight:600;">● Angle mort</span>
        </div>
    </div>
    <p class="small opacity-85 mb-2">
        Chaque article est classé selon l'orientation éditoriale de sa source. Consultez la
        <a href="{{ url('/comparatif/1') }}" class="text-decoration-underline">comparaison des sources</a>
        ou le flux des
        <a href="{{ url('/angles-morts') }}" class="text-decoration-underline">angles morts</a>
        pour une lecture équilibrée.
    </p>
</aside>
