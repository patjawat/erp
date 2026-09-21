<?php

namespace app\modules\ha12\controllers;

use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\ha12\models\Ha12Activity;
use app\modules\ha12\models\Ha12Review;
use app\modules\ha12\models\Ha12ReviewFollowup;
use app\modules\ha12\models\Ha12ReviewForm;
use app\modules\ha12\models\Ha12ReviewVersion;
use app\modules\ha12\services\Ha12ReviewService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * การทบทวน HA12 (เฟส 1) — กิจกรรมทั่วไป 9 กิจกรรม
 *
 * CRUD ผ่าน AJAX modal (มาตรฐาน .open-modal) + ประวัติรุ่น + ผลติดตาม + ลบ/กู้คืน
 * เจ้าของ = หน่วยงาน (owner_unit_id) ตาม Ha12ReviewService
 */
class ReviewController extends Controller
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
                    'restore' => ['POST'],
                    'add-followup' => ['POST'],
                    'delete-followup' => ['POST'],
                ],
            ],
        ]);
    }

    private function fiscalYear(): int
    {
        $fy = (int) Yii::$app->request->get('fy');
        return $fy ?: (int) AppHelper::YearBudget();
    }

    /** กิจกรรมทั่วไปที่รองรับในเฟส 1 (form_type=general) */
    private function generalActivities(): array
    {
        return array_values(array_filter(
            Ha12Activity::activeList(),
            static fn (Ha12Activity $a): bool => Ha12ReviewForm::isSupported((int) $a->no)
        ));
    }

    private function findActivity(int $id): Ha12Activity
    {
        $a = Ha12Activity::findOne($id);
        if (!$a) {
            throw new NotFoundHttpException('ไม่พบกิจกรรม');
        }
        return $a;
    }

    private function findReview(int $id): Ha12Review
    {
        $r = Ha12Review::findOne($id);
        if (!$r) {
            throw new NotFoundHttpException('ไม่พบรายการทบทวน');
        }
        return $r;
    }

    /** หน้ารายการ: เลือกกิจกรรม + กรอง (ปีงบ/หน่วยงาน/คำค้น/รวมที่ลบ) */
    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();
        $empUnitId = $me ? (int) $me->department : null;
        $userId = Yii::$app->user->id !== null ? (int) Yii::$app->user->id : null;

        $req = Yii::$app->request;
        $activities = $this->generalActivities();
        $activityId = (int) $req->get('activity_id');
        if (!$activityId && $activities) {
            $activityId = (int) $activities[0]->id;
        }
        $fiscalYear = $this->fiscalYear();
        $unitId = (int) $req->get('unit_id') ?: null;
        $showDeleted = (int) $req->get('deleted') === 1;
        $q = trim((string) $req->get('q'));

        $query = Ha12Review::find()
            ->with(['ownerUnit'])
            ->where(['activity_id' => $activityId, 'fiscal_year' => $fiscalYear]);
        $query->andWhere(['deleted' => $showDeleted ? 1 : 0]);

        // ขอบเขต: ผู้ดูแลเห็นหมด / ผู้ใช้ทั่วไปเห็นเฉพาะสายหน่วยตน + ที่ตนสร้าง
        if (!Ha12ReviewService::isManager()) {
            $scope = Ha12ReviewService::unitScopeIds($empUnitId) ?: [-1];
            $query->andWhere([
                'or',
                ['owner_unit_id' => $scope],
                ['created_by' => $userId ?? -1],
            ]);
        }
        if ($unitId) {
            $query->andWhere(['owner_unit_id' => $unitId]);
        }
        if ($q !== '') {
            $query->andWhere(['or',
                ['like', 'title', $q],
                ['like', 'payload_json', $q],
                ['like', 'reviewer_name', $q],
            ]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query->orderBy(['review_date' => SORT_DESC, 'id' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
            'sort' => false,
        ]);
        /** @var Ha12Review[] $reviews */
        $reviews = $dataProvider->getModels();

        // นับผลติดตามแบบ batch (กัน N+1)
        $followupCounts = [];
        if ($reviews) {
            $ids = array_map(static fn ($r) => (int) $r->id, $reviews);
            $rows = (new \yii\db\Query())
                ->select(['review_id', 'c' => 'COUNT(*)'])
                ->from(Ha12ReviewFollowup::tableName())
                ->where(['review_id' => $ids])
                ->groupBy('review_id')
                ->all();
            foreach ($rows as $row) {
                $followupCounts[(int) $row['review_id']] = (int) $row['c'];
            }
        }

        return $this->render('index', [
            'activities' => $activities,
            'activityId' => $activityId,
            'reviews' => $reviews,
            'dataProvider' => $dataProvider,
            'fiscalYear' => $fiscalYear,
            'years' => range($fiscalYear + 1, $fiscalYear - 3),
            'units' => Ha12ReviewService::orderedUnits(),
            'followupCounts' => $followupCounts,
            'filters' => ['unit_id' => $unitId, 'q' => $q, 'deleted' => $showDeleted ? 1 : 0],
            'canManage' => true, // ปุ่มเพิ่มเปิดให้ทุกคนในหน่วยตน; guard จริงตอนบันทึก
        ]);
    }

    /** สร้างรายการทบทวน (AJAX modal) */
    public function actionCreate()
    {
        $req = Yii::$app->request;
        $me = UserHelper::GetEmployee();
        if (!$me) {
            throw new ForbiddenHttpException('ไม่พบข้อมูลพนักงานของบัญชีนี้');
        }
        $activity = $this->findActivity((int) $req->get('activity_id'));
        if (!Ha12ReviewForm::isSupported((int) $activity->no)) {
            throw new ForbiddenHttpException('กิจกรรมนี้เป็นแบบเฉพาะ (จะเปิดในเฟส 2)');
        }

        $model = new Ha12Review();
        $model->activity_id = (int) $activity->id;
        $model->fiscal_year = $this->fiscalYear();
        $model->owner_unit_id = (int) $me->department ?: null;
        $model->review_date = date('Y-m-d');

        if ($this->saveFromPost($model, $me, $activity, true)) {
            return $this->jsonSaved();
        }
        return $this->modalForm($model, $activity, 'เพิ่มการทบทวน · ' . $activity->name);
    }

    /** แก้ไขรายการทบทวน (AJAX modal) */
    public function actionUpdate($id)
    {
        $model = $this->findReview((int) $id);
        $activity = $this->findActivity((int) $model->activity_id);
        $me = UserHelper::GetEmployee();
        $userId = Yii::$app->user->id !== null ? (int) Yii::$app->user->id : null;

        if (!Ha12ReviewService::canManage($model, $userId, $me ? (int) $me->department : null)) {
            throw new ForbiddenHttpException('แก้ไขได้เฉพาะผู้สร้าง คนในสายหน่วยเจ้าของ หรือผู้ดูแล');
        }

        if ($this->saveFromPost($model, $me, $activity, false)) {
            return $this->jsonSaved();
        }
        return $this->modalForm($model, $activity, 'แก้ไขการทบทวน · ' . $activity->name);
    }

    /** ดูรายละเอียด + ผลติดตาม + ประวัติรุ่น (หน้าเต็ม) */
    public function actionView($id)
    {
        $model = Ha12Review::find()->with(['activity', 'ownerUnit', 'followups', 'versions'])->where(['id' => (int) $id])->one();
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบรายการทบทวน');
        }
        $me = UserHelper::GetEmployee();
        $userId = Yii::$app->user->id !== null ? (int) Yii::$app->user->id : null;
        if (!Ha12ReviewService::canView($model, $userId, $me ? (int) $me->department : null)) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ดูรายการนี้');
        }

        return $this->render('view', [
            'model' => $model,
            'activity' => $model->activity,
            'fields' => Ha12ReviewForm::fieldsFor((int) $model->activity->no),
            'followup' => new Ha12ReviewFollowup(['review_id' => (int) $model->id, 'followup_date' => date('Y-m-d')]),
            'canManage' => Ha12ReviewService::canManage($model, $userId, $me ? (int) $me->department : null),
        ]);
    }

    /** ลบแบบกู้คืนได้ (soft delete) */
    public function actionDelete($id)
    {
        $model = $this->findReview((int) $id);
        $me = UserHelper::GetEmployee();
        $userId = Yii::$app->user->id !== null ? (int) Yii::$app->user->id : null;
        if (!Ha12ReviewService::canManage($model, $userId, $me ? (int) $me->department : null)) {
            throw new ForbiddenHttpException('ลบได้เฉพาะผู้สร้าง คนในสายหน่วยเจ้าของ หรือผู้ดูแล');
        }

        $model->deleted = 1;
        $model->revision = (int) $model->revision + 1;
        $model->save(false);
        Ha12ReviewService::recordVersion($model, Ha12ReviewVersion::ACTION_DELETE);

        Yii::$app->session->setFlash('success', 'ลบรายการแล้ว (กู้คืนได้จากตัวกรอง “รายการที่ลบ”)');
        return $this->redirect(['index', 'activity_id' => $model->activity_id, 'fy' => $model->fiscal_year]);
    }

    /** กู้คืนรายการที่ลบ */
    public function actionRestore($id)
    {
        $model = $this->findReview((int) $id);
        $me = UserHelper::GetEmployee();
        $userId = Yii::$app->user->id !== null ? (int) Yii::$app->user->id : null;
        if (!Ha12ReviewService::canManage($model, $userId, $me ? (int) $me->department : null)) {
            throw new ForbiddenHttpException('กู้คืนได้เฉพาะผู้สร้าง คนในสายหน่วยเจ้าของ หรือผู้ดูแล');
        }

        $model->deleted = 0;
        $model->revision = (int) $model->revision + 1;
        $model->save(false);
        Ha12ReviewService::recordVersion($model, Ha12ReviewVersion::ACTION_RESTORE);

        Yii::$app->session->setFlash('success', 'กู้คืนรายการแล้ว');
        return $this->redirect(['index', 'activity_id' => $model->activity_id, 'fy' => $model->fiscal_year, 'deleted' => 1]);
    }

    /** เพิ่มผลติดตาม */
    public function actionAddFollowup($id)
    {
        $review = $this->findReview((int) $id);
        $me = UserHelper::GetEmployee();
        $userId = Yii::$app->user->id !== null ? (int) Yii::$app->user->id : null;
        if (!Ha12ReviewService::canManage($review, $userId, $me ? (int) $me->department : null)) {
            throw new ForbiddenHttpException('เพิ่มผลติดตามได้เฉพาะผู้สร้าง คนในสายหน่วยเจ้าของ หรือผู้ดูแล');
        }

        $f = new Ha12ReviewFollowup(['review_id' => (int) $review->id]);
        $f->load(Yii::$app->request->post());
        $f->followup_date = AppHelper::normalizeDateToDb(Yii::$app->request->post('followup_date_thai'));
        if ($f->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกผลติดตามแล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . implode(' ', $f->getFirstErrors()));
        }
        return $this->redirect(['view', 'id' => $review->id]);
    }

    /** ลบผลติดตาม */
    public function actionDeleteFollowup($id)
    {
        $f = Ha12ReviewFollowup::findOne((int) $id);
        if (!$f) {
            throw new NotFoundHttpException('ไม่พบผลติดตาม');
        }
        $review = $this->findReview((int) $f->review_id);
        $me = UserHelper::GetEmployee();
        $userId = Yii::$app->user->id !== null ? (int) Yii::$app->user->id : null;
        if (!Ha12ReviewService::canManage($review, $userId, $me ? (int) $me->department : null)) {
            throw new ForbiddenHttpException('ลบผลติดตามได้เฉพาะผู้สร้าง คนในสายหน่วยเจ้าของ หรือผู้ดูแล');
        }
        $f->delete();
        Yii::$app->session->setFlash('success', 'ลบผลติดตามแล้ว');
        return $this->redirect(['view', 'id' => $review->id]);
    }

    // --- helpers ----------------------------------------------------------

    /** รับค่าจากฟอร์ม → validate → บันทึก + เก็บประวัติรุ่น */
    private function saveFromPost(Ha12Review $model, $me, Ha12Activity $activity, bool $isNew): bool
    {
        $req = Yii::$app->request;
        if (!$req->isPost) {
            return false;
        }
        $model->load($req->post());
        $model->review_date = AppHelper::normalizeDateToDb($req->post('review_date_thai')) ?: $model->review_date;

        // กันเลือกหน่วยงานนอกสิทธิ์ (ยกเว้นผู้ดูแล)
        $allowed = array_map(static fn ($o) => (int) $o['id'], Ha12ReviewService::selectableUnits($me ? (int) $me->department : null));
        if ($model->owner_unit_id && !in_array((int) $model->owner_unit_id, $allowed, true) && !Ha12ReviewService::isManager()) {
            $model->owner_unit_id = $me ? (int) $me->department : null;
        }

        $model->revision = $isNew ? 1 : (int) $model->revision + 1;

        if (!$model->save()) {
            return false;
        }
        Ha12ReviewService::recordVersion(
            $model,
            $isNew ? Ha12ReviewVersion::ACTION_CREATE : Ha12ReviewVersion::ACTION_UPDATE
        );
        return true;
    }

    /** ตอบ JSON สำหรับ modal (บันทึกสำเร็จ → reload ตาราง) */
    private function jsonSaved()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['status' => 'success', 'message' => 'บันทึกเรียบร้อย', 'container' => '#ha12-review-list'];
        }
        return $this->redirect(['index']);
    }

    /** ส่งฟอร์มเข้า modal (GET หรือ validation fail) */
    private function modalForm(Ha12Review $model, Ha12Activity $activity, string $title)
    {
        $me = UserHelper::GetEmployee();
        $data = [
            'model' => $model,
            'activity' => $activity,
            'fields' => Ha12ReviewForm::fieldsFor((int) $activity->no),
            'units' => Ha12ReviewService::selectableUnits($me ? (int) $me->department : null),
        ];
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['title' => $title, 'content' => $this->renderAjax('_form', $data)];
        }
        return $this->render('_form', $data);
    }
}
