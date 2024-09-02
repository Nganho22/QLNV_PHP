<?php

class ActivityModel {
    private $apiUrl;

    public function __construct($apiUrl) {
        $this->apiUrl = $apiUrl;
    }

    private function formatDate($date) {
        // Chuyển đổi ngày thành định dạng 'd-m-Y'
        $dateTime = DateTime::createFromFormat('Y-m-d', $date);
        return $dateTime ? $dateTime->format('d-m-Y') : $date;
    }

    public function isApiAvailable($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Timeout sau 5 giây
    
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        if ($result !== false && $httpCode == 200) {
            return true;
        } else {
            return false;
        }
    }


    public function getActivitiesByMonth($month) {

        $url = $this->apiUrl . '/month/' . $month;
    
        $response = file_get_contents($url);
        $activities = json_decode($response, true);
    
        if (is_array($activities)) {
            $currentDate = date('d-m-Y');
            $currentDateObj = DateTime::createFromFormat('d-m-Y', $currentDate);
            foreach ($activities as &$activity) {
                
                if (isset($activity['hanCuoiDangKy'])) {
                    $activity['hanCuoiDangKy'] = $this->formatDate($activity['hanCuoiDangKy']);
                }
                if (isset($activity['ngayBatDau'])) {
                    $activity['ngayBatDau'] = $this->formatDate($activity['ngayBatDau']);
                }
                if (isset($activity['ngayKetThuc'])) {
                    $activity['ngayKetThuc'] = $this->formatDate($activity['ngayKetThuc']);
                }
    
                // Chuyển đổi các ngày thành đối tượng DateTime
                $hanCuoiDangKyDate = DateTime::createFromFormat('d-m-Y', $activity['hanCuoiDangKy']);
                $ngayBatDauDate = DateTime::createFromFormat('d-m-Y', $activity['ngayBatDau']);
                $ngayKetThucDate = DateTime::createFromFormat('d-m-Y', $activity['ngayKetThuc']);
                $activity['TinhTrang'] = 'Chưa xác định';
                if ($currentDateObj <= $hanCuoiDangKyDate) {
                    $activity['TinhTrang'] = 'Chờ Đăng ký';
                }
                if ($currentDateObj > $hanCuoiDangKyDate && $currentDateObj < $ngayBatDauDate) {
                    $activity['TinhTrang'] = 'Sắp diễn ra';
                } 
                if ($currentDateObj >= $ngayBatDauDate && $currentDateObj <= $ngayKetThucDate) {
                    $activity['TinhTrang'] = 'Đang diễn ra';
                }
                if ($currentDateObj > $ngayKetThucDate) {
                    $activity['TinhTrang'] = 'Kết Thúc';
                }
            }
        } else {
            return null;
        }
    
        return $activities;
    }
    
    

    public function SearchActivitiesCoBan($ten) {

        $url = $this->apiUrl . '/searchCoBan?Ten=' . $ten;

        $response = file_get_contents($url);
        $activities = json_decode($response, true);

        if (is_array($activities)) {
            $currentDate = date('d-m-Y');
            $currentDateObj = DateTime::createFromFormat('d-m-Y', $currentDate);
            foreach ($activities as &$activity) {
                
                if (isset($activity['hanCuoiDangKy'])) {
                    $activity['hanCuoiDangKy'] = $this->formatDate($activity['hanCuoiDangKy']);
                }
                if (isset($activity['ngayBatDau'])) {
                    $activity['ngayBatDau'] = $this->formatDate($activity['ngayBatDau']);
                }
                if (isset($activity['ngayKetThuc'])) {
                    $activity['ngayKetThuc'] = $this->formatDate($activity['ngayKetThuc']);
                }
    
                // Chuyển đổi các ngày thành đối tượng DateTime
                $hanCuoiDangKyDate = DateTime::createFromFormat('d-m-Y', $activity['hanCuoiDangKy']);
                $ngayBatDauDate = DateTime::createFromFormat('d-m-Y', $activity['ngayBatDau']);
                $ngayKetThucDate = DateTime::createFromFormat('d-m-Y', $activity['ngayKetThuc']);
                $activity['TinhTrang'] = 'Chưa xác định';
                if ($currentDateObj <= $hanCuoiDangKyDate) {
                    $activity['TinhTrang'] = 'Chờ Đăng ký';
                }
                if ($currentDateObj > $hanCuoiDangKyDate && $currentDateObj < $ngayBatDauDate) {
                    $activity['TinhTrang'] = 'Sắp diễn ra';
                } 
                if ($currentDateObj >= $ngayBatDauDate && $currentDateObj <= $ngayKetThucDate) {
                    $activity['TinhTrang'] = 'Đang diễn ra';
                }
                if ($currentDateObj > $ngayKetThucDate) {
                    $activity['TinhTrang'] = 'Kết Thúc';
                }
            }
        } else {
            return null;
        }

        return $activities;
    }

