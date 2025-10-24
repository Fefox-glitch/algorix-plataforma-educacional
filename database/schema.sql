-- Supabase Database Schema for Algorix
-- Run this in your Supabase SQL editor

-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    email TEXT UNIQUE NOT NULL,
    name TEXT NOT NULL,
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
    time_limit INTEGER DEFAULT 30, -- minutes
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

-- Row Level Security (RLS) Policies

-- Users policies
ALTER TABLE users ENABLE ROW LEVEL SECURITY;

-- Users can read their own data
CREATE POLICY "Users can view own profile" ON users
    FOR SELECT USING (auth.uid() = id);

-- Admins can read all users
CREATE POLICY "Admins can view all users" ON users
    FOR SELECT USING (
        EXISTS (
            SELECT 1 FROM users
            WHERE id = auth.uid() AND role = 'admin'
        )
    );

-- Only admins can insert/update/delete users
CREATE POLICY "Admins can manage users" ON users
    FOR ALL USING (
        EXISTS (
            SELECT 1 FROM users
            WHERE id = auth.uid() AND role = 'admin'
        )
    );

-- Courses policies
ALTER TABLE courses ENABLE ROW LEVEL SECURITY;

-- Everyone can read active courses
CREATE POLICY "Everyone can view courses" ON courses
    FOR SELECT USING (true);

-- Teachers can manage their own courses
CREATE POLICY "Teachers can manage own courses" ON courses
    FOR ALL USING (teacher_id = auth.uid());

-- Admins can manage all courses
CREATE POLICY "Admins can manage all courses" ON courses
    FOR ALL USING (
        EXISTS (
            SELECT 1 FROM users
            WHERE id = auth.uid() AND role = 'admin'
        )
    );

-- Modules policies
ALTER TABLE modules ENABLE ROW LEVEL SECURITY;

-- Everyone can read modules from active courses
CREATE POLICY "Everyone can view modules" ON modules
    FOR SELECT USING (true);

-- Teachers can manage modules in their courses
CREATE POLICY "Teachers can manage course modules" ON modules
    FOR ALL USING (
        EXISTS (
            SELECT 1 FROM courses
            WHERE id = course_id AND teacher_id = auth.uid()
        )
    );

-- Admins can manage all modules
CREATE POLICY "Admins can manage all modules" ON modules
    FOR ALL USING (
        EXISTS (
            SELECT 1 FROM users
            WHERE id = auth.uid() AND role = 'admin'
        )
    );

-- Exercises policies
ALTER TABLE exercises ENABLE ROW LEVEL SECURITY;

-- Everyone can read exercises
CREATE POLICY "Everyone can view exercises" ON exercises
    FOR SELECT USING (true);

-- Teachers can manage exercises in their course modules
CREATE POLICY "Teachers can manage course exercises" ON exercises
    FOR ALL USING (
        EXISTS (
            SELECT 1 FROM modules m
            JOIN courses c ON m.course_id = c.id
            WHERE m.id = module_id AND c.teacher_id = auth.uid()
        )
    );

-- Admins can manage all exercises
CREATE POLICY "Admins can manage all exercises" ON exercises
    FOR ALL USING (
        EXISTS (
            SELECT 1 FROM users
            WHERE id = auth.uid() AND role = 'admin'
        )
    );

-- Submissions policies
ALTER TABLE submissions ENABLE ROW LEVEL SECURITY;

-- Students can view their own submissions
CREATE POLICY "Students can view own submissions" ON submissions
    FOR SELECT USING (user_id = auth.uid());

-- Teachers can view submissions for exercises in their courses
CREATE POLICY "Teachers can view course submissions" ON submissions
    FOR SELECT USING (
        EXISTS (
            SELECT 1 FROM exercises e
            JOIN modules m ON e.module_id = m.id
            JOIN courses c ON m.course_id = c.id
            WHERE e.id = exercise_id AND c.teacher_id = auth.uid()
        )
    );

-- Students can insert their own submissions
CREATE POLICY "Students can insert own submissions" ON submissions
    FOR INSERT WITH CHECK (user_id = auth.uid());

-- Teachers can update submissions for their courses
CREATE POLICY "Teachers can update course submissions" ON submissions
    FOR UPDATE USING (
        EXISTS (
            SELECT 1 FROM exercises e
            JOIN modules m ON e.module_id = m.id
            JOIN courses c ON m.course_id = c.id
            WHERE e.id = exercise_id AND c.teacher_id = auth.uid()
        )
    );

-- Admins can manage all submissions
CREATE POLICY "Admins can manage all submissions" ON submissions
    FOR ALL USING (
        EXISTS (
            SELECT 1 FROM users
            WHERE id = auth.uid() AND role = 'admin'
        )
    );

-- Grades policies
ALTER TABLE grades ENABLE ROW LEVEL SECURITY;

-- Students can view their own grades
CREATE POLICY "Students can view own grades" ON grades
    FOR SELECT USING (user_id = auth.uid());

-- Teachers can view grades for their courses
CREATE POLICY "Teachers can view course grades" ON grades
    FOR SELECT USING (
        EXISTS (
            SELECT 1 FROM courses
            WHERE id = course_id AND teacher_id = auth.uid()
        )
    );

-- Teachers can manage grades for their courses
CREATE POLICY "Teachers can manage course grades" ON grades
    FOR ALL USING (
        EXISTS (
            SELECT 1 FROM courses
            WHERE id = course_id AND teacher_id = auth.uid()
        )
    );

-- Admins can manage all grades
CREATE POLICY "Admins can manage all grades" ON grades
    FOR ALL USING (
        EXISTS (
            SELECT 1 FROM users
            WHERE id = auth.uid() AND role = 'admin'
        )
    );

-- Challenges policies
ALTER TABLE challenges ENABLE ROW LEVEL SECURITY;

-- Everyone can read active challenges
CREATE POLICY "Everyone can view active challenges" ON challenges
    FOR SELECT USING (is_active = true);

-- Teachers and admins can manage challenges
CREATE POLICY "Staff can manage challenges" ON challenges
    FOR ALL USING (
        EXISTS (
            SELECT 1 FROM users
            WHERE id = auth.uid() AND role IN ('teacher', 'admin')
        )
    );

-- Challenge submissions policies
ALTER TABLE challenge_submissions ENABLE ROW LEVEL SECURITY;

-- Students can view their own challenge submissions
CREATE POLICY "Students can view own challenge submissions" ON challenge_submissions
    FOR SELECT USING (user_id = auth.uid());

-- Students can insert their own challenge submissions
CREATE POLICY "Students can insert own challenge submissions" ON challenge_submissions
    FOR INSERT WITH CHECK (user_id = auth.uid());

-- Teachers and admins can view all challenge submissions
CREATE POLICY "Staff can view all challenge submissions" ON challenge_submissions
    FOR SELECT USING (
        EXISTS (
            SELECT 1 FROM users
            WHERE id = auth.uid() AND role IN ('teacher', 'admin')
        )
    );

-- Teachers and admins can update challenge submissions
CREATE POLICY "Staff can update challenge submissions" ON challenge_submissions
    FOR UPDATE USING (
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
CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_courses_updated_at BEFORE UPDATE ON courses
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_modules_updated_at BEFORE UPDATE ON modules
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_exercises_updated_at BEFORE UPDATE ON exercises
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_submissions_updated_at BEFORE UPDATE ON submissions
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_grades_updated_at BEFORE UPDATE ON grades
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_challenges_updated_at BEFORE UPDATE ON challenges
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
