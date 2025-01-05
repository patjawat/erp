<div class="card">
    <div class="card-body">
<h6><i class="bi bi-ui-checks"></i> ตารางประเภทหนังสือแยกตามหน่วยงานที่ส่งมา 10 อันดับ</h6>
<table class="table table-bordered table-striped table-hover">
      
    <thead>
        <tr>
            <th scope="col" class="text-center fw-semibold">#</th>
            <th scope="col" class="fw-semibold">จากหน่วยงาน</th>
            <th scope="col" class="text-center fw-semibold">หนังสือภายนอก</th>
            <th scope="col" class="text-center fw-semibold">หนังสือภายใน</th>
            <th scope="col" class="text-center fw-semibold">หนังสือประทับตรา</th>
            <th scope="col" class="text-center fw-semibold">หนังสือประชาสัมพันธ์</th>
            <th scope="col" class="text-center fw-semibold">หนังสือขอประวัติ</th>
            <th scope="col" class="text-center fw-semibold">หนังสือคำสั่ง</th>
            <th scope="col" class="text-center fw-semibold">อื่น ๆ</th>
            <th scope="col" class="text-center fw-semibold">รวมทั้งหมด</th>
        </tr>
    </thead>
    <tbody class="table-group-divider">
        <?php foreach($model->summaryOrg() as $key => $item):?>
            <tr class="">
                <td scope="row" class="text-center"><?php echo $key + 1?></td>
                <td><?php echo $item['org_name']?></td>
                <td class="text-center fw-semibold"><?php echo $item['DT1']?></td>
                <td class="text-center fw-semibold"><?php echo $item['DT2']?></td>
                <td class="text-center fw-semibold"><?php echo $item['DT3']?></td>
                <td class="text-center fw-semibold"><?php echo $item['DT5']?></td>
                <td class="text-center fw-semibold"><?php echo $item['DT8']?></td>
                <td class="text-center fw-semibold"><?php echo $item['DT9']?></td>
            
                <td class="text-center fw-semibold"><?php echo $item['other_count']?></td>
                <td class="text-center fw-semibold"><?php echo $item['total_count']?></td>
            </tr>
           <?php endforeach;?>
        </tbody>
    </table>
    </div>
</div>


<!-- SELECT o.title,thai_year,
 COUNT(CASE WHEN MONTH(doc_date) = 1 THEN 1 END) AS m1
FROM `documents` 
LEFT JOIN categorise o ON o.code = `documents`.document_org AND o.name = 'document_org'
WHERE document_group = 'receive'
GROUP BY o.title limit 10; -->