import { useRef, useState } from 'react';

// ─── Design tokens ───────────────────────────────────────────────────────────
const C = {
    bg: '#0d1117',
    panel: '#161b27',
    border: '#2a3347',
    accent: '#c8a96e',
    green: '#4db88c',
    red: '#c06b5a',
    blue: '#6b8cba',
    purple: '#9b7eb8',
    teal: '#4aabb8',
    orange: '#c88c4d',
    white: '#f0e6d3',
    muted: '#7a8ba8',
    text: '#c8d4e8',
};

const MOD = {
    auth: { fill: '#6b8cba', label: 'Auth / Users' },
    artisan: { fill: '#c8a96e', label: 'Artisans' },
    produit: { fill: '#4db88c', label: 'Produits' },
    commande: { fill: '#c06b5a', label: 'Commandes' },
    livraison: { fill: '#c88c4d', label: 'Livraisons' },
    formation: { fill: '#9b7eb8', label: 'Formations' },
    fournisseur: { fill: '#4aabb8', label: 'Fournisseurs' },
    support: { fill: '#7a8ba8', label: 'Support / Notif' },
};

const BOX_W = 260;
const TITLE_H = 26;
const ATTR_H = 16;
const METH_H = 16;
const PAD = 6;
const TITLE_BAR_OFFSET = 30;
const TOTAL_W = 1620;
const TOTAL_H = 2920;
const INITIAL_SCALE = 0.55;

