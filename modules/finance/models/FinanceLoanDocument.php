<?php

namespace app\modules\finance\models;

use app\modules\purchase\models\Doc;

/**
 * Snapshot เอกสารเงินยืมที่แก้ไขได้ก่อนพิมพ์
 *
 * สืบทอด purchase\models\Doc เพื่อให้ DocRenderer และหน้าจอแก้ไขของงานพัสดุ
 * ใช้กับเอกสารชุดนี้ได้ทันที โดยไม่ต้องทำระบบพิมพ์ขึ้นมาซ้ำอีกชุด
 *
 * @property FinanceLoan $loan
 */
class FinanceLoanDocument extends Doc
{
    public static function tableName()
    {
        return '{{%finance_loan_document}}';
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['loan_id'], 'required'],
            [['loan_id'], 'integer'],
        ]);
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        // Doc ตั้งค่า ref เป็น purchase_doc_* ให้เอง ซึ่งอ่านแล้วเข้าใจผิดว่ามาจากงานพัสดุ
        if ($insert && strpos((string) $this->ref, 'purchase_doc_') === 0) {
            $this->ref = 'finance_loan_doc_' . uniqid();
        }
        return true;
    }

    public function getLoan()
    {
        return $this->hasOne(FinanceLoan::class, ['id' => 'loan_id']);
    }
}
