<style>
    /* สไตล์สำหรับ Container ของปุ่มลบ */
    .template-wrapper {
        position: relative;
        display: inline-block;
    }

    /* ปุ่มลบ (x) */
    .btn-delete-template {
        position: absolute;
        top: -5px;
        right: -5px;
        width: 18px;
        height: 18px;
        background: #ef4444;
        color: white;
        border-radius: 50%;
        font-size: 10px;
        line-height: 18px;
        text-align: center;
        cursor: pointer;
        display: none; /* ซ่อนไว้ก่อน */
        border: none;
        padding: 0;
        z-index: 10;
    }

    /* แสดงปุ่มลบเมื่อเอาเมาส์มาวางที่ Wrapper */
    .template-wrapper:hover .btn-delete-template {
        display: block;
    }
</style>

<div id="template-container" class="d-flex flex-wrap gap-3 mt-3">
    <?php foreach($data as $item):?>
    <div class="template-wrapper">
        <button class="btn-delete-template btn-delete-action" data-id="<?=$item->id?>">
            <i class="fas fa-times"></i>
        </button>
        
        <button class="btn btn-sm btn-outline-secondary bg-white text-dark text-truncate text-template"
            style="max-width: 250px;">
            <?=$item->title?>
        </button>
    </div>
    <?php endforeach;?>
</div>