<?php
namespace tests\unit\modules\inventoryV2;

use app\modules\inventoryV2\services\MonthlySnapshotRestoreService as Restore;
use app\modules\inventoryV2\services\MonthlyPeriodProtection as Protection;
use app\modules\inventoryV2\controllers\ReportController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPUnit\Framework\TestCase;
use Yii;

/** Isolated SQLite plus a temporary retained workbook; never loads config/db.php. */
class MonthlySnapshotRestoreServiceTest extends TestCase
{
    private $tmp;
    private $repo;
    private $previousApp;
    private $file;

    protected function setUp(): void
    {
        $this->repo=dirname(__DIR__,4);
        $this->previousApp=Yii::$app;
        $this->tmp=sys_get_temp_dir().'/erp-monthly-test-'.bin2hex(random_bytes(8));
        mkdir($this->tmp,0700,true);
        new \yii\console\Application(['id'=>'monthly-test','basePath'=>$this->repo,'runtimePath'=>$this->tmp.'/runtime',
            'components'=>['db'=>['class'=>\yii\db\Connection::class,'dsn'=>'sqlite::memory:']]]);
        Yii::setAlias('@app',$this->tmp);
        Yii::setAlias('@app/modules',$this->repo.'/modules');
        $db=Yii::$app->db;
        $db->createCommand('CREATE TABLE warehouses (id INTEGER PRIMARY KEY, warehouse_name TEXT, warehouse_type TEXT)')->execute();
        $db->createCommand("INSERT INTO warehouses VALUES (1,'คลังทดสอบ','MAIN')")->execute();
        $db->createCommand('CREATE TABLE categorise (id INTEGER PRIMARY KEY, code TEXT, title TEXT, name TEXT, group_id TEXT, category_id TEXT)')->execute();
        $db->createCommand("INSERT INTO categorise VALUES (1,'A','วัสดุ A','asset_item','MATER','01')")->execute();
        $db->createCommand("INSERT INTO categorise VALUES (2,'01','วัสดุสำนักงาน','asset_type',NULL,'4')")->execute();
        $db->createCommand('CREATE TABLE stock_item_warehouse_setting (item_code TEXT, warehouse_id INTEGER)')->execute();
        $db->createCommand('CREATE TABLE stock_monthly_report (id INTEGER PRIMARY KEY, report_year INTEGER, report_month INTEGER, warehouse_id INTEGER, item_code TEXT, unit_name TEXT, closing_qty NUMERIC, closing_value NUMERIC, created_at TEXT)')->execute();
        $db->createCommand("INSERT INTO stock_monthly_report VALUES (1,2026,7,1,'A','กล่อง',3,3300,'2026-08-01'),(2,2026,8,1,'A','กล่อง',4,4400,'2026-09-01')")->execute();
        $db->createCommand('CREATE TABLE uploads (id INTEGER PRIMARY KEY, ref TEXT, name TEXT, real_filename TEXT)')->execute();
        // Sentinels prove report restoration does not rewrite physical stock or source documents.
        foreach (['stock_balance','stock_order','stock_detail'] as $t) {
            $extra=$t==='stock_order' ? ", order_date TEXT DEFAULT '2026-01-01', main_warehouse_id INTEGER DEFAULT 1" : '';
            $db->createCommand("CREATE TABLE $t (id INTEGER PRIMARY KEY, marker TEXT $extra)")->execute();
            $db->createCommand("INSERT INTO $t (id,marker) VALUES (1,'unchanged')")->execute();
        }
        require_once $this->repo.'/migrations/m260908_090000_create_monthly_restore.php';
        $m=new \m260908_090000_create_monthly_restore(['db'=>$db,'compact'=>true]);
        ob_start(); try { $m->safeUp(); } finally { ob_end_clean(); }
        $dir=$this->tmp.'/modules/filemanager/fileupload/fixture'; mkdir($dir,0700,true);
        $this->file=$dir.'/source.xlsx';
        $w=new Spreadsheet(); $s=$w->getActiveSheet()->setTitle('สรุปวัสดุคงคลัง');
        $s->setCellValue('A2','เดือน กรกฎาคม 2569')->setCellValue('B6','วัสดุสำนักงาน')->setCellValue('I6',3300)->setCellValue('B7','รวม')->setCellValue('I7',3300);
        $d=$w->createSheet()->setTitle('สรุปรายการ');
        $d->fromArray([['ที่','รหัส','รายการสินค้า','ประเภท','หน่วย','จำนวนคงเหลือ','มูลค่าคงเหลือ','จำนวนรับใหม่','มูลค่ารับใหม่','จำนวนจ่ายใหม่','มูลค่าจ่ายใหม่','จำนวนคงเหลือ','มูลค่าคงเหลือ'],
            [1,'A','วัสดุ A','วัสดุสำนักงาน','กล่อง',3,3300,0,0,0,0,1,3300]],null,'A2',true);
        (new Xlsx($w))->save($this->file); $w->disconnectWorksheets();
        $db->createCommand("INSERT INTO uploads VALUES (1,'fixture','monthly-certified-source','source.xlsx')")->execute();
        $db->createCommand()->insert('stock_monthly_restore',[
            'id'=>1,'report_year'=>2026,'report_month'=>7,'file_sha256'=>hash_file('sha256',$this->file),'upload_id'=>1,
            'status'=>'draft','source_json'=>json_encode(\app\modules\inventoryV2\services\MonthlySnapshotReconciliationService::readFile($this->file,2026,7)),
            'ref'=>'fixture','created_at'=>'2026-09-08','updated_at'=>'2026-09-08','created_by'=>1,'updated_by'=>1,
        ])->execute();
    }

