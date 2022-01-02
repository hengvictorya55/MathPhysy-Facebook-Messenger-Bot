<?php
function startsWith ($string, $startString) 
{ 
    $len = strlen($startString); 
    return (substr($string, 0, $len) === $startString); 
} 

	function send($sender,$dataToSend,$action="",$push="REGULAR"){
		$response = [
			'recipient' => [ 'id' => $sender ],
			'message' => $dataToSend,
			'notification_type' => $push
		];
		
		if($action!=""){
			$response = [ 
				'recipient' => [ 'id' => $sender ],
				'sender_action' => $action
			];
		}
		$accessToken =   "EAAKRWcjixlIBAL0UFyYrtoa8Fxg61X3FNeL0RuILJ5qm4ZApT9J110wgAZAYeA5GVUZALYd8bjfRTUdiRrDeqEcRpTZA1tg31YX7GhtwbLO5VfaTiIjt1gYnrXQVpZAftVS02c8zSeshwRh6xzutFQVf7uRlButMZBXQRTeH74jjzkOi0QzUpoZCE9gSY7k3egZD";

		$ch = curl_init('https://graph.facebook.com/v2.6/me/messages?access_token='.$accessToken);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
		$result = curl_exec($ch);
		curl_close($ch);
		file_put_contents("a.txt",file_get_contents("a.txt")."\n\n".trim(preg_replace('/\s+/', ' ', json_encode($response)))."\n".$result);
	}
	
	function saveAttachment($url,$type){
		$response = [];
		$response['message']['attachment']['type'] = $type;
		$response['message']['attachment']['payload']['url'] = $url;
		$response['message']['attachment']['payload']['is_reusable'] = "true";
		
		$accessToken =   "EAAKRWcjixlIBAL0UFyYrtoa8Fxg61X3FNeL0RuILJ5qm4ZApT9J110wgAZAYeA5GVUZALYd8bjfRTUdiRrDeqEcRpTZA1tg31YX7GhtwbLO5VfaTiIjt1gYnrXQVpZAftVS02c8zSeshwRh6xzutFQVf7uRlButMZBXQRTeH74jjzkOi0QzUpoZCE9gSY7k3egZD";

		$ch = curl_init('https://graph.facebook.com/v2.6/me/message_attachments?access_token='.$accessToken);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
		$result = curl_exec($ch);
		curl_close($ch);
		file_put_contents("c.txt",$result);
		return json_decode($result,true);
	}
	
	function getname($mid){
		$accessToken =   "EAAKRWcjixlIBAL0UFyYrtoa8Fxg61X3FNeL0RuILJ5qm4ZApT9J110wgAZAYeA5GVUZALYd8bjfRTUdiRrDeqEcRpTZA1tg31YX7GhtwbLO5VfaTiIjt1gYnrXQVpZAftVS02c8zSeshwRh6xzutFQVf7uRlButMZBXQRTeH74jjzkOi0QzUpoZCE9gSY7k3egZD";

		$ch = curl_init('https://graph.facebook.com/v2.6/'.$mid.'?fields=first_name,last_name,name&access_token='.$accessToken);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
		$result = curl_exec($ch);
		curl_close($ch);
		file_put_contents("c.txt",$result);
		return json_decode($result,true)['name'];
	}

	function textTemplate($text){
		$answer = [];
		$answer['text'] = $text;
		return $answer;
	}
	
	function quickReplyTemplate($text,$data){
		$answer = [];
		$answer['text'] = $text;
		$i=0;
		foreach($data as $key=>$value){
			$answer['quick_replies'][$i]['content_type'] = 'text';
			$answer['quick_replies'][$i]['title'] = explode("|",$value)[0];
			$answer['quick_replies'][$i]['payload'] = $value;
			$i++;
		}
		return $answer;
	}
	
	function postbackButtonTemplate($text,$data){
		$answer = [];
		$answer['attachment']['type'] = "template";
		$answer['attachment']['payload'] = [];
		$answer['attachment']['payload']['template_type'] = "button";
		$answer['attachment']['payload']['text'] = $text;
		$answer['attachment']['payload']['buttons'] = [];
		$i=0;
		foreach($data as $key=>$value){
			if(explode("|",$value)[2]!="web"){
				$answer['attachment']['payload']['buttons'][$i]['type'] = 'postback';
				$answer['attachment']['payload']['buttons'][$i]['title'] = explode("|",$value)[0];
				$answer['attachment']['payload']['buttons'][$i]['payload'] = $value;
			}else{
				$answer['attachment']['payload']['buttons'][$i]['type'] = "web_url";
				$answer['attachment']['payload']['buttons'][$i]['title'] = explode("|",$value)[0]; 
				$answer['attachment']['payload']['buttons'][$i]['url'] = explode("|",$value)[1];
				$answer['attachment']['payload']['buttons'][$i]['webview_height_ratio'] = "tall";
			}
			$i++;
		}
		return $answer;
	}
	
	function genericTemplate($data){
		$answer = [];
		$answer['attachment']['type'] = "template";
		$answer['attachment']['payload']['template_type'] = "generic";
		$answer['attachment']['payload']['sharable'] = "true";
		$answer['attachment']['payload']['image_aspect_ratio'] = "square"; 
		
		$k=0;
		for($i=0;$i<count($data);$i++){
			$answer['attachment']['payload']['elements'][$k]['title'] = $data[$k]['title'];
			$answer['attachment']['payload']['elements'][$k]['subtitle'] = "⠀";
			$answer['attachment']['payload']['elements'][$k]['image_url'] = $data[$k]['image'];
			//$answer['attachment']['payload']['elements'][$k]['default_action']['type'] = "postback";
			//$answer['attachment']['payload']['elements'][$k]['default_action']['payload'] = "https://google.com";
			for($j=0;$j<count($data[$k]['buttons']);$j++){
				if($data[$k]['buttons'][$j]['web']==null){
					$answer['attachment']['payload']['elements'][$k]['buttons'][$j]['type'] = "postback";
					$answer['attachment']['payload']['elements'][$k]['buttons'][$j]['title'] = explode("|",$data[$k]['buttons'][$j]['text'])[0]; 
					$answer['attachment']['payload']['elements'][$k]['buttons'][$j]['payload'] = $data[$k]['buttons'][$j]['text'];
				}else{
					$answer['attachment']['payload']['elements'][$k]['buttons'][$j]['type'] = "web_url";
					$answer['attachment']['payload']['elements'][$k]['buttons'][$j]['title'] = explode("|",$data[$k]['buttons'][$j]['text'])[0]; 
					$answer['attachment']['payload']['elements'][$k]['buttons'][$j]['url'] = $data[$k]['buttons'][$j]['web'];
					$answer['attachment']['payload']['elements'][$k]['buttons'][$j]['webview_height_ratio'] = "tall";
				}
			}
			$k++;
		}
		return $answer;
	}
	
	///////////////////os trim nis sen
	function clearStudent2Step3($conn,$student){
		$data = [];
		$data['step'] = 3;
		$data['finding'] = "";
		$data['toAsk'] = "[]";
		$data['requestid'] = "";
		$data['pendingteachers']="[]";		
		$data['chatid'] = "";
		$data['price'] = "";
		$data['prerate'] = "[]";
		actionDB($conn,"update","students",$data,"WHERE mid='".$student."'");	
	}
	function clearTeacher2Step3($conn,$teacher){
		$data = [];
		$data['step'] = 3;
		$data['online'] = 'true';
		$data['pendingid'] = "";
		$data['pendingrequestid'] = "";
		$data['askedmoney'] = ""; 
		$data['chatid'] = "";
		$data['price'] = "";
		actionDB($conn,"update","teachers",$data,"WHERE mid='".$teacher."'");	
	}
	
	function showStudentMenu($sender,$money,$uid){
		$generic = [];
		$generic[0]['title'] = "Find Tutor";
		$generic[0]['image'] = "https://i.imgur.com/zVtA7rp.png";
		$generic[0]['buttons'][0]['text'] = "Mathematics|finding";
		$generic[0]['buttons'][1]['text'] = "Physics|finding";
		
		$generic[1]['title'] = "Current Balance";
		$generic[1]['image'] = generateMoneyImage($money);
		$generic[1]['buttons'][0]['text'] = "Reload Balance";
		$generic[1]['buttons'][0]['web'] = "https://mathphysy.com/reload.php?uid=".$uid;
		$generic[1]['buttons'][1]['text'] = "View Transactions";
		 
		$generic[2]['title'] = "Other Options";
		$generic[2]['image'] = "https://i.imgur.com/zS7gqVn.png";
		$generic[2]['buttons'][0]['text'] = "Refer Friends";
		$generic[2]['buttons'][1]['text'] = "Buy Gift Card";
		$answer = genericTemplate($generic);
		send($sender,$answer,"","NO_PUSH");
	}
	
	function showTeacherMenu($sender,$isOnline,$money,$crate){
		$rate1=0;
		$rate2=0;
		$rate3=0;
	
		$arrayRate = json_decode($crate, true);
		if(count($arrayRate)>0){
			foreach($arrayRate as $rate){
				$rate1+=$rate['rate']['price'];
				$rate2+=$rate['rate']['quality'];
				$rate3+=$rate['rate']['time'];
			}
			$rate1/=count($arrayRate);
			$rate2/=count($arrayRate);
			$rate3/=count($arrayRate);
		}
		
		$generic = [];
		if($isOnline=='true'){
			$generic[0]['title'] = "You're ONLINE";
			$generic[0]['image'] = "https://i.imgur.com/XIYI9eA.png";
			$generic[0]['buttons'][0]['text'] = "Go OFFLINE";
		}else{
			$generic[0]['title'] = "You're OFFLINE";
			$generic[0]['image'] = "https://i.imgur.com/utRL8q3.png";
			$generic[0]['buttons'][0]['text'] = "Go ONLINE";
		}
		
		$generic[1]['title'] = "Current Balance";
		$generic[1]['image'] = generateMoneyImage($money);
		$generic[1]['buttons'][0]['text'] = "View Transactions";
		
		$generic[2]['title'] = "Your Rating";
		$generic[2]['image'] = generateRateImage($rate1,$rate2,$rate3);
		$generic[2]['buttons'][0]['text'] = "View Rating";
		 
		//$generic[3]['title'] = "Other Options";
		//$generic[3]['image'] = "https://i.imgur.com/zS7gqVn.png";
		//$generic[3]['buttons'][0]['text'] = "View Chat History";
		$answer = genericTemplate($generic);
		send($sender,$answer,"","NO_PUSH");
	}
	
	function resize_im($im, $w, $h) {
	   $src = $im;
	   $dst = imagecreatetruecolor($w, $h);
	   imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, 1000, 1000);
	   return $dst;
	}

	function generateMoneyImage($amount){
		$im = imagecreatefrompng('money.png');
		
		$text = '$'.number_format($amount,2);
		$font = 'arial.ttf';
		$size = 130;
		$box = imagettfbbox($size, 0, $font, $text);
		$text_width = abs($box[2]) - abs($box[0]);
		$text_height = abs($box[5]) - abs($box[3]);
		$image_width = imagesx($im);
		$image_height = imagesy($im);
		$x = ($image_width - $text_width) / 2;
		$y = 825;
		$green = imagecolorallocate($im, 0, 183, 159);
		imagettftext($im, $size, 0, $x, $y, $green, $font, $text);
		
		$im = resize_im($im,500,500);
		imagepng($im,"img/money/".explode("$",$text)[1].".png");
		imagedestroy($im);
		return "http://www.".$_SERVER['SERVER_NAME']."/"."img/money/".explode("$",$text)[1].".png";
	}
	
	function generateRateImage($rate1, $rate2, $rate3){
		$im = imagecreatefrompng('white.png');
		$im1 = imagecreatefrompng('rate.png');
		$fill = imagecreatefrompng('ratefill.png');
		
		imagecopy($im, $fill, 0, 150, 0, 0, 145+(695*$rate1/5), 156);
		imagecopy($im, $fill, 0, 458, 0, 0, 145+(695*$rate2/5), 156);
		imagecopy($im, $fill, 0, 753, 0, 0, 145+(695*$rate3/5), 156);
		
		imagecopy($im, $im1, 0, 0, 0, 0, 1000, 1000);
		
		$fileName = "img/rate".$rate1."a".$rate2."a".$rate3.".png";
		$im = resize_im($im,500,500);
		imagepng($im, $fileName);
		imagedestroy($im);
		return "http://www.".$_SERVER['SERVER_NAME']."/".$fileName;
	}
	
	function ReplaceColour($img, $r1, $g1, $b1, $r2, $g2, $b2){
		if(!imageistruecolor($img))
			imagepalettetotruecolor($img);
		$col1 = (($r1 & 0xFF) << 16) + (($g1 & 0xFF) << 8) + ($b1 & 0xFF);
		$col2 = (($r2 & 0xFF) << 16) + (($g2 & 0xFF) << 8) + ($b2 & 0xFF);

		$width = imagesx($img); 
		$height = imagesy($img);
		for($x=0; $x < $width; $x++)
			for($y=0; $y < $height; $y++)
			{
				$colrgb = imagecolorat($img, $x, $y);
				if($col1 !== $colrgb)
					continue; 
				imagesetpixel ($img, $x , $y , $col2);
			}   
	}

	function generateQRRefImage($mid){
		$fileName = "img/ref".$mid.".png";
		$data = 'http://m.me/MathPhysy?ref='.$mid;
		$size = '400x400';
		$QR = imagecreatefrompng('https://chart.googleapis.com/chart?cht=qr&chld=H|1&chs='.$size.'&chl='.urlencode($data));		
		ReplaceColour($QR, 0,0,0,0,147,128);
		imagepng($QR,$fileName);  
		imagedestroy($QR);
		return "http://www.".$_SERVER['SERVER_NAME']."/".$fileName;
	}
	
	function createGiftCardCode() { 
		$chars = "ABCDEFGHIJKMNOPQRSTUVWXYZ01234567890123456789"; 
		srand((double)microtime()*1000000); 
		$i = 0; 
		$pass = '' ; 
		while ($i < 12) { 
			$num = rand() % strlen($chars); 
			$tmp = substr($chars, $num, 1); 
			$pass = $pass . $tmp; 
			$i++; 
			if($i % 4 == 0){
				$pass.=" ";
			}
		}
		$pass = "MPGC ".$pass;
		$pass = substr($pass, 0, -1);
		return $pass; 
	} 

	function generateGiftCard($conn,$amount){
		$code = createGiftCardCode();
		$im = imagecreatefrompng('giftcard.png');
		$data = 'http://m.me/MathPhysy?ref='.str_replace(" ","",$code);
		$size = '260x260';
		$QR = imagecreatefrompng('https://chart.googleapis.com/chart?cht=qr&chld=H|1&chs='.$size.'&chl='.urlencode($data));		
		ReplaceColour($QR, 0,0,0,0,147,128);
		
		imagecopy($im, $QR, 395, 168, 0, 0, 260, 260);
		
		$text = '$'.$amount;
		$font = 'arial.ttf';
		$size = 33;
		$box = imagettfbbox($size, 0, $font, $text);
		$text_width = abs($box[2]) - abs($box[0]);
		$text_height = abs($box[5]) - abs($box[3]);
		$image_width = imagesx($im);
		$image_height = imagesy($im);
		$x = (158 - $text_width) / 2;
		$y = 95;
		$green = imagecolorallocate($im, 0, 147, 128);
		imagettftext($im, $size, 0, $x, $y, $green, $font, $text);
		
		$font = 'arial.ttf';
		$size = 25;
		$box = [];
		$box = imagettfbbox($size, 0, $font, $code);
		$text_width = abs($box[2]) - abs($box[0]);
		$text_height = abs($box[5]) - abs($box[3]);
		$image_width = imagesx($im);
		$image_height = imagesy($im);
		$x = ($image_width - $text_width) / 2;
		$y = 478;
		$green = imagecolorallocate($im, 0, 147, 128);
		imagettftext($im, $size, 0, $x, $y, $green, $font, $code);
		
		$data = [];
		$data['code'] = str_replace(" ","",$code);
		$data['amount'] = $amount;
		actionDB($conn,"insert","giftcards",$data);
		
		$fileName = "img/".str_replace(" ","",$code).".png";
		imagepng($im, $fileName);
		imagedestroy($im);
		return "http://www.".$_SERVER['SERVER_NAME']."/".$fileName;
	}
?>