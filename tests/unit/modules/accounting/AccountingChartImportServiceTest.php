<?php

namespace tests\unit\modules\accounting;

use app\modules\accounting\services\AccountingChartImportService;
use app\modules\accounting\services\AccountingJournalDraftService;
use app\modules\accounting\services\AccountingPeriodReviewService;
use app\modules\accounting\services\AccountingPeriodCloseService;
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

    public function testHospitalChartAllowsTwoDigitSubcode(): void
    {
        $this->assertSame('2101020199.137.01', AccountingChartImportService::normalizeCode('2101020199.137.01', true));
        $this->assertNull(AccountingChartImportService::normalizeCode('2101020199.137.01', false));
    }

    public function testHospitalSubcodeMapsToStandardParentCandidate(): void
    {
        $this->assertSame('2101020199.137', AccountingChartImportService::standardCandidateCode('2101020199.137.01'));
        $this->assertNull(AccountingChartImportService::standardCandidateCode('2101020199.137'));
    }

    public function testJournalDraftBalanceValidation(): void
    {
        AccountingJournalDraftService::assertBalanced([
            ['debit_amount' => 93, 'credit_amount' => 0],
            ['debit_amount' => 7, 'credit_amount' => 0],
            ['debit_amount' => 0, 'credit_amount' => 100],
        ]);
        $this->addToAssertionCount(1);
    }

    public function testJournalDraftRejectsUnbalancedLines(): void
    {
        $this->expectException(\DomainException::class);
        AccountingJournalDraftService::assertBalanced([
            ['debit_amount' => 99, 'credit_amount' => 0],
            ['debit_amount' => 0, 'credit_amount' => 100],
        ]);
    }

    public function testPeriodReviewIsReadyOnlyWithoutBlockingItems(): void
    {
        $result = AccountingPeriodReviewService::evaluateReadiness(['journal_count'=>2,'debit'=>100,'credit'=>100,'draft_count'=>0,'misplaced_count'=>0]);
        $this->assertTrue($result['ready']);
        $this->assertSame([], $result['issues']);
    }

    public function testPeriodReviewReportsEveryBlockingCondition(): void
    {
        $result = AccountingPeriodReviewService::evaluateReadiness(['journal_count'=>0,'debit'=>100,'credit'=>99,'draft_count'=>2,'misplaced_count'=>1]);
        $this->assertFalse($result['ready']);
        $this->assertCount(4, $result['issues']);
    }

    public function testPeriodCloseSnapshotHashDoesNotDependOnBalanceOrder(): void
    {
        $summary=['journal_count'=>2,'debit'=>100,'credit'=>100];
        $a=[['account_code_snapshot'=>'2','debit'=>0,'credit'=>100],['account_code_snapshot'=>'1','debit'=>100,'credit'=>0]];
        $this->assertSame(AccountingPeriodCloseService::snapshotHash($summary,$a),AccountingPeriodCloseService::snapshotHash($summary,array_reverse($a)));
    }
}
