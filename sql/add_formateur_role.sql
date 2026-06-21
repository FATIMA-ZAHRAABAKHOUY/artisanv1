-- =============================================================================
-- Tissu Artisanal — Rôle formateur (externes avec accès login)
-- À exécuter UNE SEULE FOIS sur PostgreSQL avant de tester l'espace formateur.
-- =============================================================================
-- psql -U votre_user -d pfe_tissu_db -f sql/add_formateur_role.sql
-- ou : php sql/run_add_formateur_role.php
-- =============================================================================

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM pg_enum e
        JOIN pg_type t ON e.enumtypid = t.oid
        WHERE t.typname = 'role_utilisateur'
          AND e.enumlabel = 'formateur'
    ) THEN
        ALTER TYPE role_utilisateur ADD VALUE 'formateur';
    END IF;
END $$;
