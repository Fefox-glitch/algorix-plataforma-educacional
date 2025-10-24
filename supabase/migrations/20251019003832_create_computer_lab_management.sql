/*
  # Sistema de Gestión de Laboratorios de Computadoras

  ## Descripción General
  Este sistema permite la gestión centralizada de 30+ computadoras organizadas por laboratorios y grupos,
  con control de acceso para administradores y profesores.

  ## 1. Nuevas Tablas

  ### `computer_labs`
  Representa los laboratorios físicos donde se encuentran las computadoras
  - `id` (serial, primary key) - Identificador único del laboratorio
  - `name` (text) - Nombre del laboratorio (ej: "Lab A", "Sala 101")
  - `location` (text) - Ubicación física del laboratorio
  - `capacity` (integer) - Capacidad máxima de computadoras
  - `is_active` (boolean) - Si el laboratorio está activo
  - `created_at` (timestamp) - Fecha de creación
  - `updated_at` (timestamp) - Fecha de última actualización

  ### `computers`
  Registro de todas las computadoras en el sistema
  - `id` (serial, primary key) - Identificador único de la computadora
  - `lab_id` (integer, FK) - Laboratorio al que pertenece
  - `name` (text) - Nombre identificativo (ej: "PC-01", "Workstation-15")
  - `ip_address` (text) - Dirección IP de la computadora
  - `mac_address` (text) - Dirección MAC para Wake-on-LAN
  - `status` (text) - Estado: 'online', 'offline', 'maintenance', 'error'
  - `last_boot` (timestamp) - Última vez que se encendió
  - `last_seen` (timestamp) - Última conexión registrada
  - `specs` (jsonb) - Especificaciones técnicas (CPU, RAM, etc.)
  - `created_at` (timestamp) - Fecha de registro
  - `updated_at` (timestamp) - Fecha de actualización

  ### `student_groups`
  Grupos de estudiantes asignados a profesores
  - `id` (serial, primary key) - Identificador del grupo
  - `name` (text) - Nombre del grupo (ej: "Grupo A - Turno Mañana")
  - `teacher_id` (uuid, FK) - Profesor responsable del grupo
  - `lab_id` (integer, FK) - Laboratorio asignado
  - `schedule` (jsonb) - Horario de clases del grupo
  - `is_active` (boolean) - Si el grupo está activo
  - `created_at` (timestamp) - Fecha de creación
  - `updated_at` (timestamp) - Fecha de actualización

  ### `group_members`
  Estudiantes que pertenecen a cada grupo
  - `id` (serial, primary key) - Identificador único
  - `group_id` (integer, FK) - Grupo al que pertenece
  - `student_id` (uuid, FK) - ID del estudiante
  - `joined_at` (timestamp) - Fecha de incorporación al grupo

  ### `computer_assignments`
  Asignación de computadoras a grupos o estudiantes específicos
  - `id` (serial, primary key) - Identificador de asignación
  - `computer_id` (integer, FK) - Computadora asignada
  - `group_id` (integer, FK) - Grupo asignado (opcional)
  - `student_id` (uuid, FK) - Estudiante asignado (opcional)
  - `assigned_by` (uuid, FK) - Usuario que realizó la asignación
  - `assigned_at` (timestamp) - Fecha de asignación
  - `expires_at` (timestamp) - Fecha de expiración (opcional)
  - `notes` (text) - Notas adicionales

  ### `computer_actions`
  Registro de acciones realizadas sobre las computadoras (encender, apagar, reiniciar)
  - `id` (serial, primary key) - Identificador de acción
  - `computer_id` (integer, FK) - Computadora afectada
  - `action_type` (text) - Tipo: 'power_on', 'power_off', 'restart', 'lock', 'unlock'
  - `performed_by` (uuid, FK) - Usuario que ejecutó la acción
  - `status` (text) - Estado: 'pending', 'in_progress', 'completed', 'failed'
  - `result` (text) - Resultado de la acción
  - `performed_at` (timestamp) - Fecha de ejecución

  ### `lab_sessions`
  Sesiones de uso del laboratorio por grupos
  - `id` (serial, primary key) - Identificador de sesión
  - `lab_id` (integer, FK) - Laboratorio utilizado
  - `group_id` (integer, FK) - Grupo que usa el laboratorio
  - `teacher_id` (uuid, FK) - Profesor a cargo
  - `started_at` (timestamp) - Inicio de la sesión
  - `ended_at` (timestamp) - Fin de la sesión (nullable)
  - `notes` (text) - Observaciones de la sesión

  ## 2. Seguridad (RLS)
  - Administradores: acceso completo a todas las tablas
  - Profesores: acceso a sus grupos, laboratorios asignados y computadoras relacionadas
  - Estudiantes: solo visualización de su grupo y computadora asignada
  - Todas las tablas tienen RLS habilitado

  ## 3. Índices
  - Índices en claves foráneas para mejor rendimiento
  - Índices en campos de búsqueda frecuente (status, ip_address, etc.)

  ## 4. Funciones auxiliares
  - Trigger para actualización automática de timestamps
  - Funciones para verificar permisos según rol
*/

