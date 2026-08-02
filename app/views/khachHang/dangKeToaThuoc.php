<?php
/**
 * View: Đăng kê toa thuốc
 * Chức năng: Cho phép khách hàng tải lên hình ảnh đơn thuốc (do bác sĩ chỉ định),
 * chọn danh sách các thuốc kê đơn cần mua từ danh mục hệ thống, nhập ghi chú
 * cho Dược sĩ và gửi đơn thuốc lên server để Dược sĩ kiểm tra, xử lý.
 * Dữ liệu đầu vào: $danhSachThuocModal (danh mục thuốc kê đơn hệ thống để chọn),
 * $tenThuocChonSan (tên thuốc được chọn sẵn khi vào trang từ 1 sản phẩm cụ thể).
 */
?>
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
                <!-- Input file ẩn, cho phép chọn nhiều ảnh, kích hoạt xemTruocNhieuAnh() khi có thay đổi -->
                <input type="file" id="file-in" name="hinhAnhFiles[]" class="hidden-input" multiple accept="image/*" onchange="xemTruocNhieuAnh(this)">
            </div>
            <!-- Khu vực hiển thị ảnh xem trước sau khi chọn -->
            <div class="preview-grid" id="preview-grid"></div>

            <div style="height: 24px;"></div>

            <!-- 2. DANH SÁCH THUỐC KÊ ĐƠN -->
            <div class="sec-label">
                <span class="sec-icon c-blue"><i class="fa-solid fa-pills"></i></span>
                2. Danh sách thuốc kê đơn cần mua
            </div>

            <!-- Danh sách các dòng thuốc được thêm động bằng JS (themDongThuoc) -->
            <div id="drug-list"></div>

            <div class="add-row">
                <button type="button" class="add-drug-btn" onclick="themDongThuoc()">
                    <i class="fa-solid fa-plus"></i> Thêm dòng thuốc khác
                </button>
                <button type="button" class="pick-btn" onclick="moModalChonThuoc('global')">
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
            <button type="button" class="send-btn" onclick="guiDonThuoc()">
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
            <button type="button" class="close-btn" onclick="dongModalChonThuoc()">&times;</button>
        </div>
        <!-- Ô tìm kiếm thuốc trong modal, lọc trực tiếp khi gõ -->
        <input type="text" id="modal-search" class="search-in" placeholder="Tìm kiếm tên thuốc..." oninput="locThuocTrongModal()">
        <div class="drug-list-m" id="drug-list-m"></div>
        <div class="modal-footer">
            <button type="button" class="m-cancel" onclick="dongModalChonThuoc()">Hủy</button>
            <button type="button" class="m-ok" onclick="xacNhanChonThuoc()">Xác nhận chọn</button>
        </div>
    </div>
</div>

