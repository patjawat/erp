<?php
namespace app\modules\inventoryV2\services;

use app\modules\filemanager\components\FileManagerHelper;
use app\modules\filemanager\models\Uploads;
use app\modules\inventoryV2\models\StockItem;
use Yii;
use yii\db\Query;
use yii\web\UploadedFile;

/** Certified ending balances only. Receipt/issue splits and physical stock are never inferred or modified. */
class MonthlySnapshotRestoreService
{
    public static function ready(): bool
    {
        foreach (['stock_monthly_restore','stock_monthly_restore_event','stock_monthly_period_lock'] as $table) {
            if (Yii::$app->db->getTableSchema($table, true) === null) return false;
        }
        return true;
    }

    private static function audit(?int $user): array
    {
        return ['ref'=>Yii::$app->security->generateRandomString(32), 'created_at'=>date('Y-m-d H:i:s'),
            'updated_at'=>date('Y-m-d H:i:s'), 'created_by'=>$user, 'updated_by'=>$user];
    }

    public static function createDraft(UploadedFile $file, int $year, int $month, ?int $user): int
    {
        if (!self::ready()) throw new \DomainException('ต้องติดตั้ง migration ระบบคืนยอดก่อน');
        $source = MonthlySnapshotReconciliationService::readFile($file->tempName, $year, $month);
        if ($source['errors']) throw new \DomainException(implode(' / ', $source['errors']));
        $existing=(new Query())->select('id')->from('stock_monthly_restore')->where([
            'report_year'=>$year,'report_month'=>$month,'file_sha256'=>$source['sha256'],'status'=>['draft','committed'],
        ])->orderBy(['id'=>SORT_DESC])->scalar();
        if ($existing) return (int)$existing;
        $meta = self::audit($user);
        $upload = FileManagerHelper::saveUploadedFile($file, $meta['ref'], 'monthly-certified-source');
        if (!$upload) throw new \RuntimeException('เก็บไฟล์ต้นฉบับไม่สำเร็จ');
        // Filemanager owns the retained source, including failed-draft evidence. Never publish a raw file path.
        Yii::$app->db->createCommand()->insert('stock_monthly_restore', array_merge($meta, [
            'report_year'=>$year,'report_month'=>$month,'file_sha256'=>$source['sha256'],'upload_id'=>$upload->id,
            'source_json'=>self::json($source),'status'=>'draft',
        ]))->execute();
        return (int) Yii::$app->db->getLastInsertID();
    }

    public static function get(int $id): array
    {
        if (!self::ready()) throw new \DomainException('ยังไม่ได้ติดตั้งระบบคืนยอด');
        $r = (new Query())->from('stock_monthly_restore')->where(['id'=>$id])->one();
        if (!$r) throw new \DomainException('ไม่พบชุดคืนยอด');
        return $r;
    }

    private static function source(array $draft): array
    {
        $upload = Uploads::findOne(['id'=>$draft['upload_id'],'ref'=>$draft['ref'],'name'=>'monthly-certified-source']);
        $path = $upload ? FileManagerHelper::getFilePath($upload->id) : null;
        if (!$path || !hash_equals($draft['file_sha256'], hash_file('sha256', $path))) {
            throw new \DomainException('ไฟล์ต้นฉบับหายหรือเปลี่ยนแปลง ห้ามคืนยอด');
        }
        // Re-read the retained bytes, never trust posted balances or a modified draft JSON.
        return MonthlySnapshotReconciliationService::readFile($path, (int)$draft['report_year'], (int)$draft['report_month']);
    }

    public static function inspect(int $id): array
    {
        return MonthlySnapshotReconciliationService::inspectDatabase(self::source(self::get($id)));
    }

    /** Recheck saved choices without saving a preview or authorizing a commit. */
    public static function review(int $id): array
    {
        return MonthlyPeriodProtection::run(function () use ($id) {
            $draft = self::get($id);
            $choices = json_decode($draft['decisions_json'] ?: '{}', true);
            $plan = self::build($draft, $choices['rows'] ?? [], !empty($choices['clear_later']));
            // A GET must never create a valid confirmation token.
            unset($plan['hash']);
            return $plan;
        });
    }

