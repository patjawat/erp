<?php

namespace app\modules\finance\models;

use app\modules\purchase\models\Doc;

/**
 * Snapshot เอกสารใบอนุมัติจ่ายเจ้าหนี้ (ต่อบิล) แก้ไขได้ก่อนพิมพ์
 * สืบทอด purchase\models\Doc เพื่อ reuse DocRenderer + หน้าจอแก้ไขของงานพัสดุ
 *
 * @property FinancePayable $payable
 */
class FinancePayableDocument extends Doc
{
    public static function tableName()
    {
        return '{{%finance_payable_document}}';
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['payable_id'], 'required'],
            [['payable_id'], 'integer'],
        ]);
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        // Doc ตั้ง ref เป็น purchase_doc_* ให้เอง อ่านแล้วเข้าใจผิดว่ามาจากพัสดุ
        if ($insert && strpos((string) $this->ref, 'purchase_doc_') === 0) {
            $this->ref = 'finance_payable_doc_' . uniqid();
        }
        return true;
    }

    public function getPayable()
    {
        return $this->hasOne(FinancePayable::class, ['id' => 'payable_id']);
    }
}
