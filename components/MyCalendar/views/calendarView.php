<?php

/** @var string $apiUrl URL ที่ส่งมาจากต้นทาง */
/** @var int $maxDisplay จำนวนรายการสูงสุดที่จะโชว์ในช่อง */
?>


<div class="calendar-container shadow-sm border rounded-3 bg-white position-relative">
    <div id="calendarLoading" class="position-absolute w-100 h-100 d-none justify-content-center align-items-center"
        style="background: rgba(255,255,255,0.7); z-index: 10; border-radius: 12px;">
        <div class="text-center">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-2 fw-bold text-primary" style="font-size: 14px;">กำลังโหลดข้อมูล...</div>
        </div>
    </div>

    <div class="calendar-header d-flex justify-content-between align-items-center p-3 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <h2 id="monthDisplay" class="h5 fw-bold mb-0 text-dark"></h2>
            <button class="btn btn-outline-primary btn-sm btn-today" onclick="goToToday()">
                วันนี้
            </button>
        </div>
        <div class="btn-group">
            <button class="btn btn-outline-secondary btn-sm border-0" onclick="changeMonth(-1)">
                <i class="bi bi-chevron-left"></i>
            </button>
            <button class="btn btn-outline-secondary btn-sm border-0" onclick="changeMonth(1)">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </div>

    <div class="calendar-body p-3">
        <div class="calendar-grid mb-2">
            <div class="weekday-label">อา.</div>
            <div class="weekday-label">จ.</div>
            <div class="weekday-label">อ.</div>
            <div class="weekday-label">พ.</div>
            <div class="weekday-label">พฤ.</div>
            <div class="weekday-label">ศ.</div>
            <div class="weekday-label">ส.</div>
        </div>
        <div id="calendarDays" class="calendar-grid"></div>
    </div>
</div>
<style>
    /* Container หลัก */
    .calendar-container {
        margin: 0 auto;
        overflow: hidden;
    }

    /* Grid Layout */
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 4px;
        /* ลดช่องว่างลงเล็กน้อยสำหรับหน้าจอเล็ก */
    }

    /* ช่องวันที่ */
    .calendar-day {
        min-height: 120px;
        /* ความสูงมาตรฐานสำหรับ Desktop */
        border: 1px solid #eee;
        border-radius: 8px;
        padding: 6px;
        background: #fff;
        text-decoration: none !important;
        display: flex;
        flex-direction: column;
        transition: all 0.2s;
    }

    .calendar-day:hover {
        background-color: #f8f9fa;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--bs-primary) !important;
        /* เพิ่มเส้นขอบตามที่ต้องการ */
        z-index: 2;
        /* เพื่อให้เส้นขอบไม่ถูกช่องข้างๆ ทับ */
    }

    /* ปรับขนาด Event Tag */
    /* ปรับแต่งในส่วน CSS ของปฏิทิน */
    .event-tag {
        font-size: 10px;
        font-weight: 700;
        padding: 2px 6px;
        margin-bottom: 2px;
        border-radius: 4px;

        /* เทคนิคตัดคำ */
        display: block;
        width: 100%;
        white-space: nowrap;
        /* บังคับบรรทัดเดียว */
        overflow: hidden;
        /* ซ่อนส่วนที่เกิน */
        text-overflow: ellipsis;
        /* เติม ... */

        line-height: 1.4;
        transition: all 0.2s;
    }

    .event-tag:hover {
        filter: brightness(0.9);
        /* ให้เข้มขึ้นนิดหน่อยเวลาชี้ */
        cursor: pointer;
    }


    /* --- Media Queries สำหรับ Tablet (หน้าจอน้อยกว่า 992px) --- */
    @media (max-width: 991.98px) {
        .calendar-day {
            min-height: 90px;
            padding: 4px;
        }

        .day-number {
            font-size: 12px;
        }

        .event-tag {
            font-size: 9px;
            padding: 1px 3px;
        }
    }

    /* --- Media Queries สำหรับ Mobile (หน้าจอน้อยกว่า 576px) --- */
    @media (max-width: 575.98px) {
        .calendar-body {
            padding: 8px;
            /* ลด Padding ขอบข้าง */
        }

        .calendar-grid {
            gap: 2px;
            /* ช่องไฟชิดกันมากขึ้น */
        }

        .calendar-day {
            min-height: 65px;
            /* ลดความสูงลงเพื่อให้เห็นทั้งเดือนในหน้าจอเดียว */
            border-radius: 4px;
            padding: 2px;
        }

        .day-number {
            font-size: 10px;
            margin-bottom: 2px;
        }

        /* บนมือถือ ถ้ามี Event เยอะ เราจะซ่อนชื่อ และโชว์เป็นจุดสีเล็กๆ หรือขีดแทน */
        .event-tag {
            height: 4px;
            font-size: 0;
            /* ซ่อนตัวอักษร */
            padding: 0;
            margin-bottom: 1px;
            border-left: none !important;
            /* เอา Border ออกเพื่อประหยัดที่ */
        }

        .weekday-label {
            font-size: 9px;
            letter-spacing: 0;
        }

        /* ปรับ Header บนมือถือ */
        .calendar-header {
            padding: 10px !important;
            flex-wrap: wrap;
            /* ให้ปุ่มลงมาบรรทัดใหม่ได้ถ้าที่จำกัด */
        }

        .calendar-header h2 {
            font-size: 14px !important;
        }
    }
</style>

