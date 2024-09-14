<?php

class ActivityModel {
    private $apiUrl;

    public function __construct($apiUrl) {
        $this->apiUrl = $apiUrl;
    }

    private static function formatDate($date) {

        $dateTime = DateTime::createFromFormat('Y-m-d', $date);
        return $dateTime ? $dateTime->format('d-m-Y') : $date;
    }

    private static function isApiAvailable($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        if ($result !== false && $httpCode == 200) {
            return true;
        } else {
            return false;
        }
    }


    public static function getActivitiesByMonth($month, $apiUrl) {

        $url = $apiUrl . '/month/' . $month;
        if (!self::isApiAvailable($url)) {
            return null;
        }
        $response = file_get_contents($url);
        $activities = json_decode($response, true);
    
        if (is_array($activities)) {
            $currentDate = date('d-m-Y');
            $currentDateObj = DateTime::createFromFormat('d-m-Y', $currentDate);
            foreach ($activities as &$activity) {
                
                if (isset($activity['hanCuoiDangKy'])) {
                    $activity['hanCuoiDangKy'] = self::formatDate($activity['hanCuoiDangKy']);
                }
                if (isset($activity['ngayBatDau'])) {
                    $activity['ngayBatDau'] = self::formatDate($activity['ngayBatDau']);
                }
                if (isset($activity['ngayKetThuc'])) {
                    $activity['ngayKetThuc'] = self::formatDate($activity['ngayKetThuc']);
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

    public static function getActivitiesmonth($month, $apiUrl) {

    $url = $apiUrl . '/month/' . $month;
    if (!self::isApiAvailable($url)) {
        return null;
    }
    $response = file_get_contents($url);
    $activities = json_decode($response, true);

    if (is_array($activities)) {
        $currentDate = date('d-m-Y');
        $currentDateObj = DateTime::createFromFormat('d-m-Y', $currentDate);
        foreach ($activities as &$activity) {
            
            if (isset($activity['hanCuoiDangKy'])) {
                $activity['hanCuoiDangKy'] = self::formatDate($activity['hanCuoiDangKy']);
            }
            if (isset($activity['ngayBatDau'])) {
                $activity['ngayBatDau'] = self::formatDate($activity['ngayBatDau']);
            }
            if (isset($activity['ngayKetThuc'])) {
                $activity['ngayKetThuc'] = self::formatDate($activity['ngayKetThuc']);
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
    
    

    public static function SearchActivitiesCoBan($ten, $apiUrl) {

        $url = $apiUrl . '/searchCoBan?Ten=' . urlencode($ten);
        if (!self::isApiAvailable($url)) {
            return null;
        }
        $response = file_get_contents($url);
        $activities = json_decode($response, true);
        if (is_array($activities)) {
            $currentDate = date('d-m-Y');
            $currentDateObj = DateTime::createFromFormat('d-m-Y', $currentDate);
            foreach ($activities as &$activity) {
                
                if (isset($activity['hanCuoiDangKy'])) {
                    $activity['hanCuoiDangKy'] = self::formatDate($activity['hanCuoiDangKy']);
                }
                if (isset($activity['ngayBatDau'])) {
                    $activity['ngayBatDau'] = self::formatDate($activity['ngayBatDau']);
                }
                if (isset($activity['ngayKetThuc'])) {
                    $activity['ngayKetThuc'] = self::formatDate($activity['ngayKetThuc']);
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

    public static function SearchActivitiesLienKet($ten, $apiUrl) {

        $url = $apiUrl . '/searchLienKet?Ten=' . urlencode($ten);
        if (!self::isApiAvailable($url)) {
            return null;
        }
        $response = file_get_contents($url);
        $activities = json_decode($response, true);

        if (is_array($activities)) {
            $currentDate = date('d-m-Y');
            $currentDateObj = DateTime::createFromFormat('d-m-Y', $currentDate);
            foreach ($activities as &$activity) {
                
                if (isset($activity['hanCuoiDangKy'])) {
                    $activity['hanCuoiDangKy'] = self::formatDate($activity['hanCuoiDangKy']);
                }
                if (isset($activity['ngayBatDau'])) {
                    $activity['ngayBatDau'] = self::formatDate($activity['ngayBatDau']);
                }
                if (isset($activity['ngayKetThuc'])) {
                    $activity['ngayKetThuc'] = self::formatDate($activity['ngayKetThuc']);
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

    public static function CountActivityByMonth($month, $apiUrl) {

        $url = $apiUrl . '/countByMonth/' . $month;
        if (!self::isApiAvailable($url)) {
            return null;
        }
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

    public static function CountActivity($apiUrl) {

        $url = $apiUrl . '/countall';
        if (!self::isApiAvailable($url)) {
            return null;
        }
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
    
    public static function GetActivityDetail($ActivityID, $apiUrl) {
            $url = $apiUrl . '/activityAndProfile/' . $ActivityID;
    
        if (!self::isApiAvailable($url)) {
            return null;
        }
    
        $response = file_get_contents($url);
        $data = json_decode($response, true);  // Giải mã JSON
    
        if (isset($data['activity'])) {
            $activity = $data['activity'];
    
            if (isset($activity['hanCuoiDangKy'])) {
                $activity['hanCuoiDangKy'] = self::formatDate($activity['hanCuoiDangKy']);
            }
            if (isset($activity['ngayBatDau'])) {
                $activity['ngayBatDau'] = self::formatDate($activity['ngayBatDau']);
            }
            if (isset($activity['ngayKetThuc'])) {
                $activity['ngayKetThuc'] = self::formatDate($activity['ngayKetThuc']);
            }
    
            $hanCuoiDangKyDate = DateTime::createFromFormat('d-m-Y', $activity['hanCuoiDangKy']);
            $ngayBatDauDate = DateTime::createFromFormat('d-m-Y', $activity['ngayBatDau']);
            $ngayKetThucDate = DateTime::createFromFormat('d-m-Y', $activity['ngayKetThuc']);
            $currentDateObj = new DateTime();
    
            if ($currentDateObj <= $hanCuoiDangKyDate) {
                $activity['TinhTrang'] = 'Chờ Đăng ký';
            } elseif ($currentDateObj > $hanCuoiDangKyDate && $currentDateObj < $ngayBatDauDate) {
                $activity['TinhTrang'] = 'Sắp diễn ra';
            } elseif ($currentDateObj >= $ngayBatDauDate && $currentDateObj <= $ngayKetThucDate) {
                $activity['TinhTrang'] = 'Đang diễn ra';
            } else {
                $activity['TinhTrang'] = 'Kết Thúc';
            }
    
            if (isset($data['profiles'])) {
                $activity['profiles'] = $data['profiles'];

                $phongBanCounts = [];
            foreach ($data['profiles'] as $profile) {
                $phongban = $profile['tenphong'];
                if (!isset($phongBanCounts[$phongban])) {
                    $phongBanCounts[$phongban] = 0;
                }
                $phongBanCounts[$phongban]++;
            }

                $activity['phongBanCounts'] = $phongBanCounts;
            }
    
            return $activity;
        } else {
            return null;
        }
    }
    

    
    public static function GetActivityJoin($empID, $apiUrl) {
        $url = $apiUrl . '/join/SearchByempID/' . $empID;
        if (!self::isApiAvailable($url)) {
            return null;
        }
    

        $response = file_get_contents($url);
        $activities = json_decode($response, true);
        if (is_array($activities)) {
            $currentDate = date('d-m-Y');
            $currentDateObj = DateTime::createFromFormat('d-m-Y', $currentDate);
            foreach ($activities as &$activity) {
                
                if (isset($activity['hanCuoiDangKy'])) {
                    $activity['hanCuoiDangKy'] = self::formatDate($activity['hanCuoiDangKy']);
                }
                if (isset($activity['ngayBatDau'])) {
                    $activity['ngayBatDau'] = self::formatDate($activity['ngayBatDau']);
                }
                if (isset($activity['ngayKetThuc'])) {
                    $activity['ngayKetThuc'] = self::formatDate($activity['ngayKetThuc']);
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

    public static function CheckJoin($activityID, $empID,$apiUrl) {

        $url = $apiUrl . '/join/countt?activityID='.$activityID.'&empID='.$empID;
        if (!self::isApiAvailable($url)) {
            return null;
        }
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
    
    public static function CreateJoinActivity($activityID, $empID, $apiUrl) {
        $url = $apiUrl . '/join/create';
        
        $data = array(
            'activityID' => $activityID,
            'employeeID' => $empID,
        );
        
        $postData = http_build_query($data);
        
        $options = array(
            'http' => array(
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n" .
                             "Content-Length: " . strlen($postData) . "\r\n",
                'method'  => 'POST',
                'content' => $postData,
            ),
        );
        
        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        
        if ($response === FALSE) {
            error_log('API call failed.');
            return 2;
        }
        
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON decode error: ' . json_last_error_msg());
            return 2;
        }
        
        if (isset($result['activityID']) && isset($result['employeeID'])) {
            return 1; 
        }
        
        return 2;
    }

    public static function CreateActivity($tenHoatDong, $point, $noiDung, $chiTiet, $soNguoiThamGia, $chiPhi, $hanCuoiDangKy, $ngayBatDau, $ngayKetThuc, $loaiHoatDong, $loai, $apiUrl) {
        $url = $apiUrl . '/activity/create';
        $data = array(
            'tenHoatDong' => $tenHoatDong,
            'loaiHoatDong' => $loaiHoatDong,
            'point' => $point,
            'noiDung' => $noiDung,
            'chiTiet' => $chiTiet,
            'soNguoiThamGia' => $soNguoiThamGia,
            'chiPhi' => $chiPhi,
            'hanCuoiDangKy' => $hanCuoiDangKy,
            'ngayBatDau' => $ngayBatDau,
            'ngayKetThuc' => $ngayKetThuc,
            'loai' => $loai,
        );
        
        $postData = json_encode($data); // Encode data as JSON
        
        $options = array(
            'http' => array(
                'header'  => "Content-Type: application/json\r\n" .
                             "Content-Length: " . strlen($postData) . "\r\n",
                'method'  => 'POST',
                'content' => $postData,
            ),
        );
        
       
        
        $context = stream_context_create($options);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === FALSE) {
            error_log('API call failed.');
            return 0; 
        }
        
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON decode error: ' . json_last_error_msg());
            return 0; 
        }
        
        if (isset($result['activityID'])) {
            return $result['activityID'];
        }
        
        return 0;
    }
    
    

    
    
    
    
    
    
    
    
}
?>
