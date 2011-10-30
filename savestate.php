<?php

$data = stripslashes($_POST["data"]);
$hash = substr(md5($data),0,15);
//$data=gzcompress($data,9);
if(!@file_exists("savedstates/$hash")){
	if(! ($fp = @fopen("savedstates/$hash", 'w'))){
		echo 'cant open';
	}
	if( @fwrite($fp, $data)===FALSE) echo 'write error';
	@fclose($fp);

}
	echo $hash;
?>