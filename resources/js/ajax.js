/**
 * ================================================================
 *  resources/js/ajax.js
 *  Appels AJAX — Panier, Notifications, Admin
 * ================================================================
 */

// ────────────────────────────────────────────────────────────────
// CSRF Helper
// ────────────────────────────────────────────────────────────────
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

async function apiPost(url, data = {}) {
    const res = await fetch(url, {
        method : 'POST',
        headers: {
            'Content-Type' : 'application/json',
            'X-CSRF-TOKEN' : CSRF,
            'Accept'       : 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(data),
    });
    return res.json();
}

async function apiGet(url) {
    const res = await fetch(url, {
        headers: {
            'Accept'           : 'application/json',
            'X-Requested-With' : 'XMLHttpRequest',
        },
    });
    return res.json();
}


// ────────────────────────────────────────────────────────────────
// PANIER AJAX — Ajouter sans rechargement
// ────────────────────────────────────────────────────────────────
document.querySelectorAll('[data-ajax-panier]').forEach(btn => {
    btn.addEventListener('click', async function (e) {
        e.preventDefault();
        const produitId = this.dataset.ajaxPanier;
        const quantite  = this.dataset.quantite || 1;

        // Animation
        this.style.transform = 'scale(1.2)';
        this.innerHTML       = '<i class="bi bi-bag-check"></i>';
        this.style.background = 'var(--vert-atlas)';

        try {
            const data = await apiPost(`/panier/${produitId}`, { quantite });

            if (data.success !== false) {
                // Mettre à jour le badge
                const badge = document.querySelector('.action-icon[href*="panier"] .badge-count');
                if (badge) {
                    badge.textContent   = data.count || '';
                    badge.style.display = 'flex';
                }
                TissuArtisanal.toast('Ajouté au panier !', 'success');
            } else {
                TissuArtisanal.toast(data.error || 'Erreur', 'error');
            }
        } catch {
            TissuArtisanal.toast('Erreur réseau', 'error');
        }

        setTimeout(() => {
            this.style.transform  = 'scale(1)';
            this.innerHTML        = '<i class="bi bi-bag-plus"></i>';
            this.style.background = 'var(--indigo)';
        }, 1200);
    });
});


// ────────────────────────────────────────────────────────────────
// NOTIFICATIONS — Marquer lue en AJAX
// ────────────────────────────────────────────────────────────────
document.querySelectorAll('[data-notif-lire]').forEach(btn => {
    btn.addEventListener('click', async function () {
        const id = this.dataset.notifLire;
        try {
            await fetch(`/notifications/${id}/lire`, {
                method : 'PUT',
                headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
            });
            // Mise à jour visuelle
            const row = this.closest('[data-notif-row]');
            if (row) {
                row.style.background  = 'white';
                row.style.borderColor = 'var(--sable-dark)';
            }
            this.style.display = 'none';

            // Décrémenter badge
            const badge = document.querySelector('.action-icon[href*="notifications"] .badge-count');
            if (badge) {
                const count = parseInt(badge.textContent) - 1;
                if (count <= 0) badge.style.display = 'none';
                else badge.textContent = count;
            }
        } catch { /* silencieux */ }
    });
});


// ────────────────────────────────────────────────────────────────
// ADMIN — Toggle actif/inactif produit en AJAX
// ────────────────────────────────────────────────────────────────
document.querySelectorAll('[data-toggle-produit]').forEach(btn => {
    btn.addEventListener('click', async function () {
        const id       = this.dataset.toggleProduit;
        const isActive = this.dataset.actif === '1';

        try {
            const data = await fetch(`/admin/produits/${id}/toggle`, {
                method : 'PUT',
                headers: {
                    'X-CSRF-TOKEN'     : CSRF,
                    'X-Requested-With' : 'XMLHttpRequest',
                    'Content-Type'     : 'application/json',
                },
            });

            if (data.ok) {
                const newActif = !isActive;
                this.dataset.actif    = newActif ? '1' : '0';
                this.textContent      = newActif ? 'Désactiver' : 'Activer';
                this.style.color      = newActif ? 'var(--rouge-fes)' : 'var(--vert-atlas)';
                this.style.borderColor= this.style.color;

                const badge = this.closest('tr')?.querySelector('.badge-statut');
                if (badge) {
                    badge.textContent  = newActif ? 'Actif' : 'Inactif';
                    badge.className    = `badge-statut ${newActif ? 'badge-actif' : 'badge-inactif'}`;
                }
            }
        } catch { /* silencieux */ }
    });
});


// ────────────────────────────────────────────────────────────────
// ADMIN — Changement statut commande inline
// ────────────────────────────────────────────────────────────────
document.querySelectorAll('[data-statut-commande]').forEach(sel => {
    sel.addEventListener('change', async function () {
        const id     = this.dataset.statutCommande;
        const statut = this.value;

        try {
            const res = await fetch(`/admin/commandes/${id}/statut`, {
                method : 'PUT',
                headers: {
                    'Content-Type'     : 'application/json',
                    'X-CSRF-TOKEN'     : CSRF,
                    'X-Requested-With' : 'XMLHttpRequest',
                },
                body: JSON.stringify({ statut }),
            });

            if (res.ok) {
                TissuArtisanal.toast(`Commande #${id} → ${statut}`, 'success');
                // Mettre à jour le badge dans la ligne
                const badge = this.closest('tr')?.querySelector('.badge-statut');
                if (badge) {
                    const bMap = {
                        pending: 'badge-pending', confirmed: 'badge-confirmed',
                        processing: 'badge-processing', shipped: 'badge-shipped',
                        delivered: 'badge-delivered', cancelled: 'badge-cancelled',
                    };
                    badge.className  = `badge-statut ${bMap[statut] || ''}`;
                    badge.textContent = statut;
                }
            }
        } catch {
            TissuArtisanal.toast('Erreur lors de la mise à jour', 'error');
        }
    });
});


// ────────────────────────────────────────────────────────────────
// ADMIN — Valider artisan inline
// ────────────────────────────────────────────────────────────────
document.querySelectorAll('[data-valider-artisan]').forEach(btn => {
    btn.addEventListener('click', async function () {
        const id = this.dataset.validerArtisan;
        if (!confirm('Valider cet artisan ?')) return;

        try {
            const res = await fetch(`/admin/artisans/${id}/valider`, {
                method : 'POST',
                headers: {
                    'X-CSRF-TOKEN'     : CSRF,
                    'X-Requested-With' : 'XMLHttpRequest',
                },
            });

            if (res.ok) {
                TissuArtisanal.toast('Artisan validé avec succès !', 'success');
                const row = this.closest('tr, .artisan-card');
                if (row) {
                    row.style.opacity    = '0.5';
                    row.style.transition = '0.4s';
                    setTimeout(() => row.remove(), 400);
                }
            }
        } catch {
            TissuArtisanal.toast('Erreur lors de la validation', 'error');
        }
    });
});


// ────────────────────────────────────────────────────────────────
// RECHERCHE — Autocomplete produits
// ────────────────────────────────────────────────────────────────
const searchBar = document.querySelector('.search-bar input[name="q"]');
if (searchBar) {
    let timer;
    const dropdown = document.createElement('div');
    dropdown.style.cssText =
        'position:absolute;top:100%;left:0;right:0;background:white;' +
        'border-radius:0 0 var(--radius) var(--radius);' +
        'box-shadow:var(--shadow-md);z-index:200;display:none;max-height:300px;overflow-y:auto;';
    searchBar.parentNode.style.position = 'relative';
    searchBar.parentNode.appendChild(dropdown);

    searchBar.addEventListener('input', function () {
        clearTimeout(timer);
        const q = this.value.trim();
        if (q.length < 2) { dropdown.style.display = 'none'; return; }

        timer = setTimeout(async () => {
            try {
                const data = await apiGet(`/catalogue?q=${encodeURIComponent(q)}&ajax=1`);
                // Afficher résultats (si endpoint JSON supporté)
            } catch { /* silencieux */ }
        }, 300);
    });

    document.addEventListener('click', e => {
        if (!searchBar.parentNode.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
}


// ────────────────────────────────────────────────────────────────
// PROGRESSION FORMATION — Mise à jour slider (artisan)
// ────────────────────────────────────────────────────────────────
const progressSlider = document.getElementById('progression-slider');
if (progressSlider) {
    const display = document.getElementById('progression-display');
    progressSlider.addEventListener('input', function () {
        if (display) display.textContent = this.value + '%';
        const bar = document.getElementById('progression-bar');
        if (bar) bar.style.width = this.value + '%';
    });
}
