<?php
include "db.php";
include "function.php";
ini_set('error_log', 'log');
$hubVerifyToken = 'handsome';
if ($_REQUEST['hub_verify_token'] === $hubVerifyToken) {
  echo $_REQUEST['hub_challenge'];
  exit;
}

$ref = "";
$message = "";
$postback = "";
$sender="123123";
$answer = textTemplate("hi");
$attachments = [];
if(1==1){
	$input = json_decode(file_get_contents('php://input'), true);
	file_put_contents("b.txt",json_encode($input)); 
	$sender = $input['entry'][0]['messaging'][0]['sender']['id'];
	$message = $input['entry'][0]['messaging'][0]['message']['text'];
	$postback = $input['entry'][0]['messaging'][0]['postback']['payload'];
	if($postback!=""){
		$message = $postback;
	}
	if($input['entry'][0]['messaging'][0]['message']['attachments']!=null){
		$attachments = $input['entry'][0]['messaging'][0]['message']['attachments'];
	}
	if($input['entry'][0]['messaging'][0]['postback']['referral']['ref']!=null){
		$ref = $input['entry'][0]['messaging'][0]['postback']['referral']['ref'];
	}else if($input['entry'][0]['messaging'][0]['referral']['ref']!=null){
		$ref = $input['entry'][0]['messaging'][0]['referral']['ref'];
	}
}





