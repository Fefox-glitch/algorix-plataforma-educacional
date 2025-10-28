<?php

namespace App\Services;

class ComputerControlService
{
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db;
    }

    /**
     * Verifica si un equipo está activo mediante ping
     * Versión segura y compatible con Windows/Linux
     */
    public function checkComputerStatus($ipAddress)
    {
        // Sanitizar la dirección IP para prevenir inyección de comandos
        if (!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            return false;
        }

        return $this->safePing($ipAddress);
    }

    /**
     * Realiza un "ping" sin usar exec: prueba puertos comunes vía sockets
     */
    private function safePing($ip)
    {
        $ports = [80, 443, 22, 3389]; // HTTP/HTTPS/SSH/RDP
        foreach ($ports as $p) {
            if ($this->checkPortOpen($ip, $p, 1)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Alternativa más segura usando sockets en lugar de exec
     */
    public function checkPortOpen($ip, $port = 80, $timeout = 1)
    {
        $fp = @fsockopen($ip, $port, $errno, $errstr, $timeout);
        if ($fp) {
            fclose($fp);
            return true;
        }
        return false;
    }

    /**
     * Obtiene la lista de equipos desde la base de datos
     */
    public function getComputerList()
    {
        if (!$this->db) {
            throw new \Exception("Database connection not initialized");
        }

        // Consulta segura usando prepared statements
        $stmt = $this->db->prepare("SELECT * FROM computers WHERE active = 1");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza el estado de un equipo
     */
    public function updateComputerStatus($computerId, $status)
    {
        if (!$this->db) {
            throw new \Exception("Database connection not initialized");
        }

        // Validar entradas
        $computerId = filter_var($computerId, FILTER_VALIDATE_INT);
        $status = filter_var($status, FILTER_VALIDATE_BOOLEAN);
        if ($computerId === false) {
            throw new \Exception("Invalid computer ID");
        }

        // Consulta segura usando prepared statements
        $stmt = $this->db->prepare("UPDATE computers SET active = :status WHERE id = :id");
        $stmt->bindParam(':status', $status, \PDO::PARAM_BOOL);
        $stmt->bindParam(':id', $computerId, \PDO::PARAM_INT);
        return $stmt->execute();
    }
}
