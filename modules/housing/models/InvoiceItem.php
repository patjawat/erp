<?php
declare(strict_types=1);
namespace app\modules\housing\models;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
final class InvoiceItem extends ActiveRecord
{
    public static function tableName(): string { return '{{%housing_invoice_item}}'; }
    public function behaviors(): array { return [
        ['class'=>TimestampBehavior::class,'createdAtAttribute'=>'created_at','updatedAtAttribute'=>'updated_at','value'=>new Expression('NOW()')],
        ['class'=>BlameableBehavior::class,'createdByAttribute'=>'created_by','updatedByAttribute'=>'updated_by'],
    ]; }
    public function rules(): array { return [[['invoice_id','description','quantity','unit_price','amount'],'required'],[['invoice_id','charge_type_id','sort_order','created_by','updated_by'],'integer'],[['quantity','unit_price','amount'],'number'],[['description','calculation_note'],'string','max'=>255],[['unit_name'],'string','max'=>50]]; }
}
