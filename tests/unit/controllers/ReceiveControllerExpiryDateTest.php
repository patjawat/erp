<?php

namespace tests\unit\controllers;

use app\modules\inventoryV2\controllers\ReceiveController;
use Codeception\Test\Unit;
use Yii;

require_once dirname(__DIR__, 3) . '/modules/inventoryV2/controllers/ReceiveController.php';

class ReceiveControllerExpiryDateTest extends Unit
{
    public function testNormalizesGregorianBuddhistAndIsoDates(): void
    {
        $details = $this->controller()->normalizeExpiryDates([
            ['expiry_date' => '16/08/2028'],
            ['expiry_date' => '16/08/2571'],
            ['expiry_date' => '2028-08-16'],
        ]);

        $this->assertSame('2028-08-16', $details[0]['expiry_date']);
        $this->assertSame('2028-08-16', $details[1]['expiry_date']);
        $this->assertSame('2028-08-16', $details[2]['expiry_date']);
    }

    public function testKeepsEmptyExpiryDateAsNull(): void
    {
        $details = $this->controller()->normalizeExpiryDates([
            ['expiry_date' => ''],
            [],
        ]);

        $this->assertNull($details[0]['expiry_date']);
        $this->assertNull($details[1]['expiry_date']);
    }

    public function testRejectsInvalidCalendarDateWithRowNumber(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('รายการที่ 2: วันหมดอายุไม่ถูกต้อง');

        $this->controller()->normalizeExpiryDates([
            ['expiry_date' => '2028-08-16'],
            ['expiry_date' => '31/02/2571'],
        ]);
    }

    private function controller(): ExpiryDateTestController
    {
        return new ExpiryDateTestController('receive', Yii::$app);
    }
}

class ExpiryDateTestController extends ReceiveController
{
    public function normalizeExpiryDates(array $details): array
    {
        return $this->normalizeDetailExpiryDates($details);
    }
}
