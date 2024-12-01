<div class="card">
    <div class="card-body">

<div
    class="table-responsive"
>
    <table
        class="table table-primary"
    >
        <thead>
            <tr>
                <th scope="col">ชื่อ-นามสกุล</th>
                <th scope="col">ประเภท</th>
                <th scope="col">อายุราชการ</th>
                <th scope="col">Column 3</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($employee as $emp):?>
            <tr class="">
                <td scope="row"><?php echo $emp->fullname?></td>
                <td scope="row">
                    <?php 
                    try {
                        echo $emp->LeaveRole()['title'];
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                
                ?></td>
                <td><?php
                $role =  $emp->LeaveRole();
                if ($role) {
                    try {
                        echo number_format($role['years_of_service'],0);  // Accessing the leave days
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                } else {
                    echo 'No data found';  // Handle when no result is returned
                }
               ?></td>
                <td>R1C3</td>
            </tr>
<?php endforeach?>
        </tbody>
    </table>
</div>

</div>
</div>
