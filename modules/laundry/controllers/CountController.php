<?php

namespace app\modules\laundry\controllers;

use app\components\AppHelper;
use app\modules\hr\models\Organization;
use app\modules\laundry\models\LaundryItemCategory;
use app\modules\laundry\models\LaundryUnit;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * ตรวจนับผ้า — ไปนับผ้าสะอาดคงเหลือที่ตู้ของหน่วยงาน (รายประเภท เป็นชิ้น)
 * flow การ์ดหน่วยงานเหมือนรับผ้า → แตะการ์ด → ลงจำนวนทุกประเภทในครั้งเดียว
 */
class CountController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['index'], 'roles' => ['laundry.view']],
                    ['allow' => true, 'actions' => ['save'], 'roles' => ['laundry.manage']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['save' => ['POST']],
            ],
        ];
    }

    public function actionIndex()
    {
        $dateInput = trim((string) Yii::$app->request->get('date'));
        $date = $dateInput !== '' ? (AppHelper::convertToGregorian($dateInput) ?: date('Y-m-d')) : date('Y-m-d');

        $units = LaundryUnit::find()->where(['is_active' => 1])->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->all();
        $treeIds = array_map(static fn($u) => $u->tree_id, $units);
        $names = $treeIds
            ? Organization::find()->select(['name', 'id'])->where(['id' => $treeIds])->indexBy('id')->column()
            : [];

        // ประเภทผ้าทั้งหมด (ให้ลงจำนวนทีเดียว) แยกตามหมวด — ผ้าของ รพ. / ผ้าจากหน่วยงานภายนอก (Refer ส่งกลับ)
        $items = (new Query())->select(['id', 'item_name', 'category_id'])->from('laundry_item')
            ->where(['is_active' => 1])->orderBy(['item_name' => SORT_ASC])->all();
        $itemGroups = LaundryItemCategory::group($items);

        // สรุปต่อหน่วยงาน: จำนวนครั้ง + ผลนับล่าสุด (วันที่ / คนนับ / กี่ประเภท / รวมชิ้น)
        $summary = [];
        if ($treeIds) {
            $rows = (new Query())->select(['tree_id', 'times' => new Expression('COUNT(*)'), 'last_id' => new Expression('MAX(id)')])
                ->from('laundry_unit_count')->where(['tree_id' => $treeIds])->groupBy('tree_id')->all();
            $lastIds = array_column($rows, 'last_id');
            $lastHead = $lastIds
                ? (new Query())->select(['id', 'counted_at', 'created_by'])->from('laundry_unit_count')->where(['id' => $lastIds])->indexBy('id')->all()
                : [];
            $lastLines = $lastIds
                ? (new Query())->select(['count_id', 'total' => new Expression('SUM(qty)'), 'types' => new Expression('COUNT(*)')])
                    ->from('laundry_unit_count_line')->where(['count_id' => $lastIds])->groupBy('count_id')->indexBy('count_id')->all()
                : [];
            // ชื่อคนนับ (created_by = user_id) → fullname
            $userIds = array_filter(array_column($lastHead, 'created_by'));
            $staffNames = $userIds
                ? \yii\helpers\ArrayHelper::map(
                    \app\modules\hr\models\Employees::find()->select(['user_id', 'prefix', 'fname', 'lname'])->where(['user_id' => $userIds])->asArray()->all(),
                    'user_id',
                    static fn($e) => trim(($e['prefix'] ?? '') . ($e['fname'] ?? '') . ' ' . ($e['lname'] ?? ''))
                )
                : [];
            foreach ($rows as $r) {
                $head = $lastHead[$r['last_id']] ?? null;
                $line = $lastLines[$r['last_id']] ?? null;
                $summary[$r['tree_id']] = [
                    'times' => (int) $r['times'],
                    'latest' => (int) ($line['total'] ?? 0),
                    'types' => (int) ($line['types'] ?? 0),
                    'counted_at' => $head['counted_at'] ?? null,
                    'staff' => $head && $head['created_by'] ? ($staffNames[$head['created_by']] ?? '') : '',
                ];
            }
        }

        return $this->render('index', compact('date', 'units', 'names', 'items', 'itemGroups', 'summary'));
    }

    public function actionSave()
    {
        $req = Yii::$app->request;
        $treeId = (int) $req->post('tree_id');
        $date = AppHelper::convertToGregorian(trim((string) $req->post('counted_date'))) ?: date('Y-m-d');
        $time = trim((string) $req->post('counted_time')) ?: date('H:i');
        $qty = (array) $req->post('qty', []);

        if (!$treeId || !Organization::find()->where(['id' => $treeId])->exists()) {
            Yii::$app->session->setFlash('error', 'ไม่พบหน่วยงาน');
            return $this->redirect(['index', 'date' => AppHelper::convertToThai($date)]);
        }

        $lines = [];
        foreach ($qty as $itemId => $q) {
            $q = trim((string) $q);
            if ($q === '' || !ctype_digit($q)) {
                continue;
            }
            if ((int) $q > 0 && ctype_digit((string) $itemId)) {
                $lines[(int) $itemId] = (int) $q;
            }
        }
        if (!$lines) {
            Yii::$app->session->setFlash('error', 'กรุณาลงจำนวนอย่างน้อยหนึ่งประเภท');
            return $this->redirect(['index', 'date' => AppHelper::convertToThai($date)]);
        }

        $db = Yii::$app->db;
        $tx = $db->beginTransaction();
        try {
            $ts = strtotime($date . ' ' . $time) ?: time();
            $db->createCommand()->insert('laundry_unit_count', [
                'tree_id' => $treeId,
                'counted_at' => date('Y-m-d H:i:s', $ts),
                'created_by' => Yii::$app->user->id ? (int) Yii::$app->user->id : null,
                'created_at' => date('Y-m-d H:i:s'),
            ])->execute();
            $countId = (int) $db->getLastInsertID();
            $db->createCommand()->update('laundry_unit_count',
                ['count_no' => 'CN' . (((int) date('Y', $ts) + 543) % 100) . '-' . str_pad((string) $countId, 6, '0', STR_PAD_LEFT)],
                ['id' => $countId])->execute();
            foreach ($lines as $itemId => $q) {
                $db->createCommand()->insert('laundry_unit_count_line', [
                    'count_id' => $countId, 'item_id' => $itemId, 'qty' => $q,
                ])->execute();
            }
            $tx->commit();
            Yii::$app->session->setFlash('success', 'บันทึกผลตรวจนับแล้ว');
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ');
        }
        return $this->redirect(['index', 'date' => AppHelper::convertToThai($date)]);
    }
}
