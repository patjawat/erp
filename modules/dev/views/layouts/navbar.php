<?php
use yii\helpers\Url;

// รับค่า Controller ID เพื่อเช็คว่าเมนูไหนกำลัง Active
// (ถ้า $c ถูกส่งมาจาก main.php แล้ว ก็ใช้ได้เลย หรือเรียกใหม่เพื่อความชัวร์ก็ได้)
$c = Yii::$app->controller->id; 
?>

<?php 
// ตรวจสอบว่ามีข้อมูล $menuItems ส่งมาหรือไม่ (เพื่อป้องกัน Error)
if (!empty($menuItems)): 
?>
    <?php foreach ($menuItems as $item): ?>
        <?php 
            // เช็คว่าเมนูนี้ Active หรือไม่
            $isActive = ($c === $item['active']); 
        ?>
        
        <a href="<?= Url::to($item['url']) ?>" class="erp-nav-item <?= $isActive ? 'active' : '' ?>">
            <div class="erp-icon-box">
                <?= $item['icon'] ?> </div>
            <span class="erp-nav-text"><?= $item['label'] ?></span>
        </a>
        
    <?php endforeach; ?>
<?php endif; ?>