// ─── Class data (25 classes) ─────────────────────────────────────────────────
const CLASSES = [
    {
        name: 'User',
        mod: 'auth',
        x: 30,
        y: 30,
        attrs: [
            'id : PK',
            'nom, prenom',
            'email : UNIQUE',
            'password, telephone',
            'adresse, ville',
            'role : enum(client/artisan/admin/livreur/apprenant)',
            'statut : enum(actif/inactif/suspendu)',
            'avatar, email_verified_at',
        ],
        meths: ['+ seConnecter() : bool', '+ seDeconnecter() : void', '+ modifierProfil() : void'],
    },
    {
        name: 'Artisan',
        mod: 'artisan',
        x: 420,
        y: 30,
        attrs: [
            'id : PK',
            'user_id : FK',
            'specialite, experience_annees',
            'cin : UNIQUE, bio, rib',
            'note_moyenne : decimal(3,2)',
            'statut : enum(actif/suspendu/formation/inactif)',
            'date_adhesion, is_verified : bool',
        ],
        meths: ['+ publierProduit() : void', '+ creerFormation() : void', '+ voirRevenus() : decimal'],
    },
    {
        name: 'Formateur',
        mod: 'artisan',
        x: 820,
        y: 30,
        attrs: [
            'id : PK',
            'user_id : FK',
            'artisan_id : FK nullable',
            'biographie, specialite, diplomes',
            'langues, experience_annees',
            'est_externe : bool, organisme',
            'tarif_journee : decimal',
            'is_disponible : bool',
        ],
        meths: ['+ animer() : void', '+ creerContenu() : void'],
    },
    {
        name: 'Categorie',
        mod: 'produit',
        x: 30,
        y: 420,
        attrs: ['id : PK', 'nom, description, image', 'parent_id : FK self', 'slug : UNIQUE'],
        meths: ['+ getSousCategories() : list'],
    },
    {
        name: 'Produit',
        mod: 'produit',
        x: 420,
        y: 420,
        attrs: [
            'id : PK',
            'artisan_id : FK',
            'categorie_id : FK',
            'nom, description',
            'prix : decimal, stock : int',
            'images : json[], poids : decimal',
            'dimensions, is_active : bool',
            'slug : UNIQUE',
        ],
        meths: [
            '+ verifierStock(qte) : bool',
            '+ diminuerStock(qte) : void',
            '+ augmenterStock(qte) : void',
        ],
    },
    {
        name: 'Avis',
        mod: 'produit',
        x: 820,
        y: 420,
        attrs: [
            'id : PK',
            'produit_id : FK',
            'client_id : FK',
            'note : smallint(1-5)',
            'commentaire, created_at',
        ],
        meths: ['+ calculerMoyenne() : decimal'],
    },
    {
        name: 'Commande',
        mod: 'commande',
        x: 30,
        y: 800,
        attrs: [
            'id : PK',
            'client_id : FK',
            'statut : enum(pending/confirmed/processing/shipped/delivered/cancelled)',
            'adresse_livraison, ville, code_postal',
            'total_ht : decimal, tva : decimal',
            'total_ttc : decimal, notes',
        ],
        meths: [
            '+ calculerTotal() : decimal',
            '+ valider() : bool',
            '+ annuler() : void',
            '+ changerStatut(s) : void',
        ],
    },
    {
        name: 'LigneCommande',
        mod: 'commande',
        x: 420,
        y: 800,
        attrs: [
            'id : PK',
            'commande_id : FK',
            'produit_id : FK',
            'quantite : int>0',
            'prix_unitaire : decimal>0',
            'remise : decimal(0-1)',
            'sous_total : decimal',
        ],
        meths: ['+ calculerSousTotal() : decimal {= qte*prix*(1-remise)}'],
    },
    {
        name: 'Paiement',
        mod: 'commande',
        x: 820,
        y: 800,
        attrs: [
            'id : PK',
            'commande_id : FK UNIQUE',
            'methode : enum(carte/livraison/virement/cmi)',
            'statut : enum(pending/paid/failed/refunded)',
            'montant : decimal',
            'reference : UNIQUE',
            'gateway_data : json',
            'paid_at : timestamp',
        ],
        meths: ['+ effectuer() : void', '+ confirmer() : void', '+ rembourser() : void'],
    },
    {
        name: 'Livraison',
        mod: 'livraison',
        x: 30,
        y: 1180,
        attrs: [
            'id : PK',
            'commande_id : FK UNIQUE',
            'livreur_id : FK nullable',
            'adresse_livraison, ville',
            'telephone_recepteur, transporteur',
            'numero_suivi : UNIQUE',
            'statut : enum(preparee/expediee/en_transit/livree/retournee)',
            'date_expedition, date_livraison_prevue',
            'date_livraison_reelle',
            'frais_livraison : decimal',
            'preuve_livraison_url',
        ],
        meths: [
            '+ assignerLivreur(id) : void',
            '+ mettreAJourStatut(s) : void',
            '+ confirmerLivraison() : void',
        ],
    },
    {
        name: 'LivraisonHistorique',
        mod: 'livraison',
        x: 420,
        y: 1180,
        attrs: [
            'id : PK',
            'livraison_id : FK',
            'statut, commentaire',
            'localisation',
            'changed_by : FK',
            'created_at',
        ],
        meths: ['+ enregistrer() : void'],
    },
    {
        name: 'Formation',
        mod: 'formation',
        x: 820,
        y: 1100,
        attrs: [
            'id : PK',
            'artisan_id : FK',
            'titre, description',
            'date_debut, date_fin',
            'prix : decimal',
            'places_max : int',
            'lieu, image',
            'is_active : bool',
        ],
        meths: [
            '+ ajouterFormation() : void',
            '+ inscrireApprenant() : void',
            '+ getPlacesDisponibles() : int',
        ],
    },
    {
        name: 'InscriptionFormation',
        mod: 'formation',
        x: 1210,
        y: 1100,
        attrs: [
            'id : PK',
            'formation_id : FK',
            'apprenant_id : FK',
            'statut_inscription : enum(en_cours/terminee/abandonnee/suspendue)',
            'progression : smallint(0-100)',
            'note_finale : decimal(0-20)',
            'date_inscription',
            'date_debut_reelle, date_fin_reelle',
            'certificat_url',
        ],
        meths: ['+ mettreAJourProgression() : void', '+ genererCertificat() : void'],
    },
    {
        name: 'FormationFormateur',
        mod: 'formation',
        x: 1210,
        y: 1320,
        attrs: [
            'id : PK',
            'formation_id : FK',
            'formateur_id : FK',
            'role : enum(principal/assistant/intervenant)',
        ],
        meths: [],
    },
    {
        name: 'MateriauFormation',
        mod: 'formation',
        x: 820,
        y: 1560,
        attrs: [
            'id : PK',
            'formation_id : FK',
            'nom',
            'type : enum(fil/laine/coton/soie/lin/autre)',
            'couleur, quantite : decimal',
            'unite : enum(metre/gramme/pelote/bobine/piece)',
            'est_fourni : bool, image',
            'ordre : smallint',
        ],
        meths: [],
    },
    {
        name: 'OutilFormation',
        mod: 'formation',
        x: 1210,
        y: 1560,
        attrs: [
            'id : PK',
            'formation_id : FK',
            'nom, description',
            'quantite : int',
            'est_fourni : bool, image',
            'lien_achat',
            'ordre : smallint',
        ],
        meths: [],
    },
    {
        name: 'RessourceFormation',
        mod: 'formation',
        x: 820,
        y: 1880,
        attrs: [
            'id : PK',
            'formation_id : FK',
            'type : enum(video/document_pdf/image)',
            'titre, url',
            'duree_secondes : int',
            'est_public : bool',
            'ordre : smallint',
        ],
        meths: [],
    },
    {
        name: 'EtapeFormation',
        mod: 'formation',
        x: 1210,
        y: 1880,
        attrs: [
            'id : PK',
            'formation_id : FK',
            'numero_ordre : smallint UNIQUE',
            'titre, description',
            'duree_minutes : int',
            'objectif, materiaux_requis',
        ],
        meths: [],
    },
    {
        name: 'Fournisseur',
        mod: 'fournisseur',
        x: 30,
        y: 1600,
        attrs: [
            'id : PK',
            'nom',
            'type : enum(local/national/en_ligne)',
            'statut, email, telephone',
            'whatsapp, adresse, ville',
            'site_web, logo',
            'remise_cooperative : decimal',
            'delai_livraison_min : int',
            'delai_livraison_max : int',
            'note_moyenne : decimal',
        ],
        meths: ['+ getSuggestions() : list'],
    },
    {
        name: 'FournisseurMateriau',
        mod: 'fournisseur',
        x: 420,
        y: 1600,
        attrs: [
            'id : PK',
            'materiau_id : FK',
            'fournisseur_id : FK',
            'nom_produit, reference_produit',
            'prix_unitaire : decimal',
            'unite_prix, url_produit',
            'delai_livraison_min/max',
            'est_recommande : bool',
            'stock_disponible : bool',
            'notes_apprenant',
        ],
        meths: [],
    },
    {
        name: 'FournisseurOutil',
        mod: 'fournisseur',
        x: 30,
        y: 1960,
        attrs: [
            'id : PK',
            'outil_id : FK',
            'fournisseur_id : FK',
            'nom_produit, reference_produit',
            'prix_unitaire : decimal',
            'unite_prix, url_produit',
            'est_recommande : bool',
            'stock_disponible : bool',
            'notes_apprenant',
        ],
        meths: [],
    },
    {
        name: 'Approvisionnement',
        mod: 'fournisseur',
        x: 420,
        y: 1960,
        attrs: [
            'id : PK',
            'artisan_id : FK',
            'fournisseur_id : FK',
            'est_principal : bool',
            'notes',
        ],
        meths: [],
    },
    {
        name: 'SuggestionAchat',
        mod: 'fournisseur',
        x: 30,
        y: 2240,
        attrs: [
            'id : PK',
            'apprenant_id : FK',
            'formation_id : FK',
            'fournisseur_id : FK',
            'type_objet : enum(materiau/outil)',
            'objet_id : int',
            'est_clique : bool',
            'est_achete : bool',
            'created_at',
        ],
        meths: [],
    },
    {
        name: 'Notification',
        mod: 'support',
        x: 420,
        y: 2240,
        attrs: [
            'id : PK',
            'user_id : FK',
            'type, titre, message',
            'data : json',
            'is_read : bool',
            'created_at',
        ],
        meths: ['+ marquerLue() : void', '+ envoyerPush() : void'],
    },
    {
        name: 'Support',
        mod: 'support',
        x: 820,
        y: 2240,
        attrs: [
            'id : PK',
            'user_id : FK',
            'objet, description',
            'statut : enum(ouvert/en_cours/resolu/ferme)',
            'colis_id : FK nullable',
            'created_at',
        ],
        meths: ['+ ouvrir() : void', '+ resoudre() : void'],
    },
    {
        name: 'PersonalAccessToken',
        mod: 'support',
        x: 1210,
        y: 2240,
        attrs: [
            'id : bigint PK',
            'tokenable_type',
            'tokenable_id : bigint',
            'name',
            'token : UNIQUE',
            'abilities',
            'last_used_at',
            'expires_at',
        ],
        meths: [],
    },
];

