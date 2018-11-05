<?php

$fecha=date("Y-m-d H:i:s");
echo $fecha."<br>";
DBstrToTimestamp($fecha,$mkf);
echo date("Y-m-d H:i:s",$mkf);
return;

//---------------------------------------------------------------
function DBstrToTimestamp($dbDate,&$mkf){ //Y-m-d H:i:s
//---------------------------------------------------------------  
  $y=intval(substr($dbDate,0,4));
  $m=intval(substr($dbDate,5,2));
  $d=intval(substr($dbDate,8,2));
  $h=intval(substr($dbDate,11,2));
  $i=intval(substr($dbDate,14,2));
  $s=intval(substr($dbDate,17,2));  
  $mkf=mktime ( $h, $i, $s, $m, $d, $y);
  return true;
}


?>