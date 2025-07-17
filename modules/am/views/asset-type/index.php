<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\am\models\AssetItem;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetItemSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */


$this->title = 'ประเภททรัพย์สิน';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title');?>
<i class="bi bi-folder-check fs-1"></i> <?=$this->title;?>
<?php $this->endBlock();?>


<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('@app/modules/am/views/default/menu',['active' => 'setting'])?>
<?php $this->endBlock(); ?>


<div class="card">
        <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="bi bi-ui-checks me-1"></i><?=$this->title;?> <span class="badge bg-light"><?=number_format($dataProvider->getTotalCount(),0)?></span> รายการ</h6>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th class="text-center" scope="col" style="width: 5%">#ลำดับ</th>
                    <th scope="col" style="width: 15%">รหัส</th>
                    <th scope="col" style="width: 70%">ชื่อรายการ</th>
                </tr>
            </thead>
            <tbody class="table-group-divider align-middle">
                <?php foreach($dataProvider->getModels() as $key => $item):?>
                <tr>
                    <td class="text-center fw-semibold"><?php echo (($dataProvider->pagination->offset + 1)+$key)?></td>
                    <td class="fw-semibold text-primary"><?=$item->code?></td>
                    <td><?=$item->title?></td>

                </tr>
                <?php endforeach;?>
            </tbody>

        </table>


        <div class="iq-card-footer text-muted d-flex justify-content-center mt-4">
            <?= yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => [
                    'listOptions' => 'pagination pagination-sm',
                    'class' => 'pagination-sm',
                ],
            ]); ?>
        </div>

    </div>
</div>

<?php
$sql = "INSERT INTO categorise (name,title, code, data_json) VALUES
('asset_group','ที่ดิน', 'LAND', JSON_OBJECT('description', 'ที่ดินและสิทธิในที่ดิน')),
('asset_group','อาคาร', 'BLDG', JSON_OBJECT('description', 'อาคารและสิ่งปลูกสร้างขนาดใหญ่')),
('asset_group','สิ่งปลูกสร้าง', 'CONST', JSON_OBJECT('description', 'โครงสร้างพื้นฐานและสาธารณูปโภค')),
('asset_group','ครุภัณฑ์', 'EQUIP', JSON_OBJECT('description', 'อุปกรณ์และเครื่องมือต่างๆ')),
('asset_group','ครุภัณฑ์ต่ำกว่าเกณฑ์', 'MINOR', JSON_OBJECT('description', 'ครุภัณฑ์มูลค่าต่ำ')),
('asset_group','สินทรัพย์ไม่มีตัวตน', 'INTAN', JSON_OBJECT('description', 'ลิขสิทธิ์ สิทธิบัตร ซอฟต์แวร์')),
('asset_group','วัสดุ', 'MATER', JSON_OBJECT('description', 'วัสดุสิ้นเปลืองและคงคลัง'));
;";

