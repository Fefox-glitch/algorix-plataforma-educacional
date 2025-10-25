<?php

namespace App\Services;

use PDO;

class GroupManagementService
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAllGroups()
    {
        $stmt = $this->db->query("
            SELECT sg.*, u.name as teacher_name, cl.name as lab_name,
                   (SELECT COUNT(*) FROM group_members WHERE group_id = sg.id) as member_count
            FROM student_groups sg
            LEFT JOIN users u ON sg.teacher_id = u.id
            LEFT JOIN computer_labs cl ON sg.lab_id = cl.id
            ORDER BY sg.name
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGroupsByTeacher($teacherId)
    {
        $stmt = $this->db->prepare("
            SELECT sg.*, cl.name as lab_name,
                   (SELECT COUNT(*) FROM group_members WHERE group_id = sg.id) as member_count
            FROM student_groups sg
            LEFT JOIN computer_labs cl ON sg.lab_id = cl.id
            WHERE sg.teacher_id = ?
            ORDER BY sg.name
        ");
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGroupById($groupId)
    {
        $stmt = $this->db->prepare("
            SELECT sg.*, u.name as teacher_name, cl.name as lab_name
            FROM student_groups sg
            LEFT JOIN users u ON sg.teacher_id = u.id
            LEFT JOIN computer_labs cl ON sg.lab_id = cl.id
            WHERE sg.id = ?
        ");
        $stmt->execute([$groupId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createGroup($name, $teacherId, $labId, $schedule)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO student_groups (name, teacher_id, lab_id, schedule)
             VALUES (?, ?, ?, ?::jsonb) RETURNING id"
        );
        $stmt->execute([$name, $teacherId, $labId, json_encode($schedule)]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['id'];
    }

    public function updateGroup($groupId, $name, $teacherId, $labId, $schedule, $isActive)
    {
        $stmt = $this->db->prepare(
            "UPDATE student_groups
             SET name = ?, teacher_id = ?, lab_id = ?, schedule = ?::jsonb, is_active = ?
             WHERE id = ?"
        );
        return $stmt->execute([$name, $teacherId, $labId, json_encode($schedule), $isActive, $groupId]);
    }

    public function deleteGroup($groupId)
    {
        $stmt = $this->db->prepare("DELETE FROM student_groups WHERE id = ?");
        return $stmt->execute([$groupId]);
    }

    public function getGroupMembers($groupId)
    {
        $stmt = $this->db->prepare("
            SELECT gm.*, u.name as student_name, u.email as student_email
            FROM group_members gm
            JOIN users u ON gm.student_id = u.id
            WHERE gm.group_id = ?
            ORDER BY u.name
        ");
        $stmt->execute([$groupId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addGroupMember($groupId, $studentId)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO group_members (group_id, student_id) VALUES (?, ?)
             ON CONFLICT (group_id, student_id) DO NOTHING RETURNING id"
        );
        $stmt->execute([$groupId, $studentId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['id'] : null;
    }

    public function removeGroupMember($groupId, $studentId)
    {
        $stmt = $this->db->prepare(
            "DELETE FROM group_members WHERE group_id = ? AND student_id = ?"
        );
        return $stmt->execute([$groupId, $studentId]);
    }

    public function getStudentGroups($studentId)
    {
        $stmt = $this->db->prepare("
            SELECT sg.*, u.name as teacher_name, cl.name as lab_name
            FROM student_groups sg
            JOIN group_members gm ON sg.id = gm.group_id
            LEFT JOIN users u ON sg.teacher_id = u.id
            LEFT JOIN computer_labs cl ON sg.lab_id = cl.id
            WHERE gm.student_id = ?
            ORDER BY sg.name
        ");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function assignComputerToGroup($computerId, $groupId, $assignedBy, $expiresAt = null, $notes = null)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO computer_assignments (computer_id, group_id, assigned_by, expires_at, notes)
             VALUES (?, ?, ?, ?, ?) RETURNING id"
        );
        $stmt->execute([$computerId, $groupId, $assignedBy, $expiresAt, $notes]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['id'];
    }

    public function assignComputerToStudent($computerId, $studentId, $assignedBy, $expiresAt = null, $notes = null)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO computer_assignments (computer_id, student_id, assigned_by, expires_at, notes)
             VALUES (?, ?, ?, ?, ?) RETURNING id"
        );
        $stmt->execute([$computerId, $studentId, $assignedBy, $expiresAt, $notes]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['id'];
    }

    public function getGroupAssignments($groupId)
    {
        $stmt = $this->db->prepare("
            SELECT ca.*, c.name as computer_name, c.ip_address, c.status,
                   u.name as assigned_by_name
            FROM computer_assignments ca
            JOIN computers c ON ca.computer_id = c.id
            LEFT JOIN users u ON ca.assigned_by = u.id
            WHERE ca.group_id = ?
            ORDER BY ca.assigned_at DESC
        ");
        $stmt->execute([$groupId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStudentAssignments($studentId)
    {
        $stmt = $this->db->prepare("
            SELECT ca.*, c.name as computer_name, c.ip_address, c.status,
                   u.name as assigned_by_name
            FROM computer_assignments ca
            JOIN computers c ON ca.computer_id = c.id
            LEFT JOIN users u ON ca.assigned_by = u.id
            WHERE ca.student_id = ?
            ORDER BY ca.assigned_at DESC
        ");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function removeAssignment($assignmentId)
    {
        $stmt = $this->db->prepare("DELETE FROM computer_assignments WHERE id = ?");
        return $stmt->execute([$assignmentId]);
    }

    public function createLabSession($labId, $groupId, $teacherId, $notes = null)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO lab_sessions (lab_id, group_id, teacher_id, notes)
             VALUES (?, ?, ?, ?) RETURNING id"
        );
        $stmt->execute([$labId, $groupId, $teacherId, $notes]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['id'];
    }

    public function endLabSession($sessionId, $notes = null)
    {
        $stmt = $this->db->prepare(
            "UPDATE lab_sessions SET ended_at = NOW(), notes = COALESCE(?, notes) WHERE id = ?"
        );
        return $stmt->execute([$notes, $sessionId]);
    }

    public function getActiveLabSessions()
    {
        $stmt = $this->db->query("
            SELECT ls.*, cl.name as lab_name, sg.name as group_name, u.name as teacher_name
            FROM lab_sessions ls
            JOIN computer_labs cl ON ls.lab_id = cl.id
            LEFT JOIN student_groups sg ON ls.group_id = sg.id
            LEFT JOIN users u ON ls.teacher_id = u.id
            WHERE ls.ended_at IS NULL
            ORDER BY ls.started_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGroupSessions($groupId, $limit = 20)
    {
        $stmt = $this->db->prepare("
            SELECT ls.*, cl.name as lab_name, u.name as teacher_name
            FROM lab_sessions ls
            JOIN computer_labs cl ON ls.lab_id = cl.id
            LEFT JOIN users u ON ls.teacher_id = u.id
            WHERE ls.group_id = ?
            ORDER BY ls.started_at DESC
            LIMIT ?
        ");
        $stmt->execute([$groupId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
