<?php

require_once __DIR__ .'/lib/Class_tiktok.php';

$download = New TiktokDownloader();
$analyze = $download->analyze('https://vt.tiktok.com/ZSvfNnV7/');
print_r($download->Download($analyze['Data']['VideoID']));