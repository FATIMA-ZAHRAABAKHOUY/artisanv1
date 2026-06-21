{{--
================================================================
SCRIPTS INLINE PAR PAGE
À injecter dans @push('scripts') de chaque vue Blade
================================================================
--}}


{{-- ============================================================
     home.blade.php — Animations hero + compteurs stats
============================================================ --}}
{{-- @push('scripts') --}}
<script>
// Compteurs animés dans le hero
document.querySelectorAll('.hero-stat .num').forEach(el => {
    const target = parseInt(el.textContent.replace(/\D/g, '')) || 0;
    const suffix = el.textContent.replace(/[0-9]/g, '');
    let current  = 0;
    const step   = Math.ceil(target / 40);
    const timer  = setInterval(() => {
        current  = Math.min(current + step, target);
        el.textContent = current + suffix;
        if (current >= target) clearInterval(timer);
    }, 30);
});
</script>
{{-- @endpush --}}


{{-- ============================================================
     catalogue/index.blade.php — Filtres + tri auto
============================================================ --}}
{{-- @push('scripts') --}}
<script>
// Soumettre les filtres au changement de select
document.querySelectorAll('#filterForm select').forEach(sel => {
    sel.addEventListener('change', () =>
        document.getElementById('filterForm')?.submit()
    );
});

// Filtres prix avec validation
const prixMin = document.querySelector('[name="prix_min"]');
const prixMax = document.querySelector('[name="prix_max"]');
if (prixMin && prixMax) {
    prixMax.addEventListener('change', function () {
        if (prixMin.value && parseFloat(this.value) < parseFloat(prixMin.value)) {
            TissuArtisanal.toast('Le prix max doit être supérieur au prix min', 'error');
            this.value = '';
        }
    });
}
</script>
{{-- @endpush --}}


{{-- ============================================================
     catalogue/show.blade.php — Galerie + étoiles + quantité
============================================================ --}}
{{-- @push('scripts') --}}
<script>
// Galerie images
function changeImg(src, el) {
    const mainImg = document.getElementById('mainImgEl');
    if (mainImg) mainImg.src = src;
    document.querySelectorAll('.thumb-img').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
}

// Quantité panier
function changeQty(delta) {
    const input = document.getElementById('qty');
    if (!input) return;
    const max   = parseInt(input.getAttribute('max')) || 999;
    let v       = parseInt(input.value) + delta;
    if (v < 1)    v = 1;
    if (v > max)  v = max;
    input.value = v;
}

// Étoiles avis
document.querySelectorAll('.star-btn').forEach((star, i, all) => {
    star.addEventListener('mouseover', () => {
        all.forEach((s, j) =>
            s.style.color = j <= i ? 'var(--or)' : 'var(--sable-dark)'
        );
    });
    star.addEventListener('mouseout', () => {
        const checked = document.querySelector('[name="note"]:checked');
        const val     = checked ? parseInt(checked.value) : 0;
        all.forEach((s, j) =>
            s.style.color = j < val ? 'var(--or)' : 'var(--sable-dark)'
        );
    });
    star.addEventListener('click', () => {
        all.forEach((s, j) =>
            s.style.color = j <= i ? 'var(--or)' : 'var(--sable-dark)'
        );
        const radio = star.closest('label')?.querySelector('input');
        if (radio) radio.checked = true;
    });
});

// Zoom image principale au survol
const mainImg = document.querySelector('.main-img');
if (mainImg) {
    mainImg.style.cursor = 'zoom-in';
    mainImg.addEventListener('click', function () {
        const img = this.querySelector('img');
        if (img) window.open(img.src, '_blank');
    });
}
</script>
{{-- @endpush --}}


