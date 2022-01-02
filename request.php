<?php
include "db.php";
include "function.php";
$requestid = $_GET['rid'];
$fail = false;

$data = [];$data['mid']='';$data['name']='';$data['step']='';$data['finding']='';$data['toAsk']='';$data['requestid']='';$data['pendingteachers']='';$data['chatid']='';$data['price']='';$data['money']='';$data['prerate']='';$data['transactions']="";
$select = actionDB($conn,"select","students",$data,"WHERE requestid='".$requestid."'");
if($select!=false){
	$array = json_decode($select[0]['toAsk'], true);
	if(count($array)>0){
	?>
	<html>
		<head>
			<title>Request | MathPhysy</title>
			<meta charset="UTF-8"/>
			<meta name="viewport" content="width=device-width, initial-scale=1" />
			<script src="jplayer/jquery.js" type="text/javascript"></script>
			<script src="jplayer/jplayer.js" type="text/javascript"></script>
			<link rel="stylesheet" href="jplayer/jplayer.css?<?=uniqid()?>" type="text/css" media="all" />
			<style>
				@import url('https://fonts.googleapis.com/css?family=Roboto+Slab&display=swap');
				body{
					background:white;
					font-family: 'Roboto Slab', serif;
					font-size:17px;
				}
				.text{
					background: #00b79f;
					padding:10px;
					border-radius:10px;
					margin:10px;
					display:inline-block;
				}
				.text img{
					width:100%;
					max-width:250px;
				}
				.title{
					font-size:19px;
					margin-bottom:20px;
				}
			</style>
		</head>
		<body>
		<center><div class="title"><?=$select[0]['name']?>'s request:</div></center>
		<?php
		foreach($array as $item){
			$uid = uniqid();
			if($item['type']=="text"){
				?>
				<div class="text"><?=rawurldecode($item['value'])?></div><br/>
				<?php
			}else if($item['type']=="image"){
				?>
				<div class="text"><img src="<?=$item['value']?>"/></div><br/>
				<?php
			}else if($item['type']=="file"){
				?>
				<div class="text"><a href="<?=$item['value']?>" target="_blank">Download File</a></div><br/>
				<?php
			}else if($item['type']=="video"){
				?>
				<div class="text">
				<video width="320" height="240" controls>
				  <source src="<?=$item['value']?>" type="video/mp4">
				</video>
				</div><br/>
				<?php
			}else if($item['type']=="audio"){
		?>
				<div class="jp-audio">
					<div class="jp-type-single">
						<div id="jquery_jplayer_<?=$$uid?>" class="jp-jplayer"></div>
						<div id="jp_interface_1" class="jp-interface">
							<ul class="jp-controls">
								<li><a href="#" class="jp-play" tabindex="1">play</a></li>
								<li><a href="#" class="jp-pause" tabindex="1">pause</a></li>
							</ul>
							<div class="jp-progress">
								<div class="jp-seek-bar">
									<div class="jp-play-bar"></div>
								</div>
							</div>
							<div class="jp-current-time"></div>
							<div class="jp-duration"></div>
						</div>
					</div>
				</div>
				<script>
				$(document).ready(function(){
					$("#jquery_jplayer_<?=$$uid?>").jPlayer({
						ready: function () {
							$(this).jPlayer("setMedia", {
								mp3: "<?=$item['value']?>",
							});
						},
						swfPath: "swf",
						supplied: "mp3"
					})
					.bind($.jPlayer.event.play, function() { // pause other instances of player when current one play
							$(this).jPlayer("pauseOthers");
					});
				});
				</script>
		<?php
			}
		}
		?>
		</body>
	</html>
	<?php
	}else{
		$fail=true;
	}
}else{
	$fail=true;
}


if($fail){
	?>
	<html>
		<head>
			<title>Request | MathPhysy</title>
			<meta charset="UTF-8"/>
			<meta name="viewport" content="width=device-width, initial-scale=1" />
			<script src="jplayer/jquery.js" type="text/javascript"></script>
			<script src="jplayer/jplayer.js" type="text/javascript"></script>
			<link rel="stylesheet" href="jplayer/jplayer.css?<?=uniqid()?>" type="text/css" media="all" />
			<style>
				@import url('https://fonts.googleapis.com/css?family=Roboto+Slab&display=swap');
				body{
					background:white;
					font-family: 'Roboto Slab', serif;
					font-size:17px;
				}
				.text{
					background: #00b79f;
					padding:10px;
					border-radius:10px;
					margin:10px;
					display:inline-block;
				}
				.text img{
					width:100%;
					max-width:250px;
				}
				.title{
					font-size:19px;
					margin-bottom:20px;
				}
			</style>
		</head>
		<body>
			<center><div class="title">Sorry, this request has been handled or canceled!</div></center>
		</body>
	</html>
	<?php
}
?>