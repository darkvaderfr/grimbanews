/* GrimbaNews — live preview card on the post editor.
   Hooks grimba_source_id and grimba_story_cluster_id selects and
   updates the two panes (data-slot="source" / "cluster") via the
   /admin/grimba/api/preview/* endpoints.

   All DOM construction uses createElement + textContent — no innerHTML
   with dynamic values — so this is XSS-safe even if the DB is tainted. */

(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    function adminBase() {
        var m = window.location.pathname.match(/^(\/[^/]+)\/blog\/posts\//);
        return m ? m[1] : '/admin';
    }

    function el(tag, opts) {
        var e = document.createElement(tag);
        if (!opts) return e;
        if (opts.cls) e.className = opts.cls;
        if (opts.text != null) e.textContent = String(opts.text);
        if (opts.title) e.title = String(opts.title);
        if (opts.style) e.setAttribute('style', String(opts.style));
        if (opts.href) e.href = String(opts.href);
        if (opts.target) e.target = String(opts.target);
        if (opts.rel) e.rel = String(opts.rel);
        return e;
    }

    function clear(node) {
        while (node.firstChild) node.removeChild(node.firstChild);
    }

    function setEmpty(slotEl, msg) {
        clear(slotEl);
        slotEl.classList.add('is-empty');
        slotEl.textContent = msg;
    }

    function setError(slotEl, msg) {
        clear(slotEl);
        slotEl.classList.remove('is-empty');
        var span = el('span', { cls: 'gp-error', text: msg });
        slotEl.appendChild(span);
    }

    function metaPair(label, value) {
        var wrap = el('span');
        var strong = el('strong', { text: label + ': ' });
        wrap.appendChild(strong);
        wrap.appendChild(document.createTextNode(String(value)));
        return wrap;
    }

    function renderSource(slotEl, data) {
        clear(slotEl);
        slotEl.classList.remove('is-empty');

        slotEl.appendChild(el('div', { cls: 'gp-name', text: data.name || '' }));

        var meta = el('div', { cls: 'gp-meta' });
        var bias = (data.bias_rating || 'unknown');
        meta.appendChild(el('span', { cls: 'gp-chip gp-chip--' + bias, text: data.bias_label || bias }));
        if (data.ownership_label) meta.appendChild(metaPair('Propriété', data.ownership_label));
        if (data.country) meta.appendChild(metaPair('Pays', data.country));
        if (data.language) meta.appendChild(metaPair('Langue', data.language));
        slotEl.appendChild(meta);

        if (data.credibility_score != null) {
            var pct = Math.max(0, Math.min(100, Number(data.credibility_score)));
            var bar = el('div', { cls: 'gp-credbar' });
            bar.appendChild(el('span', { style: 'width:' + pct + '%' }));
            slotEl.appendChild(bar);
            slotEl.appendChild(el('div', { cls: 'gp-credlabel', text: 'Crédibilité: ' + pct + '/100' }));
        }

        if (data.website) {
            var href = String(data.website);
            if (!/^https?:\/\//i.test(href)) href = 'https://' + href;
            var meta2 = el('div', { cls: 'gp-meta', style: 'margin-top:6px' });
            meta2.appendChild(el('a', { href: href, target: '_blank', rel: 'noopener', text: data.website }));
            slotEl.appendChild(meta2);
        }
    }

    function renderCluster(slotEl, data) {
        clear(slotEl);
        slotEl.classList.remove('is-empty');

        var counts = data.counts || { left: 0, center: 0, right: 0, unknown: 0 };
        var total = Number(data.total || 0);

        slotEl.appendChild(el('div', { cls: 'gp-name', text: '#' + data.id + ' — ' + (data.topic || '') }));

        var countMeta = el('div', { cls: 'gp-meta' });
        var countStrong = el('strong', { text: String(total) });
        countMeta.appendChild(countStrong);
        countMeta.appendChild(document.createTextNode(
            ' article' + (total === 1 ? '' : 's') + ' publié' + (total === 1 ? '' : 's')
        ));
        slotEl.appendChild(countMeta);

        var sides = ['left', 'center', 'right', 'unknown'];
        var legendMap = { left: 'Gauche', center: 'Centre', right: 'Droite', unknown: 'Inconnu' };

        if (total > 0) {
            var bar = el('div', { cls: 'gp-bardist' });
            sides.forEach(function (side) {
                var n = Number(counts[side] || 0);
                if (n > 0) {
                    var pct = (n / total * 100).toFixed(1);
                    bar.appendChild(el('span', {
                        cls: 'gp-bar--' + side,
                        style: 'width:' + pct + '%',
                        title: side + ': ' + n,
                    }));
                }
            });
            slotEl.appendChild(bar);

            var legend = el('div', { cls: 'gp-distlegend' });
            sides.forEach(function (side) {
                var n = Number(counts[side] || 0);
                if (n > 0) legend.appendChild(metaPair(legendMap[side], n));
            });
            slotEl.appendChild(legend);
        }

        if (data.latest) {
            var latest = el('div', { cls: 'gp-latest' });
            latest.appendChild(document.createTextNode('Dernier: '));
            latest.appendChild(el('em', { text: data.latest.name || '' }));
            if (data.latest.source_name) {
                latest.appendChild(document.createTextNode(' — ' + data.latest.source_name));
            }
            slotEl.appendChild(latest);
        } else if (total === 0) {
            slotEl.appendChild(el('div', {
                cls: 'gp-latest',
                text: 'Aucun article publié dans ce dossier pour l’instant.',
            }));
        }
    }

    function fetchJson(url, token) {
        return fetch(url, {
            credentials: 'same-origin',
            headers: token ? { 'X-CSRF-TOKEN': token } : {},
        }).then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        });
    }

    ready(function () {
        var card = document.getElementById('grimba-post-preview');
        if (!card) return;

        var sourceSel = document.querySelector('[name="grimba_source_id"]');
        var clusterSel = document.querySelector('[name="grimba_story_cluster_id"]');
        var sourceSlot = card.querySelector('[data-slot="source"]');
        var clusterSlot = card.querySelector('[data-slot="cluster"]');
        if (!sourceSlot || !clusterSlot) return;

        var base = adminBase();
        var tokenMeta = document.querySelector('meta[name="csrf-token"]');
        var token = tokenMeta ? tokenMeta.content : '';

        function updateSource() {
            if (!sourceSel) return;
            var id = sourceSel.value;
            if (!id) {
                setEmpty(sourceSlot, 'Sélectionnez une source pour voir ses détails.');
                return;
            }
            clear(sourceSlot);
            sourceSlot.classList.remove('is-empty');
            sourceSlot.textContent = 'Chargement…';
            fetchJson(base + '/grimba/api/preview/source/' + encodeURIComponent(id), token)
                .then(function (data) { renderSource(sourceSlot, data); })
                .catch(function (err) { setError(sourceSlot, 'Impossible de charger la source (' + err.message + ').'); });
        }

        function updateCluster() {
            if (!clusterSel) return;
            var id = clusterSel.value;
            if (!id) {
                setEmpty(clusterSlot, 'Sélectionnez un dossier pour voir sa composition.');
                return;
            }
            clear(clusterSlot);
            clusterSlot.classList.remove('is-empty');
            clusterSlot.textContent = 'Chargement…';
            fetchJson(base + '/grimba/api/preview/cluster/' + encodeURIComponent(id), token)
                .then(function (data) { renderCluster(clusterSlot, data); })
                .catch(function (err) { setError(clusterSlot, 'Impossible de charger le dossier (' + err.message + ').'); });
        }

        if (sourceSel) {
            sourceSel.addEventListener('change', updateSource);
            sourceSel.addEventListener('select2:select', updateSource);
            updateSource();
        }
        if (clusterSel) {
            clusterSel.addEventListener('change', updateCluster);
            clusterSel.addEventListener('select2:select', updateCluster);
            updateCluster();
        }
    });
})();
