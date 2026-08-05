<?php

namespace tests\unit\controllers;

use app\modules\inventoryV2\controllers\ReceiveController;
use app\modules\inventoryV2\models\StockOrder;
use Codeception\Test\Unit;
use Yii;
use yii\helpers\BaseFileHelper;

require_once dirname(__DIR__, 3) . '/modules/inventoryV2/models/StockOrder.php';
require_once dirname(__DIR__, 3) . '/modules/inventoryV2/controllers/ReceiveController.php';

class ReceiveControllerExpenseItemsTest extends Unit
{
    private $originalBodyParams;
    private $originalWebroot;
    private $temporaryWebroot;

    protected function _before(): void
    {
        $this->originalBodyParams = Yii::$app->request->getBodyParams();
        $this->originalWebroot = Yii::getAlias('@webroot');
        $this->temporaryWebroot = sys_get_temp_dir() . '/receive-expenses-' . bin2hex(random_bytes(6));
        BaseFileHelper::createDirectory($this->temporaryWebroot);
        Yii::setAlias('@webroot', $this->temporaryWebroot);
    }

    protected function _after(): void
    {
        Yii::$app->request->setBodyParams($this->originalBodyParams);
        Yii::setAlias('@webroot', $this->originalWebroot);
        BaseFileHelper::removeDirectory($this->temporaryWebroot);
    }

    public function testSaveWithoutExpenseItemsDoesNotTouchTheModelOrFilesystem(): void
    {
        Yii::$app->request->setBodyParams([]);
        $model = new ExpenseTrackingStockOrder();

        $this->controller()->saveExpenseItems($model);

        $this->assertFalse($model->saved);
        $this->assertDirectoryDoesNotExist($this->temporaryWebroot . '/uploads');
    }

    public function testExpenseWithoutReceiptDoesNotCreateUploadDirectory(): void
    {
        Yii::$app->request->setBodyParams([
            'ExpenseItems' => [[
                'description' => 'ค่าขนส่ง',
                'amount' => '25.50',
            ]],
        ]);
        $model = new ExpenseTrackingStockOrder();

        $this->controller()->saveExpenseItems($model);

        $this->assertTrue($model->saved);
        $this->assertSame('ค่าขนส่ง', $model->getExpenseItems()[0]['description']);
        $this->assertSame(25.5, $model->getExpenseItems()[0]['amount']);
        $this->assertDirectoryDoesNotExist($this->temporaryWebroot . '/uploads');
    }

    private function controller(): TestableReceiveController
    {
        return new TestableReceiveController('receive', Yii::$app);
    }
}

class TestableReceiveController extends ReceiveController
{
    public function saveExpenseItems(StockOrder $model): void
    {
        $this->saveExpenseItemsAndReceipts($model);
    }
}

class ExpenseTrackingStockOrder extends StockOrder
{
    public $saved = false;
    private $expenseItems = [];

    public function getExpenseItems()
    {
        return $this->expenseItems;
    }

    public function setExpenseItems(array $items)
    {
        $this->expenseItems = $items;
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        $this->saved = true;
        return true;
    }
}
