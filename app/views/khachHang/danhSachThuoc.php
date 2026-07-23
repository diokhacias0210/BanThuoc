<div class="layout">
    <!-- ══ SIDEBAR FILTER ══ -->
    <aside class="filter-box">
        <div class="filter-head">
            <i class="fa-solid fa-sliders"></i> Bộ lọc thuốc
        </div>

        <div class="filter-group">
            <div class="fg-title">Danh mục thuốc hệ thống</div>
            <div class="category-scroll-area" id="categoryBox">
                <!-- Danh mục sẽ được render động từ CSDL -->
            </div>
        </div>

        <div class="divider-h"></div>

        <div class="filter-group">
            <div class="fg-title">Phân loại đơn thuốc</div>
            <div class="fg-item">
                <input type="checkbox" id="t1" class="rx-filter" value="Không kê đơn">
                <label for="t1">Không kê đơn (OTC)</label>
            </div>
            <div class="fg-item">
                <input type="checkbox" id="t2" class="rx-filter" value="Kê đơn">
                <label for="t2">Bắt buộc kê đơn (Rx)</label>
            </div>
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
                <input type="range" id="priceMin" min="0" max="200000" step="2000" value="0">
                <input type="range" id="priceMax" min="0" max="200000" step="2000" value="200000">
            </div>
            <div class="price-presets">
                <div class="price-tag active" data-min="0" data-max="200000">Tất cả mức giá</div>
                <div class="price-tag" data-min="0" data-max="50000">Dưới 50k</div>
                <div class="price-tag" data-min="50000" data-max="200000">50k - 200k</div>
            </div>
        </div>

        <button class="filter-apply" id="btnApplyFilter">Lọc sản phẩm</button>
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
            <input type="text" id="localSearchInput" placeholder="Tìm kiếm nhanh tên thuốc hoặc hoạt chất...">
        </div>

        <div class="pgrid" id="productGrid"></div>
        <div class="pagination" id="pagination"></div>
    </main>
</div>

