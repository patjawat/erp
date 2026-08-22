<?php

namespace tests\unit\views;

use Codeception\Test\Unit;

class StockHealthReadOnlyViewTest extends Unit
{
    public function testHealthPageUsesControlledRepairFlowOnly(): void
    {
        $view = file_get_contents(__DIR__ . '/../../../modules/inventoryV2/views/stock-health/index.php');
        $this->assertStringContainsString('ผล Dry-run', $view);
        $this->assertStringContainsString('fingerprint', $view);
        $this->assertStringContainsString('เหตุผลและหลักฐานประกอบ', $view);
        $this->assertStringContainsString('id="healthRepairButton" disabled', $view);
        $this->assertStringNotContainsString('update-lot-balance', $view);
        $this->assertStringNotContainsString('stock-adjust/save', $view);
    }

    public function testIssuePageDistinguishesDataMismatchFromShortage(): void
    {
        $view = file_get_contents(__DIR__ . '/../../../modules/inventoryV2/views/issue/process.php');
        $this->assertStringContainsString('ข้อมูลสต็อกไม่ตรงกัน', $view);
        $this->assertStringContainsString('/inventory-v2/stock-health/index', $view);
        $this->assertStringContainsString('FIFO อัตโนมัติ', $view);
        $this->assertStringContainsString('value="AUTO_FIFO"', $view);
        $this->assertStringContainsString('Rollback ทั้งใบ', $view);
        $this->assertStringContainsString('แผนการตัด Lot ตาม FIFO', $view);
        $this->assertStringContainsString('ผลการตัด Lot จริง', $view);
        $this->assertStringContainsString('item.slices.length > 1', $view);
        $this->assertStringContainsString('(item.lots || []).length > 1', $view);
        $this->assertStringContainsString('table-info multi-lot-row', $view);
        $this->assertStringContainsString('Lot แรกไม่พอ · ตัดต่อ Lot ถัดไป', $view);
        $this->assertStringContainsString("row.attr('data-first-lot-stock')", $view);
        $this->assertStringContainsString("row.attr('data-total-stock')", $view);
        $this->assertStringContainsString('ยอดรวมทุก Lot ไม่พอจ่าย', $view);
        $this->assertStringContainsString("data-invalid-issue", $view);
        $this->assertStringNotContainsString('data-requested-qty', $view);
    }

    public function testStockHealthAppearsInTheMainWarehouseMenu(): void
    {
        $menu = file_get_contents(__DIR__ . '/../../../modules/inventoryV2/views/default/_menu_main.php');
        $view = file_get_contents(__DIR__ . '/../../../modules/inventoryV2/views/stock-health/index.php');
        $this->assertStringContainsString('/inventory-v2/stock-health/index', $menu);
        $this->assertStringContainsString('ตรวจสุขภาพสต็อก', $menu);
        $this->assertStringContainsString("'active' => 'stock-health'", $view);
        $this->assertStringContainsString('views/default/_menu_main', $view);
    }

    public function testHistoryVarianceDoesNotSuggestAStockAdjustmentThatPreservesTheVariance(): void
    {
        $view = file_get_contents(__DIR__ . '/../../../modules/inventoryV2/views/report/_item_history_modal.php');
        $this->assertStringNotContainsString('id="hist-link-adjust"', $view);
        $this->assertStringNotContainsString('id="hist-copy-qty"', $view);
        $this->assertStringContainsString('อย่าสร้างเอกสาร ADJUST จากผลต่างนี้', $view);
        $this->assertStringContainsString('id="hist-link-health"', $view);
    }

    public function testHistoryIdentifiesCandidateRowsAndBlocksWrongDirectionDeletion(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../../modules/inventoryV2/controllers/ReportController.php');
        $view = file_get_contents(__DIR__ . '/../../../modules/inventoryV2/views/report/_item_history_modal.php');

        $this->assertStringContainsString("'diagnostic_code'", $controller);
        $this->assertStringContainsString('wrong_direction', $controller);
        $this->assertStringContainsString('$historyVariance > 0.000001', $controller);
        $this->assertStringContainsString('ควรตรวจอันดับแรก', $view);
        $this->assertStringContainsString('ไม่ควรแก้รายการนี้', $view);
        $this->assertStringNotContainsString('hist-restore-btn', $view);
        $this->assertStringNotContainsString("return_stock: '0'", $view);
        $this->assertStringNotContainsString('hist-delete-btn', $view);
        $this->assertStringContainsString('ไปยังจุดแก้', $view);
    }

    public function testStockCardUsesActualPostingTimeWithSafeLegacyOrdering(): void
    {
        $controller = file_get_contents(__DIR__ . '/../../../modules/inventoryV2/controllers/ReportController.php');
        $issue = file_get_contents(__DIR__ . '/../../../modules/inventoryV2/controllers/IssueController.php');
        $receive = file_get_contents(__DIR__ . '/../../../modules/inventoryV2/controllers/ReceiveController.php');
        $view = file_get_contents(__DIR__ . '/../../../modules/inventoryV2/views/report/_item_history_modal.php');

        $this->assertStringContainsString("'stock_posted_at'", $controller);
        $this->assertStringContainsString('usort($txRows', $controller);
        $this->assertStringContainsString("['in' => 0, 'out' => 1]", $controller);
        $this->assertStringContainsString('setStockPostedAt(time())', $issue);
        $this->assertStringContainsString('setStockPostedAt(time())', $receive);
        $this->assertStringContainsString('time_is_estimated', $view);
    }
}
