/**
 * ================================================================
 *  resources/js/app.js
 *  Scripts globaux — Plateforme Tissu Artisanal
 * ================================================================
 */

// ────────────────────────────────────────────────────────────────
// 1. FLASH MESSAGES — auto-disparition après 4.5s
// ────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {

    // Auto-disparition flash
    setTimeout(() => {
        document.querySelectorAll('.flash-msg').forEach(el => {
            el.style.transition = 'all 0.4s ease';
            el.style.opacity    = '0';
            el.style.transform  = 'translateX(120%)';
            setTimeout(() => el.remove(), 400);
        });
    }, 4500);

    // ────────────────────────────────────────────────────────────
    // 2. PANIER — badge count depuis session
    // ────────────────────────────────────────────────────────────
    const panierBadge = document.querySelector('.action-icon .badge-count');
    if (panierBadge) {
        const count = parseInt(panierBadge.textContent);
        if (count === 0) panierBadge.style.display = 'none';
    }

    // ────────────────────────────────────────────────────────────
    // 3. FORMULAIRES — loading state sur submit
    // ────────────────────────────────────────────────────────────
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function () {
            const btn = this.querySelector('button[type="submit"]');
            if (btn && !btn.dataset.noLoading) {
                const originalText = btn.innerHTML;
                btn.innerHTML = '<span class="spinner"></span> Chargement…';
                btn.disabled  = true;
                // Restore si erreur (navigation back)
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.disabled  = false;
                }, 8000);
            }
        });
    });

    // ────────────────────────────────────────────────────────────
    // 4. TOOLTIPS Bootstrap
    // ────────────────────────────────────────────────────────────
    if (typeof bootstrap !== 'undefined') {
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
            .forEach(el => new bootstrap.Tooltip(el));
    }

    // ────────────────────────────────────────────────────────────
    // 5. IMAGES — lazy loading fallback
    // ────────────────────────────────────────────────────────────
    document.querySelectorAll('img[loading="lazy"]').forEach(img => {
        img.addEventListener('error', function () {
            this.style.display = 'none';
            const parent = this.closest('.produit-img, .formation-img');
            if (parent) parent.innerHTML = '🧵';
        });
    });

    // ────────────────────────────────────────────────────────────
    // 6. CONFIRM — boutons de suppression/annulation
    // ────────────────────────────────────────────────────────────
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function (e) {
            const msg = this.dataset.confirm || 'Êtes-vous sûr ?';
            if (!confirm(msg)) e.preventDefault();
        });
    });

    // ────────────────────────────────────────────────────────────
    // 7. SEARCH BAR — submit au clic
    // ────────────────────────────────────────────────────────────
    const searchInput = document.querySelector('.search-bar input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                this.closest('form')?.submit();
            }
        });
    }

    // ────────────────────────────────────────────────────────────
    // 8. NOTIFICATION COUNT — rafraîchir depuis API
    // ────────────────────────────────────────────────────────────
    const notifBadge = document.querySelector('.action-icon[href*="notifications"] .badge-count');
    if (notifBadge && document.querySelector('meta[name="csrf-token"]')) {
        // Rafraîchir le count toutes les 60s si l'utilisateur est connecté
        setInterval(async () => {
            try {
                const res = await fetch('/notifications/count-ajax', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.count > 0) {
                        notifBadge.textContent     = data.count;
                        notifBadge.style.display   = 'flex';
                    } else {
                        notifBadge.style.display = 'none';
                    }
                }
            } catch (e) { /* silencieux */ }
        }, 60000);
    }

    // ────────────────────────────────────────────────────────────
    // 9. SCROLL TO TOP — bouton flottant
    // ────────────────────────────────────────────────────────────
    const scrollBtn = document.getElementById('scrollTopBtn');
    if (scrollBtn) {
        window.addEventListener('scroll', () => {
            scrollBtn.style.opacity  = window.scrollY > 400 ? '1' : '0';
            scrollBtn.style.pointerEvents = window.scrollY > 400 ? 'all' : 'none';
        });
        scrollBtn.addEventListener('click', () =>
            window.scrollTo({ top: 0, behavior: 'smooth' })
        );
    }

    // ────────────────────────────────────────────────────────────
    // 10. TABS génériques — [data-tab-target]
    // ────────────────────────────────────────────────────────────
    document.querySelectorAll('[data-tab-target]').forEach(btn => {
        btn.addEventListener('click', function () {
            const target = this.dataset.tabTarget;
            const group  = this.dataset.tabGroup || 'default';

            // Désactiver tous les boutons du groupe
            document.querySelectorAll(`[data-tab-group="${group}"]`)
                .forEach(b => b.classList.remove('active'));

            // Masquer tous les panneaux du groupe
            document.querySelectorAll(`[data-tab-panel="${group}"]`)
                .forEach(p => p.style.display = 'none');

            // Activer ce bouton + son panneau
            this.classList.add('active');
            const panel = document.getElementById(target);
            if (panel) panel.style.display = '';
        });
    });

    // ────────────────────────────────────────────────────────────
    // 11. PANIER — ajouter avec animation (AJAX optionnel)
    // ────────────────────────────────────────────────────────────
    document.querySelectorAll('.btn-panier').forEach(btn => {
        btn.addEventListener('click', function () {
            // Animation pulse
            this.style.transform = 'scale(1.3)';
            setTimeout(() => { this.style.transform = 'scale(1)'; }, 200);
        });
    });

    // ────────────────────────────────────────────────────────────
    // 12. ADMIN SIDEBAR — mobile toggle
    // ────────────────────────────────────────────────────────────
    const sidebarToggle = document.getElementById('sidebarToggle');
    const adminSidebar  = document.querySelector('.admin-sidebar');
    if (sidebarToggle && adminSidebar) {
        sidebarToggle.addEventListener('click', () => {
            adminSidebar.classList.toggle('show');
        });
    }

    // ────────────────────────────────────────────────────────────
    // 13. IMAGES PREVIEW — formulaires avec upload
    // ────────────────────────────────────────────────────────────
    document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
        const previewId = input.dataset.preview;
        input.addEventListener('change', function () {
            const preview = document.getElementById(previewId);
            if (!preview) return;
            preview.innerHTML = '';
            Array.from(this.files).slice(0, 5).forEach(file => {
                const reader = new FileReader();
                reader.onload = e => {
                    const div = document.createElement('div');
                    div.style.cssText =
                        'width:80px;height:80px;border-radius:8px;overflow:hidden;' +
                        'border:2px solid var(--or);display:inline-block;margin:4px;';
                    div.innerHTML =
                        `<img src="${e.target.result}" ` +
                        `style="width:100%;height:100%;object-fit:cover;">`;
                    preview.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        });
    });

    // ────────────────────────────────────────────────────────────
    // 14. PROGRESSION FORMATIONS — animation au chargement
    // ────────────────────────────────────────────────────────────
    document.querySelectorAll('[data-progress]').forEach(bar => {
        const target = parseInt(bar.dataset.progress) || 0;
        setTimeout(() => { bar.style.width = target + '%'; }, 300);
    });

    // ────────────────────────────────────────────────────────────
    // 15. RATING STARS — interaction générique
    // ────────────────────────────────────────────────────────────
    document.querySelectorAll('.star-rating').forEach(container => {
        const stars  = container.querySelectorAll('.star');
        const input  = container.querySelector('input[type="hidden"]');
        let selected = parseInt(input?.value) || 0;

        stars.forEach((star, i) => {
            star.addEventListener('mouseover', () => {
                stars.forEach((s, j) => {
                    s.style.color = j <= i ? 'var(--or)' : 'var(--sable-dark)';
                });
            });

            star.addEventListener('mouseout', () => {
                stars.forEach((s, j) => {
                    s.style.color = j < selected ? 'var(--or)' : 'var(--sable-dark)';
                });
            });

            star.addEventListener('click', () => {
                selected = i + 1;
                if (input) input.value = selected;
                stars.forEach((s, j) => {
                    s.style.color = j < selected ? 'var(--or)' : 'var(--sable-dark)';
                });
            });
        });
    });

});

// ────────────────────────────────────────────────────────────────
// UTILS GLOBAUX
// ────────────────────────────────────────────────────────────────
window.TissuArtisanal = {

    // Formater un prix en MAD
    formatPrix(montant) {
        return new Intl.NumberFormat('fr-MA', {
            style: 'currency', currency: 'MAD', minimumFractionDigits: 2
        }).format(montant);
    },

    // Afficher un toast
    toast(message, type = 'success') {
        const container = document.getElementById('flashContainer');
        if (!container) return;

        const div = document.createElement('div');
        div.className = `flash-msg flash-${type}`;
        div.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill'}"></i>
            ${message}
        `;
        container.appendChild(div);

        setTimeout(() => {
            div.style.transition = 'all 0.4s ease';
            div.style.opacity    = '0';
            div.style.transform  = 'translateX(120%)';
            setTimeout(() => div.remove(), 400);
        }, 4000);
    },

    // Confirmer une action
    confirmer(msg) {
        return confirm(msg || 'Êtes-vous sûr de vouloir effectuer cette action ?');
    }
};