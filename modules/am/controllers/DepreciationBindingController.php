<?php

namespace app\modules\am\controllers;

use Yii;
use yii\db\Query;
use yii\web\Controller;
use yii\data\Pagination;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\modules\am\models\DepreciationProfile;
use app\modules\am\services\DepreciationRunService;
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
                'rules' => [
                    ['allow' => true, 'actions' => ['index'], 'roles' => ['depreciationView']],
                    ['allow' => true, 'actions' => ['set', 'bulk-set'], 'roles' => ['depreciationSetup']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['set' => ['POST'], 'bulk-set' => ['POST']],
            ],
        ]);
    }

    public function actionIndex($level = DepreciationProfileResolver::SOURCE_TYPE, $q = null, $type = null, $bind = null)
    {
        if (!array_key_exists($level, self::LEVELS)) {
            $level = DepreciationProfileResolver::SOURCE_TYPE;
        }
        if (!in_array($bind, ['yes', 'no'], true)) {
            $bind = null;
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
        // กรองตามสถานะการผูก — งานหลักของหน้านี้คือไล่ปิดรายการที่ยังไม่ผูก
        if ($bind === 'yes') {
            $query->andWhere(['like', 'data_json', 'depreciation_profile_id']);
        } elseif ($bind === 'no') {
            // data_json เป็น NULL ได้ — NOT LIKE กับ NULL ให้ผล NULL จึงต้องรวมเงื่อนไข IS NULL ด้วย
            $query->andWhere(['or',
                ['data_json' => null],
                ['not like', 'data_json', 'depreciation_profile_id'],
            ]);
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

        $assetCounts = $this->assetCountsFor($level, array_column($rows, 'code'));
        $inherited = $this->inheritedBindings($level, $rows);

        foreach ($rows as &$r) {
            $r['asset_count'] = (int) ($assetCounts[(string) $r['code']] ?? 0);
            // เกณฑ์ที่ "ใช้จริง" เมื่อแถวนี้ไม่ได้ผูกเอง — สืบทอดจากประเภทหลักที่เป็นแม่
            $inh = $inherited[(string) ($r['parent_type_code'] ?? '')] ?? null;
            $r['inherited_profile_id'] = ($r['bound_profile_id'] === null) ? $inh : null;
            $r['inherited_profile_name'] = $r['inherited_profile_id']
                ? ($profileNames[$r['inherited_profile_id']] ?? ('#' . $r['inherited_profile_id']))
                : null;
        }
        unset($r);

        return $this->render('index', [
            'level' => $level,
            'levels' => self::LEVELS,
            'q' => $q,
            'type' => $type,
            'bind' => $bind,
            'typeOptions' => $typeOptions,
            'rows' => $rows,
            'pages' => $pages,
            'count' => $count,
            'profiles' => $profiles,
            'canEdit' => Yii::$app->user->can('depreciationSetup'),
            'recentLogs' => $this->recentLogs($profileNames),
        ]);
    }

    /**
     * จำนวนทรัพย์สินที่ผูกอยู่กับแต่ละรหัสในระดับนั้น (นับเฉพาะชุดที่คิดค่าเสื่อมจริง)
     * ใช้บอกผู้ใช้ว่าการผูกแถวนี้กระทบกี่ชิ้น — เดิมผูกโดยไม่รู้ผลกระทบเลย
     *
     * @param string[] $codes
     * @return array<string,int> code => จำนวนชิ้น
     */
    private function assetCountsFor(string $level, array $codes): array
    {
        $codes = array_values(array_filter(array_unique(array_map('strval', $codes)), static fn($c) => $c !== ''));
        if (!$codes) {
            return [];
        }

        $column = [
            DepreciationProfileResolver::SOURCE_TYPE => 'asset_type_id',
            DepreciationProfileResolver::SOURCE_CATEGORY => 'asset_category_id',
            DepreciationProfileResolver::SOURCE_ITEM => 'asset_item_id',
        ][$level] ?? null;
        if ($column === null) {
            return [];
        }

        $rows = (new DepreciationRunService())->eligibleQuery()
            ->select([$column, 'n' => 'COUNT(*)'])
            ->andWhere([$column => $codes])
            ->groupBy($column)
            ->orderBy([])
            ->asArray()
            ->all();

        $out = [];
        foreach ($rows as $r) {
            $out[(string) $r[$column]] = (int) $r['n'];
        }

        return $out;
    }

    /**
     * เกณฑ์ที่ผูกไว้ที่ระดับประเภทหลัก — ใช้แสดงว่าแถวที่ยังไม่ผูก "รับเกณฑ์อะไรมาอยู่แล้ว"
     * กันผู้ใช้ผูกซ้ำโดยไม่จำเป็น
     *
     * @return array<string,int> asset_type code => profile_id
     */
    private function inheritedBindings(string $level, array $rows): array
    {
        if ($level === DepreciationProfileResolver::SOURCE_TYPE) {
            return [];
        }
        $parentCodes = array_values(array_filter(array_unique(
            array_map(static fn($r) => (string) ($r['parent_type_code'] ?? ''), $rows)
        ), static fn($c) => $c !== ''));
        if (!$parentCodes) {
            return [];
        }

        $out = [];
        foreach ((new Query())->select(['code', 'data_json'])->from('{{%categorise}}')
            ->where(['name' => DepreciationProfileResolver::SOURCE_TYPE, 'code' => $parentCodes])
            ->all() as $row) {
            $pid = self::extractProfileId($row['data_json']);
            if ($pid !== null) {
                $out[(string) $row['code']] = $pid;
            }
        }

        return $out;
    }

    /**
     * ประวัติการผูกล่าสุด (แสดงท้ายหน้า) — ตารางอาจยังไม่ถูกสร้างถ้ายังไม่ได้รัน migration
     *
     * @param array<int,string> $profileNames
     */
    private function recentLogs(array $profileNames): array
    {
        try {
            if (Yii::$app->db->getTableSchema('{{%depreciation_binding_logs}}') === null) {
                return [];
            }
            $logs = (new Query())->from('{{%depreciation_binding_logs}}')
                ->orderBy(['id' => SORT_DESC])->limit(15)->all();
        } catch (\Throwable $e) {
            return [];
        }

        // ชื่อผู้ทำรายการ — ไม่ให้ล้มถ้าโครงตาราง user ต่างไป
        $userNames = [];
        $userIds = array_values(array_filter(array_unique(array_column($logs, 'created_by'))));
        if ($userIds) {
            try {
                foreach ((new Query())->select(['id', 'username'])->from('{{%user}}')
                    ->where(['id' => $userIds])->all() as $u) {
                    $userNames[(int) $u['id']] = $u['username'];
                }
            } catch (\Throwable $e) {
                $userNames = [];
            }
        }

        foreach ($logs as &$l) {
            $l['old_profile_name'] = $l['old_profile_id'] ? ($profileNames[$l['old_profile_id']] ?? ('#' . $l['old_profile_id'])) : null;
            $l['new_profile_name'] = $l['new_profile_id'] ? ($profileNames[$l['new_profile_id']] ?? ('#' . $l['new_profile_id'])) : null;
            $l['created_by_name'] = $l['created_by']
                ? ($userNames[(int) $l['created_by']] ?? ('#' . $l['created_by']))
                : null;
        }
        unset($l);

        return $logs;
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
            return $this->redirect(['index', 'level' => $level, 'q' => $req->post('q'), 'type' => $req->post('type'), 'bind' => $req->post('bind')]);
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

        return $this->redirect(['index', 'level' => $level, 'q' => $req->post('q'), 'type' => $req->post('type'), 'bind' => $req->post('bind')]);
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

        $backUrl = ['index', 'level' => $level, 'q' => $req->post('q'), 'type' => $req->post('type'), 'bind' => $req->post('bind')];

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
            $written = $this->writeBinding($ids, $clear ? null : $wantId, 'bulk');
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
     * @param string $source single|bulk — บันทึกลงประวัติเพื่อรู้ที่มาของการเปลี่ยน
     * @return int จำนวนแถวที่เขียนจริง (อาจมากกว่าจำนวนที่เลือกเมื่อมีรหัสซ้ำ)
     */
    private function writeBinding(array $ids, ?int $profileId, string $source = 'single'): int
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
        $rows = (new Query())->select(['id', 'name', 'code', 'title', 'data_json'])->from('{{%categorise}}')
            ->where($conditions)->all();

        // รวมแถวตามรหัส — ประวัติบันทึกครั้งเดียวต่อรหัส (ผูกที่ระดับรหัส ไม่ใช่ระดับแถว)
        $byCode = [];
        foreach ($rows as $row) {
            // อ่านค่าเดิม (อาจ double-encoded) แล้วเขียนกลับเป็น JSON object จริงด้วย PHP
            // (JSON_SET ใช้ไม่ได้กับแถวที่ data_json เป็น JSON string ซ้อน)
            $dj = DepreciationProfileResolver::decodeDataJson($row['data_json']);
            $old = self::extractProfileId($row['data_json']);
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

            $key = $row['name'] . '::' . $row['code'];
            if (!isset($byCode[$key])) {
                $byCode[$key] = [
                    'level' => $row['name'],
                    'code' => $row['code'],
                    'title' => $row['title'],
                    'old_profile_id' => $old,
                    'rows_written' => 0,
                ];
            }
            $byCode[$key]['rows_written']++;
        }

        foreach ($byCode as $entry) {
            if ($entry['old_profile_id'] === $profileId) {
                continue; // ไม่มีอะไรเปลี่ยน ไม่ต้องบันทึกประวัติ
            }
            self::writeLog($entry, $profileId, $source);
        }

        return count($rows);
    }

    /**
     * บันทึกประวัติการผูก — ห้ามทำให้การบันทึกหลักล้มเหลว (ตารางอาจยังไม่ถูกสร้าง)
     */
    private static function writeLog(array $entry, ?int $newProfileId, string $source): void
    {
        try {
            if (Yii::$app->db->getTableSchema('{{%depreciation_binding_logs}}') === null) {
                return;
            }
            Yii::$app->db->createCommand()->insert('{{%depreciation_binding_logs}}', [
                'level' => $entry['level'],
                'code' => $entry['code'],
                'title' => mb_substr((string) $entry['title'], 0, 255),
                'old_profile_id' => $entry['old_profile_id'],
                'new_profile_id' => $newProfileId,
                'rows_written' => $entry['rows_written'],
                'source' => $source,
                // ไม่มี component user เมื่อเรียกจาก console (เช่น seed) — อย่าให้ล้ม
                'created_by' => (Yii::$app->has('user') && !Yii::$app->user->isGuest) ? Yii::$app->user->id : null,
                'created_at' => date('Y-m-d H:i:s'),
            ])->execute();
        } catch (\Throwable $e) {
            Yii::warning('บันทึกประวัติการผูกเกณฑ์ไม่สำเร็จ: ' . $e->getMessage(), __METHOD__);
        }
    }

    private static function extractProfileId($dataJson): ?int
    {
        $dj = DepreciationProfileResolver::decodeDataJson($dataJson);
        return !empty($dj['depreciation_profile_id']) ? (int) $dj['depreciation_profile_id'] : null;
    }
}
