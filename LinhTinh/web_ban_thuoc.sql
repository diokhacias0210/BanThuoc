CREATE DATABASE IF NOT EXISTS HeThongBanThuoc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE HeThongBanThuoc;

CREATE TABLE NguoiDung (
    idNguoiDung INT AUTO_INCREMENT PRIMARY KEY,
    hoTen VARCHAR(255) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    soDienThoai VARCHAR(15) UNIQUE NOT NULL,
    matKhau VARCHAR(255) NOT NULL,
    trangThai BOOLEAN DEFAULT TRUE,
    vaiTro ENUM('KHACH_HANG', 'DUOC_SI', 'QUAN_TRI_VIEN') NOT NULL
);

CREATE TABLE KhachHang (
    idNguoiDung INT PRIMARY KEY,
    diemTichLuy INT DEFAULT 0,
    ngaySinh DATE,
    FOREIGN KEY (idNguoiDung) REFERENCES NguoiDung(idNguoiDung) ON DELETE CASCADE
);

CREATE TABLE DiaChiGiaoHang (
    idDiaChi INT AUTO_INCREMENT PRIMARY KEY,
    idNguoiDung INT NOT NULL,                  -- Liên kết với bảng KhachHang/NguoiDung
    tenNguoiNhan VARCHAR(255) NOT NULL,        -- Họ tên người nhận tại địa chỉ đó
    soDienThoaiNhan VARCHAR(15) NOT NULL,     -- Số điện thoại người nhận hàng
    diaChiChiTiet VARCHAR(500) NOT NULL,       -- Số nhà, tên đường, phường/xã, quận/huyện, tỉnh thành
    laMacDinh BOOLEAN DEFAULT FALSE,           -- Đánh dấu địa chỉ ưu tiên khi đặt hàng
    FOREIGN KEY (idNguoiDung) REFERENCES KhachHang(idNguoiDung) ON DELETE CASCADE
);

CREATE TABLE DuocSi (
    idNguoiDung INT PRIMARY KEY,
    chungChiHanhNghe VARCHAR(100) NOT NULL,
    noiCap NVARCHAR(255) NOT NULL, -- Nơi cấp chứng chỉ hoặc bằng cấp
    trinhDo NVARCHAR(100) NOT NULL, -- Trình độ (VD: Đại học, Cao đẳng, Thạc sĩ,...)
    FOREIGN KEY (idNguoiDung) REFERENCES NguoiDung(idNguoiDung) ON DELETE CASCADE
);

CREATE TABLE QuanTriVien (
    idNguoiDung INT PRIMARY KEY,
    FOREIGN KEY (idNguoiDung) REFERENCES NguoiDung(idNguoiDung) ON DELETE CASCADE
);

-- 2. BẢNG SẢN PHẨM & KHO
CREATE TABLE DanhMucThuoc (
    idDanhMuc INT AUTO_INCREMENT PRIMARY KEY,
    tenDanhMuc NVARCHAR(255) NOT NULL,
    moTa NVARCHAR(255)
);

CREATE TABLE Thuoc (
    idThuoc INT AUTO_INCREMENT PRIMARY KEY,
    idDanhMuc INT,
    tenThuoc NVARCHAR(255) NOT NULL,
    thanhPhan NVARCHAR(255) NOT NULL,
    hamLuong VARCHAR(100),
    congDung NVARCHAR(255) NOT NULL,
    donViTinh NVARCHAR(50),
    giaBan DECIMAL(12, 2) NOT NULL,
    yeuCauKeDon ENUM('Kê đơn', 'Không kê đơn') NOT NULL DEFAULT 'Không kê đơn',
    gioiHanMua INT DEFAULT -1, -- -1: Không giới hạn, >0: Giới hạn tối đa
    trangThai BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (idDanhMuc) REFERENCES DanhMucThuoc(idDanhMuc) ON DELETE SET NULL
);
CREATE TABLE HinhAnhThuoc (
    idHinhAnh INT AUTO_INCREMENT PRIMARY KEY,
    idThuoc INT NOT NULL,
    duongDan VARCHAR(500) NOT NULL,
    FOREIGN KEY (idThuoc) REFERENCES Thuoc(idThuoc) ON DELETE CASCADE
);
CREATE TABLE LoThuoc (
    idLo INT AUTO_INCREMENT PRIMARY KEY,
    idThuoc INT NOT NULL,
    maLo VARCHAR(100) NOT NULL,
    ngaySanXuat DATE,
    hanSuDung DATE NOT NULL,
    soLuongTon INT DEFAULT 0,
    giaNhap DECIMAL(12, 2) NOT NULL,
    FOREIGN KEY (idThuoc) REFERENCES Thuoc(idThuoc) ON DELETE CASCADE
);

-- 3. BẢNG ĐƠN HÀNG & ĐƠN THUỐC (Được chuyển lên trước ChiTietGioHang)
CREATE TABLE DonHang (
    idDonHang INT AUTO_INCREMENT PRIMARY KEY,
    idKhachHang INT NOT NULL,
    ngayDat DATETIME DEFAULT CURRENT_TIMESTAMP,
    tongTien DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    phiVanChuyen DECIMAL(12, 2) DEFAULT 0.00,
    trangThai ENUM('CHO_XAC_NHAN','DA_XAC_NHAN','DANG_GIAO','DA_GIAO','DA_HUY') DEFAULT 'CHO_XAC_NHAN',
    lyDoHuy VARCHAR(500) NULL,
    FOREIGN KEY (idKhachHang) REFERENCES KhachHang(idNguoiDung) ON DELETE RESTRICT
);

CREATE TABLE ChiTietDonHang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idDonHang INT NOT NULL,
    idThuoc INT NOT NULL,
    soLuong INT NOT NULL CHECK (soLuong > 0),
    donGia DECIMAL(12, 2) NOT NULL,
    giamGia DECIMAL(12, 2) DEFAULT 0.00,
    FOREIGN KEY (idDonHang) REFERENCES DonHang(idDonHang) ON DELETE CASCADE,
    FOREIGN KEY (idThuoc) REFERENCES Thuoc(idThuoc) ON DELETE RESTRICT
);

CREATE TABLE DonThuoc (
    idDonThuoc INT AUTO_INCREMENT PRIMARY KEY,
    idKhachHang INT NOT NULL,
    idDuocSi INT NULL,              
    idDonHang INT NULL,             
    ngayGui DATETIME DEFAULT CURRENT_TIMESTAMP,
    ghiChu NVARCHAR(500),
    trangThai ENUM('CHO_DUYET','DA_DUYET','TU_CHOI','KH_HUY') DEFAULT 'CHO_DUYET',
    hinhAnhDonThuoc VARCHAR(255) NOT NULL,
    FOREIGN KEY (idKhachHang) REFERENCES KhachHang(idNguoiDung) ON DELETE RESTRICT,
    FOREIGN KEY (idDuocSi) REFERENCES DuocSi(idNguoiDung) ON DELETE SET NULL,
    FOREIGN KEY (idDonHang) REFERENCES DonHang(idDonHang) ON DELETE SET NULL
);

CREATE TABLE ChiTietDonThuoc (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idDonThuoc INT NOT NULL,
    tenThuoc VARCHAR(255) NOT NULL,
    lieuDung TEXT,
    soLuong INT NOT NULL CHECK (soLuong > 0),
    FOREIGN KEY (idDonThuoc) REFERENCES DonThuoc(idDonThuoc) ON DELETE CASCADE
);

-- 4. BẢNG GIỎ HÀNG (Tạo sau DonThuoc để không bị lỗi khóa ngoại)
CREATE TABLE GioHang (
    idGioHang INT AUTO_INCREMENT PRIMARY KEY,
    idKhachHang INT UNIQUE NOT NULL, 
    ngayTao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idKhachHang) REFERENCES KhachHang(idNguoiDung) ON DELETE CASCADE
);

CREATE TABLE ChiTietGioHang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idGioHang INT NOT NULL,
    idThuoc INT NOT NULL,
    idDonThuoc INT NULL,
    soLuong INT NOT NULL CHECK (soLuong > 0),
    donGia DECIMAL(12, 2) NOT NULL,
    trangThaiThaoTac ENUM('CHO_PHEP', 'KHOA') DEFAULT 'CHO_PHEP',
    FOREIGN KEY (idGioHang) REFERENCES GioHang(idGioHang) ON DELETE CASCADE,
    FOREIGN KEY (idThuoc) REFERENCES Thuoc(idThuoc) ON DELETE CASCADE,
    FOREIGN KEY (idDonThuoc) REFERENCES DonThuoc(idDonThuoc) ON DELETE SET NULL
);