    protected function tearDown(): void
    {
        Yii::$app->db->close();
        Yii::$app=$this->previousApp;
        Yii::setAlias('@app',$this->repo);
        \yii\helpers\FileHelper::removeDirectory($this->tmp);
    }

    private function preview(): array { return Restore::preview(1,[],true,'ใช้จำนวนตาม Excel ที่ส่งบัญชี',1); }
    private function rows(): array { return Yii::$app->db->createCommand('SELECT * FROM stock_monthly_report ORDER BY id')->queryAll(); }

    public function testCommitUsesLiteralQuantityClearsLaterLocksAndIsIdempotent(): void
    {
        $p=$this->preview(); self::assertSame([],$p['errors']);
        Restore::commit(1,$p['hash'],1);
        self::assertCount(1,$this->rows());
        self::assertEquals(1,$this->rows()[0]['closing_qty'],'Must keep source quantity 1, not equation result 3');
        self::assertEquals(3300,$this->rows()[0]['closing_value']);
        Restore::commit(1,$p['hash'],1);
        self::assertEquals(1,(new \yii\db\Query())->from('stock_monthly_restore_event')->count());
        self::assertEquals(1,(new \yii\db\Query())->from('stock_monthly_period_lock')->count());
        foreach (['stock_balance','stock_order','stock_detail'] as $t) self::assertSame('unchanged',Yii::$app->db->createCommand("SELECT marker FROM $t")->queryScalar());
        try { Protection::assertWritable([1],2026,8,'through'); self::fail('Autofix must not cross certified July'); }
        catch (\DomainException $e) { self::assertStringContainsString('ล็อกแล้ว',$e->getMessage()); }
        Protection::assertWritable([1],2026,8);
        $opening=ReportController::buildOpeningForMonth(1,2026,8);
        self::assertEquals(1,$opening['A']['closing_qty']);
        self::assertEquals(3300,$opening['A']['closing_value']);
    }

    public function testLaterSnapshotsNeedExplicitConsent(): void
    {
        $p=Restore::preview(1,[],false,'ใช้จำนวนตาม Excel ที่ส่งบัญชี',1);
        self::assertNotEmpty($p['errors']); self::assertNull(Restore::get(1)['preview_hash']);
        self::assertCount(2,$this->rows());
    }

    public function testStalePreviewCannotWrite(): void
    {
        $p=$this->preview();
        Yii::$app->db->createCommand()->update('stock_monthly_report',['closing_qty'=>9],['id'=>1])->execute();
        $this->expectException(\DomainException::class);
        Restore::commit(1,$p['hash'],1);
    }

