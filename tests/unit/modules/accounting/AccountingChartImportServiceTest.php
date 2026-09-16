<?php

namespace tests\unit\modules\accounting;

use app\modules\accounting\services\AccountingChartImportService;
use Codeception\Test\Unit;

class AccountingChartImportServiceTest extends Unit
{
    /** @dataProvider validCodeProvider */
    public function testNormalizesAccountCodeWithoutLosingTrailingZero($input, string $expected): void
    {
        $this->assertSame($expected, AccountingChartImportService::normalizeCode($input));
    }

    public static function validCodeProvider(): array
    {
        return [
            'text' => ['4201020106.101', '4201020106.101'],
            'numeric trailing zero' => [5104010107.11, '5104010107.110'],
            'text trailing zero' => ['5104040102.100', '5104040102.100'],
        ];
    }

    /** @dataProvider invalidCodeProvider */
    public function testRejectsInvalidAccountCode($input): void
    {
        $this->assertNull(AccountingChartImportService::normalizeCode($input));
    }

    public static function invalidCodeProvider(): array
    {
        return [[''], ['ABC'], ['123.101'], ['12345678901.101'], ['4201020106.1000'], ['5104040102.1']];
    }
}
