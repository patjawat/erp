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
     * ระดับ "ประเภทหลัก" (asset_type) แสดงเฉพาะกลุ่มที่คิดค่าเสื่อม:
     * ครุภัณฑ์ (EQUIP) + สิ่งปลูกสร้าง (STRUCT) — ตัดวัสดุ (MATER)/อาคาร (BLDG)/ไม่มีตัวตน (INTAN)/ไม่ระบุ ออก
     */
    private const DEPRECIABLE_TYPE_GROUPS = ['EQUIP', 'STRUCT'];

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['set' => ['POST']],
            ],
        ]);
    }

    public function actionIndex($level = DepreciationProfileResolver::SOURCE_TYPE, $q = null, $type = null)
    {
        if (!array_key_exists($level, self::LEVELS)) {
            $level = DepreciationProfileResolver::SOURCE_TYPE;
        }

        $query = (new Query())
            ->from('{{%categorise}}')
            ->where(['name' => $level]);
        // ประเภทหลัก: จำกัดเฉพาะกลุ่มที่คิดค่าเสื่อม (ครุภัณฑ์ + สิ่งปลูกสร้าง)
        if ($level === DepreciationProfileResolver::SOURCE_TYPE) {
            $query->andWhere(['group_id' => self::DEPRECIABLE_TYPE_GROUPS]);
        }
        // หมวด: กรองตามประเภทหลัก (category_id = asset_type.code)
        if ($level === DepreciationProfileResolver::SOURCE_CATEGORY && $type !== null && $type !== '') {
            $query->andWhere(['category_id' => $type]);
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

        // ระดับหมวด: ประเภทหลักที่เป็นแม่ของหมวด (ผูกผ่าน category_id = asset_type.code)
        // ใช้ทั้งเป็นตัวเลือก filter และป้ายในตาราง
        $typeOptions = [];
        if ($level === DepreciationProfileResolver::SOURCE_CATEGORY) {
            $parentCodes = (new Query())
                ->select('category_id')->distinct()
                ->from('{{%categorise}}')
                ->where(['name' => DepreciationProfileResolver::SOURCE_CATEGORY])
                ->andWhere(['not', ['category_id' => null]])
                ->andWhere(['<>', 'category_id', ''])
                ->column();
            if ($parentCodes) {
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
            Yii::$app->session->setFlash('error', 'ไม่พบรายการ');
            return $this->redirect(['index', 'level' => $level]);
        }

        // อ่านค่าเดิม (อาจ double-encoded) แล้วเขียนกลับเป็น JSON object จริงด้วย PHP
        // (JSON_SET ใช้ไม่ได้กับแถวที่ data_json เป็น JSON string ซ้อน)
        $current = (new Query())->select('data_json')->from('{{%categorise}}')->where(['id' => $id])->scalar();
        $dj = DepreciationProfileResolver::decodeDataJson($current);

        if ($profileId === '' || $profileId === null) {
            unset($dj['depreciation_profile_id']);
            $msg = 'ล้างการผูกเกณฑ์เรียบร้อย';
        } else {
            $dj['depreciation_profile_id'] = (int) $profileId;
            $msg = 'ผูกเกณฑ์เรียบร้อย';
        }

        Yii::$app->db->createCommand()
            ->update('{{%categorise}}',
                ['data_json' => empty($dj) ? null : json_encode($dj, JSON_UNESCAPED_UNICODE)],
                ['id' => $id])
            ->execute();
        Yii::$app->session->setFlash('success', $msg);

        return $this->redirect(['index', 'level' => $level, 'q' => $req->post('q'), 'type' => $req->post('type')]);
    }

    private static function extractProfileId($dataJson): ?int
    {
        $dj = DepreciationProfileResolver::decodeDataJson($dataJson);
        return !empty($dj['depreciation_profile_id']) ? (int) $dj['depreciation_profile_id'] : null;
    }
}