<script>
    <?php
    // $danhSachTenThuoc: chỉ lấy riêng cột tenThuoc từ danh mục thuốc kê đơn hệ thống
    // (dùng để đẩy sang JS làm danh sách gợi ý chọn thuốc trong modal)
    $danhSachTenThuoc = (isset($danhSachThuocModal) && is_array($danhSachThuocModal))
        ? array_column($danhSachThuocModal, 'tenThuoc')
        : array();
    ?>

    // SYSTEM_DRUGS: danh sách tên thuốc kê đơn hợp lệ trong hệ thống, dùng để hiển thị
    // trong modal chọn thuốc và để kiểm tra hợp lệ trước khi gửi đơn
    const SYSTEM_DRUGS = <?php echo json_encode($danhSachTenThuoc); ?>;
    // TEN_THUOC_CHON_SAN: tên thuốc được chọn sẵn (nếu người dùng vào trang này
    // từ nút "Đăng kê toa thuốc" của 1 sản phẩm cụ thể), dùng để tự thêm dòng thuốc đầu tiên
    const TEN_THUOC_CHON_SAN = <?php echo json_encode(isset($tenThuocChonSan) ? $tenThuocChonSan : ''); ?>;

    // drugRowCount: bộ đếm dùng để sinh id duy nhất cho mỗi dòng thuốc được thêm vào
    let drugRowCount = 0;
    // tempPicked: tập hợp (Set) các tên thuốc đang được chọn tạm thời trong modal
    let tempPicked = new Set();
    // modalMode: chế độ hoạt động của modal ('global' - chọn nhiều thuốc cho toàn form,
    // 'row' - chọn thuốc cho 1 dòng cụ thể)
    let modalMode = 'global';
    // targetRowId: id của dòng thuốc đang được chỉnh sửa khi modalMode = 'row'
    let targetRowId = null;

    /**
     * Xử lý xem trước nhiều ảnh đơn thuốc ngay sau khi người dùng chọn file.
     * @param {HTMLInputElement} input - Input file chứa danh sách ảnh được chọn
     */
    function xemTruocNhieuAnh(input) {
        const grid = document.getElementById('preview-grid');
        grid.innerHTML = '';
        if (input.files && input.files.length > 0) {
            // Duyệt qua từng file ảnh được chọn để tạo ảnh xem trước
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

    /**
     * Thêm 1 dòng thuốc mới vào danh sách thuốc kê đơn cần mua.
     * @param {string} val - Tên thuốc mặc định điền sẵn cho dòng mới (mặc định rỗng)
     */
    function themDongThuoc(val = '') {
        drugRowCount++;
        // id: id duy nhất của dòng thuốc, dựa theo bộ đếm drugRowCount
        const id = 'row_' + drugRowCount;
        const list = document.getElementById('drug-list');
        const div = document.createElement('div');
        div.className = 'drug-row';
        div.id = id;

        // cleanVal: giá trị tên thuốc đã escape dấu ngoặc kép để tránh lỗi khi gán vào thuộc tính value
        const cleanVal = val.replace(/"/g, '&quot;');

        div.innerHTML = `
            <input type="text" class="drug-input" name="danhSachThuoc[]" value="${cleanVal}" placeholder="Nhấn để chọn thuốc từ danh mục..." readonly onclick="moModalChonThuoc('row', '${id}')" style="cursor:pointer;">
            <button type="button" class="icon-btn green" onclick="moModalChonThuoc('row', '${id}')" title="Chọn thuốc">
                <i class="fa-solid fa-plus"></i>
            </button>
            <button type="button" class="icon-btn red" onclick="document.getElementById('${id}').remove()" title="Xóa dòng">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        `;
        list.appendChild(div);
    }

    /**
     * Mở modal chọn thuốc kê đơn từ danh mục hệ thống.
     * @param {string} mode - Chế độ mở modal: 'global' (chọn cho toàn form) hoặc 'row' (chọn cho 1 dòng)
     * @param {string|null} rowId - Id của dòng thuốc cần chọn, chỉ dùng khi mode = 'row'
     */
    function moModalChonThuoc(mode = 'global', rowId = null) {
        modalMode = mode;
        targetRowId = rowId;
        // Xóa lựa chọn tạm trước đó mỗi khi mở lại modal
        tempPicked.clear();

        if (mode === 'row' && rowId) {
            // Nếu chọn cho 1 dòng cụ thể, nạp sẵn giá trị hiện tại của dòng đó (nếu có) vào tempPicked
            const input = document.querySelector(`#${rowId} .drug-input`);
            if (input && input.value.trim()) {
                tempPicked.add(input.value.trim());
            }
            document.getElementById('modal-title').textContent = 'Chọn thuốc kê đơn';
        } else {
            // Nếu chọn cho toàn form, nạp tất cả tên thuốc đang có trong các dòng hiện tại vào tempPicked
            document.querySelectorAll('.drug-input').forEach(input => {
                const val = input.value.trim();
                if (val) tempPicked.add(val);
            });
            document.getElementById('modal-title').textContent = 'Chọn danh sách thuốc kê đơn hệ thống';
        }

        document.getElementById('modal-search').value = '';
        document.getElementById('modal-bg').classList.add('open');
        // Render lại danh sách thuốc trong modal theo trạng thái tempPicked vừa thiết lập
        locThuocTrongModal();
    }

    /**
     * Đóng modal chọn thuốc kê đơn.
     */
    function dongModalChonThuoc() {
        document.getElementById('modal-bg').classList.remove('open');
    }

    /**
     * Lọc và render lại danh sách thuốc trong modal theo từ khóa tìm kiếm
     * và trạng thái đã chọn (tempPicked) hiện tại.
     */
    function locThuocTrongModal() {
        // q: từ khóa tìm kiếm người dùng nhập, chuyển về chữ thường để so khớp không phân biệt hoa/thường
        const q = document.getElementById('modal-search').value.toLowerCase();
        const container = document.getElementById('drug-list-m');
        container.innerHTML = '';

        // Lọc danh sách SYSTEM_DRUGS theo từ khóa q, sau đó render từng lựa chọn thuốc
        SYSTEM_DRUGS.filter(d => !q || d.toLowerCase().includes(q)).forEach(d => {
            // isPicked: đánh dấu thuốc này đã được chọn tạm thời hay chưa
            const isPicked = tempPicked.has(d);
            const div = document.createElement('div');
            div.className = 'drug-opt' + (isPicked ? ' picked' : '');
            div.innerHTML = `<span>${d}</span> <i class="fa-regular ${isPicked ? 'fa-circle-check' : 'fa-circle'}"></i>`;
            div.onclick = () => {
                if (modalMode === 'row') {
                    // Ở chế độ 'row' chỉ được chọn duy nhất 1 thuốc, nên xóa lựa chọn cũ trước khi thêm mới
                    tempPicked.clear();
                    tempPicked.add(d);
                } else {
                    // Ở chế độ 'global' cho phép chọn/bỏ chọn nhiều thuốc (toggle)
                    if (tempPicked.has(d)) tempPicked.delete(d);
                    else tempPicked.add(d);
                }
                locThuocTrongModal();
            };
            container.appendChild(div);
        });
    }

    /**
     * Xác nhận danh sách thuốc đã chọn trong modal, áp dụng kết quả
     * vào form chính (dòng cụ thể hoặc toàn bộ danh sách thuốc) rồi đóng modal.
     */
    function xacNhanChonThuoc() {
        if (modalMode === 'row' && targetRowId) {
            // Chế độ 'row': chỉ cập nhật giá trị cho đúng dòng đang chỉnh sửa
            const input = document.querySelector(`#${targetRowId} .drug-input`);
            if (input) {
                input.value = tempPicked.size > 0 ? Array.from(tempPicked)[0] : '';
            }
        } else {
            // Chế độ 'global': xóa toàn bộ danh sách dòng thuốc cũ rồi tạo lại theo tempPicked
            const listContainer = document.getElementById('drug-list');
            listContainer.innerHTML = '';

            if (tempPicked.size > 0) {
                // Tạo lại 1 dòng thuốc cho mỗi tên thuốc đã chọn
                tempPicked.forEach(drugName => themDongThuoc(drugName));
            } else {
                // Nếu không chọn thuốc nào, vẫn thêm 1 dòng trống để người dùng chọn lại
                themDongThuoc();
            }
        }
        dongModalChonThuoc();
    }

    /**
     * Kiểm tra hợp lệ dữ liệu và gửi đơn thuốc (ảnh đơn thuốc + danh sách thuốc + ghi chú)
     * lên server thông qua API, sau đó điều hướng người dùng theo kết quả trả về.
     */
    function guiDonThuoc() {
        const fileInput = document.getElementById('file-in');
        // Bắt buộc phải có ít nhất 1 ảnh đơn thuốc đính kèm
        if (!fileInput.files || fileInput.files.length === 0) {
            alert('Vui lòng đính kèm ít nhất 1 hình ảnh đơn thuốc!');
            return;
        }

        // Xóa các dòng thuốc trống (chưa chọn) để không gửi rác lên server,
        // đồng thời kiểm tra tên đã chọn có thật sự nằm trong danh mục hệ thống không
        // (phòng trường hợp giá trị bị can thiệp bất thường ở phía client).
        const drugInputs = Array.from(document.querySelectorAll('.drug-input'));
        // coTenKhongHopLe: cờ đánh dấu có tồn tại tên thuốc không hợp lệ (không thuộc SYSTEM_DRUGS)
        let coTenKhongHopLe = false;

        drugInputs.forEach(input => {
            const val = input.value.trim();
            if (val === '') {
                // Dòng trống thì xóa khỏi form, không gửi lên server
                input.closest('.drug-row').remove();
            } else if (!SYSTEM_DRUGS.includes(val)) {
                coTenKhongHopLe = true;
            }
        });

        if (coTenKhongHopLe) {
            alert('Có dòng thuốc chưa chọn hợp lệ từ danh mục hệ thống. Vui lòng chọn lại bằng nút "Chọn thuốc".');
            return;
        }

        // Bắt buộc phải còn ít nhất 1 dòng thuốc hợp lệ sau khi đã xóa các dòng trống ở trên,
        // nếu không sẽ gửi đơn thuốc lên server mà không kèm tên thuốc nào.
        if (document.querySelectorAll('.drug-input').length === 0) {
            alert('Vui lòng chọn ít nhất 1 loại thuốc kê đơn từ danh mục!');
            return;
        }

        // formData: dữ liệu form gửi lên server, bao gồm cả file ảnh (multipart/form-data)
        const form = document.getElementById('prescriptionForm');
        const formData = new FormData(form);

        // Gửi đơn thuốc lên server bằng phương thức POST
        fetch(`<?php echo URLROOT; ?>/khachHang/dangKeToaThuoc/guiDonThuoc`, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                alert(res.message);
                // Nếu gửi thành công và server trả về đường dẫn chuyển hướng thì điều hướng trang
                if (res.status && res.redirect) {
                    window.location.href = res.redirect;
                }
            })
            .catch(() => alert("Không thể kết nối máy chủ."));
    }

    // Khi tải trang: nếu có sẵn tên thuốc được chọn từ trước (TEN_THUOC_CHON_SAN)
    // thì tự động thêm 1 dòng thuốc với tên đó, ngược lại thêm 1 dòng thuốc trống
    if (TEN_THUOC_CHON_SAN) {
        themDongThuoc(TEN_THUOC_CHON_SAN);
    } else {
        themDongThuoc();
    }
</script>