// ─── Relations (28) ──────────────────────────────────────────────────────────
const RELATIONS = [
    { from: 'User', to: 'Artisan', type: 'comp', label: '1     1', fx: 0.8, fy: 0.5, tx: 0.0, ty: 0.3 },
    { from: 'User', to: 'Formateur', type: 'assoc', label: '1     0..1', fx: 1.0, fy: 0.3, tx: 0.0, ty: 0.3 },
    { from: 'Artisan', to: 'Formateur', type: 'assoc', label: '0..1  1', fx: 1.0, fy: 0.5, tx: 0.0, ty: 0.5 },
    { from: 'Artisan', to: 'Produit', type: 'comp', label: '1     *', fx: 0.5, fy: 1.0, tx: 0.5, ty: 0.0 },
    { from: 'Artisan', to: 'Formation', type: 'comp', label: '1     *', fx: 1.0, fy: 0.8, tx: 0.0, ty: 0.3 },
    { from: 'Produit', to: 'Categorie', type: 'assoc', label: '*     1', fx: 0.0, fy: 0.5, tx: 1.0, ty: 0.5 },
    { from: 'Produit', to: 'Avis', type: 'comp', label: '1     *', fx: 1.0, fy: 0.5, tx: 0.0, ty: 0.5 },
    { from: 'User', to: 'Commande', type: 'assoc', label: '1     *', fx: 0.2, fy: 1.0, tx: 0.2, ty: 0.0 },
    { from: 'Commande', to: 'LigneCommande', type: 'comp', label: '1     1..*', fx: 1.0, fy: 0.5, tx: 0.0, ty: 0.5 },
    { from: 'LigneCommande', to: 'Produit', type: 'assoc', label: '*     1', fx: 0.5, fy: 0.0, tx: 0.5, ty: 1.0 },
    { from: 'Commande', to: 'Paiement', type: 'assoc', label: '1     1', fx: 0.5, fy: 1.0, tx: 0.5, ty: 0.0 },
    { from: 'Commande', to: 'Livraison', type: 'comp', label: '1     1', fx: 0.0, fy: 0.8, tx: 1.0, ty: 0.2 },
    { from: 'Livraison', to: 'LivraisonHistorique', type: 'comp', label: '1     *', fx: 1.0, fy: 0.5, tx: 0.0, ty: 0.5 },
    { from: 'Formation', to: 'InscriptionFormation', type: 'comp', label: '1     *', fx: 1.0, fy: 0.5, tx: 0.0, ty: 0.5 },
    { from: 'Formation', to: 'MateriauFormation', type: 'comp', label: '1     *', fx: 0.3, fy: 1.0, tx: 0.3, ty: 0.0 },
    { from: 'Formation', to: 'OutilFormation', type: 'comp', label: '1     *', fx: 0.7, fy: 1.0, tx: 0.3, ty: 0.0 },
    { from: 'Formation', to: 'RessourceFormation', type: 'comp', label: '1     *', fx: 0.2, fy: 1.0, tx: 0.2, ty: 0.0 },
    { from: 'Formation', to: 'EtapeFormation', type: 'comp', label: '1     *', fx: 0.8, fy: 1.0, tx: 0.2, ty: 0.0 },
    { from: 'Formation', to: 'FormationFormateur', type: 'comp', label: '1     *', fx: 1.0, fy: 0.2, tx: 0.0, ty: 0.5 },
    { from: 'Formateur', to: 'FormationFormateur', type: 'assoc', label: '1     *', fx: 0.5, fy: 1.0, tx: 0.5, ty: 0.0 },
    { from: 'MateriauFormation', to: 'FournisseurMateriau', type: 'assoc', label: '*     *', fx: 0.0, fy: 0.5, tx: 1.0, ty: 0.3 },
    { from: 'OutilFormation', to: 'FournisseurOutil', type: 'assoc', label: '*     *', fx: 0.0, fy: 0.5, tx: 1.0, ty: 0.3 },
    { from: 'Fournisseur', to: 'FournisseurMateriau', type: 'assoc', label: '1     *', fx: 1.0, fy: 0.4, tx: 0.0, ty: 0.4 },
    { from: 'Fournisseur', to: 'FournisseurOutil', type: 'assoc', label: '1     *', fx: 0.3, fy: 1.0, tx: 0.3, ty: 0.0 },
    { from: 'Artisan', to: 'Approvisionnement', type: 'assoc', label: '1     *', fx: 0.3, fy: 1.0, tx: 0.3, ty: 0.0 },
    { from: 'Fournisseur', to: 'Approvisionnement', type: 'assoc', label: '1     *', fx: 1.0, fy: 0.7, tx: 0.0, ty: 0.5 },
    { from: 'User', to: 'Notification', type: 'comp', label: '1     *', fx: 0.9, fy: 1.0, tx: 0.5, ty: 0.0 },
    { from: 'User', to: 'Support', type: 'assoc', label: '1     *', fx: 1.0, fy: 0.9, tx: 0.5, ty: 0.0 },
];

