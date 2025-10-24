-- Migration: Add soft-delete columns to lab/compute/group tables
-- Purpose: Support reversible delete via deleted_at/deleted_by
-- Notes: Application will PATCH these fields instead of hard DELETE

BEGIN;

-- computer_labs soft-delete
ALTER TABLE public.computer_labs
  ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP WITH TIME ZONE,
  ADD COLUMN IF NOT EXISTS deleted_by UUID REFERENCES public.users(id) ON DELETE SET NULL;
CREATE INDEX IF NOT EXISTS idx_computer_labs_deleted_at ON public.computer_labs(deleted_at);
CREATE INDEX IF NOT EXISTS idx_computer_labs_deleted_by ON public.computer_labs(deleted_by);

-- computers soft-delete
ALTER TABLE public.computers
  ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP WITH TIME ZONE,
  ADD COLUMN IF NOT EXISTS deleted_by UUID REFERENCES public.users(id) ON DELETE SET NULL;
CREATE INDEX IF NOT EXISTS idx_computers_deleted_at ON public.computers(deleted_at);
CREATE INDEX IF NOT EXISTS idx_computers_deleted_by ON public.computers(deleted_by);

-- student_groups soft-delete
ALTER TABLE public.student_groups
  ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP WITH TIME ZONE,
  ADD COLUMN IF NOT EXISTS deleted_by UUID REFERENCES public.users(id) ON DELETE SET NULL;
CREATE INDEX IF NOT EXISTS idx_student_groups_deleted_at ON public.student_groups(deleted_at);
CREATE INDEX IF NOT EXISTS idx_student_groups_deleted_by ON public.student_groups(deleted_by);

COMMIT;