    private static function state(int $year, int $month): array
    {
        return (new Query())->from('stock_monthly_report')->where(['>=',new \yii\db\Expression('report_year * 12 + report_month'),$year*12+$month])
            ->andWhere(['warehouse_id'=>(new Query())->select('id')->from('warehouses')->where(['warehouse_type'=>'MAIN'])])
            ->orderBy(['report_year'=>SORT_ASC,'report_month'=>SORT_ASC,'warehouse_id'=>SORT_ASC,'item_code'=>SORT_ASC,'id'=>SORT_ASC])->all();
    }

    /** Historical rows take precedence over present-day warehouse settings. */
    public static function defaultWarehouse(array $row, array $duplicates): int
    {
        if (isset($duplicates[$row['item_code']])) return 0;
        $history = $row['current_snapshots'] ?? [];
        if (count($history) === 1) return (int)$history[0]['warehouse_id'];
        if ($history) return 0;
        return count($row['candidates'] ?? []) === 1 ? (int)array_key_first($row['candidates']) : 0;
    }

    /** Pure plan; mappings are row-based and cannot merge duplicate codes or invent missing quantities. */
    public static function plan(array $source, array $decisions, array $snapshots, array $catalog, bool $clearLater): array
    {
        if ($source['errors']) throw new \DomainException(implode(' / ', $source['errors']));
        $materials = []; foreach ($catalog as $item) $materials[$item['code']][] = $item;
        $current = []; $later = [];
        foreach ($snapshots as $s) {
            if ((int)$s['report_year'] === $source['year'] && (int)$s['report_month'] === $source['month']) {
                $current[$s['warehouse_id'].'|'.$s['item_code']][] = $s;
            } else $later[] = $s;
        }
        $targets = []; $skipped = []; $warehouses = []; $errors = [];
        foreach ($source['rows'] as $row) {
            $n = $row['row']; $d = $decisions[$n] ?? [];
            if (!empty($d['skip'])) {
                $zero = true;
                foreach (['opening_qty','opening_value','in_qty','in_value','out_qty','out_value','closing_qty','closing_value'] as $f) {
                    if (abs($row[$f]) > 0.000001) $zero = false;
                }
                if (!$zero || mb_strlen(trim((string)($d['note'] ?? ''))) < 3) $errors[] = "แถว {$n}: ยกเว้นได้เฉพาะแถวศูนย์ทุกช่องพร้อมเหตุผล";
                else $skipped[] = ['row'=>$n,'item_code'=>$row['item_code'],'note'=>$d['note']];
                continue;
            }
            $code = trim((string)($d['item_code'] ?? $row['item_code']));
            $wid = (int)($d['warehouse_id'] ?? 0);
            if (!$wid) $wid = self::defaultWarehouse($row, $source['duplicates']);
            $key = $wid.'|'.$code;
            if (!isset($source['warehouses'][$wid])) { $errors[] = "แถว {$n}: ต้องเลือกคลังปลายทาง"; continue; }
            if (count($materials[$code] ?? []) !== 1) { $errors[] = "แถว {$n}: ต้องระบุรหัสวัสดุ MATER ที่ไม่ซ้ำ หรือยกเว้นแถวศูนย์พร้อมเหตุผล"; continue; }
            $categoryTitle=(string)($materials[$code][0]['category_title'] ?? '');
            if (preg_replace('/\s+/u','',$categoryTitle)!==preg_replace('/\s+/u','',$row['category'])) {
                $errors[] = "แถว {$n}: ประเภทวัสดุปลายทางไม่ตรง Excel ต้องตรวจทะเบียนก่อน"; continue;
            }
            if (isset($targets[$key])) { $errors[] = "แถว {$n}: จับคู่ซ้ำคลังและรหัสเดียวกับแถว {$targets[$key]['source_row']}"; continue; }
            if (count($current[$key] ?? []) > 1) { $errors[] = "แถว {$n}: ข้อมูลปิดเดือนเดิมซ้ำ ต้องตรวจฐานข้อมูลก่อนคืนยอด"; continue; }
            $isNew = empty($current[$key]);
            // Only fill a missing item, never create a second warehouse copy of an existing item.
            if ($isNew) {
                foreach ($current as $existingRows) foreach ($existingRows as $existing) {
                    if ($existing['item_code'] === $code) {
                        $errors[] = "แถว {$n}: พบยอดเดิมในคลังอื่น ให้เลือกคลังที่มียอดเดิม หรือตรวจแก้คลังก่อนคืนยอด";
                        continue 3;
                    }
                }
            }
            if ($row['closing_qty'] < 0 || $row['closing_value'] < 0) { $errors[] = "แถว {$n}: ยอดรับรองติดลบ"; continue; }
            $old = $current[$key][0] ?? ['id'=>null,'unit_name'=>$row['unit'],'closing_qty'=>0,'closing_value'=>0];
            if (trim((string)$old['unit_name']) !== trim($row['unit']) && empty($d['unit_confirmed'])) {
                $errors[] = "แถว {$n}: หน่วยใน Excel กับระบบต่างกัน ต้องยืนยันหน่วยนับก่อน"; continue;
            }
            // Existing monthly schema stores two decimal places. Do not silently round certified quantities.
            if (abs(round($row['closing_qty'],2)-$row['closing_qty']) > .000001 || abs(round($row['closing_value'],2)-$row['closing_value']) > .000001) {
                $errors[] = "แถว {$n}: ทศนิยมเกินสองตำแหน่ง ต้องตรวจโครงสร้างฐานก่อน"; continue;
            }
            $targets[$key] = ['id'=>$old['id'],'is_new'=>$isNew,'warehouse_id'=>$wid,'item_code'=>$code,'source_row'=>$n,
                'unit_name'=>$row['unit'],'closing_qty'=>$row['closing_qty'],'closing_value'=>$row['closing_value'],
                'old_qty'=>(float)$old['closing_qty'],'old_value'=>(float)$old['closing_value'],'category'=>$row['category']];
            $warehouses[$wid] = $wid;
        }
        foreach ($current as $key=>$rows) {
            if (!isset($targets[$key]) && (abs(array_sum(array_column($rows,'closing_qty'))) > .00001 || abs(array_sum(array_column($rows,'closing_value'))) > .001)) {
                $errors[] = "ยังไม่ครอบคลุมยอดเดิม {$key} ห้ามละทิ้งหรือกระจายยอดอัตโนมัติ";
            }
        }
        // Include zero-only represented warehouses in certification and subsequent-period invalidation.
        foreach ($current as $rows) foreach ($rows as $r) $warehouses[(int)$r['warehouse_id']] = (int)$r['warehouse_id'];
        $later = array_values(array_filter($later, static fn($s)=>isset($warehouses[(int)$s['warehouse_id']])));
        if ($later && !$clearLater) $errors[] = 'มีงวดถัดไป ต้องยืนยันสำรองและยกเลิก snapshot งวดถัดไปก่อน';
        if (abs(array_sum(array_column($targets,'closing_value'))-$source['source_total']) > .001) $errors[] = 'ยอดที่จับคู่ยังไม่ครบยอด Excel';
        if (!$targets) $errors[] = 'ไม่มีรายการคืนยอด';
        $byCategory=[]; $byWarehouse=[];
        foreach ($targets as $t) {
            $byCategory[$t['category']]=($byCategory[$t['category']] ?? 0)+$t['closing_value'];
            $byWarehouse[$t['warehouse_id']]=($byWarehouse[$t['warehouse_id']] ?? 0)+$t['closing_value'];
        }
        return ['targets'=>array_values($targets),'warehouses'=>array_values($warehouses),'skipped'=>$skipped,
            'by_category'=>$byCategory,'by_warehouse'=>$byWarehouse,
            'later'=>$later,'errors'=>array_values(array_unique($errors)),'source_total'=>$source['source_total']];
    }