const OCL_RULES = [
    { x: 30, y: 2500, text: 'Un apprenant a max 1 formation en_cours — INDEX UNIQUE PARTIEL' },
    { x: 480, y: 2500, text: 'sousTotal = quantite * prixUnitaire * (1 - remise)' },
    { x: 930, y: 2500, text: 'totalTTC = Σ(sousTotaux) * (1+tva) | cancelled → immuable' },
    { x: 30, y: 2640, text: 'dateDebut < dateFin | nb_inscrits ≤ placesMax' },
    { x: 480, y: 2640, text: "stock ≥ 0 | prix ≥ 0 | artisan.statut='actif' pour publier" },
    { x: 930, y: 2640, text: 'livree → immuable | date_reelle ≥ date_expedition' },
    { x: 30, y: 2780, text: "type='en_ligne' → site_web obligatoire" },
    { x: 480, y: 2780, text: 'est_externe=FALSE → artisan_id NOT NULL (et vice versa)' },
];

// ─── Helpers ─────────────────────────────────────────────────────────────────
const classMap = Object.fromEntries(CLASSES.map((c) => [c.name, c]));

function boxHeight(cls) {
    const methBlock = cls.meths.length > 0 ? PAD + cls.meths.length * METH_H + PAD : PAD;
    return TITLE_H + PAD + cls.attrs.length * ATTR_H + methBlock;
}

