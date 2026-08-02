<div class="content" id="contentRoot">
</div>

<script>
    // Ảnh mặc định (dạng SVG placeholder) dùng khi thuốc không có ảnh nào
    var PLACEHOLDER_IMG = 'data:image/svg+xml;utf8,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" rx="14" fill="%23e9edf2"/><g fill="none" stroke="%237c869a" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><rect x="30" y="22" width="40" height="56" rx="8"/><path d="M42 40h16M42 50h16M42 60h10"/></g></svg>');

    /**
     * Định dạng một số tiền sang chuỗi tiền tệ Việt Nam (ví dụ: 15000 -> "15.000đ").
     * @param {number} n - Số tiền cần định dạng
     * @returns {string} Chuỗi tiền đã định dạng, có hậu tố "đ"
     */
    function dinhDangTien(n) {
        // n || 0 giúp tránh lỗi khi n là null/undefined/NaN
        return Number(n || 0).toLocaleString('vi-VN') + 'đ';
    }

    // Chuẩn hóa đường dẫn ảnh từ CSDL (xử lý cả đường dẫn tương đối và tuyệt đối)
    function chuanHoaDuongDanAnh(path) {
        if (!path) return PLACEHOLDER_IMG; // Không có path -> dùng ảnh mặc định
        if (path.indexOf('http') === 0) return path; // Đã là URL đầy đủ -> giữ nguyên
        if (path.indexOf('assets/') === 0) return '<?php echo URLROOT; ?>/' + path; // Đường dẫn tương đối bắt đầu bằng "assets/"
        if (path.indexOf('/assets/') === 0) return '<?php echo URLROOT; ?>' + path; // Đường dẫn tuyệt đối bắt đầu bằng "/assets/"
        if (path.indexOf('/') === 0) return '<?php echo URLROOT; ?>' + path; // Đường dẫn tuyệt đối khác
        return '<?php echo URLROOT; ?>/' + path; // Trường hợp còn lại: ghép URLROOT vào phía trước
    }

    /**
     * Định dạng chuỗi ngày (ISO) sang định dạng ngày Việt Nam (dd/mm/yyyy).
     * @param {string} str - Chuỗi ngày đầu vào
     * @returns {string} Ngày đã định dạng, hoặc "—" nếu rỗng
     */
    function dinhDangNgayVN(str) {
        if (!str) return '—';
        return new Date(str).toLocaleDateString('vi-VN');
    }

    /**
     * Lấy chữ cái đầu của họ và tên (dùng để hiển thị avatar chữ, ví dụ "Nguyễn Văn A" -> "VA").
     * @param {string} name - Họ tên đầy đủ (có thể có tiền tố "DS." - Dược sĩ)
     * @returns {string} Chữ cái viết hoa đại diện cho tên, tối đa 2 ký tự
     */
    function layChuCaiDau(name) {
        if (!name) return '?';
        // Bỏ tiền tố "DS. " (Dược sĩ) nếu có, rồi tách theo khoảng trắng
        var parts = name.replace(/^DS\.\s*/, '').split(' ');
        var result = '';
        if (parts.length >= 2) {
            // Lấy chữ cái đầu của từ áp chót (tên đệm/họ) và từ cuối (tên)
            result = parts[parts.length - 2][0] + parts[parts.length - 1][0];
        } else {
            result = parts[0][0]; // Chỉ có 1 từ -> lấy chữ cái đầu của từ đó
        }
        return result.toUpperCase();
    }

    /**
     * Tính trạng thái hạn sử dụng của một lô thuốc dựa trên ngày hết hạn.
     * Quy tắc: đã qua HSD -> "expired"; còn dưới 30 ngày -> "disabled" (vô hiệu hóa);
     * còn dưới 90 ngày -> "warn" (sắp hết hạn); còn lại -> "active" (còn hạn).
     * @param {string} hanSuDungStr - Chuỗi ngày hạn sử dụng
     * @returns {{code: string, label: string, daysLeft: number}} Đối tượng mô tả trạng thái HSD
     */
    function tinhTrangThaiHan(hanSuDungStr) {
        var hsd = new Date(hanSuDungStr);
        hsd.setHours(0, 0, 0, 0); // Xóa phần giờ để so sánh chỉ theo ngày
        var today = new Date();
        today.setHours(0, 0, 0, 0);
        var daysLeft = Math.round((hsd - today) / 86400000); // Số ngày còn lại (86400000ms = 1 ngày)
        if (daysLeft < 0) return { code: 'expired', label: 'Đã hết hạn', daysLeft: daysLeft };
        if (daysLeft < 30) return { code: 'disabled', label: 'Vô hiệu hóa', daysLeft: daysLeft };
        if (daysLeft < 90) return { code: 'warn', label: 'Sắp hết hạn', daysLeft: daysLeft };
        return { code: 'active', label: 'Còn hạn', daysLeft: daysLeft };
    }

    // Lấy idThuoc từ biến PHP truyền từ controller (ép kiểu int để tránh lỗi injection/parse)
    var idThuoc = <?php echo isset($idThuoc) ? intval($idThuoc) : 0; ?>;

    // Vùng nội dung gốc trên trang, nơi sẽ render toàn bộ chi tiết thuốc
    var root = document.getElementById('contentRoot');

    /**
     * Tải và hiển thị dữ liệu chi tiết của thuốc (thông tin chung, ảnh, các lô hàng)
     * bằng cách gọi API layChiTietDuLieu, sau đó dựng HTML và gán vào #contentRoot.
     */
    function taiChiTiet() {
        // Kiểm tra idThuoc hợp lệ trước khi gọi API
        if (!idThuoc || idThuoc <= 0) {
            root.innerHTML = '<div class="not-found"><div style="font-weight:700;color:var(--gray-700);margin-bottom:6px;">Không tìm thấy thuốc</div><div>Mã ID thuốc không hợp lệ.</div></div>';
            return;
        }

        fetch('<?php echo URLROOT; ?>/admin/quanLyThuoc/layChiTietDuLieu/' + idThuoc)
            .then(function (res) { return res.json(); })
            .then(function (res) {
                // API trả về status = false -> hiển thị thông báo lỗi từ server
                if (!res.status) {
                    root.innerHTML = '<div class="not-found"><div style="font-weight:700;color:var(--gray-700);margin-bottom:6px;">' + res.message + '</div></div>';
                    return;
                }

                var thuoc = res.thuoc;   // Thông tin chung của thuốc
                var lots = res.lots || []; // Danh sách các lô thuốc (nhập kho)
                var images = res.images || []; // Danh sách ảnh của thuốc

                // Xây dựng danh sách ảnh (ưu tiên bảng images, fallback về cột hinhAnh của thuốc)
                var imageList = [];
                if (images.length > 0) {
                    for (var i = 0; i < images.length; i++) {
                        imageList.push(chuanHoaDuongDanAnh(images[i].duongDan));
                    }
                } else if (thuoc.hinhAnh) {
                    imageList.push(chuanHoaDuongDanAnh(thuoc.hinhAnh));
                }

                // Ảnh chính hiển thị to ở đầu gallery, mặc định là ảnh đầu tiên hoặc placeholder
                var mainImg = imageList.length > 0 ? imageList[0] : PLACEHOLDER_IMG;

                /* ===== Xây dựng khối Gallery (ảnh lớn + danh sách thumbnail) ===== */
                var galleryHtml = '';
                galleryHtml += '<div class="gallery-card">';
                galleryHtml += '<div class="gallery-main"><img id="mainImg" src="' + mainImg + '" alt="' + thuoc.tenThuoc + '"></div>';
                galleryHtml += '<div class="gallery-thumbs">';
                for (var j = 0; j < imageList.length; j++) {
                    var cls = j === 0 ? 'active' : ''; // Thumbnail đầu tiên mặc định active
                    // Khi click vào thumbnail: đổi ảnh chính và cập nhật class active
                    galleryHtml += '<img src="' + imageList[j] + '" class="' + cls + '" onclick="document.getElementById(\'mainImg\').src=this.src;document.querySelectorAll(\'.gallery-thumbs img\').forEach(function(i){i.classList.remove(\'active\');});this.classList.add(\'active\');">';
                }
                galleryHtml += '</div>';
                galleryHtml += '</div>';

                /* ===== Xây dựng khối Info card (thông tin chung của thuốc) ===== */
                // trangThai: xử lý linh hoạt vì giá trị có thể là number, string hoặc boolean tùy nguồn dữ liệu
                var trangThai = thuoc.trangThai == 1 || thuoc.trangThai === '1' || thuoc.trangThai === true;
                var statusClass = trangThai ? 'badge-active' : 'badge-inactive'; // Class badge trạng thái bán
                var statusLabel = trangThai ? 'Đang bán' : 'Tạm ngưng'; // Nhãn hiển thị trạng thái bán
                // gioiHanMua = -1 nghĩa là không giới hạn số lượng mua mỗi đơn
                var gioiHanText = thuoc.gioiHanMua == -1 ? 'Không giới hạn' : 'Tối đa ' + thuoc.gioiHanMua + ' ' + thuoc.donViTinh + ' / đơn hàng';

                var infoHtml = '';
                infoHtml += '<div class="info-card">';
                // Mã thuốc hiển thị dạng TH0001, TH0002... (pad 4 chữ số)
                infoHtml += '<span class="idthuoc-tag">Mã thuốc: TH' + String(thuoc.idThuoc).padStart(4, '0') + '</span>';
                infoHtml += '<h1>' + thuoc.tenThuoc + '</h1>';
                infoHtml += '<div class="price-row">' + dinhDangTien(thuoc.giaBan) + ' <span class="unit">/ ' + thuoc.donViTinh + '</span></div>';
                infoHtml += '<div class="spec-grid">';
                infoHtml += '<div class="spec-item"><div class="k">Danh mục</div><div class="v">' + (thuoc.tenDanhMuc || '—') + '</div></div>';
                infoHtml += '<div class="spec-item"><div class="k">Phân loại</div><div class="v">' + thuoc.yeuCauKeDon + '</div></div>';
                infoHtml += '<div class="spec-item"><div class="k">Trạng thái</div><div class="v">' + statusLabel + '</div></div>';
                infoHtml += '<div class="spec-item"><div class="k">Thành phần</div><div class="v">' + thuoc.thanhPhan + '</div></div>';
                infoHtml += '<div class="spec-item"><div class="k">Hàm lượng</div><div class="v">' + (thuoc.hamLuong || '—') + '</div></div>';
                infoHtml += '<div class="spec-item"><div class="k">Đơn vị tính</div><div class="v">' + thuoc.donViTinh + '</div></div>';

                // Tính tổng tồn kho bằng cách cộng dồn soLuongTon của tất cả các lô
                var tongTon = 0;
                for (var k = 0; k < lots.length; k++) {
                    tongTon += parseInt(lots[k].soLuongTon) || 0; // Ép kiểu số nguyên, fallback 0 nếu lỗi
                }
                infoHtml += '<div class="spec-item"><div class="k">Tồn kho hiện tại</div><div class="v">' + tongTon.toLocaleString('vi-VN') + ' ' + thuoc.donViTinh + '</div></div>';
                infoHtml += '<div class="spec-item"><div class="k">Giới hạn mua</div><div class="v">' + gioiHanText + '</div></div>';
                infoHtml += '<div class="spec-item" style="grid-column: span 2;"><div class="k">Số lô đang theo dõi</div><div class="v">' + lots.length + ' lô</div></div>';
                infoHtml += '</div>';

                /* ===== Khối nút hành động: sửa / đổi trạng thái / xóa ===== */
                infoHtml += '<div class="actions-row">';
                infoHtml += '<a href="<?php echo URLROOT; ?>/admin/quanLyThuoc" class="btn btn-primary">';
                infoHtml += '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>';
                infoHtml += 'Chỉnh sửa thuốc';
                infoHtml += '</a>';
                // Nút đổi trạng thái (mở bán / tạm ngưng), gọi hàm doiTrangThai() truyền idThuoc
                infoHtml += '<button class="btn btn-ghost" onclick="doiTrangThai(' + thuoc.idThuoc + ')">';
                infoHtml += '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m4.9 4.9 14.2 14.2"/><path d="M9.9 4.24A10 10 0 0 1 21 12c-.6 1.1-1.3 2.1-2.1 3"/><path d="M6.1 6.1C4.3 7.4 2.9 9.5 2 12c1.6 4 5.5 7 10 7 1.2 0 2.4-.2 3.5-.6"/></svg>';
                infoHtml += (trangThai ? 'Tạm ngưng bán' : 'Mở bán lại');
                infoHtml += '</button>';
                // Nút xóa thuốc, có xác nhận confirm() trước khi gọi xoaThuoc()
                infoHtml += '<button class="btn btn-danger" onclick="if(confirm(\'Bạn có chắc muốn xóa thuốc này?\')) xoaThuoc(' + thuoc.idThuoc + ')">';
                infoHtml += '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>';
                infoHtml += 'Xóa thuốc';
                infoHtml += '</button>';
                infoHtml += '</div>';
                infoHtml += '</div>';

                /* ===== Khối mô tả & công dụng của thuốc ===== */
                var descHtml = '';
                descHtml += '<div class="section-card">';
                descHtml += '<h2><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg>Mô tả & công dụng</h2>';
                descHtml += '<div class="desc-text">' + thuoc.congDung + '</div>';
                descHtml += '</div>';

                /* ===== Khối bảng danh sách các lô thuốc ===== */
                var lotsHtml = '';
                lotsHtml += '<div class="section-card">';
                lotsHtml += '<h2><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v1"/><path d="M3 8h18l-1.2 11.1a2 2 0 0 1-2 1.9H6.2a2 2 0 0 1-2-1.9L3 8Z"/></svg>Danh sách lô thuốc (' + lots.length + ')</h2>';
                lotsHtml += '<div class="table-scroll">';
                lotsHtml += '<table>';
                lotsHtml += '<thead><tr><th>Mã lô</th><th>Ngày SX</th><th>Hạn sử dụng</th><th>SL tồn</th><th>Giá nhập</th><th>Trạng thái</th><th>Dược sĩ thêm lô</th></tr></thead>';
                lotsHtml += '<tbody>';

                if (lots.length === 0) {
                    // Chưa có lô thuốc nào -> hiển thị dòng thông báo trống
                    lotsHtml += '<tr><td colspan="7" style="text-align:center;color:var(--gray-500);padding:30px;">Chưa có lô thuốc nào được nhập cho sản phẩm này.</td></tr>';
                } else {
                    // Sắp xếp các lô theo hạn sử dụng tăng dần (lô sắp hết hạn hiển thị trước)
                    var sortedLots = lots.slice().sort(function(a, b) {
                        return new Date(a.hanSuDung) - new Date(b.hanSuDung);
                    });

                    for (var m = 0; m < sortedLots.length; m++) {
                        var lo = sortedLots[m];
                        var st = tinhTrangThaiHan(lo.hanSuDung); // Trạng thái HSD của lô hiện tại
                        // Chọn class CSS cho pill hiển thị hạn sử dụng dựa theo mã trạng thái
                        var pillCls = 'hsd-ok';
                        if (st.code === 'warn') pillCls = 'hsd-warn';
                        else if (st.code === 'disabled') pillCls = 'hsd-danger';
                        else if (st.code === 'expired') pillCls = 'hsd-expired';

                        // Bảng ánh xạ mã trạng thái -> [class badge, nhãn hiển thị]
                        var badgeMap = {
                            active: ['badge-active', 'Còn hạn'],
                            warn: ['badge-warn', 'Sắp hết hạn'],
                            disabled: ['badge-danger', 'Vô hiệu hóa'],
                            expired: ['badge-expired', 'Đã hết hạn']
                        };
                        var bd = badgeMap[st.code];

                        lotsHtml += '<tr>';
                        lotsHtml += '<td class="cell-mono cell-strong">' + lo.maLo + '</td>';
                        lotsHtml += '<td>' + dinhDangNgayVN(lo.ngaySanXuat) + '</td>';
                        lotsHtml += '<td><span class="hsd-pill ' + pillCls + '">' + dinhDangNgayVN(lo.hanSuDung) + '</span></td>';
                        lotsHtml += '<td class="cell-strong">' + (parseInt(lo.soLuongTon) || 0).toLocaleString('vi-VN') + '</td>';
                        lotsHtml += '<td>' + dinhDangTien(lo.giaNhap) + '</td>';
                        lotsHtml += '<td><span class="badge ' + bd[0] + '">' + bd[1] + '</span></td>';
                        lotsHtml += '<td>';
                        // Dược sĩ phụ trách nhập lô - chỉ hiển thị nếu dữ liệu có kèm thông tin dược sĩ
                        if (lo.idDuocSi && lo.hoTen) {
                            lotsHtml += '<div class="ds-chip"><div class="ds-avatar">' + layChuCaiDau(lo.hoTen) + '</div>';
                            lotsHtml += '<div><div class="cell-strong" style="font-size:12.8px;">' + lo.hoTen + '</div>';
                            lotsHtml += '<div class="cell-sub">' + (lo.chungChiHanhNghe || '') + '</div></div></div>';
                        } else {
                            lotsHtml += '<span class="cell-sub">—</span>'; // Không có dữ liệu dược sĩ
                        }
                        lotsHtml += '</td>';
                        lotsHtml += '</tr>';
                    }
                }

                lotsHtml += '</tbody></table></div></div>';

                // Gộp toàn bộ các khối HTML và render vào vùng nội dung chính
                root.innerHTML = '<div class="detail-layout">' + galleryHtml + infoHtml + '</div>' + descHtml + lotsHtml;
            })
            .catch(function (err) {
                // Lỗi mạng / lỗi gọi API -> log ra console và hiển thị thông báo cho người dùng
                console.error('Lỗi tải dữ liệu:', err);
                root.innerHTML = '<div class="not-found"><div style="font-weight:700;color:var(--gray-700);margin-bottom:6px;">Lỗi kết nối</div><div>Không thể tải dữ liệu chi tiết thuốc. Vui lòng thử lại.</div></div>';
            });
    }

    // Tải dữ liệu chi tiết khi trang load
    taiChiTiet();

    /**
     * Gửi yêu cầu đổi trạng thái (đang bán / tạm ngưng) của một thuốc lên server,
     * sau khi người dùng xác nhận, rồi tải lại dữ liệu để cập nhật giao diện.
     * @param {number} id - idThuoc cần đổi trạng thái
     */
    function doiTrangThai(id) {
        if (confirm('Xác nhận thay đổi trạng thái mở bán / tạm ngưng của mặt hàng thuốc này?')) {
            fetch('<?php echo URLROOT; ?>/admin/quanLyThuoc/doiTrangThai/' + id, {
                method: 'POST',
                headers: { 'Cache-Control': 'no-cache' } // Đảm bảo không lấy kết quả cũ từ cache trình duyệt
            })
                .then(function (res) { return res.json(); })
                .then(function (res) {
                    if (res.status) {
                        alert(res.message);
                        // Cập nhật lại giao diện ngay mà không cần reload trang
                        taiChiTiet();
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

    /**
     * Xóa một thuốc khỏi hệ thống.
     * @param {number} id - idThuoc cần xóa
     * @todo Chức năng đang được phát triển, hiện chỉ hiển thị thông báo tạm.
     */
    function xoaThuoc(id) {
        alert('Chức năng xóa thuốc đang phát triển.');
    }
</script>
