<?php

namespace tests\unit\modules\serviceProfile;

use app\modules\jd\components\RichText;
use Codeception\Test\Unit;

class RichTextTableTest extends Unit
{
    public function testPreservesSafeTableStructure(): void
    {
        $html = '<table class="table table-bordered"><thead><tr><th scope="col">หัวข้อ</th></tr></thead><tbody><tr><td colspan="2">ข้อมูล</td></tr></tbody></table>';
        $clean = RichText::sanitize($html);

        $this->assertStringContainsString('<table', $clean);
        $this->assertStringContainsString('<thead>', $clean);
        $this->assertStringContainsString('<th scope="col">', $clean);
        $this->assertStringContainsString('<td colspan="2">', $clean);
    }

    public function testRemovesUnsafeTableAttributesAndScripts(): void
    {
        $clean = RichText::sanitize('<table onclick="alert(1)"><tr><td><script>alert(1)</script>ข้อมูล</td></tr></table>');

        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringContainsString('<table>', $clean);
        $this->assertStringContainsString('ข้อมูล', $clean);
    }
}
