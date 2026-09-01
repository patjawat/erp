<?php

namespace tests\unit\modules\inventoryV2;

use Codeception\Test\Unit;

class StockItemPackagingSafetyTest extends Unit
{
    public function testPackagingMetadataIsExplicitlyDisplayOnly(): void
    {
        $model = $this->source('models/StockItem.php');
        $form = $this->source('views/stock-item/_form.php');

        $this->assertStringContainsString("data_json.package_unit_name", $model);
        $this->assertStringContainsString("data_json.package_size", $model);
        $this->assertStringContainsString('แสดงผลเท่านั้น ไม่ใช้คำนวณสต็อก', $form);
    }

    public function testPackagingMetadataNeverEntersStockMutationServices(): void
    {
        foreach ([
            'components/InventoryService.php',
            'controllers/ReceiveController.php',
            'controllers/IssueController.php',
        ] as $relativePath) {
            $source = $this->source($relativePath);
            $this->assertStringNotContainsString('package_unit_name', $source, $relativePath);
            $this->assertStringNotContainsString('package_size', $source, $relativePath);
        }
    }

    public function testStockUnitRemainsCompatibleWithLegacyConsumers(): void
    {
        $controller = $this->methodSource(
            'controllers/StockItemController.php',
            'private function normalizePackagingMetadata',
            'private function getPurchaseHistory'
        );

        $this->assertStringContainsString("\$json['unit_name'] = \$unitName", $controller);
        $this->assertStringContainsString("\$json['unit'] = \$unitName", $controller);
    }

    public function testPurchaseHistoryUsesConfirmedReceiptsOnly(): void
    {
        $controller = $this->methodSource(
            'controllers/StockItemController.php',
            'private function getPurchaseHistory',
            'private function buildStockItemZipManifest'
        );

        $this->assertStringContainsString("'so.order_type' => 'IN'", $controller);
        $this->assertStringContainsString("'so.status' => StockOrder::STATUS_CONFIRMED", $controller);
        $this->assertStringContainsString("'sd.item_code' => (string) \$itemCode", $controller);
        $this->assertStringContainsString('->limit(50)', $controller);
    }

    private function source(string $relativePath): string
    {
        $path = dirname(__DIR__, 4) . '/modules/inventoryV2/' . $relativePath;
        $source = file_get_contents($path);
        $this->assertNotFalse($source, "Unable to read {$path}");
        return $source;
    }

    private function methodSource(string $relativePath, string $start, string $end): string
    {
        $source = $this->source($relativePath);
        $startAt = strpos($source, $start);
        $this->assertNotFalse($startAt, "Missing method marker: {$start}");
        $endAt = strpos($source, $end, $startAt + strlen($start));
        $this->assertNotFalse($endAt, "Missing method end marker: {$end}");
        return substr($source, $startAt, $endAt - $startAt);
    }
}
