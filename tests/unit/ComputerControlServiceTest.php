<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/Services/ComputerControlService.php';

class ComputerControlServiceTest extends TestCase {
    public function testCheckPortOpenReturnsBoolean() {
        $svc = new App\Services\ComputerControlService();
        $result = $svc->checkPortOpen('127.0.0.1', 65535, 1);
        $this->assertIsBool($result);
    }

    public function testCheckPortOpenLikelyClosedPortIsFalse() {
        $svc = new App\Services\ComputerControlService();
        $this->assertFalse($svc->checkPortOpen('127.0.0.1', 65535, 1));
    }
}