-- 5. BẢNG THANH TOÁN & BÁO CÁO
CREATE TABLE ThanhToan (
    idThanhToan INT AUTO_INCREMENT PRIMARY KEY,
    idDonHang INT UNIQUE NOT NULL, 
    phuongThuc VARCHAR(50) NOT NULL,
    soTien DECIMAL(12, 2) NOT NULL,
    ngayThanhToan DATETIME DEFAULT CURRENT_TIMESTAMP,
    trangThai VARCHAR(50) DEFAULT 'CHUA_THANH_TOAN',
    FOREIGN KEY (idDonHang) REFERENCES DonHang(idDonHang) ON DELETE CASCADE
);

CREATE TABLE BaoCaoThongKe (
    idBaoCao INT AUTO_INCREMENT PRIMARY KEY,
    idQuanTriVien INT NOT NULL,
    thoiGianBatDau DATE NOT NULL,
    thoiGianKetThuc DATE NOT NULL,
    loaiBaoCao VARCHAR(100),
    FOREIGN KEY (idQuanTriVien) REFERENCES QuanTriVien(idNguoiDung) ON DELETE RESTRICT
);
-- ==========================================================
-- 0. CHÈN TÀI KHOẢN QUẢN TRỊ VIÊN (idNguoiDung = 1)
-- ==========================================================
INSERT INTO NguoiDung (idNguoiDung, hoTen, email, soDienThoai, matKhau, trangThai, vaiTro)
VALUES (1, 'admin', 'admin@pharmacare.com', '0999888777', 'admin123', TRUE, 'QUAN_TRI_VIEN');

INSERT INTO QuanTriVien (idNguoiDung)
VALUES (1);


-- ==========================================================
-- 1. CHÈN 5 KHÁCH HÀNG (idNguoiDung từ 2 đến 6)
-- ==========================================================

-- Chèn vào bảng cha NguoiDung
INSERT INTO NguoiDung (idNguoiDung, hoTen, email, soDienThoai, matKhau, trangThai, vaiTro) VALUES
(2, 'Nguyễn Văn A', 'nguyenvana@gmail.com', '0901234567', 'p@ssword123', TRUE, 'KHACH_HANG'),
(3, 'Trần Thị B', 'tranthib@gmail.com', '0902345678', 'p@ssword123', TRUE, 'KHACH_HANG'),
(4, 'Lê Văn C', 'levanc@gmail.com', '0903456789', 'p@ssword123', TRUE, 'KHACH_HANG'),
(5, 'Phạm Thị D', 'phamthid@gmail.com', '0904567890', 'p@ssword123', TRUE, 'KHACH_HANG'),
(6, 'Hoàng Văn E', 'hoangvane@gmail.com', '0905678901', 'p@ssword123', TRUE, 'KHACH_HANG');

-- Chèn vào bảng con KhachHang (Đã bỏ cột diaChiGiaoHang thừa)
INSERT INTO KhachHang (idNguoiDung, diemTichLuy, ngaySinh) VALUES
(2, 120, '1995-04-12'),
(3, 50, '1998-08-23'),
(4, 0, '1990-11-02'),
(5, 340, '2001-01-15'),
(6, 15, '1993-06-30');


-- ==========================================================
-- 2. CHÈN DỮ LIỆU ĐỊA CHỈ GIAO HÀNG (Mỗi khách hàng có 1-2 địa chỉ)
-- ==========================================================
INSERT INTO DiaChiGiaoHang (idNguoiDung, tenNguoiNhan, soDienThoaiNhan, diaChiChiTiet, laMacDinh) VALUES
-- Địa chỉ của Nguyễn Văn A (id=2)
(2, 'Nguyễn Văn A', '0901234567', '123 Đường Nguyễn Trãi, Phường 3, Quận 5, TP. Hồ Chí Minh', TRUE),
(2, 'Anh A (Cơ quan)', '0988777666', 'Tòa nhà văn phòng Lotte, 54 Liễu Giai, Ba Đình, Hà Nội', FALSE),

-- Địa chỉ của Trần Thị B (id=3)
(3, 'Trần Thị B', '0902345678', '456 Đường Mậu Thân, Phường Xuân Khánh, Quận Ninh Kiều, Cần Thơ', TRUE),

-- Địa chỉ của Lê Văn C (id=4)
(4, 'Lê Văn C', '0903456789', '789 Đại lộ Hòa Bình, Phường Tân An, Quận Ninh Kiều, Cần Thơ', TRUE),
(4, 'Trần Hải Yến (Vợ anh C)', '0912233445', 'Số 12, Hẻm 5, Đường Mạc Thiên Tích, Xuân Khánh, Ninh Kiều, Cần Thơ', FALSE),

-- Địa chỉ của Phạm Thị D (id=5)
(5, 'Phạm Thị D', '0904567890', '101 Đường Trần Hưng Đạo, Phường Mỹ Xuyên, TP. Long Xuyên, An Giang', TRUE),

-- Địa chỉ của Hoàng Văn E (id=6)
(6, 'Hoàng Văn E', '0905678901', '202 Đường Lý Tự Trọng, Phường An Cư, Quận Ninh Kiều, Cần Thơ', TRUE);


-- ==========================================================
-- 3. CHÈN 5 DƯỢC SĨ (idNguoiDung từ 7 đến 11)
-- ==========================================================

-- Chèn vào bảng cha NguoiDung
INSERT INTO NguoiDung (idNguoiDung, hoTen, email, soDienThoai, matKhau, trangThai, vaiTro) VALUES
(7, 'Dược sĩ Nguyễn Tiến Minh', 'tienminh.ds@gmail.com', '0911234567', 'ds_secret2026', TRUE, 'DUOC_SI'),
(8, 'Dược sĩ Lê Thanh Hoa', 'thanhhoa.ds@gmail.com', '0912345678', 'ds_secret2026', TRUE, 'DUOC_SI'),
(9, 'Dược sĩ Phạm Minh Tuấn', 'minhtuan.ds@gmail.com', '0913456789', 'ds_secret2026', TRUE, 'DUOC_SI'),
(10, 'Dược sĩ Trần Thu Linh', 'thulinh.ds@gmail.com', '0914567890', 'ds_secret2026', TRUE, 'DUOC_SI'),
(11, 'Dược sĩ Hoàng Văn Nam', 'hoangnam.ds@gmail.com', '0915678901', 'ds_secret2026', TRUE, 'DUOC_SI');

-- Chèn vào bảng con DuocSi tương ứng với ID vừa tạo
INSERT INTO DuocSi (idNguoiDung, chungChiHanhNghe, noiCap, trinhDo) VALUES
(7, 'CCHN-CT-0891', 'Sở Y tế Cần Thơ', 'Đại học'),
(8, 'CCHN-HCM-2345', 'Sở Y tế TP. Hồ Chí Minh', 'Thạc sĩ'),
(9, 'CCHN-HN-7654', 'Sở Y tế Hà Nội', 'Đại học'),
(10, 'CCHN-CT-0432', 'Sở Y tế Cần Thơ', 'Cao đẳng'),
(11, 'CCHN-DN-9981', 'Sở Y tế Đà Nẵng', 'Đại học');





