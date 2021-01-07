<?php

require_once __DIR__ .'/lib/Class_tiktok.php';

$download = New TiktokDownloader();


//Download Single Video Using Link
$analyze = $download->analyze('https://vt.tiktok.com/ZSvfNnV7/');
print_r($download->Download($analyze['Data']['VideoID'])); 

/* Download Single Video Random by username
Enter Username without @
$a = $download->RandomVideo('saybillaaa_');
$rand = $a['VideoID'][rand( 0 , count($a['VideoID']) -1)];
print_r($rand); */
