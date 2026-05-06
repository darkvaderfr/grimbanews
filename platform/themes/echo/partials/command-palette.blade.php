<div id="grimba-command-palette"
     class="grimba-command-palette"
     role="dialog"
     aria-modal="true"
     aria-hidden="true"
     aria-labelledby="grimba-command-title"
     data-grimba-command-palette>
    <div class="grimba-command-palette__backdrop" data-grimba-command-close></div>
    <section class="grimba-command-palette__panel glass-panel" role="document">
        <div class="grimba-command-palette__head">
            <h2 id="grimba-command-title" class="visually-hidden">{{ __('Recherche rapide') }}</h2>
            <label class="grimba-command-palette__search" for="grimba-command-input">
                <span class="grimba-command-palette__search-icon" aria-hidden="true">
                    <x-core::icon name="ti ti-search" />
                </span>
                <input id="grimba-command-input"
                       type="search"
                       autocomplete="off"
                       spellcheck="false"
                       role="combobox"
                       aria-autocomplete="list"
                       aria-expanded="false"
                       aria-controls="grimba-command-results"
                       placeholder="{{ __('Rechercher une histoire, un sujet, une source') }}"
                       data-grimba-command-input>
            </label>
            <button type="button"
                    class="grimba-command-palette__close"
                    aria-label="{{ __('Fermer') }}"
                    data-grimba-command-close>
                <x-core::icon name="ti ti-x" />
            </button>
        </div>

        <div class="grimba-command-palette__body">
            <div class="grimba-command-palette__state" data-grimba-command-loading hidden>{{ __('Chargement') }}</div>
            <div id="grimba-command-results"
                 class="grimba-command-palette__results"
                 role="listbox"
                 aria-label="{{ __('Résultats') }}"
                 data-grimba-command-results></div>
            <a class="grimba-command-palette__search-all" href="{{ url('/search') }}" data-grimba-command-search-all hidden>
                {{ __('Rechercher dans tous les articles') }}
            </a>
            <div class="grimba-command-palette__state" data-grimba-command-empty hidden>{{ __('Aucun résultat') }}</div>
        </div>
    </section>
</div>

