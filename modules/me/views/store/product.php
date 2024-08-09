<div class="card">
    <div class="card-body">
<h6>รายการวัสดุในคลัง</h6>
    </div>
</div>

<table
            class="table table-primary"
        >
        <thead>
            <tr>
                <th>รายการ</th>
                <th>คงเหลือ</th>
            </tr>
        </thead>
            <tbody>
                <?php foreach($dataProvider->getModels() as $item):?>
                <tr class="">
                    <td>
                        <?=$item->product->Avatar()?>
                    </td>
                    <td>
                    <div class="progress">
            <div class="progress-bar progress-bar-striped progress-bar-animated progress-bar-width-1" role="progressbar" aria-label="Bootstrap" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100" style="width: 85%" >85</div>
          </div>
                    </td>
                </tr>
               <?php endforeach;?>
            </tbody>
        </table>

        


        