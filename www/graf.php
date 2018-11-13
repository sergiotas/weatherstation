<?php

  include "config.php";

  if(!isset($_GET["user"])) die("not user");
  $user=substr(trim($_GET["user"]),0,30);

  $existuser=false;
  for($i=0;$i<count($ValidUsers);$i++){
		if($ValidUsers[$i]["user"]==$user) {
			$existuser=true;
			break;
		}
  }
  if(!$existuser) die("invalid user");

  $tipo="avg";
  if(isset($_GET["t"])) $tipo=trim(substr($_GET["t"],0,10));
  
  $q="";
  if(!isset($_GET["w"])) die("invalid type");
  $q=substr(trim($_GET["w"]),0,25);
  
  // d1 = Y-m-d
  if(!isset($_GET["d1"])) die("invalid date1");  
  if(!YmdToTimeStamp($_GET["d1"],$mkf1)) die("invalid date1.");

  // d2 = Y-m-d
  if(!isset($_GET["d2"])) die("invalid date2");
  if(!YmdToTimeStamp($_GET["d2"],$mkf2)) die("invalid date2.");

  if($mkf1>$mkf2) die("date1>date2");
  if($mkf2-$mkf1>$diferencedays*24*60*60){
	  die("Max $diferencedays days diference");
  }
  
  if($mkf1==$mkf2){ // day
	$dias=1;  
  }else{          // period  
	$dias=intval(($mkf2-$mkf1)/(24*60*60))+1;  
  }	
  
  $font_file = realpath('.').'/FreeSans.ttf'; 
//  $font_file = realpath('.').'/DejaVuSans.ttf'; 
  $font_size8 = 8; 
  $type_space = imagettfbbox($font_size8, 0, $font_file, "99/99");

  $txtwidth = abs($type_space[4] - $type_space[0]);
  $txtheight = abs($type_space[5] - $type_space[1]);
  
  $top=35;
  $bottom=35;
  $left=60;
  $right=10;
  
  if($dias==1){
	  $w=$left+$right+24*$txtwidth;
	  $intervalo = 10*60; // 10 minutos,600 segundos
  }else{
	  $w=$left+$right+$dias*($txtwidth+5);	  
	  $intervalo = 60*60; // 60 minutos,3600 segundos
  }
  if($w<def_graph_width) $w=def_graph_width;
  
  $h=def_graph_height;
  $hh=$h-$bottom-$top;
  $ww=$w-$left-$right;
  

  
  if(strcasecmp($q,"tempc")==0){
	  $fld="tempc";
	  $tit=_("Temperature")." &#xb0;C";
  }elseif(strcasecmp($q,"windspeed")==0){
	  $fld="windspeedkmh";
	  $tit=_("Wind")." Km/h";
  }elseif(strcasecmp($q,"winddir")==0){
	  $fld="winddir";
	  $tit=_("Direction");
  }elseif(strcasecmp($q,"rainin")==0){
	  $fld="rainin";
	  $tit=_("Rain");
  }elseif(strcasecmp($q,"pressure")==0){
	  $fld="pressure";
	  $tit=_("Pressure");
  }elseif(strcasecmp($q,"humidity")==0){
	  $fld="humidity";
	  $tit=_("Humidity");
  }elseif(strcasecmp($q,"light_lvl")==0){
	  $fld="light_lvl";
	  $tit=_("Light");
  }elseif(strcasecmp($q,"windgustkmh")==0){
	  $fld="windgustkmh";
	  $tit="WindGust km/h";
  }elseif(strcasecmp($q,"windspdkmh_avg")==0){
	  $fld="windspdkmh_avg";
	  $tit=_("Wind")." Avg Km/h";
  }elseif(strcasecmp($q,"winddir_avg")==0){
	  $fld="winddir_avg";
  	  $tit=_("Direction")." Avg Km/h";
  }else{
		  die("invalid type 2");
  }
