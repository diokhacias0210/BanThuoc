<?php
class DuyetDonController extends Controller
{
    private $duyetDonModel;

    public function __construct()
    {
        $this->duyetDonModel = $this->model('DuyetDonModel');
    }

    private function ensureAllowedRole()
    {
        $role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
        if ($role !== 'DUOC_SI' && $role !== 'QUAN_TRI_VIEN') {
            session_unset();
            session_destroy();
            header('Location: ' . URLROOT . '/khachHang/xacThuc/dangNhap');
            exit;
        }
    }

    public function index()
    {
        $this->ensureAllowedRole();

        $data['title'] = 'Duyệt đơn thuốc';
        $data['page_title'] = 'Duyệt thuốc kê đơn';
        $data['active_tab'] = 'donthuoc';
        $data['page_css'] = 'duyetDon';

        ob_start();
        require_once APPROOT . '/views/duocSi/duyetDon.php';
        $data['content'] = ob_get_clean();

        $this->view('layouts/duocSiLayout', $data);
    }

    public function getList()
    {
        $this->ensureAllowedRole();
        header('Content-Type: application/json');

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $status = isset($_GET['status']) ? trim($_GET['status']) : 'all';
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $pageSize = 8;

        $items = $this->duyetDonModel->getList($search, $status, $page, $pageSize);
        $total = $this->duyetDonModel->countList($search, $status);

        echo json_encode([
            'status' => true,
            'data' => $items,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize,
            'pendingCount' => $this->duyetDonModel->getPendingCount()
        ]);
        exit;
    }

    public function detail($id)
    {
        $this->ensureAllowedRole();
        header('Content-Type: application/json');
        $item = $this->duyetDonModel->getById($id);

        if ($item) {
            echo json_encode(['status' => true, 'data' => $item]);
        } else {
            echo json_encode(['status' => false, 'message' => 'Không tìm thấy đơn thuốc.']);
        }
        exit;
    }

    public function approve($id)
    {
        $this->ensureAllowedRole();
        header('Content-Type: application/json');

        $idDuocSi = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
        $ok = $this->duyetDonModel->updateStatus($id, 'DA_DUYET', $idDuocSi);

        if ($ok) {
            echo json_encode(['status' => true, 'message' => 'Đã duyệt đơn thuốc thành công.']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Không thể duyệt đơn thuốc.']);
        }
        exit;
    }

    public function approveAll()
    {
        $this->ensureAllowedRole();
        header('Content-Type: application/json');
        $idDuocSi = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

        $items = $this->duyetDonModel->getList('', 'CHO_DUYET', 1, 1000);
        $updated = 0;

        foreach ($items as $item) {
            if ($this->duyetDonModel->updateStatus($item['idDonThuoc'], 'DA_DUYET', $idDuocSi)) {
                $updated++;
            }
        }

        echo json_encode(['status' => true, 'message' => "Đã duyệt {$updated} đơn thuốc.", 'updated' => $updated]);
        exit;
    }

    public function reject($id)
    {
        $this->ensureAllowedRole();
        header('Content-Type: application/json');

        $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
        if (empty($reason)) {
            echo json_encode(['status' => false, 'message' => 'Vui lòng nhập lý do từ chối.']);
            exit;
        }

        $idDuocSi = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
        $ok = $this->duyetDonModel->updateStatus($id, 'TU_CHOI', $idDuocSi, $reason);

        if ($ok) {
            echo json_encode(['status' => true, 'message' => 'Đã từ chối đơn thuốc.']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Không thể từ chối đơn thuốc.']);
        }
        exit;
    }
}
