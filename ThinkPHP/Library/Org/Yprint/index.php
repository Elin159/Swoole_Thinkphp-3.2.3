<?php 
include("Yprint.class.php");
$print = new Yprint();
$content = "测试测试";
//$apiKey = "1a00dd7170f66d8c5c0d4d56bab9ca7f1fa924c6";
//
//$msign = 'aty65jc68r4w';
$content = "<MA>0</MA>\r\r\r<center>测试333</center>\r<QR>https://www.baidu.com</QR>\r<FH2><FW2>加大测试内容测试测试</FW2></FH2>";
//打印
$print->action_print(6051,'4004514417',$content,$apiKey,$msign);
//添加打印机
//$print->action_addprint(407,'4004508952','wajuka','k2s测试','18111111111',$apikEY,$msign);
//删除打印机
//$print->action_removeprinter(407,'4004508952',$apiKey,$msign);
 ?>