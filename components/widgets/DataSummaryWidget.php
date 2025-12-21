<?php

// @app/components/widgets/DataSummaryWidget.php
namespace app\components\widgets;

use yii\base\Widget;
use yii\data\BaseDataProvider;
use yii\bootstrap5\LinkPager; // ใช้ LinkPager ของ Bootstrap 5

class DataSummaryWidget extends Widget
{
    /**
     * @var BaseDataProvider $dataProvider
     */
    public $dataProvider;
    
    /**
     * @var array $pagerOptions ตัวเลือกสำหรับ LinkPager
     */
    public $pagerOptions = [];

    /**
     * @var string $summaryTemplate รูปแบบของข้อความสรุป
     */
    public $summaryTemplate = 'แสดง <strong>{start}</strong> ถึง <strong>{end}</strong>  จาก <strong>{totalCount}</strong>  รายการ';

    public function run()
    {
        if ($this->dataProvider === null || $this->dataProvider->getTotalCount() === 0) {
            // ไม่ต้องแสดงผลหากไม่มีข้อมูล
            return '<span class="text-muted small">ไม่พบรายการข้อมูล</span>';
        }

        $totalCount = $this->dataProvider->getTotalCount();
        $count = $this->dataProvider->getCount();
        $pagination = $this->dataProvider->getPagination();
        
        // 1. คำนวณค่าเริ่มต้นและสิ้นสุด
        if ($pagination !== false) {
            $start = $pagination->getOffset() + 1;
            $end = $start + $count - 1;
        } else {
            $start = 1;
            $end = $totalCount;
        }
        $formattedTotalCount = number_format($totalCount); // จัดรูปแบบหลักพัน

        // 2. สร้างข้อความ Summary
        $summary = strtr($this->summaryTemplate, [
            '{start}' => $start,
            '{end}' => $end,
            '{totalCount}' => $formattedTotalCount,
        ]);

        // 3. สร้าง Pager
        $pager = '';
        if ($pagination !== false && $pagination->getPageCount() > 1) {
            $defaultPagerOptions = [
                'pagination' => $pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => [
                    'class' => 'pagination pagination-sm',
                ],
            ];
            // ผสานตัวเลือกที่ผู้ใช้กำหนด
            $options = array_merge($defaultPagerOptions, $this->pagerOptions);
            
            $pager = LinkPager::widget($options);
        }
        
        // 4. แสดงผลลัพธ์ทั้งหมดในรูปแบบ card-footer
        return $this->renderFooter($summary, $pager);
    }
    
    protected function renderFooter($summary, $pager)
    {
        return '<div class="d-flex justify-content-between align-items-center">'
            . '<span class="text-muted">' . $summary . '</span>'
            . $pager
            . '</div>';
    }
}