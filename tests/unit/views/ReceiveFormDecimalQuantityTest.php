<?php

namespace tests\unit\views;

use Codeception\Test\Unit;

class ReceiveFormDecimalQuantityTest extends Unit
{
    public function testEveryQuantityInputAcceptsTwoDecimalPlaces(): void
    {
        $view = file_get_contents(__DIR__ . '/../../../modules/inventoryV2/views/receive/_form.php');

        $this->assertNotFalse($view);
        $this->assertSame(3, substr_count($view, 'class="form-control text-center qty-input"'));
        $this->assertSame(3, substr_count($view, 'min="0.01" step="0.01" inputmode="decimal"'));
        $this->assertStringNotContainsString('qty-input" value="<?= $item->qty ?>" min="1" step="1"', $view);
        $this->assertStringNotContainsString('qty-input" value="1" min="1" step="1"', $view);
    }

    public function testExpiryDateConversionAcceptsGregorianAndBuddhistYears(): void
    {
        $view = file_get_contents(__DIR__ . '/../../../modules/inventoryV2/views/receive/_form.php');

        $this->assertNotFalse($view);
        $this->assertStringContainsString('if (y >= 2400) y -= 543;', $view);
        $this->assertStringContainsString("err.push('วันหมดอายุ (ใช้รูปแบบ วัน/เดือน/ปี)');", $view);
        $this->assertStringNotContainsString('parseInt(p[2], 10) - 543', $view);
    }
}
