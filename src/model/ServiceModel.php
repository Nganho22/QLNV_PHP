<?php

class ServiceModel {
    private $apiUrl;

    public function __construct($apiUrl) {
        $this->apiUrl = $apiUrl;
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

    
    public static function getAPI() {

        $apiUrl='http://localhost:9001/apiService/';
        

        if (!self::isApiAvailable($apiUrl)) {
            return null;
        }
        $response = file_get_contents($apiUrl);
        $data = json_decode($response, true);
        $Services = array();
    
        if ($data && is_array($data)) {
            foreach ($data as $entry) {
                $ServiceName = isset($entry['serviceName']) ? (string)$entry['serviceName'] : null;
                $url = isset($entry['url']) ? (string)$entry['url'] : null;

                if($ServiceName && $url)
                {
                    $Services[$ServiceName] = $url;
                }
            }
        } else {
            return null;
        }
    
        return $Services;
    }
    
}
?>
