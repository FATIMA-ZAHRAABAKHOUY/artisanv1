-- ═══════════════════════════════════════════════════════════════
-- Données de test — catalogue fournisseur « fourni » (id = 5)
-- Formation id = 2 (Tissage / laine)
--
-- Exécuter : php sql/seed_fournisseur_catalog.php
-- Ou coller dans pgAdmin / psql
-- ═══════════════════════════════════════════════════════════════

-- ── MATÉRIAUX (fournisseur_materiaux) ──────────────────────────

INSERT INTO fournisseur_materiaux (
    materiau_id, fournisseur_id, nom_produit_fournisseur, reference_produit,
    prix_unitaire, unite_prix, url_produit,
    delai_livraison_min, delai_livraison_max,
    est_recommande, stock_disponible, notes_apprenant,
    created_at, updated_at
) VALUES
(
    1, 5,
    'Laine naturelle brute — qualité coopérative',
    'LN-001',
    85.00, 'kg',
    'https://example.ma/produits/laine-naturelle',
    2, 5,
    TRUE, TRUE,
    'Fil recommandé pour débuter la formation tissage.',
    NOW(), NOW()
),
(
    2, 5,
    'Fil de chaîne coton 100% — bobine 500g',
    'FC-500',
    45.00, 'bobine',
    'https://example.ma/produits/fil-chaine-coton',
    1, 3,
    FALSE, TRUE,
    NULL,
    NOW(), NOW()
),
(
    3, 5,
    'Laine teinte — palette couleur 1 (bordeaux)',
    'LT-C1',
    120.00, 'kg',
    'https://example.ma/produits/laine-teinte-c1',
    3, 7,
    TRUE, TRUE,
    'Couleur fidèle aux échantillons de la formation.',
    NOW(), NOW()
);

-- ── OUTILS (fournisseur_outils) ────────────────────────────────
-- Colonnes réelles en base : outil_id, fournisseur_id,
-- nom_produit_fournisseur, prix_unitaire, est_recommande, url_produit

INSERT INTO fournisseur_outils (
    outil_id, fournisseur_id, nom_produit_fournisseur,
    prix_unitaire, est_recommande, url_produit,
    created_at, updated_at
) VALUES
(
    1, 5,
    'Ciseaux de couture professionnels 21 cm',
    95.00, TRUE,
    'https://example.ma/produits/ciseaux-couture',
    NOW(), NOW()
),
(
    2, 5,
    'Navette de tissage en bois de thuya',
    35.00, FALSE,
    'https://example.ma/produits/navette-bois',
    NOW(), NOW()
),
(
    4, 5,
    'Aiguille à laine grande taille — lot de 3',
    18.00, TRUE,
    'https://example.ma/produits/aiguille-laine',
    NOW(), NOW()
);

-- ── Vérification ───────────────────────────────────────────────
-- SELECT fm.id, fm.nom_produit_fournisseur, mf.nom AS materiau_formation
-- FROM fournisseur_materiaux fm
-- JOIN materiaux_formation mf ON mf.id = fm.materiau_id
-- WHERE fm.fournisseur_id = 5;
--
-- SELECT fo.id, fo.nom_produit_fournisseur, of2.nom AS outil_formation
-- FROM fournisseur_outils fo
-- JOIN outils_formation of2 ON of2.id = fo.outil_id
-- WHERE fo.fournisseur_id = 5;
