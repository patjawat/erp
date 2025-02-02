<?php
use yii\helpers\Html;
?>
<table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">ทะเบียนยานพาหนะ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach($dataProvider->getModels() as $item):?>
                <tr class="">
                    <td><?php echo $item->Avatar()?></td>
                <?php endforeach;?>
            </tbody>
        </table>