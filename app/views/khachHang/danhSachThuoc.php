<?php
/**
 * View: Danh sách thuốc
 * Chức năng: Hiển thị lưới sản phẩm thuốc kèm bộ lọc (danh mục, loại kê đơn,
 * khoảng giá) và ô tìm kiếm nhanh theo tên; hỗ trợ phân trang phía client
 * và cho phép thêm nhanh thuốc (không kê đơn) vào giỏ hàng.
 * Dữ liệu đầu vào: $danhMucList (danh mục thuốc hệ thống để render bộ lọc),
 * $thuocList (danh sách toàn bộ thuốc, được đẩy sang JS dưới dạng JSON để
 * xử lý lọc/tìm kiếm/phân trang trực tiếp trên trình duyệt).
 */
?>
<div class="layout">
    <!-- ══ SIDEBAR FILTER ══ -->
    <aside class="filter-box">
        <div class="filter-head">
            <i class="fa-solid fa-sliders"></i> Bộ lọc thuốc
        </div>

        <div class="filter-group">
            <div class="fg-title">Danh mục thuốc hệ thống</div>
            <div class="category-scroll-area">
                <div class="fg-item">
                    <!-- Checkbox "Tất cả sản phẩm", mặc định được chọn -->
                    <input type="checkbox" id="c0" data-catid="all" checked>
                    <label for="c0">Tất cả sản phẩm</label>
                </div>
                <?php if (!empty($danhMucList)): ?>
                    <?php // Render 1 checkbox cho mỗi danh mục thuốc lấy từ $danhMucList ?>
                    <?php foreach ($danhMucList as $dm): ?>
                        <div class="fg-item">
                            <input type="checkbox" id="c_<?php echo $dm['idDanhMuc']; ?>" data-catid="<?php echo $dm['idDanhMuc']; ?>">
                            <label for="c_<?php echo $dm['idDanhMuc']; ?>"><?php echo htmlspecialchars($dm['tenDanhMuc']); ?></label>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="divider-h"></div>

        <div class="filter-group">
            <div class="fg-title">Phân loại đơn thuốc</div>
            <!-- Lọc theo thuốc không kê đơn (OTC) -->
            <div class="fg-item"><input type="checkbox" id="t1" data-rxtype="OTC"><label for="t1">Không kê đơn (OTC)</label></div>
            <!-- Lọc theo thuốc kê đơn (RX) -->
            <div class="fg-item"><input type="checkbox" id="t2" data-rxtype="Kê đơn"><label for="t2">Thuốc kê đơn (RX)</label></div>
        </div>

        <div class="divider-h"></div>

        <div class="filter-group">
            <div class="fg-title">Khoảng giá bán</div>
            <div class="price-inputs">
                <!-- Ô hiển thị giá trị tối thiểu đang chọn (chỉ đọc, cập nhật bằng JS) -->
                <input type="text" id="priceInputMin" value="0đ" readonly>
                <span class="price-sep">—</span>
                <!-- Ô hiển thị giá trị tối đa đang chọn (chỉ đọc, cập nhật bằng JS) -->
                <input type="text" id="priceInputMax" value="200.000đ" readonly>
            </div>
            <div class="price-slider-wrap">
                <div class="price-slider-track"></div>
                <!-- Thanh tô màu thể hiện khoảng giá đang chọn giữa 2 range input -->
                <div class="price-slider-range" id="priceRangeFill"></div>
                <!-- Thanh trượt chọn giá tối thiểu -->
                <input type="range" class="price-slider" id="priceMin" min="0" max="200000" step="2000" value="0">
                <!-- Thanh trượt chọn giá tối đa -->
                <input type="range" class="price-slider" id="priceMax" min="0" max="200000" step="2000" value="200000">
            </div>
            <div class="price-presets">
                <!-- Các mốc giá dựng sẵn (preset), click để set nhanh khoảng giá -->
                <div class="price-tag active" data-min="0" data-max="200000">Tất cả mức giá</div>
                <div class="price-tag" data-min="0" data-max="50000">Dưới 50k</div>
                <div class="price-tag" data-min="50000" data-max="200000">50k - 200k</div>
            </div>
        </div>

        <button class="filter-apply" onclick="apDungBoLoc()">Lọc sản phẩm</button>
    </aside>

    <!-- ══ MAIN CONTENT ══ -->
    <main>
        <div class="content-head">
            <div>
                <div class="content-title" id="contentTitle">Tất cả sản phẩm</div>
                <div class="content-count" id="contentCount">0 sản phẩm</div>
            </div>
        </div>

        <div class="content-search-bar">
            <i class="fa-solid fa-magnifying-glass"></i>
            <!-- Ô tìm kiếm nhanh theo tên thuốc, lọc ngay khi gõ (oninput) -->
            <input type="text" id="localSearchInput" placeholder="Tìm kiếm nhanh tên thuốc..." oninput="xuLyTimKiem()">
        </div>

        <!-- Lưới sản phẩm, được render động bằng hàm hienThiSanPham() -->
        <div class="pgrid" id="productGrid"></div>
        <!-- Khu vực phân trang, được render động bằng hàm hienThiPhanTrang() -->
        <div class="pagination" id="pagination"></div>
    </main>
