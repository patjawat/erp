<?php

namespace tests\unit\modules\housing;

use app\modules\housing\models\Building;
use app\modules\housing\models\Occupancy;
use app\modules\housing\models\Unit as HousingUnit;
use Codeception\Test\Unit;

final class OccupancyTest extends Unit
{
    public function testWholeUnitCannotBeAllocatedTwice(): void
    {
        $suffix = strtoupper(bin2hex(random_bytes(3)));
        $building = new Building([
            'code' => 'TEST-B-' . $suffix,
            'name' => 'อาคารทดสอบ',
            'building_type' => Building::TYPE_HOUSE,
        ]);
        $this->assertTrue($building->save(), json_encode($building->errors, JSON_UNESCAPED_UNICODE));
        $unit = new HousingUnit([
            'building_id' => $building->id,
            'code' => 'TEST-U-' . $suffix,
            'name' => 'ยูนิตทดสอบ',
            'occupancy_mode' => HousingUnit::MODE_FAMILY,
        ]);
        $this->assertTrue($unit->save(), json_encode($unit->errors, JSON_UNESCAPED_UNICODE));

        $first = new Occupancy([
            'emp_id' => 910001,
            'payer_emp_id' => 910001,
            'unit_id' => $unit->id,
            'occupancy_type' => HousingUnit::MODE_FAMILY,
        ]);
        $this->assertTrue($first->save(), json_encode($first->errors, JSON_UNESCAPED_UNICODE));

        try {
            $second = new Occupancy([
                'emp_id' => 910002,
                'payer_emp_id' => 910002,
                'unit_id' => $unit->id,
                'occupancy_type' => HousingUnit::MODE_FAMILY,
            ]);
            $this->assertFalse($second->validate());
            $this->assertNotEmpty($second->getErrors('unit_id'));
        } finally {
            $first->delete();
            $unit->delete();
            $building->delete();
        }
    }
}