    public function SearchActivitiesLienKet($ten) {

        $url = $this->apiUrl . '/searchLienKet?Ten=' . $ten;

        $response = file_get_contents($url);
        $activities = json_decode($response, true);

        if (is_array($activities)) {
            $currentDate = date('d-m-Y');
            $currentDateObj = DateTime::createFromFormat('d-m-Y', $currentDate);
            foreach ($activities as &$activity) {
                
                if (isset($activity['hanCuoiDangKy'])) {
                    $activity['hanCuoiDangKy'] = $this->formatDate($activity['hanCuoiDangKy']);
                }
                if (isset($activity['ngayBatDau'])) {
                    $activity['ngayBatDau'] = $this->formatDate($activity['ngayBatDau']);
                }
                if (isset($activity['ngayKetThuc'])) {
                    $activity['ngayKetThuc'] = $this->formatDate($activity['ngayKetThuc']);
                }
    
                // Chuyển đổi các ngày thành đối tượng DateTime
                $hanCuoiDangKyDate = DateTime::createFromFormat('d-m-Y', $activity['hanCuoiDangKy']);
                $ngayBatDauDate = DateTime::createFromFormat('d-m-Y', $activity['ngayBatDau']);
                $ngayKetThucDate = DateTime::createFromFormat('d-m-Y', $activity['ngayKetThuc']);
                $activity['TinhTrang'] = 'Chưa xác định';
                if ($currentDateObj <= $hanCuoiDangKyDate) {
                    $activity['TinhTrang'] = 'Chờ Đăng ký';
                }
                elseif ($currentDateObj > $hanCuoiDangKyDate && $currentDateObj < $ngayBatDauDate) {
                    $activity['TinhTrang'] = 'Sắp diễn ra';
                } 
                elseif ($currentDateObj >= $ngayBatDauDate && $currentDateObj <= $ngayKetThucDate) {
                    $activity['TinhTrang'] = 'Đang diễn ra';
                }
                elseif ($currentDateObj > $ngayKetThucDate) {
                    $activity['TinhTrang'] = 'Kết Thúc';
                }
            }
        } else {
            return null;
        }

        return $activities;
    }

    public function CountActivityByMonth($month) {

        $url = $this->apiUrl . '/countByMonth/' . $month;

        $response = @file_get_contents($url);
        if ($response === FALSE) {
            return 0;
        }
    
       
        $countactivities = json_decode($response, true);
    
       
        if (json_last_error() !== JSON_ERROR_NONE) {
            return 0;
        }
    
        
        if (isset($countactivities) && is_numeric($countactivities)) {
            return (int)$countactivities;
        }
        return 0;
    }

    public function CountActivity() {

        $url = $this->apiUrl . '/countall';

        $response = @file_get_contents($url);
        if ($response === FALSE) {
            return 0;
        }
    
       
        $countactivities = json_decode($response, true);
    
       
        if (json_last_error() !== JSON_ERROR_NONE) {
            return 0;
        }
    
        
        if (isset($countactivities) && is_numeric($countactivities)) {
            return (int)$countactivities;
        }
        return 0;
    }
    
}
?>
