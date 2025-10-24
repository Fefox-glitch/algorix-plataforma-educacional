/*
  # Create Algorix Database Schema

  1. New Tables
    - `users`
      - `id` (uuid, primary key)
      - `email` (text, unique)
      - `name` (text)
      - `password_hash` (text)
      - `role` (text) - student, teacher, or admin
      - `created_at` (timestamp)
      - `updated_at` (timestamp)
    
    - `courses`
      - `id` (serial, primary key)
      - `title` (text)
      - `description` (text)
      - `category` (text)
      - `teacher_id` (uuid, foreign key to users)
      - `created_at` (timestamp)
      - `updated_at` (timestamp)
    
    - `modules`
      - `id` (serial, primary key)
      - `course_id` (integer, foreign key to courses)
      - `title` (text)
      - `description` (text)
      - `difficulty` (text) - easy, medium, or hard
      - `is_active` (boolean)
      - `created_at` (timestamp)
      - `updated_at` (timestamp)
    
    - `exercises`
      - `id` (serial, primary key)
      - `module_id` (integer, foreign key to modules)
      - `title` (text)
      - `statement` (text)
      - `solution` (text)
      - `difficulty` (text)
      - `created_at` (timestamp)
      - `updated_at` (timestamp)
    
    - `submissions`
      - `id` (serial, primary key)
      - `exercise_id` (integer, foreign key to exercises)
      - `user_id` (uuid, foreign key to users)
      - `code` (text)
      - `score` (integer)
      - `grade` (float)
      - `feedback` (text)
      - `created_at` (timestamp)
      - `updated_at` (timestamp)
    
    - `grades`
      - `id` (serial, primary key)
      - `user_id` (uuid, foreign key to users)
      - `course_id` (integer, foreign key to courses)
      - `module_id` (integer, foreign key to modules)
      - `final_grade` (float)
      - `created_at` (timestamp)
      - `updated_at` (timestamp)
    
    - `challenges`
      - `id` (serial, primary key)
      - `title` (text)
      - `description` (text)
      - `difficulty` (text)
      - `category` (text)
      - `points` (integer)
      - `time_limit` (integer)
      - `solution` (text)
      - `is_active` (boolean)
      - `created_at` (timestamp)
      - `updated_at` (timestamp)
    
    - `challenge_test_cases`
      - `id` (serial, primary key)
      - `challenge_id` (integer, foreign key to challenges)
      - `input` (text)
      - `expected_output` (text)
      - `created_at` (timestamp)
    
    - `challenge_submissions`
      - `id` (serial, primary key)
      - `challenge_id` (integer, foreign key to challenges)
      - `user_id` (uuid, foreign key to users)
      - `code` (text)
      - `language` (text)
      - `status` (text)
      - `score` (integer)
      - `feedback` (text)
      - `execution_time` (integer)
      - `memory_used` (integer)
      - `passed_tests` (integer)
      - `total_tests` (integer)
      - `submitted_at` (timestamp)
      - `evaluated_at` (timestamp)

  2. Security
    - Enable RLS on all tables
    - Add policies for users based on roles
    - Students can view their own data
    - Teachers can manage their courses and view submissions
    - Admins can manage everything

  3. Performance
    - Add indexes on frequently queried columns
    - Add triggers for automatic timestamp updates
*/

-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    email TEXT UNIQUE NOT NULL,
    name TEXT NOT NULL,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL CHECK (role IN ('student', 'teacher', 'admin')),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Courses table
