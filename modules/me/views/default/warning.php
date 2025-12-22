<style>
    .error-code {
        font-size: 8rem;
        font-weight: 800;
        line-height: 1;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 1rem;
    }

    .error-card {
        border: none;
        border-radius: 24px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .status-badge {
        background-color: #fff7ed;
        color: #ea580c;
        font-weight: 700;
        padding: 0.5rem 1.25rem;
        border-radius: 99px;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }
</style>
<div class="container">


    <div class="card maintenance-card mx-auto p-4 p-md-5 text-center">
        <div class="card-body">
            <div class="icon-box">
                <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-lock-icon lucide-user-lock">
                    <circle cx="10" cy="7" r="4" />
                    <path d="M10.3 15H7a4 4 0 0 0-4 4v2" />
                    <path d="M15 15.5V14a2 2 0 0 1 4 0v1.5" />
                    <rect width="8" height="5" x="13" y="16" rx=".899" />
                </svg>
            </div>

            <h2 class="fw-bold text-dark mb-3">ขออภัยในความไม่สะดวก</h2>
            <h5 class="text-secondary mb-4">
                เนื่องจาก ระบบตรวจพบว่าบัญชีของคุณ <strong>ยังไม่ได้ถูกตั้งค่าตำแหน่งบุคลากร</strong>
            </h5>
            <p class="text-secondary mb-4">
                กรุณาติดต่อฝ่ายบุคคล (HR) หรือผู้ดูแลระบบเพื่อระบุตำแหน่งก่อนเริ่มใช้งาน
            </p>


        </div>
    </div>
</div>