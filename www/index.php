<?php



include "config.php";

  $lsTYPE[]=array("id"=>1,"type"=>"avg","name"=>_("Average"));
  $lsTYPE[]=array("id"=>2,"type"=>"sum","name"=>_("Accumulated"));
  
  $lsOPT[]=array("id"=>1, "name"=>_("Temperature"),   "type"=>1, "bydef"=>1);
  $lsOPT[]=array("id"=>2, "name"=>_("Wind"),        "type"=>1, "bydef"=>1);
  $lsOPT[]=array("id"=>3, "name"=>_("Direction"),       "type"=>1, "bydef"=>1);
  $lsOPT[]=array("id"=>4, "name"=>_("Rain"),        "type"=>1, "bydef"=>1);
  $lsOPT[]=array("id"=>5, "name"=>_("Humidity"),       "type"=>1, "bydef"=>0);
  $lsOPT[]=array("id"=>6, "name"=>_("Pressure"),"type"=>1, "bydef"=>0);
  $lsOPT[]=array("id"=>7, "name"=>_("Light"),           "type"=>1, "bydef"=>0);
  $lsOPT[]=array("id"=>8, "name"=>_("Wind Avg"),    "type"=>1, "bydef"=>0);
  $lsOPT[]=array("id"=>9, "name"=>_("Direction Avg"),   "type"=>1, "bydef"=>0);
  $lsOPT[]=array("id"=>10, "name"=>_("Rain"),       "type"=>2, "bydef"=>1);

//  if(!isset($_GET["user"])) die("not user");
  if(!isset($_GET["user"])) $_GET["user"]="jc";
  $user=substr(trim($_GET["user"]),0,30);
  

  $existuser=false;
  for($i=0;$i<count($ValidUsers);$i++){
		if($ValidUsers[$i]["user"]==$user) {
			$existuser=true;
			break;
		}
  }
  if(!$existuser) die("invalid user");


  strToTimestamp(date("d-m-Y"),$ayer);
  $ayer=$ayer-60*60*24;


  $fecha1="";
  if(isset($_GET["f1"])) $fecha1=substr($_GET["f1"],0,10);
  if(isset($_POST["txtfecha1"])) $fecha1=substr($_POST["txtfecha1"],0,10);
  if(!YmdToTimeStamp($fecha1,$mkf1)){
	  $fecha1="";
  }

  $fecha2="";
  if(isset($_GET["f2"])) $fecha2=substr($_GET["f2"],0,10);
  if(isset($_POST["txtfecha2"])) $fecha2=substr($_POST["txtfecha2"],0,10);
  if(!YmdToTimeStamp($fecha2,$mkf2)){
	  $fecha2="";
  }
  
  if($mkf1>$mkf2){
	  die("Date1 &gt; Date2");
  }

  if($mkf2-$mkf1>$diferencedays*24*60*60){
	  die("Max $diferencedays days diference");
  }
  
  ShowHeader($db,$user,$idioma,$fecha1,$fecha2,$ayer,$lastrow);
  
  ShowActual($db,$user,$lastrow);

  // Graficas  
  if($fecha1!=""&&$fecha2!=""){
	 $query="user=$user&lang=$idioma&d1=".date("Y-m-d",$mkf1)."&d2=".date("Y-m-d",$mkf2)."";
	 echo "<div style=\"margin-left:10px;\"><hr/>"._("Since")." ".date("d-m-Y",$mkf1)." "._("until")." ".date("d-m-Y",$mkf2)."<hr/>\r\n";
     if($lsOPT[0]["value"]==1) echo "<a href=\"/graf.php?data&$query&w=tempc\" target=\"_blank\"><img  src=\"/graf.php?$query&w=tempc\"/></a><br/>\r\n";
     if($lsOPT[1]["value"]==1) echo "<a href=\"/graf.php?data&$query&w=windspeed\" target=\"_blank\"><img  src=\"/graf.php?$query&w=windspeed\"/></a><br/>\r\n";
     if($lsOPT[2]["value"]==1) echo "<a href=\"/graf.php?data&$query&w=winddir\" target=\"_blank\"><img  src=\"/graf.php?$query&w=winddir\"/></a><br/>\r\n";
     if($lsOPT[3]["value"]==1) echo "<a href=\"/graf.php?data&$query&w=rainin\" target=\"_blank\"><img  src=\"/graf.php?$query&w=rainin\"/></a><br/>\r\n";
     if($lsOPT[4]["value"]==1) echo "<a href=\"/graf.php?data&$query&w=pressure\" target=\"_blank\"><img  src=\"/graf.php?$query&w=pressure\"/></a><br/>\r\n";
     if($lsOPT[5]["value"]==1) echo "<a href=\"/graf.php?data&$query&w=humidity\" target=\"_blank\"><img  src=\"/graf.php?$query&w=humidity\"/></a><br/>\r\n";
     if($lsOPT[6]["value"]==1) echo "<a href=\"/graf.php?data&$query&w=light_lvl\" target=\"_blank\"><img  src=\"/graf.php?$query&w=light_lvl\"/></a><br/>\r\n";
//     if($lsOPT[0]["value"]==1) echo "<a href=\"/graf.php?data&$query&w=windgustkmh\" target=\"_blank\"><img  src=\"/graf.php?$query&w=windgustkmh\"/></a><br/>\r\n";
     if($lsOPT[7]["value"]==1) echo "<a href=\"/graf.php?data&$query&w=windspdkmh_avg\" target=\"_blank\"><img  src=\"/graf.php?$query&w=windspdkmh_avg\"/></a><br/>\r\n";
     if($lsOPT[8]["value"]==1) echo "<a href=\"/graf.php?data&$query&w=winddir_avg\" target=\"_blank\"><img  src=\"/graf.php?$query&w=winddir_avg\"/></a><br/>\r\n";	 
     if($lsOPT[9]["value"]==1) echo "<a href=\"/raf.php?data&$query&w=rainin\" target=\"_blank\"><img  src=\"/graf.php?t=sum&$query&w=rainin\"/></a><br/>\r\n";
	 echo "</div>";
  }
	   
  echo ShowFooter();
 
  return;

