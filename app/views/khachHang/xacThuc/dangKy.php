<?php 
require_once APPROOT . '/views/layouts/partials/header.php'; 
?>

<div class="wrap">
  <!-- LEFT BRAND PANEL -->
  <div class="brand-panel">
    <div class="brand-logo">
      <div class="logo-icon">
        <div class="icon icon-plus icon-24">
          <div class="bar-h"></div>
          <div class="bar-v"></div>
        </div>
      </div>
      <div>
        <div class="logo-text">PharmaCare</div>
        <div class="logo-sub">Nhà thuốc trực tuyến</div>
      </div>
    </div>

    <div class="brand-hero">
      <h1>Chăm sóc sức khoẻ<br>cho cả gia đình bạn</h1>
      <p>Đặt thuốc nhanh chóng, giao hàng tận nơi và được dược sĩ tư vấn miễn phí — mọi lúc, mọi nơi.</p>

      <div class="brand-features">
        <div class="brand-feature">
          <div class="f-icon">
            <div class="icon icon-truck icon-16">
              <div class="box"></div><div class="cab"></div><div class="wheel l"></div><div class="wheel r"></div>
            </div>
          </div>
          Giao hàng nhanh trong 2 giờ
        </div>
        <div class="brand-feature">
          <div class="f-icon">
            <div class="icon icon-shield icon-16">
              <div class="plate"></div><div class="check"></div>
            </div>
          </div>
          100% thuốc chính hãng, rõ nguồn gốc
        </div>
        <div class="brand-feature">
          <div class="f-icon">
            <div class="icon icon-message icon-16">
              <div class="bubble"></div><div class="tail"></div>
            </div>
          </div>
          Dược sĩ tư vấn miễn phí 24/7
        </div>
      </div>
    </div>

    <div class="brand-footer">© 2026 PharmaCare. Đơn vị nhà thuốc trực tuyến uy tín.</div>
  </div>

  <!-- RIGHT FORM PANEL -->
  <div class="form-panel">
    <div class="form-box">

      <div class="mobile-logo">
        <div class="logo-icon">
          <div class="icon icon-plus icon-20">
            <div class="bar-h"></div>
            <div class="bar-v"></div>
          </div>
        </div>
        <div class="logo-text">PharmaCare</div>
      </div>

      <h2 class="form-title">Tạo tài khoản mới</h2>
      <p class="form-subtitle">Đăng ký để nhận ưu đãi và theo dõi đơn hàng dễ dàng</p>

      <?php if (isset($error)): ?>
        <div style="background-color: #fef2f2; color: #dc2626; padding: 12px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; border: 1px solid #fca5a5;">
            <?= $error; ?>
        </div>
      <?php endif; ?>

      <form action="<?php echo URLROOT; ?>/khachHang/xacThuc/xuLyDangKy" method="POST">
          <div class="field">
            <label>Họ và tên</label>
            <div class="input-wrap">
              <div class="icon icon-user icon-14"><div class="head"></div><div class="body"></div></div>
              <input type="text" name="hoTen" value="<?= isset($_POST['hoTen']) ? htmlspecialchars($_POST['hoTen']) : '' ?>" required placeholder="Nguyễn Văn A">
            </div>
          </div>

          <div class="field">
            <label>Email</label>
            <div class="input-wrap">
              <div class="icon icon-message icon-14"><div class="bubble"></div><div class="tail"></div></div>
              <input type="email" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required placeholder="example@gmail.com">
            </div>
          </div>

          <div class="field">
            <label>Số điện thoại</label>
            <div class="input-wrap">
              <div class="icon icon-phone icon-16"><div class="handset"></div></div>
              <input type="tel" name="soDienThoai" value="<?= isset($_POST['soDienThoai']) ? htmlspecialchars($_POST['soDienThoai']) : '' ?>" required placeholder="09xx xxx xxx">
            </div>
          </div>

          <div class="field">
            <label>Mật khẩu</label>
            <div class="input-wrap">
              <div class="icon icon-lock icon-14"><div class="shackle"></div><div class="body"></div></div>
              <input type="password" name="matKhau" required placeholder="Tối thiểu 6 ký tự">
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Tạo tài khoản</button>
      </form>

      <div class="switch-note">Đã có tài khoản? <a href="<?php echo URLROOT; ?>/khachHang/xacThuc/dangNhap">Đăng nhập</a></div>

    </div>
  </div>
</div>

<?php 
require_once APPROOT . '/views/layouts/partials/footer.php'; 
?>