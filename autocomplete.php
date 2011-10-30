<?php
include '/f5/xarxa/public/mysql.php';
include '/f5/xarxa/public/begin_caching.php';

function format($course_id){
	return strtoupper(str_replace("-"," ",$course_id));
}

if (!$mysql) {
	die('Could not connect: ' . mysql_error());
}
$text = $_GET["text"];


$query = "select * from courses where course_id like \"".($text)."%\" order by popularity desc limit 20";
//$query = "select * from urls";
//echo $query;
$results = array();
if($result = mysql_query($query)){
	if(mysql_num_rows($result)>0){
		while($row = mysql_fetch_assoc($result)){
			$results[]=($row[course_id])." (".$row[name].")";
		}
	}
	else{
		$text = str_ireplace("-"," ",$text);
		$query = "select * from courses where name like \"%".($text)."%\" order by popularity desc limit 20";
		if($result = mysql_query($query)){
			if(mysql_num_rows($result)>0){
				while($row = mysql_fetch_assoc($result)){
					$results[]=($row[course_id])." (".$row[name].")";
				}
			}
		}
		else{
			echo "<br>$query</br>";
		}
	}
}
else{
	echo "<br>$query</br>";
}
foreach ($results as $r){

	echo "<option>$r</option>";
}
include 'f5/xarxa/public/end_caching.php';
?>