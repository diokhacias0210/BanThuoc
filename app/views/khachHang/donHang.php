<?php
/**
 * View: Danh sách đơn hàng của khách hàng
 * Chức năng: Hiển thị bảng danh sách toàn bộ đơn hàng mà khách hàng đã đặt,
 * gồm mã đơn, ngày đặt, tổng tiền, trạng thái đơn hàng, và nút xem chi tiết
 * để chuyển sang trang chi tiết đơn hàng tương ứng.
 * Dữ liệu đầu vào: biến $danhSachDonHang (mảng), mỗi phần tử là 1 đơn hàng
 * (idDonHang, ngayDat, tongTien, trangThai) truyền từ Controller xuống View.
 */
?>
<link rel="stylesheet" href="<?= ASSETROOT ?>/css/khachHang/donHang.css">

<div class="container mt-4">

    <h2>Đơn hàng của tôi</h2>

    <table class="table table-bordered table-hover mt-4">

        <thead class="table-primary">

            <tr>

                <th>Mã đơn</th>

                <th>Ngày đặt</th>

                <th>Tổng tiền</th>

                <th>Trạng thái</th>

                <th></th>

            </tr>

        </thead>

        <tbody>

        <?php
        // Duyệt qua từng đơn hàng trong danh sách $danhSachDonHang
        // $donHang: mảng chứa thông tin 1 đơn hàng (idDonHang, ngayDat, tongTien, trangThai)
        foreach($danhSachDonHang as $donHang){ ?>

            <tr>

                <td>

                    <?php // Mã đơn hàng, hiển thị kèm dấu # phía trước ?>
                    #<?= $donHang["idDonHang"] ?>

                </td>

                <td>

                    <?php // Ngày khách hàng đặt đơn hàng ?>
                    <?= $donHang["ngayDat"] ?>

                </td>

                <td>

                    <?php // Tổng tiền của đơn hàng, định dạng theo chuẩn VNĐ (dấu chấm ngăn cách hàng nghìn) ?>
                    <?= number_format($donHang["tongTien"],0,",",".") ?>

                    đ

                </td>

                <td>

                    <?php // Trạng thái hiện tại của đơn hàng (VD: Chờ xử lý, Đang giao, Hoàn thành...) ?>
                    <?= $donHang["trangThai"] ?>

                </td>

                <td>

                    <?php // Nút chuyển sang trang chi tiết của đúng đơn hàng này (theo idDonHang) ?>
                    <a

                    href="<?= URLROOT ?>/khachHang/DonHang/chiTiet/<?= $donHang["idDonHang"] ?>"

                    class="btn btn-primary btn-sm">

                    Xem

                    </a>

                </td>

            </tr>

        <?php } ?>

        </tbody>

    </table>

</div>