//  $tit="";

  if(strcasecmp($tipo,"sum")==0){
	  $funcion="sum";
	  $titfunc=_("Accumulated");
  }else{
	  $funcion="avg";
	  $titfunc=_("Average");
  }
  
  $tabla=$user."_disk";
  $entre="d.fecha between \"".date("Y-m-d",$mkf1)." 0:0:0\" AND \"".date("Y-m-d",$mkf2)." 23:59:59\"";

  $sql="SELECT max(d.$fld) as maximo, min(d.$fld) as minimo FROM $tabla d ".
	   "WHERE $entre ";
  if(!$rsmm=$db->query($sql)){
    echo "errno: " . mysqli_errno($db) . "<br/>\r\n";
    echo "error: " . mysqli_error($db) . "<br/>\r\n";
	echo $sql."\r\n";
	return;	  
  }
  if($rsmm->num_rows!=1) die("Invalid query");
  $rowmm=$rsmm->fetch_assoc();
//  die($rowmm["maximo"]."##".$rowmm["minimo"]);
  $sql="SELECT d.fecha,  $funcion(d.$fld) as promedio FROM $tabla d ".
	   "WHERE $entre ".
	   "GROUP BY UNIX_TIMESTAMP(d.fecha) div $intervalo ".
	   "ORDER BY d.fecha";  
  if(!$rs=$db->query($sql)){
    echo "errno: " . mysqli_errno($db) . "<br/>\r\n";
    echo "error: " . mysqli_error($db) . "<br/>\r\n";
	echo $sql."\r\n";
	return;
	  
  }
  
  if(isset($_GET["data"])){
	  if($rs->num_rows>0){
		echo "<table border=\"1\" cellpadding=\"5\">";
		while($row=$rs->fetch_assoc()){
			DBstrToTimestamp($row["fecha"],$fl);
			echo "<tr><td>".date("d-m-Y",$fl)."</td><td>".date("H:i:s",$fl)."</td><td>".number_format($row["promedio"], 2)."</td></tr>";
		} // while
		echo "</table>";
	  }else echo "no data";
	  return;
  }

  $im = @imagecreate($w,def_graph_height);
  
  if($mkf1==$mkf2){ // day
	$titulo="$tit  (".date("d-m-Y",$mkf1).")";
  }else{
	$titulo="$tit  (".date("d-m-Y",$mkf1)." # ".date("d-m-Y",$mkf2).")";
  }	  
  $font_size = 12; // Font size is in pixels.
  $type_space = imagettfbbox($font_size, 0, $font_file, $titulo);

  $txtwidth = abs($type_space[4] - $type_space[0]);
  $txtheight = abs($type_space[5] - $type_space[1]);

  
  $black = imagecolorallocate($im, 0, 0, 0);
  $white = imagecolorallocate($im, 255, 255, 255);
  $red = imagecolorallocate($im, 255, 0, 0);
  $blue = imagecolorallocate($im, 0, 0, 255);
  imagefill ( $im , 0 , 0 , $white );
  imageline($im,$left,$top,$left,$hh+$top,$black);
  imageline($im,$left,$hh+$top,$ww+$left,$hh+$top,$black);