    private static function build(array $draft, array $decisions, bool $clearLater): array
    {
        $source = MonthlySnapshotReconciliationService::inspectDatabase(self::source($draft));
        $state = self::state((int)$draft['report_year'],(int)$draft['report_month']);
        $catalog = StockItem::find()->leftJoin(['category'=>'categorise'],"category.code = categorise.category_id AND category.name = 'asset_type' AND category.category_id = '4'")
            ->select(['categorise.code','category.title AS category_title'])->orderBy(['categorise.code'=>SORT_ASC,'categorise.id'=>SORT_ASC])->asArray()->all();
        $plan = self::plan($source,$decisions,$state,$catalog,$clearLater);
        try { MonthlyPeriodProtection::assertWritable($plan['warehouses'],(int)$draft['report_year'],(int)$draft['report_month'],'from'); }
        catch (\DomainException $e) { $plan['errors'][]=$e->getMessage(); }
        $plan['hash'] = hash('sha256', self::json([$draft['file_sha256'],$source['fingerprint'],$state,$plan['targets'],$decisions,$clearLater]));
        $plan['state'] = $state;
        return $plan;
    }

    public static function preview(int $id, array $decisions, bool $clearLater, string $reason, int $user): array
    {
        return MonthlyPeriodProtection::run(function () use ($id,$decisions,$clearLater,$reason,$user) {
            $draft=self::get($id);
            if ($draft['status'] !== 'draft') throw new \DomainException('ชุดนี้บันทึกแล้วหรือย้อนคืนแล้ว');
            if (mb_strlen(trim($reason)) < 10) throw new \DomainException('ระบุเหตุผลอย่างน้อย 10 ตัวอักษร');
            $plan=self::build($draft,$decisions,$clearLater);
            Yii::$app->db->createCommand()->update('stock_monthly_restore',[
                'decisions_json'=>self::json(['rows'=>$decisions,'clear_later'=>$clearLater]),'preview_hash'=>$plan['errors'] ? null : $plan['hash'],
                'reason'=>$reason,'updated_at'=>date('Y-m-d H:i:s'),'updated_by'=>$user,
            ],['id'=>$id,'status'=>'draft'])->execute();
            return $plan;
        });
    }

