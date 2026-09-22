<?php

namespace app\modules\finance\controllers;

use app\modules\finance\models\RegisterCatalog;
use app\modules\finance\services\FinanceRegisterService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * ทะเบียนคุมงานการเงิน — hub + register layer (เฟส 0)
 *
 * actionIndex  : หน้า hub รวมทะเบียนคุมทั้ง 18 เล่ม 5 หมวด
 * actionView   : หน้าทะเบียนรายเล่ม (ฉายข้อมูลจริงถ้าต่อ builder แล้ว มิฉะนั้นโครง placeholder)
 * actionExport : ส่งออก Excel รูปแบบเดียวกับหน้าจอ
 */
class RegisterController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['financeView']]],
            ],
        ]);
    }

    public function actionIndex()
    {
        return $this->render('index', [
            'categories' => RegisterCatalog::categories(),
            'registers' => RegisterCatalog::registers(),
        ]);
    }

    public function actionView(string $key)
    {
        $register = $this->findRegister($key);
        $filters = $this->filtersFromRequest();

        return $this->render('view', [
            'register' => $register,
            'categories' => RegisterCatalog::categories(),
            'data' => FinanceRegisterService::build($key, $filters),
            'filters' => $filters,
            'fiscalYears' => FinanceRegisterService::availableFiscalYears(),
        ]);
    }

    public function actionExport(string $key)
    {
        $register = $this->findRegister($key);
        $book = FinanceRegisterService::spreadsheet($key, $this->filtersFromRequest());
        if ($book === null) {
            Yii::$app->session->setFlash('warning', 'ทะเบียนนี้ยังไม่พร้อมส่งออก');
            return $this->redirect(['view', 'key' => $key]);
        }

        $path = Yii::getAlias('@runtime') . '/register_' . $key . '_' . date('YmdHis') . '.xlsx';
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save($path);
        $fileName = 'ทะเบียนคุม_' . $register['label'] . '.xlsx';

        return Yii::$app->response
            ->sendFile($path, $fileName, ['mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
            ->on(Response::EVENT_AFTER_SEND, function () use ($path) {
                @unlink($path);
            });
    }

    private function findRegister(string $key): array
    {
        $register = RegisterCatalog::find($key);
        if ($register === null) {
            throw new NotFoundHttpException('ไม่พบทะเบียนคุมที่ระบุ');
        }
        return $register;
    }

    /** @return array{fiscal_year:?int,month:?int,vendor_id:?int} */
    private function filtersFromRequest(): array
    {
        $req = Yii::$app->request;
        $month = (int) $req->get('month', 0);
        return [
            'fiscal_year' => (int) $req->get('fiscal_year', 0) ?: null,
            'month' => ($month >= 1 && $month <= 12) ? $month : null,
            'vendor_id' => (int) $req->get('vendor_id', 0) ?: null,
            'wht_type' => $req->get('wht_type') ?: null,
            'status' => $req->get('status') ?: null,
            'fund_id' => (int) $req->get('fund_id', 0) ?: null,
        ];
    }
}
