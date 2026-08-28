<?php

namespace app\modules\am\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use app\components\AppHelper;
use app\modules\am\models\DepreciationProfile;
use app\modules\am\models\DepreciationProfileRate;
use app\modules\am\services\DepreciationProfileResolver;

/**
 * เกณฑ์ค่าเสื่อม + อัตราหลายช่วง (screens 1-2)
 */
class DepreciationProfileController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['index', 'view'], 'roles' => ['depreciationView']],
                    ['allow' => true, 'roles' => ['depreciationSetup']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'rate-delete' => ['POST'],
                    'rate-create' => ['POST'],
                    'seed-standard' => ['POST'],
                    'clear-standard' => ['POST'],
                ],
            ],
        ]);
    }

    /** คำนำหน้ารหัสของเกณฑ์ตั้งต้นมาตรฐาน (ใช้ตรวจ/ล้าง) */
    private const STD_PREFIX = 'STD-';
    /** คำนำหน้าเดิม (เกณฑ์ตัวอย่างชุดแรก) — ล้างทิ้งตอนนำเข้าข้อมูลตั้งต้น */
    private const LEGACY_PREFIX = 'DEMO-';

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => DepreciationProfile::find()->orderBy(['status' => SORT_ASC, 'code' => SORT_ASC]),
            'pagination' => ['pageSize' => 30],
        ]);

        $hasStd = DepreciationProfile::find()
            ->where(['like', 'code', self::STD_PREFIX . '%', false])->exists();

        return $this->render('index', ['dataProvider' => $dataProvider, 'hasStd' => $hasStd]);
    }

    /**
     * เกณฑ์ค่าเสื่อมตั้งต้นตามแนวทางกรมบัญชีกลาง (มาตรฐานการบัญชีภาครัฐ ฉบับที่ 17)
     * วิธีเส้นตรง · มูลค่าซาก 1 บาท · ผูกเข้า asset_type.code ตามกลุ่มที่คิดค่าเสื่อม
     *
     * @return array<int,array{code:string,name:string,months:int,bind:string}>
     */
    private static function standardDefs(): array
    {
        // อายุการใช้งาน (ปี) → เดือน
        $y = static fn(int $years): int => $years * 12;
        return [
            // อาคาร / สิ่งก่อสร้าง
            ['code' => 'STD-BLDG', 'name' => 'อาคารถาวร (40 ปี)', 'months' => $y(40), 'bind' => '1'],
            ['code' => 'STD-STR-CONCRETE', 'name' => 'สิ่งก่อสร้าง คอนกรีต/เหล็ก (25 ปี)', 'months' => $y(25), 'bind' => 'STR_GRP_CONCRETE'],
            ['code' => 'STD-STR-WOOD', 'name' => 'สิ่งก่อสร้าง ไม้/วัสดุอื่น (15 ปี)', 'months' => $y(15), 'bind' => 'STR_GRP_WOOD'],
            ['code' => 'STD-STR-TEMP', 'name' => 'อาคารชั่วคราว/โรงเรือน (15 ปี)', 'months' => $y(15), 'bind' => 'STR_GRP_TEMP'],
            // ครุภัณฑ์
            ['code' => 'STD-OFF', 'name' => 'ครุภัณฑ์สำนักงาน (12 ปี)', 'months' => $y(12), 'bind' => 'OFF'],
            ['code' => 'STD-VEH', 'name' => 'ครุภัณฑ์ยานพาหนะและขนส่ง (8 ปี)', 'months' => $y(8), 'bind' => 'VEH'],
            ['code' => 'STD-ELE', 'name' => 'ครุภัณฑ์ไฟฟ้าและวิทยุ (8 ปี)', 'months' => $y(8), 'bind' => 'ELE'],
            ['code' => 'STD-ADV', 'name' => 'ครุภัณฑ์โฆษณาและเผยแพร่ (8 ปี)', 'months' => $y(8), 'bind' => 'ADV'],
            ['code' => 'STD-AGR', 'name' => 'ครุภัณฑ์การเกษตร (8 ปี)', 'months' => $y(8), 'bind' => 'AGR'],
            ['code' => 'STD-IND', 'name' => 'ครุภัณฑ์โรงงาน (8 ปี)', 'months' => $y(8), 'bind' => 'IND'],
            ['code' => 'STD-SCI', 'name' => 'ครุภัณฑ์วิทยาศาสตร์ (10 ปี)', 'months' => $y(10), 'bind' => 'SCI'],
            ['code' => 'STD-MED', 'name' => 'ครุภัณฑ์การแพทย์ (10 ปี)', 'months' => $y(10), 'bind' => 'MED'],
            ['code' => 'STD-COM', 'name' => 'ครุภัณฑ์คอมพิวเตอร์ (5 ปี)', 'months' => $y(5), 'bind' => 'COM'],
            ['code' => 'STD-EDU', 'name' => 'ครุภัณฑ์การศึกษา (8 ปี)', 'months' => $y(8), 'bind' => 'EDU'],
            ['code' => 'STD-HOM', 'name' => 'ครุภัณฑ์งานบ้านงานครัว (8 ปี)', 'months' => $y(8), 'bind' => 'HOM'],
        ];
    }

    /**
     * นำเข้าข้อมูลตั้งต้น: สร้างเกณฑ์มาตรฐาน (idempotent) แล้วผูกเข้ากับทุกประเภทที่คิดค่าเสื่อม
     * ล้างเกณฑ์ชุดตัวอย่างเดิม (DEMO-) ทิ้งไปด้วย
     */
    public function actionSeedStandard()
    {
        $created = 0;
        $binds = 0;
        $tx = Yii::$app->db->beginTransaction();
        try {
            $this->purgeByPrefix(self::LEGACY_PREFIX); // ล้างชุดตัวอย่างเดิมก่อน

            foreach (self::standardDefs() as $d) {
                $p = DepreciationProfile::findOne(['code' => $d['code']]) ?: new DepreciationProfile();
                if ($p->isNewRecord) {
                    $p->code = $d['code'];
                    $created++;
                }
                $p->name = $d['name'];
                $p->method = DepreciationProfile::METHOD_STRAIGHT_LINE;
                $p->useful_life_months = $d['months'];
                $p->annual_rate = null;
                $p->salvage_value_type = DepreciationProfile::SALVAGE_ONE_BAHT;
                $p->salvage_value = 1;
                $p->calculation_basis = DepreciationProfile::BASIS_MONTHLY;
                $p->start_rule = DepreciationProfile::START_DAY_15_CUTOFF;
                $p->rounding_scale = 2;
                $p->status = DepreciationProfile::STATUS_ACTIVE;
                if (!$p->save()) {
                    throw new \RuntimeException('บันทึกเกณฑ์ ' . $d['code'] . ' ไม่สำเร็จ: ' . implode(' ', $p->getFirstErrors()));
                }

                // ผูกเข้ากับทุกแถว asset_type ที่ code ตรง (ข้อมูลอาจมีซ้ำหลายแถว)
                $rows = (new \yii\db\Query())->select(['id', 'data_json'])->from('{{%categorise}}')
                    ->where(['name' => DepreciationProfileResolver::SOURCE_TYPE, 'code' => $d['bind']])->all();
                foreach ($rows as $row) {
                    $dj = DepreciationProfileResolver::decodeDataJson($row['data_json']);
                    $dj['depreciation_profile_id'] = (int) $p->id;
                    Yii::$app->db->createCommand()->update('{{%categorise}}',
                        ['data_json' => json_encode($dj, JSON_UNESCAPED_UNICODE)], ['id' => $row['id']])->execute();
                    $binds++;
                }
            }
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::$app->session->setFlash('error', 'นำเข้าข้อมูลตั้งต้นไม่สำเร็จ: ' . $e->getMessage());
            return $this->redirect(['index']);
        }

        Yii::$app->session->setFlash('success', sprintf(
            'นำเข้าเกณฑ์ตั้งต้นมาตรฐานแล้ว (สร้างใหม่ %d รายการ) และผูกเข้าประเภททรัพย์สิน %d จุด — เปิดหน้า “ผูกเกณฑ์เข้าลำดับชั้น” เพื่อตรวจ/ปรับได้',
            $created, $binds
        ));
        return $this->redirect(['index']);
    }

    /**
     * ล้างข้อมูลตั้งต้น: ถอนการผูกทุกจุดที่ชี้ไปเกณฑ์ STD- แล้วลบเกณฑ์ STD-
     */
    public function actionClearStandard()
    {
        $tx = Yii::$app->db->beginTransaction();
        try {
            $this->purgeByPrefix(self::STD_PREFIX);
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::$app->session->setFlash('error', 'ล้างข้อมูลตั้งต้นไม่สำเร็จ (อาจมีทรัพย์สินใช้เกณฑ์นี้อยู่): ' . $e->getMessage());
            return $this->redirect(['index']);
        }

        Yii::$app->session->setFlash('success', 'ล้างข้อมูลตั้งต้นเรียบร้อย');
        return $this->redirect(['index']);
    }

    /**
     * ถอน binding ทุกระดับที่ชี้ไปเกณฑ์ตาม prefix แล้วลบเกณฑ์นั้น (ใช้ภายใน transaction)
     */
    private function purgeByPrefix(string $prefix): void
    {
        $ids = array_map('intval', DepreciationProfile::find()->select('id')
            ->where(['like', 'code', $prefix . '%', false])->column());
        if (!$ids) {
            return;
        }
        $rows = (new \yii\db\Query())->select(['id', 'data_json'])->from('{{%categorise}}')
            ->where(['name' => DepreciationProfileResolver::PRECEDENCE])
            ->andWhere(['like', 'data_json', 'depreciation_profile_id'])->all();
        foreach ($rows as $row) {
            $dj = DepreciationProfileResolver::decodeDataJson($row['data_json']);
            if (!empty($dj['depreciation_profile_id']) && in_array((int) $dj['depreciation_profile_id'], $ids, true)) {
                unset($dj['depreciation_profile_id']);
                Yii::$app->db->createCommand()->update('{{%categorise}}',
                    ['data_json' => empty($dj) ? null : json_encode($dj, JSON_UNESCAPED_UNICODE)],
                    ['id' => $row['id']])->execute();
            }
        }
        DepreciationProfile::deleteAll(['id' => $ids]);
    }

    public function actionView($id)
    {
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    public function actionCreate()
    {
        $model = new DepreciationProfile();
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post())) {
            $this->convertProfileDatesToGregorian($model);
            if ($model->save()) {
                // เปิดผ่าน .open-modal → ตอบ JSON ให้ erp.js ปิด modal + reload pjax
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ['status' => 'success', 'message' => 'สร้างเกณฑ์ค่าเสื่อมเรียบร้อย', 'container' => '#am-dp-container'];
                }
                Yii::$app->session->setFlash('success', 'สร้างเกณฑ์ค่าเสื่อมเรียบร้อย');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        $this->convertProfileDatesToThai($model);

        // GET/validation-fail ผ่าน AJAX → ส่งเนื้อหาฟอร์มเข้า modal
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => Yii::$app->request->get('title', 'เพิ่มเกณฑ์ค่าเสื่อม'),
                'content' => $this->renderAjax('_form', ['model' => $model]),
            ];
        }

        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // ป้องกันแก้เกณฑ์ที่ถูกใช้แล้ว (กระทบ snapshot ทรัพย์สินเก่า) — แนะนำสร้างเกณฑ์ใหม่
        if ($model->load(Yii::$app->request->post())) {
            $this->convertProfileDatesToGregorian($model);
            if ($model->save()) {
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ['status' => 'success', 'message' => 'บันทึกการแก้ไขเรียบร้อย (ไม่กระทบ snapshot ทรัพย์สินเดิม)', 'container' => '#am-dp-container'];
                }
                Yii::$app->session->setFlash('success', 'บันทึกการแก้ไขเรียบร้อย (ไม่กระทบ snapshot ทรัพย์สินเดิม)');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        $this->convertProfileDatesToThai($model);

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => Yii::$app->request->get('title', 'แก้ไขเกณฑ์ค่าเสื่อม'),
                'content' => $this->renderAjax('_form', ['model' => $model]),
            ];
        }

        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        // ลบไม่ได้ถ้ามีอัตราหรือถูกใช้งาน (FK restrict บน asset_depreciations/profile_rates)
        try {
            $model->delete();
            Yii::$app->session->setFlash('success', 'ลบเกณฑ์เรียบร้อย');
        } catch (\Throwable $e) {
            Yii::$app->session->setFlash('error', 'ลบไม่ได้: เกณฑ์นี้ถูกใช้งานหรือมีอัตราค่าเสื่อมผูกอยู่');
        }
        return $this->redirect(['index']);
    }

    /**
     * เพิ่มช่วงอัตรา (validate ไม่ซ้อนกันใน model)
     */
    public function actionRateCreate($id)
    {
        $profile = $this->findModel($id);
        $rate = new DepreciationProfileRate();
        $rate->depreciation_profile_id = $profile->id;
        if ($rate->load(Yii::$app->request->post()) && $rate->save()) {
            Yii::$app->session->setFlash('success', 'เพิ่มช่วงอัตราเรียบร้อย');
        } else {
            Yii::$app->session->setFlash('error', 'เพิ่มช่วงอัตราไม่สำเร็จ: ' . implode(' ', $rate->getFirstErrors()));
        }
        return $this->redirect(['view', 'id' => $profile->id]);
    }

    public function actionRateDelete($id)
    {
        $rate = DepreciationProfileRate::findOne($id);
        if ($rate) {
            $pid = $rate->depreciation_profile_id;
            $rate->delete();
            Yii::$app->session->setFlash('success', 'ลบช่วงอัตราเรียบร้อย');
            return $this->redirect(['view', 'id' => $pid]);
        }
        return $this->redirect(['index']);
    }

    private function convertProfileDatesToGregorian(DepreciationProfile $model): void
    {
        foreach (['effective_from', 'effective_to'] as $attribute) {
            $value = $model->{$attribute};
            if ($value === null || $value === '') {
                $model->{$attribute} = null;
                continue;
            }

            $converted = AppHelper::convertToGregorian($value);
            $model->{$attribute} = $converted ?? $value;
        }
    }

    private function convertProfileDatesToThai(DepreciationProfile $model): void
    {
        foreach (['effective_from', 'effective_to'] as $attribute) {
            $value = $model->{$attribute};
            if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                $model->{$attribute} = AppHelper::convertToThai($value);
            }
        }
    }

    protected function findModel($id)
    {
        if (($model = DepreciationProfile::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('ไม่พบเกณฑ์ค่าเสื่อม');
    }
}