    public function testTamperedRetainedFileCannotWrite(): void
    {
        $p=$this->preview(); file_put_contents($this->file,'tampered');
        $this->expectException(\DomainException::class); Restore::commit(1,$p['hash'],1);
    }

    public function testAuditFailureRollsBackBalancesLaterRowsAndLocks(): void
    {
        $p=$this->preview(); $before=$this->rows();
        Yii::$app->db->pdo->exec("CREATE TRIGGER fail_audit BEFORE INSERT ON stock_monthly_restore_event BEGIN SELECT RAISE(ABORT,'audit failure'); END");
        try { Restore::commit(1,$p['hash'],1); self::fail('Expected failure'); }
        catch (\yii\db\Exception $e) { self::assertSame($before,$this->rows()); }
        self::assertSame('draft',Restore::get(1)['status']);
        self::assertEquals(0,(new \yii\db\Query())->from('stock_monthly_period_lock')->count());
    }

    public function testRevertRestoresOriginalRowsAndAppendsAudit(): void
    {
        $before=$this->rows(); $p=$this->preview(); Restore::commit(1,$p['hash'],1);
        Restore::revert(1,'ย้อนคืนข้อมูลทดสอบก่อนนำเข้า',1);
        self::assertSame($before,$this->rows()); self::assertSame('reverted',Restore::get(1)['status']);
        self::assertEquals(2,(new \yii\db\Query())->from('stock_monthly_restore_event')->count());
        self::assertEquals(0,(new \yii\db\Query())->from('stock_monthly_period_lock')->count());
    }

    public function testRevertRefusesToClobberSubsequentWork(): void
    {
        $p=$this->preview(); Restore::commit(1,$p['hash'],1);
        Yii::$app->db->createCommand("INSERT INTO stock_monthly_report VALUES (9,2026,8,1,'A','กล่อง',1,3300,'later')")->execute();
        $this->expectException(\DomainException::class); Restore::revert(1,'ย้อนคืนข้อมูลทดสอบก่อนนำเข้า',1);
    }

    public function testBothConsoleClosingEntryPointsRefuseCertifiedPeriod(): void
    {
        $p=$this->preview(); Restore::commit(1,$p['hash'],1);
        foreach (['closeMonthForWarehouse','closeMonthFromStart'] as $method) {
            try { ReportController::$method(1,2026,7); self::fail('Must reject locked month'); }
            catch (\DomainException $e) { self::assertStringContainsString('ล็อกแล้ว',$e->getMessage()); }
        }
        self::assertEquals(1,$this->rows()[0]['closing_qty']);
    }

    public function testWebMutationsAreStoppedBeforeSideEffects(): void
    {
        $p=$this->preview(); Restore::commit(1,$p['hash'],1);
        $oldMethod=$_SERVER['REQUEST_METHOD'] ?? null;
        $_SERVER['REQUEST_METHOD']='POST';
        $request=new \yii\web\Request(['enableCsrfValidation'=>false]); // Isolated test request, never deployed.
        Yii::$app->set('request',$request);
        Yii::$app->set('response',new \yii\web\Response());
        try {
            foreach ([['close-month',7],['cancel-close',7],['close-month-autofix',7],['close-month-autofix',6],['set-period-closing',6]] as [$action,$month]) {
                $request->setBodyParams(['year'=>2026,'month'=>$month,'warehouse_id'=>1]);
                $controller=new ReportController('report',Yii::$app);
                self::assertFalse($controller->beforeAction($controller->createAction($action)));
                self::assertFalse(Yii::$app->response->data['success']);
                self::assertStringContainsString('ล็อกแล้ว',Yii::$app->response->data['message']);
            }
            self::assertEquals(1,$this->rows()[0]['closing_qty']);
            self::assertSame('unchanged',Yii::$app->db->createCommand('SELECT marker FROM stock_order')->queryScalar());
            $request->setBodyParams(['year'=>2026,'month'=>8,'warehouse_id'=>1]);
            $controller=new ReportController('report',Yii::$app);
            $action=$controller->createAction('close-month-autofix');
            try { self::assertTrue($controller->beforeAction($action)); }
            finally { $controller->afterAction($action,[]); }
        } finally {
            if ($oldMethod===null) unset($_SERVER['REQUEST_METHOD']); else $_SERVER['REQUEST_METHOD']=$oldMethod;
        }
    }

