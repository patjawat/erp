<style>
    /* คอนเทนเนอร์หลัก */
.timeline {
    position: relative;
    /* max-width: 800px; */
    margin: 20px auto;
    padding: 20px 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* สร้างเส้นแนวตั้งยาวลงมา */
.timeline::before {
    content: '';
    position: absolute;
    left: 20px; /* ระยะห่างจากด้านซ้าย */
    top: 0;
    width: 2px;
    height: 100%;
    background: #e0e0e0;
}

/* กล่องรายการแต่ละจุด */
.timeline-item {
    position: relative;
    margin-bottom: 30px;
    padding-left: 50px; /* เว้นที่ให้เส้นและจุด */
}

/* สร้างจุดวงกลมบนเส้น */
.timeline-item::before {
    content: '';
    position: absolute;
    left: 13px; /* จัดให้กึ่งกลางเส้น (20px - ขนาดจุด) */
    top: 5px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #fff;
    border: 3px solid #007bff; /* สีหลัก */
    z-index: 1;
}

/* วันที่ */
.timeline-date {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: bold;
    margin-bottom: 5px;
}

/* สถานะ / หัวข้อหลัก */
.timeline-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

/* เนื้อหา */
.timeline-body {
    font-size: 0.95rem;
    color: #555;
    background: #f8f9fa;
    padding: 10px 15px;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}

/* ปรับแต่งรายการล่าสุด (ตัวเลือกเสริม) */
.timeline-item:first-child::before {
    background: #007bff; /* ให้จุดแรกเป็นสีทึบ */
}
</style>
<div class="timeline">
    <?php foreach ($lists as $item): ?>
        <div class="timeline-item">
            <div class="timeline-date"><?= $item->viewCreateDateTime() ?></div>
            <div class="timeline-title"><?= $item->status ?></div>
            <div class="timeline-body"><?= $item->title ?></div>
        </div>
    <?php endforeach; ?>
</div>