<script>
    let globalMedicineList = [];
    let currentPage = 1;
    const itemsPerPage = 12; // Phân trang 12 sản phẩm / trang
    let selectedCatId = 'all';

    function fmtMoney(n) {
        return Number(n || 0).toLocaleString('vi-VN') + 'đ';
    }

    // Xử lý sửa đường dẫn ảnh chuẩn từ CSDL
    function resolveImageUrl(path) {
        if (!path || path.trim() === '') return 'https://placehold.co/300x360/e8f5ee/2d7a4f?text=💊';
        if (path.startsWith('http')) return path;
        let clean = path.replace(/\\/g, '/');
        if (clean.startsWith('images/')) clean = 'assets/' + clean;
        if (clean.startsWith('/')) clean = clean.substring(1);
        if (clean.startsWith('public/')) clean = clean.substring(7);
        return `<?php echo URLROOT; ?>/` + clean;
    }

    // Nạp dữ liệu từ API Backend
    function fetchMedicines() {
        const search = document.getElementById('localSearchInput').value.trim();
        const minP = document.getElementById('priceMin').value;
        const maxP = document.getElementById('priceMax').value;

        // Lấy phân loại Rx
        const rxChecked = Array.from(document.querySelectorAll('.rx-filter:checked')).map(c => c.value);
        let keDonVal = 'all';
        if (rxChecked.length === 1) keDonVal = rxChecked[0];

        const url = `<?php echo URLROOT; ?>/khachHang/thuoc/getList?search=${encodeURIComponent(search)}&idDanhMuc=${selectedCatId}&keDon=${encodeURIComponent(keDonVal)}&minPrice=${minP}&maxPrice=${maxP}`;

        fetch(url)
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    globalMedicineList = res.data;
                    document.getElementById('contentCount').textContent = `${globalMedicineList.length} sản phẩm`;
                    renderCategories(res.categories);
                    renderPageData();
                }
            })
            .catch(err => console.error("Lỗi lấy sản phẩm:", err));
    }

    function renderCategories(categories) {
        const box = document.getElementById('categoryBox');
        if (box.children.length > 0) return; // Đã render rồi thì giữ nguyên

        let html = `<div class="fg-item"><input type="radio" name="cat" id="cat_all" value="all" checked><label for="cat_all">Tất cả sản phẩm</label></div>`;
        html += categories.map(c => `
            <div class="fg-item">
                <input type="radio" name="cat" id="cat_${c.idDanhMuc}" value="${c.idDanhMuc}">
                <label for="cat_${c.idDanhMuc}">${c.tenDanhMuc}</label>
            </div>
        `).join('');

        box.innerHTML = html;

        // Bắt sự kiện chọn danh mục
        box.querySelectorAll('input[name="cat"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                selectedCatId = e.target.value;
                const labelText = e.target.nextElementSibling.textContent;
                document.getElementById('contentTitle').textContent = labelText;
                currentPage = 1;
                fetchMedicines();
            });
        });
    }

    function renderPageData() {
        const grid = document.getElementById('productGrid');
        const totalPages = Math.ceil(globalMedicineList.length / itemsPerPage);

        if (globalMedicineList.length === 0) {
            grid.innerHTML = `<div style="grid-column: 1/-1; text-align:center; padding:60px 0; color:var(--muted2);"><i class="fa-solid fa-box-open" style="font-size:36px; margin-bottom:10px; display:block;"></i>Không tìm thấy sản phẩm thuốc nào phù hợp.</div>`;
            document.getElementById('pagination').innerHTML = '';
            return;
        }

        const start = (currentPage - 1) * itemsPerPage;
        const pageItems = globalMedicineList.slice(start, start + itemsPerPage);

        grid.innerHTML = pageItems.map(p => {
            const isRx = p.yeuCauKeDon === 'Kê đơn';
            return `
                <a class="pcard" href="<?php echo URLROOT; ?>/khachHang/thuoc/chiTiet/${p.idThuoc}">
                    <div class="pcard-img">
                        ${isRx ? `<span class="pcard-tag">Kê đơn</span>` : ''}
                        <img src="${resolveImageUrl(p.hinhAnh)}" alt="${p.tenThuoc}">
                    </div>
                    <div class="pcard-body">
                        <div class="pcard-name">${p.tenThuoc}</div>
                        <div class="pcard-foot">
                            <span class="pcard-price">${fmtMoney(p.giaBan)}</span>
                            ${isRx ? `<span class="btn-view-detail">Xem chi tiết</span>` : `<button type="button" class="add-btn" onclick="themNhanhGioHang(event, ${p.idThuoc})"><i class="fa-solid fa-plus"></i></button>`}
                        </div>
                    </div>
                </a>
            `;
        }).join('');

        renderPaginationControls(totalPages);
    }

    // Xử lý nút cộng thêm nhanh giỏ hàng
    function themNhanhGioHang(event, idThuoc) {
        event.preventDefault();
        event.stopPropagation();

        // Thông báo thử nghiệm
        if (typeof hienThiThongBao === 'function') {
            hienThiThongBao("Đã thêm sản phẩm vào giỏ hàng!");
        } else {
            alert("Đã thêm sản phẩm vào giỏ hàng thành công!");
        }
    }

    function renderPaginationControls(totalPages) {
        const container = document.getElementById('pagination');
        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = `<button class="page-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="goToPage(${currentPage - 1})"><i class="fa-solid fa-angle-left"></i></button>`;
        for (let i = 1; i <= totalPages; i++) {
            html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="goToPage(${i})">${i}</button>`;
        }
        html += `<button class="page-btn" ${currentPage === totalPages ? 'disabled' : ''} onclick="goToPage(${currentPage + 1})"><i class="fa-solid fa-angle-right"></i></button>`;

        container.innerHTML = html;
    }

    function goToPage(p) {
        currentPage = p;
        renderPageData();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    // Xử lý thanh trượt giá
    const priceMin = document.getElementById('priceMin');
    const priceMax = document.getElementById('priceMax');
    const priceInputMin = document.getElementById('priceInputMin');
    const priceInputMax = document.getElementById('priceInputMax');
    const priceRangeFill = document.getElementById('priceRangeFill');

    function updatePriceFill() {
        const minPct = (parseInt(priceMin.value) / 200000) * 100;
        const maxPct = (parseInt(priceMax.value) / 200000) * 100;
        priceRangeFill.style.left = minPct + '%';
        priceRangeFill.style.right = (100 - maxPct) + '%';
    }

    priceMin.addEventListener('input', () => {
        if (parseInt(priceMin.value) > parseInt(priceMax.value) - 2000) priceMin.value = parseInt(priceMax.value) - 2000;
        priceInputMin.value = fmtMoney(priceMin.value);
        updatePriceFill();
    });

    priceMax.addEventListener('input', () => {
        if (parseInt(priceMax.value) < parseInt(priceMin.value) + 2000) priceMax.value = parseInt(priceMin.value) + 2000;
        priceInputMax.value = fmtMoney(priceMax.value);
        updatePriceFill();
    });

    document.querySelectorAll('.price-tag').forEach(tag => {
        tag.addEventListener('click', () => {
            document.querySelectorAll('.price-tag').forEach(t => t.classList.remove('active'));
            tag.classList.add('active');
            priceMin.value = tag.dataset.min;
            priceMax.value = tag.dataset.max;
            priceInputMin.value = fmtMoney(priceMin.value);
            priceInputMax.value = fmtMoney(priceMax.value);
            updatePriceFill();
            currentPage = 1;
            fetchMedicines();
        });
    });

    // Lắng nghe tìm kiếm và sự kiện lọc
    let searchTimer;
    document.getElementById('localSearchInput').addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            currentPage = 1;
            fetchMedicines();
        }, 300);
    });

    document.querySelectorAll('.rx-filter').forEach(cb => cb.addEventListener('change', () => {
        currentPage = 1;
        fetchMedicines();
    }));
    document.getElementById('btnApplyFilter').addEventListener('click', () => {
        currentPage = 1;
        fetchMedicines();
    });

    // Khởi chạy
    updatePriceFill();
    fetchMedicines();
</script>