    public function testDuplicateMappingsAndSkippingNonzeroRowsAreRejected(): void
    {
        $source=Restore::inspect(1);
        $source['rows'][]=array_merge($source['rows'][0],['row'=>4]);
        $source['duplicates']=['A'=>[3,4]];
        $plan=Restore::plan($source,[3=>['warehouse_id'=>1],4=>['warehouse_id'=>1]],$this->rows(),[['code'=>'A','category_title'=>'วัสดุสำนักงาน']],true);
        self::assertStringContainsString('จับคู่ซ้ำ',implode(' ',$plan['errors']));
        $plan=Restore::plan(Restore::inspect(1),[3=>['skip'=>1,'note'=>'ทดลองข้ามรายการ']],$this->rows(),[['code'=>'A','category_title'=>'วัสดุสำนักงาน']],true);
        self::assertStringContainsString('ยกเว้นได้เฉพาะแถวศูนย์',implode(' ',$plan['errors']));
        $plan=Restore::plan(Restore::inspect(1),[],$this->rows(),[['code'=>'A','category_title'=>'วัสดุอื่น']],true);
        self::assertStringContainsString('ประเภทวัสดุปลายทางไม่ตรง Excel',implode(' ',$plan['errors']));
    }

    public function testDraftViewEscapesFileTextAndOnlyOffersCommitForValidPreview(): void
    {
        $plan=$this->preview(); $draft=Restore::get(1); $result=Restore::inspect(1);
        $result['rows'][0]['item_name']='<script>alert(1)</script>';
        Yii::$app->set('request',new \yii\web\Request(['cookieValidationKey'=>'isolated-test','scriptUrl'=>'/index.php','baseUrl'=>'','hostInfo'=>'http://localhost']));
        Yii::$app->set('response',new \yii\web\Response());
        Yii::$app->set('urlManager',new \yii\web\UrlManager(['scriptUrl'=>'/index.php','baseUrl'=>'','hostInfo'=>'http://localhost']));
        Yii::$app->set('user',new class extends \yii\base\Component { public function can($p) { return true; } });
        Yii::$app->set('session',new class extends \yii\base\Component { public function hasFlash($k) { return false; } });
        Yii::$app->controller=new \yii\web\Controller('monthly-snapshot',Yii::$app);
        $render=function($plan) use($draft,$result) {
            return Yii::$app->view->renderFile($this->repo.'/modules/inventoryV2/views/monthly-snapshot/draft.php',[
                'draft'=>$draft,'result'=>$result,'plan'=>$plan,'error'=>null,'events'=>[],
            ]);
        };
        $html=$render($plan);
        self::assertStringNotContainsString('<script>alert(1)</script>',$html);
        self::assertStringContainsString('&lt;script&gt;',$html);
        self::assertStringContainsString('name="hash"',$html);
        self::assertStringContainsString('name="_csrf"',$html);
        $plan['errors']=['ต้องเลือกคลัง'];
        self::assertStringNotContainsString('name="hash"',$render($plan));
        $plan['errors']=['แถว 3: ต้องเลือกคลังปลายทาง'];
        self::assertStringContainsString('href="#restore-row-3"',$render($plan));
        self::assertStringContainsString('id="restore-row-3"',$render($plan));
    }

    public function testReadOnlyReviewPreservesDraftAndCannotAuthorizeCommit(): void
    {
        $before=Restore::get(1); $snapshots=$this->rows();
        $plan=Restore::review(1);
        self::assertArrayNotHasKey('hash',$plan);
        self::assertSame($before,Restore::get(1));
        self::assertSame($snapshots,$this->rows());
    }