$sqlAssetType = "INSERT INTO categorise 
(name,group_id, title, code, data_json) 
VALUES
('asset_type','EQUIP','ครุภัณฑ์การแพทย์', 'MED', JSON_OBJECT('title_en', 'Medical Equipment', 'description','อุปกรณ์ทางการแพทย์และเครื่องมือรักษาพยาบาล')),
('asset_type','EQUIP','ครุภัณฑ์ไฟฟ้าและวิทยุ', 'ELE', JSON_OBJECT('title_en','Electrical and Radio Equipment', 'description', 'อุปกรณ์ไฟฟ้าและเครื่องมือวิทยุกสารสนเทศ')),
('asset_type','EQUIP','ครุภัณฑ์โรงงาน', 'IND', JSON_OBJECT('title_en','Industrial Equipment', 'description', 'เครื่องจักรและอุปกรณ์ในงานโรงงาน การผลิต')),
('asset_type','EQUIP','ครุภัณฑ์การเกษตร', 'AGR', JSON_OBJECT('title_en','Agricultural Equipment', 'description', 'เครื่องมือและอุปกรณ์ทางการเกษตร')),
('asset_type','EQUIP','ครุภัณฑ์การศึกษา', 'EDU', JSON_OBJECT('title_en','Educational Equipment', 'description', 'อุปกรณ์การเรียนการสอนและวัสดุการศึกษา')),
('asset_type','EQUIP','ครุภัณฑ์คอมพิวเตอร์', 'COM', JSON_OBJECT('title_en','Computer Equipment', 'description', 'เครื่องคอมพิวเตอร์และอุปกรณ์เทคโนโลยีสารสนเทศ')),
('asset_type','EQUIP','ครุภัณฑ์โฆษณาและเผยแพร่', 'ADV', JSON_OBJECT('title_en','Advertising and Publishing Equipment', 'description', 'อุปกรณ์โฆษณา ประชาสัมพันธ์ และเผยแพร่ข้อมูล')),
('asset_type','EQUIP','ครุภัณฑ์งานบ้านงานครัว', 'HOM', JSON_OBJECT('title_en','Household and Kitchen Equipment', 'description', 'อุปกรณ์ใช้ในบ้านและครัว สำหรับงานทั่วไป')),
('asset_type','EQUIP','ครุภัณฑ์ยานพาหนะ', 'VEH', JSON_OBJECT('title_en','Vehicle Equipment', 'description', 'ยานพาหนะและอุปกรณ์การขนส่ง')),
('asset_type','EQUIP','ครุภัณฑ์วิทยาศาสตร์', 'SCI', JSON_OBJECT('title_en','Scientific Equipment', 'description', 'เครื่องมือและอุปกรณ์ทางวิทยาศาสตร์และการวิจัย')),
('asset_type','EQUIP','ครุภัณฑ์สำนักงาน', 'OFF', JSON_OBJECT('title_en','Office Equipment', 'description', 'อุปกรณ์สำนักงานและเครื่องใช้ในการบริหารงาน'));
";

