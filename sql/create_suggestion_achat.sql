-- Table de suivi des clics / achats apprenants vers fournisseurs
-- Exécuter : php sql/run_create_suggestion_achat.php

CREATE TABLE IF NOT EXISTS suggestion_achat (
    id              BIGSERIAL PRIMARY KEY,
    apprenant_id    BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    formation_id    BIGINT NOT NULL REFERENCES formations(id) ON DELETE CASCADE,
    fournisseur_id  BIGINT NOT NULL REFERENCES fournisseurs(id) ON DELETE CASCADE,
    type_objet      VARCHAR(20) NOT NULL CHECK (type_objet IN ('materiau', 'outil')),
    objet_id        BIGINT NOT NULL,
    est_clique      BOOLEAN NOT NULL DEFAULT FALSE,
    est_achete      BOOLEAN NOT NULL DEFAULT FALSE,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE UNIQUE INDEX IF NOT EXISTS suggestion_achat_unique_tracking
    ON suggestion_achat (apprenant_id, formation_id, fournisseur_id, type_objet, objet_id);

CREATE INDEX IF NOT EXISTS suggestion_achat_fournisseur_id_idx
    ON suggestion_achat (fournisseur_id);

CREATE INDEX IF NOT EXISTS suggestion_achat_est_clique_idx
    ON suggestion_achat (fournisseur_id, est_clique);

-- Ancienne table stub Laravel (migration vide) — renommer si elle existe sans données utiles
DO $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.tables
        WHERE table_schema = 'public' AND table_name = 'suggestion_achats'
    ) AND NOT EXISTS (
        SELECT 1 FROM information_schema.tables
        WHERE table_schema = 'public' AND table_name = 'suggestion_achat'
    ) THEN
        ALTER TABLE suggestion_achats RENAME TO suggestion_achat;
    END IF;
END $$;