if($message=="iamateacher"){
	send($sender,[],"typing_on");
	actionDB($conn,"delete","students",[],"WHERE mid='".$sender."'");
	actionDB($conn,"delete","people",[],"WHERE mid='".$sender."'");
	$data = [];$data['mid']='';
	if(actionDB($conn,"select","teachers",$data,"WHERE mid='".$sender."'")==false){
		$uid = uniqid();
		$data = [];
		$data['uid'] = $uid;
		$data['name'] = getname($sender);
		$data['mid'] = $sender;
		$data['money'] = "0";
		$data['online'] = "false";
		$data['major'] = "";
		$data['expertise'] = '[]';
		$data['mrate'] = "[]";
		$data['crate'] = "[]";
		$data['transactions'] = "[]";
		$data['step'] = "1";
		actionDB($conn,"insert","teachers",$data);
		$data = [];
		$data['uid'] = $uid;
		$data['mid'] = $sender;
		$data['position'] = 'teacher';
		actionDB($conn,"insert","people",$data);
		$answer = postbackButtonTemplate("Please select your subject:",['Mathematics','Physics']); 
		send($sender,$answer);
	}
}else{
	$data = [];
	$data['mid']='';$data['position']='';
	$peopleSelect = actionDB($conn,"select","people",$data,"WHERE mid='".$sender."'");	
	if($peopleSelect==false){
		send($sender,[],"typing_on");
		$selectRef = false;
		if($ref!=""){
			$data=[];$data['mid']="";$data['money']="";$data['transactions']="";
			$selectRef = actionDB($conn,"select","students",$data,"WHERE mid='".$ref."'");	
		}
		
		$uid = uniqid();
		$data = [];
		$data['uid'] = $uid;
		$myName = getname($sender);
		$data['name'] = $myName;
		$data['mid'] = $sender;
		$data['money'] = "5";
		$data['step'] = "1";
		
		$transactions = json_decode("[]",true);
		$transaction = [];
		$transaction['name'] = "Initial balance";
		$transaction['id'] = "1";
		$transaction['requestid'] = "1";
		$transaction['time'] = time();
		$transaction['amount'] = "+5.00";
		array_push($transactions,$transaction);
		
		if($selectRef!=false){
			$data['money'] = $data['money']+1;
			$transaction = [];
			$transaction['name'] = "Joined through referral";
			$transaction['id'] = "1";
			$transaction['requestid'] = "1";
			$transaction['time'] = time();
			$transaction['amount'] = "+1.00";
			array_push($transactions,$transaction);
		}
		$data['transactions'] = json_encode($transactions);
		
		actionDB($conn,"insert","students",$data);
		$data = [];
		$data['uid'] = $uid;
		$data['mid'] = $sender;
		$data['position'] = 'student';
		actionDB($conn,"insert","people",$data);
		$peopleSelect = actionDB($conn,"select","people",$data,"WHERE mid='".$sender."'");	

		if($selectRef!=false){
			$data = [];
			$data['money'] = $selectRef[0]['money'] + 1;
			$transactions = json_decode($selectRef[0]['transactions'],true);
			$transaction = [];
			$transaction['name'] = $myName." joined through your referral";
			$transaction['id'] = "1";
			$transaction['requestid'] = "1";
			$transaction['time'] = time();
			$transaction['amount'] = "+1.00";
			array_push($transactions,$transaction);
			$data['transactions'] = json_encode($transactions);
			
			actionDB($conn,"update","students",$data,"WHERE mid='".$ref."'");	
		}
		
	}else if($ref!="" && startsWith($ref,"MPGC")==false){
		exit();
	}

	if($peopleSelect[0]['position']=="student"){
		$data = [];$data['uid']='';$data['mid']='';$data['name']='';$data['step']='';$data['finding']='';$data['toAsk']='';$data['requestid']='';$data['pendingteachers']='';$data['chatid']='';$data['price']='';$data['money']='';$data['prerate']='';$data['transactions']="";
		$select = actionDB($conn,"select","students",$data,"WHERE mid='".$sender."'");
		if($select[0]['step']==1){
			send($sender,[],"typing_on");
			$data = [];
			$data['step'] = 2;
			actionDB($conn,"update","students",$data,"WHERE mid='".$sender."'");
			$answer = postbackButtonTemplate("Welcome to MathPhysy, the messenger based online tutoring service for math and physics. With this application, you can request for tutor by firstable sending your question/problem and the application will notify every available tutor and when they accept, they will ask you for a reasonable price.\nBy clicking I UNDERSTAND, you have understood this and join MathPhysy.",['I UNDERSTAND']);
			send($sender,$answer);
		}else if($select[0]['step']==2){
			if($message=="I UNDERSTAND"){
				send($sender,[],"typing_on");
				$data = [];
				$data['step'] = 3;
				actionDB($conn,"update","students",$data,"WHERE mid='".$sender."'");
				$answer = textTemplate("Yayy! You've successfully joined MathPhysy. 😆");
				send($sender,$answer);
				send($sender,[],"typing_on");
				showStudentMenu($sender,$select[0]['money'],$select[0]['uid']);
			}else{
				send($sender,[],"typing_on");
				$answer = postbackButtonTemplate("Welcome to MathPhysy, the messenger based online tutoring service for math and physics. With this application, you can request for tutor by firstable sending your question/problem and the application will notify every available tutor and when they accept, they will ask you for a reasonable price.\nBy clicking I UNDERSTAND, you have understood this and join MathPhysy.",['I UNDERSTAND']);
				send($sender,$answer);
			}
		}else if($select[0]['step']==3){
			if(isset(explode("|",$postback)[1]) && explode("|",$postback)[1]=="finding"){
				send($sender,[],"typing_on");
				if($select[0]['money']>0){
					$data = [];
					$data['finding'] = explode("|",$postback)[0];
					$data['toAsk'] = "[]";
					$data['pendingteachers']="[]";
					$data['step'] = 4;
					actionDB($conn,"update","students",$data,"WHERE mid='".$sender."'");
					$answer = postbackButtonTemplate("Please send your question and/or include pictures, voices, etc. Click DONE when you're done.",['DONE','CANCEL']);
					send($sender,$answer);
				}else{
					send($sender,textTemplate("Sorry, you need to have at least some balance to request for tutor❗️"));
					send($sender,[],"typing_on");
					showStudentMenu($sender,$select[0]['money'],$select[0]['uid']);
				}
			}else if($message=="View Transactions"){
				send($sender,[],"typing_on");
				$text = "Your transactions:\n\n";
				$transactions = json_decode($select[0]['transactions'], true);
				foreach($transactions as $transaction){
					$text.= date('m/d/Y', $transaction['time']).": ".str_replace("-","-$",str_replace("+","+$",$transaction['amount']))." (".$transaction['name'].")\n";
				}
				if(count($transactions)==0){
					$text = "You have no transaction.";
				}
				send($sender,textTemplate(rawurldecode($text)));
			}else if($message=="Refer Friends"){
				send($sender,[],"typing_on");
				send($sender,textTemplate("When your friends join through your referral, both of you will get $1 each to use MathPhysy. Sound good?"));
				send($sender,[],"typing_on");
				send($sender,textTemplate("Send or forward this to your friends and let them join through this link:\nhttp://m.me/MathPhysy?ref=".$sender));
				send($sender,[],"typing_on");
				send($sender,textTemplate("Or let them scan the code below:"));
				send($sender,[],"typing_on");
				$answer = [];
				$answer['attachment']['type'] = "image";
				$answer['attachment']['payload']['url'] = generateQRRefImage($sender);
				$answer['attachment']['payload']['is_reusable'] = 'true';
				send($sender,$answer);
			}else if($message=="Buy Gift Card"){
				send($sender,[],"typing_on");
				$data = [];
				$data['step'] = "8.1";
				actionDB($conn,"update","students",$data,"WHERE mid='".$sender."'");
				$answer = quickReplyTemplate("What gift card do you want?",["CANCEL","$10","$25","$50","$100"]);
				send($sender,$answer);
			}else if(startsWith($ref,"MPGC") || startsWith(strtoupper($message),"MPGC")){
				if(startsWith(strtoupper($message),"MPGC")){
					$ref = str_replace(" ","",strtoupper($message));
				}
				$data = [];$data['code']="";$data['amount']="";
				$selectCard = actionDB($conn,"select","giftcards",$data,"WHERE code='".$ref."'");
				if($selectCard!=false){
					send($sender,[],"typing_on");
					$data = [];
					$data['money'] = $select[0]['money'] + $selectCard[0]['amount'];
					$transactions = json_decode($select[0]['transactions'],true);
					$transaction = [];
					$transaction['name'] = "Redeem $".$selectCard[0]['amount']." from gift card";
					$transaction['id'] = "1";
					$transaction['requestid'] = "1";
					$transaction['time'] = time();
					$transaction['amount'] = "+".$selectCard[0]['amount'].".00";
					array_push($transactions,$transaction);
					$data['transactions'] = json_encode($transactions);
					actionDB($conn,"update","students",$data,"WHERE mid='".$sender."'");
					
					actionDB($conn,"delete","giftcards",[],"WHERE code='".$ref."'");
					send($sender,textTemplate("You successfully redeem $".number_format($selectCard[0]['amount'],2)." from gift card"));
					send($sender,[],"typing_on");
					showStudentMenu($sender,$select[0]['money']+$selectCard[0]['amount'],$select[0]['uid']);
				}else{
					send($sender,[],"typing_on");
					send($sender,textTemplate("Sorry, this gift card is not in the system or was already used❗️"));
					send($sender,[],"typing_on");
					showStudentMenu($sender,$select[0]['money']+$selectCard[0]['amount'],$select[0]['uid']);
				}
			}else{
				send($sender,[],"typing_on");
				showStudentMenu($sender,$select[0]['money'],$select[0]['uid']);
			}
		}else if($select[0]['step']==4){
			if($postback=="DONE"){
				$json = $select[0]['toAsk'];
				$array = json_decode($json, true);
				if(count($array)>0){
					send($sender,[],"typing_on");
					$uid = uniqid();
					$data = [];
					$data['step'] = 5;
					$data['requestid'] = $uid;
					actionDB($conn,"update","students",$data,"WHERE mid='".$sender."'");
					send($sender,postbackButtonTemplate("Finding teachers...Please wait...",['CANCEL']));
					
					$data = [];$data['online']='';$data['major']='';$data['mid']='';
					$selectTeacher = actionDB($conn,"select","teachers",$data,"WHERE online='true' AND step='3' AND (major='".$select[0]['finding']."')");				
					if($selectTeacher!=false){
						//for($i=0;$i<count($array);$i++){
						//	$att = $array[$i];
						//	if($att['type']!="text"){
						//		$array[$i]['value'] = saveAttachment($att['value'],$att['type'])['attachment_id'];
						//	}
						//}
						//
						//foreach($selectTeacher as $teacher){
						//	send($teacher['mid'],[],"typing_on");	
						//	send($teacher['mid'],textTemplate("❓".$select[0]['name']."'s request:"));
						//	foreach($array as $att){
						//		send($teacher['mid'],[],"typing_on");	
						//		$answer = [];
						//		if($att['type']=="text"){
						//			$answer = textTemplate(rawurldecode($att['value']));
						//		}else{
						//			$answer['attachment']['type'] = $att['type'];
						//			$answer['attachment']['payload']['attachment_id'] = $att['value'];
						//		}
						//		send($teacher['mid'],$answer);
						//	}
						//	
						//	send($teacher['mid'],[],"typing_on");	
						//	$answer = postbackButtonTemplate("Would you like to accept this tutoring request?",['ACCEPT|'.$sender.'|'.$uid]);
						//	send($teacher['mid'],$answer);
						//}

						foreach($selectTeacher as $teacher){
							send($teacher['mid'],[],"typing_on");	
							$answer = postbackButtonTemplate("❓".$select[0]['name']."'s request:",['VIEW|https://mathphysy.com/request.php?rid='.$uid.'|web','ACCEPT|'.$sender.'|'.$uid]);
							send($teacher['mid'],$answer);							
						}
					}else{
						send($sender,[],"typing_on");
						clearStudent2Step3($conn,$sender);
						send($sender,textTemplate("Sorry, there is no teacher available❗️"));
						send($sender,[],"typing_on");
						showStudentMenu($sender,$select[0]['money'],$select[0]['uid']);
					}
				}else{
					send($sender,[],"typing_on");
					send($sender,textTemplate("Please send something you would like to get help first before clicking DONE❗️"));
				}
			}else if($postback=="CANCEL"){
				send($sender,[],"typing_on");
				clearStudent2Step3($conn,$sender);
				send($sender,textTemplate("You have canceled your task❗️"));
				send($sender,[],"typing_on");
				showStudentMenu($sender,$select[0]['money'],$select[0]['uid']);
			}else{
				$json = $select[0]['toAsk'];
				$array = json_decode($json, true);
				if(count($attachments)>0){
					for($i=0;$i<count($attachments);$i++){
						$toPush = [];
						$toPush['type'] = $attachments[$i]['type'];
						$toPush['value'] = $attachments[$i]['payload']['url'];
						array_push($array,$toPush);
					}
				}else if($message!=""){
					$toPush = [];
					$toPush['type'] = 'text';
					$toPush['value'] = rawurlencode($message);
					array_push($array,$toPush);
				}
				$json = json_encode($array);
				$data = [];
				$data['toAsk'] = $json;
				actionDB($conn,"update","students",$data,"WHERE mid='".$sender."'");
				send($sender,[],"mark_seen");
			}
		}else if($select[0]['step']==5){
			if(startsWith($postback,"ACCEPT|")){
				$teacher = explode("|",$postback)[1];
				send($sender,[],"typing_on");
				send($teacher,[],"typing_on");
				if($select[0]['money'] >= explode("|",$postback)[2]){
					$data = [];$data['mid']='';$data['step']='';$data['expertise']='';$data['online']='';$data['pendingid']='';$data['pendingrequestid']='';$data['askedmoney']='';$data['chatid']='';
					$teacherSQL = actionDB($conn,"select","teachers",$data,"WHERE mid='".$teacher."' AND askedmoney='true' AND step='4' AND pendingrequestid='".$select[0]['requestid']."'");
					if($teacherSQL!=false){
						$pendingTeachers = json_decode($select[0]['pendingteachers'], true);
						foreach($pendingTeachers as $pendingTeacher){
							if($pendingTeacher!=$teacher){
								send($pendingTeacher,[],"typing_on");
								send($pendingTeacher,textTemplate("Sorry! this student has found their teacher."));
								clearTeacher2Step3($conn,$pendingTeacher);
							}
						}
						
						//START CONVERSATION
						send($teacher,textTemplate($select[0]['name']." accepts your offer."));
						send($teacher,[],"typing_on");
						send($teacher,postbackButtonTemplate("Conversation starts now. Send \"FINISH\" or click on the FINISH button to finish the thread.",['FINISH']));
						send($sender,postbackButtonTemplate("Conversation starts now. Send \"FINISH\" or click on the FINISH button to finish the thread.",['FINISH']));
						$data = [];
						$data['step'] = 6;
						$data['chatid'] = $teacher;
						$data['price'] = number_format(explode("|",$postback)[2],2);
						actionDB($conn,"update","students",$data,"WHERE mid='".$sender."'");
						$data = [];
						$data['step'] = 5;
						$data['online'] = 'false';
						$data['chatid'] = $sender;
						$data['price'] = number_format(explode("|",$postback)[2],2); 
						actionDB($conn,"update","teachers",$data,"WHERE mid='".$teacher."'");
					}else{
						send($sender,textTemplate("Sorry, this teacher has canceled his/her request❗️"));
					}
				}else{
					send($sender,textTemplate("Sorry, you have insufficient balance️❗️"));
					$teacher = explode("|",$postback)[1];
					$data = [];$data['mid']='';$data['step']='';$data['expertise']='';$data['online']='';$data['pendingid']='';$data['pendingrequestid']='';$data['askedmoney']='';$data['chatid']='';
					$teacherSQL = actionDB($conn,"select","teachers",$data,"WHERE mid='".$teacher."' AND askedmoney='true' AND step='4' AND pendingrequestid='".$select[0]['requestid']."'");
					if($teacherSQL!=false){
						send($teacher,textTemplate("Sorry, this student has declined your request❗️"));
						clearTeacher2Step3($conn,$teacher);
						//take out this teacher from pendingteachers array db DONE!
						$pendingTeachers = json_decode($select[0]['pendingteachers'], true);
						unset($pendingTeachers[array_search($teacher,$pendingTeachers)]);
						$data = [];
						$data['pendingteachers'] = json_encode($pendingTeachers);
						actionDB($conn,"update","students",$data,"WHERE mid='".$sender."'");
					}
				}
			}else if(startsWith($postback,"DECLINE|")){
				$teacher = explode("|",$postback)[1];
				send($teacher,[],"typing_on");
				send($sender,"[]","mark_seen");
				$data = [];$data['mid']='';$data['step']='';$data['expertise']='';$data['online']='';$data['pendingid']='';$data['pendingrequestid']='';$data['askedmoney']='';$data['chatid']='';
				$teacherSQL = actionDB($conn,"select","teachers",$data,"WHERE mid='".$teacher."' AND askedmoney='true' AND step='4' AND pendingrequestid='".$select[0]['requestid']."'");
				if($teacherSQL!=false){
					send($teacher,textTemplate("Sorry, this student has declined your request❗️"));
					clearTeacher2Step3($conn,$teacher);
					//take out this teacher from pendingteachers array db DONE!
					$pendingTeachers = json_decode($select[0]['pendingteachers'], true);
					unset($pendingTeachers[array_search($teacher,$pendingTeachers)]);
					$data = [];
					$data['pendingteachers'] = json_encode($pendingTeachers);
					actionDB($conn,"update","students",$data,"WHERE mid='".$sender."'");
				}
			}else if($message=="CANCEL"){
				send($sender,[],"typing_on");
				clearStudent2Step3($conn,$sender);
				send($sender,textTemplate("You have canceled your task❗️"));
				send($sender,[],"typing_on");
				showStudentMenu($sender,$select[0]['money'],$select[0]['uid']);
				
				//SEND BACK to kru na del request with yerng
				$pendingTeachers = json_decode($select[0]['pendingteachers'], true);
				foreach($pendingTeachers as $pendingTeacher){
					$data = [];$data['mid']='';$data['step']='';$data['expertise']='';$data['online']='';$data['pendingid']='';$data['pendingrequestid']='';$data['askedmoney']='';$data['chatid']='';
					$teacherSQL = actionDB($conn,"select","teachers",$data,"WHERE mid='".$pendingTeacher."' AND askedmoney='true' AND step='4' AND pendingrequestid='".$select[0]['requestid']."'");
					if($teacherSQL!=false){
						send($pendingTeacher,[],"typing_on");
						send($pendingTeacher,textTemplate("Sorry, this student has canceled their request❗️"));
						clearTeacher2Step3($conn,$pendingTeacher);
					}
				}
			}
		}else if($select[0]['step']==6){
			$teacher = $select[0]['chatid'];
			if($message!="FINISH"){
				send($sender,[],"mark_seen");
				if(count($attachments)>0){
					for($i=0;$i<count($attachments);$i++){
						$answer = [];
						$answer['attachment']['type'] = $attachments[$i]['type'];
						$answer['attachment']['payload']['url'] = $attachments[$i]['payload']['url'];
						$answer['attachment']['payload']['is_reusable'] = 'true';
						send($teacher,$answer);
					} 
				}else{
					send($teacher,textTemplate($message));
				}
			}else{
				$requestID = $select[0]['requestid'];
				send($sender,[],"typing_on");
				send($teacher,[],"typing_on");
				
				send($sender,textTemplate("This thread has finished. You've paid $".$select[0]['price']));
				send($teacher,textTemplate("This thread has finished. You've recieved $".$select[0]['price']));
				
				send($sender,[],"typing_on");
				send($teacher,[],"typing_on");
				
				$selectTeacher = actionDB($conn,"select","teachers",["money"=>"","name"=>"","transactions"=>""],"WHERE mid='".$teacher."'");
				$data = [];
				$data['money'] = $select[0]['money'] - $select[0]['price'];
				$studentMoney = $data['money'];
				
				$transactions = json_decode($select[0]['transactions'],true);
				$transaction = [];
				$transaction['name'] = $selectTeacher[0]['name']." - ".$select[0]['finding'];
				$transaction['id'] = $teacher;
				$transaction['requestid'] = $select[0]['requestid'];
				$transaction['time'] = time();
				$transaction['amount'] = "-".$select[0]['price'];
				array_push($transactions,$transaction);
				$data['transactions'] = json_encode($transactions);
				
				actionDB($conn,"update","students",$data,"WHERE mid='".$sender."'");
				clearStudent2Step3($conn,$sender);
				
				$data = [];
				$data['money'] = $selectTeacher[0]['money'] + $select[0]['price'];
				$teacherMoney = $data['money'];
				
				$transactions = json_decode($selectTeacher[0]['transactions'],true);
				$transaction = [];
				$transaction['name'] = $select[0]['name'];
				$transaction['id'] = $sender;
				$transaction['requestid'] = $select[0]['requestid'];
				$transaction['time'] = time();
				$transaction['amount'] = "+".$select[0]['price'];
				array_push($transactions,$transaction);
				$data['transactions'] = json_encode($transactions);
				
				actionDB($conn,"update","teachers",$data,"WHERE mid='".$teacher."'");
				clearTeacher2Step3($conn,$teacher);
				
				showTeacherMenu($teacher,"true",$teacherMoney,$select[0]['crate']);
				//showStudentMenu($sender,$studentMoney);
				
				$data = [];
				$data['step']="7.1";
				$data['requestid']=$requestID;
				$data['chatid']=$teacher;
				actionDB($conn,"update","students",$data,"WHERE mid='".$sender."'");
				
				send($sender,quickReplyTemplate("Please rate this teacher in term of price (1-5):",['1','2','3','4','5']));
			}
		}else if($select[0]['step']=="7.1"){
			send($sender,[],"typing_on");
			if(is_numeric($message) && $message>=1 && $message<=5){
				$array = json_decode($select[0]['prerate'],true);
				$thisRate = [];
				$thisRate['studentId'] = $sender;
				$thisRate['name'] = $select[0]['name'];
				$thisRate['threadId'] = $select[0]['requestid'];
				$thisRate['time'] = time();
				$thisRate['rate']['price'] = $message;
				
				$data = [];
				$data['prerate'] = json_encode($thisRate);
				$data['step'] = "7.2";
				actionDB($conn,"update","students",$data,"WHERE mid='".$sender."'");
				send($sender,quickReplyTemplate("Please rate this teacher in term of quality (1-5):",['1','2','3','4','5']));
			}else{
				send($sender,quickReplyTemplate("Please rate this teacher in term of price (1-5):",['1','2','3','4','5']));
			}
		}else if($select[0]['step']=="7.2"){
			send($sender,[],"typing_on");
			if(is_numeric($message) && $message>=1 && $message<=5){
				$thisRate = json_decode($select[0]['prerate'],true);
				$thisRate['rate']['quality'] = $message;
				
				$data = [];
				$data['prerate'] = json_encode($thisRate);
				$data['step'] = "7.3";
				actionDB($conn,"update","students",$data,"WHERE mid='".$sender."'");
				send($sender,quickReplyTemplate("Please rate this teacher in term of response time (1-5):",['1','2','3','4','5']));
			}else{
				send($sender,quickReplyTemplate("Please rate this teacher in term of quality (1-5):",['1','2','3','4','5']));
			}
		}else if($select[0]['step']=="7.3"){
			send($sender,[],"typing_on");
			if(is_numeric($message) && $message>=1 && $message<=5){
				$thisRate = json_decode($select[0]['prerate'],true);
				$thisRate['rate']['time'] = $message;
				
				$data = [];$data['crate']='';$data['mid']='';
				$selectTeacher = actionDB($conn,"select","teachers",$data,"WHERE mid='".$select[0]['chatid']."'");				
				$array = json_decode($selectTeacher[0]['crate'], true);
				array_push($array,$thisRate);
				
				$data = [];
				$data['crate'] = json_encode($array); 
				actionDB($conn,"update","teachers",$data,"WHERE mid='".$select[0]['chatid']."'");
				
				send($sender,textTemplate("You're done rating."));
				send($sender,[],"typing_on");
				clearStudent2Step3($conn,$sender);
				showStudentMenu($sender,$select[0]['money'],$select[0]['uid']);
			}else{
				send($sender,quickReplyTemplate("Please rate this teacher in term of response time (1-5):",['1','2','3','4','5']));
			}
		}else if($select[0]['step']=="8.1"){
			if($message=="CANCEL"){
				send($sender,[],"typing_on");
				clearStudent2Step3($conn,$sender);
				showStudentMenu($sender,$select[0]['money'],$select[0]['uid']);
			}else{
				if($message=="$10" || $message=="$25" || $message=="$50" || $message=="$100"){
					$price = str_replace("$","",$message);
					if($price>$select[0]['money']){
						send($sender,[],"typing_on");
						send($sender,textTemplate("Sorry, you don't have enough balance to purchase this gift card❗️"));
						send($sender,[],"typing_on");
						clearStudent2Step3($conn,$sender);
						showStudentMenu($sender,$select[0]['money'],$select[0]['uid']);
					}else{
						send($sender,[],"typing_on");
						$data = [];
						$data['money'] = $select[0]['money'] - $price;
						$transactions = json_decode($select[0]['transactions'],true);
						$transaction = [];
						$transaction['name'] = "Bought $".$price." gift card";
						$transaction['id'] = "1";
						$transaction['requestid'] = "1";
						$transaction['time'] = time();
						$transaction['amount'] = "-".$price.".00";
						array_push($transactions,$transaction);
						$data['transactions'] = json_encode($transactions);
						actionDB($conn,"update","students",$data,"WHERE mid='".$sender."'");
						clearStudent2Step3($conn,$sender);
						
						send($sender,textTemplate("Below is your virtual gift card:"));
						$urlCard = generateGiftCard($conn,$price);
						send($sender,[],"typing_on");
						$answer = [];
						$answer['attachment']['type'] = "image";
						$answer['attachment']['payload']['url'] = $urlCard;
						$answer['attachment']['payload']['is_reusable'] = 'true';
						send($sender,$answer);
					}
				}else{
					send($sender,[],"typing_on");
					$answer = quickReplyTemplate("What gift card do you want?",["CANCEL","$10","$25","$50","$100"]);
					send($sender,$answer);
				}
			}
		}
	////////////////////////TEACHER//////////////////////////// 
	////////////////////////TEACHER////////////////////////////
	////////////////////////TEACHER////////////////////////////
	////////////////////////TEACHER////////////////////////////
	////////////////////////TEACHER////////////////////////////
	////////////////////////TEACHER////////////////////////////
	////////////////////////TEACHER////////////////////////////
	////////////////////////TEACHER////////////////////////////
	////////////////////////TEACHER////////////////////////////
	////////////////////////TEACHER////////////////////////////
	////////////////////////TEACHER////////////////////////////
	}else if($peopleSelect[0]['position']=="teacher"){
		$data = [];$data['mid']='';$data['name']='';$data['crate']='';$data['step']='';$data['expertise']='';$data['online']='';$data['pendingid']='';$data['pendingrequestid']='';$data['askedmoney']='';$data['chatid']='';$data['price']='';$data['money']='';$data['transactions']="";
		$select = actionDB($conn,"select","teachers",$data,"WHERE mid='".$sender."'");
		if($select[0]['step']==1){
			send($sender,[],"typing_on");
			//SELECTING MAJOR
			$answer['text']="sddd".$message;
			if($message == "Mathematics"){
				$data = [];
				$data['step'] = 2;
				$data['major'] = "Mathematics";
				$data['step'] = 3; // skip step 2
				$data['online'] = 'true'; // skip step2
				actionDB($conn,"update","teachers",$data,"WHERE mid='".$sender."'");
				$answer = textTemplate("Yay! You are done setting up.");
				send($sender,$answer);
				send($sender,[],"typing_on");
				showTeacherMenu($sender,"true",$select[0]['money'],$select[0]['crate']);
				//$answer = textTemplate("Please select your expertise areas in mathematics:");
				//send($sender,$answer);
				//$answer = postbackButtonTemplate("Select:",['Basic Algebra','Intermedia Algebra','Calculus I']);
				//send($sender,$answer);
				//$answer = postbackButtonTemplate("Select:",['Calculus II','Calculus III','Linear Algebra']);
				//send($sender,$answer);
				//$answer = postbackButtonTemplate("Click when your done:",['Done']);
				//send($sender,$answer);
			}else if($message == "Physics"){
				$data = [];
				$data['step'] = 2;
				$data['major'] = "Physics";
				$data['step'] = 3; // skip step 2
				$data['online'] = 'true'; // skip step2
				actionDB($conn,"update","teachers",$data,"WHERE mid='".$sender."'");
				$answer = textTemplate("Yay! You are done setting up.");
				send($sender,$answer);
				send($sender,[],"typing_on");
				showTeacherMenu($sender,"true",$select[0]['money'],$select[0]['crate']);
			}else{
				$answer = postbackButtonTemplate("Please select your subject:",['Mathematics','Physics']); 
				send($sender,$answer);
			}
		}else if($select[0]['step']==2){
			$expertise = json_decode($select[0]['expertise']);
			if(in_array($message,['Basic Algebra','Intermedia Algebra','Calculus I','Calculus II','Calculus III','Linear Algebra'])){
				if(!in_array($message,$expertise)){
					array_push($expertise,$message);
					$json = json_encode($expertise);
					$data = [];
					$data['expertise'] = $json;
					actionDB($conn,"update","teachers",$data,"WHERE mid='".$sender."'");
				}
				send($sender,[],"mark_seen");
			}else if($message == "Done"){
				$data = [];
				$data['step'] = 3;
				$data['online'] = 'true';
				actionDB($conn,"update","teachers",$data,"WHERE mid='".$sender."'");
				//SENDING MENU
				$answer = textTemplate("Yay! You are done setting up.");
				send($sender,$answer);
			}
		}else if($select[0]['step']==3){
			if(startsWith($postback,"ACCEPT|")){
				send($sender,[],"typing_on");
				$student = explode("|",$postback)[1];
				$data = [];$data['mid']='';$data['step']='';$data['requestid']='';
				$studentSQL = actionDB($conn,"select","students",$data,"WHERE mid='".$student."' AND step='5' AND requestid='".explode("|",$postback)[2]."'");
				if($studentSQL!=false){
					send($sender,postbackButtonTemplate("Please enter amount you want for this request:",['CANCEL']));
					$data = [];
					$data['online'] = 'false';
					$data['step'] = 4;
					$data['pendingid'] = $student;
					$data['pendingrequestid'] = explode("|",$postback)[2];
					actionDB($conn,"update","teachers",$data,"WHERE mid='".$sender."'");
				}else{
					send($sender,textTemplate("Sorry, this request has been canceled or handled️❗️"));
				}
			}else if($message=="View Transactions"){
				send($sender,[],"typing_on");
				$text = "Your transactions:\n\n";
				$transactions = json_decode($select[0]['transactions'], true);
				foreach($transactions as $transaction){
					$text.= date('m/d/Y', $transaction['time']).": ".str_replace("-","-$",str_replace("+","+$",$transaction['amount']))." (".$transaction['name'].")\n";
				}
				if(count($transactions)==0){
					$text = "You have no transaction.";
				}
				send($sender,textTemplate(rawurldecode($text)));
			}else if($message=="View Rating"){
				send($sender,[],"typing_on");
				$text = "Rating:\n\n";
				$crate = json_decode($select[0]['crate'],true);
				foreach($crate as $rate){
					$text.= date('m/d/Y', $rate['time'])."[".$rate['name']."]: Price(".$rate['rate']['price']."), Quality(".$rate['rate']['quality']."), Response Time(".$rate['rate']['time'].")\n";
				}
				if(count($crate)==0){
					$text = "There is no rate yet.";
				}
				send($sender,textTemplate(rawurldecode($text)));
			}else{
				send($sender,[],"typing_on");
				$isOnline = $select[0]['online'];
				if($message == "Go OFFLINE"){
					$data = [];
					$data['online'] = 'false';
					actionDB($conn,"update","teachers",$data,"WHERE mid='".$sender."'");
					$isOnline = false;
				}else if($message == "Go ONLINE"){
					$data = [];
					$data['online'] = 'true';
					actionDB($conn,"update","teachers",$data,"WHERE mid='".$sender."'");
					$isOnline = true;
				}
				showTeacherMenu($sender,$isOnline,$select[0]['money'],$select[0]['crate']);
			}
		}else if($select[0]['step']==4){
			send($sender,[],"typing_on");
			$student = $select[0]['pendingid'];
			$data = [];$data['mid']='';$data['step']='';$data['requestid']='';$data['pendingteachers']='';
			$studentSQL = actionDB($conn,"select","students",$data,"WHERE mid='".$student."' AND step='5' AND requestid='".$select[0]['pendingrequestid']."'");
			if($select[0]['askedmoney']!="true"){ 
				if($studentSQL!=false){
					if(is_numeric($message)){
						send($student,[],"typing_on");
						send($sender,[],"typing_on");
						$data = [];
						$data['online'] = 'true';
						$data['askedmoney'] = "true";
						actionDB($conn,"update","teachers",$data,"WHERE mid='".$sender."'");
						
						$arrayStudentPendingTeachers = json_decode($studentSQL[0]['pendingteachers'],true);
						array_push($arrayStudentPendingTeachers, $sender);
						$data = [];
						$data['pendingteachers'] = json_encode($arrayStudentPendingTeachers);
						actionDB($conn,"update","students",$data,"WHERE mid='".$student."'");
						
						send($student,postbackButtonTemplate("❓".$select[0]['name']." wants to offer your request for 【$".number_format($message,2)."】",['ACCEPT|'.$sender.'|'.$message,'DECLINE|'.$sender,'CANCEL']));
						send($sender,postbackButtonTemplate("Waiting for their reply...",['CANCEL'])); // this should be change in case someone send again
					}else if($message=="CANCEL"){// maybe button or not or other words or lower case..?
						send($sender,[],"typing_on");
						clearTeacher2Step3($conn,$sender);
						send($sender,textTemplate("You have canceled this request❗️"));
					}else{
						send($sender,[],"typing_on");
						send($sender,textTemplate("Please enter number only❗️ (Example: 2.50)"));
					}
				}else{
					send($sender,[],"typing_on");
					clearTeacher2Step3($conn,$sender);
					send($sender,textTemplate("Sorry, this request has been canceled or handled❗️"));
				}
			}else if($message=="CANCEL"){
				send($sender,[],"typing_on");
				clearTeacher2Step3($conn,$sender);
				send($sender,textTemplate("You have canceled your request❗️"));
			}
		}else if($select[0]['step']==5){
			$student = $select[0]['chatid'];
			if($message!="FINISH"){
				send($sender,[],"mark_seen");
				if(count($attachments)>0){
					for($i=0;$i<count($attachments);$i++){
						$answer = [];
						$answer['attachment']['type'] = $attachments[$i]['type'];
						$answer['attachment']['payload']['url'] = $attachments[$i]['payload']['url'];
						$answer['attachment']['payload']['is_reusable'] = 'true';
						send($student,$answer);
					} 
				}else{
					send($student,textTemplate($message));
				}
			}else{
				send($sender,[],"typing_on");
				send($student,[],"typing_on");
				
				send($sender,textTemplate("This thread has finished. You've recieved $".$select[0]['price']));
				send($student,textTemplate("This thread has finished. You've paid $".$select[0]['price']));
				
				send($sender,[],"typing_on");
				send($student,[],"typing_on");
				
				$selectStudent = actionDB($conn,"select","students",["money"=>"","name"=>"","transactions"=>""],"WHERE mid='".$student."'");
				
				$data = [];
				$data['money'] = $select[0]['money'] + $select[0]['price'];
				$teacherMoney = $data['money'];
				
				$transactions = json_decode($select[0]['transactions'],true);
				$transaction = [];
				$transaction['name'] = $selectStudent[0]['name'];
				$transaction['id'] = $student;
				$transaction['requestid'] = $select[0]['pendingrequestid'];
				$transaction['time'] = time();
				$transaction['amount'] = "+".$select[0]['price'];
				array_push($transactions,$transaction);
				$data['transactions'] = json_encode($transactions);
				actionDB($conn,"update","teachers",$data,"WHERE mid='".$sender."'");
				clearTeacher2Step3($conn,$sender);
				
				$data = [];
				$data['money'] = $selectStudent[0]['money'] - $select[0]['price'];
				$studentMoney = $data['money'];
				
				$transactions = json_decode($selectStudent[0]['transactions'],true);
				$transaction = [];
				$transaction['name'] = $select[0]['name']." - ".$selectStudent[0]['finding'];
				$transaction['id'] = $sender;
				$transaction['requestid'] = $select[0]['pendingrequestid'];
				$transaction['time'] = time();
				$transaction['amount'] = "-".$select[0]['price'];
				array_push($transactions,$transaction);
				$data['transactions'] = json_encode($transactions);
				
				actionDB($conn,"update","students",$data,"WHERE mid='".$student."'");
				clearStudent2Step3($conn,$student);
				
				showTeacherMenu($sender,"true",$teacherMoney,$select[0]['crate']);
				//showStudentMenu($student,$studentMoney);
				
				$data = [];
				$data['step']="7.1";
				$data['requestid']=$select[0]['pendingrequestid'];
				$data['chatid']=$sender;
				actionDB($conn,"update","students",$data,"WHERE mid='".$student."'");
				
				send($student,quickReplyTemplate("Please rate this teacher in term of price (1-5):",['1','2','3','4','5']));
			}
		}
	}
}

?>