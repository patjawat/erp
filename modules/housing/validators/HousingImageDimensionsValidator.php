<?php

declare(strict_types=1);

namespace app\modules\housing\validators;

use yii\validators\Validator;
use yii\web\UploadedFile;

final class HousingImageDimensionsValidator extends Validator
{
    public int $maxPixels = 50_000_000;
    public int $maxDimension = 12_000;

    public function validateAttribute($model, $attribute): void
    {
        if ($model->hasErrors($attribute)) {
            return;
        }

        $value = $model->$attribute;
        $files = is_array($value) ? $value : [$value];
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile || $file->tempName === '' || !is_file($file->tempName)) {
                continue;
            }

            $dimensions = @getimagesize($file->tempName);
            if ($dimensions === false) {
                $this->addError($model, $attribute, 'ไฟล์ {file} ไม่ใช่รูปภาพที่ระบบอ่านได้', [
                    'file' => $file->name,
                ]);
                continue;
            }

            $width = (int) $dimensions[0];
            $height = (int) $dimensions[1];
            if ($width > $this->maxDimension
                || $height > $this->maxDimension
                || $width * $height > $this->maxPixels) {
                $this->addError(
                    $model,
                    $attribute,
                    'รูปภาพ {file} มีความละเอียดสูงเกินกำหนด สูงสุด {maxPixels} ล้านพิกเซล และด้านละไม่เกิน {maxDimension} พิกเซล',
                    [
                        'file' => $file->name,
                        'maxPixels' => number_format($this->maxPixels / 1_000_000),
                        'maxDimension' => number_format($this->maxDimension),
                    ]
                );
            }
        }
    }
}
