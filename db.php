<?php 
$servername = "localhost";
$username = "pandeminifier";
$password = "XXXXXXX";
$dbname = "zadmin_pandeminifier";
$conn = new mysqli($servername, $username, $password, $dbname);
function actionDB($conn,$method,$table,$data=[],$condition=""){
	if(strtolower($method)=="add" || strtolower($method)=="insert"){
		$dataTmpKey=[];
		$dataTmpValue=[];
		foreach($data as $key=>$value){
			array_push($dataTmpKey,$key);
			array_push($dataTmpValue,"'".$value."'");
		}
		$sql = "INSERT INTO ".$table." (".join(",",$dataTmpKey).")
		VALUES (".join(",",$dataTmpValue).") ".$condition;
		$add = $conn->query($sql);
		if ($add === TRUE) {
			return true;
			
		} else {
			return false; 
		}
	}
	if(strtolower($method)=="update"){
		$dataTmp = [];
		foreach($data as $key=>$value){
			array_push($dataTmp,$key."='".$value."'");
		}
		$sql = "UPDATE ".$table." SET ".join(",",$dataTmp)." ".$condition;
		if ($conn->query($sql) === TRUE) {
			return true;
		} else {
			return false;
		}
	}
	if(strtolower($method)=="delete"){
		$sql = "DELETE FROM ".$table." ".$condition;
		if ($conn->query($sql) === TRUE) {
			return true;
		} else {
			return false;
		}
	}
	if(strtolower($method)=="select"){
		$dataTmpReturn = [];
		$dataTmpKey=[];
		$dataTmpValue=[];
		foreach($data as $key=>$value){
			array_push($dataTmpKey,$key);
			array_push($dataTmpValue,"'".$value."'");
		}
		$str = join(",",$dataTmpKey);
		if(count($data)==0){
			$str = "*";
		}
		$sql = "SELECT ".$str." FROM ".$table." ".$condition;

		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			while($row = $result->fetch_assoc()) {
				array_push($dataTmpReturn,$row);
			}
			return $dataTmpReturn;
		}else{
			return false;
		}
		
		
	}
}
?>