//---------------------------------------------------------------
function getRainin($db,$user,&$aInfo){
//---------------------------------------------------------------
  $aInfo=array('ok'=>0);
  
  $tabla=$user."_mem";
  $sql="SELECT fecha FROM $tabla ORDER BY fecha ASC LIMIT 1";  
  if(!($rsi=$db->query($sql))) return false;
  if($rsi->num_rows==0) return false;
  $rowi=$rsi->fetch_assoc();
  DBstrToTimestamp($rowi["fecha"],$fi);
  $sql="SELECT fecha FROM $tabla ORDER BY fecha DESC LIMIT 1";  
  if(!($rsf=$db->query($sql))) return false;
  if($rsf->num_rows==0) return false;
  $rowf=$rsf->fetch_assoc();
  DBstrToTimestamp($rowf["fecha"],$ff);
  $dif=$ff-$fi;
  $h=intval($dif/3600);
  $resto=$dif-3600*$h;
  $m=intval($resto/60);
  $s=$resto-$m*60;

  $sql="SELECT SUM(rainin) as total FROM $tabla";  
  if(!($rs=$db->query($sql))) return false;
  if($rs->num_rows==0) return false;
  $row=$rs->fetch_assoc();
  
  $aInfo=array('ok'=>1,'inic'=>$fi,'end'=>$ff,'h'=>$h,'m'=>$m,'s'=>$s,'total'=>$row['total']);
  
  return true;
}  

