<?php
use yii\helpers\Html;
?>
<div class="card" style="height:300px">
    <div class="card-body">
        <h6>สถิติการลา</h6>
            <table
                class="table table-primary"
            >
                <thead>
                    <tr>
                        <th scope="col">Column 1</th>
                        <th scope="col">Column 2</th>
                        <th scope="col">Column 3</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="">
                        <td scope="row">R1C1</td>
                        <td>R1C2</td>
                        <td>R1C3</td>
                    </tr>
                    <tr class="">
                        <td scope="row">Item</td>
                        <td>Item</td>
                        <td>Item</td>
                    </tr>
                </tbody>
            </table>
        
        <div class="attendance-list">
            <div class="row">
                <div class="col-md-4">
                    <div class="attendance-details">
                        <h4 class="text-primary">9</h4>
                        <p>Total Leaves</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="attendance-details">
                        <h4 class="text-pink">5.5</h4>
                        <p>Leaves Taken</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="attendance-details">
                        <h4 class="text-success">04</h4>
                        <p>Leaves Absent</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="attendance-details">
                        <h4 class="text-purple">0</h4>
                        <p>Pending Approval</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="attendance-details">
                        <h4 class="text-info">214</h4>
                        <p>Working Days</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="attendance-details">
                        <h4 class="text-danger">2</h4>
                        <p>Loss of Pay</p>
                    </div>
                </div>
            </div>
        </div>

        </div>
        <div class="card-footer">
        <?=Html::a('ระบบการลา <i class="fe fe-arrow-right-circle"></i>',['/me/leave'],['class' => 'btn btn-light'])?>

    </div>
</div>