    public function testHistoricalWarehouseWinsOverCurrentSettingsWithoutGuessingDuplicates(): void
    {
        $row=['item_code'=>'A','candidates'=>[1=>'Lab',7=>'Supplies'],'current_snapshots'=>[['warehouse_id'=>1]]];
        self::assertSame(1,Restore::defaultWarehouse($row,[]));
        self::assertSame(0,Restore::defaultWarehouse($row,['A'=>[3,4]]));
        $row['current_snapshots'][]=['warehouse_id'=>7];
        self::assertSame(0,Restore::defaultWarehouse($row,[]));
        $row['candidates']=[1=>'Lab'];
        self::assertSame(0,Restore::defaultWarehouse($row,[]));
    }

    public function testMissingItemCanBeInsertedAndRevertedExactly(): void
    {
        Yii::$app->db->createCommand()->delete('stock_monthly_report',['report_month'=>7])->execute();
        $before=$this->rows();
        $plan=Restore::preview(1,[3=>['warehouse_id'=>1]],true,'คืนยอดตามไฟล์รับรองทดสอบ',1);
        self::assertSame([],$plan['errors']);
        self::assertTrue($plan['targets'][0]['is_new']);
        Restore::commit(1,$plan['hash'],1);
        self::assertEquals(3300,$this->rows()[0]['closing_value']);
        Restore::revert(1,'ย้อนคืนรายการที่สร้างจากไฟล์',1);
        self::assertSame($before,$this->rows());
    }

    public function testMissingWarehouseDoesNotDuplicateAnExistingItem(): void
    {
        $source=Restore::inspect(1);
        $source['warehouses'][7]='คลังพัสดุ';
        $plan=Restore::plan($source,[3=>['warehouse_id'=>7]],$this->rows(),[['code'=>'A','category_title'=>'วัสดุสำนักงาน']],true);
        self::assertSame([],$plan['targets']);
        self::assertStringContainsString('พบยอดเดิมในคลังอื่น',implode(' ',$plan['errors']));
    }

    public function testRepairStartsAfterCertificationEvenWithoutSourceOrders(): void
    {
        $p=$this->preview(); Restore::commit(1,$p['hash'],1);
        Yii::$app->db->createCommand()->delete('stock_order')->execute();
        [$year,$month,$opening]=ReportController::repairStart(1,2026,8);
        self::assertSame([2026,8],[$year,$month]);
        self::assertEquals(1,$opening['A']['closing_qty']);
        self::assertEquals(3300,ReportController::buildOpeningForMonth(1,2026,8)['A']['closing_value']);
        self::assertSame(0,ReportController::diagnoseCloseMonth(1,2026,7)['months']);
    }

    public function testZeroCostPlanOnlyIncludesOpenPeriodReceipts(): void
    {
        $p=$this->preview(); Restore::commit(1,$p['hash'],1);
        $db=Yii::$app->db;
        foreach (['status TEXT','order_type TEXT'] as $column) $db->createCommand('ALTER TABLE stock_order ADD COLUMN '.$column)->execute();
        foreach (['stock_order_id INTEGER','item_code TEXT','qty NUMERIC','unit_price NUMERIC'] as $column) $db->createCommand('ALTER TABLE stock_detail ADD COLUMN '.$column)->execute();
        $order=\app\modules\inventoryV2\models\StockOrder::class;
        foreach ([7,8,9] as $month) {
            $db->createCommand()->insert('stock_order',['id'=>$month,'order_date'=>"2026-0$month-10 00:00:00",'main_warehouse_id'=>1,'order_type'=>$order::ORDER_TYPE_IN,'status'=>$order::STATUS_CONFIRMED])->execute();
            $db->createCommand()->insert('stock_detail',['id'=>$month,'stock_order_id'=>$month,'item_code'=>'A','qty'=>1,'unit_price'=>0])->execute();
        }
        $db->createCommand()->insert('stock_detail',['id'=>10,'stock_order_id'=>7,'item_code'=>'A','qty'=>1,'unit_price'=>100])->execute();
        $plans=\app\modules\inventoryV2\components\CloseMonthAutofixService::planZeroCost(1,['A'],'2026-08-01 00:00:00','2026-09-01 00:00:00');
        self::assertSame([8],$plans[0]['detail_ids']);
    }
}