//---------------------------------------------------------------
function getParametros(){
//---------------------------------------------------------------
global $lsOPT,$lsTYPE;

	for($i=0;$i<count($lsOPT);$i++){
		$lsOPT[$i]["value"]=0;
	}

	
	if(isset($_POST["botDo"])){ // submit
		for($j=0;$j<count($lsTYPE);$j++){
			$nh="hidd".$lsTYPE[$j]["id"];
			if(isset($_POST[$nh])){
				$l=explode(";",$_POST[$nh]);
				for($m=0;$m<count($l);$m++){
				  if(trim($l[$m])!=""){
					 for($i=0;$i<count($lsOPT);$i++){
						if($lsOPT[$i]["id"]==$l[$m]){
							$lsOPT[$i]["value"]=1;
							break;
						}
					 } // for 
				  }
				} // for
			} // POST
		} // for	
	}else{
		for($i=0;$i<count($lsOPT);$i++){			
			$fld="idopt".$lsOPT[$i]["id"];
			if(isset($_COOKIE[$fld])){
				$lsOPT[$i]["value"]=intval($_COOKIE[$fld]);
//				echo $fld."-".$_COOKIE[$fld]."# ";
			}else{
				$lsOPT[$i]["value"]=$lsOPT[$i]["bydef"];
			}
		}
	}
	
	for($i=0;$i<count($lsOPT);$i++){
		$fld="idopt".$lsOPT[$i]["id"];
		@setcookie($fld, $lsOPT[$i]["value"], time()+60*60*24*60); // 60 days
//		echo $fld."-".$lsOPT[$i]["value"]."# ";
		
	}

//die("--");
}


//---------------------------------------------------------------
function ShowPerido($user,$fecha1,$fecha2,$idioma,$ayer){
//---------------------------------------------------------------
global $lsOPT,$lsTYPE;
    $mb=isMobile();
	
	echo "<form id=\"frmfechas\" name=\"frmfechas\" method=\"post\" enctype=\"multipart/form-data\" action=\"".$_SERVER["PHP_SELF"]."?user=$user&lang=$idioma\" data-ajax=\"false\">";
	
	echo "<table><tr><td>";
	
	echo "<table><tr>".
		"<td style=\"height:25px\">"._("Ini").": </td>".
		"<td style=\"height:25px\"><input name=\"txtfecha1\" id=\"txtfecha1\" type=\"datapicker\" value=\"".($fecha1==""?date("Y-m-d",$ayer):$fecha1)."\" style=\"width:110px;\"></td> ".
		"<td style=\"height:25px\"><input name=\"botDo\" id=\"botDo\" type=\"submit\" data-inline=\"true\" value=\""._("GO")."\" data-mini=\"true\"></td>".
		"</tr><tr>".
		"<td style=\"height:25px\">"._("End").": </td>".
		"<td style=\"height:25px\"><input name=\"txtfecha2\" id=\"txtfecha2\" type=\"datapicker\" value=\"".($fecha2==""?date("Y-m-d",$ayer):$fecha2)."\" style=\"width:110px;\"></td> ".
		"<td>&nbsp;</td>".

		"</tr></table>";
		
	if($mb){
		echo "</td></tr><tr><td>";
	}else{
		echo "</td><td>&nbsp;&nbsp;</td><td>&nbsp;</td><td>";
	}

	
	for($j=0;$j<count($lsTYPE);$j++){
		$nn="select-".$lsTYPE[$j]["id"];
		$nh="hidd".$lsTYPE[$j]["id"];
		echo "<input type=\"hidden\" id=\"$nh\" name=\"$nh\" value=\"\">";
		echo "<div class=\"ui-field-contain\">
		<legend style=\"display: inline-block;height:1em;padding:0px;\">"._($lsTYPE[$j]["name"])."</legend>
	    <select  name=\"$nn\" id=\"$nn\" data-native-menu=\"false\" multiple=\"multiple\" data-iconpos=\"left\">
	        <option>"._("Choose Options")."</option>";
		
		for($i=0;$i<count($lsOPT);$i++){
			if(strcmp($lsTYPE[$j]["id"],$lsOPT[$i]["type"])==0){
				$fld="idchk".$lsOPT[$i]["id"];
				echo "<option id=\"$fld\" name=\"$fld\" value=\"".$lsOPT[$i]["id"]."\" ".($lsOPT[$i]["value"]==1?"selected":"").">".$lsOPT[$i]["name"]."</option>\r\n";
			}
		} // for $i		
		echo "</select></div>";
		if($mb)
			echo "</td></tr><tr><td>";		
		else
			echo "</td><td>";		
	} // for $j


	
	
	
	echo "</td></tr></table>";
	
	
	echo "</form>\r\n";
	

}	