    public static function commit(int $id, string $hash, int $user): void
    {
        MonthlyPeriodProtection::run(function () use ($id,$hash,$user) {
            $draft=self::get($id);
            if ($draft['status']==='committed') return; // Same batch is idempotent.
            if ($draft['status']!=='draft' || !$draft['preview_hash'] || !hash_equals($draft['preview_hash'],$hash)) throw new \DomainException('ต้องตรวจตัวอย่างใหม่ก่อนบันทึก');
            $d=json_decode($draft['decisions_json'],true,512,JSON_THROW_ON_ERROR);
            $plan=self::build($draft,$d['rows'],$d['clear_later']);
            if ($plan['errors'] || !hash_equals($hash,$plan['hash'])) throw new \DomainException('ข้อมูลเปลี่ยนหลังตรวจตัวอย่าง กรุณาตรวจใหม่');
            MonthlyPeriodProtection::assertWritable($plan['warehouses'],(int)$draft['report_year'],(int)$draft['report_month'],'from');
            $db=Yii::$app->db; $tx=$db->beginTransaction();
            try {
                foreach ($plan['targets'] as $r) {
                    $values = [
                    'closing_qty'=>$r['closing_qty'],'closing_value'=>$r['closing_value'],'unit_name'=>$r['unit_name'],
                    ];
                    if (!empty($r['is_new'])) {
                        $db->createCommand()->insert('stock_monthly_report',array_merge($values,[
                            'report_year'=>$draft['report_year'],'report_month'=>$draft['report_month'],
                            'warehouse_id'=>$r['warehouse_id'],'item_code'=>$r['item_code'],
                        ]))->execute();
                    } else $db->createCommand()->update('stock_monthly_report',$values,['id'=>$r['id']])->execute();
                }
                if ($plan['later']) $db->createCommand()->delete('stock_monthly_report',['id'=>array_column($plan['later'],'id')])->execute();
                foreach ($plan['warehouses'] as $wid) $db->createCommand()->insert('stock_monthly_period_lock',array_merge(self::audit($user),[
                    'report_year'=>$draft['report_year'],'report_month'=>$draft['report_month'],'warehouse_id'=>$wid,'restore_id'=>$id,
                ]))->execute();
                $after=self::state((int)$draft['report_year'],(int)$draft['report_month']);
                $total=0;
                foreach ($after as $r) if ((int)$r['report_year']===(int)$draft['report_year'] && (int)$r['report_month']===(int)$draft['report_month']) $total+=(float)$r['closing_value'];
                if (abs($total-$plan['source_total'])>.001) throw new \RuntimeException('ยอดหลังบันทึกไม่ตรง Excel ยกเลิกทั้งชุด');
                self::event($id,'commit',$plan['state'],$after,$draft['reason'],$user);
                $db->createCommand()->update('stock_monthly_restore',['status'=>'committed','updated_at'=>date('Y-m-d H:i:s'),'updated_by'=>$user],['id'=>$id])->execute();
                $tx->commit();
            } catch (\Throwable $e) { $tx->rollBack(); throw $e; }
        });
    }

