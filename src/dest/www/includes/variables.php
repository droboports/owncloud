<?php 
$app = "owncloud";
$appname = "ownCloud";
$appversion = "8.1.1";
$appsite = "https://owncloud.org/";
$apphelp = "https://owncloud.org/support/";

$applogs = array("/tmp/DroboApps/".$app."/log.txt",
                 "/tmp/DroboApps/".$app."/access.log",
                 "/tmp/DroboApps/".$app."/error.log");

$appprotos = array("https");
$appports = array("8051");
$droboip = $_SERVER['SERVER_ADDR'];
$apppage = $appprotos[0]."://".$droboip.":".$appports[0]."/";
if ($publicip != "") {
  $publicurl = $appprotos[0]."://".$publicip.":".$appports[0]."/";
} else {
  $publicurl = $appprotos[0]."://public.ip.address.here:".$appports[0]."/";
}
$portscansite = "http://mxtoolbox.com/SuperTool.aspx?action=scan%3a".$publicip."&run=toolpage";
?>