function anchorPoint(cls, fx, fy) {
    return {
        x: cls.x + fx * BOX_W,
        y: cls.y + TITLE_BAR_OFFSET + fy * boxHeight(cls),
    };
}

function relStyle(type) {
    switch (type) {
        case 'comp':
            return { stroke: C.accent, markerEnd: 'url(#rel-comp)', markerStart: 'url(#diamond)' };
        case 'inh':
            return { stroke: C.blue, markerEnd: 'url(#rel-inh)' };
        case 'dep':
            return { stroke: C.muted, strokeDasharray: '6 4', markerEnd: 'url(#rel-dep)' };
        default:
            return { stroke: C.muted, markerEnd: 'url(#rel-assoc)' };
    }
}

// ─── SVG sub-components ──────────────────────────────────────────────────────
function Grid() {
    const lines = [];
    for (let x = 0; x <= TOTAL_W; x += 60) {
        lines.push(<line key={`v${x}`} x1={x} y1={0} x2={x} y2={TOTAL_H} stroke={C.border} strokeWidth={0.3} opacity={0.3} />);
    }
    for (let y = 0; y <= TOTAL_H; y += 60) {
        lines.push(<line key={`h${y}`} x1={0} y1={y} x2={TOTAL_W} y2={y} stroke={C.border} strokeWidth={0.3} opacity={0.3} />);
    }
    return <g>{lines}</g>;
}

