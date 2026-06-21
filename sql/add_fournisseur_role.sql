-- =============================================================================
-- Tissu Artisanal — Espace fournisseur (à exécuter UNE SEULE FOIS sur PostgreSQL)
-- =============================================================================
-- Exécution : psql -U votre_user -d votre_base -f sql/add_fournisseur_role.sql
--             ou via pgAdmin / outil SQL de votre choix.
--
-- Prérequis : sauvegarde de la base recommandée avant toute modification de schéma.
-- =============================================================================

-- ── 1. Ajouter le rôle 'fournisseur' à l'enum users.role ─────────────────────
-- Type enum confirmé dans le projet : role_utilisateur
-- (CREATE TYPE role_utilisateur AS ENUM ('client','artisan','admin',...))

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM pg_enum e
        JOIN pg_type t ON e.enumtypid = t.oid
        WHERE t.typname = 'role_utilisateur'
          AND e.enumlabel = 'fournisseur'
    ) THEN
        ALTER TYPE role_utilisateur ADD VALUE 'fournisseur';
    END IF;
END $$;

-- Alternative PostgreSQL 15+ (commentée) :
-- ALTER TYPE role_utilisateur ADD VALUE IF NOT EXISTS 'fournisseur';

-- ── 2. Lier fournisseurs ↔ users (compte de connexion optionnel) ──────────────
-- user_id NULL = fournisseur géré uniquement par l'admin, sans accès login.

ALTER TABLE fournisseurs
    ADD COLUMN IF NOT EXISTS user_id BIGINT NULL REFERENCES users(id) ON DELETE SET NULL;

CREATE INDEX IF NOT EXISTS idx_fournisseurs_user_id ON fournisseurs(user_id);
