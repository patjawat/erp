<?php

namespace app\modules\purchase\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use app\modules\purchase\models\DocTemplate;
use app\modules\purchase\components\DocMergeEngine;

/**
 * จัดการแม่แบบเอกสาร
 *
 * เหตุผลที่หน้านี้มีอยู่: ข้อความในหนังสือราชการเปลี่ยนตามระเบียบและหนังสือเวียน
 * ซึ่งเปลี่ยนบ่อยกว่ารอบการ deploy ระบบ ถ้าแม่แบบเป็นไฟล์ .docx หรือเป็นข้อความ
 * ในโค้ด (อย่างที่ระบบพิมพ์เดิมเป็น) งานพัสดุต้องรอโปรแกรมเมอร์ทุกครั้งที่มีหนังสือ
 * เวียนใหม่ หน้านี้ตัดการรอนั้นออก
 *
 * สิทธิ์: role 'purchase' เท่านั้น
 */
class DocTemplateController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['purchase'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'toggle' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionIndex()
    {
        return $this->render('index', [
            'models' => DocTemplate::find()
                ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
                ->all(),
        ]);
    }

    public function actionCreate()
    {
        $model = new DocTemplate([
            'category' => 'buy',
            'ref_type' => DocTemplate::REF_ORDER,
            'orientation' => 'portrait',
            'emblem' => DocTemplate::EMBLEM_SMALL,
            'font_size' => 14,
            'active' => 1,
        ]);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกแม่แบบ "' . $model->name . '" เรียบร้อย');

            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
            'catalog' => DocMergeEngine::fieldCatalog($model->ref_type),
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash(
                'success',
                'บันทึกแม่แบบ "' . $model->name . '" เรียบร้อย'
                . ($model->docCount() > 0
                    ? ' — เอกสารที่ออกไปแล้วยังใช้ข้อความเดิม การแก้นี้มีผลกับเอกสารที่สร้างใหม่เท่านั้น'
                    : '')
            );

            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
            'catalog' => DocMergeEngine::fieldCatalog($model->ref_type),
        ]);
    }

    public function actionToggle($id)
    {
        $model = $this->findModel($id);
        $model->updateAttributes(['active' => $model->active ? 0 : 1]);

        Yii::$app->session->setFlash(
            'success',
            ($model->active ? 'เปิดใช้' : 'ปิดใช้') . 'แม่แบบ "' . $model->name . '" เรียบร้อย'
        );

        return $this->redirect(['index']);
    }

    /**
     * ลบแม่แบบ
     *
     * ลบจริงได้เพราะเอกสารที่ออกไปแล้วเก็บ body_html ของตัวเองไว้ครบ ไม่ได้อ่านจาก
     * แม่แบบตอนพิมพ์ การลบแม่แบบจึงไม่ทำให้เอกสารเก่าพิมพ์ไม่ได้ แต่ยังเตือนจำนวน
     * เอกสารที่เคยออกจากแม่แบบนี้ไว้ เพราะการลบทำให้ปุ่ม "รีเซ็ต" ของเอกสารเหล่านั้น
     * ใช้ไม่ได้อีก และเสนอทางปิดใช้ให้เป็นทางเลือกแทน
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $used = $model->docCount();

        $name = $model->name;
        $model->delete();

        Yii::$app->session->setFlash(
            'success',
            'ลบแม่แบบ "' . $name . '" เรียบร้อย'
            . ($used > 0
                ? ' — เอกสาร ' . $used . ' ฉบับที่ออกจากแม่แบบนี้ยังพิมพ์ได้ปกติ แต่ปุ่มรีเซ็ตของเอกสารเหล่านั้นจะใช้ไม่ได้อีก'
                : '')
        );

        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        $model = DocTemplate::findOne(['id' => $id]);
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบแม่แบบที่ต้องการ');
        }

        return $model;
    }
}
