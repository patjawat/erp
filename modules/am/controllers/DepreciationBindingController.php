<?php

namespace app\modules\am\controllers;

use Yii;
use yii\db\Query;
use yii\web\Controller;
use yii\data\Pagination;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\modules\am\models\DepreciationProfile;
use app\modules\am\services\DepreciationProfileResolver;

/**
 * ผูกเกณฑ์ค่าเสื่อม (profile) กับลำดับชั้นทรัพย์สินระดับ ประเภท/หมวด/รายการ
 * เก็บใน categorise.data_json.depreciation_profile_id (resolver อ่านค่านี้)
 */
class DepreciationBindingController extends Controller
{
    private const LEVELS = [
        DepreciationProfileResolver::SOURCE_TYPE => 'ประเภทหลัก',
        DepreciationProfileResolver::SOURCE_CATEGORY => 'หมวด',
        DepreciationProfileResolver::SOURCE_ITEM => 'รายการ/ชนิด',
    ];

    /**
     * ระดับ "ประเภทหลัก" (asset_type) แสดงเฉพาะกลุ่มที่คิดค่าเสื่อม
     * (นิยามกลางอยู่ที่ DepreciationProfileResolver::DEPRECIABLE_TYPE_GROUPS เพื่อให้หน้าจอกับตัวคำนวณใช้ชุดเดียวกัน)
     */
    private const DEPRECIABLE_TYPE_GROUPS = DepreciationProfileResolver::DEPRECIABLE_TYPE_GROUPS;

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['set' => ['POST'], 'bulk-set' => ['POST']],
            ],
        ]);
    }

    public function actionIndex($level = DepreciationProfileResolver::SOURCE_TYPE, $q = null, $type = null)
    {
        if (!array_key_exists($level, self::LEVELS)) {
            $level = DepreciationProfileResolver::SOURCE_TYPE;
        }

        // รหัสประเภทหลักที่คิดค่าเสื่อม — ใช้กรองระดับหมวด/รายการ ไม่ให้วัสดุ (MATER)
        // และสินทรัพย์ไม่มีตัวตนหลุดเข้ามาในหน้าผูกเกณฑ์
        $depreciableTypes = DepreciationProfileResolver::depreciableTypeCodes();

        $query = (new Query())
            ->from('{{%categorise}}')
            ->where(['name' => $level]);
        // ประเภทหลัก: จำกัดเฉพาะกลุ่มที่คิดค่าเสื่อม (อาคาร + ครุภัณฑ์ + สิ่งปลูกสร้าง)
        if ($level === DepreciationProfileResolver::SOURCE_TYPE) {
            $query->andWhere(['group_id' => self::DEPRECIABLE_TYPE_GROUPS]);
        }
        // หมวด/รายการ: ผูกกับประเภทหลักผ่าน category_id (= asset_type.code)
        if ($level !== DepreciationProfileResolver::SOURCE_TYPE) {
            $query->andWhere(['category_id' => $depreciableTypes]);
            if ($type !== null && $type !== '') {
                $query->andWhere(['category_id' => $type]);
            }
        }
        if ($q !== null && $q !== '') {
            $query->andWhere(['or', ['like', 'title', $q], ['like', 'code', $q]]);
        }

        $count = (clone $query)->count('*', Yii::$app->db);
        $pages = new Pagination(['totalCount' => $count, 'pageSize' => 50]);
        $rows = $query
            ->select(['id', 'code', 'title', 'data_json', 'category_id'])
            ->orderBy(['title' => SORT_ASC])
            ->offset($pages->offset)->limit($pages->limit)
            ->all();

        // ระดับหมวด/รายการ: ประเภทหลักที่เป็นแม่ (ผูกผ่าน category_id = asset_type.code)
        // ใช้ทั้งเป็นตัวเลือก filter และป้ายในตาราง
        $typeOptions = [];
        if ($level !== DepreciationProfileResolver::SOURCE_TYPE) {
            $parentCodes = (new Query())
                ->select('category_id')->distinct()
                ->from('{{%categorise}}')
                ->where(['name' => $level, 'category_id' => $depreciableTypes])
                ->column();
            if ($parentCodes) {
                // asset_type มีรหัสซ้ำหลายแถว — เก็บชื่อเดียวต่อรหัส
                foreach ((new Query())
                    ->select(['code', 'title'])
                    ->from('{{%categorise}}')
                    ->where(['name' => DepreciationProfileResolver::SOURCE_TYPE, 'code' => $parentCodes])
                    ->orderBy(['title' => SORT_ASC])->all() as $t) {
                    $typeOptions[$t['code']] = $t['title'];
                }
            }
            foreach ($rows as &$r) {
                $code = (string) ($r['category_id'] ?? '');
                $r['parent_type_code'] = $code !== '' ? $code : null;
                $r['parent_type_name'] = $code !== '' ? ($typeOptions[$code] ?? null) : null;
            }
            unset($r);
        }

        // decode current binding + collect profile names
        $profiles = DepreciationProfile::find()
            ->where(['status' => DepreciationProfile::STATUS_ACTIVE])
            ->orderBy(['code' => SORT_ASC])->all();
        $profileNames = [];
        foreach ($profiles as $p) {
            $profileNames[$p->id] = $p->code . ' — ' . $p->name;
        }

        foreach ($rows as &$r) {
            $r['bound_profile_id'] = self::extractProfileId($r['data_json']);
            $r['bound_profile_name'] = $r['bound_profile_id'] ? ($profileNames[$r['bound_profile_id']] ?? ('#' . $r['bound_profile_id'])) : null;
        }
        unset($r);

        return $this->render('index', [
            'level' => $level,
            'levels' => self::LEVELS,
            'q' => $q,
            'type' => $type,
            'typeOptions' => $typeOptions,
            'rows' => $rows,
            'pages' => $pages,
            'count' => $count,
            'profiles' => $profiles,
        ]);
    }

    public function actionSet()
    {
        $req = Yii::$app->request;
        $id = (int) $req->post('id');
        $level = $req->post('level');
        $profileId = $req->post('profile_id');

        if ($id <= 0) {
            if ($req->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['status' => 'error', 'message' => 'ไม่พบรายการ'];
            }
            Yii::$app->session->setFlash('error', 'ไม่พบรายการ');
            return $this->redirect(['index', 'level' => $level]);
        }

        $clear = ($profileId === '' || $profileId === null || (string) $profileId === '0');
        if (!$clear && !DepreciationProfile::find()->where(['id' => (int) $profileId])->exists()) {
            if ($req->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['status' => 'error', 'message' => 'ไม่พบเกณฑ์ที่เลือก'];
            }
            Yii::$app->session->setFlash('error', 'ไม่พบเกณฑ์ที่เลือก');
            return $this->redirect(['index', 'level' => $level, 'q' => $req->post('q'), 'type' => $req->post('type')]);
        }

        $n = $this->writeBinding([$id], $clear ? null : (int) $profileId);
        $msg = $clear ? 'ล้างการผูกเกณฑ์เรียบร้อย' : 'ผูกเกณฑ์เรียบร้อย';
        if ($n > 1) {
            $msg .= " (รหัสนี้มี {$n} แถวในทะเบียน — ตั้งให้ตรงกันทุกแถว)";
        }

        if ($req->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['status' => 'success', 'message' => $msg, 'container' => '#dp-binding-container'];
        }
        Yii::$app->session->setFlash('success', $msg);

        return $this->redirect(['index', 'level' => $level, 'q' => $req->post('q'), 'type' => $req->post('type')]);
    }

    /**
     * ผูก/ล้างเกณฑ์ให้หลายรายการพร้อมกัน (bulk)
     * profile_id: id ของเกณฑ์ · '0' = ล้างการผูก · '' = ไม่ระบุ (ถือเป็น error)
     * ตอบ JSON เมื่อเรียกผ่าน ajax (pjax reload) และ redirect เมื่อเป็น request ปกติ
     */
    public function actionBulkSet()
    {
        $req = Yii::$app->request;
        $level = $req->post('level');
        $ids = array_values(array_filter(
            array_map('intval', (array) $req->post('ids', [])),
            static fn($i) => $i > 0
        ));
        $ids = array_values(array_unique($ids));
        $profileId = $req->post('profile_id');
        $clear = ($profileId === '0' || $profileId === 0);
        $wantId = (!$clear && $profileId !== '' && $profileId !== null) ? (int) $profileId : null;

        $backUrl = ['index', 'level' => $level, 'q' => $req->post('q'), 'type' => $req->post('type')];

        // validate
        $error = null;
        if (empty($ids)) {
            $error = 'ยังไม่ได้เลือกรายการ';
        } elseif (!$clear && $wantId === null) {
            $error = 'กรุณาเลือกเกณฑ์ที่จะกำหนด';
        } elseif ($wantId !== null && !DepreciationProfile::find()->where(['id' => $wantId])->exists()) {
            $error = 'ไม่พบเกณฑ์ที่เลือก';
        }
        if ($error !== null) {
            if ($req->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['status' => 'error', 'message' => $error];
            }
            Yii::$app->session->setFlash('error', $error);
            return $this->redirect($backUrl);
        }

        $tx = Yii::$app->db->beginTransaction();
        try {
            $written = $this->writeBinding($ids, $clear ? null : $wantId);
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            if ($req->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['status' => 'error', 'message' => 'บันทึกไม่สำเร็จ: ' . $e->getMessage()];
            }
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ');
            return $this->redirect($backUrl);
        }

        $n = count($ids);
        $msg = $clear ? "ล้างการผูกเกณฑ์ {$n} รายการเรียบร้อย" : "ผูกเกณฑ์ให้ {$n} รายการเรียบร้อย";
        if ($written > $n) {
            $msg .= ' (มีรหัสซ้ำในทะเบียน — ตั้งให้ตรงกันครบทุกแถว)';
        }
        if ($req->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['status' => 'success', 'message' => $msg, 'container' => '#dp-binding-container'];
        }
        Yii::$app->session->setFlash('success', $msg);
        return $this->redirect($backUrl);
    }

    /**
     * เขียนการผูกเกณฑ์ลง categorise.data_json
     *
     * ผูกที่ระดับ "รหัส" ไม่ใช่ระดับแถว — ทะเบียนจริงเคยมีรหัสซ้ำหลายแถวจากการ import ซ้ำ
     * ถ้าเขียนแค่แถวที่ผู้ใช้เห็น ตัวหาเกณฑ์อาจไปอ่านอีกแถวที่ยังไม่ได้ผูก แล้วค่าเสื่อมไม่ขึ้น
     * (migration m260816_160000 รวมแถวซ้ำไปแล้ว — ที่นี่กันไว้เผื่อมี import ซ้ำอีก)
     *
     * @param int[] $ids id ของแถวที่ผู้ใช้เลือก
     * @param int|null $profileId null = ล้างการผูก
     * @return int จำนวนแถวที่เขียนจริง (อาจมากกว่าจำนวนที่เลือกเมื่อมีรหัสซ้ำ)
     */
    private function writeBinding(array $ids, ?int $profileId): int
    {
        $picked = (new Query())->select(['id', 'name', 'code'])->from('{{%categorise}}')
            ->where(['id' => $ids])->all();
        if (!$picked) {
            return 0;
        }

        // ขยายจากแถวที่เลือก → ทุกแถวที่ name + code เดียวกัน
        $conditions = ['or'];
        foreach ($picked as $p) {
            $conditions[] = ['name' => $p['name'], 'code' => $p['code']];
        }
        $rows = (new Query())->select(['id', 'data_json'])->from('{{%categorise}}')
            ->where($conditions)->all();

        foreach ($rows as $row) {
            // อ่านค่าเดิม (อาจ double-encoded) แล้วเขียนกลับเป็น JSON object จริงด้วย PHP
            // (JSON_SET ใช้ไม่ได้กับแถวที่ data_json เป็น JSON string ซ้อน)
            $dj = DepreciationProfileResolver::decodeDataJson($row['data_json']);
            if ($profileId === null) {
                unset($dj['depreciation_profile_id']);
            } else {
                $dj['depreciation_profile_id'] = $profileId;
            }
            Yii::$app->db->createCommand()
                ->update('{{%categorise}}',
                    ['data_json' => empty($dj) ? null : json_encode($dj, JSON_UNESCAPED_UNICODE)],
                    ['id' => $row['id']])
                ->execute();
        }

        return count($rows);
    }

    private static function extractProfileId($dataJson): ?int
    {
        $dj = DepreciationProfileResolver::decodeDataJson($dataJson);
        return !empty($dj['depreciation_profile_id']) ? (int) $dj['depreciation_profile_id'] : null;
    }
}