</div>

<script>
    // Nhận dữ liệu động từ Server PHP
    // rawProducts: toàn bộ danh sách thuốc (dạng JSON) truyền từ Controller xuống,
    // dùng làm nguồn dữ liệu gốc để lọc/tìm kiếm/phân trang phía client
    const rawProducts = <?php echo json_encode(isset($thuocList) ? $thuocList : []); ?>;
    // urlRoot: đường dẫn gốc của website, dùng để dựng các URL gọi API / điều hướng
    const urlRoot = '<?php echo URLROOT; ?>';

    // currentPage: trang hiện tại đang hiển thị trong danh sách sản phẩm
    let currentPage = 1;
    // itemsPerPage: số sản phẩm hiển thị tối đa trên mỗi trang
    const itemsPerPage = 12;
    // currentFilteredList: danh sách sản phẩm sau khi đã áp dụng bộ lọc/tìm kiếm hiện tại
    let currentFilteredList = [];

    const grid = document.getElementById('productGrid');
    const contentTitle = document.getElementById('contentTitle');
    const contentCount = document.getElementById('contentCount');
    // categoryCheckboxes: danh sách các checkbox lọc theo danh mục thuốc
    const categoryCheckboxes = document.querySelectorAll('.fg-item input[data-catid]');
    const paginationElement = document.getElementById('pagination');

    /**
     * Render danh sách thẻ sản phẩm thuốc ra khu vực lưới (#productGrid).
     * @param {Array} items - Danh sách sản phẩm cần hiển thị (đã được phân trang)
     */
    function hienThiSanPham(items) {
        // Nếu không có sản phẩm nào phù hợp thì hiển thị thông báo trống
        if (!items || items.length === 0) {
            grid.innerHTML = `<div style="grid-column: 1/-1; text-align:center; padding:40px 0; color:var(--muted2);">Không tìm thấy sản phẩm thuốc nào phù hợp.</div>`;
            return;
        }

        grid.innerHTML = items.map(p => {
            // isRx: true nếu thuốc thuộc loại kê đơn (yêu cầu đăng kê toa thay vì thêm giỏ trực tiếp)
            const isRx = p.yeuCauKeDon === 'Kê đơn';
            // hetHang: true nếu thuốc đã hết tồn kho
            const hetHang = parseInt(p.tongTon) <= 0;
            // priceFormatted: giá bán đã định dạng theo chuẩn tiền tệ Việt Nam
            const priceFormatted = parseInt(p.giaBan).toLocaleString('vi-VN') + 'đ';
            // detailUrl: đường dẫn tới trang chi tiết của sản phẩm thuốc này
            const detailUrl = `${urlRoot}/khachHang/thuoc/chiTiet/${p.idThuoc}`;

            return `
                <div class="pcard" onclick="window.location.href='${detailUrl}'">
                    <div class="pcard-img">
                        ${isRx ? `<span class="pcard-tag">Kê đơn</span>` : (hetHang ? `<span class="pcard-tag" style="background:#fdecea; color:#c0392b; border:1px solid #f9d6d2;">Hết hàng</span>` : '')}
                        <img src="${p.hinhAnhUrl}" alt="${p.tenThuoc}" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <div class="pcard-body">
                        <div class="pcard-name" title="${p.tenThuoc}">${p.tenThuoc}</div>
                        <div class="pcard-foot">
                            <span class="pcard-price">${priceFormatted}</span>
                            ${isRx ? 
                                `<button type="button" class="btn-view-detail" onclick="event.stopPropagation(); window.location.href='${detailUrl}'">Xem chi tiết</button>` : 
                                (hetHang ? 
                                    `<button type="button" class="add-btn" disabled style="opacity: 0.4; cursor: not-allowed; background: #888780;" title="Sản phẩm tạm hết hàng"><i class="fa-solid fa-ban"></i></button>` : 
                                    `<button type="button" class="add-btn" onclick="event.stopPropagation(); xuLyThemNhanh(${p.idThuoc})" title="Thêm vào giỏ"><i class="fa-solid fa-plus"></i></button>`
                                )
                            }
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    /**
     * Lọc danh sách thuốc theo danh mục, loại kê đơn, khoảng giá và từ khóa tìm kiếm,
     * sau đó cập nhật số lượng, phân trang và render lại lưới sản phẩm.
     */
    function locSanPham() {
        // query: từ khóa tìm kiếm hiện tại (đã chuẩn hóa chữ thường, bỏ khoảng trắng thừa)
        const query = document.getElementById('localSearchInput').value.trim().toLowerCase();

        // 1. Lọc theo danh mục
        // activeCat: checkbox danh mục đang được chọn (chỉ 1 danh mục được chọn tại 1 thời điểm)
        const activeCat = document.querySelector('.fg-item input[data-catid]:checked');
        let base = (activeCat && activeCat.dataset.catid !== 'all') ?
            rawProducts.filter(p => p.idDanhMuc == activeCat.dataset.catid) :
            [...rawProducts];

        // 2. Lọc theo loại kê đơn (OTC / RX)
        // checkedRxTypes: danh sách các loại đơn thuốc đang được tick chọn
        const checkedRxTypes = Array.from(document.querySelectorAll('.fg-item input[data-rxtype]:checked')).map(cb => cb.dataset.rxtype);
        if (checkedRxTypes.length > 0) {
            base = base.filter(p => {
                if (checkedRxTypes.includes('Kê đơn') && p.yeuCauKeDon === 'Kê đơn') return true;
                if (checkedRxTypes.includes('OTC') && p.yeuCauKeDon !== 'Kê đơn') return true;
                return false;
            });
        }

        // 3. Lọc theo giá
        // min, max: khoảng giá đang được chọn trên thanh trượt giá
        const min = parseInt(priceMin.value, 10);
        const max = parseInt(priceMax.value, 10);
        base = base.filter(p => parseInt(p.giaBan) >= min && parseInt(p.giaBan) <= max);

        // 4. Lọc theo tìm kiếm từ khóa
        if (query) {
            base = base.filter(p => p.tenThuoc.toLowerCase().includes(query));
        }

        currentFilteredList = base;
        contentCount.textContent = `${currentFilteredList.length} sản phẩm`;

        // totalPages: tổng số trang dựa trên số sản phẩm sau lọc và số sản phẩm/trang
        const totalPages = Math.ceil(currentFilteredList.length / itemsPerPage);
        // Nếu trang hiện tại vượt quá tổng số trang mới (do lọc làm giảm số sản phẩm) thì đưa về trang cuối
        if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;

        // startIndex: vị trí bắt đầu lấy sản phẩm cho trang hiện tại
        const startIndex = (currentPage - 1) * itemsPerPage;
        const paginatedItems = currentFilteredList.slice(startIndex, startIndex + itemsPerPage);

        hienThiSanPham(paginatedItems);
        hienThiPhanTrang(totalPages);
    }

    /**
     * Xử lý khi người dùng nhập từ khóa vào ô tìm kiếm nhanh: quay về trang 1 và lọc lại.
     */
    function xuLyTimKiem() {
        currentPage = 1;
        locSanPham();
    }

    /**
     * Xử lý khi người dùng bấm nút "Lọc sản phẩm": quay về trang 1 và áp dụng lại bộ lọc.
     */
    function apDungBoLoc() {
        currentPage = 1;
        locSanPham();
    }

    /**
     * Render khu vực phân trang dựa trên tổng số trang hiện có.
     * @param {number} totalPages - Tổng số trang sau khi đã lọc dữ liệu
     */
    function hienThiPhanTrang(totalPages) {
        // Nếu chỉ có 1 trang trở xuống thì không cần hiển thị phân trang
        if (totalPages <= 1) {
            paginationElement.innerHTML = '';
            return;
        }

        // Nút "Trang trước", bị vô hiệu hóa nếu đang ở trang đầu tiên
        let html = `<button class="page-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="chuyenTrang(${currentPage - 1})"><i class="fa-solid fa-angle-left"></i></button>`;
        // Render nút số trang cho từng trang, đánh dấu active cho trang hiện tại
        for (let i = 1; i <= totalPages; i++) {
            html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="chuyenTrang(${i})">${i}</button>`;
        }
        // Nút "Trang sau", bị vô hiệu hóa nếu đang ở trang cuối cùng
        html += `<button class="page-btn" ${currentPage === totalPages ? 'disabled' : ''} onclick="chuyenTrang(${currentPage + 1})"><i class="fa-solid fa-angle-right"></i></button>`;
        paginationElement.innerHTML = html;
    }

    /**
     * Chuyển sang trang sản phẩm được chỉ định và cuộn lên đầu trang.
     * @param {number} p - Số trang cần chuyển đến
     */
    function chuyenTrang(p) {
        currentPage = p;
        locSanPham();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    // Đảm bảo tại 1 thời điểm chỉ có 1 danh mục được chọn (giống radio button)
    categoryCheckboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            if (cb.checked) {
                // Khi 1 danh mục được chọn, bỏ chọn tất cả các danh mục khác
                categoryCheckboxes.forEach(other => {
                    if (other !== cb) other.checked = false;
                });
            } else {
                // Nếu bỏ chọn danh mục hiện tại, tự động quay về "Tất cả sản phẩm"
                document.getElementById('c0').checked = true;
            }
            currentPage = 1;
            locSanPham();
        });
    });

    // Lắng nghe thay đổi checkbox lọc theo loại kê đơn (OTC/RX), lọc lại danh sách khi thay đổi
    document.querySelectorAll('.fg-item input[data-rxtype]').forEach(cb => {
        cb.addEventListener('change', () => {
            currentPage = 1;
            locSanPham();
        });
    });

    /* ══ THANH TRƯỢT GIÁ ══ */
    const priceMin = document.getElementById('priceMin');
    const priceMax = document.getElementById('priceMax');
    const priceInputMin = document.getElementById('priceInputMin');
    const priceInputMax = document.getElementById('priceInputMax');
    const priceRangeFill = document.getElementById('priceRangeFill');
    // PRICE_SLIDER_MAX: giá trị tối đa của thanh trượt giá
    const PRICE_SLIDER_MAX = 200000;
    // PRICE_GAP: khoảng cách tối thiểu bắt buộc giữa giá min và giá max để tránh chồng lấn
    const PRICE_GAP = 2000;

    /**
     * Cập nhật vị trí và độ rộng của thanh tô màu (range fill) giữa 2 nút trượt giá min/max.
     */
    function capNhatThanhTruotGia() {
        // minPct, maxPct: vị trí (%) của nút trượt min/max so với chiều dài toàn thanh trượt
        const minPct = (parseInt(priceMin.value) / PRICE_SLIDER_MAX) * 100;
        const maxPct = (parseInt(priceMax.value) / PRICE_SLIDER_MAX) * 100;
        priceRangeFill.style.left = minPct + '%';
        priceRangeFill.style.right = (100 - maxPct) + '%';
    }

    // Khi kéo thanh trượt giá tối thiểu: đảm bảo luôn nhỏ hơn giá tối đa ít nhất PRICE_GAP
    priceMin.addEventListener('input', () => {
        if (parseInt(priceMin.value) > parseInt(priceMax.value) - PRICE_GAP) {
            priceMin.value = parseInt(priceMax.value) - PRICE_GAP;
        }
        priceInputMin.value = parseInt(priceMin.value).toLocaleString('vi-VN') + 'đ';
        capNhatThanhTruotGia();
    });

    // Khi kéo thanh trượt giá tối đa: đảm bảo luôn lớn hơn giá tối thiểu ít nhất PRICE_GAP
    priceMax.addEventListener('input', () => {
        if (parseInt(priceMax.value) < parseInt(priceMin.value) + PRICE_GAP) {
            priceMax.value = parseInt(priceMin.value) + PRICE_GAP;
        }
        priceInputMax.value = parseInt(priceMax.value).toLocaleString('vi-VN') + 'đ';
        capNhatThanhTruotGia();
    });

    // Khi bấm chọn 1 mốc giá dựng sẵn (price-tag): set nhanh khoảng giá min/max tương ứng
    document.querySelectorAll('.price-tag').forEach(tag => {
        tag.addEventListener('click', () => {
            document.querySelectorAll('.price-tag').forEach(t => t.classList.remove('active'));
            tag.classList.add('active');
            // min, max: khoảng giá được định nghĩa sẵn trong thuộc tính data-min/data-max của mốc giá
            const min = parseInt(tag.dataset.min);
            const max = parseInt(tag.dataset.max);
            priceMin.value = min;
            priceMax.value = max;
            priceInputMin.value = min.toLocaleString('vi-VN') + 'đ';
            priceInputMax.value = max.toLocaleString('vi-VN') + 'đ';
            capNhatThanhTruotGia();
            currentPage = 1;
            locSanPham();
        });
    });

    /**
     * Thêm nhanh 1 sản phẩm thuốc (số lượng mặc định = 1) vào giỏ hàng
     * ngay từ lưới danh sách sản phẩm, thông qua gọi API.
     * @param {number} idThuoc - Mã của thuốc cần thêm nhanh vào giỏ hàng
     */
    function xuLyThemNhanh(idThuoc) {
        fetch(`${urlRoot}/khachHang/gioHang/themVaoGio`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `idThuoc=${idThuoc}&soLuong=1`
            })
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    alert(res.message || "Đã thêm sản phẩm vào giỏ!");
                    // Cập nhật số lượng hiển thị trên badge giỏ hàng (nếu có) tăng thêm 1
                    const badge = document.getElementById('cartCountBadge');
                    if (badge) {
                        badge.textContent = parseInt(badge.textContent || 0) + 1;
                    }
                } else if (res.requireLogin) {
                    // Trường hợp chưa đăng nhập, yêu cầu đăng nhập trước khi thêm giỏ hàng
                    alert(res.message);
                    window.location.href = `${urlRoot}/khachHang/xacThuc/dangNhap`;
                } else {
                    alert(res.message || "Thao tác thất bại");
                }
            })
            .catch(err => {
                console.error(err);
                alert("Lỗi kết nối máy chủ");
            });
    }

    // Chạy đồng bộ hóa dữ liệu ban đầu
    capNhatThanhTruotGia();
    locSanPham();
</script>
