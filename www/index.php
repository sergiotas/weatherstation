<?php

include "config.php";


//  if(!isset($_GET["usr"])) die("not user");
  if(!isset($_GET["usr"])) $_GET["usr"]="jc";
  $user=substr(trim($_GET["usr"]),0,30);
  

  $existuser=false;
  for($i=0;$i<count($validusers);$i++){
		if($validusers[$i]==$user) {
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
  
  ShowHeader($user,$fecha1,$fecha2,$ayer);
  
  if($fecha1!=""&&$fecha2!=""){
	 $query="usr=$user&d1=".date("Y-m-d",$mkf1)."&d2=".date("Y-m-d",$mkf2)."";
	 echo "<div style=\"margin-left:10px;\"><hr/>Desde ".date("d-m-Y",$mkf1)." hasta ".date("d-m-Y",$mkf2)."<hr/>\r\n";
     echo "<a href=\"graf.php?data&$query&w=tempc\" target=\"_blank\"><img  src=\"graf.php?$query&w=tempc\"/></a><br/>\r\n";
     echo "<a href=\"graf.php?data&$query&w=windspeed\" target=\"_blank\"><img  src=\"graf.php?$query&w=windspeed\"/></a><br/>\r\n";
     echo "<a href=\"graf.php?data&$query&w=rainin\" target=\"_blank\"><img  src=\"graf.php?$query&w=rainin\"/></a><br/>\r\n";
     echo "<a href=\"graf.php?data&$query&w=rainin\" target=\"_blank\"><img  src=\"graf.php?t=sum&$query&w=rainin\"/></a><br/>\r\n";
     echo "<a href=\"graf.php?data&$query&w=pressure\" target=\"_blank\"><img  src=\"graf.php?$query&w=pressure\"/></a><br/>\r\n";
     echo "<a href=\"graf.php?data&$query&w=humidity\" target=\"_blank\"><img  src=\"graf.php?$query&w=humidity\"/></a><br/>\r\n";
     echo "<a href=\"graf.php?data&$query&w=light_lvl\" target=\"_blank\"><img  src=\"graf.php?$query&w=light_lvl\"/></a><br/>\r\n";
     echo "<a href=\"graf.php?data&$query&w=windgustkmh\" target=\"_blank\"><img  src=\"graf.php?$query&w=windgustkmh\"/></a><br/>\r\n";
     echo "<a href=\"graf.php?data&$query&w=windspdkmh_avg\" target=\"_blank\"><img  src=\"graf.php?$query&w=windspdkmh_avg\"/></a><br/>\r\n";
     echo "<a href=\"graf.php?data&$query&w=winddir_avg\" target=\"_blank\"><img  src=\"graf.php?$query&w=winddir_avg\"/></a><br/>\r\n";	 
	 echo "</div>";
  }
	   
  echo ShowFooter();
 
  return;
  
//---------------------------------------------------------------
function ShowPerido($user,$fecha1,$fecha2,$ayer){
//---------------------------------------------------------------
	echo "<form id=\"frmfechas\" name=\"frmfechas\" method=\"post\" enctype=\"multipart/form-data\" action=\"".$_SERVER["PHP_SELF"]."?usr=$user\" data-ajax=\"false\">";
//	echo "<input type=\"hidden\" name=\"sf\" id=\"sf\" />";
	echo "<table \"><tr>".
		"<td style=\"height:25px\">Inic: </td>".
		"<td style=\"height:25px\"><input name=\"txtfecha1\" id=\"txtfecha1\" type=\"datapicker\" value=\"".($fecha1==""?date("Y-m-d",$ayer):$fecha1)."\" style=\"width:110px;\"></td> ".
		"<td style=\"height:25px\"><input type=\"submit\" data-inline=\"true\" value=\"Ir\" data-mini=\"true\"></td>".
		"</tr><tr>".
		"<td style=\"height:25px\">Fin: </td>".
		"<td style=\"height:25px\"><input name=\"txtfecha2\" id=\"txtfecha2\" type=\"datapicker\" value=\"".($fecha2==""?date("Y-m-d",$ayer):$fecha2)."\" style=\"width:110px;\"></td> ".
		"<td>&nbsp;</td>".

		"</tr></table>";
	
	echo "</form>";

}	

//---------------------------------------------------------------
function SetJQ(){
//---------------------------------------------------------------
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
  
});
</script>\r\n
";	

}


//---------------------------------------------------------------
function ShowHeader($user,$fecha1,$fecha2,$ayer){
//---------------------------------------------------------------
	echo "
<!doctype html>
<html>
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
	
	
    <script src=\"/jquery/jquery-1.12.4.min.js\"></script>
    <script src=\"/jquery/jquery.mobile-1.4.5.min.js\"></script>
	
	// datepicker
	<link rel=\"stylesheet\" href=\"/jquery/arschmitz/jquery.mobile.datepicker.css\">
	<link rel=\"stylesheet\" href=\"/jquery/arschmitz/jquery.mobile.datepicker.theme.css\">
	<script src=\"/jquery/arschmitz/external/jquery-ui/datepicker.js\"></script>
    <script id=\"mobile-datepicker\" src=\"/jquery/arschmitz/jquery.mobile.datepicker.js\"></script>
";

SetJQ();	
echo "
</head>
<body>
  <div data-role=\"page\" >

        <div data-role=\"header\">";
		echo "<div class=\"ui-bar\">";
		ShowPerido($user,$fecha1,$fecha2,$ayer);
		echo "</div>";
		echo "
            <h1 class=\"ui-btn-right\">WS&nbsp;</h1>";
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
echo "
        </div><!-- /header -->
        <div role=\"main\" class=\"ui-conten jqm-contentt\">";

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




?>