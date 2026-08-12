<?php

namespace app\modules\me\controllers;

use app\components\ModalHelper;
use app\components\UserHelper;
use app\modules\hr\models\Employees;
use app\modules\roster\helpers\RosterSwapService;
use app\modules\roster\models\Item;
use app\modules\roster\models\Period;
use app\modules\roster\models\Request as RosterRequest;
use app\modules\roster\models\ShiftType;
use app\modules\roster\models\Swap;
use app\modules\roster\models\UnitShift;
use Yii;
use yii\web\Controller;
use yii\web\Response;

/**
 * เวรของฉัน — ดูเวรที่ประกาศแล้ว และยื่นคำขอหยุด/ขออยู่ล่วงหน้า
 *
 * เจ้าหน้าที่ยื่นคำขอได้ตั้งแต่ก่อนหัวหน้าเปิดรอบ (period_id เป็น NULL ไว้ก่อน)
 * พอหัวหน้าสร้างรอบของเดือนนั้น คำขอจะถูกผูกเข้ารอบให้อัตโนมัติ
 */
class RosterController extends Controller
{
    private ?Employees $me = null;

    /**
     * เส้นทางนี้อยู่ใน allowActions ระดับแอป (เจ้าหน้าที่ทุกคนเข้าดูเวรตัวเองได้
     * ไม่ผูกกับ role) จึงต้องบังคับล็อกอินเองที่นี่ — ทุก action ตรวจ emp_id ของผู้ใช้อีกชั้น
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'authOnly' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
        ]);
    }

    private function employee(): ?Employees
    {
        if ($this->me === null) {
            try {
                $emp = UserHelper::GetEmployee();
                $this->me = $emp instanceof Employees ? $emp : null;
            } catch (\Throwable $e) {
                $this->me = null;
            }
        }
        return $this->me;
    }

    public function actionIndex($month = null, $year = null)
    {
        $emp = $this->employee();
        if (!$emp) {
            return $this->render('no-profile');
        }

        $month = (int) ($month ?: date('n'));
        $year = (int) ($year ?: date('Y'));
        $firstDate = sprintf('%04d-%02d-01', $year, $month);
        $lastDate = date('Y-m-t', strtotime($firstDate));

        // เห็นเวรของตัวเองเฉพาะรอบที่ประกาศแล้ว — ระหว่างหัวหน้าจัดยังไม่ควรเห็น
        $items = Item::find()
            ->alias('i')
            ->innerJoin(['p' => Period::tableName()], 'p.id = i.period_id')
            ->where(['i.emp_id' => $emp->id])
            ->andWhere(['<>', 'i.status', Item::STATUS_CANCELLED])
            ->andWhere(['p.status' => [Period::STATUS_PUBLISHED, Period::STATUS_CLOSED]])
            ->andWhere(['between', 'i.work_date', $firstDate, $lastDate])
            ->orderBy(['i.work_date' => SORT_ASC])
            ->all();

        $byDay = [];
        foreach ($items as $item) {
            $byDay[(int) date('j', strtotime($item->work_date))][] = $item;
        }

        $requests = RosterRequest::find()
            ->where(['emp_id' => $emp->id])
            ->andWhere(['between', 'work_date', $firstDate, $lastDate])
            ->orderBy(['work_date' => SORT_ASC])
            ->all();
        $reqByDay = [];
        foreach ($requests as $request) {
            $reqByDay[(int) date('j', strtotime($request->work_date))][] = $request;
        }

        $published = Period::find()
            ->where(['unit_id' => $emp->department, 'year_ce' => $year, 'month' => $month, 'deleted_at' => null])
            ->one();

        return $this->render('index', [
            'employee' => $emp,
            'month' => $month,
            'year' => $year,
            'byDay' => $byDay,
            'reqByDay' => $reqByDay,
            'period' => $published,
            'unitShifts' => UnitShift::mapForUnit((int) $emp->department),
            'types' => ShiftType::activeList(),
            'totalShifts' => count($items),
            'incomingSwaps' => RosterSwapService::pendingForEmployee((int) $emp->id),
            'mySwaps' => Swap::find()
                ->where(['requested_by' => $emp->id])
                ->andWhere(['status' => [Swap::STATUS_PENDING, Swap::STATUS_ACCEPTED]])
                ->orderBy(['created_at' => SORT_DESC])
                ->all(),
        ]);
    }

    /** ฟอร์มขอแลก/ยกเวรให้ — เลือกเพื่อนในหน่วยเดียวกัน */
    public function actionSwapForm($item_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $emp = $this->employee();
        $item = Item::findOne((int) $item_id);
        if (!$emp || !$item || (int) $item->emp_id !== (int) $emp->id) {
            return ['status' => 'error', 'message' => 'ไม่พบเวรของคุณ'];
        }
        $period = $item->period;
        if (!$period || !$period->allowsSwap()) {
            return ['status' => 'error', 'message' => 'รอบเวรนี้ยังไม่ประกาศ หรือปิดรอบแล้ว'];
        }

        $colleagues = Employees::find()
            ->select(['id', 'prefix', 'fname', 'lname'])
            ->where(['department' => $emp->department, 'status' => 1])
            ->andWhere(['<>', 'id', $emp->id])
            ->orderBy(['fname' => SORT_ASC])
            ->asArray()->all();

        // เวรของเพื่อนในรอบเดียวกัน สำหรับเลือกแลกสองทาง
        $theirItems = Item::find()
            ->where(['period_id' => $period->id])
            ->andWhere(['<>', 'emp_id', $emp->id])
            ->andWhere(['>=', 'work_date', date('Y-m-d')])
            ->andWhere(['<>', 'status', Item::STATUS_CANCELLED])
            ->orderBy(['work_date' => SORT_ASC])
            ->all();

        return [
            'title' => 'ขอแลกเวร',
            'content' => $this->renderAjax('_swap_form', [
                'item' => $item,
                'period' => $period,
                'colleagues' => $colleagues,
                'theirItems' => $theirItems,
            ]),
            'footer' => ModalHelper::modalFooterSaveClose(),
        ];
    }

