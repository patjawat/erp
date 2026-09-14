<?php

namespace app\modules\km\controllers;

use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\km\models\KmActivity;
use app\modules\km\models\KmActivityLink;
use app\modules\km\models\KmActivityPhoto;
use app\modules\km\models\KmCategory;
use app\modules\km\services\KmActivityService;
use app\modules\km\services\KmLinkService;
use app\modules\km\services\KmPhotoService;
use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * ทะเบียนกิจกรรม KM — CRUD + card/filter
 *
 * สิทธิ์: กิจกรรมเป็นของหน่วยงาน (owner_unit_id) ตาม KmActivityService
 */
class ActivityController extends Controller
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
                'actions' => [
                    'delete' => ['POST'],
                    'upload-photos' => ['POST'],
                    'delete-photo' => ['POST'],
                    'set-cover' => ['POST'],
                    'add-link' => ['POST'],
                    'delete-link' => ['POST'],
                ],
            ],
        ]);
    }

    private function fiscalYear(): int
    {
        $fy = (int) Yii::$app->request->get('fy');
        return $fy ?: (int) AppHelper::YearBudget();
    }

    /** หน้าทะเบียน: การ์ด + ตัวกรอง (ปีงบ/หมวด/หน่วยงาน/คำค้น/สถานะ) */
    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();
        $empUnitId = $me ? (int) $me->department : null;
        $userId = Yii::$app->user->id !== null ? (int) Yii::$app->user->id : null;

        $req = Yii::$app->request;
        $fiscalYear = $this->fiscalYear();
        $categoryId = (int) $req->get('category_id') ?: null;
        $unitId = (int) $req->get('unit_id') ?: null;
        $status = (string) $req->get('status');
        $q = trim((string) $req->get('q'));

        $query = KmActivity::find()
            ->with(['category', 'ownerUnit', 'coverPhoto'])
            ->where(['fiscal_year' => $fiscalYear]);

        // เห็นได้: เผยแพร่แล้วทุกคน + ฉบับร่างเฉพาะสายหน่วยตัวเอง/ผู้สร้าง (admin เห็นหมด)
        if (!KmActivityService::isAdmin()) {
            $scope = KmActivityService::unitScopeIds($empUnitId) ?: [-1];
            $query->andWhere([
                'or',
                ['status' => KmActivity::STATUS_PUBLISHED],
                ['owner_unit_id' => $scope],
                ['created_by' => $userId ?? -1],
            ]);
        }

        if ($categoryId) {
            $query->andWhere(['category_id' => $categoryId]);
        }
        if ($unitId) {
            $query->andWhere(['owner_unit_id' => $unitId]);
        }
        if ($status !== '' && array_key_exists($status, KmActivity::statusLabels())) {
            $query->andWhere(['status' => $status]);
        }
        if ($q !== '') {
            $query->andWhere(['or',
                ['like', 'title', $q],
                ['like', 'summary', $q],
                ['like', 'location', $q],
            ]);
        }

        $count = (int) $query->count();
        $pages = new Pagination(['totalCount' => $count, 'pageSize' => 24]);
        $activities = $query
            ->orderBy(['activity_date' => SORT_DESC, 'id' => SORT_DESC])
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return $this->render('index', [
            'activities' => $activities,
            'pages' => $pages,
            'count' => $count,
            'fiscalYear' => $fiscalYear,
            'years' => range($fiscalYear + 1, $fiscalYear - 3),
            'categories' => KmCategory::find()->where(['is_active' => 1])->orderBy(['sort' => SORT_ASC, 'name' => SORT_ASC])->all(),
            'units' => KmActivityService::orderedUnits(),
            'filters' => ['category_id' => $categoryId, 'unit_id' => $unitId, 'status' => $status, 'q' => $q],
        ]);
    }

    public function actionView($id)
    {
        $activity = KmActivity::find()->with(['category', 'ownerUnit', 'photos', 'links'])->where(['id' => (int) $id])->one();
        if (!$activity) {
            throw new NotFoundHttpException('ไม่พบกิจกรรมที่ต้องการ');
        }
        $me = UserHelper::GetEmployee();
        $userId = Yii::$app->user->id !== null ? (int) Yii::$app->user->id : null;

        if (!KmActivityService::canView($activity, $me ? (int) $me->id : null, $userId, $me ? (int) $me->department : null)) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ดูกิจกรรมนี้');
        }

        return $this->render('view', [
            'activity' => $activity,
            'canManage' => KmActivityService::canManage($activity, $me ? (int) $me->id : null, $userId),
        ]);
    }

    public function actionCreate()
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            throw new ForbiddenHttpException('ไม่พบข้อมูลพนักงานของบัญชีนี้');
        }

        $model = new KmActivity();
        $model->fiscal_year = $this->fiscalYear();
        $model->owner_unit_id = (int) $me->department ?: null;
        $model->status = KmActivity::STATUS_DRAFT;

        if ($this->saveFromPost($model, $me)) {
            // ไปหน้าแก้ไขต่อ เพื่อให้แนบรูป/ผูกหลักฐานได้ทันที (ส่วนนั้นต้องมี id แล้ว)
            Yii::$app->session->setFlash('success', 'บันทึกกิจกรรมเรียบร้อย — เพิ่มรูปภาพและผูกหลักฐานได้ด้านล่าง');
            return $this->redirect(['update', 'id' => $model->id]);
        }

        return $this->render('form', $this->formData($model, $me));
    }

    public function actionUpdate($id)
    {
        $model = $this->findActivity($id);
        $me = UserHelper::GetEmployee();
        $userId = Yii::$app->user->id !== null ? (int) Yii::$app->user->id : null;

        if (!KmActivityService::canManage($model, $me ? (int) $me->id : null, $userId)) {
            throw new ForbiddenHttpException('แก้ไขได้เฉพาะผู้สร้าง หัวหน้าหน่วยเจ้าของ หรือผู้ดูแลระบบ');
        }

        if ($this->saveFromPost($model, $me)) {
            Yii::$app->session->setFlash('success', 'บันทึกการแก้ไขเรียบร้อย');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('form', $this->formData($model, $me));
    }

    public function actionDelete($id)
    {
        $model = $this->findActivity($id);
        $me = UserHelper::GetEmployee();
        $userId = Yii::$app->user->id !== null ? (int) Yii::$app->user->id : null;

        if (!KmActivityService::canManage($model, $me ? (int) $me->id : null, $userId)) {
            throw new ForbiddenHttpException('ลบได้เฉพาะผู้สร้าง หัวหน้าหน่วยเจ้าของ หรือผู้ดูแลระบบ');
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'ลบกิจกรรมแล้ว');
        return $this->redirect(['index', 'fy' => $model->fiscal_year]);
    }

    /** อัปโหลดรูปหลายไฟล์เข้าคลังภาพของกิจกรรม */
    public function actionUploadPhotos($id)
    {
        $activity = $this->findActivity($id);
        $this->assertCanManage($activity);

        $files = UploadedFile::getInstancesByName('photos');
        if (!$files) {
            Yii::$app->session->setFlash('error', 'ยังไม่ได้เลือกไฟล์รูป');
            return $this->backToManage($activity);
        }

        $sort = (int) KmActivityPhoto::find()->where(['activity_id' => $activity->id])->max('sort');
        $ok = 0;
        $errors = [];
        foreach ($files as $file) {
            $result = KmPhotoService::store($activity, $file, ++$sort);
            if ($result instanceof KmActivityPhoto) {
                $ok++;
                // ตั้งรูปแรกเป็นรูปปกอัตโนมัติ ถ้ายังไม่มี
                if (!$activity->cover_photo_id) {
                    $activity->cover_photo_id = (int) $result->id;
                    $activity->save(false, ['cover_photo_id']);
                }
            } else {
                $errors[] = (string) $result;
            }
        }

        if ($ok) {
            Yii::$app->session->setFlash('success', "อัปโหลดรูปแล้ว $ok ไฟล์");
        }
        if ($errors) {
            Yii::$app->session->setFlash('error', implode(' / ', array_slice($errors, 0, 5)));
        }
        return $this->backToManage($activity);
    }

    /** ลบรูปหนึ่งภาพ */
    public function actionDeletePhoto($id)
    {
        $photo = $this->findPhoto($id);
        $activity = $photo->activity;
        $this->assertCanManage($activity);

        $wasCover = ((int) $activity->cover_photo_id === (int) $photo->id);
        KmPhotoService::delete($photo);

        // ถ้าลบรูปปก ให้เลื่อนไปใช้รูปที่เหลือรูปแรก (ถ้ามี)
        if ($wasCover) {
            $next = KmActivityPhoto::find()->where(['activity_id' => $activity->id])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->one();
            $activity->cover_photo_id = $next ? (int) $next->id : null;
            $activity->save(false, ['cover_photo_id']);
        }

        Yii::$app->session->setFlash('success', 'ลบรูปแล้ว');
        return $this->backToManage($activity);
    }

    /** ตั้งรูปนี้เป็นรูปปก */
    public function actionSetCover($id)
    {
        $photo = $this->findPhoto($id);
        $activity = $photo->activity;
        $this->assertCanManage($activity);

        $activity->cover_photo_id = (int) $photo->id;
        $activity->save(false, ['cover_photo_id']);
        Yii::$app->session->setFlash('success', 'ตั้งรูปปกแล้ว');
        return $this->backToManage($activity);
    }

    /** เสิร์ฟไฟล์รูป (นอก webroot) พร้อมตรวจสิทธิ์การดูกิจกรรม */
    public function actionPhoto($id, $thumb = 0)
    {
        $photo = $this->findPhoto($id);
        $activity = $photo->activity;

        $me = UserHelper::GetEmployee();
        $userId = Yii::$app->user->id !== null ? (int) Yii::$app->user->id : null;
        if (!KmActivityService::canView($activity, $me ? (int) $me->id : null, $userId, $me ? (int) $me->department : null)) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ดูรูปนี้');
        }

        $rel = ((int) $thumb === 1 && $photo->thumbnail_path) ? $photo->thumbnail_path : $photo->file_path;
        $abs = KmPhotoService::absolutePath($rel);
        if (!is_file($abs)) {
            throw new NotFoundHttpException('ไม่พบไฟล์รูป');
        }

        return Yii::$app->response->sendFile($abs, $photo->file_name ?: basename($abs), [
            'inline' => true,
            'mimeType' => $photo->mime ?: null,
        ]);
    }

    /** ตัวเลือกรายการปลายทางสำหรับผูกหลักฐาน (AJAX JSON) */
    public function actionLinkOptions($type, $q = '')
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!KmLinkService::isEnabled((string) $type)) {
            return ['items' => []];
        }
        return ['items' => KmLinkService::search((string) $type, (string) $q)];
    }

    /** ผูกหลักฐาน 1 รายการเข้ากับกิจกรรม */
    public function actionAddLink($id)
    {
        $activity = $this->findActivity($id);
        $this->assertCanManage($activity);

        $type = (string) Yii::$app->request->post('item_type');
        $refId = trim((string) Yii::$app->request->post('ref_id'));
        $note = trim((string) Yii::$app->request->post('note'));

        if (!KmLinkService::isEnabled($type) || $refId === '') {
            Yii::$app->session->setFlash('error', 'กรุณาเลือกประเภทและรายการที่จะผูก');
            return $this->backToManage($activity);
        }

        // ยืนยันว่ารายการปลายทางมีจริง + ดึงป้ายชื่อฝั่ง server (ไม่เชื่อค่าจากหน้าเว็บ)
        $label = KmLinkService::resolveLabel($type, $refId);
        if ($label === null) {
            Yii::$app->session->setFlash('error', 'ไม่พบรายการที่เลือก');
            return $this->backToManage($activity);
        }

        // กันผูกซ้ำ
        $exists = KmActivityLink::find()->where([
            'activity_id' => $activity->id, 'item_type' => $type, 'ref_id' => $refId,
        ])->exists();
        if ($exists) {
            Yii::$app->session->setFlash('error', 'ผูกรายการนี้ไว้แล้ว');
            return $this->backToManage($activity);
        }

        $link = new KmActivityLink([
            'activity_id' => $activity->id,
            'item_type' => $type,
            'ref_id' => $refId,
            'ref_label' => mb_substr($label, 0, 500),
            'note' => $note !== '' ? mb_substr($note, 0, 255) : null,
        ]);
        if ($link->save()) {
            Yii::$app->session->setFlash('success', 'ผูกหลักฐานแล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'ผูกไม่สำเร็จ: ' . implode(' ', $link->getFirstErrors()));
        }
        return $this->backToManage($activity);
    }

    /** ยกเลิกการผูกหลักฐาน */
    public function actionDeleteLink($id)
    {
        $link = KmActivityLink::findOne((int) $id);
        if (!$link) {
            throw new NotFoundHttpException('ไม่พบรายการที่ผูก');
        }
        $activity = $link->activity;
        $this->assertCanManage($activity);

        $link->delete();
        Yii::$app->session->setFlash('success', 'ยกเลิกการผูกแล้ว');
        return $this->backToManage($activity);
    }

    /** รับค่าจากฟอร์ม + แปลงวันที่ พ.ศ. → ค.ศ. ก่อน validate/บันทึก */
    private function saveFromPost(KmActivity $model, $me): bool
    {
        $req = Yii::$app->request;
        if (!$req->isPost) {
            return false;
        }

        $post = $req->post('KmActivity', []);
        $model->setAttributes($post);
        // ช่องวันที่กรอกเป็น วว/ดด/พ.ศ. — แปลงกลับก่อนบันทึกเสมอ
        $model->activity_date = AppHelper::normalizeDateToDb($req->post('activity_date_thai'));
        // เวลาว่างต้องเป็น null ไม่ใช่ '' (กัน MySQL strict mode ปฏิเสธค่าเวลาว่าง)
        $model->start_time = $model->start_time ?: null;
        $model->end_time = $model->end_time ?: null;
        // ช่องเนื้อหาแบบ Word — กรอง HTML ก่อนบันทึก (กัน XSS + จำกัดแท็ก)
        $model->summary = \app\modules\km\components\RichText::sanitize($model->summary) ?: null;
        $model->objective = \app\modules\km\components\RichText::sanitize($model->objective) ?: null;
        $model->detail = \app\modules\km\components\RichText::sanitize($model->detail) ?: null;

        // กันเลือกหน่วยงานนอกสิทธิ์ (ยกเว้น admin) — ยึดค่าที่ selectableUnits อนุญาต
        $allowed = array_map(static fn ($o) => (int) $o['id'], KmActivityService::selectableUnits($me ? (int) $me->department : null));
        if ($model->owner_unit_id && !in_array((int) $model->owner_unit_id, $allowed, true) && !KmActivityService::isAdmin()) {
            $model->owner_unit_id = $me ? (int) $me->department : null;
        }

        return $model->save();
    }

    private function formData(KmActivity $model, $me): array
    {
        return [
            'model' => $model,
            'categories' => KmCategory::find()->where(['is_active' => 1])->orderBy(['sort' => SORT_ASC, 'name' => SORT_ASC])->all(),
            'units' => KmActivityService::selectableUnits($me ? (int) $me->department : null),
            'years' => range((int) AppHelper::YearBudget() + 1, (int) AppHelper::YearBudget() - 3),
        ];
    }

    private function findActivity($id): KmActivity
    {
        $model = KmActivity::findOne((int) $id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบกิจกรรมที่ต้องการ');
        }
        return $model;
    }

    private function findPhoto($id): KmActivityPhoto
    {
        $photo = KmActivityPhoto::findOne((int) $id);
        if (!$photo) {
            throw new NotFoundHttpException('ไม่พบรูปที่ต้องการ');
        }
        return $photo;
    }

    /** กลับไปหน้าที่กำลังจัดการอยู่ (ฟอร์มแก้ไข หรือหน้าดูรายละเอียด) หลังจัดการรูป/ลิงก์ */
    private function backToManage(KmActivity $activity)
    {
        $ref = Yii::$app->request->referrer;
        return $ref ? $this->redirect($ref) : $this->redirect(['view', 'id' => $activity->id]);
    }

    private function assertCanManage(KmActivity $activity): void
    {
        $me = UserHelper::GetEmployee();
        $userId = Yii::$app->user->id !== null ? (int) Yii::$app->user->id : null;
        if (!KmActivityService::canManage($activity, $me ? (int) $me->id : null, $userId)) {
            throw new ForbiddenHttpException('จัดการรูปได้เฉพาะผู้สร้าง หัวหน้าหน่วยเจ้าของ หรือผู้ดูแลระบบ');
        }
    }
}
