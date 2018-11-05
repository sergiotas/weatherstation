<?php

include "../config.php";


if(!isset($_GET["usr"])) die("not user");
$user=substr(trim($_GET["usr"]),0,30);

$existuser=false;
for($i=0;$i<count($validusers);$i++){
	if($validusers[$i]==$user) {
		$existuser=true;
		break;
	}
}
if(!$existuser) die("invalid user");

$seg=0;
$tempf=0;
$winddir=0;
$windspeedmph=0;
$windgustmph=0;
$windgustdir=0;
$windspdmph_avg=0;
$winddir_avg=0;
$humidity=0;
$rainin=0;
$pressure=0;
$light_lvl=0;
if(isset($_GET["sec"])) $seg=$_GET["sec"];
if(isset($_GET["tempf"])) $tempf=$_GET["tempf"];
if(isset($_GET["winddir"])) $winddir=$_GET["winddir"];                   // dirección viento
if(isset($_GET["windspeedmph"])) $windspeedmph=$_GET["windspeedmph"];    // velocidad viento
if(isset($_GET["windgustmph"])) $windgustmph=$_GET["windgustmph"];       // rafaga
if(isset($_GET["windgustdir"])) $windgustdir=$_GET["windgustdir"];       // dirección rafaga
if(isset($_GET["windspdmph_avg"])) $windspdmph_avg=$_GET["windspdmph_avg"];
if(isset($_GET["winddir_avg"])) $winddir_avg=$_GET["winddir_avg"];
if(isset($_GET["humidity"])) $humidity=$_GET["humidity"];
if(isset($_GET["rainin"])) $rainin=$_GET["rainin"];
if(isset($_GET["pressure"])) $pressure=$_GET["pressure"];
if(isset($_GET["light_lvl"])) $light_lvl=$_GET["light_lvl"];

// Convert Farenheit to Celsius
$tempc=(($tempf-32)*5)/9;
// Convert mph to kmh
$windspeedkmh=$windspeedmph*1.609344;
$windgustkmh=$windgustmph*1.609344;
$windspdkmh_avg=$windspdmph_avg*1.609344;
$rainin=$rainin*0.011; //Each dump is 0.011" of water

//$rs=$db->query("DROP TABLE $user"."_disk");
//$rs=$db->query("DROP TABLE $user"."_mem");

$rs=$db->query("SHOW TABLES LIKE '$user"."_disk'");
if($rs->num_rows==0) {
   $create="`fecha` datetime NOT NULL, `sec` double DEFAULT NULL, `tempc` double DEFAULT NULL,  `winddir` double DEFAULT NULL,  `windspeedkmh` double DEFAULT NULL,  `windgustkmh` double DEFAULT NULL,  `windgustdir` double DEFAULT NULL,  `windspdkmh_avg` double DEFAULT NULL,  `winddir_avg` double DEFAULT NULL, `humidity` double DEFAULT NULL,  `rainin` double DEFAULT NULL,  `pressure` double DEFAULT NULL,  `light_lvl` double DEFAULT NULL,  PRIMARY KEY (`fecha`)";
   $tbl="CREATE TABLE `$user"."_disk` ( $create ) ENGINE=MyISAM";
   $db->query($tbl);
   echo "tabla disk creada...<br>\r\n";
}

$rs=$db->query("SHOW TABLES LIKE '$user"."_mem'");
if($rs->num_rows==0) {
   $create="`fecha` datetime NOT NULL, `sec` double DEFAULT NULL, `tempc` double DEFAULT NULL,  `winddir` double DEFAULT NULL,  `windspeedkmh` double DEFAULT NULL,  `windgustkmh` double DEFAULT NULL,  `windgustdir` double DEFAULT NULL,  `windspdkmh_avg` double DEFAULT NULL,  `winddir_avg` double DEFAULT NULL, `humidity` double DEFAULT NULL,  `rainin` double DEFAULT NULL,  `pressure` double DEFAULT NULL,  `light_lvl` double DEFAULT NULL,  PRIMARY KEY (`fecha`)";
   $tbl="CREATE TABLE `$user"."_mem` ( $create ) ENGINE=MEMORY";
   $db->query($tbl);
   echo "tabla mem creada...<br>\r\n";
}


$fini=0;
$sql="SELECT * FROM $user"."_mem ORDER BY fecha ASC LIMIT 1";
$rsi=$db->query($sql);
if($rsi->num_rows>0){
	$rowi=$rsi->fetch_assoc();
	$fini=$rowi["fecha"];
}
$ffin=0;
$sql="SELECT * FROM $user"."_mem ORDER BY fecha DESC LIMIT 1";
$rsf=$db->query($sql);
if($rsf->num_rows>0){
	$rowf=$rsf->fetch_assoc();
	$ffin=$rowf["fecha"];
}

//echo $fini." - ".$ffin."<br>\r\n";
DBstrToTimestamp($fini,$fi);
DBstrToTimestamp($ffin,$ff); 
$diferencia=$ff-$fi;
//echo ">>dif: $diferencia - cada: ".save_each." <br/>\r\n";
if($diferencia>save_each){
  echo "COPIAR A DISCO<br>\r\n";
  $sql="INSERT INTO $user"."_disk SELECT * FROM $user"."_mem";
  $db->query($sql);
  $sql="TRUNCATE $user"."_mem";
  $db->query($sql);
}

$sql="INSERT INTO $user"."_mem (fecha,sec,tempc,winddir,windspeedkmh,windgustkmh,windgustdir,windspdkmh_avg,winddir_avg, humidity,rainin,pressure,light_lvl) VALUES (";
$sql.="\"".date("Y-m-d H:i:s")."\",";
$sql.="$seg,";
$sql.="$tempc,";
$sql.="$winddir,";
$sql.="$windspeedkmh,";
$sql.="$windgustkmh,";
$sql.="$windgustdir,";
$sql.="$windspdkmh_avg,";
$sql.="$winddir_avg,";
$sql.="$humidity,";
$sql.="$rainin,";
$sql.="$pressure,";
$sql.="$light_lvl";
$sql.=")";
$rsi=$db->query($sql);
if(!$rsi){
    echo "errno: " . mysqli_errno($db) . "<br/>\r\n";
    echo "error: " . mysqli_error($db) . "<br/>\r\n";
	echo "\r\n".$sql."r\n";
	return;
}
//echo $sql;
echo "ok";
return;


?>