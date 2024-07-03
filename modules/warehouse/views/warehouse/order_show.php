<div class="d-flex justify-content-between align-items-center">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link active" data-bs-toggle="tab" href="#checker" aria-selected="true" role="tab"><span
                    class="badge rounded-pill bg-primary text-white">4</span> ขอเบิกวัสดุ</a>
        </li>

        <li class="nav-item" role="presentation">
            <a class="nav-link" data-bs-toggle="tab" href="#po-detail" aria-selected="false" tabindex="-1"
                role="tab"><span class="badge rounded-pill bg-primary text-white">3</span> หน.เห็นชอบ</a>
        </li>


        <li class="nav-item" role="presentation">
            <a class="nav-link" data-bs-toggle="tab" href="#pq-detail" aria-selected="false" tabindex="-1"
                role="tab"><span class="badge rounded-pill bg-primary text-white">2</span> ครวจสอลผ่าน</a>
        </li>

        <li class="nav-item" role="presentation">
            <a class="nav-link" data-bs-toggle="tab" href="#pr-detail" aria-selected="false" tabindex="-1"
                role="tab"><span class="badge rounded-pill bg-primary text-white">1</span> อนุมัติ</a>
        </li>
    </ul>
    <a class="btn btn-light mb-4 open-modal"
        href="/purchase/order/document?id=1&amp;" data-size="modal-md"><i class="fa-solid fa-print"></i> พิมพ์เอกสาร</a>
</div>


<div class="tab-content mt-3">
    <div class="tab-pane " id="importStore">
        <div class="d-flex justify-content-between align-items-center">
            <h1>รับเข้าคลัง</h1>
            <a class="btn btn-primary rounded shadow pr-confirm"
                href="/purchase/order/confirm-status?id=1&amp;status=6"><i class="fa-solid fa-circle-exclamation"></i>
                รับเข้าคลัง</a>
        </div>
    </div>

    <div id="sm-container" data-pjax-container="" data-pjax-push-state="" data-pjax-timeout="1000">



    </div>
    <!-- ใบสั่งซื้อ -->
    <div class="tab-pane" id="po-detail" role="tabpanel">
    </div>
    <!-- จบใบขอซื้อ -->

    <!-- ทะเบียนคุม -->
    <div class="tab-pane" id="pq-detail" role="tabpanel">

    </div>
    <!-- จบทะเบียนคุม -->
    <!-- End Tabs1 -->

</div>