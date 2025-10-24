-- Migration: Add created_by audit columns to admin tables
-- Purpose: Record who created each lab, computer, and group
-- Safe to run multiple times due to IF NOT EXISTS guards

BEGIN;

-- computer_labs
ALTER TABLE public.computer_labs
  ADD COLUMN IF NOT EXISTS created_by uuid REFERENCES public.users(id) ON DELETE SET NULL;
CREATE INDEX IF NOT EXISTS idx_computer_labs_created_by ON public.computer_labs(created_by);

-- computers
ALTER TABLE public.computers
  ADD COLUMN IF NOT EXISTS created_by uuid REFERENCES public.users(id) ON DELETE SET NULL;
CREATE INDEX IF NOT EXISTS idx_computers_created_by ON public.computers(created_by);

-- student_groups
ALTER TABLE public.student_groups
  ADD COLUMN IF NOT EXISTS created_by uuid REFERENCES public.users(id) ON DELETE SET NULL;
CREATE INDEX IF NOT EXISTS idx_student_groups_created_by ON public.student_groups(created_by);

COMMIT;