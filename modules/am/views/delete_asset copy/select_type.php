
<?php
use yii\helpers\Html;
?>
<div
    class="table-responsive"
>
    <table
        class="table table-primary"
    >

        <tbody>
            
            <tr class="">
                <td scope="row">
                <?=Html::a('ที่ดิน',['/am/asset/create','group' => 1,'tilte' => 'ที่ดิน'])?>    
            </td>
        </tr>
        <tr class="">
            <td scope="row">
                    <?=Html::a('สิ่งปลูกสร้าง',['/am/asset/create','group' => 2,'tilte' => 'สิ่งปลูกสร้าง'])?>    
                </td>
            </tr>
            <tr class="">
                <td scope="row">
                    <?=Html::a('ครุภัณฑ์',['/am/asset/create','group' => 3,'tilte' => 'ครุภัณฑ์'])?>    
                </td>
            </tr>
        </tbody>
    </table>
</div>
