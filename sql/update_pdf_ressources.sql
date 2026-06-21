-- ================================================================
--  MISE À JOUR DES RESSOURCES PDF — Remplacement des placeholders
--  Prérequis : copier les 4 PDF dans
--  storage/app/public/formations/ressources/
-- ================================================================
-- Placeholders connus en base :
--   A_REMPLACER_PDF_UPLOAD
--   PLACEHOLDER_A_REMPLACER_PAR_UPLOAD_PDF
-- ================================================================

BEGIN;

-- 1. Guide Tapis Beni Ouarain (formation #2)
UPDATE ressources_formation
SET url = 'formations/ressources/guide_tapis_beni_ouarain.pdf',
    nb_pages = 2,
    taille_ko = 180
WHERE url IN ('A_REMPLACER_PDF_UPLOAD', 'PLACEHOLDER_A_REMPLACER_PAR_UPLOAD_PDF')
  AND (
      titre ILIKE '%beni ouarain%'
      OR formation_id = (
          SELECT id FROM formations
          WHERE titre ILIKE '%beni ouarain%' OR titre ILIKE '%tissage berb%'
          LIMIT 1
      )
  );

-- 2. Guide Broderie Fassi (formation à créer ou titre contenant « fassi » / « fès »)
UPDATE ressources_formation
SET url = 'formations/ressources/guide_broderie_fassi.pdf',
    nb_pages = 2,
    taille_ko = 180
WHERE url IN ('A_REMPLACER_PDF_UPLOAD', 'PLACEHOLDER_A_REMPLACER_PAR_UPLOAD_PDF')
  AND formation_id = (
      SELECT id FROM formations
      WHERE titre ILIKE '%fassi%' OR titre ILIKE '%broderie fès%' OR titre ILIKE '%fès%'
      LIMIT 1
  );

-- 3. Guide Broderie Rbatie (formation TESTE #38)
UPDATE ressources_formation
SET url = 'formations/ressources/guide_broderie_rbatie.pdf',
    nb_pages = 2,
    taille_ko = 180
WHERE url IN ('A_REMPLACER_PDF_UPLOAD', 'PLACEHOLDER_A_REMPLACER_PAR_UPLOAD_PDF')
  AND (
      titre ILIKE '%matériaux et outils%'
      OR formation_id = (
          SELECT id FROM formations
          WHERE titre ILIKE '%rbati%' OR titre = 'TESTE'
          LIMIT 1
      )
  );

-- 4. Guide Teinture Naturelle (formation #37)
UPDATE ressources_formation
SET url = 'formations/ressources/guide_teinture_naturelle.pdf',
    nb_pages = 2,
    taille_ko = 190
WHERE url IN ('A_REMPLACER_PDF_UPLOAD', 'PLACEHOLDER_A_REMPLACER_PAR_UPLOAD_PDF')
  AND (
      titre ILIKE '%plantes tinctoriales%'
      OR formation_id = (
          SELECT id FROM formations
          WHERE titre ILIKE '%teinture%'
          LIMIT 1
      )
  );

-- ────────────────────────────────────────────────────────────────
-- VÉRIFICATION : aucun placeholder PDF ne doit rester
-- ────────────────────────────────────────────────────────────────
SELECT id, formation_id, titre, type, url
FROM ressources_formation
WHERE url IN ('A_REMPLACER_PDF_UPLOAD', 'PLACEHOLDER_A_REMPLACER_PAR_UPLOAD_PDF');
-- Cette requête doit retourner ZÉRO ligne si tout s'est bien passé

-- Vue d'ensemble finale
SELECT
    f.titre AS formation,
    r.type,
    r.titre AS ressource_titre,
    r.url,
    r.nb_pages,
    r.taille_ko,
    r.est_public
FROM ressources_formation r
JOIN formations f ON f.id = r.formation_id
ORDER BY f.id, r.ordre;

COMMIT;
