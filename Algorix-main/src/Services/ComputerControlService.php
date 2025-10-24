<?php

namespace App\Services;

class ComputerControlService
{
    private $labService;

    public function __construct($labService)
    {
        $this->labService = $labService;
    }

    public function powerOn($computerId, $userId)
    {
        $computer = $this->labService->getComputerById($computerId);

        if (!$computer) {
            return ['success' => false, 'message' => 'Computadora no encontrada'];
        }

        $actionId = $this->labService->performComputerAction($computerId, 'power_on', $userId);

        $this->labService->updateActionStatus($actionId, 'in_progress');

        $result = $this->sendWakeOnLan($computer['mac_address'], $computer['ip_address']);

        if ($result['success']) {
            $this->labService->updateActionStatus($actionId, 'completed', 'Computadora encendida exitosamente');
            $this->labService->updateComputerStatus($computerId, 'online', date('Y-m-d H:i:s'), date('Y-m-d H:i:s'));
            return ['success' => true, 'message' => 'Comando de encendido enviado'];
        } else {
            $this->labService->updateActionStatus($actionId, 'failed', $result['message']);
            return ['success' => false, 'message' => $result['message']];
        }
    }

    public function powerOff($computerId, $userId)
    {
        $computer = $this->labService->getComputerById($computerId);

        if (!$computer) {
            return ['success' => false, 'message' => 'Computadora no encontrada'];
        }

        $actionId = $this->labService->performComputerAction($computerId, 'power_off', $userId);

        $this->labService->updateActionStatus($actionId, 'in_progress');

        $result = $this->sendShutdownCommand($computer['ip_address']);

        if ($result['success']) {
            $this->labService->updateActionStatus($actionId, 'completed', 'Computadora apagada exitosamente');
            $this->labService->updateComputerStatus($computerId, 'offline');
            return ['success' => true, 'message' => 'Comando de apagado enviado'];
        } else {
            $this->labService->updateActionStatus($actionId, 'failed', $result['message']);
            return ['success' => false, 'message' => $result['message']];
        }
    }

    public function restart($computerId, $userId)
    {
        $computer = $this->labService->getComputerById($computerId);

        if (!$computer) {
            return ['success' => false, 'message' => 'Computadora no encontrada'];
        }

        $actionId = $this->labService->performComputerAction($computerId, 'restart', $userId);

        $this->labService->updateActionStatus($actionId, 'in_progress');

        $result = $this->sendRestartCommand($computer['ip_address']);

        if ($result['success']) {
            $this->labService->updateActionStatus($actionId, 'completed', 'Computadora reiniciada exitosamente');
            $this->labService->updateComputerStatus($computerId, 'online', date('Y-m-d H:i:s'));
            return ['success' => true, 'message' => 'Comando de reinicio enviado'];
        } else {
            $this->labService->updateActionStatus($actionId, 'failed', $result['message']);
            return ['success' => false, 'message' => $result['message']];
        }
    }

    public function lock($computerId, $userId)
    {
        $computer = $this->labService->getComputerById($computerId);

        if (!$computer) {
            return ['success' => false, 'message' => 'Computadora no encontrada'];
        }

        $actionId = $this->labService->performComputerAction($computerId, 'lock', $userId);

        $this->labService->updateActionStatus($actionId, 'in_progress');

        $result = $this->sendLockCommand($computer['ip_address']);

        if ($result['success']) {
            $this->labService->updateActionStatus($actionId, 'completed', 'Computadora bloqueada exitosamente');
            return ['success' => true, 'message' => 'Comando de bloqueo enviado'];
        } else {
            $this->labService->updateActionStatus($actionId, 'failed', $result['message']);
            return ['success' => false, 'message' => $result['message']];
        }
    }