//---------------------------------------------------------------
function SetJQ($db,$user,&$lastrow){
//---------------------------------------------------------------
global $lsTYPE,$lsOPT;
  getRainin($db,$user,$aInfo);
  $table=$user."_mem";
  $sql="SELECT * FROM $table ORDER by fecha DESC LIMIT 1"; // last data
  $lastrow=null;
  $grados=0;$windspeed=0;
  if(($rs=$db->query($sql))) {
	if($rs->num_rows>0){
		$lastrow=$rs->fetch_assoc();
		$grados=number_format($lastrow["tempc"],1);
		$windspeed=number_format($lastrow["windspeedkmh"],1);
	}
  }

	 
	$v=""; 
	for($j=0;$j<count($lsTYPE);$j++){
		$nn="select-".$lsTYPE[$j]["id"];
		$nh="hidd".$lsTYPE[$j]["id"];
		$v.="   var lst".$lsTYPE[$j]["id"]." = document.getElementById(\"$nn\");\r\n";
		$v.="   var hid".$lsTYPE[$j]["id"]." = document.getElementById(\"$nh\");\r\n";
		$v.="   hid".$lsTYPE[$j]["id"].".value='';\r\n";
		$v.="   for(var i = 1; i < lst".$lsTYPE[$j]["id"].".options.length; i++){\r\n".
		"      if(lst".$lsTYPE[$j]["id"].".options[i].selected == true){\r\n".
		"         hid".$lsTYPE[$j]["id"].".value=hid".$lsTYPE[$j]["id"].".value+lst".$lsTYPE[$j]["id"].".options[i].value+';';\r\n".
		"      }\r\n".
		"   }\r\n";
	} // for $j
  
echo "	
<script>

$(document).ready(function() {
	  
  $('#txtfecha1').datepicker( {
	dateFormat: 'yy-mm-dd',
    selectWeek: true,
    inline: true,
    startDate: '01/01/2000',
    firstDay: 1,
  });

  $('#txtfecha2').datepicker( {
	dateFormat: 'yy-mm-dd',
    selectWeek: true,
    inline: true,
    startDate: '01/01/2000',
    firstDay: 1,
  });
  
  
  $('.vwind').kumaGauge({
	    value : $windspeed, // Math.floor((Math.random() * 99) + 1),	
		radius : 50, 
		paddingX : 40, 
		paddingY : 40,  
		gaugeWidth : 30, 
		fill : '0-#1cb42f:0-#fdbe37:50-#fa4133:100', 
		gaugeBackground : '#f4f4f4', 
		background : $( 'body' ).css( 'backgroundColor' ),
		showNeedle : false, 
		animationSpeed : 500, 
		min : 0,
		max : 100, 

		valueLabel : {
		  display : true, 
		  fontFamily : 'Arial', 
		  fontColor : '#000', 
		  fontSize : 20, 
		  fontWeight : 'normal' 
		},

		title : {
		  display : true, 
		  value : '', 
		  fontFamily : 'Arial', 
		  fontColor : '#000', 
		  fontSize : 20, 
		  fontWeight : 'normal'
		},
		
		label : {
		  display : true, 
		  left : '0', 
		  right : '100',
		  center : '"._("Wind")." km/h',
		  fontFamily : 'Arial', 
		  fontColor : '#000', 
		  fontSize : 12, 
		  fontWeight : 'normal' 
		}	
  });

  $('.vtemp').kumaGauge({
	    value : $grados,	
		radius : 50, 
		paddingX : 40, 
		paddingY : 40,  
		gaugeWidth : 30, 
		fill : '0-#06a8cb:0-#5edffb:20-#eefb5e:50-#fa4133:100', 
		gaugeBackground : '#f4f4f4', 
		background : $( 'body' ).css( 'backgroundColor' ),
		showNeedle : false, 
		animationSpeed : 500, 
		min : -15,
		max : 50, 

		valueLabel : {
		  display : true, 
		  fontFamily : 'Arial', 
		  fontColor : '#000', 
		  fontSize : 20, 
		  fontWeight : 'normal' 
		},

		title : {
		  display : true, 
		  value : '', 
		  fontFamily : 'Arial', 
		  fontColor : '#000', 
		  fontSize : 20, 
		  fontWeight : 'normal'
		},
		
		label : {
		  display : true, 
		  left : '-15', 
		  right : '50',
		  center : '"._("Temperature")." ºC ',
		  fontFamily : 'Arial', 
		  fontColor : '#000', 
		  fontSize : 12, 
		  fontWeight : 'normal' 
		}	
  });  
  ";
  
//  array('ok'=>1,'inic'=>$fi,'end'=>$ff,'h'=>$h,'m'=>$m,'s'=>$s,'total'=>$row['total'])
  if($aInfo["ok"]==1){
	  $ini=0;$fin=10;
	 echo "
  $('.vrain').kumaGauge({
	    value : ".$aInfo["total"].",	
		radius : 50, 
		paddingX : 40, 
		paddingY : 40,  
		gaugeWidth : 30, 
		fill : '0-#06a8cb:0-#5edffb:20-#eefb5e:50-#fa4133:100', 
		gaugeBackground : '#f4f4f4', 
		background : $( 'body' ).css( 'backgroundColor' ),
		showNeedle : false, 
		animationSpeed : 500, 
		min : $ini,
		max : $fin, 

		valueLabel : {
		  display : true, 
		  fontFamily : 'Arial', 
		  fontColor : '#000', 
		  fontSize : 20, 
		  fontWeight : 'normal' 
		},

		title : {
		  display : true, 
		  value : '', 
		  fontFamily : 'Arial', 
		  fontColor : '#000', 
		  fontSize : 20, 
		  fontWeight : 'normal'
		},
		
		label : {
		  display : true, 
		  left : '$ini', 
		  right : '$fin"." l',
		  center : '"._("Rain")." ".$aInfo["h"]."h ".$aInfo["m"]."m ',
		  fontFamily : 'Arial', 
		  fontColor : '#000', 
		  fontSize : 12, 
		  fontWeight : 'normal' 
		}	
  });  
	 
	 "; 
  }
  
  echo "
 $('#sellang').on('change', function(e) {
	var v=$( \"#sellang option:selected\" ).val();
	$('#frmlang').attr(\"action\", '".$_SERVER["PHP_SELF"]."?user=$user&lang='+v+'');
	$('#frmlang').submit();	
  })  
  
	
  $('#frmfechas').submit(function() {
	$v
	
//    alert('.submit() called.');
    return true;
  });
  
  document.title = '"._("Weather Station")."';
});
</script>
";	

}


//---------------------------------------------------------------
function ShowHeader($db,$user,$idioma,$fecha1,$fecha2,$ayer,&$lastrow){
//---------------------------------------------------------------
global $aLang;


	getParametros();
	
@header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
@header('Cache-Control: no-store, no-cache, must-revalidate');
@header('Cache-Control: post-check=0, pre-check=0', FALSE);
@header('Pragma: no-cache');
	
	echo "
<!doctype html>
<html lang=\"es\">
<head>
    <title>Weather Station</title>
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
        <link rel=\"shortcut icon\" href=\"/favicon.ico\" type=\"image/x-icon\">
		<meta charset=\"utf-8\"> 
        <meta http-equiv=\"Content-Type\" content=\"text/html;charset=utf-8\" />
    <link rel=\"stylesheet\" href=\"/jquery/jquery.mobile-1.4.5.min.css\">
    <link rel=\"stylesheet\" href=\"/jquery/jquery.mobile.theme-1.4.5.min.css\">
    <link rel=\"stylesheet\" href=\"/jquery/jquery.mobile.structure-1.4.5.min.css\">
    <link rel=\"stylesheet\" href=\"/jquery/jquery.mobile.icons-1.4.5.min.css\">
	<!-- <link rel=\"stylesheet\" href=\"/jquery/jquery.mobile.external-png-1.4.5.min.css\"> -->
	<link rel=\"stylesheet\" href=\"/jquery/jquery.mobile.inline-png-1.4.5.min.css\">
	
	
    <script src=\"/jquery/jquery-1.12.4.min.js\"></script>
    <script src=\"/jquery/jquery.mobile-1.4.5.min.js\"></script>
	
	<!-- datepicker -->
	<link rel=\"stylesheet\" href=\"/jquery/arschmitz/jquery.mobile.datepicker.css\">
	<link rel=\"stylesheet\" href=\"/jquery/arschmitz/jquery.mobile.datepicker.theme.css\">
	<script src=\"/jquery/arschmitz/external/jquery-ui/datepicker.js\"></script>
    <script id=\"mobile-datepicker\" src=\"/jquery/arschmitz/jquery.mobile.datepicker.js\"></script>
		
	
	<!-- https://www.jqueryscript.net/download/Creating-Animated-Gauges-Using-jQuery-Raphael-js-kumaGauge.zip -->
	<script src=\"//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.2/raphael-min.js\"></script>
	<script src=\"/Gauge/kuma-gauge.jquery.js\"></script>

 <style>\r\n\r\n";	
 foreach ($aLang as $key=>$value){
	 echo ".".$key."-lang {background: url(/locale/".$value["img"].") 5px 50% no-repeat; padding: 3px 0 3px 35px; font-size: 16px;}\r\n";
 }
echo "
 </style>
";

SetJQ($db,$user,$lastrow);	

echo "
</head>
<body>
<div data-role=\"page\" >

        <div data-role=\"header\">";
		
		
		echo "
		    <h1 class=\"ui-btn-left\">  			
			
			<form id=\"frmlang\" name=\"frmlang\" method=\"post\" action=\"".$_SERVER["PHP_SELF"]."?user=$user\" data-ajax=\"false\"> 
			<select id=\"sellang\" name=\"sellang\" data-mini=\"true\" class=\"select-with-images\">\r\n";
 foreach ($aLang as $key=>$value){
			echo "<option value=\"$key\" class=\"".$key."-lang\" ".(strcasecmp($idioma,$key)==0?"selected":"")."> &nbsp;".$value["name"]."</option>\r\n";
 }
		echo "
			</select>
			
			</h1>
			
            <h1 class=\"ui-btn-right\">  			
			</form>
			"._("Weather Station")."&nbsp;</h1>";
		echo "
        </div><!-- /header -->
        <div role=\"main\" class=\"ui-conten jqm-contentt\">";
		echo "<br/><br/><br/>";
		echo "<div class=\"ui-bar\">";
		ShowPerido($user,$fecha1,$fecha2,$idioma,$ayer);
		echo "</div>";

}

//---------------------------------------------------------------
function ShowFooter(){
//---------------------------------------------------------------
  echo "	
        </div><!-- /content -->

        <div data-role=\"footer\">
            <h4><!-- NADA --></h4>
        </div><!-- /footer -->

    </div><!-- /page -->

</body>
</html>";

}

//---------------------------------------------------------------
function ShowActual($db,$user,&$lastrow){
//---------------------------------------------------------------
  $table=$user."_mem";
  $sql="SELECT * FROM $table ORDER by fecha DESC LIMIT 1"; // last data
  if(!($rs=$db->query($sql))) return;
  if($rs->num_rows==0) return;
  $row=$rs->fetch_assoc();
  DBstrToTimestamp($row["fecha"],$mkf);
  $grados=number_format($row["tempc"],2);
  echo "<table border=\"0\"><tr>";
//  echo "<td align=\"center\">&nbsp;&nbsp;&Uacute;ltima&nbsp;&nbsp;<br/>Hora: </td>";
  echo "<td align=\"center\">"._("Last").":<br/>".date("d-m-Y",$mkf)."<br/>".date("H:i:s",$mkf)."</td>";
  echo "<td><div class=\"js-gauge vtemp gauge\"></div></td>";
  if(isMobile()){
	  echo "</tr><tr>";
  }
  echo "<td><div class=\"js-gauge vwind gauge\"></div></td>";	   
  echo "<td><div class=\"js-gauge vrain gauge\"></div></td>";	   
  echo "</tr></table>";
}

function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

?>