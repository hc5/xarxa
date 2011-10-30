<?php
//phpinfo();
$mysql = new mysqli("localhost","root","19910728","test");
if (mysqli_connect_errno()) {
	echo  mysqli_connect_error();
	exit();
}

function getCourseId($id){
	global $mysql;
	$query = "select course_id from courses where id = $id";
	if($result = $mysql->query($query)){
		while($row = $result->fetch_array(MYSQLI_ASSOC)){
			return $row[course_id];
		}
	}
	else{
		echo "<b>$query</b>";
		echo $mysql->error."<br>";
	}
}
function index($url/*String*/){
	global $mysql;
	$curl = curl_init();
	curl_setopt($curl,CURLOPT_URL,$url);
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
	$resultDoc = curl_exec($curl);
	curl_close($curl);
	//echo $resultDoc;
	$doc = new DOMDocument();
	$doc->loadHTML($resultDoc);
	$links = $doc->getElementsByTagName("a");
	$length = $links->length;



	$courses = array();
	for($i = 0;$i<$length;$i++){
		$node = $links->item($i);
		if(stristr($node->nodeValue,"Credits") ){
			$courses[]=getAttribute($node,"href");
		}
		if(stristr($node->nodeValue,"next") && getAttribute($node,"class")=="active"){
			$nextUrl = "http://www.mcgill.ca".getAttribute($node,"href");
		}
	}
	$query = "insert into urls values(";
	foreach ($courses as $course){
		$query.="null,\"".$course."\",\"null\"),(";
	}
	$query = substr($query,0,-2);
	$query.=";";

	$mysql->query($query);
	echo $nextUrl;
	index($nextUrl);
}
function getAttribute($node,$attribute){
	$att = $node->attributes->getNamedItem($attribute);
	return $att->nodeValue;
}
function getCourseUrls(){
	global $mysql;
	$result = $mysql->query("select * from urls;");
	//$mysql->use_result();
	return $result;


}
function indexCourses($result/*mysqli result object*/){
	global $mysql;
	$curl = curl_init();
	while($row = $result->fetch_array(MYSQLI_ASSOC)){

		$courseId = $row["course_id"];
		$url = $row["url"];
		curl_setopt($curl,CURLOPT_URL,$url);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
		$resultDoc = curl_exec($curl);
		$doc = new DOMDocument();
		$doc->loadHTML($resultDoc);

		$prereqP=$doc->getElementsByTagName("p");
		$length = $prereqP->length;
		$prereqs = array();
		for($i = 0;$i<$length;$i++){
			$node = $prereqP->item($i);
			if(stristr($node->nodeValue,"Prerequisite")){
				$query = "insert into prereqs_text values (\"$courseId\",\"".$node->nodeValue."\");";
				if(!$mysql->query($query)){
					echo "query failed";
				}
			}
		}
	}
	curl_close($curl);
}
function innerHTML($node){
	$doc = new DOMDocument();
	foreach ($node->childNodes as $child)
	$doc->appendChild($doc->importNode($child, true));

	return $doc->saveHTML();
}
function getCourseInfo($result){
	global $mysql;
	$curl = curl_init();
	while($row = $result->fetch_array(MYSQLI_ASSOC)){

		$courseId = $row["course_id"];
		$url = $row["url"];
		curl_setopt($curl,CURLOPT_URL,$url);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
		$resultDoc = curl_exec($curl);
		$doc = new DOMDocument();
		$doc->loadHTML($resultDoc);

		$desc=$doc->getElementById("content-inner");
		$desc= strip_tags(innerHTML($desc),"<br>");
		$desc = explode("\n",$desc);
		$parsed = array();
		foreach ($desc as $line){
			if(strlen(trim($line))==0)
			continue;
			$parsed[]=$line;
		}
		$desc = "";
		foreach ($parsed as $line){
			$desc.= "$line<br>";
		}
		//echo $desc;
		$desc = addslashes(parseDesc($desc));
		//echo $desc;
		$query = "insert into course_desc values (\"$courseId\",\"".$desc."\");";
		if(!$mysql->query($query)){
			echo $query."<br>";
			echo "query failed<br>";
				
		}
	}
	curl_close($curl);
}
function parseDesc($desc){
	$desc =$desc;
	$id = $row[course_id];
	$desc = preg_replace('/\s+/', ' ', $desc);
	$desc = preg_replace('/\s*<br>\s*/', '<br>', $desc);
	$lines = explode("<br>",$desc);
	$count = count($lines);
	$newlines = "";
	for($i=0;$i<$count;$i++){
		$line = $lines[$i];

		if(!strlen($line)){
			continue;
		}
		else if($i==0){
			//echo $line."<br>";
			$line = "<h1>$line</h1>";
			//echo $line;
		}
		else if(stristr($line,"Offered by")||stristr($line,"Administered by")){
			$line = "<h2>$line</h2>";
		}
		else if(stristr($line,"Overview")){
			$line = "<h3>$line</h3>";
		}
		else if(stristr($line,"Terms")){
			$line = "<h4>$line</h4>";
		}
		else if(stristr($line,"Instructors:")){
			$line = "<h5>$line</h5>";
		}
		else if(stristr($line,"Prerequisites:")||stristr($line,"Prerequisite:")||stristr($line,"Corequisite")||stristr($line,"Restriction")){
			$line = "<h6>$line</h6>";
		}
		else{
			$line.="<br>";
		}
		//echo "$line";
		$newlines.=$line;
	}
	return $newlines;
}
function parseInfo(){
	global $mysql;
	$result = $mysql->query("select * from prereqs_text;");
	$i = 0;
	while($row = $result->fetch_array(MYSQLI_ASSOC)){
		$courseId = $row["course_id"];
		$prereqText = $row["prereq"];
		parse($prereqText,$courseId);
	}
}
function parse($text/*string*/,$courseId){
	global $mysql;
	$part =$text;
	$courses = array();
	$matches = array();
	preg_match_all("/[A-Z]+ ([0-9][0-9][0-9][-A-Z0-9]*)/",$part,$matches);
	foreach ($matches as $match){
		foreach($match as $m){
			if(strstr($m," "))
			$courses[]=$m;
		}
	}
	$query = "insert into head_tail values ";
	foreach ($courses as $course){
		$tail = getCourseNum($course);
		if(strlen(trim($tail)))
		$query.="(".getCourseNum($courseId).",".$tail."),";
	}
	$query=substr($query,0,strlen($query)-1);
	if(strstr($query,"(")){
		if($mysql->query($query)){

		}
		else{
			echo "<b>".$query."</b><br>";
			echo $mysql->error."<br>";
		}
	}

}
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
$unfinished = array(
"BIOC-396"
,"ESLN-299"
,"FREN-599"
,"NSCI-396"
,"SURG-301"

);
foreach ($unfinished as $u){
	$result = $mysql->query("select * from urls where course_id = \"$u\"");
	getCourseInfo($result);
}
//getCourseInfo(getCourseUrls());
//index("http://www.mcgill.ca/study/2010-2011/courses/search?page=0");
//indexCourses(getCourseUrls());
//parse("Prerequisites: URBD 611 and URBD 612, or equivalent at MATH 140 and MATH 133 or COMP 250, and permission of instructor.","math-140");
//parseInfo();
?>