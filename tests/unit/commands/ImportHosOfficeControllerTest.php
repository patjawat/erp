<?php

namespace tests\unit\commands;

use app\commands\ImportHosOfficeController;
use app\modules\hr\models\EmployeePosition;
use Codeception\Test\Unit;
use ReflectionMethod;
use Yii;

require_once dirname(__DIR__, 3) . '/modules/hr/models/EmployeePosition.php';

class ImportHosOfficeControllerTest extends Unit
{
    public function testResolvesLegacyPositionVariantsWithoutCreatingGarbageMasterRows(): void
    {
        $dental = $this->position('เจ้าพนักงานทันตสาธารณสุข');
        $pharmacist = $this->position('เภสัชกร');
        $positions = [
            'เจ้าพนักงานทันตสาธารณสุข' => $dental,
            'เภสัชกร' => $pharmacist,
        ];
        $keys = array_keys($positions);

        $categorySuffix = $this->invoke('resolveHosOfficeHistoryPosition', [
            'เจ้าพนักงานทันตสาธารณสุข (ทั่วไป)',
            $positions,
            $keys,
        ]);
        $numericLevel = $this->invoke('resolveHosOfficeHistoryPosition', [
            'เจ้าพนักงานทันตสาธารณสุข 3',
            $positions,
            $keys,
        ]);
        $knownTypo = $this->invoke('resolveHosOfficeHistoryPosition', [
            'เภสัขกร',
            $positions,
            $keys,
        ]);

        $this->assertSame($dental, $categorySuffix['position']);
        $this->assertSame('normalized', $categorySuffix['match_type']);
        $this->assertSame($dental, $numericLevel['position']);
        $this->assertSame('normalized', $numericLevel['match_type']);
        $this->assertSame($pharmacist, $knownTypo['position']);
        $this->assertSame('normalized', $knownTypo['match_type']);
    }

    public function testExtractsPositionFromAppointmentNarrativeButNotSalaryMovement(): void
    {
        $technicalNurse = $this->position('พยาบาลเทคนิค');
        $positions = ['พยาบาลเทคนิค' => $technicalNurse];

        $appointment = $this->invoke('resolveHosOfficeHistoryPosition', [
            'บรรจุผู้ได้รับคัดเลือก (ม.50) พยาบาลเทคนิค 2 รพช.วิเชียรบุรี',
            $positions,
            array_keys($positions),
        ]);
        $salaryMovement = $this->invoke('resolveHosOfficeHistoryPosition', [
            'เลื่อนขั้นเงินเดือนประจำปี (1 ขั้น)',
            $positions,
            array_keys($positions),
        ]);

        $this->assertSame($technicalNurse, $appointment['position']);
        $this->assertSame('contained', $appointment['match_type']);
        $this->assertNull($salaryMovement['position']);
        $this->assertSame('unmatched', $salaryMovement['match_type']);
    }

    public function testCurrentPositionDateUsesDatePutOnlyWhenItIsNotBeforeEmployment(): void
    {
        $validDatePut = $this->invoke('currentPositionDate', [[
            'start_work_date' => '2010-04-01',
            'date_put' => '2015-11-16',
        ]]);
        $invalidDatePut = $this->invoke('currentPositionDate', [[
            'start_work_date' => '2025-01-03',
            'date_put' => '1995-06-01',
        ]]);

        $this->assertSame(['2015-11-16', 'HR_DATE_PUT'], $validDatePut);
        $this->assertSame(['2025-01-03', 'HR_STARTWORK_DATE'], $invalidDatePut);
    }

    public function testEmployeeTypeDefinitionsIncludeContractWorkers(): void
    {
        $definitions = $this->invoke('hosOfficeEmployeeTypeDefinitions');

        $this->assertSame('PT7', $definitions['จ้างเหมาบริการ']['code']);
        $this->assertSame('จ้างเหมาบริการ', $definitions['จ้างเหมาบริการ']['title']);
    }

    private function position(string $title): EmployeePosition
    {
        $position = new EmployeePositionStub();
        $position->title = $title;

        return $position;
    }

    private function invoke(string $methodName, array $arguments = [])
    {
        $method = new ReflectionMethod(ImportHosOfficeController::class, $methodName);
        $method->setAccessible(true);

        return $method->invokeArgs(
            new ImportHosOfficeController('import-hos-office', Yii::$app),
            $arguments
        );
    }
}

class EmployeePositionStub extends EmployeePosition
{
    public function attributes(): array
    {
        return [
            'id',
            'employee_type_id',
            'employee_position_group_id',
            'legacy_code',
            'title',
            'sort',
            'active',
            'data_json',
        ];
    }
}