-- Tabla de laboratorios
CREATE TABLE IF NOT EXISTS computer_labs (
    id SERIAL PRIMARY KEY,
    name TEXT NOT NULL,
    location TEXT,
    capacity INTEGER DEFAULT 30,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Tabla de computadoras
CREATE TABLE IF NOT EXISTS computers (
    id SERIAL PRIMARY KEY,
    lab_id INTEGER REFERENCES computer_labs(id) ON DELETE SET NULL,
    name TEXT NOT NULL,
    ip_address TEXT,
    mac_address TEXT,
    status TEXT DEFAULT 'offline' CHECK (status IN ('online', 'offline', 'maintenance', 'error')),
    last_boot TIMESTAMP WITH TIME ZONE,
    last_seen TIMESTAMP WITH TIME ZONE,
    specs JSONB DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Tabla de grupos de estudiantes
CREATE TABLE IF NOT EXISTS student_groups (
    id SERIAL PRIMARY KEY,
    name TEXT NOT NULL,
    teacher_id UUID REFERENCES users(id) ON DELETE SET NULL,
    lab_id INTEGER REFERENCES computer_labs(id) ON DELETE SET NULL,
    schedule JSONB DEFAULT '{}',
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Tabla de miembros de grupos
CREATE TABLE IF NOT EXISTS group_members (
    id SERIAL PRIMARY KEY,
    group_id INTEGER REFERENCES student_groups(id) ON DELETE CASCADE,
    student_id UUID REFERENCES users(id) ON DELETE CASCADE,
    joined_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    UNIQUE(group_id, student_id)
);

-- Tabla de asignaciones de computadoras
CREATE TABLE IF NOT EXISTS computer_assignments (
    id SERIAL PRIMARY KEY,
    computer_id INTEGER REFERENCES computers(id) ON DELETE CASCADE,
    group_id INTEGER REFERENCES student_groups(id) ON DELETE CASCADE,
    student_id UUID REFERENCES users(id) ON DELETE CASCADE,
    assigned_by UUID REFERENCES users(id) ON DELETE SET NULL,
    assigned_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    expires_at TIMESTAMP WITH TIME ZONE,
    notes TEXT
);

-- Tabla de acciones sobre computadoras
CREATE TABLE IF NOT EXISTS computer_actions (
    id SERIAL PRIMARY KEY,
    computer_id INTEGER REFERENCES computers(id) ON DELETE CASCADE,
    action_type TEXT NOT NULL CHECK (action_type IN ('power_on', 'power_off', 'restart', 'lock', 'unlock')),
    performed_by UUID REFERENCES users(id) ON DELETE SET NULL,
    status TEXT DEFAULT 'pending' CHECK (status IN ('pending', 'in_progress', 'completed', 'failed')),
    result TEXT,
    performed_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Tabla de sesiones de laboratorio
CREATE TABLE IF NOT EXISTS lab_sessions (
    id SERIAL PRIMARY KEY,
    lab_id INTEGER REFERENCES computer_labs(id) ON DELETE CASCADE,
    group_id INTEGER REFERENCES student_groups(id) ON DELETE SET NULL,
    teacher_id UUID REFERENCES users(id) ON DELETE SET NULL,
    started_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    ended_at TIMESTAMP WITH TIME ZONE,
    notes TEXT
);

-- Crear índices
CREATE INDEX IF NOT EXISTS idx_computers_lab_id ON computers(lab_id);
CREATE INDEX IF NOT EXISTS idx_computers_status ON computers(status);
CREATE INDEX IF NOT EXISTS idx_computers_ip_address ON computers(ip_address);
CREATE INDEX IF NOT EXISTS idx_computers_mac_address ON computers(mac_address);
CREATE INDEX IF NOT EXISTS idx_student_groups_teacher_id ON student_groups(teacher_id);
CREATE INDEX IF NOT EXISTS idx_student_groups_lab_id ON student_groups(lab_id);
CREATE INDEX IF NOT EXISTS idx_group_members_group_id ON group_members(group_id);
CREATE INDEX IF NOT EXISTS idx_group_members_student_id ON group_members(student_id);
CREATE INDEX IF NOT EXISTS idx_computer_assignments_computer_id ON computer_assignments(computer_id);
CREATE INDEX IF NOT EXISTS idx_computer_assignments_group_id ON computer_assignments(group_id);
CREATE INDEX IF NOT EXISTS idx_computer_actions_computer_id ON computer_actions(computer_id);
CREATE INDEX IF NOT EXISTS idx_lab_sessions_lab_id ON lab_sessions(lab_id);

-- Habilitar Row Level Security
ALTER TABLE computer_labs ENABLE ROW LEVEL SECURITY;
ALTER TABLE computers ENABLE ROW LEVEL SECURITY;
ALTER TABLE student_groups ENABLE ROW LEVEL SECURITY;
ALTER TABLE group_members ENABLE ROW LEVEL SECURITY;
ALTER TABLE computer_assignments ENABLE ROW LEVEL SECURITY;
ALTER TABLE computer_actions ENABLE ROW LEVEL SECURITY;
ALTER TABLE lab_sessions ENABLE ROW LEVEL SECURITY;

-- Función auxiliar para verificar si el usuario es admin
CREATE OR REPLACE FUNCTION is_admin()
RETURNS BOOLEAN AS $$
BEGIN
    RETURN EXISTS (
        SELECT 1 FROM users
        WHERE id = auth.uid() AND role = 'admin'
    );
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Función auxiliar para verificar si el usuario es profesor
CREATE OR REPLACE FUNCTION is_teacher()
RETURNS BOOLEAN AS $$
BEGIN
    RETURN EXISTS (
        SELECT 1 FROM users
        WHERE id = auth.uid() AND role IN ('teacher', 'admin')
    );
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Políticas para computer_labs
CREATE POLICY "Admins can manage all labs" ON computer_labs
    FOR ALL
    TO authenticated
    USING (is_admin())
    WITH CHECK (is_admin());

CREATE POLICY "Teachers can view labs" ON computer_labs
    FOR SELECT
    TO authenticated
    USING (is_teacher());

CREATE POLICY "Students can view active labs" ON computer_labs
    FOR SELECT
    TO authenticated
    USING (is_active = true);

-- Políticas para computers
CREATE POLICY "Admins can manage all computers" ON computers
    FOR ALL
    TO authenticated
    USING (is_admin())
    WITH CHECK (is_admin());

CREATE POLICY "Teachers can view computers" ON computers
    FOR SELECT
    TO authenticated
    USING (
        is_teacher() OR
        EXISTS (
            SELECT 1 FROM student_groups sg
            WHERE sg.lab_id = computers.lab_id AND sg.teacher_id = auth.uid()
        )
    );

CREATE POLICY "Students can view assigned computers" ON computers
    FOR SELECT
    TO authenticated
    USING (
        EXISTS (
            SELECT 1 FROM computer_assignments ca
            WHERE ca.computer_id = computers.id 
            AND (ca.student_id = auth.uid() OR ca.group_id IN (
                SELECT group_id FROM group_members WHERE student_id = auth.uid()
            ))
        )
    );

-- Políticas para student_groups
CREATE POLICY "Admins can manage all groups" ON student_groups
    FOR ALL
    TO authenticated
    USING (is_admin())
    WITH CHECK (is_admin());

CREATE POLICY "Teachers can manage own groups" ON student_groups
    FOR ALL
    TO authenticated
    USING (teacher_id = auth.uid())
    WITH CHECK (teacher_id = auth.uid());

CREATE POLICY "Students can view their groups" ON student_groups
    FOR SELECT
    TO authenticated
    USING (
        EXISTS (
            SELECT 1 FROM group_members
            WHERE group_id = student_groups.id AND student_id = auth.uid()
        )
    );

-- Políticas para group_members
CREATE POLICY "Admins can manage all group members" ON group_members
    FOR ALL
    TO authenticated
    USING (is_admin())
    WITH CHECK (is_admin());

CREATE POLICY "Teachers can manage their group members" ON group_members
    FOR ALL
    TO authenticated
    USING (
        EXISTS (
            SELECT 1 FROM student_groups
            WHERE id = group_members.group_id AND teacher_id = auth.uid()
        )
    )
    WITH CHECK (
        EXISTS (
            SELECT 1 FROM student_groups
            WHERE id = group_members.group_id AND teacher_id = auth.uid()
        )
    );

CREATE POLICY "Students can view their group members" ON group_members
    FOR SELECT
    TO authenticated
    USING (
        student_id = auth.uid() OR
        EXISTS (
            SELECT 1 FROM group_members gm2
            WHERE gm2.group_id = group_members.group_id AND gm2.student_id = auth.uid()
        )
    );

-- Políticas para computer_assignments
CREATE POLICY "Admins can manage all assignments" ON computer_assignments
    FOR ALL
    TO authenticated
    USING (is_admin())
    WITH CHECK (is_admin());

CREATE POLICY "Teachers can manage assignments for their groups" ON computer_assignments
    FOR ALL
    TO authenticated
    USING (
        EXISTS (
            SELECT 1 FROM student_groups
            WHERE id = computer_assignments.group_id AND teacher_id = auth.uid()
        )
    )
    WITH CHECK (
        EXISTS (
            SELECT 1 FROM student_groups
            WHERE id = computer_assignments.group_id AND teacher_id = auth.uid()
        )
    );

CREATE POLICY "Students can view their assignments" ON computer_assignments
    FOR SELECT
    TO authenticated
    USING (
        student_id = auth.uid() OR
        EXISTS (
            SELECT 1 FROM group_members
            WHERE group_id = computer_assignments.group_id AND student_id = auth.uid()
        )
    );

-- Políticas para computer_actions
CREATE POLICY "Admins can manage all actions" ON computer_actions
    FOR ALL
    TO authenticated
    USING (is_admin())
    WITH CHECK (is_admin());

CREATE POLICY "Teachers can manage actions for their labs" ON computer_actions
    FOR ALL
    TO authenticated
    USING (
        EXISTS (
            SELECT 1 FROM computers c
            JOIN student_groups sg ON c.lab_id = sg.lab_id
            WHERE c.id = computer_actions.computer_id AND sg.teacher_id = auth.uid()
        )
    )
    WITH CHECK (
        EXISTS (
            SELECT 1 FROM computers c
            JOIN student_groups sg ON c.lab_id = sg.lab_id
            WHERE c.id = computer_actions.computer_id AND sg.teacher_id = auth.uid()
        )
    );

CREATE POLICY "Users can view their own actions" ON computer_actions
    FOR SELECT
    TO authenticated
    USING (performed_by = auth.uid());

-- Políticas para lab_sessions
CREATE POLICY "Admins can manage all sessions" ON lab_sessions
    FOR ALL
    TO authenticated
    USING (is_admin())
    WITH CHECK (is_admin());

CREATE POLICY "Teachers can manage own sessions" ON lab_sessions
    FOR ALL
    TO authenticated
    USING (teacher_id = auth.uid())
    WITH CHECK (teacher_id = auth.uid());

CREATE POLICY "Students can view their group sessions" ON lab_sessions
    FOR SELECT
    TO authenticated
    USING (
        EXISTS (
            SELECT 1 FROM group_members
            WHERE group_id = lab_sessions.group_id AND student_id = auth.uid()
        )
    );

-- Triggers para actualizar timestamps
DROP TRIGGER IF EXISTS update_computer_labs_updated_at ON computer_labs;
CREATE TRIGGER update_computer_labs_updated_at BEFORE UPDATE ON computer_labs
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_computers_updated_at ON computers;
CREATE TRIGGER update_computers_updated_at BEFORE UPDATE ON computers
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_student_groups_updated_at ON student_groups;
CREATE TRIGGER update_student_groups_updated_at BEFORE UPDATE ON student_groups
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
