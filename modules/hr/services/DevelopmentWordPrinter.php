<?php

namespace app\modules\hr\services;

use Yii;
use yii\helpers\FileHelper;
use app\components\Processor;
use app\components\SiteHelper;
use app\components\ThaiDateHelper;
use app\modules\hr\models\Development;
use yii\web\NotFoundHttpException;

/**
 * สร้างไฟล์ Word ของเอกสารไปราชการจากแม่แบบใน webroot/msword/development
 *
 * แยกออกมาจาก controller เพราะเดิมอยู่ในโมดูล me ซึ่งเป็นหน้าของเจ้าตัวเอง
 * ทำให้ผู้ดูแลงานอบรม/ดูงานพิมพ์แทนผู้ขอไม่ได้ ตอนนี้ทั้งโมดูล me และ hr
 * เรียกที่เดียวกัน แก้ข้อความในเอกสารครั้งเดียวมีผลทั้งสองทาง
 */
final class DevelopmentWordPrinter
{
    public const DIR_TEMPLATE = '/msword/development/';
    public const DIR_RESULT = '/msword/results/development/';

    /** แบบฟอร์มขออนุญาต — คืนชื่อไฟล์ผลลัพธ์ */
    public static function permitRequest(Development $model): string
    {
        $info = self::info();
        [$processor, $resultName] = self::open('แบบฟอร์มขออนุญาต', $model);

        self::fillCommon($processor, $model, $info);
        $processor->setValue('dev_date', ThaiDateHelper::formatThaiDateRange($model->date_start, $model->date_end));
        $processor->setValue('status', self::statusLabel($model));
        self::fillDirector($processor, $info);

        return self::save($processor, $resultName);
    }

    /** ใบตอบรับเป็นวิทยากร — คืนชื่อไฟล์ผลลัพธ์ */
    public static function academicForm(Development $model): string
    {
        $info = self::info();
        [$processor, $resultName] = self::open('แบบฟอร์มตอบรับวิทยากร', $model);

        self::fillCommon($processor, $model, $info);
        $processor->setValue('document_date', ThaiDateHelper::formatThaiDate($model->document?->doc_date) ?? '-');
        $processor->setValue('dev_date', ThaiDateHelper::formatThaiDateRange($model->date_start, $model->date_end));

        try {
            $processor->setImg('emp_sign', ['src' => $model->createdByEmp->signature(), 'size' => [150, 50]]);
        } catch (\Throwable $th) {
            $processor->setValue('emp_sign', '........................................');
        }

        self::fillDirector($processor, $info);

        return self::save($processor, $resultName);
    }

    /** เปิดแม่แบบ ลบไฟล์ผลลัพธ์เก่าทิ้งก่อน แล้วคืน processor คู่กับชื่อไฟล์ใหม่ */
    private static function open(string $title, Development $model): array
    {
        $resultName = $title . '-' . $model->id . '.docx';
        $resultPath = Yii::getAlias('@webroot') . self::DIR_RESULT . $resultName;
        @unlink($resultPath);

        return [new Processor(Yii::getAlias('@webroot') . self::DIR_TEMPLATE . $title . '.docx'), $resultName];
    }

    private static function save(Processor $processor, string $resultName): string
    {
        // โฟลเดอร์ผลลัพธ์อาจยังไม่มีในเครื่องที่ติดตั้งใหม่ ต้องสร้างก่อนไม่งั้น copy ล้ม
        FileHelper::createDirectory(Yii::getAlias('@webroot') . self::DIR_RESULT);

        $filePath = Yii::getAlias('@webroot') . self::DIR_RESULT . $resultName;
        $processor->saveAs($filePath);
        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('สร้างไฟล์เอกสารไม่สำเร็จ');
        }

        return $resultName;
    }

    /** ช่องที่เอกสารทั้งสองแบบใช้เหมือนกัน */
    private static function fillCommon(Processor $processor, Development $model, array $info): void
    {
        $processor->setValue('org_fullname', $info['org_fullname']);
        $processor->setValue('org_name', $info['company_name']);
        $processor->setValue('address', $info['address']);
        $processor->setValue('phone', $info['phone']);
        $processor->setValue('doc_number', $info['doc_number']);
        $processor->setValue('document_number', $model->document?->doc_number ?? '-');
        $processor->setValue('doc_date', ThaiDateHelper::formatThaiDate(date('Y-m-d')));
        $processor->setValue('location', $model->data_json['location'] ?? '-');
        $processor->setValue('governor', $info['governor']);
        $processor->setValue('fullname', $model->createdByEmp?->fullname ?? '-');
        $processor->setValue('position', $model->createdByEmp?->positionName() ?? '-');
        $processor->setValue('department', $model->createdByEmp?->departmentName() ?? '-');
        $processor->setValue('topic', $model->topic);
    }

    private static function fillDirector(Processor $processor, array $info): void
    {
        $directorType = $info['director_type'] === 'รักษาการแทนผู้อำนวยการ' ? 'รักษาการแทนผู้อำนวยการ' : '';
        $processor->setValue('direc_fullname', $info['director_fullname']);
        $processor->setValue('direc_position', $info['director_position'] . $directorType);
        try {
            $processor->setImg('direc_sign', ['src' => $info['director']->signature(), 'size' => [150, 60]]);
        } catch (\Throwable $th) {
            $processor->setValue('direc_sign', '...........................................');
        }
    }

    private static function statusLabel(Development $model): string
    {
        return match ($model->status) {
            'Approve' => 'อนุมัติ',
            'Reject' => 'ไม่อนุมัติ',
            default => 'รอการอนุมัติ',
        };
    }

    /** ข้อมูลหน่วยงานชุดเดียวกับที่ controller ของโมดูล me เคยประกอบเอง */
    private static function info(): array
    {
        $info = SiteHelper::getInfo();

        return [
            'org_fullname' => $info['company_name'] . ' ' . $info['address'],
            'company_name' => $info['company_name'],
            'doc_number' => $info['doc_number'],
            'governor' => 'ผู้ว่าราชการจังหวัด' . $info['province'],
            'address' => $info['address'],
            'phone' => $info['phone'],
            'province' => $info['province'],
            'director' => $info['director'],
            'director_fullname' => SiteHelper::viewDirector()['fullname'],
            'director_position' => $info['director_position'],
            'director_type' => $info['director_type'],
        ];
    }
}
