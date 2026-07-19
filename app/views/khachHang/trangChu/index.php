<div class="banner mb-4">
    <div class="p-5 bg-primary text-white rounded">
        <h1>Hệ thống bán thuốc trực tuyến</h1>
        <p>Mua thuốc chính hãng - Giao hàng nhanh - Tư vấn bởi Dược sĩ</p>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form>
            <div class="row">
                <div class="col-md-10">
                    <input type="text" class="form-control" placeholder="Nhập tên thuốc hoặc hoạt chất...">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-success w-100">Tìm kiếm</button>
                </div>
            </div>
        </form>
    </div>
</div>

<h3 class="mb-4">Danh sách thuốc</h3>

<div class="row">
<?php foreach($danhSachThuoc as $thuoc){ ?>
    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
        <div class="card h-100 shadow-sm">
            <img src="<?= ASSETROOT ?>/images/uploads/<?= $thuoc['idThuoc'] ?>/<?= $thuoc['idThuoc'] ?>-1.jpg" class="card-img-top" alt="<?= htmlspecialchars($thuoc['tenThuoc']) ?>">
            <div class="card-body">
                <h5><?= $thuoc['tenThuoc']; ?></h5>
                <p><?= number_format($thuoc['giaBan']); ?> VNĐ</p>
                
                <div class="d-flex flex-column gap-2">
                    <a href="<?= URLROOT ?>/khachHang/Thuoc/chiTiet/<?= $thuoc["idThuoc"] ?>" class="btn btn-primary w-100">
                        Xem chi tiết
                    </a>
                    <a href="<?= URLROOT ?>/khachHang/GioHang/them/<?= $thuoc["idThuoc"] ?>" class="btn btn-success w-100">
                        Thêm vào giỏ hàng
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
</div>