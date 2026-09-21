<?php

namespace tests\unit\modules\accounting;

use app\modules\accounting\services\PurchaseVendorReconciliationService as Service;
use Codeception\Test\Unit;

class PurchaseVendorReconciliationServiceTest extends Unit
{
    public function testExactActiveCodeMatches(): void
    {
        $vendor = (object) ['code' => 'V329', 'title' => 'ผู้ขายตัวอย่าง', 'active' => 1];
        $result = Service::classify(' v329 ', ['V329' => [$vendor]], []);
        $this->assertSame(Service::MATCHED, $result['status']);
        $this->assertSame([$vendor], $result['candidates']);
    }

    public function testDuplicateCodeRequiresReview(): void
    {
        $vendor = (object) ['active' => 1];
        $this->assertSame(Service::REVIEW, Service::classify('V329', ['V329' => [$vendor, $vendor]], [])['status']);
    }

    public function testTaxIdIsOnlySuggestion(): void
    {
        $vendor = (object) ['active' => 1];
        $this->assertSame(Service::REVIEW, Service::classify('1234567890123', [], ['1234567890123' => [$vendor]])['status']);
    }

    public function testMissingAndInactiveCodes(): void
    {
        $this->assertSame(Service::MISSING, Service::classify('', [], [])['status']);
        $this->assertSame(Service::MISSING, Service::classify('OLD-1', [], [])['status']);
        $this->assertSame(Service::REVIEW, Service::classify('V329', ['V329' => [(object) ['active' => 0]]], [])['status']);
    }
}
