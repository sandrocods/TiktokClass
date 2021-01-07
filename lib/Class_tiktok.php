<?php
/* Class Tiktok Video Downloader
 * 05-01-2021
 * sandroputraa
 */
class TiktokDownloader
{
    private static function curl($url, $method = null, $postfields = null, $followlocation = null, $headers = null, $conf_proxy = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        if ($conf_proxy !== null) {
            curl_setopt($ch, CURLOPT_PROXY, $conf_proxy['Proxy']);
            curl_setopt($ch, CURLOPT_PROXYPORT, $conf_proxy['Proxy_Port']);
            if ($conf_proxy['Proxy_Type'] == 'SOCKS4') {
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4);
            }
            if ($conf_proxy['Proxy_Type'] == 'SOCKS5') {
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            }
            if ($conf_proxy['Proxy_Type'] == 'HTTP') {
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLOPT_HTTPPROXYTUNNEL);
            }
            if ($conf_proxy['Auth'] !== null) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $conf_proxy['Auth']['Username'].':'.$conf_proxy['Auth']['Password']);
                curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
            }
        }
        if ($followlocation !== null) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, $followlocation['Max']);
        }
        if ($method == "PUT") {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        }
        if ($method == "GET") {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        }
        if ($method == "POST") {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        }
        if ($headers !== null) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        $result = curl_exec($ch);
        $header = substr($result, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
        $body = substr($result, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
        $cookies = array();
        foreach ($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }
        return array(
            'HttpCode' => $httpcode,
            'Header' => $header,
            'Body' => $body,
            'Cookie' => $cookies,
            'Requests Config' => [
                    'Url' => $url,
                    'Header' => $headers,
                    'Method' => $method,
                    'Post' => $postfields
            ]
        );
    }

    private static function getStr($string, $start, $end)
    {
        $str = explode($start, $string);
        $str = explode($end, ($str[1]));
        return $str[0];
    }

    public function Analyze($link = null)
    {
        $analyze = TiktokDownloader::curl(
            $link,
            'GET',
            null,
            [
                'Max' => '2'
            ],
            [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9'
            ],
            null
        );
        if (strpos($analyze['Body'], '"serverCode":200')) {
            return [
                'Status' => true,
                'Data' => [
                        'VideoID' => TiktokDownloader::getStr($analyze['Body'], 'video":{"id":"', '"'),
                        'Username' => TiktokDownloader::getStr($analyze['Body'], '"uniqueId":"', '"'),
                        'Nickname' => TiktokDownloader::getStr($analyze['Body'], '"nickname":"', '"'),
                        'DownloadAddr' => TiktokDownloader::getStr($analyze['Body'], '"downloadAddr":"', '"')
                        ],
            ];
        } else {
            return [
                'Status' => false,
                'Data' => 'Error Link / Video not exits'
            ];
        }
    }

    public function Download($VideoID)
    {
        $url  = 'https://api2-16-h2.musical.ly/aweme/v1/play/?video_id='.$VideoID.'&vr_type=0&is_play_url=1&source=PackSourceEnum_PUBLISH&media_type=4';
        $curl = TiktokDownloader::curl(
            $url,
            'GET',
            null,
            [
                'Max' => '1'
            ],
            [
                'User-Agent: okhttp',
                'Referer: https://www.tiktok.com/'
            ],
            null
            );
            $filename = ".\\TiktokClass\\tmp\\" . $VideoID . ".mp4";
            $d = fopen($filename, "w");
            $fwrite = fwrite($d, $curl['Body']);
            fclose($d);
            if($fwrite === FALSE){
                return [
                    'Status' => false,
                    'Data' => 'Failed Writing File'
                ];
            }else{
                return [
                    'Status' => true,
                    'Data' => 'Success Writing File',
                    'FilePath' => $filename
                ];

            }
            //
            
        
    }

    public function RandomVideo($username = null){
        $Random = TiktokDownloader::curl(
            'https://www.tiktok.com/@'. $username,
            'GET',
            null,
            [
                'Max' => '2'
            ],
            [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9'
            ],
            null
        );
        if (strpos($Random['Body'], '"serverCode":200')) {
            preg_match_all('/video":{"id":"(.*?)"/',$Random['Body'], $out_vid);
            return [
                'Status' => true,
                'VideoID' => $out_vid[1]
            ];
        }else{
            return [
                'Status' => false,
                'Data' => 'Error Username not available / Another error'
            ];
        }
    }
}