    /** ยื่นใบขอแลก/ยกเวรให้ */
    public function actionSwapRequest()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $emp = $this->employee();
        $item = Item::findOne((int) $this->request->post('item_id'));
        if (!$emp || !$item || (int) $item->emp_id !== (int) $emp->id) {
            return ['status' => 'error', 'message' => 'ไม่พบเวรของคุณ'];
        }
        $type = (string) $this->request->post('type');
        if (!in_array($type, [Swap::TYPE_SWAP, Swap::TYPE_GIVE], true)) {
            return ['status' => 'error', 'message' => 'ชนิดคำขอไม่ถูกต้อง'];
        }

        try {
            RosterSwapService::request(
                $item,
                (int) $this->request->post('to_emp_id'),
                $type,
                (string) $this->request->post('reason') ?: null,
                $this->request->post('counter_item_id') ? (int) $this->request->post('counter_item_id') : null
            );
        } catch (\RuntimeException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
        return ['status' => 'success', 'message' => 'ยื่นคำขอแล้ว รอคู่กรณีตอบรับ'];
    }

    /** ตอบรับ/ปฏิเสธคำขอที่คนอื่นขอแลกกับเรา */
    public function actionSwapRespond()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $emp = $this->employee();
        $swap = Swap::findOne((int) $this->request->post('swap_id'));
        if (!$emp || !$swap) {
            return ['status' => 'error', 'message' => 'ไม่พบคำขอ'];
        }
        try {
            if ($this->request->post('decision') === 'accept') {
                RosterSwapService::accept($swap, (int) $emp->id);
                return ['status' => 'success', 'message' => 'รับคำขอแล้ว รอหัวหน้าอนุมัติ'];
            }
            RosterSwapService::reject($swap, (int) $emp->id);
            return ['status' => 'success', 'message' => 'ปฏิเสธคำขอแล้ว'];
        } catch (\RuntimeException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /** ยกเลิกคำขอที่ตัวเองยื่น */
    public function actionSwapCancel()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $emp = $this->employee();
        $swap = Swap::findOne((int) $this->request->post('swap_id'));
        if (!$emp || !$swap) {
            return ['status' => 'error', 'message' => 'ไม่พบคำขอ'];
        }
        try {
            RosterSwapService::cancel($swap, (int) $emp->id);
        } catch (\RuntimeException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
        return ['status' => 'success', 'message' => 'ยกเลิกคำขอแล้ว'];
    }

    /** ยื่น/ยกเลิกคำขอหยุด-ขออยู่ ของวันหนึ่ง */
    public function actionRequest()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $emp = $this->employee();
        if (!$emp) {
            return ['status' => 'error', 'message' => 'ไม่พบข้อมูลพนักงานของคุณ'];
        }

        $workDate = (string) $this->request->post('work_date');
        $type = (string) $this->request->post('type');
        if (!$workDate || !in_array($type, [RosterRequest::TYPE_OFF, RosterRequest::TYPE_ON], true)) {
            return ['status' => 'error', 'message' => 'ข้อมูลไม่ถูกต้อง'];
        }
        if ($workDate < date('Y-m-d')) {
            return ['status' => 'error', 'message' => 'ยื่นคำขอย้อนหลังไม่ได้'];
        }

        $existing = RosterRequest::findOne(['emp_id' => $emp->id, 'work_date' => $workDate, 'type' => $type]);
        if ($existing) {
            if ($existing->status !== RosterRequest::STATUS_PENDING) {
                return ['status' => 'error', 'message' => 'หัวหน้าพิจารณาคำขอนี้แล้ว ยกเลิกเองไม่ได้'];
            }
            $existing->delete();
            return ['status' => 'success', 'action' => 'removed'];
        }

        // ถ้ารอบของเดือนนั้นเลยขั้นร่างไปแล้ว หัวหน้ากำลังจัด/จัดเสร็จ — ไม่ควรรับคำขอใหม่เงียบๆ
        $period = Period::findOne([
            'unit_id' => $emp->department,
            'year_ce' => (int) date('Y', strtotime($workDate)),
            'month' => (int) date('n', strtotime($workDate)),
            'deleted_at' => null,
        ]);
        if ($period && !in_array($period->status, [Period::STATUS_DRAFT], true)) {
            return ['status' => 'error', 'message' => 'ตารางเวรเดือนนี้' . $period->getStatusLabel() . 'แล้ว กรุณาแจ้งหัวหน้าโดยตรง'];
        }

        $model = new RosterRequest([
            'period_id' => $period ? $period->id : null,
            'unit_id' => (int) $emp->department,
            'emp_id' => (int) $emp->id,
            'work_date' => $workDate,
            'type' => $type,
            'reason' => (string) $this->request->post('reason') ?: null,
        ]);
        if (!$model->save()) {
            $first = array_values($model->getFirstErrors());
            return ['status' => 'error', 'message' => $first[0] ?? 'บันทึกไม่สำเร็จ'];
        }
        return ['status' => 'success', 'action' => 'added', 'id' => $model->id];
    }
}
