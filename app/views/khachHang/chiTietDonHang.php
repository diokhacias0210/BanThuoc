/**
 * View: Chi tiết đơn hàng
 * Chức năng: Hiển thị bảng chi tiết các thuốc trong một đơn hàng của khách hàng,
 * bao gồm tên thuốc, số lượng và đơn giá tương ứng.
 * Dữ liệu đầu vào: biến $chiTiet (mảng) được truyền từ Controller xuống View,
 * mỗi phần tử là 1 dòng chi tiết đơn hàng (tenThuoc, soLuong, donGia).
 */
?>
<link rel="stylesheet" href="<?= ASSETROOT ?>/css/khachHang/donHang.css">

<div class="container mt-4">

<h2>Chi tiết đơn hàng</h2>

<table class="table table-bordered">

<tr>

<th>Thuốc</th>

<th>Số lượng</th>

<th>Đơn giá</th>

</tr>

<?php
// Duyệt qua từng dòng chi tiết đơn hàng trong mảng $chiTiet
// $ct: mảng chứa thông tin 1 dòng chi tiết (tenThuoc, soLuong, donGia)
foreach($chiTiet as $ct){ ?>

<tr>

<td><?= $ct["tenThuoc"] // Tên thuốc của dòng chi tiết hiện tại ?></td>

<td><?= $ct["soLuong"] // Số lượng thuốc được đặt mua ?></td>

<td><?= number_format($ct["donGia"],0,",",".") // Đơn giá, định dạng theo chuẩn VNĐ (dấu chấm ngăn cách hàng nghìn) ?> đ</td>

</tr>

<?php } ?>

</table>

</div>
