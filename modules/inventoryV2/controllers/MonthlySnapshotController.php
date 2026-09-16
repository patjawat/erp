<?php

namespace app\modules\inventoryV2\controllers;

use app\modules\inventoryV2\models\Warehouse;
use app\modules\inventoryV2\services\MonthlySnapshotReconciliationService;
use app\modules\inventoryV2\services\MonthlySnapshotRestoreService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\UploadedFile;

/** Review, certify and reverse monthly ending balances with the same all-warehouse access checks. */
class MonthlySnapshotController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [
                ['allow' => true, 'roles' => ['inventoryStockRepair']],
            ]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['index' => ['GET', 'POST'], 'draft'=>['GET','POST'], 'commit'=>['POST'], 'revert'=>['POST']]],
        ]);
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) return false;
        $all = Warehouse::find()->select('id')->where(['warehouse_type' => 'MAIN'])->column();
        $allowed = array_map(static fn($w) => $w->id, Warehouse::findMainWarehousesForReceive());
        if (array_diff($all, $allowed)) throw new ForbiddenHttpException('ไฟล์รวมทุกคลังต้องตรวจโดยผู้มีสิทธิ์เข้าถึงทุกคลังหลัก');
        return true;
    }

    public function actionIndex()
    {
        $year = (int) $this->request->get('year', date('Y'));
        $month = (int) $this->request->get('month', date('n'));
        $result = null; $error = null; $filename = null;
        if ($this->request->isPost) {
            $year = (int) $this->request->post('year');
            $month = (int) $this->request->post('month');
            try {
                $file = UploadedFile::getInstanceByName('workbook');
                if (!$file || $file->error !== UPLOAD_ERR_OK || strtolower($file->extension) !== 'xlsx') {
                    throw new \InvalidArgumentException('เลือกไฟล์ Excel .xlsx ที่อัปโหลดได้ครบ');
                }
                $filename = $file->name;
                if ($this->request->post('prepare') === '1') {
                    $id = MonthlySnapshotRestoreService::createDraft($file,$year,$month,(int)Yii::$app->user->id);
                    return $this->redirect(['draft','id'=>$id]);
                }
                $result = MonthlySnapshotReconciliationService::inspectDatabase(
                    MonthlySnapshotReconciliationService::readFile($file->tempName, $year, $month)
                );
            } catch (\InvalidArgumentException | \DomainException $e) {
                $error = $e->getMessage();
            } catch (\Throwable $e) {
                Yii::error($e, __METHOD__);
                $error = 'ตรวจไฟล์ไม่สำเร็จ กรุณาตรวจรูปแบบไฟล์หรือให้ผู้ดูแลตรวจบันทึกข้อผิดพลาด';
            }
        }
        return $this->render('index', compact('year', 'month', 'result', 'error', 'filename'));
    }

    public function actionDraft(int $id)
    {
        $error = null; $plan = null;
        try {
            $draft = MonthlySnapshotRestoreService::get($id);
            if ($this->request->isPost) {
                $plan = MonthlySnapshotRestoreService::preview($id,(array)$this->request->post('rows',[]),
                    $this->request->post('clear_later')==='1',(string)$this->request->post('reason'),(int)Yii::$app->user->id);
                $draft = MonthlySnapshotRestoreService::get($id);
            }
            $result = $draft['status']==='draft' ? MonthlySnapshotRestoreService::inspect($id) : null;
            if ($result && $plan === null) $plan = MonthlySnapshotRestoreService::review($id);
        } catch (\DomainException | \InvalidArgumentException $e) {
            $error = $e->getMessage();
            $draft = MonthlySnapshotRestoreService::get($id);
            $result = null;
        }
        $events = (new \yii\db\Query())->select(['action','created_at','created_by','reason'])->from('stock_monthly_restore_event')->where(['restore_id'=>$id])->orderBy(['id'=>SORT_ASC])->all();
        return $this->render('draft',compact('draft','result','plan','error','events'));
    }

    public function actionCommit(int $id)
    {
        try {
            if ($this->request->post('confirmed')!=='1') throw new \DomainException('ต้องยืนยันยอดรับรองก่อนบันทึก');
            MonthlySnapshotRestoreService::commit($id,(string)$this->request->post('hash'),(int)Yii::$app->user->id);
            Yii::$app->session->setFlash('success','คืนยอดยกไปตาม Excel และล็อกงวดแล้ว');
        } catch (\DomainException $e) { Yii::$app->session->setFlash('error',$e->getMessage()); }
        catch (\Throwable $e) { Yii::error($e,__METHOD__); Yii::$app->session->setFlash('error','คืนยอดไม่สำเร็จ ระบบยกเลิกการบันทึกทั้งชุดแล้ว กรุณาตรวจข้อมูลและลองใหม่'); }
        return $this->redirect(['draft','id'=>$id]);
    }

    public function actionRevert(int $id)
    {
        try {
            MonthlySnapshotRestoreService::revert($id,(string)$this->request->post('reason'),(int)Yii::$app->user->id);
            Yii::$app->session->setFlash('success','ย้อนคืนชุดข้อมูลก่อนนำเข้าและบันทึกประวัติแล้ว');
        } catch (\DomainException $e) { Yii::$app->session->setFlash('error',$e->getMessage()); }
        catch (\Throwable $e) { Yii::error($e,__METHOD__); Yii::$app->session->setFlash('error','ย้อนคืนไม่สำเร็จ ระบบยกเลิกการบันทึกทั้งชุดแล้ว กรุณาให้ผู้ดูแลตรวจบันทึกข้อผิดพลาด'); }
        return $this->redirect(['draft','id'=>$id]);
    }
}
