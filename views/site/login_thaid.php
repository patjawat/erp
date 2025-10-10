<?php
use yii\helpers\Html;
?>
<div class="container-fluid min-vh-100 d-flex p-0">
  <!-- Left Column - Image and Text -->
  <div class="d-none d-md-flex col-md-6 bg-primary text-white align-items-center justify-content-center">
    <div class="px-4 py-5 text-center">
      <!-- <h1 class="display-5 fw-bold mb-4">ยินดีต้อนรับกลับ</h1> -->
        <?=Html::img('@web/images/logo_new.png',['class' => 'img-fluid', 'style' => 'max-width:400px; height:auto;'])?>
      <p class="lead mb-4">เข้าสู่ระบบเพื่อจัดการโปรเจค และติดตามความคืบหน้าการพัฒนาแอปพลิเคชันของคุณ</p>
      <div class="mb-4">
        <img src="https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&q=80&w=800" alt="Developer workspace" class="img-fluid rounded shadow">
      </div>
      <div class="bg-primary-dark p-3 rounded">
        <p class="fst-italic small">
          "Angular ช่วยให้ทีมของเราพัฒนาแอปพลิเคชันที่ซับซ้อนได้อย่างรวดเร็วและมีประสิทธิภาพ เรามั่นใจในความปลอดภัยและประสิทธิภาพของแอปพลิเคชันของเรา"
        </p>
        <div class="d-flex align-items-center mt-3">
          <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
            <span class="fw-bold">ธ</span>
          </div>
          <div class="ms-2">
            <p class="mb-0 fw-medium">ธนวัฒน์ กุลธนาวัฒน์</p>
            <p class="text-white-50 small mb-0">CEO, AngularApp</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Right Column - Login Form -->
  <div class="col-12 col-md-6 d-flex align-items-center justify-content-center">
    <div class="w-100 px-4 py-5" style="max-width: 420px;">
      <div class="text-center mb-4">
        <h2 class="fw-bold mb-2">เข้าสู่ระบบ</h2>
        <p class="text-muted">กรอกข้อมูลเพื่อเข้าสู่บัญชีของคุณ</p>
      </div>

      <form method="GET" action="/admin/dashboard">
        <!-- Email Input -->
        <div class="mb-3">
          <label for="email" class="form-label">อีเมล</label>
          <div class="input-group">
            <span class="input-group-text">
              <i class="bi bi-envelope"></i>
            </span>
            <input type="email" class="form-control" id="email" name="email" placeholder="อีเมลของคุณ" value="admin@gmail.com" required>
          </div>
        </div>

        <!-- Password Input -->
        <div class="mb-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="password" class="form-label mb-0">รหัสผ่าน</label>
            <a href="/fogotpassword" class="small text-primary">ลืมรหัสผ่าน?</a>
          </div>
          <div class="input-group">
            <span class="input-group-text">
              <i class="bi bi-lock"></i>
            </span>
            <input type="password" class="form-control" id="password" name="password" placeholder="รหัสผ่านของคุณ" value="123456" required>
          </div>
        </div>

        <!-- Remember Me -->
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" value="" id="remember_me" name="remember_me">
          <label class="form-check-label" for="remember_me">
            จดจำฉันไว้ในระบบ
          </label>
        </div>

        <!-- Login Button -->
        <div class="d-grid mb-3">
          <button type="submit" class="btn btn-primary btn-lg">เข้าสู่ระบบ</button>
        </div>
      </form>

      <!-- Divider -->
      <div class="d-flex align-items-center my-3">
        <hr class="flex-grow-1">
        <span class="mx-2 text-muted small">หรือเข้าสู่ระบบด้วย</span>
        <hr class="flex-grow-1">
      </div>

      <!-- Social Login Buttons -->
      <div class="d-grid gap-2 d-md-flex">
        <button class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center mb-2 mb-md-0">
          <i class="bi bi-google me-2"></i> Google
        </button>
        <button class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center">
          <i class="bi bi-facebook me-2"></i> Facebook
        </button>
      </div>

      <!-- Sign Up Link -->
      <div class="text-center mt-4">
        <p class="small text-muted mb-0">
          ยังไม่มีบัญชี? <a href="/register" class="text-primary fw-medium">สมัครสมาชิก</a>
        </p>
      </div>
    </div>
  </div>
</div>