{{-- ============================================================
     catalogue/panier.blade.php — Mise à jour dynamique totaux
============================================================ --}}
{{-- @push('scripts') --}}
<script>
// Recalcul visuel des totaux côté client
function recalculerPanier() {
    let total = 0;
    document.querySelectorAll('.panier-item').forEach(item => {
        const prix   = parseFloat(item.dataset.prix   || 0);
        const qty    = parseInt(item.querySelector('.qty-display')?.textContent || 1);
        const sub    = prix * qty;
        total       += sub;
        const subEl  = item.querySelector('.item-subtotal');
        if (subEl)   subEl.textContent = sub.toFixed(2) + ' MAD';
    });

    const ht  = total;
    const tva = ht * 0.20;
    const ttc = ht + tva;

    const elHt  = document.getElementById('total-ht');
    const elTva = document.getElementById('total-tva');
    const elTtc = document.getElementById('total-ttc');

    if (elHt)  elHt.textContent  = ht.toFixed(2)  + ' MAD';
    if (elTva) elTva.textContent = tva.toFixed(2) + ' MAD';
    if (elTtc) elTtc.textContent = ttc.toFixed(2) + ' MAD';
}
</script>
{{-- @endpush --}}


{{-- ============================================================
     commandes/checkout.blade.php — Sélection mode paiement
============================================================ --}}
{{-- @push('scripts') --}}
<script>
// Sélection visuelle méthode paiement
document.querySelectorAll('.methode-card').forEach(card => {
    card.addEventListener('click', () => {
        document.querySelectorAll('.methode-card')
            .forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        card.querySelector('input[type="radio"]').checked = true;

        // Afficher info spécifique selon méthode
        const methode  = card.querySelector('input').value;
        const infoVirement = document.getElementById('info-virement');
        if (infoVirement) {
            infoVirement.style.display = methode === 'virement' ? '' : 'none';
        }
    });
});
</script>
{{-- @endpush --}}


{{-- ============================================================
     commandes/show.blade.php — Timeline tracking
============================================================ --}}
{{-- @push('scripts') --}}
<script>
// Animer la barre de progression de tracking
document.querySelectorAll('.tracking-dot.done, .tracking-dot.active').forEach((dot, i) => {
    setTimeout(() => {
        dot.style.transform = 'scale(1.15)';
        setTimeout(() => { dot.style.transform = 'scale(1)'; }, 200);
    }, i * 150);
});
</script>
{{-- @endpush --}}


{{-- ============================================================
     formations/show.blade.php — Système d'onglets + inscription
============================================================ --}}
{{-- @push('scripts') --}}
<script>
// Onglets formation
const tabLinks  = document.querySelectorAll('.tab-nav a');
const tabPanels = document.querySelectorAll('[id^="tab-"]');

tabLinks.forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();
        const target = link.getAttribute('data-tab');

        // Navigation active
        tabLinks.forEach(l => l.classList.remove('active'));
        link.classList.add('active');

        // Afficher panneau cible
        tabPanels.forEach(p => {
            p.style.display = p.id === 'tab-' + target ? '' : 'none';
        });
    });
});

// Ouvrir onglet via URL hash
const hash = window.location.hash.replace('#', '');
if (hash) {
    const link = document.querySelector(`.tab-nav a[data-tab="${hash}"]`);
    if (link) link.click();
}

// Bouton inscription — désactiver si déjà cliqué
const btnInscrire = document.querySelector('form[action*="inscrire"] button');
if (btnInscrire) {
    btnInscrire.closest('form').addEventListener('submit', function () {
        btnInscrire.textContent = '⏳ Inscription en cours…';
        btnInscrire.disabled    = true;
    });
}
</script>
{{-- @endpush --}}


{{-- ============================================================
     formations/mes-inscriptions.blade.php — Filtres statut
============================================================ --}}
{{-- @push('scripts') --}}
<script>
// Animer les barres de progression
document.querySelectorAll('[data-progress]').forEach(bar => {
    const target = parseInt(bar.dataset.progress) || 0;
    bar.style.width = '0%';
    setTimeout(() => {
        bar.style.transition = 'width 0.8s ease';
        bar.style.width      = target + '%';
    }, 300);
});
</script>
{{-- @endpush --}}


