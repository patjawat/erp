<?php
use yii\helpers\Html;
?>
<div class="card rounded-4 border-0 shadow">
			<div class="card-body">
        <div class="d-flex justify-content-between">

          <h6 class="card-title"><i class="fa-solid fa-user-tag"></i> ข้อมูลพื้นฐาน</h6>
        <?=Html::a('<i class="fa-regular fa-pen-to-square"></i>',['update','id' => $model->id],['class' => 'open-modal'])?>
        </div>

<div class="row p-sm-3 p-0">
          <div class="col-xl-6 col-md-12 col-sm-5 col-12 mb-xl-0 mb-md-4 mb-sm-0 mb-4">
          <table>
              <tbody>
                <tr>
                  <td class="pe-3">ชื่อ-สกุล(EN):</td>
                  <td><?=$model->fullname_en?></td>
                </tr>
                <tr>
                  <td class="pe-3">วันเกิด:</td>
                  <td><?=$model->birthday?></td>
                </tr>
                <tr>
                  <td class="pe-3">เลขบัตรประชาชน:</td>
                  <td><?=$model->cid?></td>
                </tr>
                <tr>
                  <td class="pe-3">ภูมิลำเนาเดิม:</td>
                  <td><?=isset($model->data_json['hometown']) ? $model->data_json['hometown'] : '-'?></td>
                </tr>
                <tr>
                  <td class="pe-3">สถานภาพสมรส:</td>
                  <td><?=isset($model->data_json['marital_status']) ? $model->data_json['marital_status'] : '-'?></td>
                </tr>
                <tr>
                  <td class="pe-3">เชื้อชาติ:</td>
                  <td><?=isset($model->data_json['ethnicity']) ? $model->data_json['ethnicity'] : '-'?></td>
                </tr>
                <tr>
                  <td class="pe-3">สัญชาติ:</td>
                  <td><?=isset($model->data_json['nationality']) ? $model->data_json['nationality'] : '-' ?></td>
                </tr>
                <tr>
                  <td class="pe-3">ศาสนา:</td>
                  <td><?=isset($model->data_json['religion']) ? $model->data_json['religion'] : '-'?></td>
                </tr>
                
              </tbody>
            </table>
          </div>
          <div class="col-xl-6 col-md-12 col-sm-7 col-12">
            <table>
              <tbody>
                <tr>
                  <td class="pe-3">ที่อยู่:</td>
                  <td><?=$model->address?></td>
                </tr>
                <tr>
                  <td class="pe-3"></td>
                  <td><?=$model->fulladdress?></td>
                </tr>
                <tr>
                  <td class="pe-3">โทรศัพท์:</td>
                  <td><?=$model->phone?></td>
                </tr>
                <tr>
                  <td class="pe-3">อีเมล:</td>
                  <td><?=$model->email?></td>
                </tr>
                <tr>
                  <td class="pe-3">IBAN:</td>
                  <td>ETD95476213874685</td>
                </tr>
                <tr>
                  <td class="pe-3">หมายเลขบัญชี:</td>
                  <td>BR91905</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

            </div>
        </div>