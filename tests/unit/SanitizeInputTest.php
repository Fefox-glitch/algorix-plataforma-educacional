<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/Utils/functions.php';

class SanitizeInputTest extends TestCase {
    public function testSanitizeStringEscapesHtml() {
        $input = " <script>alert('x')</script> ";
        $out = sanitize_input($input);
        $this->assertSame("&lt;script&gt;alert('x')&lt;/script&gt;", $out);
    }

    public function testSanitizeArrayRecurses() {
        $input = [
            'name' => " <b>John</b> ",
            'nested' => [" <i>Doe</i> "]
        ];
        $out = sanitize_input($input);
        $this->assertSame('&lt;b&gt;John&lt;/b&gt;', $out['name']);
        $this->assertSame('&lt;i&gt;Doe&lt;/i&gt;', $out['nested'][0]);
    }
}