    public static function revert(int $id, string $reason, int $user): void
    {
        MonthlyPeriodProtection::run(function () use ($id,$reason,$user) {
            $draft=self::get($id);
            if ($draft['status']!=='committed' || mb_strlen(trim($reason))<10) throw new \DomainException('ย้อนคืนได้เฉพาะชุดที่บันทึกแล้ว พร้อมเหตุผลอย่างน้อย 10 ตัวอักษร');
            $event=(new Query())->from('stock_monthly_restore_event')->where(['restore_id'=>$id,'action'=>'commit'])->orderBy(['id'=>SORT_DESC])->one();
            $before=json_decode($event['before_json'],true,512,JSON_THROW_ON_ERROR);
            $after=json_decode($event['after_json'],true,512,JSON_THROW_ON_ERROR);
            $current=self::state((int)$draft['report_year'],(int)$draft['report_month']);
            if (self::json($current)!==self::json($after)) throw new \DomainException('ข้อมูลเปลี่ยนหรือมีงวดใหม่หลังคืนยอดแล้ว ต้องตรวจผลกระทบก่อนย้อนคืน');
            $foreign=(new Query())->from('stock_monthly_period_lock')->where(['>=',new \yii\db\Expression('report_year * 12 + report_month'),$draft['report_year']*12+$draft['report_month']])->andWhere(['<>','restore_id',$id])->exists();
            if ($foreign) throw new \DomainException('มีงวดอื่นรับรองแล้ว ไม่สามารถย้อนคืนอัตโนมัติ');
            $db=Yii::$app->db; $tx=$db->beginTransaction();
            try {
                // Restore exact row IDs and all prior fields; only the report table is touched.
                if ($current) $db->createCommand()->delete('stock_monthly_report',['id'=>array_column($current,'id')])->execute();
                foreach ($before as $r) $db->createCommand()->insert('stock_monthly_report',$r)->execute();
                $db->createCommand()->delete('stock_monthly_period_lock',['restore_id'=>$id])->execute();
                self::event($id,'revert',$current,self::state((int)$draft['report_year'],(int)$draft['report_month']),$reason,$user);
                $db->createCommand()->update('stock_monthly_restore',['status'=>'reverted','updated_at'=>date('Y-m-d H:i:s'),'updated_by'=>$user],['id'=>$id])->execute();
                $tx->commit();
            } catch (\Throwable $e) { $tx->rollBack(); throw $e; }
        });
    }

    private static function event(int $id,string $action,array $before,array $after,string $reason,int $user): void
    {
        Yii::$app->db->createCommand()->insert('stock_monthly_restore_event',array_merge(self::audit($user),[
            'restore_id'=>$id,'action'=>$action,'before_json'=>self::json($before),'after_json'=>self::json($after),'reason'=>$reason,
        ]))->execute();
    }
    private static function json($data): string { return json_encode($data,JSON_UNESCAPED_UNICODE|JSON_PRESERVE_ZERO_FRACTION|JSON_THROW_ON_ERROR); }
}
