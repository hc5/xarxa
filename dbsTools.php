<?php
$mysql = new mysqli("courseindex.db","yangzhou","19910728","test");
if (mysqli_connect_errno()) {
	echo  mysqli_connect_error();
	exit();
}
function updateCourseDesc(){
	$desc;
	global $mysql;
	if($result = $mysql->query("select * from course_desc")){
		try{
			while($row = $result->fetch_array(MYSQLI_ASSOC)){
				$desc =trim($row[descr]);
				$id = $row[course_id];

				$name =explode("</h1>",$desc);
				//print $name[]

				if(!strpos($name[0],"</a>")){
					//echo $name[0]."<br>";
					$name = explode("(",$name[0]);
					$name=trim($name[0]);
					$name=trim(strstr(trim(strstr(trim(strstr($name," "))," "))," "));
					if($r = $mysql->query("update courses set name = \"$name\" where course_id = \"$id\"")){}
					else{
						echo $mysql->error."<br>";
					}
					echo($id."<br>");
				}
				else{
					$name = explode("</a>",$name[0]);
					$name = explode("(",$name[1]);
					$name=trim($name[0]);
					if($r = $mysql->query("update courses set name = \"$name\" where course_id = \"$id\"")){}
					else{
						echo $mysql->error."<br>";
					}
					echo($id."<br>");
				}
			}
		}
		catch(Exception $e){
			echo $e->getTraceAsString();
		}
	}
	else{
		echo "<b>$query</b>";
		echo $mysql->error."<br>";
	}

}
//phpinfo();
updateCourseDesc();
?>