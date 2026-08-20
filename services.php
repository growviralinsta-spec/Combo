<?php
header('Content-Type: application/json; charset=utf-8');
$file=__DIR__.'/data.json';
$data=file_exists($file)?json_decode(file_get_contents($file),true):['services'=>[]];
$out=[];
foreach(($data['services']??[]) as $s){if(!empty($s['active'])){$out[]=$s;}}
echo json_encode($out);
?>