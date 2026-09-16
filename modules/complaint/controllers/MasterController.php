<?php

namespace app\modules\complaint\controllers;

use app\modules\complaint\models\ComplaintMaster;
use app\modules\complaint\services\ComplaintService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * ตั้งค่าตัวเลือกกลาง (Master_Data) — เฉพาะทีมศูนย์ฯ (admin/role complaint)
 */
class MasterController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['save' => ['POST'], 'delete' => ['POST'], 'toggle' => ['POST']],
            ],
        ]);
    }

    private function assertManager(): void
    {
        if (!ComplaintService::isManager()) {
            throw new ForbiddenHttpException('ตั้งค่าได้เฉพาะทีมศูนย์รับเรื่องร้องเรียน');
        }
    }

    public function actionIndex($group = 'channel')
    {
        $this->assertManager();
        if (!array_key_exists($group, ComplaintMaster::GROUPS)) {
            $group = 'channel';
        }
        $items = ComplaintMaster::find()
            ->where(['group' => $group])
            ->orderBy(['sort' => SORT_ASC, 'name' => SORT_ASC])
            ->all();

        return $this->render('index', [
            'group' => $group,
            'items' => $items,
        ]);
    }

    /** เพิ่ม/แก้ไขรายการ (id ว่าง = เพิ่มใหม่) */
    public function actionSave()
    {
        $this->assertManager();
        $req = Yii::$app->request;
        $id = (int) $req->post('id');
        $group = (string) $req->post('group');

        $model = $id ? ComplaintMaster::findOne($id) : new ComplaintMaster();
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบรายการ');
        }
        if (!$id) {
            $model->group = $group;
            $model->sort = (int) ComplaintMaster::find()->where(['group' => $group])->max('sort') + 1;
        }
        $model->name = trim((string) $req->post('name'));
        $model->code = trim((string) $req->post('code')) ?: null;
        if ($req->post('sort') !== null && $req->post('sort') !== '') {
            $model->sort = (int) $req->post('sort');
        }

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกแล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . implode(' ', $model->getFirstErrors()));
        }
        return $this->redirect(['index', 'group' => $model->group]);
    }

    public function actionToggle($id)
    {
        $this->assertManager();
        $model = ComplaintMaster::findOne((int) $id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบรายการ');
        }
        $model->is_active = $model->is_active ? 0 : 1;
        $model->save(false, ['is_active']);
        return $this->redirect(['index', 'group' => $model->group]);
    }

    public function actionDelete($id)
    {
        $this->assertManager();
        $model = ComplaintMaster::findOne((int) $id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบรายการ');
        }
        $group = $model->group;
        $model->delete();
        Yii::$app->session->setFlash('success', 'ลบรายการแล้ว');
        return $this->redirect(['index', 'group' => $group]);
    }
}
