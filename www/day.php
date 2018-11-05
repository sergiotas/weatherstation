<?php

  include "config.php";

  $f1="2018-5-22";
  echo $f1."<br>";
  if(!YmdToTimeStamp($f1,&$mkf1)) return die("no");
  echo date("d-m-Y",$mkf1);
  return;
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

  $q="";
  if(!isset($_GET["w"])) die("invalid type");;
  $q=substr(trim($_GET["w"]),0,25);
  
  $f="";
  if(!isset($_GET["d"])) die("invalid date");;
  if(!strToTimestamp($_GET["d"],$mkf)) die("invalid date 2");

  
  if(strcasecmp($q,"tempc")==0){
	  $fld="tempc";
  }elseif(strcasecmp($q,"windspeed")==0){
	  $fld="windspeedkmh";
  }elseif(strcasecmp($q,"winddir")==0){
	  $fld="winddir";
  }elseif(strcasecmp($q,"rainin")==0){
	  $fld="rainin";
  }elseif(strcasecmp($q,"pressure")==0){
	  $fld="pressure";
  }elseif(strcasecmp($q,"humidity")==0){
	  $fld="humidity";
  }elseif(strcasecmp($q,"light_lvl")==0){
	  $fld="light_lvl";
  }elseif(strcasecmp($q,"windgustkmh")==0){
	  $fld="windgustkmh";
  }elseif(strcasecmp($q,"windspdkmh_avg")==0){
	  $fld="windspdkmh_avg";
  }elseif(strcasecmp($q,"winddir_avg")==0){
	  $fld="winddir_avg";
  }else{
		  die("invalid type 2");
  }
  
  $intervalo = 10*60; // 10 minutos,600 segundos
  $tabla=$user."_disk";
  $entre="d.fecha between \"".date("Y-m-d",$mkf)." 0:0:0\" AND \"".date("Y-m-d",$mkf)." 23:59:59\"";

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
  $sql="SELECT d.fecha,  avg(d.$fld) as promedio FROM $tabla d ".
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
		echo "<table border=\"1\">";
		while($row=$rs->fetch_assoc()){
			echo "<tr><td>".$row["fecha"]."</td><td>".number_format($row["promedio"], 2)."</td></tr>";
		} // while
		echo "</table>";
	  }else echo "no data";
	  return;
  }
// header("Content-Type: image/png");
//  die(realpath('.'));
//  if(!putenv('GDFONTPATH=' . realpath('.'))) die("error putenv: ".getenv('GDFONTPATH'));

  $im = @imagecreate(def_graph_width,def_graph_height);
  $w=def_graph_width;
  $h=def_graph_height;
  $top=35;
  $bottom=25;
  $left=60;
  $right=10;
  $hh=$h-$bottom-$top;
  $ww=$w-$left-$right;
  
  $titulo="$q  (".date("d-m-Y",$mkf).")";
  $font_size = 12; // Font size is in pixels.
  $font_file = realpath('.').'/FreeSans.ttf'; // This is the path to your font file.
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
  $auxx=$ww/24;
  for($i=0;$i<25;$i++){
    imagesetstyle($im, $estilo);
	imageline($im,$left+$i*$auxx,$top,$left+$i*$auxx,$hh+$top,IMG_COLOR_STYLED);
	if($i<24){
		$type_space = imagettfbbox($font_size, 0, $font_file, "$i");
		$txtwidth = abs($type_space[4] - $type_space[0]);
		$txtheight = abs($type_space[5] - $type_space[1]);	
		imagettftext($im, $font_size, 0, $left+($i-1)*$auxx+($auxx-$txtwidth)+$txtwidth, $hh+$top+$txtheight+3, $black, $font_file, "$i");	
	}
  }
  
  $divisiones=10; //<--------------------------------------------------------------------------------divisiones verticales
  $salto=($rowmm["maximo"]-$rowmm["minimo"])/$divisiones;
  $auxy=$hh/$divisiones;
  for($i=0;$i<=$divisiones;$i++){
    imagesetstyle($im, $estilo);
	imageline($im,$left,$top+$i*$auxy,$ww+$left,$top+$i*$auxy,IMG_COLOR_STYLED);
	
	$v=($divisiones-$i)*$salto+$rowmm["minimo"];
	$v=number_format ($v, 2);
	$type_space = imagettfbbox($font_size, 0, $font_file, "$v");
    $txtwidth = abs($type_space[4] - $type_space[0]);
    $txtheight = abs($type_space[5] - $type_space[1]);
    imagettftext($im, $font_size, 0, $left-$txtwidth-2, $top+$i*$auxy+$txtheight/2, $black, $font_file, "$v");	
	
  }
  
  if($rs->num_rows>0)
	$rowant=$rs->fetch_assoc();
	while($rowsig=$rs->fetch_assoc()){
		DBstrToTimestamp($rowant["fecha"],$fant);
		DBstrToTimestamp($rowsig["fecha"],$fsig);
		$horaant=substr($rowant["fecha"],11,2);
		$minant=substr($rowant["fecha"],14,2);
		$horasig=substr($rowsig["fecha"],11,2);
		$minsig=substr($rowsig["fecha"],14,2);
		
		if($salto>0)
		imageline($im,$left+$horaant*$auxx+$minant*$auxx/60,
					  $top+(($rowmm["maximo"]-$rowant["promedio"])*$auxy)/$salto,
					  $left+$horasig*$auxx+$minsig*$auxx/60,
					  $top+(($rowmm["maximo"]-$rowsig["promedio"])*$auxy)/$salto,
					  $red);					  
		
		$rowant=$rowsig;

	} // while
	
		
	
  # Prints out all the figures and picture and frees memory 
  header('Content-type: image/png'); 

  ImagePNG($im); 
  imagedestroy($im); 	
	
  return;
  
?>