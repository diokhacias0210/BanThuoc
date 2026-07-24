<script>
    <?php
    // Gán biến an toàn tránh lỗi Undefined Variable trên VS Code
    $danhSachTenThuoc = (isset($danhSachThuocModal) && is_array($danhSachThuocModal))
        ? array_column($danhSachThuocModal, 'tenThuoc')
        : array();
    ?>

    // Danh sách chỉ gồm các thuốc BẮT BUỘC KÊ ĐƠN
    const SYSTEM_DRUGS = <?php echo json_encode($danhSachTenThuoc); ?>;
    const TEN_THUOC_CHON_SAN = <?php echo json_encode(isset($tenThuocChonSan) ? $tenThuocChonSan : ''); ?>;

    let drugRowCount = 0;
    let tempPicked = new Set();
    let modalMode = 'global';
    let targetRowId = null;

    // Xem trước nhiều hình ảnh tải lên
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
            <input type="text" class="drug-input" name="danhSachThuoc[]" value="${cleanVal}" placeholder="Nhập tên thuốc kê đơn hoặc chọn bên dưới...">
            <button type="button" class="icon-btn green" onclick="openDrugModal('row', '${id}')" title="Chọn thuốc cho dòng này">
                <i class="fa-solid fa-plus"></i>
            </button>
            <button type="button" class="icon-btn red" onclick="document.getElementById('${id}').remove()" title="Xóa dòng này">
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
            document.getElementById('modal-title').textContent = 'Chọn thuốc kê đơn cho ô này';
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

    // Tự động khởi chạy
    if (TEN_THUOC_CHON_SAN) {
        addDrugRow(TEN_THUOC_CHON_SAN);
    } else {
        addDrugRow();
    }
</script>