    public function performBulkAction($computerIds, $action, $userId)
    {
        $results = [];

        foreach ($computerIds as $computerId) {
            switch ($action) {
                case 'power_on':
                    $results[$computerId] = $this->powerOn($computerId, $userId);
                    break;
                case 'power_off':
                    $results[$computerId] = $this->powerOff($computerId, $userId);
                    break;
                case 'restart':
                    $results[$computerId] = $this->restart($computerId, $userId);
                    break;
                case 'lock':
                    $results[$computerId] = $this->lock($computerId, $userId);
                    break;
            }
        }

        return $results;
    }

    private function sendWakeOnLan($macAddress, $ipAddress)
    {
        if (empty($macAddress)) {
            return ['success' => false, 'message' => 'Dirección MAC no configurada'];
        }

        $macAddress = str_replace([':', '-'], '', $macAddress);

        if (strlen($macAddress) != 12) {
            return ['success' => false, 'message' => 'Dirección MAC inválida'];
        }

        $macBinary = '';
        for ($i = 0; $i < 12; $i += 2) {
            $macBinary .= chr(hexdec(substr($macAddress, $i, 2)));
        }

        $packet = str_repeat(chr(255), 6) . str_repeat($macBinary, 16);

        $broadcast = '255.255.255.255';
        if (!empty($ipAddress)) {
            $ipParts = explode('.', $ipAddress);
            $broadcast = $ipParts[0] . '.' . $ipParts[1] . '.' . $ipParts[2] . '.255';
        }

        $socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($socket === false) {
            return ['success' => false, 'message' => 'No se pudo crear socket UDP'];
        }

        $result = @socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, 1);
        if ($result === false) {
            socket_close($socket);
            return ['success' => false, 'message' => 'No se pudo configurar broadcast'];
        }

        $result = @socket_sendto($socket, $packet, strlen($packet), 0, $broadcast, 9);
        socket_close($socket);

        if ($result === false) {
            return ['success' => false, 'message' => 'Error al enviar paquete WOL'];
        }

        return ['success' => true, 'message' => 'Paquete WOL enviado'];
    }

    private function sendShutdownCommand($ipAddress)
    {
        if (empty($ipAddress)) {
            return ['success' => false, 'message' => 'Dirección IP no configurada'];
        }

        $result = @fsockopen($ipAddress, 8080, $errno, $errstr, 5);

        if ($result !== false) {
            $command = json_encode(['action' => 'shutdown']);
            fwrite($result, $command);
            fclose($result);
            return ['success' => true, 'message' => 'Comando enviado'];
        }

        return ['success' => false, 'message' => 'No se pudo conectar con la computadora'];
    }

    private function sendRestartCommand($ipAddress)
    {
        if (empty($ipAddress)) {
            return ['success' => false, 'message' => 'Dirección IP no configurada'];
        }

        $result = @fsockopen($ipAddress, 8080, $errno, $errstr, 5);

        if ($result !== false) {
            $command = json_encode(['action' => 'restart']);
            fwrite($result, $command);
            fclose($result);
            return ['success' => true, 'message' => 'Comando enviado'];
        }

        return ['success' => false, 'message' => 'No se pudo conectar con la computadora'];
    }

    private function sendLockCommand($ipAddress)
    {
        if (empty($ipAddress)) {
            return ['success' => false, 'message' => 'Dirección IP no configurada'];
        }

        $result = @fsockopen($ipAddress, 8080, $errno, $errstr, 5);

        if ($result !== false) {
            $command = json_encode(['action' => 'lock']);
            fwrite($result, $command);
            fclose($result);
            return ['success' => true, 'message' => 'Comando enviado'];
        }

        return ['success' => false, 'message' => 'No se pudo conectar con la computadora'];
    }

    public function getComputerStatus($ipAddress)
    {
        if (empty($ipAddress)) {
            return ['status' => 'unknown', 'message' => 'IP no configurada'];
        }

        $ping = @exec("ping -c 1 -W 1 $ipAddress", $output, $returnVar);

        if ($returnVar === 0) {
            return ['status' => 'online', 'message' => 'Computadora en línea'];
        }

        return ['status' => 'offline', 'message' => 'Computadora fuera de línea'];
    }
}