{{-- ============================================================
     artisan/dashboard.blade.php — Graphique revenus
============================================================ --}}
{{-- @push('scripts') --}}
<script>
// Animer les barres du graphique revenus
document.querySelectorAll('.bar-revenus').forEach((bar, i) => {
    const h    = bar.dataset.height || '0%';
    bar.style.height = '0%';
    setTimeout(() => {
        bar.style.transition = 'height 0.6s ease';
        bar.style.height     = h;
    }, i * 80);
});
</script>
{{-- @endpush --}}


{{-- ============================================================
     artisan/produit_form.blade.php — Preview images upload
============================================================ --}}
{{-- @push('scripts') --}}
<script>
// Preview images avant upload
function previewImages(input) {
    const preview = document.getElementById('imagePreview');
    if (!preview) return;
    preview.innerHTML = '';

    Array.from(input.files).slice(0, 5).forEach((file, i) => {
        const reader = new FileReader();
        reader.onload = e => {
            const wrapper = document.createElement('div');
            wrapper.style.cssText =
                'width:80px;height:80px;border-radius:8px;overflow:hidden;' +
                'border:2px solid var(--or);position:relative;display:inline-block;margin:4px;';
            wrapper.innerHTML = `
                <img src="${e.target.result}"
                     style="width:100%;height:100%;object-fit:cover;">
                <span style="position:absolute;top:2px;right:4px;
                             background:rgba(0,0,0,0.5);color:white;
                             border-radius:50%;width:18px;height:18px;
                             display:flex;align-items:center;justify-content:center;
                             font-size:10px;cursor:pointer;"
                      onclick="this.closest('div').remove()">×</span>
            `;
            preview.appendChild(wrapper);
        };
        reader.readAsDataURL(file);
    });
}

// Zone de drop pour images
const dropZone = document.querySelector('.drop-zone');
if (dropZone) {
    ['dragenter','dragover'].forEach(evt =>
        dropZone.addEventListener(evt, e => {
            e.preventDefault();
            dropZone.style.borderColor = 'var(--or)';
            dropZone.style.background  = 'var(--sable)';
        })
    );
    dropZone.addEventListener('dragleave', () => {
        dropZone.style.borderColor = 'var(--sable-dark)';
        dropZone.style.background  = 'transparent';
    });
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        const input = document.getElementById('imagesInput');
        if (input) {
            input.files = e.dataTransfer.files;
            previewImages(input);
        }
        dropZone.style.borderColor = 'var(--sable-dark)';
        dropZone.style.background  = 'transparent';
    });
}
</script>
{{-- @endpush --}}


{{-- ============================================================
     auth/register.blade.php — Sélection rôle visuelle
============================================================ --}}
{{-- @push('scripts') --}}
<script>
// Sélection rôles
document.querySelectorAll('.role-card').forEach(card => {
    card.addEventListener('click', () => {
        document.querySelectorAll('.role-card')
            .forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        card.querySelector('input[type="radio"]').checked = true;
        const role = card.querySelector('input').value;
        const fieldSpec = document.getElementById('specialiteField');
        if (fieldSpec) fieldSpec.style.display = role === 'artisan' ? '' : 'none';
    });
});

// Validation mot de passe en temps réel
const pwd     = document.querySelector('[name="password"]');
const pwdConf = document.querySelector('[name="password_confirmation"]');
if (pwd && pwdConf) {
    pwdConf.addEventListener('input', function () {
        this.style.borderColor = this.value === pwd.value
            ? 'var(--vert-atlas)'
            : 'var(--rouge-fes)';
    });
}
</script>
{{-- @endpush --}}


{{-- ============================================================
     admin/dashboard.blade.php — Stats + Actions rapides
============================================================ --}}
{{-- @push('scripts') --}}
<script>
// Animer les valeurs statistiques
document.querySelectorAll('.stat-val').forEach(el => {
    const raw    = el.textContent.trim().replace(/\s/g, '');
    const num    = parseInt(raw.replace(/\D/g, ''));
    const suffix = raw.replace(/[0-9,]/g, '');
    if (!num || isNaN(num)) return;

    let current  = 0;
    const step   = Math.max(1, Math.ceil(num / 30));
    const timer  = setInterval(() => {
        current  = Math.min(current + step, num);
        el.textContent = current.toLocaleString('fr-MA') + suffix;
        if (current >= num) clearInterval(timer);
    }, 40);
});

