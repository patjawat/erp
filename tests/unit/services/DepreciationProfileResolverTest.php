<?php

namespace tests\unit\services;

use Codeception\Test\Unit;
use app\modules\am\services\DepreciationProfileResolver as R;

/**
 * ทดสอบการเลือกเกณฑ์ตามลำดับความเฉพาะเจาะจง (cases 1-4)
 * ใช้ pure method pickMostSpecific() จึงไม่ต้องพึ่ง DB
 */
class DepreciationProfileResolverTest extends Unit
{
    /** Case 1: มีเฉพาะประเภทหลัก → ใช้ประเภทหลัก */
    public function testAssetUsesTypeLevel()
    {
        $picked = R::pickMostSpecific([
            R::SOURCE_ASSET => null,
            R::SOURCE_ITEM => null,
            R::SOURCE_CATEGORY => null,
            R::SOURCE_TYPE => 11,
        ]);
        $this->assertSame(R::SOURCE_TYPE, $picked['source_type']);
        $this->assertSame(11, $picked['profile_id']);
    }

    /** Case 2: หมวด override ประเภทหลัก */
    public function testCategoryOverridesType()
    {
        $picked = R::pickMostSpecific([
            R::SOURCE_ASSET => null,
            R::SOURCE_ITEM => null,
            R::SOURCE_CATEGORY => 22,
            R::SOURCE_TYPE => 11,
        ]);
        $this->assertSame(R::SOURCE_CATEGORY, $picked['source_type']);
        $this->assertSame(22, $picked['profile_id']);
    }

    /** Case 3: รายการ override หมวด */
    public function testItemOverridesCategory()
    {
        $picked = R::pickMostSpecific([
            R::SOURCE_ASSET => null,
            R::SOURCE_ITEM => 33,
            R::SOURCE_CATEGORY => 22,
            R::SOURCE_TYPE => 11,
        ]);
        $this->assertSame(R::SOURCE_ITEM, $picked['source_type']);
        $this->assertSame(33, $picked['profile_id']);
    }

    /** Case 4: ทรัพย์สินรายชิ้น override รายการ */
    public function testAssetOverridesItem()
    {
        $picked = R::pickMostSpecific([
            R::SOURCE_ASSET => 44,
            R::SOURCE_ITEM => 33,
            R::SOURCE_CATEGORY => 22,
            R::SOURCE_TYPE => 11,
        ]);
        $this->assertSame(R::SOURCE_ASSET, $picked['source_type']);
        $this->assertSame(44, $picked['profile_id']);
    }

    /** ไม่มีเกณฑ์ระดับใดเลย → null */
    public function testNoProfileReturnsNull()
    {
        $this->assertNull(R::pickMostSpecific([
            R::SOURCE_ASSET => null,
            R::SOURCE_ITEM => null,
            R::SOURCE_CATEGORY => 0,
            R::SOURCE_TYPE => null,
        ]));
    }
}
