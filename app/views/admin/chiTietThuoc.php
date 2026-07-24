<div class="content" id="contentRoot">
</div>

<script>
    var PLACEHOLDER_IMG = 'data:image/svg+xml;utf8,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" rx="14" fill="%23e9edf2"/><g fill="none" stroke="%237c869a" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><rect x="30" y="22" width="40" height="56" rx="8"/><path d="M42 40h16M42 50h16M42 60h10"/></g></svg>');

    function fmtMoney(n) {
        return Number(n || 0).toLocaleString('vi-VN') + 'đ';
    }

    // Chuẩn hóa đường dẫn ảnh từ CSDL (xử lý cả đường dẫn tương đối và tuyệt đối)
    function normalizeImgPath(path) {
        if (!path) return PLACEHOLDER_IMG;
        if (path.indexOf('http') === 0) return path;
        if (path.indexOf('assets/') === 0) return '<?php echo URLROOT; ?>/' + path;
        if (path.indexOf('/assets/') === 0) return '<?php echo URLROOT; ?>' + path;
        if (path.indexOf('/') === 0) return '<?php echo URLROOT; ?>' + path;
        return '<?php echo URLROOT; ?>/' + path;
    }

    function fmtDateVN(str) {
        if (!str) return '—';
        return new Date(str).toLocaleDateString('vi-VN');
    }

    function initials(name) {
        if (!name) return '?';
        var parts = name.replace(/^DS\.\s*/, '').split(' ');
        var result = '';
        if (parts.length >= 2) {
            result = parts[parts.length - 2][0] + parts[parts.length - 1][0];
        } else {
            result = parts[0][0];
        }
        return result.toUpperCase();
    }

    function tinhTrangThaiHan(hanSuDungStr) {
        var hsd = new Date(hanSuDungStr);
        hsd.setHours(0, 0, 0, 0);
        var today = new Date();
        today.setHours(0, 0, 0, 0);
        var daysLeft = Math.round((hsd - today) / 86400000);
        if (daysLeft < 0) return { code: 'expired', label: 'Đã hết hạn', daysLeft: daysLeft };
        if (daysLeft < 30) return { code: 'disabled', label: 'Vô hiệu hóa', daysLeft: daysLeft };
        if (daysLeft < 90) return { code: 'warn', label: 'Sắp hết hạn', daysLeft: daysLeft };
        return { code: 'active', label: 'Còn hạn', daysLeft: daysLeft };
    }

    // Lấy idThuoc từ biến PHP truyền từ controller
    var idThuoc = <?php echo isset($idThuoc) ? intval($idThuoc) : 0; ?>;

    var root = document.getElementById('contentRoot');

    function loadDetail() {
        if (!idThuoc || idThuoc <= 0) {
            root.innerHTML = '<div class="not-found"><div style="font-weight:700;color:var(--gray-700);margin-bottom:6px;">Không tìm thấy thuốc</div><div>Mã ID thuốc không hợp lệ.</div></div>';
            return;
        }

        fetch('<?php echo URLROOT; ?>/admin/quanLyThuoc/getDetailData/' + idThuoc)
            .then(function (res) { return res.json(); })
            .then(function (res) {
                if (!res.status) {
                    root.innerHTML = '<div class="not-found"><div style="font-weight:700;color:var(--gray-700);margin-bottom:6px;">' + res.message + '</div></div>';
                    return;
                }

                var thuoc = res.thuoc;
                var lots = res.lots || [];
                var images = res.images || [];

                // Xây dựng danh sách ảnh
                var imageList = [];
                if (images.length > 0) {
                    for (var i = 0; i < images.length; i++) {
                        imageList.push(normalizeImgPath(images[i].duongDan));
                    }
                } else if (thuoc.hinhAnh) {
                    imageList.push(normalizeImgPath(thuoc.hinhAnh));
                }

                var mainImg = imageList.length > 0 ? imageList[0] : PLACEHOLDER_IMG;

                // Gallery
                var galleryHtml = '';
                galleryHtml += '<div class="gallery-card">';
                galleryHtml += '<div class="gallery-main"><img id="mainImg" src="' + mainImg + '" alt="' + thuoc.tenThuoc + '"></div>';
                galleryHtml += '<div class="gallery-thumbs">';
                for (var j = 0; j < imageList.length; j++) {
                    var cls = j === 0 ? 'active' : '';
                    galleryHtml += '<img src="' + imageList[j] + '" class="' + cls + '" onclick="document.getElementById(\'mainImg\').src=this.src;document.querySelectorAll(\'.gallery-thumbs img\').forEach(function(i){i.classList.remove(\'active\');});this.classList.add(\'active\');">';
                }
                galleryHtml += '</div>';
                galleryHtml += '</div>';

                // Info card
                var trangThai = thuoc.trangThai == 1 || thuoc.trangThai === '1' || thuoc.trangThai === true;
                var statusClass = trangThai ? 'badge-active' : 'badge-inactive';
                var statusLabel = trangThai ? 'Đang bán' : 'Tạm ngưng';
                var gioiHanText = thuoc.gioiHanMua == -1 ? 'Không giới hạn' : 'Tối đa ' + thuoc.gioiHanMua + ' ' + thuoc.donViTinh + ' / đơn hàng';

                var infoHtml = '';
                infoHtml += '<div class="info-card">';
                infoHtml += '<span class="idthuoc-tag">Mã thuốc: TH' + String(thuoc.idThuoc).padStart(4, '0') + '</span>';
                infoHtml += '<h1>' + thuoc.tenThuoc + '</h1>';
                infoHtml += '<div class="price-row">' + fmtMoney(thuoc.giaBan) + ' <span class="unit">/ ' + thuoc.donViTinh + '</span></div>';
                infoHtml += '<div class="spec-grid">';
                infoHtml += '<div class="spec-item"><div class="k">Danh mục</div><div class="v">' + (thuoc.tenDanhMuc || '—') + '</div></div>';
                infoHtml += '<div class="spec-item"><div class="k">Phân loại</div><div class="v">' + thuoc.yeuCauKeDon + '</div></div>';
                infoHtml += '<div class="spec-item"><div class="k">Trạng thái</div><div class="v">' + statusLabel + '</div></div>';
                infoHtml += '<div class="spec-item"><div class="k">Thành phần</div><div class="v">' + thuoc.thanhPhan + '</div></div>';
                infoHtml += '<div class="spec-item"><div class="k">Hàm lượng</div><div class="v">' + (thuoc.hamLuong || '—') + '</div></div>';
                infoHtml += '<div class="spec-item"><div class="k">Đơn vị tính</div><div class="v">' + thuoc.donViTinh + '</div></div>';

                // Tính tổng tồn kho
                var tongTon = 0;
                for (var k = 0; k < lots.length; k++) {
                    tongTon += parseInt(lots[k].soLuongTon) || 0;
                }
                infoHtml += '<div class="spec-item"><div class="k">Tồn kho hiện tại</div><div class="v">' + tongTon.toLocaleString('vi-VN') + ' ' + thuoc.donViTinh + '</div></div>';
                infoHtml += '<div class="spec-item"><div class="k">Giới hạn mua</div><div class="v">' + gioiHanText + '</div></div>';
                infoHtml += '<div class="spec-item" style="grid-column: span 2;"><div class="k">Số lô đang theo dõi</div><div class="v">' + lots.length + ' lô</div></div>';
                infoHtml += '</div>';

                // Actions
                infoHtml += '<div class="actions-row">';
                infoHtml += '<a href="<?php echo URLROOT; ?>/admin/quanLyThuoc" class="btn btn-primary">';
                infoHtml += '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>';
                infoHtml += 'Chỉnh sửa thuốc';
                infoHtml += '</a>';
                infoHtml += '<button class="btn btn-ghost" onclick="toggleStatus(' + thuoc.idThuoc + ')">';
                infoHtml += '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m4.9 4.9 14.2 14.2"/><path d="M9.9 4.24A10 10 0 0 1 21 12c-.6 1.1-1.3 2.1-2.1 3"/><path d="M6.1 6.1C4.3 7.4 2.9 9.5 2 12c1.6 4 5.5 7 10 7 1.2 0 2.4-.2 3.5-.6"/></svg>';
                infoHtml += (trangThai ? 'Tạm ngưng bán' : 'Mở bán lại');
                infoHtml += '</button>';
                infoHtml += '<button class="btn btn-danger" onclick="if(confirm(\'Bạn có chắc muốn xóa thuốc này?\')) deleteThuoc(' + thuoc.idThuoc + ')">';
                infoHtml += '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>';
                infoHtml += 'Xóa thuốc';
                infoHtml += '</button>';
                infoHtml += '</div>';
                infoHtml += '</div>';

                // Description section
                var descHtml = '';
                descHtml += '<div class="section-card">';
                descHtml += '<h2><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg>Mô tả & công dụng</h2>';
                descHtml += '<div class="desc-text">' + thuoc.congDung + '</div>';
                descHtml += '</div>';

                // Lots section
                var lotsHtml = '';
                lotsHtml += '<div class="section-card">';
                lotsHtml += '<h2><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v1"/><path d="M3 8h18l-1.2 11.1a2 2 0 0 1-2 1.9H6.2a2 2 0 0 1-2-1.9L3 8Z"/></svg>Danh sách lô thuốc (' + lots.length + ')</h2>';
                lotsHtml += '<div class="table-scroll">';
                lotsHtml += '<table>';
                lotsHtml += '<thead><tr><th>Mã lô</th><th>Ngày SX</th><th>Hạn sử dụng</th><th>SL tồn</th><th>Giá nhập</th><th>Trạng thái</th><th>Dược sĩ thêm lô</th></tr></thead>';
                lotsHtml += '<tbody>';

                if (lots.length === 0) {
                    lotsHtml += '<tr><td colspan="7" style="text-align:center;color:var(--gray-500);padding:30px;">Chưa có lô thuốc nào được nhập cho sản phẩm này.</td></tr>';
                } else {
                    // Sắp xếp theo HSD tăng dần
                    var sortedLots = lots.slice().sort(function(a, b) {
                        return new Date(a.hanSuDung) - new Date(b.hanSuDung);
                    });

                    for (var m = 0; m < sortedLots.length; m++) {
                        var lo = sortedLots[m];
                        var st = tinhTrangThaiHan(lo.hanSuDung);
                        var pillCls = 'hsd-ok';
                        if (st.code === 'warn') pillCls = 'hsd-warn';
                        else if (st.code === 'disabled') pillCls = 'hsd-danger';
                        else if (st.code === 'expired') pillCls = 'hsd-expired';

                        var badgeMap = {
                            active: ['badge-active', 'Còn hạn'],
                            warn: ['badge-warn', 'Sắp hết hạn'],
                            disabled: ['badge-danger', 'Vô hiệu hóa'],
                            expired: ['badge-expired', 'Đã hết hạn']
                        };
                        var bd = badgeMap[st.code];

                        lotsHtml += '<tr>';
                        lotsHtml += '<td class="cell-mono cell-strong">' + lo.maLo + '</td>';
                        lotsHtml += '<td>' + fmtDateVN(lo.ngaySanXuat) + '</td>';
                        lotsHtml += '<td><span class="hsd-pill ' + pillCls + '">' + fmtDateVN(lo.hanSuDung) + '</span></td>';
                        lotsHtml += '<td class="cell-strong">' + (parseInt(lo.soLuongTon) || 0).toLocaleString('vi-VN') + '</td>';
                        lotsHtml += '<td>' + fmtMoney(lo.giaNhap) + '</td>';
                        lotsHtml += '<td><span class="badge ' + bd[0] + '">' + bd[1] + '</span></td>';
                        lotsHtml += '<td>';
                        // Dược sĩ - lấy từ dữ liệu nếu có
                        if (lo.idDuocSi && lo.hoTen) {
                            lotsHtml += '<div class="ds-chip"><div class="ds-avatar">' + initials(lo.hoTen) + '</div>';
                            lotsHtml += '<div><div class="cell-strong" style="font-size:12.8px;">' + lo.hoTen + '</div>';
                            lotsHtml += '<div class="cell-sub">' + (lo.chungChiHanhNghe || '') + '</div></div></div>';
                        } else {
                            lotsHtml += '<span class="cell-sub">—</span>';
                        }
                        lotsHtml += '</td>';
                        lotsHtml += '</tr>';
                    }
                }

                lotsHtml += '</tbody></table></div></div>';

                root.innerHTML = '<div class="detail-layout">' + galleryHtml + infoHtml + '</div>' + descHtml + lotsHtml;
            })
            .catch(function (err) {
                console.error('Lỗi tải dữ liệu:', err);
                root.innerHTML = '<div class="not-found"><div style="font-weight:700;color:var(--gray-700);margin-bottom:6px;">Lỗi kết nối</div><div>Không thể tải dữ liệu chi tiết thuốc. Vui lòng thử lại.</div></div>';
            });
    }

    // Tải dữ liệu chi tiết khi trang load
    loadDetail();

    // Hàm đổi trạng thái nhanh
    function toggleStatus(id) {
        if (confirm('Xác nhận thay đổi trạng thái mở bán / tạm ngưng của mặt hàng thuốc này?')) {
            fetch('<?php echo URLROOT; ?>/admin/quanLyThuoc/toggleStatus/' + id, {
                method: 'POST',
                headers: { 'Cache-Control': 'no-cache' }
            })
                .then(function (res) { return res.json(); })
                .then(function (res) {
                    if (res.status) {
                        alert(res.message);
                        // Cập nhật lại giao diện ngay mà không cần reload trang
                        loadDetail();
                    } else {
                        alert(res.message || 'Thay đổi trạng thái thất bại!');
                    }
                })
                .catch(function (err) {
                    console.error('Lỗi khi đổi trạng thái:', err);
                    alert('Có lỗi kết nối khi đổi trạng thái!');
                });
        }
    }

    // Hàm xóa thuốc
    function deleteThuoc(id) {
        alert('Chức năng xóa thuốc đang phát triển.');
    }
</script>
