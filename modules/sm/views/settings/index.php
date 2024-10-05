
<?php
use yii\helpers\Html;
?>

<div class="">

</div>


<div class="d-flex">
    <form class="subnav-search d-flex flex-nowrap ms-auto">
        <label for="search" class="visually-hidden">Search for icons</label>
        <input class="form-control search mb-0" id="search" type="search" placeholder="ค้นหาหมูวดหมู่..."
            autocomplete="off">
    </form>
</div>



<div class="card">
    <div class="card-body">
        <!-- <h4 class="card-title">Title</h4> -->

            <table id="icons-list" class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th scope="col">รายการ</th>
                        <!-- <th scope="col" style="width:30px;">จำนวน</th> -->
                    </tr>
                </thead>
                <tbody>
                    <tr class="">
                        <td><i class="fa-regular fa-circle-check"></i> <?=Html::a('xx',['/hr/categorise'],['class' => ''])?>
                        </td>
                    </tr>
                    
                </tbody>
            </table>

    </div>
</div>