<script>
    (function () {
        const palette = document.querySelector('[data-grimba-command-palette]');
        if (!palette || palette.dataset.grimbaReady === '1') return;
        palette.dataset.grimbaReady = '1';

        const endpoint = @json(route('public.command-palette.index'));
        const searchUrl = @json(url('/search'));
        const cacheKey = 'grimba_command_palette_index_v1';
        const metaKey = 'grimba_command_palette_meta_v1';
        const cookieName = 'grimba_command_palette_cached';
        const input = palette.querySelector('[data-grimba-command-input]');
        const results = palette.querySelector('[data-grimba-command-results]');
        const loading = palette.querySelector('[data-grimba-command-loading]');
        const empty = palette.querySelector('[data-grimba-command-empty]');
        const searchAll = palette.querySelector('[data-grimba-command-search-all]');
        let items = [];
        let visibleItems = [];
        let activeIndex = 0;
        let loadPromise = null;
        let lastFocus = null;
        const trap = window.GrimbaFocus?.trap(palette, {
            initialFocus: input,
            onEscape: close
        });

        const typeRank = { nav: 0, story: 1, source: 2, category: 3 };

        function normalize(value) {
            return String(value || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .trim();
        }

        function writeCookie(value, maxAge) {
            document.cookie = cookieName + '=' + encodeURIComponent(value)
                + '; path=/; max-age=' + maxAge + '; SameSite=Lax';
        }

        function readCache() {
            try {
                const meta = JSON.parse(window.localStorage.getItem(metaKey) || 'null');
                if (!meta || !meta.expires_at || Date.now() > meta.expires_at) return null;
                const cached = JSON.parse(window.localStorage.getItem(cacheKey) || '[]');
                if (!Array.isArray(cached)) return null;
                writeCookie(meta.generated_at || String(Date.now()), Math.max(60, Math.floor((meta.expires_at - Date.now()) / 1000)));
                return cached;
            } catch (_) {
                return null;
            }
        }

        function writeCache(payload) {
            try {
                const ttl = Math.max(60, Number(payload.ttl_seconds || 300));
                window.localStorage.setItem(cacheKey, JSON.stringify(payload.items || []));
                window.localStorage.setItem(metaKey, JSON.stringify({
                    generated_at: payload.generated_at || null,
                    expires_at: Date.now() + ttl * 1000
                }));
                writeCookie(payload.generated_at || String(Date.now()), ttl);
            } catch (_) {}
        }

        async function loadIndex() {
            const cached = readCache();
            if (cached) {
                items = cached;
                return items;
            }
            if (loadPromise) return loadPromise;

            loading.hidden = false;
            loadPromise = fetch(endpoint, { headers: { Accept: 'application/json' } })
                .then(response => response.ok ? response.json() : Promise.reject(new Error('palette fetch failed')))
                .then(payload => {
                    items = Array.isArray(payload.items) ? payload.items : [];
                    writeCache({ ...payload, items });
                    return items;
                })
                .catch(() => {
                    items = [];
                    return items;
                })
                .finally(() => {
                    loading.hidden = true;
                    loadPromise = null;
                });

            return loadPromise;
        }

        function scoreItem(item, query) {
            if (!query) return 100 - (typeRank[item.type] || 5);
            const terms = normalize(query).split(/\s+/).filter(Boolean);
            const title = normalize(item.title);
            const subtitle = normalize(item.subtitle);
            const meta = normalize(item.meta + ' ' + item.label + ' ' + item.type);
            const haystack = title + ' ' + subtitle + ' ' + meta;
            if (!terms.every(term => haystack.includes(term))) return 0;

            return terms.reduce((score, term) => {
                if (title === term) return score + 90;
                if (title.startsWith(term)) return score + 70;
                if (title.includes(term)) return score + 48;
                if (subtitle.includes(term)) return score + 24;
                return score + 12;
            }, 10 - (typeRank[item.type] || 5));
        }

        function setActive(index) {
            activeIndex = Math.max(0, Math.min(index, Math.max(visibleItems.length - 1, 0)));
            results.querySelectorAll('[role="option"]').forEach((node, i) => {
                const active = i === activeIndex;
                node.classList.toggle('is-active', active);
                node.setAttribute('aria-selected', active ? 'true' : 'false');
                if (active) {
                    input.setAttribute('aria-activedescendant', node.id);
                    node.scrollIntoView({ block: 'nearest' });
                }
            });
        }

        function resultNode(item, index) {
            const link = document.createElement('a');
            link.className = 'grimba-command-palette__item';
            link.href = item.url || searchUrl;
            link.id = 'grimba-command-option-' + index;
            link.setAttribute('role', 'option');
            link.setAttribute('aria-selected', 'false');
            link.dataset.grimbaCommandType = item.type || 'nav';

            const icon = document.createElement('span');
            icon.className = 'grimba-command-palette__item-icon';
            icon.setAttribute('aria-hidden', 'true');

            const body = document.createElement('span');
            body.className = 'grimba-command-palette__item-body';

            const title = document.createElement('strong');
            title.textContent = item.title || '';

            const subtitle = document.createElement('span');
            subtitle.textContent = item.subtitle || '';

            const meta = document.createElement('span');
            meta.className = 'grimba-command-palette__item-meta';
            meta.textContent = [item.label, item.meta].filter(Boolean).join(' · ');

            body.append(title, subtitle);
            link.append(icon, body, meta);
            link.addEventListener('mousemove', () => setActive(index));
            return link;
        }

        function render() {
            const query = input.value.trim();
            const normalizedQuery = normalize(query);
            results.textContent = '';

            visibleItems = (items || [])
                .map(item => ({ item, score: scoreItem(item, normalizedQuery) }))
                .filter(row => normalizedQuery.length < 2 || row.score > 0)
                .sort((a, b) => b.score - a.score)
                .slice(0, 10)
                .map(row => row.item);

            visibleItems.forEach((item, index) => results.appendChild(resultNode(item, index)));

            empty.hidden = visibleItems.length > 0 || loading.hidden === false;
            searchAll.hidden = query === '';
            if (query !== '') {
                searchAll.href = searchUrl + '?q=' + encodeURIComponent(query);
                searchAll.textContent = @json(__('Rechercher dans tous les articles')) + ' : ' + query;
            }
            input.setAttribute('aria-expanded', palette.classList.contains('is-open') ? 'true' : 'false');
            setActive(0);
        }

        async function open(seed) {
            lastFocus = document.activeElement instanceof HTMLElement ? document.activeElement : null;
            palette.classList.add('is-open');
            palette.setAttribute('aria-hidden', 'false');
            document.body.classList.add('grimba-command-open');
            input.value = seed || '';
            trap?.activate(lastFocus);
            await loadIndex();
            render();
        }

        function close() {
            palette.classList.remove('is-open');
            palette.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('grimba-command-open');
            input.removeAttribute('aria-activedescendant');
            input.setAttribute('aria-expanded', 'false');
            trap?.deactivate(false);
            if (lastFocus && document.contains(lastFocus)) {
                lastFocus.focus({ preventScroll: true });
            }
        }

        function isTypingTarget(target) {
            return target instanceof HTMLElement
                && (['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName) || target.isContentEditable);
        }

        document.querySelectorAll('[data-grimba-command-form]').forEach(form => {
            form.addEventListener('submit', event => {
                event.preventDefault();
                const source = form.querySelector('[data-grimba-command-source]');
                open(source ? source.value : '');
            });
        });

        palette.querySelectorAll('[data-grimba-command-close]').forEach(button => {
            button.addEventListener('click', close);
        });

        input.addEventListener('input', render);
        input.addEventListener('keydown', event => {
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                setActive(activeIndex + 1);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                setActive(activeIndex - 1);
            } else if (event.key === 'Home') {
                event.preventDefault();
                setActive(0);
            } else if (event.key === 'End') {
                event.preventDefault();
                setActive(visibleItems.length - 1);
            } else if (event.key === 'Enter') {
                const item = visibleItems[activeIndex];
                if (item && item.url) {
                    event.preventDefault();
                    window.location.href = item.url;
                } else if (input.value.trim() !== '') {
                    event.preventDefault();
                    window.location.href = searchUrl + '?q=' + encodeURIComponent(input.value.trim());
                }
            } else if (event.key === 'Escape') {
                event.preventDefault();
                close();
            }
        });

        document.addEventListener('keydown', event => {
            const key = String(event.key || '').toLowerCase();
            if (palette.classList.contains('is-open') && key === 'escape') {
                event.preventDefault();
                close();
                return;
            }
            if ((event.metaKey || event.ctrlKey) && key === 'k' && !isTypingTarget(event.target)) {
                event.preventDefault();
                open('');
            }
        });
    })();
</script>
