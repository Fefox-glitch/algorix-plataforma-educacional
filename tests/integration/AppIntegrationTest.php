<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/Utils/functions.php';

class AppIntegrationTest extends TestCase {
    public function testHomeViewRendersWelcomeAndLinks() {
        $html = render_view('home');
        $this->assertIsString($html);
        $this->assertStringContainsString('¡Bienvenido a Algorix!', $html);
        // Verificamos que base_url genere rutas internas
        $this->assertStringContainsString('/auth/login', $html);
        $this->assertStringContainsString('/auth/register', $html);
    }
}