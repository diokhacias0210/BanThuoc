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
                    <input type="checkbox" id="c0" data-catid="all" checked>
                    <label for="c0">Tất cả sản phẩm</label>
                </div>
                <?php if (!empty($danhMucList)): ?>
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
            <div class="fg-item"><input type="checkbox" id="t1" data-rxtype="OTC"><label for="t1">Không kê đơn (OTC)</label></div>
            <div class="fg-item"><input type="checkbox" id="t2" data-rxtype="Kê đơn"><label for="t2">Thuốc kê đơn (RX)</label></div>
        </div>

        <div class="divider-h"></div>

        <div class="filter-group">
            <div class="fg-title">Khoảng giá bán</div>
            <div class="price-inputs">
                <input type="text" id="priceInputMin" value="0đ" readonly>
                <span class="price-sep">—</span>
                <input type="text" id="priceInputMax" value="200.000đ" readonly>
            </div>
            <div class="price-slider-wrap">
                <div class="price-slider-track"></div>
                <div class="price-slider-range" id="priceRangeFill"></div>
                <input type="range" class="price-slider" id="priceMin" min="0" max="200000" step="2000" value="0">
                <input type="range" class="price-slider" id="priceMax" min="0" max="200000" step="2000" value="200000">
            </div>
            <div class="price-presets">
                <div class="price-tag active" data-min="0" data-max="200000">Tất cả mức giá</div>
                <div class="price-tag" data-min="0" data-max="50000">Dưới 50k</div>
                <div class="price-tag" data-min="50000" data-max="200000">50k - 200k</div>
            </div>
        </div>

        <button class="filter-apply" onclick="applyFilters()">Lọc sản phẩm</button>
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
            <input type="text" id="localSearchInput" placeholder="Tìm kiếm nhanh tên thuốc..." oninput="handleLocalSearch()">
        </div>

        <div class="pgrid" id="productGrid"></div>
        <div class="pagination" id="pagination"></div>
    </main>
</div>

