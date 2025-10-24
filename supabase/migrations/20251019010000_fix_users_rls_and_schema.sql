/*
  # Fix Users RLS Recursion and Ensure Password Column Exists (Robust)

  1) Ensure the public.users table has the expected schema
  2) Drop ALL existing policies on public.users (by enumerating pg_policies)
  3) Recreate simple, non-recursive policies

  Apply this script in Supabase SQL Editor (project DB).
*/

-- Ensure password_hash column exists (avoid NOT NULL to prevent immediate failures)
ALTER TABLE public.users
    ADD COLUMN IF NOT EXISTS password_hash TEXT;

-- Drop all existing policies on public.users to avoid unknown recursive rules
DO $$
DECLARE pol RECORD;
BEGIN
  FOR pol IN
    SELECT polname FROM pg_policies
    WHERE schemaname = 'public' AND tablename = 'users'
  LOOP
    EXECUTE format('DROP POLICY IF EXISTS %I ON public.users', pol.polname);
  END LOOP;
END $$;

-- Enable RLS (safe if already enabled)
ALTER TABLE public.users ENABLE ROW LEVEL SECURITY;

-- Recreate minimal, non-recursive policies
CREATE POLICY "Allow read for all" ON public.users
    FOR SELECT
    TO anon, authenticated
    USING (true);

CREATE POLICY "Allow insert for all" ON public.users
    FOR INSERT
    TO anon, authenticated
    WITH CHECK (true);

CREATE POLICY "Allow update self" ON public.users
    FOR UPDATE
    TO authenticated
    USING (id = auth.uid())
    WITH CHECK (id = auth.uid());

CREATE POLICY "Allow delete self" ON public.users
    FOR DELETE
    TO authenticated
    USING (id = auth.uid());

/*
Notes:
- This script removes ALL previous policies to eliminate recursion.
- Avoid calling helper functions that query public.users inside policies on public.users.
- After applying, test: SELECT * FROM public.users; and INSERT into public.users.
*/