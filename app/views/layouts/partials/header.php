<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';

$cartCount = 0;
if ($isLoggedIn) {
    $idKhachHang = $_SESSION['user_id'];
    if (class_exists('Database')) {
        $db = new Database();
        $sqlCart = "SELECT COALESCE(SUM(ct.soLuong), 0) AS total 
                    FROM ChiTietGioHang ct 
                    INNER JOIN GioHang g ON ct.idGioHang = g.idGioHang 
                    WHERE g.idKhachHang = :idKhachHang";
        $db->query($sqlCart);
        $db->bind(':idKhachHang', $idKhachHang);
        $row = $db->single();
        if ($row) {
            $cartCount = intval($row['total']);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'PharmaCare'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="<?php echo ASSETROOT; ?>/css/Layout/khachHangLayout.css">
    <?php if (!empty($page_css)): ?>
        <link rel="stylesheet" href="<?php echo ASSETROOT; ?>/css/khachHang/<?php echo $page_css; ?>.css">
    <?php endif; ?>

    <style>
        .topbar-search {
            position: relative;
        }

        .search-dropdown {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            background: #ffffff;
            border: 1px solid #bcded0;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(12, 32, 22, 0.15);
            z-index: 999;
            display: none;
            overflow: hidden;
            max-height: 380px;
            overflow-y: auto;
        }

        .search-dropdown.show {
            display: block;
        }

        .search-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            text-decoration: none;
            color: inherit;
            border-bottom: 1px solid #f1f2f4;
            transition: background 0.15s;
        }

        .search-item:last-child {
            border-bottom: none;
        }

        .search-item:hover {
            background: #f2faf5;
        }

        .search-item img {
            width: 44px;
            height: 44px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid var(--border);
            background: #fcfdfc;
            flex-shrink: 0;
        }

        .search-item-info {
            flex: 1;
            min-width: 0;
        }

        .search-item-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .search-item-price {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--green);
            margin-top: 2px;
        }

        .search-no-result {
            padding: 14px;
            text-align: center;
            color: var(--muted2);
            font-size: 13px;
        }
    </style>
</head>

<body>
    <div class="topbar">
        <div class="topbar-inner">
            <div class="menu-toggle" id="menuToggle">
                <i class="fa-solid fa-bars"></i> <?php echo isset($page_title) ? $page_title : 'PharmaCare'; ?>
            </div>

            <div class="topbar-search">
                <div class="ts-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="globalSearchInput" placeholder="Tìm kiếm sản phẩm, tên thuốc..." autocomplete="off">
                </div>
                <div class="search-dropdown" id="searchDropdown"></div>
            </div>

            <div class="topbar-right">
                <a class="tb-icon" href="<?php echo URLROOT; ?>/khachHang/gioHang" title="Giỏ hàng">
                    <span class="tb-icon-wrap">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span class="tb-badge" id="cartCountBadge"><?php echo $cartCount; ?></span>
                    </span>
                </a>

                <?php if ($isLoggedIn): ?>
                    <a class="tb-icon" href="<?php echo URLROOT; ?>/khachHang/thongTinCaNhan" title="Tài khoản: <?php echo htmlspecialchars($userName); ?>">
                        <span class="tb-icon-wrap" style="color: var(--green);"><i class="fa-solid fa-circle-user"></i></span>
                    </a>
                <?php else: ?>
                    <a class="tb-icon" href="<?php echo URLROOT; ?>/khachHang/xacThuc/dangNhap" title="Đăng nhập / Đăng ký">
                        <span class="tb-icon-wrap"><i class="fa-solid fa-right-to-bracket"></i></span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('globalSearchInput');
        const searchDropdown = document.getElementById('searchDropdown');
        let searchTimer = null;

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimer);
                const query = this.value.trim();

                if (query.length < 1) {
                    searchDropdown.classList.remove('show');
                    searchDropdown.innerHTML = '';
                    return;
                }

                searchTimer = setTimeout(() => {
                    fetch(`<?php echo URLROOT; ?>/khachHang/thuoc/timKiemAjax?q=${encodeURIComponent(query)}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.length > 0) {
                                let html = '';
                                data.forEach(item => {
                                    html += `
                                    <a class="search-item" href="<?php echo URLROOT; ?>/khachHang/thuoc/chiTiet/${item.idThuoc}">
                                        <img src="${item.hinhAnh}" alt="${item.tenThuoc}">
                                        <div class="search-item-info">
                                            <div class="search-item-title">${item.tenThuoc}</div>
                                            <div class="search-item-price">${item.giaBanFormatted || item.giaBan}</div>
                                        </div>
                                    </a>
                                `;
                                });
                                searchDropdown.innerHTML = html;
                            } else {
                                searchDropdown.innerHTML = `<div class="search-no-result"><i class="fa-solid fa-magnifying-glass"></i> Không tìm thấy sản phẩm nào</div>`;
                            }
                            searchDropdown.classList.add('show');
                        })
                        .catch(() => searchDropdown.classList.remove('show'));
                }, 250);
            });

            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
                    searchDropdown.classList.remove('show');
                }
            });
        }
    </script>