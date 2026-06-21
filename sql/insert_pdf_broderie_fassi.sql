-- ================================================================
--  RESSOURCE PDF — Broderie Fassi (à exécuter quand la formation existe)
--  Prérequis : guide_broderie_fassi.pdf dans
--  storage/app/public/formations/ressources/
-- ================================================================

BEGIN;

INSERT INTO ressources_formation (
    formation_id, type, titre, description, url,
    nb_pages, taille_ko, est_public, ordre
)
SELECT
    f.id,
    'document_pdf',
    'Guide complet — Broderie Fassi',
    'Guide pédagogique de 2 pages : matériaux, points traditionnels et motifs fassis.',
    'formations/ressources/guide_broderie_fassi.pdf',
    2,
    180,
    true,
    COALESCE((SELECT MAX(ordre) + 1 FROM ressources_formation WHERE formation_id = f.id), 1)
FROM formations f
WHERE (f.titre ILIKE '%fassi%' OR f.titre ILIKE '%broderie fès%')
  AND NOT EXISTS (
      SELECT 1 FROM ressources_formation r
      WHERE r.formation_id = f.id
        AND r.url = 'formations/ressources/guide_broderie_fassi.pdf'
  )
LIMIT 1;

SELECT f.titre, r.titre AS ressource, r.url
FROM ressources_formation r
JOIN formations f ON f.id = r.formation_id
WHERE r.url = 'formations/ressources/guide_broderie_fassi.pdf';

COMMIT;
