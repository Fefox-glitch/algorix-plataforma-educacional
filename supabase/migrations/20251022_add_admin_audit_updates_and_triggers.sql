-- Migration: Add updated_by and audit triggers
-- Purpose: Strengthen DB-level audit for created_by/updated_by using auth.uid()
-- Notes:
-- - Triggers set values only when NULL to avoid overriding application-provided IDs
-- - With service role key, auth.uid() is NULL; app should send created_by/updated_by

BEGIN;

-- 1) Add updated_by columns with FK to public.users
ALTER TABLE public.computer_labs
  ADD COLUMN IF NOT EXISTS updated_by uuid REFERENCES public.users(id) ON DELETE SET NULL;
CREATE INDEX IF NOT EXISTS idx_computer_labs_updated_by ON public.computer_labs(updated_by);

ALTER TABLE public.computers
  ADD COLUMN IF NOT EXISTS updated_by uuid REFERENCES public.users(id) ON DELETE SET NULL;
CREATE INDEX IF NOT EXISTS idx_computers_updated_by ON public.computers(updated_by);

ALTER TABLE public.student_groups
  ADD COLUMN IF NOT EXISTS updated_by uuid REFERENCES public.users(id) ON DELETE SET NULL;
CREATE INDEX IF NOT EXISTS idx_student_groups_updated_by ON public.student_groups(updated_by);

-- 2) Helper trigger functions to fill created_by / updated_by from auth.uid()
CREATE OR REPLACE FUNCTION public.set_created_by_if_null()
RETURNS trigger
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = public
AS $$
BEGIN
  IF NEW.created_by IS NULL THEN
    -- When executed under anon key with valid JWT, auth.uid() is available
    NEW.created_by := auth.uid();
  END IF;
  RETURN NEW;
END;
$$;

CREATE OR REPLACE FUNCTION public.set_updated_by_if_null()
RETURNS trigger
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = public
AS $$
BEGIN
  IF NEW.updated_by IS NULL THEN
    NEW.updated_by := auth.uid();
  END IF;
  RETURN NEW;
END;
$$;

-- 3) Attach triggers (do not override values provided by application)
-- computer_labs
DROP TRIGGER IF EXISTS t_set_created_by_computer_labs ON public.computer_labs;
CREATE TRIGGER t_set_created_by_computer_labs
BEFORE INSERT ON public.computer_labs
FOR EACH ROW EXECUTE FUNCTION public.set_created_by_if_null();

DROP TRIGGER IF EXISTS t_set_updated_by_computer_labs ON public.computer_labs;
CREATE TRIGGER t_set_updated_by_computer_labs
BEFORE UPDATE ON public.computer_labs
FOR EACH ROW EXECUTE FUNCTION public.set_updated_by_if_null();

-- computers
DROP TRIGGER IF EXISTS t_set_created_by_computers ON public.computers;
CREATE TRIGGER t_set_created_by_computers
BEFORE INSERT ON public.computers
FOR EACH ROW EXECUTE FUNCTION public.set_created_by_if_null();

DROP TRIGGER IF EXISTS t_set_updated_by_computers ON public.computers;
CREATE TRIGGER t_set_updated_by_computers
BEFORE UPDATE ON public.computers
FOR EACH ROW EXECUTE FUNCTION public.set_updated_by_if_null();

-- student_groups
DROP TRIGGER IF EXISTS t_set_created_by_student_groups ON public.student_groups;
CREATE TRIGGER t_set_created_by_student_groups
BEFORE INSERT ON public.student_groups
FOR EACH ROW EXECUTE FUNCTION public.set_created_by_if_null();

DROP TRIGGER IF EXISTS t_set_updated_by_student_groups ON public.student_groups;
CREATE TRIGGER t_set_updated_by_student_groups
BEFORE UPDATE ON public.student_groups
FOR EACH ROW EXECUTE FUNCTION public.set_updated_by_if_null();

COMMIT;