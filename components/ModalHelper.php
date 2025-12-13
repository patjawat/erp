<?php

namespace app\components;

use Yii;
use yii\helpers\Html;
use yii\base\Component;

class ModalHelper extends Component
{
    public static function modalFooterSaveClose()
    {
        return Html::button('<i class="fa-solid fa-circle-check"></i> บันทึก', ['class' => 'btn btn-primary form-submit', 'data' => ['id' => 'form'], 'type' => "submit"]) .
            Html::button('<i class="fa-solid fa-xmark"></i> ปิด', ['class' => 'btn btn-secondary pull-left', 'data-bs-dismiss' => "modal"]);
    }

     public static function modalFooterUpdateDeleteClose($id)
    {
        return Html::a('<i class="fa-regular fa-pen-to-square"></i> แก้ไข', ['update', 'id' => $id], ['class' => 'btn btn-warning open-modal', 'data' => ['size' => 'modal-lg']]) .
            Html::button('<i class="fa-solid fa-xmark"></i> ปิด', ['class' => 'btn btn-secondary pull-left', 'data-bs-dismiss' => "modal"]);
    }
}
