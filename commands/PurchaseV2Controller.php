<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Exception as DbException;
use yii\helpers\BaseConsole;
use app\modules\hr\models\Employees;
use app\modules\purchaseV2\services\PurchaseMigrationService;

class PurchaseV2Controller extends Controller
{
    public $batchSize = 50;
    public $limit = 0;
    public $fromId = null;
    public $toId = null;
    public $id = null;
    public $q = null;
    public $dryRun = false;
    public $force = false;
    public $actorEmpId = null;

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'batchSize',
            'limit',
            'fromId',
            'toId',
            'id',
            'q',
            'dryRun',
            'force',
            'actorEmpId',
        ]);
    }

    public function actionIndex()
    {
        $this->stdout("Purchase V2 migration command\n");
        $this->stdout("Usage:\n");
        $this->stdout("  yii purchase-v2/preview [--id=123] [--fromId=1] [--toId=1000] [--q=PR]\n");
        $this->stdout("  yii purchase-v2/migrate [--batchSize=50] [--limit=0] [--dryRun=1] [--force=1]\n");
        $this->stdout("Options:\n");
        $this->stdout("  --batchSize    จำนวนรายการต่อรอบย่อย\n");
        $this->stdout("  --limit        จำกัดจำนวนรายการที่จะย้าย\n");
        $this->stdout("  --fromId       เริ่มจาก legacy order id นี้\n");
        $this->stdout("  --toId         จบที่ legacy order id นี้\n");
        $this->stdout("  --id           ย้ายเฉพาะ legacy order id เดียว\n");
        $this->stdout("  --q            ค้นหาจากเลขเอกสารหรือ ref เดิม\n");
        $this->stdout("  --dryRun       จำลองการทำงานโดยไม่บันทึกข้อมูล\n");
        $this->stdout("  --force        ข้ามการ confirm ก่อนย้ายจริง\n");
        $this->stdout("  --actorEmpId   ระบุ employee ที่ใช้เป็นผู้ดำเนินการ\n");
        $this->stdout("Before migrate, run: yii migrate --migrationPath=@app/modules/purchaseV2/migrations\n");

        return ExitCode::OK;
    }

    public function actionPreview()
    {
        try {
            $service = $this->service();
            $previewLimit = $this->previewLimit();
            $filters = $this->buildFilters();
            $preview = $service->previewLegacyOrders($previewLimit, $filters);

            $this->stdout("Preview purchaseV2 migration\n");
            $this->stdout("  Total candidates : " . number_format((int) $preview['total']) . PHP_EOL);
            $this->stdout("  Already migrated : " . number_format((int) $preview['migrated_count']) . PHP_EOL);
            $this->stdout("  Remaining        : " . number_format(max(0, (int) $preview['total'] - (int) $preview['migrated_count'])) . PHP_EOL);
            $this->stdout("  Sample size      : " . number_format($previewLimit) . PHP_EOL);

            if (empty($preview['rows'])) {
                $this->stdout("No legacy orders found for the selected filters.\n");
                return ExitCode::OK;
            }

            $this->stdout("\n");
            $this->stdout("ID      Request No                 Status         Requester                 Department                Amount\n");
            $this->stdout(str_repeat('-', 110) . "\n");
            foreach ($preview['rows'] as $row) {
                $this->stdout(sprintf(
                    "%-7s %-25s %-13s %-24s %-24s %s\n",
                    '#' . $row['legacy_id'],
                    $row['request_no'],
                    $row['status_label'],
                    $this->trimColumn($row['requester_name'] ?? '-'),
                    $this->trimColumn($row['department_name'] ?? '-'),
                    number_format((float) ($row['grand_total'] ?? 0), 2)
                ));
            }

            return ExitCode::OK;
        } catch (DbException $e) {
            $this->stderr("ไม่สามารถเชื่อมต่อฐานข้อมูลได้: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        } catch (\Throwable $e) {
            $this->stderr("เกิดข้อผิดพลาดระหว่าง preview: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    public function actionMigrate()
    {
        try {
            $service = $this->service();
            $filters = $this->buildFilters();
            $batchSize = $this->normalizedBatchSize();
            $limit = $this->normalizedLimit();
            $actor = $this->resolveActor();

            if ($this->actorEmpId !== null && (int) $this->actorEmpId > 0 && !$actor) {
                $this->stderr("ไม่พบ employee ที่ระบุด้วย --actorEmpId\n");
                return ExitCode::UNSPECIFIED_ERROR;
            }

            $query = $service->buildLegacyOrderQuery($filters, true)->orderBy(['id' => SORT_ASC]);
            $countQuery = clone $query;

            if ($limit > 0) {
                $query->limit($limit);
            }

            $total = (int) $countQuery->count();
            $selected = $limit > 0 ? min($total, $limit) : $total;

            $this->stdout("Purchase V2 migration\n");
            $this->stdout("  Selected        : " . number_format($selected) . PHP_EOL);
            $this->stdout("  Batch size      : " . number_format($batchSize) . PHP_EOL);
            $this->stdout("  Dry run         : " . ($this->dryRun ? 'yes' : 'no') . PHP_EOL);
            if ($actor) {
                $this->stdout("  Actor           : " . ($actor->fullname ?? ('Employee #' . $actor->id)) . PHP_EOL);
            }

            if ($selected === 0) {
                $this->stdout("No legacy orders need migration.\n");
                return ExitCode::OK;
            }

            if (!$this->dryRun) {
                if (!$this->force && !$this->interactive) {
                    $this->stderr("เพิ่ม --force เพื่อยืนยันการย้ายข้อมูลในโหมด non-interactive\n");
                    return ExitCode::UNSPECIFIED_ERROR;
                }

                if (!$this->force && $this->interactive && !BaseConsole::confirm('ยืนยันการย้ายข้อมูล purchaseV2 ใช่หรือไม่?')) {
                    $this->stdout("Cancelled.\n");
                    return ExitCode::OK;
                }
            }

            $migrated = 0;
            $failed = 0;
            $processed = 0;
            $errors = [];

            foreach ($query->each($batchSize) as $legacyOrder) {
                $processed++;

                if ($this->dryRun) {
                    $this->stdout(sprintf(
                        "[DRY] #%d %s\n",
                        $legacyOrder->id,
                        $legacyOrder->pr_number ?: ($legacyOrder->pq_number ?: ($legacyOrder->po_number ?: (string) $legacyOrder->ref))
                    ));
                    continue;
                }

                try {
                    $request = $service->migrateLegacyOrder((int) $legacyOrder->id, $actor);
                    $migrated++;
                    $this->stdout(sprintf(
                        "[OK]   #%d -> %s\n",
                        $legacyOrder->id,
                        $request->request_no
                    ));
                } catch (\Throwable $e) {
                    $failed++;
                    $errors[] = sprintf('#%d %s', $legacyOrder->id, $e->getMessage());
                    $this->stderr(sprintf("[ERR]  #%d %s\n", $legacyOrder->id, $e->getMessage()));
                }
            }

            $this->stdout("\nSummary\n");
            $this->stdout("  Processed : " . number_format($processed) . PHP_EOL);
            $this->stdout("  Migrated  : " . number_format($migrated) . PHP_EOL);
            $this->stdout("  Failed    : " . number_format($failed) . PHP_EOL);

            if (!empty($errors)) {
                $this->stdout("\nErrors\n");
                foreach ($errors as $error) {
                    $this->stdout('  - ' . $error . PHP_EOL);
                }
            }

            return $failed > 0 ? ExitCode::UNSPECIFIED_ERROR : ExitCode::OK;
        } catch (DbException $e) {
            $this->stderr("ไม่สามารถเชื่อมต่อฐานข้อมูลได้: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        } catch (\Throwable $e) {
            $this->stderr("เกิดข้อผิดพลาดระหว่าง migrate: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    protected function service(): PurchaseMigrationService
    {
        return new PurchaseMigrationService();
    }

    protected function buildFilters(): array
    {
        $filters = [];

        if ($this->id !== null && $this->id !== '') {
            $filters['id'] = (int) $this->id;
        } else {
            if ($this->fromId !== null && $this->fromId !== '') {
                $filters['fromId'] = (int) $this->fromId;
            }

            if ($this->toId !== null && $this->toId !== '') {
                $filters['toId'] = (int) $this->toId;
            }
        }

        if ($this->q !== null && trim((string) $this->q) !== '') {
            $filters['q'] = trim((string) $this->q);
        }

        return $filters;
    }

    protected function previewLimit(): int
    {
        $limit = (int) $this->limit;
        return $limit > 0 ? min($limit, 50) : 20;
    }

    protected function normalizedBatchSize(): int
    {
        return max(1, (int) $this->batchSize);
    }

    protected function normalizedLimit(): int
    {
        return max(0, (int) $this->limit);
    }

    protected function resolveActor(): ?Employees
    {
        if ($this->actorEmpId === null || $this->actorEmpId === '') {
            return null;
        }

        return Employees::findOne((int) $this->actorEmpId);
    }

    protected function trimColumn($value, int $length = 24): string
    {
        $text = trim((string) $value);
        if ($text === '') {
            return '-';
        }

        if (function_exists('mb_strimwidth')) {
            return mb_strimwidth($text, 0, $length, '...', 'UTF-8');
        }

        return strlen($text) > $length ? substr($text, 0, max(0, $length - 3)) . '...' : $text;
    }
}