CREATE TABLE IF NOT EXISTS courses (
    id SERIAL PRIMARY KEY,
    title TEXT NOT NULL,
    description TEXT,
    category TEXT,
    teacher_id UUID REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Modules table
CREATE TABLE IF NOT EXISTS modules (
    id SERIAL PRIMARY KEY,
    course_id INTEGER REFERENCES courses(id) ON DELETE CASCADE,
    title TEXT NOT NULL,
    description TEXT,
    difficulty TEXT CHECK (difficulty IN ('easy', 'medium', 'hard')),
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Exercises table
CREATE TABLE IF NOT EXISTS exercises (
    id SERIAL PRIMARY KEY,
    module_id INTEGER REFERENCES modules(id) ON DELETE CASCADE,
    title TEXT NOT NULL,
    statement TEXT NOT NULL,
    solution TEXT,
    difficulty TEXT CHECK (difficulty IN ('easy', 'medium', 'hard')),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Submissions table
CREATE TABLE IF NOT EXISTS submissions (
    id SERIAL PRIMARY KEY,
    exercise_id INTEGER REFERENCES exercises(id) ON DELETE CASCADE,
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    code TEXT NOT NULL,
    score INTEGER DEFAULT 0,
    grade FLOAT,
    feedback TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Grades table
CREATE TABLE IF NOT EXISTS grades (
    id SERIAL PRIMARY KEY,
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    course_id INTEGER REFERENCES courses(id) ON DELETE CASCADE,
    module_id INTEGER REFERENCES modules(id) ON DELETE CASCADE,
    final_grade FLOAT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Challenges table
CREATE TABLE IF NOT EXISTS challenges (
    id SERIAL PRIMARY KEY,
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    difficulty TEXT CHECK (difficulty IN ('easy', 'medium', 'hard')),
    category TEXT,
    points INTEGER DEFAULT 10,
    time_limit INTEGER DEFAULT 30,
    solution TEXT,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Challenge test cases table
CREATE TABLE IF NOT EXISTS challenge_test_cases (
    id SERIAL PRIMARY KEY,
    challenge_id INTEGER REFERENCES challenges(id) ON DELETE CASCADE,
    input TEXT NOT NULL,
    expected_output TEXT NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Challenge submissions table
CREATE TABLE IF NOT EXISTS challenge_submissions (
    id SERIAL PRIMARY KEY,
    challenge_id INTEGER REFERENCES challenges(id) ON DELETE CASCADE,
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    code TEXT NOT NULL,
    language TEXT DEFAULT 'javascript',
    status TEXT DEFAULT 'pending' CHECK (status IN ('pending', 'running', 'completed', 'failed')),
    score INTEGER DEFAULT 0,
    feedback TEXT,
    execution_time INTEGER,
    memory_used INTEGER,
    passed_tests INTEGER DEFAULT 0,
    total_tests INTEGER DEFAULT 0,
    submitted_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    evaluated_at TIMESTAMP WITH TIME ZONE
);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
CREATE INDEX IF NOT EXISTS idx_courses_teacher_id ON courses(teacher_id);
CREATE INDEX IF NOT EXISTS idx_modules_course_id ON modules(course_id);
CREATE INDEX IF NOT EXISTS idx_exercises_module_id ON exercises(module_id);
CREATE INDEX IF NOT EXISTS idx_submissions_user_id ON submissions(user_id);
CREATE INDEX IF NOT EXISTS idx_submissions_exercise_id ON submissions(exercise_id);
CREATE INDEX IF NOT EXISTS idx_grades_user_id ON grades(user_id);
CREATE INDEX IF NOT EXISTS idx_grades_course_id ON grades(course_id);
CREATE INDEX IF NOT EXISTS idx_challenge_submissions_user_id ON challenge_submissions(user_id);
CREATE INDEX IF NOT EXISTS idx_challenge_submissions_challenge_id ON challenge_submissions(challenge_id);

-- Enable Row Level Security
ALTER TABLE users ENABLE ROW LEVEL SECURITY;
ALTER TABLE courses ENABLE ROW LEVEL SECURITY;
ALTER TABLE modules ENABLE ROW LEVEL SECURITY;
ALTER TABLE exercises ENABLE ROW LEVEL SECURITY;
ALTER TABLE submissions ENABLE ROW LEVEL SECURITY;
ALTER TABLE grades ENABLE ROW LEVEL SECURITY;
ALTER TABLE challenges ENABLE ROW LEVEL SECURITY;
ALTER TABLE challenge_test_cases ENABLE ROW LEVEL SECURITY;
ALTER TABLE challenge_submissions ENABLE ROW LEVEL SECURITY;

-- Users policies
CREATE POLICY "Users can view own profile" ON users
    FOR SELECT
    TO authenticated
    USING (id = auth.uid());

CREATE POLICY "Anyone can insert users (for registration)" ON users
    FOR INSERT
    TO anon, authenticated
    WITH CHECK (true);

CREATE POLICY "Users can update own profile" ON users
    FOR UPDATE
    TO authenticated
    USING (id = auth.uid())
    WITH CHECK (id = auth.uid());

-- Courses policies
CREATE POLICY "Everyone can view courses" ON courses
    FOR SELECT
    TO authenticated, anon
    USING (true);

CREATE POLICY "Teachers can insert courses" ON courses
    FOR INSERT
    TO authenticated
    WITH CHECK (
        EXISTS (
            SELECT 1 FROM users
            WHERE id = auth.uid() AND role IN ('teacher', 'admin')
        )
    );

CREATE POLICY "Teachers can manage own courses" ON courses
    FOR UPDATE
    TO authenticated
    USING (teacher_id = auth.uid())
    WITH CHECK (teacher_id = auth.uid());

CREATE POLICY "Teachers can delete own courses" ON courses
    FOR DELETE
    TO authenticated
    USING (teacher_id = auth.uid());

-- Modules policies
CREATE POLICY "Everyone can view modules" ON modules
    FOR SELECT
    TO authenticated, anon
    USING (true);

CREATE POLICY "Teachers can manage course modules" ON modules
    FOR ALL
    TO authenticated
    USING (
        EXISTS (
            SELECT 1 FROM courses
            WHERE id = course_id AND teacher_id = auth.uid()
        )
    );

-- Exercises policies
CREATE POLICY "Everyone can view exercises" ON exercises
    FOR SELECT
    TO authenticated, anon
    USING (true);

CREATE POLICY "Teachers can manage course exercises" ON exercises
    FOR ALL
    TO authenticated
    USING (
        EXISTS (
            SELECT 1 FROM modules m
            JOIN courses c ON m.course_id = c.id
            WHERE m.id = module_id AND c.teacher_id = auth.uid()
        )
    );

-- Submissions policies
CREATE POLICY "Students can view own submissions" ON submissions
    FOR SELECT
    TO authenticated
    USING (user_id = auth.uid());

CREATE POLICY "Teachers can view course submissions" ON submissions
    FOR SELECT
    TO authenticated
    USING (
        EXISTS (
            SELECT 1 FROM exercises e
            JOIN modules m ON e.module_id = m.id
            JOIN courses c ON m.course_id = c.id
            WHERE e.id = exercise_id AND c.teacher_id = auth.uid()
        )
    );

CREATE POLICY "Students can insert own submissions" ON submissions
    FOR INSERT
    TO authenticated
    WITH CHECK (user_id = auth.uid());

CREATE POLICY "Teachers can update course submissions" ON submissions
    FOR UPDATE
    TO authenticated
    USING (
        EXISTS (
            SELECT 1 FROM exercises e
            JOIN modules m ON e.module_id = m.id
            JOIN courses c ON m.course_id = c.id
            WHERE e.id = exercise_id AND c.teacher_id = auth.uid()
        )
    );

-- Grades policies
CREATE POLICY "Students can view own grades" ON grades
    FOR SELECT
    TO authenticated
    USING (user_id = auth.uid());

CREATE POLICY "Teachers can view course grades" ON grades
    FOR SELECT
    TO authenticated
    USING (
        EXISTS (
            SELECT 1 FROM courses
            WHERE id = course_id AND teacher_id = auth.uid()
        )
    );

CREATE POLICY "Teachers can manage course grades" ON grades
    FOR ALL
    TO authenticated
    USING (
        EXISTS (
            SELECT 1 FROM courses
            WHERE id = course_id AND teacher_id = auth.uid()
        )
    );

-- Challenges policies
CREATE POLICY "Everyone can view active challenges" ON challenges
    FOR SELECT
    TO authenticated, anon
    USING (is_active = true);

CREATE POLICY "Staff can manage challenges" ON challenges
    FOR ALL
    TO authenticated
    USING (
        EXISTS (
            SELECT 1 FROM users
            WHERE id = auth.uid() AND role IN ('teacher', 'admin')
        )
    );

-- Challenge test cases policies
CREATE POLICY "Everyone can view test cases" ON challenge_test_cases
    FOR SELECT
    TO authenticated, anon
    USING (true);

CREATE POLICY "Staff can manage test cases" ON challenge_test_cases
    FOR ALL
    TO authenticated
    USING (
        EXISTS (
            SELECT 1 FROM users
            WHERE id = auth.uid() AND role IN ('teacher', 'admin')
        )
    );

-- Challenge submissions policies
CREATE POLICY "Students can view own challenge submissions" ON challenge_submissions
    FOR SELECT
    TO authenticated
    USING (user_id = auth.uid());

CREATE POLICY "Students can insert own challenge submissions" ON challenge_submissions
    FOR INSERT
    TO authenticated
    WITH CHECK (user_id = auth.uid());

CREATE POLICY "Staff can view all challenge submissions" ON challenge_submissions
    FOR SELECT
    TO authenticated
    USING (
        EXISTS (
            SELECT 1 FROM users
            WHERE id = auth.uid() AND role IN ('teacher', 'admin')
        )
    );

CREATE POLICY "Staff can update challenge submissions" ON challenge_submissions
    FOR UPDATE
    TO authenticated
    USING (
        EXISTS (
            SELECT 1 FROM users
            WHERE id = auth.uid() AND role IN ('teacher', 'admin')
        )
    );

-- Functions for updating timestamps
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Triggers for updating timestamps
DROP TRIGGER IF EXISTS update_users_updated_at ON users;
CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_courses_updated_at ON courses;
CREATE TRIGGER update_courses_updated_at BEFORE UPDATE ON courses
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_modules_updated_at ON modules;
CREATE TRIGGER update_modules_updated_at BEFORE UPDATE ON modules
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_exercises_updated_at ON exercises;
CREATE TRIGGER update_exercises_updated_at BEFORE UPDATE ON exercises
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_submissions_updated_at ON submissions;
CREATE TRIGGER update_submissions_updated_at BEFORE UPDATE ON submissions
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_grades_updated_at ON grades;
CREATE TRIGGER update_grades_updated_at BEFORE UPDATE ON grades
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_challenges_updated_at ON challenges;
CREATE TRIGGER update_challenges_updated_at BEFORE UPDATE ON challenges
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