<script>
    const apiUrl = '<?= $apiUrl ?>';
    const maxDisplay = <?= $maxDisplay ?? 2 ?>;
    const viewUrl = '<?= \yii\helpers\Url::to(['view-detail']) ?>'; // แก้ไขเป็น Action ที่คืนค่า JSON title/content/footer

    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();
    let events = {};

    const monthNames = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];

    async function fetchEvents(year, month) {
        const loading = document.getElementById('calendarLoading');
        const firstDay = `${year}-${String(month + 1).padStart(2, '0')}-01`;
        const lastDay = `${year}-${String(month + 1).padStart(2, '0')}-${new Date(year, month + 1, 0).getDate()}`;

        // 1. แสดงสถานะกำลังโหลด
        loading.classList.remove('d-none');
        loading.classList.add('d-flex');

        try {
            const response = await fetch(`${apiUrl}${apiUrl.includes('?') ? '&' : '?'}start=${firstDay}&end=${lastDay}`);
            if (!response.ok) throw new Error('Response error');

            events = await response.json();
            renderCalendar(month, year);
        } catch (error) {
            console.error('Fetch error:', error);
            // แสดงผลปฏิทินว่างๆ หากเกิดข้อผิดพลาด
            renderCalendar(month, year);
        } finally {
            // 2. ปิดสถานะกำลังโหลด (ทำใน finally เพื่อให้ปิดเสมอไม่ว่าจะสำเร็จหรือพลาด)
            setTimeout(() => { // ใส่หน่วงเวลานิดหน่อยเพื่อให้ Smooth (optional)
                loading.classList.remove('d-flex');
                loading.classList.add('d-none');
            }, 300);
        }
    }

    // ฟังก์ชันกลับมาเดือนปัจจุบัน
    function goToToday() {
        const now = new Date();
        currentMonth = now.getMonth();
        currentYear = now.getFullYear();
        fetchEvents(currentYear, currentMonth);
    }
    /**
     * ฟังก์ชันวาดปฏิทินลงใน HTML
     * @param {number} month - เดือน (0-11)
     * @param {number} year - ปี ค.ศ. (คริสต์ศักราช)
     */
    function renderCalendar(month, year) {
        const calendarDays = document.getElementById('calendarDays');
        const monthDisplay = document.getElementById('monthDisplay');

        // เตรียมข้อมูลวันที่สำหรับเช็ค "วันนี้" (Today)
        const today = new Date();
        const isCurrentMonth = (today.getMonth() === month && today.getFullYear() === year);
        const todayDate = today.getDate();

        // คำนวณวันแรกของเดือน และจำนวนวันทั้งหมดในเดือนนั้น
        const firstDayIdx = new Date(year, month, 1).getDay(); // 0 = อาทิตย์, 1 = จันทร์...
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        // แสดงชื่อเดือนและ พ.ศ. (ค.ศ. + 543)
        monthDisplay.innerText = `${monthNames[month]} ${year + 543}`;

        // ล้างข้อมูลเก่าใน Grid ออกก่อน
        calendarDays.innerHTML = '';

        // 1. สร้างช่องว่าง (Empty Days) สำหรับวันก่อนหน้าวันที่ 1 ของเดือน
        for (let i = 0; i < firstDayIdx; i++) {
            const emptyDiv = document.createElement('div');
            emptyDiv.className = 'calendar-day empty-day';
            calendarDays.appendChild(emptyDiv);
        }

        // 2. วนลูปสร้างวันที่ 1 ถึงวันสุดท้ายของเดือน
        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const dayEvents = events[dateStr] || [];
            const isToday = (isCurrentMonth && day === todayDate);

            // จัดการ Event Tags (ดึงข้อมูลสี Dynamic จาก Controller)
            let eventTagsHtml = '';
            dayEvents.slice(0, maxDisplay).forEach(ev => {
                const plainText = ev.title.replace(/<[^>]*>?/gm, '');
                eventTagsHtml += `
                <div class="event-tag shadow-sm" 
                    title="${plainText}" 
                    style="background-color: ${ev.bg_color}; color: ${ev.color}; border-left: 3px solid ${ev.color};">
                    ${ev.title}
                </div>`;
            });

            // ถ้ามีกิจกรรมมากกว่าที่กำหนดให้โชว์ (+ อีก x รายการ)
            if (dayEvents.length > maxDisplay) {
                eventTagsHtml += `
                <div class="text-muted fw-bold" style="font-size: 9px; padding-left: 5px; margin-top: 2px;">
                    + อีก ${dayEvents.length - maxDisplay} รายการ
                </div>`;
            }

            // กำหนด Class พิเศษสำหรับวันนี้
            const todayClass = isToday ? 'border-primary bg-primary-subtle' : '';

            // สร้าง HTML สำหรับช่องวันที่
            // - href: ส่งวันที่ไปให้ Controller เพื่อโหลด Modal content
            // - class open-modal: เพื่อให้สคริปต์กลางของระบบคุณทำงาน
            const dayHtml = `
            <a href="${viewUrl}${viewUrl.includes('?') ? '&' : '?'}date=${dateStr}" 
               class="calendar-day open-modal ${todayClass}" 
               data-size="modal-lg"
               style="text-decoration: none; color: inherit;">
                <span class="day-number ${isToday ? 'text-primary' : ''}">${day}</span>
                <div class="d-flex flex-column gap-1 overflow-hidden">
                    ${eventTagsHtml}
                </div>
            </a>
        `;

            calendarDays.insertAdjacentHTML('beforeend', dayHtml);
        }
    }

    function changeMonth(step) {
        currentMonth += step;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        } else if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        fetchEvents(currentYear, currentMonth);
    }

    // โหลดครั้งแรก
    document.addEventListener('DOMContentLoaded', () => fetchEvents(currentYear, currentMonth));
</script>