<script>
    // Nhận dữ liệu động từ Server PHP
    const rawProducts = <?php echo json_encode(isset($thuocList) ? $thuocList : []); ?>;
    const urlRoot = '<?php echo URLROOT; ?>';

    let currentPage = 1;
    const itemsPerPage = 12;
    let currentFilteredList = [];

    const grid = document.getElementById('productGrid');
    const contentTitle = document.getElementById('contentTitle');
    const contentCount = document.getElementById('contentCount');
    const categoryCheckboxes = document.querySelectorAll('.fg-item input[data-catid]');
    const paginationElement = document.getElementById('pagination');

    function renderProducts(items) {
        if (!items || items.length === 0) {
            grid.innerHTML = `<div style="grid-column: 1/-1; text-align:center; padding:40px 0; color:var(--muted2);">Không tìm thấy sản phẩm thuốc nào phù hợp.</div>`;
            return;
        }

        grid.innerHTML = items.map(p => {
            const isRx = p.yeuCauKeDon === 'Kê đơn';
            const hetHang = parseInt(p.tongTon) <= 0;
            const priceFormatted = parseInt(p.giaBan).toLocaleString('vi-VN') + 'đ';

            return `
                <div class="pcard" onclick="window.location.href='${urlRoot}/khachHang/thuoc/chiTiet/${p.idThuoc}'">
                    <div class="pcard-img">
                        ${isRx ? `<span class="pcard-tag">Kê đơn</span>` : (hetHang ? `<span class="pcard-tag" style="background:#fdecea; color:#c0392b; border:1px solid #f9d6d2;">Hết hàng</span>` : '')}
                        <img src="${p.hinhAnhUrl}" alt="${p.tenThuoc}" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <div class="pcard-body">
                        <div class="pcard-name" title="${p.tenThuoc}">${p.tenThuoc}</div>
                        <div class="pcard-foot">
                            <span class="pcard-price">${priceFormatted}</span>
                            ${isRx ? 
                                `<button type="button" class="btn-view-detail">Xem chi tiết</button>` : 
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

    function executeFiltering() {
        const query = document.getElementById('localSearchInput').value.trim().toLowerCase();

        // 1. Lọc theo danh mục
        const activeCat = document.querySelector('.fg-item input[data-catid]:checked');
        let base = (activeCat && activeCat.dataset.catid !== 'all') ?
            rawProducts.filter(p => p.idDanhMuc == activeCat.dataset.catid) :
            [...rawProducts];

        // 2. Lọc theo loại kê đơn (OTC / RX)
        const checkedRxTypes = Array.from(document.querySelectorAll('.fg-item input[data-rxtype]:checked')).map(cb => cb.dataset.rxtype);
        if (checkedRxTypes.length > 0) {
            base = base.filter(p => {
                if (checkedRxTypes.includes('Kê đơn') && p.yeuCauKeDon === 'Kê đơn') return true;
                if (checkedRxTypes.includes('OTC') && p.yeuCauKeDon !== 'Kê đơn') return true;
                return false;
            });
        }

        // 3. Lọc theo giá
        const min = parseInt(priceMin.value, 10);
        const max = parseInt(priceMax.value, 10);
        base = base.filter(p => parseInt(p.giaBan) >= min && parseInt(p.giaBan) <= max);

        // 4. Lọc theo tìm kiếm từ khóa
        if (query) {
            base = base.filter(p => p.tenThuoc.toLowerCase().includes(query));
        }

        currentFilteredList = base;
        contentCount.textContent = `${currentFilteredList.length} sản phẩm`;

        const totalPages = Math.ceil(currentFilteredList.length / itemsPerPage);
        if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;

        const startIndex = (currentPage - 1) * itemsPerPage;
        const paginatedItems = currentFilteredList.slice(startIndex, startIndex + itemsPerPage);

        renderProducts(paginatedItems);
        renderPagination(totalPages);
    }

    function handleLocalSearch() {
        currentPage = 1;
        executeFiltering();
    }

    function applyFilters() {
        currentPage = 1;
        executeFiltering();
    }

    function renderPagination(totalPages) {
        if (totalPages <= 1) {
            paginationElement.innerHTML = '';
            return;
        }

        let html = `<button class="page-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="changePage(${currentPage - 1})"><i class="fa-solid fa-angle-left"></i></button>`;
        for (let i = 1; i <= totalPages; i++) {
            html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
        }
        html += `<button class="page-btn" ${currentPage === totalPages ? 'disabled' : ''} onclick="changePage(${currentPage + 1})"><i class="fa-solid fa-angle-right"></i></button>`;
        paginationElement.innerHTML = html;
    }

    function changePage(p) {
        currentPage = p;
        executeFiltering();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    categoryCheckboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            if (cb.checked) {
                categoryCheckboxes.forEach(other => {
                    if (other !== cb) other.checked = false;
                });
            } else {
                document.getElementById('c0').checked = true;
            }
            currentPage = 1;
            executeFiltering();
        });
    });

    document.querySelectorAll('.fg-item input[data-rxtype]').forEach(cb => {
        cb.addEventListener('change', () => {
            currentPage = 1;
            executeFiltering();
        });
    });

    /* ══ THANH TRƯỢT GIÁ ══ */
    const priceMin = document.getElementById('priceMin');
    const priceMax = document.getElementById('priceMax');
    const priceInputMin = document.getElementById('priceInputMin');
    const priceInputMax = document.getElementById('priceInputMax');
    const priceRangeFill = document.getElementById('priceRangeFill');
    const PRICE_SLIDER_MAX = 200000;
    const PRICE_GAP = 2000;

    function updatePriceRangeFill() {
        const minPct = (parseInt(priceMin.value) / PRICE_SLIDER_MAX) * 100;
        const maxPct = (parseInt(priceMax.value) / PRICE_SLIDER_MAX) * 100;
        priceRangeFill.style.left = minPct + '%';
        priceRangeFill.style.right = (100 - maxPct) + '%';
    }

    priceMin.addEventListener('input', () => {
        if (parseInt(priceMin.value) > parseInt(priceMax.value) - PRICE_GAP) {
            priceMin.value = parseInt(priceMax.value) - PRICE_GAP;
        }
        priceInputMin.value = parseInt(priceMin.value).toLocaleString('vi-VN') + 'đ';
        updatePriceRangeFill();
    });

    priceMax.addEventListener('input', () => {
        if (parseInt(priceMax.value) < parseInt(priceMin.value) + PRICE_GAP) {
            priceMax.value = parseInt(priceMin.value) + PRICE_GAP;
        }
        priceInputMax.value = parseInt(priceMax.value).toLocaleString('vi-VN') + 'đ';
        updatePriceRangeFill();
    });

    document.querySelectorAll('.price-tag').forEach(tag => {
        tag.addEventListener('click', () => {
            document.querySelectorAll('.price-tag').forEach(t => t.classList.remove('active'));
            tag.classList.add('active');
            const min = parseInt(tag.dataset.min);
            const max = parseInt(tag.dataset.max);
            priceMin.value = min;
            priceMax.value = max;
            priceInputMin.value = min.toLocaleString('vi-VN') + 'đ';
            priceInputMax.value = max.toLocaleString('vi-VN') + 'đ';
            updatePriceRangeFill();
            currentPage = 1;
            executeFiltering();
        });
    });

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
                    const badge = document.getElementById('cartCountBadge');
                    if (badge) {
                        badge.textContent = parseInt(badge.textContent || 0) + 1;
                    }
                } else if (res.requireLogin) {
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
    updatePriceRangeFill();
    executeFiltering();
</script>