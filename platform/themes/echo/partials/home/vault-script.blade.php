<script>
    (function () {
        const COOKIE = 'grimba_vault';
        const MAX = 50;

        function ids() {
            const m = document.cookie.match(/(?:^|; )grimba_vault=([^;]+)/);
            if (! m) return [];
            return decodeURIComponent(m[1]).split(',').filter(Boolean).map(s => parseInt(s, 10)).filter(Number.isFinite);
        }
        function write(arr) {
            const v = arr.slice(0, MAX).join(',');
            document.cookie = COOKIE + '=' + encodeURIComponent(v) + '; path=/; max-age=' + (60 * 60 * 24 * 365) + '; SameSite=Lax';
        }

        function paint(btn, saved) {
            btn.setAttribute('aria-pressed', String(saved));
            const icon = btn.querySelector('.grimba-save-btn__icon, span[aria-hidden]');
            if (icon) icon.textContent = saved ? '★' : '☆';
            if (btn.classList.contains('grimba-save-btn--pill')) {
                const label = btn.querySelector('.grimba-save-btn__label');
                if (label) label.textContent = saved ? 'Sauvegardé' : 'Sauvegarder';
                btn.style.background = saved ? 'var(--gn-ink, #1a1713)' : 'rgba(255,255,255,0.6)';
                btn.style.color = saved ? 'var(--gn-paper, #f6f1e8)' : 'var(--gn-ink, #1a1713)';
            } else {
                btn.style.background = saved ? 'var(--gn-ink, #1a1713)' : 'rgba(255,255,255,0.6)';
                btn.style.color = saved ? 'var(--gn-paper, #f6f1e8)' : 'var(--gn-ink, #1a1713)';
            }
        }

        function paintCount() {
            const n = ids().length;
            document.querySelectorAll('[data-grimba-vault-count]').forEach(el => {
                el.textContent = String(n);
            });
            const fab = document.querySelector('[data-grimba-vault-fab]');
            if (fab) fab.style.display = n > 0 ? '' : 'none';
        }

        function broadcast(list) {
            document.dispatchEvent(new CustomEvent('grimba:vault-changed', {
                detail: { ids: list.slice() }
            }));
        }

        function syncAll() {
            const saved = new Set(ids());
            document.querySelectorAll('[data-grimba-save]').forEach(btn => {
                const id = parseInt(btn.dataset.grimbaSave, 10);
                if (! Number.isFinite(id)) return;
                paint(btn, saved.has(id));
            });
            paintCount();
            broadcast(Array.from(saved));
        }

        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-grimba-save]');
            if (btn) {
                e.preventDefault();
                const id = parseInt(btn.dataset.grimbaSave, 10);
                if (! Number.isFinite(id)) return;
                const list = ids();
                const i = list.indexOf(id);
                if (i >= 0) list.splice(i, 1);
                else list.unshift(id);
                write(list);
                paint(btn, list.includes(id));
                paintCount();
                broadcast(list);
                return;
            }

            const copy = e.target.closest('[data-grimba-copy-link]');
            if (! copy) return;
            e.preventDefault();
            const value = copy.dataset.grimbaCopyLink || window.location.href;
            const original = copy.textContent;
            navigator.clipboard?.writeText(value).then(() => {
                copy.textContent = 'Lien copié';
                setTimeout(() => { copy.textContent = original; }, 1600);
            }).catch(() => {
                copy.textContent = 'Copie indisponible';
                setTimeout(() => { copy.textContent = original; }, 1600);
            });
        });

        document.addEventListener('keydown', (e) => {
            if (e.key !== 's' && e.key !== 'S') return;
            const active = document.activeElement;
            if (active && ['INPUT', 'TEXTAREA', 'SELECT'].includes(active.tagName)) return;
            if (active && active.isContentEditable) return;

            const btn = document.querySelector('.grimba-save-btn--pill[data-grimba-save]');
            if (! btn) return;

            e.preventDefault();
            btn.click();
        });

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', syncAll);
        } else {
            syncAll();
        }
    })();
</script>
