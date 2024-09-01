<?php

class ActivityModel {
    private $apiUrl;

    public function __construct($apiUrl) {
        $this->apiUrl = $apiUrl;
    }

    private function formatDate($date) {
        $dateTime = DateTime::createFromFormat('Y-m-d', $date);
        return $dateTime ? $dateTime->format('d-m-Y') : $date;
    }

    private function isApiAvailable($url) {
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

        if (!$this->isApiAvailable($url)) {
            return null;
        }

        $response = file_get_contents($url);
        $activities = json_decode($response, true);

        if (is_array($activities)) { 
            $currentDate = date('d-m-Y');
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
                if ($currentDate < $activity['hanCuoiDangKy']) {
                    $activity['TinhTrang'] = 'Chờ Đăng ký';
                } elseif ($currentDate >= $activity['ngayBatDau'] && $currentDate <= $activity['ngayKetThuc']) {
                    $activity['TinhTrang'] = 'Đang diễn ra';
                } else {
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

        if (!$this->isApiAvailable($url)) {
            return null;
        }

        $response = file_get_contents($url);
        $activities = json_decode($response, true);

        if (is_array($activities)) {
            $currentDate = date('d-m-Y');
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
                if ($currentDate < $activity['hanCuoiDangKy']) {
                    $activity['TinhTrang'] = 'Chờ Đăng ký';
                } elseif ($currentDate >= $activity['ngayBatDau'] && $currentDate <= $activity['ngayKetThuc']) {
                    $activity['TinhTrang'] = 'Đang diễn ra';
                } else {
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

        if (!$this->isApiAvailable($url)) {
            return null;
        }

        $response = file_get_contents($url);
        $activities = json_decode($response, true);

        if (is_array($activities)) {
            $currentDate = date('d-m-Y');
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
                if ($currentDate < $activity['hanCuoiDangKy']) {
                    $activity['TinhTrang'] = 'Chờ Đăng ký';
                } elseif ($currentDate >= $activity['ngayBatDau'] && $currentDate <= $activity['ngayKetThuc']) {
                    $activity['TinhTrang'] = 'Đang diễn ra';
                } else {
                    $activity['TinhTrang'] = 'Kết Thúc';
                }
            }
        } else {
            return null;
        }

        return $activities;
    }
    
}
?>
