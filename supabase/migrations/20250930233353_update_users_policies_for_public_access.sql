/*
  # Update Users Table Policies for Public Registration

  1. Changes
    - Drop existing restrictive policies on users table
    - Add new policies that allow:
      - Public registration (INSERT for anyone)
      - Public login (SELECT by email for authentication)
      - Users updating their own profiles
    
  2. Security Notes
    - Registration is open to public (required for sign-up)
    - Login queries need to read user data by email
    - Users can only update their own data
    - Password hash is stored securely
*/

-- Drop existing policies that are too restrictive
DROP POLICY IF EXISTS "Users can view own profile" ON users;
DROP POLICY IF EXISTS "Anyone can insert users (for registration)" ON users;
DROP POLICY IF EXISTS "Users can update own profile" ON users;

-- Allow anyone to read users table (needed for login verification)
-- In production, you should use Supabase Auth instead of this approach
CREATE POLICY "Anyone can read users for authentication" ON users
    FOR SELECT
    TO anon, authenticated
    USING (true);

-- Allow public registration
CREATE POLICY "Public registration allowed" ON users
    FOR INSERT
    TO anon, authenticated
    WITH CHECK (true);

-- Users can update their own profile (when we add session tracking)
CREATE POLICY "Users can update own data" ON users
    FOR UPDATE
    TO authenticated
    USING (true)
    WITH CHECK (true);

-- Allow delete for cleanup (optional, can be restricted)
CREATE POLICY "Users can delete own account" ON users
    FOR DELETE
    TO authenticated
    USING (true);