function ClassBox({ cls }) {
    const color = MOD[cls.mod].fill;
    const h = boxHeight(cls);
    const x = cls.x;
    const y = cls.y + TITLE_BAR_OFFSET;
    const bodyH = h - TITLE_H;

    return (
        <g>
            <rect x={x + 4} y={y + 4} width={BOX_W} height={h} fill="#000" opacity={0.35} rx={4} />
            <rect x={x} y={y} width={BOX_W} height={h} fill={C.panel} stroke={color} strokeWidth={2} rx={4} />
            <rect x={x} y={y} width={BOX_W} height={TITLE_H} fill={color} rx={4} />
            <rect x={x} y={y + TITLE_H - 2} width={BOX_W} height={4} fill={color} />
            <text x={x + BOX_W / 2} y={y + 17} textAnchor="middle" fill={C.bg} fontSize={12} fontWeight="bold" fontFamily="monospace">
                {cls.name}
            </text>
            {cls.attrs.map((a, i) => (
                <text key={`a${i}`} x={x + 8} y={y + TITLE_H + PAD + (i + 1) * ATTR_H - 4} fill={C.text} fontSize={9.5} fontFamily="monospace">
                    {a}
                </text>
            ))}
            {cls.meths.length > 0 && (
                <>
                    <line x1={x + 4} y1={y + TITLE_H + PAD + cls.attrs.length * ATTR_H + 2} x2={x + BOX_W - 4} y2={y + TITLE_H + PAD + cls.attrs.length * ATTR_H + 2} stroke={color} opacity={0.5} />
                    {cls.meths.map((m, i) => (
                        <text key={`m${i}`} x={x + 8} y={y + TITLE_H + PAD + cls.attrs.length * ATTR_H + PAD + (i + 1) * METH_H + 2} fill={color} fontSize={9.5} fontFamily="monospace" fontStyle="italic">
                            {m}
                        </text>
                    ))}
                </>
            )}
        </g>
    );
}

function Relation({ rel }) {
    const from = classMap[rel.from];
    const to = classMap[rel.to];
    if (!from || !to) return null;

    const p1 = anchorPoint(from, rel.fx, rel.fy);
    const p2 = anchorPoint(to, rel.tx, rel.ty);
    const style = relStyle(rel.type);
    const mx = (p1.x + p2.x) / 2;
    const my = (p1.y + p2.y) / 2;
    const lw = rel.label.length * 5.5 + 12;

    return (
        <g>
            <line x1={p1.x} y1={p1.y} x2={p2.x} y2={p2.y} strokeWidth={1.5} fill="none" {...style} />
            <rect x={mx - lw / 2} y={my - 9} width={lw} height={14} fill={C.bg} opacity={0.85} rx={2} />
            <text x={mx} y={my + 1} textAnchor="middle" fill={C.muted} fontSize={8} fontFamily="monospace">
                {rel.label}
            </text>
        </g>
    );
}

function OclBox({ rule, index }) {
    const w = 420;
    const h = 52;
    return (
        <g>
            <rect x={rule.x} y={rule.y} width={w} height={h} fill={C.panel} stroke={C.red} strokeWidth={1.5} strokeDasharray="6 3" rx={4} />
            <text x={rule.x + 8} y={rule.y + 16} fill={C.red} fontSize={10} fontWeight="bold" fontFamily="monospace">
                {`«OCL» ${index + 1}`}
            </text>
            <text x={rule.x + 8} y={rule.y + 34} fill={C.text} fontSize={9} fontFamily="monospace">
                {rule.text}
            </text>
        </g>
    );
}

