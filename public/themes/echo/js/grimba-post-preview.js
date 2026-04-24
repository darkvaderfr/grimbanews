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

    function renderSuggestion(slotEl, data, clusterSel) {
        clear(slotEl);
        slotEl.classList.remove('is-empty');

        var banner = el('div', { cls: 'gp-meta' });
        banner.appendChild(el('span', { cls: 'gp-chip gp-chip--suggest', text: 'Suggestion auto' }));
        banner.appendChild(document.createTextNode(
            ' — ce titre ressemble à un dossier existant.'
        ));
        slotEl.appendChild(banner);

        slotEl.appendChild(el('div', {
            cls: 'gp-name',
            text: '#' + data.id + ' — ' + (data.topic || ''),
        }));

        var total = Number(data.total || 0);
        var countRow = el('div', { cls: 'gp-meta' });
        countRow.appendChild(el('strong', { text: String(total) }));
        countRow.appendChild(document.createTextNode(
            ' article' + (total === 1 ? '' : 's') + ' déjà attaché' + (total === 1 ? '' : 's')
        ));
        slotEl.appendChild(countRow);

        if (Array.isArray(data.sources) && data.sources.length > 0) {
            var srcs = el('div', { cls: 'gp-meta' });
            srcs.appendChild(el('strong', { text: 'Sources: ' }));
            srcs.appendChild(document.createTextNode(data.sources.slice(0, 4).join(' · ')));
            slotEl.appendChild(srcs);
        }

        var btnRow = el('div', { style: 'margin-top: 10px; display:flex; gap:8px;' });
        var attach = el('button', {
            cls: 'btn btn-sm btn-outline-primary',
            text: 'Attacher ce dossier',
        });
        attach.type = 'button';
        attach.addEventListener('click', function () {
            if (!clusterSel) return;
            var opt = Array.from(clusterSel.options).find(function (o) {
                return String(o.value) === String(data.id);
            });
            if (!opt) return;
            clusterSel.value = String(data.id);
            // Notify any listeners (select2, native). select2 wraps <select>
            // with a custom dropdown; dispatch 'change' so both its data
            // binding and our own preview-fetch listener fire.
            clusterSel.dispatchEvent(new Event('change', { bubbles: true }));
            if (window.jQuery) { window.jQuery(clusterSel).trigger('change'); }
        });
        btnRow.appendChild(attach);

        var dismiss = el('button', {
            cls: 'btn btn-sm btn-link text-muted',
            text: 'Ignorer',
        });
        dismiss.type = 'button';
        dismiss.addEventListener('click', function () {
            setEmpty(slotEl, 'Sélectionnez un dossier pour voir sa composition.');
            // Remember dismissal for the remainder of the page session so the
            // nudge doesn't keep coming back on every title keystroke.
            slotEl.dataset.dismissedSuggestion = String(data.id);
        });
        btnRow.appendChild(dismiss);

        slotEl.appendChild(btnRow);
    }

    ready(function () {
        var card = document.getElementById('grimba-post-preview');
        if (!card) return;

        var titleInput = document.querySelector('input[name="name"]');
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

        function maybeSuggestCluster() {
            if (! clusterSel || ! titleInput) return;
            // Only nudge when the editor hasn't picked a cluster themselves.
            if (clusterSel.value) return;
            var title = String(titleInput.value || '').trim();
            if (title.length < 10) {
                if (clusterSlot.dataset.suggestionShown === '1') {
                    setEmpty(clusterSlot, 'Sélectionnez un dossier pour voir sa composition.');
                    delete clusterSlot.dataset.suggestionShown;
                }
                return;
            }
            fetchJson(base + '/grimba/api/preview/cluster-suggest?title=' + encodeURIComponent(title), token)
                .then(function (data) {
                    if (! data || ! data.suggested) {
                        // No suggestion — only reset if we had one showing.
                        if (clusterSlot.dataset.suggestionShown === '1') {
                            setEmpty(clusterSlot, 'Sélectionnez un dossier pour voir sa composition.');
                            delete clusterSlot.dataset.suggestionShown;
                        }
                        return;
                    }
                    // Respect "Ignorer" for this cluster within this page load.
                    if (String(clusterSlot.dataset.dismissedSuggestion || '') === String(data.id)) return;
                    // Don't clobber an already-loaded cluster card — only render
                    // the suggestion when no real cluster selection is active.
                    if (clusterSel.value) return;
                    renderSuggestion(clusterSlot, data, clusterSel);
                    clusterSlot.dataset.suggestionShown = '1';
                })
                .catch(function () { /* silent — nudge is optional polish */ });
        }

        var suggestTimer = null;
        function scheduleSuggest() {
            if (suggestTimer) clearTimeout(suggestTimer);
            suggestTimer = setTimeout(maybeSuggestCluster, 700);
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
        if (titleInput) {
            titleInput.addEventListener('input', scheduleSuggest);
            titleInput.addEventListener('blur',  maybeSuggestCluster);
        }
        // First-load pass — covers the edit-existing-draft case where the
        // editor hasn't touched the title but still has no cluster yet.
        scheduleSuggest();
    });
})();