// Sidebar mobile toggle
const menuBtn = document.getElementById('sidebarToggle');
const sidebar = document.querySelector('.admin-sidebar');
if (menuBtn && sidebar) {
    menuBtn.addEventListener('click', () => sidebar.classList.toggle('show'));
    document.addEventListener('click', e => {
        if (!sidebar.contains(e.target) && e.target !== menuBtn) {
            sidebar.classList.remove('show');
        }
    });
}
</script>
{{-- @endpush --}}


{{-- ============================================================
     admin/commandes.blade.php — Changement statut inline
============================================================ --}}
{{-- @push('scripts') --}}
<script>
// Soumission automatique au changement de statut
document.querySelectorAll('.statut-select').forEach(sel => {
    sel.addEventListener('change', function () {
        this.closest('form')?.submit();
    });
});
</script>
{{-- @endpush --}}


{{-- ============================================================
     notifications/index.blade.php — Marquer tout lu
============================================================ --}}
{{-- @push('scripts') --}}
<script>
// Marquer une notif comme lue sans rechargement (optionnel)
document.querySelectorAll('.btn-notif-lire').forEach(btn => {
    btn.addEventListener('click', function (e) {
        const row = this.closest('.notif-row');
        if (row) {
            row.style.background = 'white';
            row.style.borderColor = 'var(--sable-dark)';
        }
    });
});
</script>
{{-- @endpush --}}


{{-- ============================================================
     support/index.blade.php — Confirmation envoi ticket
============================================================ --}}
{{-- @push('scripts') --}}
<script>
// Compteur caractères description
const descArea = document.querySelector('[name="description"]');
if (descArea) {
    const counter = document.createElement('div');
    counter.style.cssText = 'font-size:12px;color:var(--gris-doux);text-align:right;margin-top:4px;';
    descArea.parentNode.appendChild(counter);

    function updateCounter() {
        const len = descArea.value.length;
        const max = 2000;
        counter.textContent = `${len} / ${max} caractères`;
        counter.style.color = len > max * 0.9 ? 'var(--rouge-fes)' : 'var(--gris-doux)';
    }
    descArea.addEventListener('input', updateCounter);
    updateCounter();
}
</script>
{{-- @endpush --}}


{{-- ============================================================
     profile.blade.php — Aperçu avatar upload
============================================================ --}}
{{-- @push('scripts') --}}
<script>
// Aperçu avatar
const avatarInput = document.querySelector('[name="avatar"]');
if (avatarInput) {
    avatarInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            const avatarEl = document.querySelector('.avatar-preview');
            if (avatarEl) {
                avatarEl.innerHTML = `<img src="${e.target.result}"
                    style="width:100%;height:100%;border-radius:50%;object-fit:cover;">`;
            }
        };
        reader.readAsDataURL(file);
    });
}

// Indicateur force mot de passe
const pwdInput = document.querySelector('[name="password"]');
if (pwdInput) {
    const bar = document.getElementById('pwd-strength');
    pwdInput.addEventListener('input', function () {
        if (!bar) return;
        const v   = this.value;
        let score = 0;
        if (v.length >= 8)              score++;
        if (/[A-Z]/.test(v))           score++;
        if (/[0-9]/.test(v))           score++;
        if (/[^A-Za-z0-9]/.test(v))    score++;

        const colors = ['var(--rouge-fes)','#f59e0b','var(--or)','var(--vert-atlas)'];
        const widths = ['25%','50%','75%','100%'];
        bar.style.width      = widths[score - 1] || '0%';
        bar.style.background = colors[score - 1] || 'var(--sable-dark)';
    });
}
</script>
{{-- @endpush --}}