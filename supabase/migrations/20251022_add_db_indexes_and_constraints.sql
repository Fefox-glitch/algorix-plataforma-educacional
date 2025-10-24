-- Migration: Add performance indexes and integrity constraints
-- Date: 2025-10-22

-- Note: Uses CREATE INDEX IF NOT EXISTS for safe re-runs.
-- For CHECK constraints, uses DO blocks to avoid duplicates.

BEGIN;

-- =========================
-- Indexes: computers
-- =========================
CREATE INDEX IF NOT EXISTS idx_computers_lab_id ON public.computers(lab_id);
CREATE INDEX IF NOT EXISTS idx_computers_status ON public.computers(status);
CREATE INDEX IF NOT EXISTS idx_computers_created_by ON public.computers(created_by);
CREATE INDEX IF NOT EXISTS idx_computers_updated_by ON public.computers(updated_by);

-- Unique MAC address globally (NULLs allowed and not conflicting)
CREATE UNIQUE INDEX IF NOT EXISTS uidx_computers_mac_address ON public.computers(mac_address);

-- =========================
-- Indexes: computer_labs
-- =========================
CREATE INDEX IF NOT EXISTS idx_computer_labs_created_by ON public.computer_labs(created_by);
CREATE INDEX IF NOT EXISTS idx_computer_labs_updated_by ON public.computer_labs(updated_by);

-- =========================
-- Indexes: student_groups
-- =========================
CREATE INDEX IF NOT EXISTS idx_student_groups_lab_id ON public.student_groups(lab_id);
CREATE INDEX IF NOT EXISTS idx_student_groups_teacher_id ON public.student_groups(teacher_id);
CREATE INDEX IF NOT EXISTS idx_student_groups_created_by ON public.student_groups(created_by);
CREATE INDEX IF NOT EXISTS idx_student_groups_updated_by ON public.student_groups(updated_by);

-- =========================
-- Indexes: group_members
-- =========================
CREATE INDEX IF NOT EXISTS idx_group_members_group_id ON public.group_members(group_id);
CREATE INDEX IF NOT EXISTS idx_group_members_student_id ON public.group_members(student_id);

-- =========================
-- Indexes: computer_actions
-- =========================
CREATE INDEX IF NOT EXISTS idx_computer_actions_computer_id ON public.computer_actions(computer_id);
CREATE INDEX IF NOT EXISTS idx_computer_actions_performed_by ON public.computer_actions(performed_by);
CREATE INDEX IF NOT EXISTS idx_computer_actions_performed_at ON public.computer_actions(performed_at DESC);

-- =========================
-- Indexes: computer_assignments
-- =========================
CREATE INDEX IF NOT EXISTS idx_computer_assignments_computer_id ON public.computer_assignments(computer_id);
CREATE INDEX IF NOT EXISTS idx_computer_assignments_group_id ON public.computer_assignments(group_id);
CREATE INDEX IF NOT EXISTS idx_computer_assignments_student_id ON public.computer_assignments(student_id);

-- =========================
-- Indexes: lab_sessions
-- =========================
CREATE INDEX IF NOT EXISTS idx_lab_sessions_group_id ON public.lab_sessions(group_id);
CREATE INDEX IF NOT EXISTS idx_lab_sessions_teacher_id ON public.lab_sessions(teacher_id);
CREATE INDEX IF NOT EXISTS idx_lab_sessions_started_at ON public.lab_sessions(started_at DESC);

-- =========================
-- Constraints: computers
-- =========================
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conname = 'chk_computers_status_valid'
    ) THEN
        ALTER TABLE public.computers
        ADD CONSTRAINT chk_computers_status_valid CHECK (status IN ('online','offline','maintenance'));
    END IF;
END $$;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conname = 'chk_computers_mac_format'
    ) THEN
        ALTER TABLE public.computers
        ADD CONSTRAINT chk_computers_mac_format CHECK (
            mac_address IS NULL OR mac_address ~* '^([0-9A-F]{2}:){5}[0-9A-F]{2}$'
        );
    END IF;
END $$;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conname = 'chk_computers_name_len'
    ) THEN
        ALTER TABLE public.computers
        ADD CONSTRAINT chk_computers_name_len CHECK (char_length(name) BETWEEN 1 AND 100);
    END IF;
END $$;

-- =========================
-- Constraints: computer_labs
-- =========================
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conname = 'chk_computer_labs_name_len'
    ) THEN
        ALTER TABLE public.computer_labs
        ADD CONSTRAINT chk_computer_labs_name_len CHECK (char_length(name) BETWEEN 1 AND 100);
    END IF;
END $$;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conname = 'chk_computer_labs_location_len'
    ) THEN
        ALTER TABLE public.computer_labs
        ADD CONSTRAINT chk_computer_labs_location_len CHECK (location IS NULL OR char_length(location) <= 150);
    END IF;
END $$;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conname = 'chk_computer_labs_capacity_range'
    ) THEN
        ALTER TABLE public.computer_labs
        ADD CONSTRAINT chk_computer_labs_capacity_range CHECK (capacity >= 0 AND capacity <= 10000);
    END IF;
END $$;

-- =========================
-- Constraints: student_groups
-- =========================
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conname = 'chk_student_groups_name_len'
    ) THEN
        ALTER TABLE public.student_groups
        ADD CONSTRAINT chk_student_groups_name_len CHECK (char_length(name) BETWEEN 1 AND 100);
    END IF;
END $$;

COMMIT;