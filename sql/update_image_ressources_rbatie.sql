-- Images broderie rbatie — pièces finies (galerie formation TESTE)
-- Fichiers : formations/ressources/images/galerie-rbatie-piece-1.jpg
--            formations/ressources/images/galerie-rbatie-piece-2.jpg

BEGIN;

UPDATE ressources_formation
SET url = 'formations/ressources/images/galerie-rbatie-piece-1.jpg',
    taille_ko = 3250
WHERE id = 20; -- Galerie de pièces finies — broderie rbatie

UPDATE ressources_formation
SET url = 'formations/ressources/images/galerie-rbatie-piece-2.jpg',
    taille_ko = 3368
WHERE id = 14; -- Galerie de pièces finies

COMMIT;
