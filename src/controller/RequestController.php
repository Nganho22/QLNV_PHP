<?php
require_once __DIR__ . '/../model/RequestModel.php';

class RequestController {
    public function GetRequestPage() {
        if (isset($_SESSION['user'])) {
            $title = 'Yêu cầu';
            $user_id = $_SESSION['user']['EmpID'];
            $role = $_SESSION['user']['Role'];
    
            $limit = 3;
            $pagePending = isset($_GET['pagePending']) ? (int)$_GET['pagePending'] : 1;
            $pageApproved = isset($_GET['pageApproved']) ? (int)$_GET['pageApproved'] : 1;
            $offsetPending = ($pagePending - 1) * $limit;
            $offsetApproved = ($pageApproved - 1) * $limit;
            $timeSheets = RequestModel::getTimeSheetsByEmpID($user_id);
    
            $qllimit = 7; 
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $qllimit;

            switch ($role) {
                case 'Nhân viên':
                    $file = "./views/pages/NV/request.phtml";
                    $creq = RequestModel::getRequestCountsByEmpID($user_id);
                    $pendingRequests = RequestModel::getPendingRequestsByEmpID($user_id, $limit, $offsetPending);
                    $approvedRequests = RequestModel::getApprovedRequestsByEmpID($user_id, $limit, $offsetApproved);
                    $totalPending = RequestModel::countPendingRequests($user_id);
                    $totalApproved = RequestModel::countApprovedRequests($user_id);
    
                    if (isset($_GET['ajax'])) {
                        // Trả về dữ liệu dưới dạng JSON cho AJAX
                        $pendingHtml = '';
                        foreach ($pendingRequests as $request) {
                            $pendingHtml .= '<tr>'
                            . '<td><a href = "index.php?action=GetDetailRequestPage&id=' . htmlspecialchars($request['RequestID']) . '">' 
                                . htmlspecialchars($request['TieuDe']) . '</a></td>'
                            // . '<td>' . ($request['TrangThai'] == 0 ? 'Chưa duyệt' : 'Đã duyệt') . '</td>'
                            . '<td>' . htmlspecialchars($request['Loai']) . '</td>'
                            . '<td>' . htmlspecialchars($request['NgayGui']) . '</td>'
                            . '</tr>';
                        
                        }
    
                        $approvedHtml = '';
                        foreach ($approvedRequests as $request) {
                            $approvedHtml .= '<tr>'
                                . '<td><a href = "index.php?action=GetDetailRequestPage&id=' . htmlspecialchars($request['RequestID']) . '">' 
                                . htmlspecialchars($request['TieuDe']) . '</a></td>'
                                // . '<td>' . ($request['TrangThai'] == 0 ? 'Chưa duyệt' : 'Đã duyệt') . '</td>'
                                . '<td>' . htmlspecialchars($request['Loai']) . '</td>'
                                . '<td>' . htmlspecialchars($request['NgayXuLy']) . '</td>'
                                . '</tr>';
                        }
    
                        $pendingPagination = '';
                        if ($totalPending > $limit) {
                            for ($i = 1; $i <= ceil($totalPending / $limit); $i++) {
                                $pendingPagination .= '<li class="page-item"><a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
                            }
                        }
    
                        $approvedPagination = '';
                        if ($totalApproved > $limit) {
                            for ($i = 1; $i <= ceil($totalApproved / $limit); $i++) {
                                $approvedPagination .= '<li class="page-item"><a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
                            }
                        }
    
                        echo json_encode([
                            'pendingHtml' => $pendingHtml,
                            'pendingPagination' => $pendingPagination,
                            'approvedHtml' => $approvedHtml,
                            'approvedPagination' => $approvedPagination,
                            'timeSheets' => $timeSheets
                        ]);
                    } else {
                        ob_start();
                        require($file);
                        $content = ob_get_clean();
                        require(__DIR__ . '/../views/template.phtml');
                    }
                    break;
                case 'Quản lý':
                    $creq = RequestModel::getRequestCountsByEmpID_QL($user_id);
                    $requests = RequestModel::getRequestsByEmpID_QL($user_id);

                    $totalRequests = count($requests);
                    $requestsPage = array_slice($requests, $offset, $qllimit);

                    $file = "./views/pages/QL/request.phtml";

                    if (isset($_GET['ajax'])) {
                        $requestHtml = '';
                        foreach ($requestsPage as $request) {
                            $requestHtml .= '<tr>'
                                . '<td><a href = "index.php?action=GetDetailRequestPage&id=' . htmlspecialchars($request['RequestID']) . '">' 
                                . htmlspecialchars($request['TieuDe']) . '</a></td>'
                                . '<td>' . htmlspecialchars($request['Loai']) . '</td>'
                                . '<td>' . htmlspecialchars($request['NgayGui']) . '</td>'
                                . '<td>' . ($request['TrangThai'] == 0 ? 'Chưa duyệt' : 'Đã duyệt') . '</td>'
                                . '</tr>';
                        }
                        $paginationHtml = '';
                        if ($totalRequests > $qllimit) {
                            for ($i = 1; $i <= ceil($totalRequests / $qllimit); $i++) {
                                $paginationHtml .= '<li class="page-item"><a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
                            }
                        }
    
                        echo json_encode([
                            'requestHtml' => $requestHtml,
                            'paginationHtml' => $paginationHtml
                        ]);
                    } else {
                        ob_start();
                        require($file);
                        $content = ob_get_clean();
                        require(__DIR__ . '/../views/template.phtml');
                    }
                    break;
                default:
                    $file = null;
                    $title = 'Error';
                    break;
            }

        } else {
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
    }

    public function GetTimeSheetDetails() {
        if (isset($_GET['timeSheetID'])) {
            $timeSheetID = $_GET['timeSheetID'];
            $timeSheet = RequestModel::getTimeSheetByID($timeSheetID);
            echo json_encode($timeSheet);
        }
    }
    
    public function submitRequest() {
        if (isset($_SESSION['user']) && $_SERVER['REQUEST_METHOD'] === 'POST'){
            $user_id = $_SESSION['user']['EmpID'];
            $nguoiGui = $_SESSION['user']['HoTen'];
            $loai = $_POST['loai'];
            $tieuDe = $_POST['tieuDe'];
            $ngayGui = $_POST['ngayGui'];
            $noiDung = $_POST['noiDung'];

            if (empty($tieuDe)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Vui lòng cập nhật tiêu đề!'
                ]);
                exit();
            }

            $result = RequestModel::createRequest($user_id, $nguoiGui, $loai, $tieuDe, $ngayGui, $noiDung);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Gửi đơn yêu cầu thành công!'
                ]);
                exit();

            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Tạo đơn yêu cầu thất bại !'
                ]);
                exit();
            }
        }
        else {
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
    }

    public function submitTimeSheetRequest() {
        if (isset($_SESSION['user']) && $_SERVER['REQUEST_METHOD'] === 'POST'){
            // Lấy dữ liệu từ form submission
            $userId = $_SESSION['user']['EmpID'];
            $nguoiGui = $_POST['nguoiGui'];
            $loai = $_POST['loai'];
            $tieuDe = $_POST['tieuDe'];
            $ngayGui = $_POST['ngayGui'];
            $noiDung = $_POST['noiDung'];
            $timeSheetID = $_POST['controllerTimeSheetID'];
            $trangThai = $_POST['trangThai'];
            $soGio = $_POST['soGio'];
            $customHours = isset($_POST['customHours']) ? $_POST['customHours'] : null;

            if (empty($tieuDe)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Vui lòng cập nhật tiêu đề!'
                ]);
                exit();
            }

            if (empty($trangThai)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Vui lòng cập nhật trạng thái!'
                ]);
                exit();
            }

            // Xử lý số giờ
            if ($soGio === 'custom') {
                $soGio = $customHours;
            }

            if (empty($soGio)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Vui lòng chọn thời gian đã thực hiện!'
                ]);
                exit();
            }

            // Lấy thông tin thời gian từ model
            $timeSheetDetails = RequestModel::getTimeSheetByID($timeSheetID);
            
            if ($timeSheetDetails) {
                // Tính toán thời gian mới
                // $upTinhTrangTimesheet = $timeSheetDetails['TrangThai'];
                $upThoiGianTimesheet = $timeSheetDetails['SoGioThucHien'];
                $newUpThoiGianTimesheet = $upThoiGianTimesheet + $soGio;
                
                // Tạo request mới
                RequestModel::createTimeSheetRequest($userId, $nguoiGui, $loai, $tieuDe, $ngayGui, $noiDung, $timeSheetID, $trangThai, $newUpThoiGianTimesheet);
                
                echo json_encode(['success' => true, 'message' => 'Gửi đơn yêu cầu thành công!']);
                exit();
            } else {
                echo json_encode(['success' => false, 'message' => 'Vui lòng chọn Time-sheet để cập nhật!']);
                exit();
            }

        }
        else {
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
    }

    public function GetDetailPage($requestId) {
        if (isset($_SESSION['user'])) {
            $title='Chi tiết Request';
            $user_id = $_SESSION['user']['EmpID'];

            $detail = RequestModel::getDetailRequest($requestId);
            $detail_ts = RequestModel::getTimeSheetByID($detail['Time_sheetID']);
            
            ob_start();
            require("./views/pages/NV/detail_req.phtml");
            $content = ob_get_clean();
            require(__DIR__ . '/../views/template.phtml');
        }
        else{
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
     
    }

}
?>
