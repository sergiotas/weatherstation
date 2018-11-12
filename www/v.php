<?php

  $lista=array("S","SW","W","NW","N","NE","E","SE");
  $ori="";
  if(isset($_GET["o"])) $ori=strtoupper(trim(substr($_GET["o"],0,2)));  
  $t=100;
  if(isset($_GET["t"])) $t=intval(substr($_GET["t"],0,3));  
  if($t<=0||$t>100) $t=100;

  $w=50*$t/100;
  $h=50*$t/100;
  
  for($pos=0;$pos<8;$pos++) if($ori==$lista[$pos]) break;
  if($pos>7) die("Not find");
  $src=imagecreatefrompng('v.png');
  $dst = imagecreatetruecolor($w,$h);
  imagecopyresampled($dst,$src,0,0,$pos*50,0,$w,$h,50,50);

  header('Content-type: image/png'); 
  ImagePNG($dst); 
  imagedestroy($dst); 	

?>