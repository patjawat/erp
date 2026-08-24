<?php

namespace tests\unit\modules\serviceProfile;

use app\modules\serviceProfile\forms\AiTemplateForm;
use Codeception\Test\Unit;

class AiTemplateFormTest extends Unit
{
    public function testValidInputPasses(): void
    {
        $form=new AiTemplateForm(['owner_id'=>1,'name'=>'Service Profile OPD','mission'=>'ให้บริการผู้ป่วยนอก','section_count'=>12,'effective_fiscal_year'=>2569]);
        $this->assertTrue($form->validate(),json_encode($form->errors,JSON_UNESCAPED_UNICODE));
    }

    /** @dataProvider invalidRanges */
    public function testRejectsUnsafeRanges(int $count,int $year): void
    {
        $form=new AiTemplateForm(['owner_id'=>1,'name'=>'Template','mission'=>'ภารกิจ','section_count'=>$count,'effective_fiscal_year'=>$year]);
        $this->assertFalse($form->validate());
    }

    public function invalidRanges(): array
    {
        return [[5,2569],[21,2569],[12,2499],[12,2701]];
    }

    public function testMissionIsRequired(): void
    {
        $form=new AiTemplateForm(['owner_id'=>1,'name'=>'Template','section_count'=>12,'effective_fiscal_year'=>2569]);
        $this->assertFalse($form->validate());$this->assertArrayHasKey('mission',$form->errors);
    }
}