$sqlAssetCategory = "INSERT INTO categorise (name, category_id, title, code, data_json) VALUES
('asset_category','MED','กระตุกไฟฟ้าหัวใจ','DF',JSON_OBJECT('title_en', 'Defibrillation')),
('asset_category','MED','กล้องจุลทรรศน์ในการผ่าตัด','MC',JSON_OBJECT('title_en', 'Microscopy')),
('asset_category','MED','กล้องส่องตรวจวินิจฉัยและรักษา','ES',JSON_OBJECT('title_en', 'Endoscopic examination')),
('asset_category','MED','กายภาพบำบัด','PT',JSON_OBJECT('title_en', 'Physical Therapy')),
('asset_category','MED','คลังเลือด','BB',JSON_OBJECT('title_en', 'Blood Bank')),
('asset_category','MED','ควบคุมการให้สารน้ำ','IP',JSON_OBJECT('title_en', 'Infusion pump')),
('asset_category','MED','โคมไฟผ่าตัด','LO',JSON_OBJECT('title_en', 'Lamp operation')),
('asset_category','MED','จักษุ','EM',JSON_OBJECT('title_en', 'Eye medical')),
('asset_category','MED','จ่ายกลาง','CSSD',JSON_OBJECT('title_en', 'Central Sterile Supply Department')),
('asset_category','MED','จี้ห้ามเลือดและตัดเนื้อเยื่อ','CE',JSON_OBJECT('title_en', 'Cauterization equipment')),
('asset_category','MED','ช่วยหายใจ','RS',JSON_OBJECT('title_en', 'Respiration')),
('asset_category','MED','ชันสูตร','LAB',JSON_OBJECT('title_en', 'Laboratory')),
('asset_category','MED','ตรวจทารกในครรภ์','FT',JSON_OBJECT('title_en', 'Fetus')),
('asset_category','MED','ตรวจรักษาหัวใจและปอด','HL',JSON_OBJECT('title_en', 'Heart Lung')),
('asset_category','MED','ตรวจวินิจฉัยและรักษาสมอง','NE',JSON_OBJECT('title_en', 'Neuro equipment')),
('asset_category','MED','ติดตามการทำงานของหัวใจและสัญญาณชีพ','ME',JSON_OBJECT('title_en', 'Monitor equipment')),
('asset_category','MED','เตียงผ่าตัด-คลอด','OB',JSON_OBJECT('title_en', 'Operation Bed')),
('asset_category','MED','เตียงผู้ป่วย','BP',JSON_OBJECT('title_en', 'Bed patient')),
('asset_category','MED','ไตเทียม','CKD',JSON_OBJECT('title_en', 'Chronic Kidney Disease')),
('asset_category','MED','ทันตกรรม','DE',JSON_OBJECT('title_en', 'Dental equipment')),
('asset_category','MED','ทารกแรกคลอด','NB',JSON_OBJECT('title_en', 'New born')),
('asset_category','MED','ผ่าตัด','OE',JSON_OBJECT('title_en', 'Operation equipment')),
('asset_category','MED','เภสัชกรรม','PHR',JSON_OBJECT('title_en', 'Pharmacy')),
('asset_category','MED','รังสีรักษา','RT',JSON_OBJECT('title_en', 'Radio therapy')),
('asset_category','MED','วิสัญญี','AE',JSON_OBJECT('title_en', 'Anesthesia equipment')),
('asset_category','MED','ศัลยกรรมออร์โธปิดิกส์','ORT',JSON_OBJECT('title_en', 'Orthopedic')),
('asset_category','MED','ศัลยศาสตร์ทางเดินปัสสาวะ','URO',JSON_OBJECT('title_en', 'Urology')),
('asset_category','MED','สนับสนุนการแพทย์','MP',JSON_OBJECT('title_en', 'Medical Support')),
('asset_category','MED','หู คอ จมูก','ENT',JSON_OBJECT('title_en', 'Ear Nose Throat')),
('asset_category','MED','อัลตราซาวด์','US',JSON_OBJECT('title_en', 'Ultrasound')),
('asset_category','MED','เอกซเรย์','XR',JSON_OBJECT('title_en', 'Xray')),
('asset_category','ELE','ไฟฟ้าและวิทยุ','EE',JSON_OBJECT('title_en', 'Electric equipment')),
('asset_category','IND','ช่างซ่อมบำรุง','HT',JSON_OBJECT('title_en', 'Hand Tool')),
('asset_category','AGR','การเกษตร','AC',JSON_OBJECT('title_en', 'Agriculture')),
('asset_category','EDU','การศึกษา','ED',JSON_OBJECT('title_en', 'Education')),
('asset_category','COM','กล้องโทรทัศน์วงจรปิด','CCTV',JSON_OBJECT('title_en', 'Close Circuit Television')),
('asset_category','COM','คอมพิวเตอร์และอุปกรณ์','COM',JSON_OBJECT('title_en', 'Computer')),
('asset_category','ADV','โฆษณาและเผยแพร่','AP',JSON_OBJECT('title_en', 'Advertise and publish')),
('asset_category','HOM','งานบ้านงานครัว','HK',JSON_OBJECT('title_en', 'Housework kitchen work')),
('asset_category','VEH','ยานพาหนะบริการทางการแพทย์','VM',JSON_OBJECT('title_en', 'Vehicle medical')),
('asset_category','VEH','ยานพาหนะและขนส่ง','VT',JSON_OBJECT('title_en', 'Vehicle transport')),
('asset_category','SCI','วิทยาศาสตร์','SC',JSON_OBJECT('title_en', 'Science')),
('asset_category','OFF','สำนักงาน','OFF',JSON_OBJECT('title_en', 'Office')),
('asset_category','OFF','เครื่องปรับอากาศและฟอกอากาศ','AIR',JSON_OBJECT('title_en', 'Air condition'));";
?>

<?php
$sql = "SELECT 
  t.code AS t_code,
  t.title AS t_name,
  cat.code AS cat_code,
  cat.title AS cat_title,
  i.category_id,
  i.code,
  i.title
FROM `categorise` i
LEFT JOIN categorise cat ON cat.code = i.category_id
LEFT JOIN categorise t ON t.code = cat.category_id
WHERE i.name = 'asset_item' 
AND t.name = 'asset_type'
AND cat.name = 'asset_category' LIMIT 10000;";

// ตัวอย่างการ  query asset_item
$sql2 ="SELECT 
i.code,
i.name,
i.title,
asset_category.code as category_code,
asset_category.name as category_name,
asset_category.title as category_title,
asset_category.code as type_code,
asset_category.name as type_name,
asset_category.title as type_title 
FROM `categorise` i
LEFT JOIN categorise asset_category ON asset_category.code = i.category_id AND asset_category.name = 'asset_category'
LEFT JOIN categorise asset_type ON asset_type.code = asset_category.category_id AND asset_type.name = 'asset_type'
WHERE i.`id` = 13865;";

?>