function Legend() {
    const x = 1380;
    const y = 36;
    const w = 220;
    const h = 300;

    return (
        <g>
            <rect x={x} y={y} width={w} height={h} fill={C.panel} stroke={C.border} strokeWidth={1} rx={4} />
            <text x={x + 12} y={y + 20} fill={C.white} fontSize={11} fontWeight="bold" fontFamily="monospace">
                LÉGENDE
            </text>
            <text x={x + 12} y={y + 38} fill={C.muted} fontSize={9} fontFamily="monospace">
                Modules
            </text>
            {Object.entries(MOD).map(([key, m], i) => (
                <g key={key}>
                    <rect x={x + 12} y={y + 48 + i * 18} width={14} height={14} fill={m.fill} rx={2} />
                    <text x={x + 32} y={y + 59 + i * 18} fill={C.text} fontSize={9} fontFamily="monospace">
                        {m.label}
                    </text>
                </g>
            ))}
            <text x={x + 12} y={y + 200} fill={C.muted} fontSize={9} fontFamily="monospace">
                Relations
            </text>
            {[
                { label: 'Composition', color: C.accent, dash: '', diamond: true },
                { label: 'Héritage', color: C.blue, dash: '', diamond: false },
                { label: 'Association', color: C.muted, dash: '', diamond: false },
                { label: 'Dépendance', color: C.muted, dash: '4 3', diamond: false },
                { label: 'Contrainte OCL', color: C.red, dash: '4 3', diamond: false, box: true },
            ].map((item, i) => (
                <g key={item.label}>
                    {item.box ? (
                        <rect x={x + 12} y={y + 210 + i * 16} width={40} height={10} fill="none" stroke={item.color} strokeDasharray={item.dash} />
                    ) : (
                        <line x1={x + 12} x2={x + 52} y1={y + 215 + i * 16} y2={y + 215 + i * 16} stroke={item.color} strokeWidth={1.5} strokeDasharray={item.dash} />
                    )}
                    <text x={x + 58} y={y + 219 + i * 16} fill={C.text} fontSize={8.5} fontFamily="monospace">
                        {item.label}
                    </text>
                </g>
            ))}
        </g>
    );
}

function Stats() {
    const x = 1380;
    const y = 350;
    const w = 220;
    const h = 210;
    const items = [
        { n: '25', l: 'Classes' },
        { n: '10', l: 'Modules' },
        { n: '28+', l: 'Relations' },
        { n: '12', l: 'Triggers OCL' },
        { n: '8', l: 'Règles OCL' },
        { n: '20+', l: 'Index SQL' },
    ];

    return (
        <g>
            <rect x={x} y={y} width={w} height={h} fill={C.panel} stroke={C.border} strokeWidth={1} rx={4} />
            <text x={x + 12} y={y + 22} fill={C.white} fontSize={11} fontWeight="bold" fontFamily="monospace">
                STATISTIQUES
            </text>
            {items.map((item, i) => {
                const col = i % 2;
                const row = Math.floor(i / 2);
                return (
                    <g key={item.l}>
                        <text x={x + 20 + col * 100} y={y + 58 + row * 52} fill={C.accent} fontSize={22} fontWeight="bold" fontFamily="monospace">
                            {item.n}
                        </text>
                        <text x={x + 20 + col * 100} y={y + 76 + row * 52} fill={C.muted} fontSize={9} fontFamily="monospace">
                            {item.l}
                        </text>
                    </g>
                );
            })}
        </g>
    );
}

function SvgDefs() {
    return (
        <defs>
            <marker id="rel-assoc" viewBox="0 0 10 10" refX={9} refY={5} markerWidth={8} markerHeight={8} orient="auto-start-reverse">
                <path d="M0,0 L10,5 L0,10 Z" fill={C.muted} />
            </marker>
            <marker id="rel-comp" viewBox="0 0 10 10" refX={9} refY={5} markerWidth={8} markerHeight={8} orient="auto-start-reverse">
                <path d="M0,0 L10,5 L0,10 Z" fill={C.accent} />
            </marker>
            <marker id="rel-inh" viewBox="0 0 12 12" refX={11} refY={6} markerWidth={10} markerHeight={10} orient="auto-start-reverse">
                <path d="M0,0 L12,6 L0,12 Z" fill="none" stroke={C.blue} strokeWidth={1.5} />
            </marker>
            <marker id="rel-dep" viewBox="0 0 10 10" refX={9} refY={5} markerWidth={8} markerHeight={8} orient="auto-start-reverse">
                <path d="M0,0 L10,5 L0,10 Z" fill={C.muted} />
            </marker>
            <marker id="diamond" viewBox="-8 -8 16 16" refX={0} refY={0} markerWidth={10} markerHeight={10} orient="auto">
                <path d="M0,-7 L7,0 L0,7 L-7,0 Z" fill={C.accent} />
            </marker>
        </defs>
    );
}

