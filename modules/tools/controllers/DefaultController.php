<?php

namespace app\modules\tools\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * หน้า hub เครื่องมือ — เปิดให้ผู้ล็อกอินทุกคน (roles => ['@'])
 * เครื่องมือแต่ละตัวมี guard สิทธิ์ของตัวเองอยู่แล้ว
 */
class DefaultController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
        ]);
    }

    public function actionIndex()
    {
        // ทะเบียนเครื่องมือในโซนนี้ — เพิ่มเครื่องมือใหม่แค่ต่อการ์ดที่นี่
        $tools = [
            [
                'title' => 'ผังกระบวนการ',
                'desc' => 'ป้อนขั้นตอนการทำงาน แล้วสร้างผัง (Flowchart) และตารางเอกสารให้อัตโนมัติ',
                'icon' => 'bi-diagram-3',
                'tone' => 'primary',
                'url' => ['/flowchart/default/index'],
            ],
            [
                'title' => 'SWOT & SOAR',
                'desc' => 'เครื่องมือวิเคราะห์เชิงกลยุทธ์ — จุดแข็ง จุดอ่อน โอกาส อุปสรรค พร้อมสังเคราะห์กลยุทธ์',
                'icon' => 'bi-grid-1x2',
                'tone' => 'success',
                'url' => ['/swot/default/index'],
            ],
        ];

        return $this->render('index', ['tools' => $tools]);
    }
}
