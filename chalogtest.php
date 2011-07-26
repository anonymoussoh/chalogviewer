<?php

$logdata = file_get_contents("http://81.la/c/chalog.php?page=1&value=1000");
$starttime = mktime(0,0,0,7,22,2011);
$endtime = mktime(0,0,0,7,23,2011);
//$logdata = file_get_contents("http://81.la/c/chalog.php?starttime=".$starttime."&endtime=".$endtime."&value=10000");
$decoded_data = json_decode($logdata,true);
var_dump($decoded_data);
$end_data = end($decoded_data['newcomments']);
var_dump($end_data);
$number = count($decoded_data['newcomments']);
echo $number;
echo "<br>";
echo date("Y m d H:i:s",$end_data['date']);
