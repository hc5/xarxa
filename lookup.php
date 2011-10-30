<?php
include 'f5/xarxa/public/mysql.php';
if($code = $_GET["course"]){
	mysql_query("update courses set popularity = popularity+1 where course_id = \"$code\"");
}
include '/f5/xarxa/public/begin_caching.php';

function getCourseNum($course){
	global $mysql;
	$course = str_replace("-"," ",$course);
	$course = strtoupper($course);
	if($result = $mysql->query("select id from courses where course_id = \"$course\";")){
		$row =  $result->fetch_array(MYSQLI_ASSOC);
		return $row["id"];
	}

	else{
		echo $mysql->error;
		return $course;
	}
}

function getCourseId($id){
	global $mysql;
	$query = "select course_id from courses where id = $id";
	if($result = mysql_query($query)){
		while($row = mysql_fetch_assoc($result)){
			return $row[course_id];
		}
	}
	else{
		echo "<b>$query</b>";
		echo mysql_error($mysql)."<br>";
	}
}
function getPrereqs($id){
	if($id=="")
	return null;
	global $reverse;
	global $mysql;
	$subtree = array();

	$query = "select distinct tail from head_tail where head = $id";
	if($result = mysql_query($query)){
		while($row = mysql_fetch_assoc($result)){

			$prereq = $row[tail];
			$prereqId = getCourseId($prereq);

			$subtree[$prereqId] = getPrereqs($prereq);
		}
	}
	else{
		echo "<b>$query</b>";
		echo mysql_error($mysql)."<br>";
	}
	if(count($subtree)==0){
		$subtree = "No prerequisites for this course";
	}

	return $subtree;
}
function getPrereqText($id){
	$desc;
	global $mysql;
	$query ="select distinct prereq from prereqs_text where course_id = \"".($id)."\";";

	if($result = mysql_query($query)){
		while($row = mysql_fetch_assoc($result))
		{
			$desc =$row[prereq];
		}

	}
	else{
		echo "<b>$query</b>";
		echo mysql_error($mysql)."<br>";
	}
	return $desc;
}
$courseCount = 0;
function getJson($id){
	global $mysql;
	global $courseCount;
	$dataArray = array();
	$courseId = ($id);
	//mysql_query("update courses set popularity = popularity+1 where course_id = \"".($courseId)."\"");
	$dataArray["id"]="node".$courseCount++;
	$dataArray["name"]=format($courseId);
	$data = array();
	//$data["prereq"]=getPrereqText($courseId);
	$data["desc"]=getCourseDesc($id);
	//$data['$orn']="right";
	$dataArray["data"]=$data;
	$dataArray["children"]=getNodeChildren($id);
	foreach (getPostReqs($courseId) as $parent){
		$dataArray["children"][]=$parent;
	}
	return $dataArray;
}
function getCourseDesc($id){
	$desc;
	global $mysql;
	if($result = mysql_query("select distinct descr from course_desc where course_id = \"".($id)."\";")){
		try{
			while($row = mysql_fetch_assoc($result)){
				$desc =trim($row[descr]);
			}
		}
		catch(Exception $e){
			echo $e->getTraceAsString();
		}
	}
	else{
		echo "<b>$query</b>";
		echo mysql_error($mysql)."<br>";
	}
	$desc = preg_replace('/\s+/', ' ', $desc);
	$desc = preg_replace('/\s*<br>\s*/', '<br>', $desc);
	return $desc;

}

function getNodeChildren($id){
	global $mysql;
	global $courseCount;
	$courseId = ($id);
	//echo "<b>".$dataArray["data"]."</b><br>";
	$children = array();
	$query = "select distinct tail from headtail where head = \"$id\"";
	if($result = mysql_query($query)){
		while($row = mysql_fetch_assoc($result)){
			$prereq = $row[tail];

			$childArray = array();
			$childArray["id"]= "node".$courseCount++;

			$childArray["name"]=format($prereq);
			$childData = array();
			$childData['desc']=getCourseDesc($prereq);
			$childData['$orn']="right";
			//echo $childData;
			$childArray["data"]=$childData;

			$childArray["children"]=getNodeChildren($prereq);
			$children[]=$childArray;
		}
	}
	else{
		echo "<b>$query</b>";
		echo mysql_error($mysql)."<br>";
	}
	return $children;
}
$edgeList = array();
function genSalt($max = 15) {
	$characterList = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$i = 0;
	$salt = "";
	do {
		$salt .= $characterList{mt_rand(0,strlen($characterList))};
		$i++;
	} while ($i <= $max);
	return $salt;
}
function getPostReqs($courseId){
	global $mysql;
	global $courseCount;
	$parents = array();
	$query = "select distinct head from headtail where tail = \"$courseId\"";
	if($result = mysql_query($query)){
		while($row = mysql_fetch_assoc($result)){

			$parentId= ($row[head]);
			$parent = array();
			$parent["id"]=genSalt();
			$parent["name"]=($parentId);
			$parentData=array();
			$parentData['desc']=getCourseDesc($parentId);
			$parentData['$orn']="left";
			$parent["data"]=$parentData;
			$parent["children"]=array();
			$parents[]=$parent;
		}
	}
	else{
		echo "<b>$query</b>";
		echo mysql_error($mysql)."<br>";
	}
	return $parents;
}
function format($course_id){
	return strtoupper(str_replace("-"," ",$course_id));
}

if($_GET["test"]){
	$json = getJson((173));
	echo json_encode($json);
}
else{
	$reverse = $_GET["reverse"];
	//echo !$reverse;
	if(!$reverse){

		echo json_encode(getJson(($_GET["course"])));

	}
	else{
		$courseCount = $_GET["nodeCount"];
		echo json_encode(getPostReqs($_GET["course"]));
	}
}
include '/f5/xarxa/public/end_caching.php';
?>