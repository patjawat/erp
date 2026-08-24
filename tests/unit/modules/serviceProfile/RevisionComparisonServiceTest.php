<?php

namespace tests\unit\modules\serviceProfile;

use app\modules\serviceProfile\services\RevisionComparisonService;
use Codeception\Test\Unit;

class RevisionComparisonServiceTest extends Unit
{
    public function testDetectsAddedChangedRemovedAndUnchangedSections(): void
    {
        $previous=$this->profile([
            $this->section('same','หัวข้อเดิม','ข้อความเดิม'),
            $this->section('changed','หัวข้อแก้ไข','ก่อนแก้'),
            $this->section('removed','หัวข้อที่นำออก','ข้อมูล'),
        ]);
        $current=$this->profile([
            $this->section('same','หัวข้อเดิม','ข้อความเดิม'),
            $this->section('changed','หัวข้อแก้ไข','หลังแก้'),
            $this->section('added','หัวข้อใหม่','ข้อมูลใหม่'),
        ]);
        $result=(new RevisionComparisonService())->compareSections($current,$previous);
        $this->assertSame(['added'=>1,'changed'=>1,'removed'=>1,'unchanged'=>1],$result['summary']);
        $statuses=[];foreach($result['rows'] as $row)$statuses[$row['code']]=$row['status'];
        $this->assertSame('unchanged',$statuses['same']);
        $this->assertSame('changed',$statuses['changed']);
        $this->assertSame('removed',$statuses['removed']);
        $this->assertSame('added',$statuses['added']);
    }

    public function testStructuredDataChangeIsDetected(): void
    {
        $old=$this->section('kpi','ตัวชี้วัด','');$old->setData(['items'=>[['indicator'=>'A','target'=>'80']]]);
        $new=$this->section('kpi','ตัวชี้วัด','');$new->setData(['items'=>[['indicator'=>'A','target'=>'90']]]);
        $result=(new RevisionComparisonService())->compareSections([$new],[$old]);
        $this->assertSame(1,$result['summary']['changed']);
    }

    private function profile(array $sections): array
    {
        return $sections;
    }

    private function section(string $code,string $title,string $content): object
    {
        return new class($code,$title,$content) {
            public string $block_type='rich_text';public bool $is_required=true;public int $sort_order=10;private array $data=[];
            public function __construct(public string $section_code,public string $title,public string $content){}
            public function setData(array $data):void{$this->data=$data;}
            public function getData():array{return $this->data;}
        };
    }
}
