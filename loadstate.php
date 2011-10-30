<?php
$id = $_GET["i"];
$fileName = "savedstates/$id";
if(!@file_exists($fileName)){
	echo "File doesn't exist";	
}
else{
	header('Content-Encoding: gzip');
	print("\x1f\x8b\x08\x00\x00\x00\x00\x00");
	@readfile(($fileName));
}
?>