// ─── Main App ────────────────────────────────────────────────────────────────
export default function App() {
    const [scale, setScale] = useState(INITIAL_SCALE);
    const [showOcl, setShowOcl] = useState(true);
    const svgRef = useRef(null);

    const btnStyle = (active = false) => ({
        background: active ? C.accent : C.panel,
        color: active ? C.bg : C.white,
        border: `1px solid ${C.border}`,
        borderRadius: 4,
        padding: '6px 12px',
        cursor: 'pointer',
        fontFamily: 'monospace',
        fontSize: 12,
    });

    const exportSvg = () => {
        const svg = svgRef.current;
        if (!svg) return;
        const clone = svg.cloneNode(true);
        clone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        const blob = new Blob([new XMLSerializer().serializeToString(clone)], { type: 'image/svg+xml' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'tissu-artisanal-diagramme-classes.svg';
        a.click();
        URL.revokeObjectURL(url);
    };

    return (
        <div style={{ background: C.bg, minHeight: '100vh', fontFamily: 'monospace' }}>
            <div
                style={{
                    display: 'flex',
                    alignItems: 'center',
                    gap: 8,
                    padding: '10px 16px',
                    background: C.panel,
                    borderBottom: `1px solid ${C.border}`,
                    position: 'sticky',
                    top: 0,
                    zIndex: 10,
                }}
            >
                <span style={{ color: C.accent, fontWeight: 'bold', marginRight: 12 }}>🧵 Tissu Artisanal — UML</span>
                <button type="button" style={btnStyle()} onClick={() => setScale((s) => Math.max(0.25, s - 0.1))}>
                    🔍−
                </button>
                <span style={{ color: C.white, minWidth: 44, textAlign: 'center' }}>{Math.round(scale * 100)}%</span>
                <button type="button" style={btnStyle()} onClick={() => setScale((s) => Math.min(1.5, s + 0.1))}>
                    🔍+
                </button>
                <button type="button" style={btnStyle()} onClick={() => setScale(INITIAL_SCALE)}>
                    Reset
                </button>
                <button type="button" style={btnStyle(showOcl)} onClick={() => setShowOcl((v) => !v)}>
                    {showOcl ? 'Masquer OCL' : 'Afficher OCL'}
                </button>
                <button type="button" style={btnStyle()} onClick={exportSvg}>
                    Export SVG
                </button>
            </div>

            <div style={{ overflow: 'auto', padding: 16 }}>
                <div style={{ transform: `scale(${scale})`, transformOrigin: 'top left', width: TOTAL_W * scale }}>
                    <svg ref={svgRef} width={TOTAL_W} height={TOTAL_H} style={{ display: 'block' }}>
                        <SvgDefs />
                        <rect width={TOTAL_W} height={TOTAL_H} fill={C.bg} />
                        <Grid />

                        {/* Title bar */}
                        <rect width={TOTAL_W} height={26} fill={C.accent} />
                        <text x={TOTAL_W / 2} y={18} textAnchor="middle" fill={C.bg} fontSize={13} fontWeight="bold" fontFamily="monospace">
                            Diagramme de classes — Tissu Artisanal (PFE) — 25 classes · 28 relations · 8 contraintes OCL
                        </text>

                        {/* Relations behind boxes */}
                        <g>
                            {RELATIONS.map((rel, i) => (
                                <Relation key={i} rel={rel} />
                            ))}
                        </g>

                        {/* Class boxes */}
                        {CLASSES.map((cls) => (
                            <ClassBox key={cls.name} cls={cls} />
                        ))}

                        <Legend />
                        <Stats />

                        {showOcl && (
                            <g>
                                <line x1={20} y1={2480} x2={TOTAL_W - 20} y2={2480} stroke={C.red} strokeWidth={1} strokeDasharray="8 4" opacity={0.6} />
                                <text x={30} y={2495} fill={C.red} fontSize={11} fontWeight="bold" fontFamily="monospace">
                                    CONTRAINTES OCL (Object Constraint Language)
                                </text>
                                {OCL_RULES.map((rule, i) => (
                                    <OclBox key={i} rule={rule} index={i} />
                                ))}
                            </g>
                        )}
                    </svg>
                </div>
            </div>
        </div>
    );
}
