
<?php
$this->title ="อยู่ระหว่างรอผู้อำนวยการ/อนุมัติ"
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/hr/views/leave/menu') ?>
<?php $this->endBlock(); ?>
  <script>
    // ฟังก์ชันสำหรับ Select/Deselect ทุกช่อง
    function toggleAll(source) {
      const checkboxes = document.querySelectorAll('.item-checkbox');
      checkboxes.forEach(checkbox => checkbox.checked = source.checked);
    }
  </script>

<div class="card">
    <div class="card-body">
<h6>รายการขออนุมัติ</h6>

    <table
        class="table table-bordered table-sm table-responsive"
        style="font-size: 12px; white-space: nowrap;"
    >
        <thead class="align-middle">
    
            <tr>
            <th class="text-center"><input type="checkbox" id="selectAll" onclick="toggleAll(this)"></th>
                <th scope="col" class="text-center">รายการขออนุมัติ</th>
                <th scope="col" class="text-center">หน่วยงาน</th>
                <?php for ($i=1; $i < 32 ; $i++):?>
                    <th scope="col" class="text-center" style="width: 35px;"><?php echo $i;?></th>
                <?php endfor?>
                </tr>
        </thead>
        <?php foreach($dataProvider->getModels() as $item):?>
        <tbody class="align-middle table-group-divider">
            <tr>
            <td class="text-center"><input type="checkbox" class="item-checkbox"></td>
                <td scope="row"> <?=$item->getAvatar(false)['avatar']?></td>
                <td scope="row"><?php echo $item->employee->departmentName()?></td>
                <?php for ($i=1; $i < 32 ; $i++):?>
                    <td scope="col">
                        <?php
                        $dateStart  = new DateTime($item->date_start);
                        $dateEnd  = new DateTime($item->date_end);
                        if($dateStart->format('d') == $dateEnd->format('d') && $dateStart->format('d') == $i){
                            echo '<i class="bi bi-hourglass-split text-primary"></i>';
                        }
                    ?>
                    </td>
                <?php endfor?>
                <?php endforeach;?>
               
            </tr>
           
        </tbody>
    </table>

    </div>
</div>
