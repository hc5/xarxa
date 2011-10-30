<?php
//$mysql = mysql_connect("courseindex.db","yangzhou","19910728");
//mysql_select_db("test", $mysql);
$mysql = mysql_connect("localhost","root","19910728");
mysql_select_db("test", $mysql);
$ip=($_SERVER['REMOTE_ADDR']);

//echo $ip;
$r=mysql_query("select ip from log where ip=\"$ip\"");
if(mysql_num_rows($r)==0){
	if(mysql_query("insert into log values(\"$ip\")")){
		
	}
	else{
			
	}
}

return $mysql;
?>