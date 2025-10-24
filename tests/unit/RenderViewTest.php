<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/Utils/functions.php';

class RenderViewTest extends TestCase {
    public function testRenderHomeContainsWelcomeTitle() {
        // Renderiza solo la vista (sin layout)
        $html = render_view('home');
        $this->assertIsString($html);
        $this->assertStringContainsString('¡Bienvenido a Algorix!', $html);
    }

    public function testRenderErrorProducesContent() {
        $html = render_error(404, 'Prueba');
        $this->assertIsString($html);
        $this->assertNotEmpty($html);
    }
}