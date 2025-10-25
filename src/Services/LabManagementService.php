<?php

namespace App\Services;

use PDO;

class LabManagementService
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAllLabs()
    {
        $stmt = $this->db->query("SELECT * FROM computer_labs ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLabById($labId)
    {
        $stmt = $this->db->prepare("SELECT * FROM computer_labs WHERE id = ?");
        $stmt->execute([$labId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createLab($name, $location, $capacity)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO computer_labs (name, location, capacity) VALUES (?, ?, ?) RETURNING id"
        );
        $stmt->execute([$name, $location, $capacity]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['id'];
    }

    public function updateLab($labId, $name, $location, $capacity, $isActive)
    {
        $stmt = $this->db->prepare(
            "UPDATE computer_labs SET name = ?, location = ?, capacity = ?, is_active = ? WHERE id = ?"
        );
        return $stmt->execute([$name, $location, $capacity, $isActive, $labId]);
    }

    public function getComputersByLab($labId)
    {
        $stmt = $this->db->prepare("SELECT * FROM computers WHERE lab_id = ? ORDER BY name");
        $stmt->execute([$labId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllComputers()
    {
        $stmt = $this->db->query("
            SELECT c.*, cl.name as lab_name
            FROM computers c
            LEFT JOIN computer_labs cl ON c.lab_id = cl.id
            ORDER BY c.name
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getComputerById($computerId)
    {
        $stmt = $this->db->prepare("SELECT * FROM computers WHERE id = ?");
        $stmt->execute([$computerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createComputer($labId, $name, $ipAddress, $macAddress, $specs)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO computers (lab_id, name, ip_address, mac_address, specs)
             VALUES (?, ?, ?, ?, ?::jsonb) RETURNING id"
        );
        $stmt->execute([$labId, $name, $ipAddress, $macAddress, json_encode($specs)]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['id'];
    }

    public function updateComputer($computerId, $labId, $name, $ipAddress, $macAddress, $status, $specs)
    {
        $stmt = $this->db->prepare(
            "UPDATE computers
             SET lab_id = ?, name = ?, ip_address = ?, mac_address = ?, status = ?, specs = ?::jsonb
             WHERE id = ?"
        );
        return $stmt->execute([$labId, $name, $ipAddress, $macAddress, $status, json_encode($specs), $computerId]);
    }

    public function updateComputerStatus($computerId, $status, $lastBoot = null, $lastSeen = null)
    {
        $query = "UPDATE computers SET status = ?";
        $params = [$status];

        if ($lastBoot !== null) {
            $query .= ", last_boot = ?";
            $params[] = $lastBoot;
        }

        if ($lastSeen !== null) {
            $query .= ", last_seen = ?";
            $params[] = $lastSeen;
        }

        $query .= " WHERE id = ?";
        $params[] = $computerId;

        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    public function performComputerAction($computerId, $actionType, $performedBy)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO computer_actions (computer_id, action_type, performed_by)
             VALUES (?, ?, ?) RETURNING id"
        );
        $stmt->execute([$computerId, $actionType, $performedBy]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['id'];
    }

    public function updateActionStatus($actionId, $status, $result = null)
    {
        $stmt = $this->db->prepare(
            "UPDATE computer_actions SET status = ?, result = ? WHERE id = ?"
        );
        return $stmt->execute([$status, $result, $actionId]);
    }

    public function getComputerActions($computerId, $limit = 50)
    {
        $stmt = $this->db->prepare(
            "SELECT ca.*, u.name as performed_by_name
             FROM computer_actions ca
             LEFT JOIN users u ON ca.performed_by = u.id
             WHERE ca.computer_id = ?
             ORDER BY ca.performed_at DESC
             LIMIT ?"
        );
        $stmt->execute([$computerId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getComputersByStatus($status)
    {
        $stmt = $this->db->prepare("
            SELECT c.*, cl.name as lab_name
            FROM computers c
            LEFT JOIN computer_labs cl ON c.lab_id = cl.id
            WHERE c.status = ?
            ORDER BY c.name
        ");
        $stmt->execute([$status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteComputer($computerId)
    {
        $stmt = $this->db->prepare("DELETE FROM computers WHERE id = ?");
        return $stmt->execute([$computerId]);
    }

    public function deleteLab($labId)
    {
        $stmt = $this->db->prepare("DELETE FROM computer_labs WHERE id = ?");
        return $stmt->execute([$labId]);
    }
}
