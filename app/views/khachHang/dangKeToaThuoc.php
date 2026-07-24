<div class="wrap">
    <div class="card">
        <!-- BANNER GIỚI THIỆU -->
        <div class="intro-banner">
            <div class="intro-icon">
                <i class="fa-solid fa-file-prescription"></i>
            </div>
            <div>
                <div class="intro-text-title">Tải lên đơn thuốc / Bác sĩ chỉ định</div>
                <div class="intro-text-sub">Vui lòng tải ảnh đơn thuốc để Dược sĩ kiểm tra và hỗ trợ cấp phát thuốc chính xác.</div>
            </div>
        </div>

        <form id="prescriptionForm" enctype="multipart/form-data" onsubmit="return false;">
            <!-- 1. TẢI ẢNH ĐƠN THUỐC -->
            <div class="sec-label">
                <span class="sec-icon c-teal"><i class="fa-solid fa-camera"></i></span>
                1. Hình ảnh đơn thuốc (Có thể chọn nhiều ảnh)
            </div>

            <div class="drop-zone" onclick="document.getElementById('file-in').click()">
                <div class="drop-zone-icon">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <div style="font-weight: 600; color: var(--text);">Nhấn vào đây để tải ảnh đơn thuốc từ thiết bị</div>
                <div style="font-size: 12px; color: var(--muted2);">Hỗ trợ định dạng: JPG, PNG, WEBP</div>
                <input type="file" id="file-in" name="hinhAnhFiles[]" class="hidden-input" multiple accept="image/*" onchange="previewMultipleFiles(this)">
            </div>
            <div class="preview-grid" id="preview-grid"></div>

            <div style="height: 24px;"></div>

            <!-- 2. DANH SÁCH THUỐC KÊ ĐƠN -->
            <div class="sec-label">
                <span class="sec-icon c-blue"><i class="fa-solid fa-pills"></i></span>
                2. Danh sách thuốc kê đơn cần mua
            </div>

            <div id="drug-list"></div>

            <div class="add-row">
                <button type="button" class="add-drug-btn" onclick="addDrugRow()">
                    <i class="fa-solid fa-plus"></i> Thêm dòng thuốc khác
                </button>
                <button type="button" class="pick-btn" onclick="openDrugModal('global')">
                    <i class="fa-solid fa-list-check"></i> Chọn từ danh mục
                </button>
            </div>

            <div style="height: 24px;"></div>

            <!-- 3. GHI CHÚ -->
            <div class="sec-label">
                <span class="sec-icon c-amber"><i class="fa-solid fa-note-sticky"></i></span>
                3. Ghi chú cho Dược sĩ (Tùy chọn)
            </div>
            <textarea name="ghiChu" class="note-ta" rows="3" placeholder="Nhập tiền sử dị ứng thuốc, triệu chứng sức khỏe hoặc yêu cầu thêm..."></textarea>

            <!-- NÚT GỬI -->
            <button type="button" class="send-btn" onclick="submitPrescription()">
                <i class="fa-solid fa-paper-plane"></i> Gửi đơn thuốc cho Dược sĩ
            </button>
        </form>
    </div>
</div>

<!-- MODAL CHỌN THUỐC KÊ ĐƠN -->
<div class="modal-bg" id="modal-bg">
    <div class="modal">
        <div class="modal-head">
            <div class="modal-title" id="modal-title">Chọn danh sách thuốc kê đơn hệ thống</div>
            <button type="button" class="close-btn" onclick="closeDrugModal()">&times;</button>
        </div>
        <input type="text" id="modal-search" class="search-in" placeholder="Tìm kiếm tên thuốc..." oninput="filterModalDrugs()">
        <div class="drug-list-m" id="drug-list-m"></div>
        <div class="modal-footer">
            <button type="button" class="m-cancel" onclick="closeDrugModal()">Hủy</button>
            <button type="button" class="m-ok" onclick="confirmModalPick()">Xác nhận chọn</button>
        </div>
    </div>
</div>

