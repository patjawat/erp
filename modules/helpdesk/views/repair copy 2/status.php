<?php if($model->data_json['status_name'] == 'ร้องขอ'):?>
                <label class="badge rounded-pill text-danger-emphasis bg-danger-subtle py-2 fs-6 align-middle">
                    <i class="fa-regular fa-hourglass-half"></i> ร้องขอ</label>
                <?php else:?>
                <?=$model->data_json['status_name']?>
                <?php endif;?>