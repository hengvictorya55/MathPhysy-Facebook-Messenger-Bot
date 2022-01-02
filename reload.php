<?php
include "db.php";
include "function.php";
$uid = $_GET['uid'];
if(!isset($_POST['amount'])){
$data = [];$data['mid']='';$data['name']='';$data['step']='';$data['finding']='';$data['toAsk']='';$data['requestid']='';$data['pendingteachers']='';$data['chatid']='';$data['price']='';$data['money']='';$data['prerate']='';$data['transactions']="";
$select = actionDB($conn,"select","students",$data,"WHERE uid='".$uid."'");
?>

<html>
	<head>
		<title>Reload Balance | MathPhysy</title>
		<meta charset="UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
		<style>
			@import url('https://fonts.googleapis.com/css?family=Roboto+Slab&display=swap');
			body, html{
				background: #009380;
				z-index:1;
			}
			
			#body{
				position:fixed;
				background: #009380;
				width:100%;
				height:100%;
				top:0;
				left:0;
				z-index:-1;
			}
			
			#name{
				color: white;
				font-family: 'Roboto Slab', serif;
				font-size:30px;
				margin-top:20px;
			}
			#form>.text{
				width:80%;
				max-width:500px;
				font-family: 'Roboto Slab', serif;
				font-size:20px;
				padding:10px;
				margin:20px;
				box-sizing:border-box;
				text-align: center; 
				border:3px solid #00b79f;
				border-radius:10px;
			}
			#form>.submit{
				background:#00b79f;
				width:80%;
				max-width:500px;
				color:white;
				font-family: 'Roboto Slab', serif;
				font-size:20px;
				padding:10px;
				margin:20px;
				box-sizing:border-box;
				text-align: center; 
				border:3px solid #00b79f;
				border-radius:10px;
				cursor: hand;
				margin-top:-2px;
			}
			#loading{
				display:none;
				position:fixed;
				background: #009380;
				width:100%;
				height:100%;
				top:0;
				left:0;
				z-index:2;
			}
			#loading img{
				width: 80%;
				max-width: 150px;
				/* margin: auto; */
				
			}
			.loadwrap{
				height:200px;
				position: absolute;
				top: 0;
				left: 0;
				right: 0;
				bottom: 0;
				margin: auto;
			}
			.reloading{
				color: white;
				font-family: 'Roboto Slab', serif;
				font-size:30px;
			}
			.info{
				display:none;
				color: white;
				font-family: 'Roboto Slab', serif;
				font-size:15px;
				margin-top:-5px;
				margin-bottom:15px;
			}
		</style>
		<script>
			$(document).ready(function(){
				$(".submit").click(function(){
					if(!isNaN($(".text").val()) && $(".text").val()>=1 && $(".text").val()<=500){
						$(".info").hide();
						$("#loading").show();
						$.post(window.location.href,
						{
						  amount: $(".text").val()
						},function(data,status){
							$(".loadingimg").hide();
							$(".tickimg").show();
							$(".reloading").text("DONE!");
						});
					}else{
						$(".info").text("Please enter amount in number only between 1 and 500 (Example: 50)");
						$(".info").show();
					}
				});
			});
		</script>
	</head>
	
	<body>
		<div id="body">
		</div>
		
		<div id="loading">
			<center>
				<div class="loadwrap">
					<div class="reloading">RELOADING...</div>
					<img class="loadingimg"src="loading.gif"/>
					<img class="tickimg" src="tick.png" style="display:none;max-width: 100px;margin-top:20px;"/>
				</div>
			</center>
		</div>
		
		<center>
			<img src="logoh.png" style="width:80%;max-width:500px;"/>
		</center>
		
		<center>
		<div id="name">
			<?=$select[0]['name']?>
		</div>
		</center>
		
		<center>
		<div id="form">
			<input class="text" type="number" name="amount" placeholder="Enter amount to reload"/>
			<div class="submit">RELOAD</div>
			<div class="info"></div>
		</div>
		</center>
		
		<center>
			<img src="payment3.png" style="width:80%;max-width:500px;"/>
		</center>
		
	</body>
	
</html>
<?php
}else{
	$amount = str_replace(",","",number_format($_POST['amount'],2));
	if($amount>=1 && $amount<=500){
		$data = [];$data['mid']='';$data['name']='';$data['step']='';$data['finding']='';$data['toAsk']='';$data['requestid']='';$data['pendingteachers']='';$data['chatid']='';$data['price']='';$data['money']='';$data['prerate']='';$data['transactions']="";
		$select = actionDB($conn,"select","students",$data,"WHERE uid='".$uid."'");
		
		if($select[0]['step']=="3"){
			send($select[0]['mid'],[],"typing_on");
		}
		
		$data = [];
		$data['money'] = $select[0]['money'] + $amount;
		
		$transactions = json_decode($select[0]['transactions'],true);
		$transaction = [];
		$transaction['name'] = "Reload";
		$transaction['id'] = "1";
		$transaction['requestid'] = "0";
		$transaction['time'] = time();
		$transaction['amount'] = "+".$amount;
		array_push($transactions,$transaction);
		$data['transactions'] = json_encode($transactions);
					
		actionDB($conn,"update","students",$data,"WHERE uid='".$uid."'");
		
		if($select[0]['step']=="3"){
			send($select[0]['mid'],textTemplate("You have successfully reloaded $".number_format($amount,2)));
			send($select[0]['mid'],[],"typing_on");
			showStudentMenu($select[0]['mid'],$data['money'],$uid);
		}
	}
}
?>