<script>
    <?php
    $danhSachTenThuoc = (isset($danhSachThuocModal) && is_array($danhSachThuocModal))
        ? array_column($danhSachThuocModal, 'tenThuoc')
        : array();
    ?>

    const SYSTEM_DRUGS = <?php echo json_encode($danhSachTenThuoc); ?>;
    const TEN_THUOC_CHON_SAN = <?php echo json_encode(isset($tenThuocChonSan) ? $tenThuocChonSan : ''); ?>;

    let drugRowCount = 0;
    let tempPicked = new Set();
    let modalMode = 'global';
    let targetRowId = null;

    function previewMultipleFiles(input) {
        const grid = document.getElementById('preview-grid');
        grid.innerHTML = '';
        if (input.files && input.files.length > 0) {
            Array.from(input.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const item = document.createElement('div');
                    item.className = 'preview-item';
                    item.innerHTML = `<img src="${e.target.result}" alt="preview">`;
                    grid.appendChild(item);
                }
                reader.readAsDataURL(file);
            });
        }
    }

    function addDrugRow(val = '') {
        drugRowCount++;
        const id = 'row_' + drugRowCount;
        const list = document.getElementById('drug-list');
        const div = document.createElement('div');
        div.className = 'drug-row';
        div.id = id;

        const cleanVal = val.replace(/"/g, '&quot;');

        div.innerHTML = `
            <input type="text" class="drug-input" name="danhSachThuoc[]" value="${cleanVal}" placeholder="Nhập tên thuốc kê đơn...">
            <button type="button" class="icon-btn green" onclick="openDrugModal('row', '${id}')" title="Chọn thuốc">
                <i class="fa-solid fa-plus"></i>
            </button>
            <button type="button" class="icon-btn red" onclick="document.getElementById('${id}').remove()" title="Xóa dòng">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        `;
        list.appendChild(div);
    }

    function openDrugModal(mode = 'global', rowId = null) {
        modalMode = mode;
        targetRowId = rowId;
        tempPicked.clear();

        if (mode === 'row' && rowId) {
            const input = document.querySelector(`#${rowId} .drug-input`);
            if (input && input.value.trim()) {
                tempPicked.add(input.value.trim());
            }
            document.getElementById('modal-title').textContent = 'Chọn thuốc kê đơn';
        } else {
            document.querySelectorAll('.drug-input').forEach(input => {
                const val = input.value.trim();
                if (val) tempPicked.add(val);
            });
            document.getElementById('modal-title').textContent = 'Chọn danh sách thuốc kê đơn hệ thống';
        }

        document.getElementById('modal-search').value = '';
        document.getElementById('modal-bg').classList.add('open');
        filterModalDrugs();
    }

    function closeDrugModal() {
        document.getElementById('modal-bg').classList.remove('open');
    }

    function filterModalDrugs() {
        const q = document.getElementById('modal-search').value.toLowerCase();
        const container = document.getElementById('drug-list-m');
        container.innerHTML = '';

        SYSTEM_DRUGS.filter(d => !q || d.toLowerCase().includes(q)).forEach(d => {
            const isPicked = tempPicked.has(d);
            const div = document.createElement('div');
            div.className = 'drug-opt' + (isPicked ? ' picked' : '');
            div.innerHTML = `<span>${d}</span> <i class="fa-regular ${isPicked ? 'fa-circle-check' : 'fa-circle'}"></i>`;
            div.onclick = () => {
                if (modalMode === 'row') {
                    tempPicked.clear();
                    tempPicked.add(d);
                } else {
                    if (tempPicked.has(d)) tempPicked.delete(d);
                    else tempPicked.add(d);
                }
                filterModalDrugs();
            };
            container.appendChild(div);
        });
    }

    function confirmModalPick() {
        if (modalMode === 'row' && targetRowId) {
            const input = document.querySelector(`#${targetRowId} .drug-input`);
            if (input) {
                input.value = tempPicked.size > 0 ? Array.from(tempPicked)[0] : '';
            }
        } else {
            const listContainer = document.getElementById('drug-list');
            listContainer.innerHTML = '';

            if (tempPicked.size > 0) {
                tempPicked.forEach(drugName => addDrugRow(drugName));
            } else {
                addDrugRow();
            }
        }
        closeDrugModal();
    }

    function submitPrescription() {
        const fileInput = document.getElementById('file-in');
        if (!fileInput.files || fileInput.files.length === 0) {
            alert('Vui lòng đính kèm ít nhất 1 hình ảnh đơn thuốc!');
            return;
        }

        const form = document.getElementById('prescriptionForm');
        const formData = new FormData(form);

        fetch(`<?php echo URLROOT; ?>/khachHang/dangKeToaThuoc/guiDonThuoc`, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                alert(res.message);
                if (res.status && res.redirect) {
                    window.location.href = res.redirect;
                }
            })
            .catch(() => alert("Không thể kết nối máy chủ."));
    }

    if (TEN_THUOC_CHON_SAN) {
        addDrugRow(TEN_THUOC_CHON_SAN);
    } else {
        addDrugRow();
    }
</script>