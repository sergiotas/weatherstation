<?php

define ("def_graph_width", 400);
define ("def_graph_height", 300);

$ValidUsers=array(array("user"=>"jc",   "inputkey"=>"xxxxxxxxx"),
                  array("user"=>"user2","inputkey"=>"xxxxxxxxx"));
$validusers=array("jc","rbk");
$diferencedays=60; // DIAS DE DIFERENCIA PERMITIDOS A MOSTRAR EN CONSULTA


// Guarda los datos desde la tabla temporal a disco
define("save_each",4*60*60); // 4 horas


$db=@mysqli_connect("127.0.0.1","tiempo","xxxxxxxx","tiempo");
if(!$db){
    echo "Error: No se pudo conectar a MySQL." . "<br/>";
    echo "errno: " . mysqli_connect_errno() . "<br/>";
    echo "error: " . mysqli_connect_error() . "<br/>";
    exit;
}

$appname="weatherstation";
$rutalanguage=__DIR__."/locale";

$idioma="en_US.utf8";
if(isset($_GET["lang"])){
        if(strcasecmp($_GET["lang"],"es")==0){
                $idioma="es";
                @setcookie('idioma','es');
        }
    else @setcookie('idioma','en_US.utf8');
}else{
        if(isset($_COOKIE['idioma']))  if(strcasecmp($_COOKIE['idioma'],"es")==0) $idioma="es";
}
//die($idioma."--3");

if(strcasecmp($idioma,"en_US.utf8")==0){	
        @putenv ("LANG=$idioma");
        @putenv ("LC_ALL=$idioma");
        @setlocale(LC_ALL, $idioma);

        @bindtextdomain($appname, "$rutalanguage");
        @textdomain($appname);
		
}else{ // el setlocale fallaría, se conserva el idioma original
        @putenv ("LANG=$idioma");
        @putenv ("LC_ALL=$idioma");
        @setlocale(LC_ALL, $idioma);

        @bindtextdomain($appname, "$rutalanguage");
        @textdomain($appname);
}

@date_default_timezone_set("Europe/Madrid");

return;



//---------------------------------------------------------------
function YmdToTimeStamp($fecha,&$mkf){ //Y-m-d
//---------------------------------------------------------------
  $fecha=preg_replace( "/\//", "-", $fecha);
  $afecha=explode("-",$fecha);
  $u=count($afecha);
  if($u!=3) return false;
  if(intval($afecha[0])<100) $anyo=2000+intval($afecha[0]); else $anyo=intval($afecha[0]);
  if(!checkdate($afecha[1],$afecha[2],$anyo)) return false;
  $mkf=mktime ( 0, 0, 0, intval($afecha[1])+0, intval($afecha[2])+0, $anyo+0);
  return true;
}

//---------------------------------------------------------------
function dmYToTimeStamp($fecha,&$mkf){ //d-m-Y
//---------------------------------------------------------------
  $fecha=preg_replace( "/\//", "-", $fecha);
  $afecha=explode("-",$fecha);
  $u=count($afecha);
  if($u!=3) return false;
  if(intval($afecha[2])<100) $anyo=2000+intval($afecha[2]); else $anyo=intval($afecha[2]);
  if(!checkdate($afecha[1],$afecha[0],$anyo)) return false;
  $mkf=mktime ( 0, 0, 0, intval($afecha[1])+0, intval($afecha[0])+0, $anyo+0);
  return true;
}

//---------------------------------------------------------------
function strToTimestamp($fecha,&$mkf){ //dd-mm-aa
//---------------------------------------------------------------
  $fecha=preg_replace( "/\//", "-", $fecha);
  $afecha=explode("-",$fecha);
  $u=count($afecha);
  if($u!=3) return false;
  if(intval($afecha[2])<100) $anyo=2000+intval($afecha[2]); else $anyo=intval($afecha[2]);
  if(!checkdate($afecha[1],$afecha[0],$anyo)) return false;
  $mkf=mktime ( 0, 0, 0, intval($afecha[1])+0, intval($afecha[0])+0, $anyo+0);
  return true;
}

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