//  imageline($im,$ww+$left,$top,$ww+$left,$hh+$top,$black);

  imagettftext($im, $font_size, 0, ($w-$txtwidth)/2, $txtheight+3, $black, $font_file, $titulo);

  
  $estilo = Array(
                IMG_COLOR_TRANSPARENT, 
                IMG_COLOR_TRANSPARENT, 
                $black, 
                );
				
  $font_size=8; 
  
  imagettftext($im, $font_size, 0, 5, 10, $black, $font_file, $titfunc);
  
  
  if($mkf1==$mkf2){ // day
	$divisionesx=24;  
  }else{          // period  
	$divisionesx=$dias;  
  }	
  $auxx=$ww/$divisionesx;
  for($i=0;$i<=$divisionesx;$i++){
    imagesetstyle($im, $estilo);
	imageline($im,$left+$i*$auxx,$top,$left+$i*$auxx,$hh+$top,IMG_COLOR_STYLED);
	if($i<$divisionesx){
		if($mkf1==$mkf2){ // day
			$titx="$i";
		}else{
			$xf=mktime(0,0,0,date("m",$mkf1),date("d",$mkf1)+$i,date("Y",$mkf1));			
			$titx=date("d/m",$xf);
//			imageline($im,$left+$i*$auxx+1*$auxx/4,$top,$left+$i*$auxx+1*$auxx/4,$hh+$top,IMG_COLOR_STYLED);
//			imageline($im,$left+$i*$auxx+2*$auxx/4,$top,$left+$i*$auxx+2*$auxx/4,$hh+$top,IMG_COLOR_STYLED);
//			imageline($im,$left+$i*$auxx+3*$auxx/4,$top,$left+$i*$auxx+3*$auxx/4,$hh+$top,IMG_COLOR_STYLED);
		}
		$type_space = imagettfbbox($font_size, 0, $font_file, $titx);
		$txtwidth = abs($type_space[4] - $type_space[0]);
		$txtheight = abs($type_space[5] - $type_space[1]);	
		imagettftext($im, $font_size, 0, $left+($i-1)*$auxx+($auxx-$txtwidth)+$txtwidth, $hh+$top+$txtheight+3, $black, $font_file, $titx);	
	}
  }
  
  $divisionesy=10; //<--------------------------------------------------------------------------------divisionesy verticales
  $salto=($rowmm["maximo"]-$rowmm["minimo"])/$divisionesy;
  $auxy=$hh/$divisionesy;
  for($i=0;$i<=$divisionesy;$i++){
    imagesetstyle($im, $estilo);
	imageline($im,$left,$top+$i*$auxy,$ww+$left,$top+$i*$auxy,IMG_COLOR_STYLED);
	
	$v=($divisionesy-$i)*$salto+$rowmm["minimo"];
	$v=number_format ($v, 2);
	$type_space = imagettfbbox($font_size, 0, $font_file, "$v");
    $txtwidth = abs($type_space[4] - $type_space[0]);
    $txtheight = abs($type_space[5] - $type_space[1]);
    imagettftext($im, $font_size, 0, $left-$txtwidth-2, $top+$i*$auxy+$txtheight/2, $black, $font_file, "$v");	
	
  }
  
  if($rs->num_rows>0&&$salto>0)
	$rowant=$rs->fetch_assoc();
	
	while($rowsig=$rs->fetch_assoc()){
		DBstrToTimestamp($rowant["fecha"],$fant);
		DBstrToTimestamp($rowsig["fecha"],$fsig);
		$horaant=substr($rowant["fecha"],11,2);
		$minant=substr($rowant["fecha"],14,2);
		$horasig=substr($rowsig["fecha"],11,2);
		$minsig=substr($rowsig["fecha"],14,2);
		if($salto!=0){
			if($dias==1)
				imageline($im,$left+$horaant*$auxx+$minant*$auxx/60,
						  $top+(($rowmm["maximo"]-$rowant["promedio"])*$auxy)/$salto,
						  $left+$horasig*$auxx+$minsig*$auxx/60,
						  $top+(($rowmm["maximo"]-$rowsig["promedio"])*$auxy)/$salto,
						  $red);					  
			else{
	//			$antd=intdiv(($fant-$mkf1),86400);
				$antd=($fant-$mkf1-($fant-$mkf1)%86400)/86400; 
				$anth=($fant-$mkf1)%86400;
	//			$sigd=intdiv(($fsig-$mkf1),86400);
				$sigd=($fsig-$mkf1-($fsig-$mkf1)%86400)/86400; 
				$sigh=($fsig-$mkf1)%86400;
				
				imageline($im,$left+$antd*$auxx+ $anth*$auxx/86400,
						  $top+(($rowmm["maximo"]-$rowant["promedio"])*$auxy)/$salto,
						  $left+$sigd*$auxx+$sigh*$auxx/86400,
						  $top+(($rowmm["maximo"]-$rowsig["promedio"])*$auxy)/$salto,
						  $red);					  
			}
		}
		$rowant=$rowsig;

	} // while
	
  if($mkf1==$mkf2){ // day
     $tit=_("hours");
  }else{
     $tit=_("days");
  }
  
  $type_space = imagettfbbox($font_size, 0, $font_file, "$tit");
  $txtwidth = abs($type_space[4] - $type_space[0]);
  $txtheight = abs($type_space[5] - $type_space[1]);
  imagettftext($im, $font_size, 0, ($w-$txtwidth)/2, $h-$txtheight/2, $black, $font_file, "$tit");	
		
	
  # Prints out all the figures and picture and frees memory 
  header('Content-type: image/png'); 

  ImagePNG($im); 
  imagedestroy($im); 	
	
  return;
  
?>