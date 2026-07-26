<?php

declare(strict_types=1);

namespace tests\unit\modules\housing;

use app\modules\filemanager\components\FileManagerHelper;
use app\modules\filemanager\models\Uploads;
use app\modules\housing\services\HousingUploadService;
use app\modules\housing\validators\HousingImageDimensionsValidator;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\DynamicModel;
use yii\helpers\BaseFileHelper;
use yii\web\Application;
use yii\web\UploadedFile;

final class HousingUploadServiceTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $appRoot = dirname(__DIR__, 4);
        Yii::setAlias('@app', $appRoot);
        if (Yii::$app === null) {
            new Application(require $appRoot . '/config/test.php');
        }
    }

    public function testRepairPhotoLimitIncludesExistingFiles(): void
    {
        $this->assertFalse(HousingUploadService::exceedsLimit(7, 3, 10));
        $this->assertTrue(HousingUploadService::exceedsLimit(7, 4, 10));
        $this->assertTrue(HousingUploadService::exceedsLimit(10, 1, 10));
    }

    public function testEveryHousingSlotRequiresHousingPermission(): void
    {
        foreach ([
            HousingUploadService::SLOT_BUILDING_IMAGE,
            HousingUploadService::SLOT_LOCATION_PHOTO,
            HousingUploadService::SLOT_ASSET_PHOTO,
            HousingUploadService::SLOT_REPAIR_BEFORE,
            HousingUploadService::SLOT_REPAIR_AFTER,
        ] as $slot) {
            $this->assertTrue(HousingUploadService::isProtectedSlot($slot));
        }
        $this->assertFalse(HousingUploadService::isProtectedSlot('avatar'));
        $this->assertFalse(HousingUploadService::isProtectedSlot(null));
    }

    public function testImageDimensionsValidatorRejectsExcessivePixelCount(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'housing-image-');
        file_put_contents(
            $tempFile,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
        );
        $file = new UploadedFile([
            'name' => 'tiny.png',
            'tempName' => $tempFile,
            'type' => 'image/png',
            'size' => filesize($tempFile),
            'error' => UPLOAD_ERR_OK,
        ]);

        try {
            $acceptedModel = new DynamicModel(['image' => $file]);
            (new HousingImageDimensionsValidator())->validateAttribute($acceptedModel, 'image');
            $this->assertFalse($acceptedModel->hasErrors('image'));

            $model = new DynamicModel(['image' => $file]);
            $validator = new HousingImageDimensionsValidator([
                'maxPixels' => 0,
                'maxDimension' => 12_000,
            ]);
            $validator->validateAttribute($model, 'image');
            $this->assertNotEmpty($model->getErrors('image'));

            $multipleModel = new DynamicModel(['images' => [$file]]);
            $validator->validateAttribute($multipleModel, 'images');
            $this->assertNotEmpty($multipleModel->getErrors('images'));

            $dimensionModel = new DynamicModel(['image' => $file]);
            (new HousingImageDimensionsValidator([
                'maxPixels' => 50_000_000,
                'maxDimension' => 0,
            ]))->validateAttribute($dimensionModel, 'image');
            $this->assertNotEmpty($dimensionModel->getErrors('image'));
        } finally {
            @unlink($tempFile);
        }
    }

    public function testDeleteUploadsRemovesUploadRowAndPhysicalFiles(): void
    {
        $this->requireTestDatabase();

        $ref = 'test-housing-' . bin2hex(random_bytes(5));
        $realFilename = 'photo-' . bin2hex(random_bytes(4)) . '.jpg';
        $directory = FileManagerHelper::getUploadPath() . $ref;
        $thumbnailDirectory = $directory . '/thumbnail';
        BaseFileHelper::createDirectory($thumbnailDirectory);
        file_put_contents($directory . '/' . $realFilename, 'original');
        file_put_contents($thumbnailDirectory . '/' . $realFilename, 'thumbnail');

        $upload = new Uploads([
            'ref' => $ref,
            'name' => HousingUploadService::SLOT_BUILDING_IMAGE,
            'file_name' => 'building.jpg',
            'real_filename' => $realFilename,
            'type' => 'image',
            'size' => 8,
        ]);
        $this->assertTrue($upload->save(false));
        $uploadId = (int) $upload->id;

        try {
            $service = new HousingUploadService();
            $this->assertSame(
                [$uploadId],
                $service->findIdsByRefsAndSlots(
                    [$ref],
                    [HousingUploadService::SLOT_BUILDING_IMAGE]
                )
            );
            $this->assertSame([], $service->deleteUploads([$uploadId]));
            $this->assertNull(Uploads::findOne($uploadId));
            $this->assertFileDoesNotExist($directory . '/' . $realFilename);
            $this->assertFileDoesNotExist($thumbnailDirectory . '/' . $realFilename);
        } finally {
            if (Uploads::findOne($uploadId) !== null) {
                FileManagerHelper::Deletefile($uploadId);
            }
            BaseFileHelper::removeDirectory($directory);
        }
    }

    public function testSaveUploadedFileTruncatesLongOriginalName(): void
    {
        $this->requireTestDatabase();

        $tempFile = tempnam(sys_get_temp_dir(), 'housing-upload-');
        file_put_contents(
            $tempFile,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
        );
        $ref = 'test-housing-' . bin2hex(random_bytes(5));
        $file = new UploadedFile([
            'name' => str_repeat('ก', 200) . '.png',
            'tempName' => $tempFile,
            'type' => 'image/png',
            'size' => filesize($tempFile),
            'error' => UPLOAD_ERR_OK,
        ]);
        $upload = null;

        try {
            $upload = FileManagerHelper::saveUploadedFile(
                $file,
                $ref,
                HousingUploadService::SLOT_BUILDING_IMAGE,
                false
            );
            $this->assertNotNull($upload);
            $this->assertLessThanOrEqual(150, mb_strlen($upload->file_name, 'UTF-8'));
            $this->assertStringEndsWith('.png', $upload->file_name);
        } finally {
            if ($upload !== null) {
                (new HousingUploadService())->deleteUploads([(int) $upload->id]);
            }
            @unlink($tempFile);
            BaseFileHelper::removeDirectory(FileManagerHelper::getUploadPath() . $ref);
        }
    }

    private function requireTestDatabase(): void
    {
        try {
            Yii::$app->db->open();
        } catch (\Throwable $exception) {
            $this->markTestSkipped('ไม่พบ test database: ' . $exception->getMessage());
        }
    }
}