insert into DanhMucThuoc (idDanhMuc, tenDanhMuc, moTa) values (1, 'Thuốc hô hấp', 'Bao gồm thuốc ho, long đờm (Acetylcystein, Ambroxol, Dextromethorphan) và thuốc giãn phế quản. Dùng để giảm ho, cải thiện thông khí, hỗ trợ điều trị viêm phế quản, hen suyễn.');
insert into DanhMucThuoc (idDanhMuc, tenDanhMuc, moTa) values (2, 'Thuốc da liễu', 'Gồm thuốc bôi ngoài da (corticoid, kháng nấm, kháng khuẩn) và thuốc uống điều trị bệnh da liễu như viêm da dị ứng, nấm da, mụn trứng cá. Cần dùng đúng chỉ định để tránh tác dụng phụ như teo da, kháng thuốc.');
insert into DanhMucThuoc (idDanhMuc, tenDanhMuc, moTa) values (3, 'Thuốc dị ứng', 'Chủ yếu là kháng histamin (Loratadin, Cetirizin, Fexofenadin). Giúp giảm ngứa, nổi mề đay, viêm mũi dị ứng. Một số loại có thể gây buồn ngủ, khô miệng.');
insert into DanhMucThuoc (idDanhMuc, tenDanhMuc, moTa) values (4, 'Miếng dán, cao xoa, dầu', 'Là nhóm thuốc đặc trị như hóa chất chống ung thư (Cyclophosphamid, Cisplatin), thuốc nhắm trúng đích, thuốc miễn dịch. Tác dụng mạnh, nhiều tác dụng phụ, chỉ dùng theo phác đồ của bác sĩ chuyên khoa');
insert into DanhMucThuoc (idDanhMuc, tenDanhMuc, moTa) values (5, 'Thuốc kháng sinh kháng nấm', 'Kháng sinh: Penicillin, Cephalosporin, Macrolid, Quinolon. Dùng điều trị nhiễm khuẩn, cần tuân thủ liều để tránh kháng thuốc.Kháng nấm: Ketoconazol, Fluconazol, Itraconazol. Dùng cho nhiễm nấm da, nấm nội tạng');
insert into DanhMucThuoc (idDanhMuc, tenDanhMuc, moTa) values (6, 'Thuốc mắt tai mũi họng', 'Bao gồm thuốc nhỏ mắt (kháng sinh, chống viêm), thuốc nhỏ tai, thuốc xịt mũi (kháng histamin, corticoid). Dùng trong viêm kết mạc, viêm tai, viêm mũi dị ứng');
insert into DanhMucThuoc (idDanhMuc, tenDanhMuc, moTa) values (7, 'Thuốc hệ thần kinh', 'Gồm thuốc an thần, chống trầm cảm, thuốc chống động kinh, thuốc giảm đau thần kinh. Ví dụ: Diazepam, Carbamazepin, Fluoxetin. Cần theo dõi chặt chẽ vì ảnh hưởng trực tiếp đến não bộ.');
insert into DanhMucThuoc (idDanhMuc, tenDanhMuc, moTa) values (8, 'Thuốc giải độc khử độc', 'Dùng trong ngộ độc thuốc, hóa chất, kim loại nặng. Ví dụ: N-acetylcystein (giải độc paracetamol), Atropin (giải độc phospho hữu cơ), EDTA (giải độc chì).');
insert into DanhMucThuoc (idDanhMuc, tenDanhMuc, moTa) values (9, 'Thuốc bổ và vitamin', 'Bao gồm vitamin A, B, C, D, E và khoáng chất (sắt, kẽm, canxi). Giúp tăng cường sức khỏe, phòng thiếu hụt vi chất. Tuy nhiên, lạm dụng có thể gây thừa vitamin, ảnh hưởng gan thận.');
insert into DanhMucThuoc (idDanhMuc, tenDanhMuc, moTa) values (10, 'Thuốc giảm đau hạ  sốt', 'Phổ biến nhất là Paracetamol (an toàn, ít tác dụng phụ nếu dùng đúng liều). Ngoài ra có nhóm NSAID (Ibuprofen, Diclofenac) vừa giảm đau vừa kháng viêm, nhưng dễ gây kích ứng dạ dày.');
insert into DanhMucThuoc (idDanhMuc, tenDanhMuc, moTa) values (11, 'Chưa phân loại', 'Những thứ khác bổ sung bổ sung cho các nhóm trên, ví dụ thuốc chống nôn, thuốc chống say tàu xe, thuốc chống viêm, thuốc chống loét dạ dày.');





insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (1, 'Siro HoAstex-S 90ml', 'Húng chanh (45.00g), Núc nác (11.25g), Tinh dầu bạch đàn (119.52mg)', '90ml', 'Siro HoAtex dùng trị ho, giảm ho trong viêm họng, viêm phế quản, viêm khí quản (viêm đường hô hấp)', 'Chai', 53000, 'Không kê đơn', 2, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (1, 'Siro Deslotid OPV', '1ml chứa: Desloratadin (0.5mg)', '60ml', 'Siro Deslotid được chỉ định dùng trong các trường hợp sau: Viêm mũi dị ứng: Hắt hơi, sổ mũi, nghẹt mũi, ngứa mũi họng và ngứa, chảy nước mắt. Phản ứng dị ứng da: Mày đay, ngứa, phát ban.', 'Hộp', 65000, 'Không kê đơn', 2, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (1, 'Thuốc Tocemux', '1 viên chứa: Acetylcysteine (200mg)', '10 viên', 'Dùng làm thuốc tiêu chất nhầy trong bệnh nhầy nhớt (mucoviscidosis) (xơ nang tuyến tụy), bệnh lý hô hấp có đờm nhầy quánh như trong viêm phế quản cấp và mạn, và làm sạch thường quy trong mở khí quản.', 'Hộp', 70000, 'Không kê đơn', 5, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (1, 'Thuốc ho người lớn OPC', '90ml chứa: cineol (18.00mg), Hoàng cầm (1.80 ), Bạch linh (1.80 ), Thiên môn đông (2.70 )', '90ml', 'Điều trị các bệnh viêm nhiễm đường hô hấp, các chứng ho gió, ho cảm, ho có đàm, đau họng.', 'Chai', 38000, 'Không kê đơn', 7, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (1, 'Viên ngậm Strepsils Throat Irritation & Cough Reckitt Benckiser', '1 viên chứa: Ambroxol (15mg)', '12 viên', 'Viên ngậm Strepsils Throat Irritation & Cough Reckitt Benckiser là thuốc làm tan chất nhầy trong các bệnh đường hô hấp có tăng tiết chất nhầy (long đờm). Thuốc cũng được dùng để làm lỏng các chất nhầy đặc trong các bệnh phế quản và phổi cấp và mãn tính.', 'Vỉ', 55000, 'Không kê đơn', 10, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (2, 'Kem bôi da Ketoconazol 2% Medipharco', '1g chứa: Ketoconazol (20mg)', '10g', 'Thuốc bôi da Ketoconazol 2% Medipharco được dùng bôi tại chỗ để điều trị các bệnh nấm ở da và niêm mạc (Candida, Trichophyton rubrum, T. mentagrophytes, Epidermophyton floccosum, Malassezia furfur...).', 'Hộp', 11000, 'Không kê đơn', 7, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (2, 'Dung dịch PVP - IODINE 10% Danapha', '1 chai chứa: Povidone-iodine (10%)', '20mg', 'Thuốc Pvp - Iodine 10% được chỉ định dùng trong các trường hợp sau: Sát trùng vết thương hoặc vết bỏng bề mặt, mức độ nhẹ. Điều trị hỗ trợ các tình trạng da, niêm mạc tổn thương để tránh nhiễm khuẩn. Sát trùng da, niêm mạc trước khi phẫu thuật. Lau rửa các dụng cụ y tế trước khi tiệt khuẩn', 'Chai', 8500, 'Không kê đơn', -1, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (2, 'Thuốc bôi ngoài da Biroxime 1% ', '1g chứa: Clotrimazol (10mg)', '20g', 'Điều trị nấm da chân, nấm kẽ, nấm bẹn, lác đồng tiền. Bệnh nấm Candida do C.albicans.', 'Tuýp', 28000, 'Không kê đơn', 2, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (2, 'Dung dịch LeoPovidone 10% ', '1 chai chứa: Povidone-iodine (10%)', '15ml', 'Điều trị các vết thương và ngăn ngừa nhiễm trùng đối với các vi khuẩn nhạy cảm. LeoPovidone có thể được dùng cho các vết bỏng, vết trầy xước.', 'Chai', 16000, 'Không kê đơn', 10, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (2, 'Dung dịch dùng ngoài Xanh Methylen 1% ', '10ml chứa: Xanh Methylen (0.1g)', '17ml', 'Dung dịch dùng ngoài Xanh Methylen 1% dùng để điều trị chốc lở, viêm da mủ, điều trị nhiễm virus ngoài da.', 'Lọ', 13000, 'Không kê đơn', 3, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (3, 'Thuốc Cetirizin 10mg ', '1 viên chứa: Cetirizin (10mg)', '10 viên', 'Thuốc Cetirizin 10mg Trường Thọ được chỉ định điều trị triệu chứng viêm mũi dị theo mùa hoặc không theo mùa, các bệnh ngứa ngoài da do dị ứng, nổi mề đay mãn tính, bệnh viêm kết mạc dị ứng.', 'Hộp', 40000, 'Không kê đơn', 14, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (3, 'Thuốc Exopadin 60mg Trường Thọ', '1 viên chứa: Fexofenadin Hydroclorid (60mg)', '10 viên', 'Viêm mũi dị ứng: Exopadin được chỉ định để điều trị viêm mũi dị ứng theo mùa ở người lớn và trẻ em từ 12 tuổi trở lên. Mày đay vô căn mạn tính: Exopadin được chỉ định để điều trị các biểu hiện ngoài da không biến chứng của mày đay vô căn mạn tính ở người lớn và trẻ em từ 12 tuổi trở lên. Thuốc làm giảm ngứa và số lượng dát mày đay một cách đáng kể.', 'Vỉ', 60000, 'Không kê đơn', 10, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (3, 'Thuốc Clorpheniramin 4mg Khapharco', '1 viên chứa: Clorpheniramin maleat (4mg)', '10 viên', 'Clorpheniramin maleat được dùng để điều trị triệu chứng các bệnh dị ứng như mày đay, phù mạch, viêm mũi dị ứng, viêm màng tiếp hợp dị ứng và ngứa. Thuốc là thành phần phổ biến trong many chế phẩm để điều trị ho, cảm lạnh. Tuy vậy, các chế phẩm này phải dùng thận trọng ở trẻ em và thường phải tránh dùng cho trẻ nhỏ dưới 2 tuổi, vì có nguy cơ gây tử vong.', 'Vỉ', 2000, 'Không kê đơn', 7, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (3, 'Thuốc Allerphast 180mg Mebiphar', '1 viên chứa: Fexofenadin Hydroclorid (180mg)', '10 viên', 'Ðiều trị triệu chứng trong viêm mũi dị ứng theo mùa, mày đay mạn tính vô căn ở người lớn và trẻ em trên 6 tuổi', 'Vỉ', 2500, 'Không kê đơn', -1, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (3, 'Thuốc Histalong - L 5mg Dr. Reddy', '1 viên chứa: Levocetirizine (5mg)', '10 viên', 'Ðiều trị triệu chứng viêm mũi dị ứng (kể cả viêm mũi dị ứng dai dẳng) and mày đay ở người lớn và trẻ em từ 6 tuổi trở lên.', 'Vỉ', 38000, 'Không kê đơn', -1, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (4, 'Cao Sao Vàng Danapha', 'Camphor (4.128 ), Menthol (0.656 ), Tinh dầu bạc hà (2 ), Tinh dầu tràm (1.408 ), Tinh dầu đinh hương (0.144 )', '16g', 'Cao xoa Sao Vàng chỉ định điều trị trong các trường hợp cảm cúm, đau đầu, sổ mũi, chóng mặt, đau khớp, bị muỗi và côn trùng khác đốt.', 'Hộp', 29000, 'Không kê đơn', 14, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (4, 'Dầu gừng Thái Dương', '24ml chứa: Gừng (12g), Tinh dầu bạc hà (0.96ml), Methyl salicylate (4.8g), Long não (0.48ml)', '24ml', 'Đau đầu, đau lưng, đau dây thần kinh, đau vai gáy, đau nhức do phong thấp, lòng bàn chân, bàn tay lạnh giá, tê, mỏi. Cảm cúm, ngạt mũi, sổ mũi, đau bụng lạnh, buồn nôn do cảm gió, cảm lạnh, say tàu xe, ngứa do muỗi đốt, côn trùng cắn.', 'Chai', 80000, 'Không kê đơn', 2, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (4, 'Dầu Khuynh Diệp OPC', 'Eucalyptol (12.44g)', '25ml', 'Phòng, trị cảm cúm, sổ mũi, nghẹt mũi, ho tức ngực, đau bụng, nhức mỏi, nhức đầu, chóng mặt, buồn nôn, côn trùng đốt, trật gân, sưng.', 'Chai', 83000, 'Không kê đơn', -1, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (4, 'Cao dán Salonpas Diclofenac Patch Hisamitsu', 'Diclofenac Sodium (15mg)', '2 miếng', 'Người lớn và trẻ em từ 15 tuổi trở lên: Dùng giảm đau, kháng viêm trong các cơn đau liên quan đến: Đau cơ.Đau vai.Đau lưng.Bầm tím.Bong gân.Căng cơ.Đau khớp.Viêm gân.Đau khuỷu tay.', 'Gói', 45000, 'Không kê đơn', -1, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (4, 'Cao dán Salonsip Gel - Patch Hisamitsu', 'L-menthol (1g), DL-camphor (0.3g), Glycol salicylate (1.25g), Tocopherol acetat (1g)', '3 miếng', 'Cao dán Salonsip Gel - Patch chỉ định dùng cho người lớn và trẻ em từ 30 tháng tuổi trở lên dùng giảm đau, kháng viêm trong các cơn đau liên quan đến: Mỏi cơ, đau cơ, đau vai, đau lưng đơn thuần, bầm tím, bong gân, căng cơ, viêm khớp.', 'Gói', 34000, 'Không kê đơn', 7, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (5, 'Thuốc Fucagi 500mg Agimexpharm', '1 viên chứa: Mebendazol (500mg)', '1 viên', 'Điều trị trong các trường hợp nhiễm một hay nhiều loại giun đường ruột: Enterobius vermicularis (giun kim); Trichuris trichiura (giun tóc); Ascaris lumbricoides (giun đũa); Ancylostoma duodenale, Necator americanus (giun móc)', 'Hộp', 8000, 'Không kê đơn', 3, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (5, 'Thuốc mỡ bôi da Agiclovir 5% Agimexpharm', '5g chứa: Aciclovir (0.25g), Excipients q.s (5g)', '5g', 'Các trường hợp nhiễm Herpes simplex trên da và niêm mạc, nhiễm Herpes zoster, Herpes sinh dục, Herpes môi khởi phát và tái phát.', 'Tuýp', 10000, 'Không kê đơn', 2, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (5, 'Thuốc Fugacar 500mg Janssen', '1 viên chứa: Mebendazole (500mg)', '1 viên 500mg', 'Để điều trị nhiễm một hoặc nhiều loại giun ở đường ruột bao gồm giun tóc (Trichuris trichuria), giun kim (Enterobius vermicularis), giun đũa (Ascaris lumbricoides), giun móc (Ancylostoma duodenale, Necator americanus). Không có bằng chứng nào cho thấy viên nén Fugacar có hiệu quả trong điều trị bệnh nhiễm ấu trùng sán lợn.', 'Hộp', 23000, 'Không kê đơn', 2, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (5, 'Viên nén Mebendazole 500mg Mekophar', '1 viên chứa: Mebendazole (500mg)', '1 viên', 'Điều trị nhiễm một hay nhiều loại giun, như giun kim, giun tóc, giun móc, giun đũa và giun lươn.', 'hộp', 2200, 'Không kê đơn', 5, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (5, 'Thuốc rơ miệng Nyst 25.000IU OPC', '1g chứa: Nystatin (25000iu)', '1g', 'Thuốc Nyst 25.000 IU được chỉ định dùng trong trường hợp dự phòng và điều trị bệnh Candida miệng (đẹn): Tưa miệng, viêm miệng, lưỡi bị mất nhú, lưỡi đẹn, viêm họng do Candida albicans.', 'Gói', 1900, 'Không kê đơn', 2, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (6, 'Dung dịch xịt mũi Otrera 0.1% An Thiên', 'Xylometazoline hydrochloride (0.1%)', '10ml', 'Nghẹt mũi, sung huyết mũi do viêm mũi cấp hoặc mạn tính, viêm xoang, cảm lạnh, cảm mạo, dị ứng đường hô hấp trên. Hỗ trợ điều trị sung huyết mũi họng trong viêm tai giữa.', 'Hộp', 2500, 'Không kê đơn', 7, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (6, 'Thuốc viêm xoang Abipha Cap', '1 viên chứa: Bạch chỉ (225mg), Thương nhĩ tử (300mg), Phòng phong (225mg), Hoàng kỳ (375mg), Tân di hoa', '10 viên', 'Nghẹt mũi, sổ mũi, chảy nước mũi, nhức đầu vùng trán, viêm mũi dị ứng, viêm mũi, viêm xoang cấp và mạn tính', 'Vỉ', 400, 'Không kê đơn', 10, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (6, 'Thuốc nhỏ mắt V.Rohto Vitamin', '13ml chứa: Natri chondroitin sulfat (13.0mg), Chlorphenamine (3.9mg), Vitamin B6 (13.0mg)', '13ml', 'Hỗ trợ cải thiện tình trạng giảm thị lực. Xung huyết kết mạc.Mắt mờ (do tiết dịch).Ngứa mắt.Mắt mỏi mệt.Viêm mí mắt.Phòng ngừa các bệnh về mắt (do bơi lội hoặc bụi, mồ hôi rơi vào mắt).Viêm mắt do tia tử ngoại hoặc do các tia sáng khác (như mù tuyết).Cảm giác khó chịu khi sử dụng kính tiếp xúc cứng.', 'Chai', 55000, 'Không kê đơn', -1, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (6, 'Nước súc miệng Thái Dương hương bạc hà', '250ml chứa: Tinh dầu bạc hà (0.2), Menthol (0.2), Long não (2)', '500ml', 'Sát trùng răng, miệng, vòm họng, thúc đẩy tuần hoàn lợi (nướu), ngăn ngừa nguy cơ cao răng và viêm nhiễm gây sâu răng. Giúp khử sạch mùi hôi miệng, giữ cho hơi thở thơm mát. Giúp răng chắc khỏe mỗi ngày.', 'Chai', 400, 'Không kê đơn', 2, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (6, 'Ống hít Sao Vàng Danapha', 'Menthol (769mg), Camphor (145mg), Tinh dầu đinh hương (312.5mg), Tinh dầu tràm (39mg', '1.5g', 'công dụng', 'Ống', 9000, 'Không kê đơn', 5, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (7, 'Viên an thần Mimosa OPC', '1 viên chứa: Củ Bình vôi (150mg), lá sen (180mg), Lá vông nem (600mg), Lạc tiên (600mg), Trinh nữ', '10 viên', 'Dùng cho những trường hợp mất ngủ hoặc giấc ngủ đến chậm, suy nhược thần kinh.   Dùng thay thế cho Diazepam khi bệnh nhân bị quen thuốc.', 'Vỉ', 1900, 'Không kê đơn', 10, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (7, 'Thuốc chống say tàu xe Momvina Hadiphar', '1 viên chứa: Dimenhydrinate (50mg)', '4 viên', 'Thuốc chống say tàu xe Momvina dùng trong phòng và điều trị chứng buồn nôn, nôn, chóng mặt khi say tàu xe. Điều trị chứng nôn và chóng mặt trong bệnh Ménière và các rối loạn tiền đình khác.', 'Vỉ', 91000, 'Không kê đơn', -1, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (7, 'Dung dịch uống Laferine 80mg Cho-A', '1 ống chứa: Cao khô lá Bạch quả (80mg)', '20 ml', 'Người bị suy giảm khả năng ghi nhớ, kém tập trung, giảm trí nhớ và đặc biệt ở người lớn tuổi. Thiểu năng chức năng tuần hoàn máu não.Chóng mặt, đau đầu, đau nửa đầu, ù tai, giảm thính lực.Chân đi kiểu chân cao, chân thấp, loạng choạng.Một số người bị thiếu máu võng mạc.Nhược dương.', 'Ống', 60000, 'Không kê đơn', 2, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (7, 'Thuốc Dưỡng Tâm An Thần Danapha', '1 viên chứa: Long nhãn (91.25mg), Lá vông (91.25mg), Lá dâu (91.25mg), Hắc táo nhân (91.25mg)', '100 viên', 'Mất ngủ do lo âu, làm việc quá sức, tim đập hồi hộp.Tâm thần bất an, giảm trí nhớ, suy nhược cơ thể, ăn không ngon.', 'Hộp', 500, 'Không kê đơn', 3, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (7, 'Thuốc Hoàn An Thần Traphaco', '1 viên chứa: Táo Nhân (2g), Thảo quyết minh (1.5g), Tâm sen (1g), Đăng tâm thảo (0.6g)', '10 viên', 'Mất ngủ do suy nhược cơ thể. Các trường hợp lo lắng căng thẳng, khó ngủ, giấc ngủ không sâu dẫn đến mệt mỏi.', 'Vỉ', 400, 'Không kê đơn', 10, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (8, 'Viên nhai OH NO Việt Phúc', '1 viên chứa: Nicotine (2mg), Natri hydro carbonat (30mg), Sorbitol (65mg), Aspartame (1.5mg)Amoxicillin', '12 viên', 'công dụng', 'Lọ', 36000, 'Không kê đơn', 14, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (8, 'Thuốc tiêm Vinluta 600 Vinphaco', 'Glutathion (600mg)', '10ml', 'Hỗ trợ làm giảm độc tính trên thần kinh của xạ trị và của các hóa chất điều trị ung thư bao gồm cisplatin, cyclophosphamid, oxaplatin, 5-fluorouracil, carboplatin: Tiêm truyền tĩnh mạch ngay trước khi tiến hành xạ trị và trước phác đồ hóa trị liệu của các hóa chất trên. Hỗ trợ điều trị ngộ độc thủy ngân: Phối hợp các thuốc điều trị ngộ độc thủy ngân đặc hiệu như 2,3-dimercaptopropanl-sulfonat và meso-1,3-dimercaptosuccinic acid với tiêm truyền glutathion và vitamin C liều cao làm giảm nồng độ thủy ngân trong máu.Hỗ trợ điều trị xơ gan do rượu, xơ gan, viêm gan do virus B, C, D và gan nhiễm mỡ giúp cải thiện thể trạng của bệnh nhân và các chỉ số sinh hóa như bilirubin, GOT, GT cũng như giảm MDA và tổn thương tế bào gan rõ rệt.', 'Hộp', 400, 'Không kê đơn', 14, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (8, 'Bột pha tiêm và dung môi Glumidtab 600 Pharbaco', 'Glutathione (600mg)', '600ml', 'Hỗ trợ giảm độc tính trên thần kinh của xạ trị và của các hóa chất điều trị ung thư. Hỗ trợ trong điều trị xơ gan do rượu, xơ gan, viêm gan do virus B, C, D và gan nhiễm mỡ.Hỗ trợ trong điều trị liên quan đến rối loạn mạch ngoại vi, mạch vành và các rối loạn huyết học.Cải thiện đáp ứng vận mạch với các thuốc giãn mạch vành như acetylcholin, nitro glycerin ở những bệnh nhân có các yếu tố nguy cơ bệnh mạch vành.Cải thiện tình trạng thiếu máu ở các bệnh nhân lọc máu do suy thận mãn.Hỗ trợ điều trị đái tháo đường không phụ thuộc insulin.Hỗ trợ trong điều trị viêm tụy cấp.Hỗ trợ điều trị ngộ độc thủy ngân.Hỗ trợ điều trị chảy máu dưới nhện.', 'Hộp', 50000, 'Không kê đơn', 7, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (8, 'Thuốc Methionin 250mg Domesco', '1 viên chứa: Methionin (250mg)', '100 viên', 'mô tả', 'Hộp', 49000, 'Không kê đơn', 3, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (8, 'Thuốc Nodict 50mg Sun Pharma', '1 viên chứa: Naltrexone HCL (50mg)', '10 viên', 'Dùng cho bệnh nhân cắt cơn cai nghiện ma túy và mong muốn được điều trị để duy trì chống tái nghiện', 'Vỉ', 5000, 'Không kê đơn', 10, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (9, 'Siro Ceelin + Z United', '5ml chứa: Kẽm (10mg), Vitamin C (100mg)', '60 ml', 'mô tả', 'Hộp', 72000, 'Không kê đơn', 5, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (9, 'Dung dịch uống Pokemine 50mg Medisun', '10ml chứa: Sắt (50mg)', '10 ml', 'Bổ sung sắt cho bệnh nhân có nguy cơ bị thiếu máu do thiếu sắt như: Phụ nữ mang thai. Phụ nữ cho con bú. Người suy dinh dưỡng. Người bệnh sau phẫu thuật. Trẻ em thiếu máu do thiếu sắt, chậm lớn, còi cọc', 'Ống', 500, 'Không kê đơn', 5, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (9, 'Thuốc Enervon United', '1 viên chứa: Vitamin B12 (5mcg), Vitamin B1 (50mg), Vitamin B2 (20mg), Vitamin B3 (50mg), Vitamin B5', '10 viên', 'Thuốc Enervon là chế phẩm bổ sung để điều trị thiếu vitamin C và B ở người lớn và thanh thiếu niên trên 16 tuổi trong trường hợp thiếu hụt hoặc tăng nhu cầu như thời kì tăng trưởng nhanh, mệt mỏi, các trường hợp gắng sức về tinh thần và thể chất.', 'Vỉ', 2290, 'Không kê đơn', 5, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (9, 'Thuốc Myhemo 305mg Reliv', '1 viên chứa: Acid folic (0.35mg), Sắt (100mg)', '1o viên', 'Dự phòng thiếu sắt và folic acid trong khi mang thai.', 'Vỉ', 5500, 'Không kê đơn', 2, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (9, 'Thuốc Cardioton Lipa Pharma', '1 viên chứa: Ubidecarenone (30mg), D-alpha tocopherol (6.71mg)', '10 viên', 'mô tả', 'Vỉ', 7670, 'Không kê đơn', 3, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (10, 'Thuốc Actadol 500 Medipharco', '1 viên chứa: Paracetamol (500mg)', '10 viên', 'Paracetamol được dùng rộng rãi trong điều trị các chứng đau và sốt từ nhẹ đến vừa. Đau:Paracetamol được dùng giảm đau tạm thời trong điều trị chứng đau nhẹ và vừa: Đau đầu, đau răng, đau bụng kinh, đau cơ... Thuốc có hiệu quả nhất là làm giảm đau cường độ thấp có nguồn gốc không phải nội tạng. Paracetamol không có tác dụng trị thấp khớp.Paracetamol là thuốc thay thế salicylat (được ưa thích ở người bệnh chống chỉ định hoặc không dung nạp salicylat) để giảm đau nhẹ hoặc hạ sốt;Sốt:Paracetamol thường được dùng để giảm thân nhiệt ở người bệnh sốt, khi sốt có thể có hại hoặc khi hạ sốt, người bệnh sẽ dễ chịu hơn. Tuy vậy, liệu pháp hạ sốt nói chung không đặc hiệu, không ảnh hưởng đến tiến trình của bệnh cơ bản, và có thể che lấp tình trạng bệnh của người bệnh', 'Vỉ', 500, 'Không kê đơn', 3, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (10, 'Viên nén Paracetamol Stada 500mg', '1 viên chứa: Paracetamol (500mg)', '10 viên', 'Paracetamol có tác dụng gì? Thuốc Paracetamol 500mg được chỉ định điều trị trong các trường hợp sau: Các cơn đau từ nhẹ đến trung bình bao gồm đau đầu, đau nửa đầu, đau thần kinh đau răng, đau họng, đau do hành kinh, đau nhức.Giảm triệu chứng đau nhức do thấp khớp, cảm cúm, cảm sốt và cảm lạnh.', 'Vỉ', 400, 'Không kê đơn', 10, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (10, 'Viên sủi Tovalgan Ef Trường Thọ Pharma', '1 viên chứa: Paracetamol (500mg)', '4 viên', 'Viên nén sủi bọt Tovalgan Ef chứa Paracetamol là một chất giảm đau và hạ sốt được dùng trong các trường hợp: Các cơn đau nhẹ đến trung bình bao gồm: Nhức đầu, đau nhức do cảm lạnh hay cảm cúm, đau họng, đau do hành kinh, đau nhức cơ xương, đau sau khi tiêm ngừa hay nhổ răng, đau răng, đau trong viêm xương khớp, nhức nửa đầu.Sốt.', 'Vỉ', 40000, 'Không kê đơn', 3, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (10, 'Bột Glotadol 250 Abbott', '1 gói chứa: Paracetamol (250mg)Paracetamol', '2.5g', 'Bột pha hỗn dịch uống Glotadol có dùng hạ sốt và giảm các cơn đau do cảm cúm hay cảm lạnh thông thường, đau đầu, đau họng, mọc răng, tiêm ngừa, cắt amiđan.', 'Gói', 48000, 'Không kê đơn', 7, TRUE);
insert into Thuoc (idDanhMuc, tenThuoc, thanhPhan, hamLuong, congDung, donViTinh, giaBan, yeuCauKeDon, gioiHanMua, trangThai) values (10, 'Thuốc Ameflu Không Gây Buồn Ngủ OPV', '1 viên chứa: Paracetamol (500mg), Phenylephrine hydrochloride (5mg), Caffeine (25mg)', '10 viên', 'Thuốc Ameflu được chỉ định dùng trong các trường hợp sau: Làm giảm các triệu chứng cảm lạnh và cảm cúm như nhức đầu, đau họng, đau nhức cơ thể, sung huyết mũi, đau xoang trong viêm xoang và sốt.', 'Vỉ', 1100, 'Không kê đơn', 2, TRUE);






insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (1, 'LO-2026-1', '2025-09-22', '2027-03-26', 176, 80882);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (2, 'LO-2026-2', '2025-07-19', '2030-02-18', 67, 29864);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (3, 'LO-2026-3', '2026-03-23', '2025-08-29', 195, 2433);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (4, 'LO-2026-4', '2025-09-03', '2029-12-31', 104, 31143);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (5, 'LO-2026-5', '2026-05-25', '2027-08-26', 177, 57486);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (6, 'LO-2026-6', '2025-09-21', '2029-03-31', 16, 60560);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (7, 'LO-2026-7', '2026-01-28', '2025-11-28', 3, 63166);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (8, 'LO-2026-8', '2026-04-10', '2026-09-27', 45, 62868);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (9, 'LO-2026-9', '2026-05-21', '2025-11-21', 53, 19297);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (10, 'LO-2026-10', '2025-07-02', '2026-05-11', 170, 24810);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (11, 'LO-2026-11', '2025-11-15', '2026-04-29', 21, 80975);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (12, 'LO-2026-12', '2025-12-05', '2029-04-11', 125, 68859);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (13, 'LO-2026-13', '2025-09-02', '2027-02-23', 97, 96241);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (14, 'LO-2026-14', '2026-02-28', '2026-02-13', 167, 98496);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (15, 'LO-2026-15', '2025-08-08', '2028-08-14', 21, 54830);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (16, 'LO-2026-16', '2026-04-22', '2025-12-23', 131, 54111);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (17, 'LO-2026-17', '2026-04-29', '2028-11-12', 140, 32289);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (18, 'LO-2026-18', '2025-12-22', '2027-10-17', 43, 21350);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (19, 'LO-2026-19', '2026-02-16', '2029-02-27', 177, 82509);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (20, 'LO-2026-20', '2025-07-28', '2029-07-19', 80, 8056);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (21, 'LO-2026-21', '2026-04-16', '2026-09-25', 194, 12149);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (22, 'LO-2026-22', '2026-03-24', '2025-07-09', 31, 55362);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (23, 'LO-2026-23', '2025-07-05', '2026-01-28', 76, 48871);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (24, 'LO-2026-24', '2026-06-23', '2027-08-14', 39, 56118);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (25, 'LO-2026-25', '2025-10-27', '2030-05-13', 86, 37772);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (26, 'LO-2026-26', '2026-02-02', '2028-12-01', 55, 78038);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (27, 'LO-2026-27', '2026-06-17', '2028-04-23', 163, 38201);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (28, 'LO-2026-28', '2025-12-08', '2028-08-24', 71, 70014);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (29, 'LO-2026-29', '2025-09-15', '2025-11-05', 136, 5435);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (30, 'LO-2026-30', '2026-01-22', '2028-03-28', 146, 30989);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (31, 'LO-2026-31', '2025-07-11', '2030-04-27', 158, 29585);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (32, 'LO-2026-32', '2025-10-29', '2029-08-26', 36, 45007);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (33, 'LO-2026-33', '2025-08-11', '2028-06-07', 62, 94712);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (34, 'LO-2026-34', '2026-01-23', '2029-01-11', 67, 71713);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (35, 'LO-2026-35', '2025-12-07', '2029-10-03', 20, 23410);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (36, 'LO-2026-36', '2025-11-18', '2028-10-01', 5, 28164);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (37, 'LO-2026-37', '2026-06-10', '2026-08-16', 7, 16996);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (38, 'LO-2026-38', '2025-11-16', '2027-05-14', 49, 74869);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (39, 'LO-2026-39', '2025-10-07', '2028-04-11', 69, 49816);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (40, 'LO-2026-40', '2025-09-25', '2026-04-07', 177, 99903);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (41, 'LO-2026-41', '2026-01-26', '2029-04-22', 171, 61752);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (42, 'LO-2026-42', '2026-05-11', '2028-04-11', 6, 79506);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (43, 'LO-2026-43', '2026-01-12', '2028-11-14', 151, 72568);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (44, 'LO-2026-44', '2025-08-14', '2028-12-07', 109, 6518);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (45, 'LO-2026-45', '2025-12-08', '2029-12-16', 21, 97532);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (46, 'LO-2026-46', '2026-04-09', '2026-03-11', 106, 98945);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (47, 'LO-2026-47', '2025-10-04', '2030-06-17', 181, 79470);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (48, 'LO-2026-48', '2026-02-23', '2028-01-18', 5, 32004);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (49, 'LO-2026-49', '2026-01-22', '2030-01-06', 159, 84806);
insert into LoThuoc (idThuoc, maLo, ngaySanXuat, hanSuDung, soLuongTon, giaNhap) values (50, 'LO-2026-50', '2026-02-19', '2029-06-24', 88, 38303);

-- ==========================================================
-- 1. GIỎ HÀNG & CHI TIẾT GIỎ HÀNG
-- (Mỗi khách hàng idNguoiDung từ 2 -> 6 có 1 giỏ hàng)
-- ==========================================================
INSERT INTO GioHang (idGioHang, idKhachHang, ngayTao) VALUES
(1, 2, '2026-01-10 08:30:00'),
(2, 3, '2026-02-15 10:15:00'),
(3, 4, '2026-03-01 14:20:00'),
(4, 5, '2026-05-12 09:00:00'),
(5, 6, '2026-07-01 16:45:00');

INSERT INTO ChiTietGioHang (idGioHang, idThuoc, idDonThuoc, soLuong, donGia, trangThaiThaoTac) VALUES
-- Giỏ hàng KH 2
(1, 1, NULL, 2, 53000.00, 'CHO_PHEP'),
(1, 12, NULL, 1, 60000.00, 'CHO_PHEP'),
-- Giỏ hàng KH 3
(2, 5, NULL, 3, 55000.00, 'CHO_PHEP'),
(2, 18, NULL, 1, 83000.00, 'CHO_PHEP'),
-- Giỏ hàng KH 4
(3, 28, NULL, 2, 55000.00, 'CHO_PHEP'),
-- Giỏ hàng KH 5
(4, 37, NULL, 1, 36000.00, 'CHO_PHEP'),
(4, 41, NULL, 5, 72000.00, 'CHO_PHEP'),
-- Giỏ hàng KH 6
(5, 46, NULL, 2, 500.00, 'CHO_PHEP');


-- ==========================================================
-- 2. ĐƠN HÀNG (DonHang)
-- Đa dạng thời gian (2025 - 2026) và các trạng thái
-- ==========================================================
INSERT INTO DonHang (idDonHang, idKhachHang, ngayDat, tongTien, phiVanChuyen, trangThai, lyDoHuy) VALUES
-- Đơn hàng năm 2025
(1, 2, '2025-11-10 09:15:00', 166000.00, 15000.00, 'DA_GIAO', NULL),
(2, 3, '2025-12-05 14:30:00', 165000.00, 20000.00, 'DA_HUY', 'Khách hàng đổi ý không mua nữa'),
(3, 4, '2025-12-20 18:00:00', 183000.00, 15000.00, 'DA_GIAO', NULL),

-- Đơn hàng năm 2026 (Quý 1 & 2)
(4, 2, '2026-01-15 10:00:00', 210000.00, 0.00, 'DA_GIAO', NULL),
(5, 5, '2026-02-14 11:20:00', 135000.00, 15000.00, 'DA_GIAO', NULL),
(6, 6, '2026-03-08 15:45:00', 90000.00, 15000.00, 'DA_HUY', 'Đặt nhầm sản phẩm'),
(7, 3, '2026-04-10 08:30:00', 400000.00, 0.00, 'DA_GIAO', NULL),
(8, 4, '2026-05-22 16:10:00', 144000.00, 15000.00, 'DA_GIAO', NULL),
(9, 2, '2026-06-18 13:25:00', 170000.00, 15000.00, 'DA_GIAO', NULL),

-- Đơn hàng gần đây (Tháng 7/2026)
(10, 5, '2026-07-20 09:00:00', 220000.00, 15000.00, 'DA_GIAO', NULL),
(11, 6, '2026-07-22 14:10:00', 110000.00, 15000.00, 'DANG_GIAO', NULL),
(12, 3, '2026-07-23 10:05:00', 165000.00, 15000.00, 'CHO_XAC_NHAN', NULL);


-- ==========================================================
-- 3. CHI TIẾT ĐƠN HÀNG (ChiTietDonHang)
-- ==========================================================
INSERT INTO ChiTietDonHang (idDonHang, idThuoc, soLuong, donGia, giamGia) VALUES
-- Đơn 1 (Đã giao - 166.000đ)
(1, 1, 2, 53000.00, 0.00),         -- Siro HoAstex-S (106k)
(1, 12, 1, 60000.00, 0.00),        -- Exopadin 60mg (60k)

-- Đơn 2 (Đã hủy)
(2, 5, 3, 55000.00, 0.00),         -- Strepsils (165k)

-- Đơn 3 (Đã giao - 183.000đ)
(3, 17, 1, 80000.00, 0.00),        -- Dầu gừng Thái Dương (80k)
(3, 18, 1, 83000.00, 0.00),        -- Dầu Khuynh Diệp OPC (83k)
(3, 8, 1, 20000.00, 0.00),         -- Biroxime (20k)

-- Đơn 4 (Đã giao - 210.000đ)
(4, 3, 3, 70000.00, 0.00),         -- Tocemux (210k)

-- Đơn 5 (Đã giao - 135.000đ)
(5, 19, 3, 45000.00, 0.00),        -- Cao dán Salonpas (135k)

-- Đơn 6 (Đã hủy)
(6, 20, 2, 34000.00, 0.00),        -- Cao dán Salonsip (68k)
(6, 24, 1, 2200.00, 0.00),         -- Mebendazole (2.2k)

-- Đơn 7 (Đã giao - 400.000đ)
(7, 43, 10, 40000.00, 0.00),       -- Tovalgan Ef (400k)

-- Đơn 8 (Đã giao - 144.000đ)
(8, 41, 2, 72000.00, 0.00),        -- Ceelin + Z (144k)

-- Đơn 9 (Đã giao - 170.000đ)
(9, 28, 2, 55000.00, 0.00),        -- V.Rohto Vitamin (110k)
(9, 33, 1, 60000.00, 0.00),        -- Laferine 80mg (60k)

-- Đơn 10 (Đã giao - 220.000đ)
(10, 1, 2, 53000.00, 3000.00),     -- Siro HoAstex-S (100k)
(10, 12, 2, 60000.00, 0.00),       -- Exopadin 60mg (120k)

-- Đơn 11 (Đang giao)
(11, 6, 10, 11000.00, 0.00),       -- Ketoconazol (110k)

-- Đơn 12 (Chờ xác nhận)
(12, 5, 3, 55000.00, 0.00);        -- Strepsils (165k)


-- ==========================================================
-- 4. THANH TOÁN (ThanhToan)
-- ==========================================================
INSERT INTO ThanhToan (idDonHang, phuongThuc, soTien, ngayThanhToan, trangThai) VALUES
(1, 'Thanh toán khi nhận hàng (COD)', 181000.00, '2025-11-12 14:20:00', 'DA_THANH_TOAN'),
(2, 'Chuyển khoản ngân hàng', 185000.00, '2025-12-05 14:35:00', 'DA_HOAN_TIEN'),
(3, 'Thanh toán khi nhận hàng (COD)', 198000.00, '2025-12-22 10:15:00', 'DA_THANH_TOAN'),
(4, 'Ví MoMo', 210000.00, '2026-01-15 10:02:00', 'DA_THANH_TOAN'),
(5, 'Thanh toán khi nhận hàng (COD)', 150000.00, '2026-02-16 16:40:00', 'DA_THANH_TOAN'),
(6, 'Ví MoMo', 105000.00, '2026-03-08 15:46:00', 'DA_HOAN_TIEN'),
(7, 'Chuyển khoản ngân hàng', 400000.00, '2026-04-10 08:32:00', 'DA_THANH_TOAN'),
(8, 'Thanh toán khi nhận hàng (COD)', 159000.00, '2026-05-24 11:00:00', 'DA_THANH_TOAN'),
(9, 'Ví VNPay', 185000.00, '2026-06-18 13:27:00', 'DA_THANH_TOAN'),
(10, 'Chuyển khoản ngân hàng', 235000.00, '2026-07-20 09:05:00', 'DA_THANH_TOAN'),
(11, 'Thanh toán khi nhận hàng (COD)', 125000.00, '2026-07-22 14:10:00', 'CHUA_THANH_TOAN'),
(12, 'Chuyển khoản ngân hàng', 180000.00, '2026-07-23 10:05:00', 'CHUA_THANH_TOAN');


-- ==========================================================
-- 5. BÁO CÁO THỐNG KÊ (BaoCaoThongKe)
-- Lưu lại các lịch sử xuất báo cáo của admin (idNguoiDung = 1)
-- ==========================================================
INSERT INTO BaoCaoThongKe (idQuanTriVien, thoiGianBatDau, thoiGianKetThuc, loaiBaoCao) VALUES
(1, '2025-01-01', '2025-12-31', 'Báo cáo doanh thu năm 2025'),
(1, '2026-01-01', '2026-03-31', 'Báo cáo doanh thu Quý 1/2026'),
(1, '2026-04-01', '2026-06-30', 'Báo cáo doanh thu Quý 2/2026'),
(1, '2026-07-01', '2026-07-23', 'Báo cáo bán hàng tháng 7/2026');


INSERT INTO HinhAnhThuoc (idThuoc, duongDan) VALUES
-- Thuốc 1
(1, 'assets/images/uploads/1/1-1.jpg'),
(1, 'assets/images/uploads/1/1-2.jpg'),
(1, 'assets/images/uploads/1/1-3.jpg'),

-- Thuốc 2
(2, 'assets/images/uploads/2/2-1.jpg'),
(2, 'assets/images/uploads/2/2-2.jpg'),
(2, 'assets/images/uploads/2/2-3.jpg'),

-- Thuốc 3
(3, 'assets/images/uploads/3/3-1.jpg'),
(3, 'assets/images/uploads/3/3-2.jpg'),
(3, 'assets/images/uploads/3/3-3.jpg'),
(3, 'assets/images/uploads/3/3-4.jpg'),
(3, 'assets/images/uploads/3/3-5.jpg'),

-- Thuốc 4
(4, 'assets/images/uploads/4/4-1.jpg'),
(4, 'assets/images/uploads/4/4-2.jpg'),
(4, 'assets/images/uploads/4/4-3.jpg'),

-- Thuốc 5
(5, 'assets/images/uploads/5/5-1.jpg'),
(5, 'assets/images/uploads/5/5-2.jpg'),
(5, 'assets/images/uploads/5/5-3.jpg'),

-- Thuốc 6
(6, 'assets/images/uploads/6/6-1.jpg'),
(6, 'assets/images/uploads/6/6-2.jpg'),
(6, 'assets/images/uploads/6/6-3.jpg'),

-- Thuốc 7
(7, 'assets/images/uploads/7/7-1.jpg'),
(7, 'assets/images/uploads/7/7-2.jpg'),
(7, 'assets/images/uploads/7/7-3.jpg'),

-- Thuốc 8
(8, 'assets/images/uploads/8/8-1.jpg'),

-- Thuốc 9
(9, 'assets/images/uploads/9/9-1.jpg'),
(9, 'assets/images/uploads/9/9-2.jpg'),
(9, 'assets/images/uploads/9/9-3.jpg'),
(9, 'assets/images/uploads/9/9-4.jpg'),

-- Thuốc 10
(10, 'assets/images/uploads/10/10-1.jpg'),

-- Thuốc 11
(11, 'assets/images/uploads/11/11-1.jpg'),
(11, 'assets/images/uploads/11/11-2.jpg'),
(11, 'assets/images/uploads/11/11-3.jpg'),
(11, 'assets/images/uploads/11/11-4.jpg'),

-- Thuốc 12
(12, 'assets/images/uploads/12/12-1.jpg'),
(12, 'assets/images/uploads/12/12-2.jpg'),

-- Thuốc 13
(13, 'assets/images/uploads/13/13-1.jpg'),
(13, 'assets/images/uploads/13/13-2.jpg'),
(13, 'assets/images/uploads/13/13-3.jpg'),
(13, 'assets/images/uploads/13/13-4.jpg'),

-- Thuốc 14
(14, 'assets/images/uploads/14/14-1.jpg'),
(14, 'assets/images/uploads/14/14-2.jpg'),
(14, 'assets/images/uploads/14/14-3.jpg'),

-- Thuốc 15
(15, 'assets/images/uploads/15/15-1.jpg'),
(15, 'assets/images/uploads/15/15-2.jpg'),
(15, 'assets/images/uploads/15/15-3.jpg'),

-- Thuốc 16
(16, 'assets/images/uploads/16/16-1.jpg'),
(16, 'assets/images/uploads/16/16-2.jpg'),
(16, 'assets/images/uploads/16/16-3.jpg'),

-- Thuốc 17
(17, 'assets/images/uploads/17/17-1.jpg'),
(17, 'assets/images/uploads/17/17-2.jpg'),

-- Thuốc 18
(18, 'assets/images/uploads/18/18-2.jpg'),

-- Thuốc 19
(19, 'assets/images/uploads/19/19-1.jpg'),
(19, 'assets/images/uploads/19/19-2.jpg'),
(19, 'assets/images/uploads/19/19-3.jpg'),

-- Thuốc 20
(20, 'assets/images/uploads/20/20-1.jpg'),
(20, 'assets/images/uploads/20/20-2.jpg'),
(20, 'assets/images/uploads/20/20-3.jpg'),

-- Thuốc 21
(21, 'assets/images/uploads/21/21-1.jpg'),
(21, 'assets/images/uploads/21/21-2.jpg'),
(21, 'assets/images/uploads/21/21-3.jpg'),

-- Thuốc 22
(22, 'assets/images/uploads/22/22-1.jpg'),
(22, 'assets/images/uploads/22/22-2.jpg'),
(22, 'assets/images/uploads/22/22-3.jpg'),

-- Thuốc 23
(23, 'assets/images/uploads/23/23-1.jpg'),
(23, 'assets/images/uploads/23/23-2.jpg'),
(23, 'assets/images/uploads/23/23-3.jpg'),

-- Thuốc 24
(24, 'assets/images/uploads/24/24-1.jpg'),
(24, 'assets/images/uploads/24/24-2.jpg'),

-- Thuốc 25
(25, 'assets/images/uploads/25/25-1.jpg'),

-- Thuốc 26
(26, 'assets/images/uploads/26/26-1.jpg'),
(26, 'assets/images/uploads/26/26-2.jpg'),
(26, 'assets/images/uploads/26/26-3.jpg'),

-- Thuốc 27
(27, 'assets/images/uploads/27/27-1.jpg'),
(27, 'assets/images/uploads/27/27-2.jpg'),

-- Thuốc 28
(28, 'assets/images/uploads/28/28-1.jpg'),
(28, 'assets/images/uploads/28/28-2.jpg'),
(28, 'assets/images/uploads/28/28-3.jpg'),

-- Thuốc 29
(29, 'assets/images/uploads/29/29-1.jpg'),
(29, 'assets/images/uploads/29/29-2.jpg'),

-- Thuốc 30
(30, 'assets/images/uploads/30/30-1.jpg'),

-- Thuốc 31
(31, 'assets/images/uploads/31/31-1.jpg'),
(31, 'assets/images/uploads/31/31-2.jpg'),

-- Thuốc 32
(32, 'assets/images/uploads/32/32-1.jpg'),
(32, 'assets/images/uploads/32/32-2.jpg'),

-- Thuốc 33
(33, 'assets/images/uploads/33/33-1.jpg'),

-- Thuốc 34
(34, 'assets/images/uploads/34/34-1.jpg'),

-- Thuốc 35
(35, 'assets/images/uploads/35/35-1.jpg'),

-- Thuốc 36
(36, 'assets/images/uploads/36/36-1.jpg'),
(36, 'assets/images/uploads/36/36-2.jpg'),
(36, 'assets/images/uploads/36/36-3.jpg'),

-- Thuốc 37
(37, 'assets/images/uploads/37/37-1.jpg'),

-- Thuốc 38
(38, 'assets/images/uploads/38/38-1.jpg'),

-- Thuốc 39
(39, 'assets/images/uploads/39/39-1.jpg'),

-- Thuốc 40
(40, 'assets/images/uploads/40/40-1.jpg'),

-- Thuốc 41
(41, 'assets/images/uploads/41/41-1.jpg'),
(41, 'assets/images/uploads/41/41-2.jpg'),
(41, 'assets/images/uploads/41/41-3.jpg'),

-- Thuốc 42
(42, 'assets/images/uploads/42/42-1.jpg'),
(42, 'assets/images/uploads/42/42-2.jpg'),
(42, 'assets/images/uploads/42/42-3.jpg'),
(42, 'assets/images/uploads/42/42-4.jpg'),
(42, 'assets/images/uploads/42/42-5.jpg'),

-- Thuốc 43
(43, 'assets/images/uploads/43/43-1.jpg'),
(43, 'assets/images/uploads/43/43-2.jpg'),
(43, 'assets/images/uploads/43/43-3.jpg'),

-- Thuốc 44
(44, 'assets/images/uploads/44/44-1.jpg'),
(44, 'assets/images/uploads/44/44-2.jpg'),

-- Thuốc 45
(45, 'assets/images/uploads/45/45-1.jpg'),
(45, 'assets/images/uploads/45/45-2.jpg'),
(45, 'assets/images/uploads/45/45-3.jpg'),

-- Thuốc 46
(46, 'assets/images/uploads/46/46-1.jpg'),
(46, 'assets/images/uploads/46/46-2.jpg'),
(46, 'assets/images/uploads/46/46-3.jpg'),
(46, 'assets/images/uploads/46/46-4.jpg'),

-- Thuốc 47
(47, 'assets/images/uploads/47/47-1.jpg'),
(47, 'assets/images/uploads/47/47-2.jpg'),
(47, 'assets/images/uploads/47/47-3.jpg'),
(47, 'assets/images/uploads/47/47-4.jpg'),
(47, 'assets/images/uploads/47/47-5.jpg'),

-- Thuốc 48
(48, 'assets/images/uploads/48/48-1.jpg'),
(48, 'assets/images/uploads/48/48-2.jpg'),
(48, 'assets/images/uploads/48/48-3.jpg'),

-- Thuốc 49
(49, 'assets/images/uploads/49/49-1.jpg'),
(49, 'assets/images/uploads/49/49-2.jpg'),

-- Thuốc 50
(50, 'assets/images/uploads/50/50-1.jpg'),
(50, 'assets/images/uploads/50/50-2.jpg');






