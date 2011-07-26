<?php
if((strpos($_SERVER["REMOTE_ADDR"],"61.27.73.")!==false)||(strpos($_SERVER["REMOTE_ADDR"],"118.110.199.")!==false)){
die("Hahaha You are idiot");
}
//ここから条件分岐
if(!$_POST){
	$html = new html();
	$html->head();
	$html->top();
	$html->foot();
}else{
	$html = new html();
	$result = new result();
	$html->head();
	$html->top();
	$result->view();
	$html->foot();
}
//ここまで条件分岐
class html{

	function head(){
 print <<<END
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<!--
1B:取得範囲で全角数字で入力しても半角に変換する、3番目の選択肢の取得範囲が1行多かったのを修正
1C:「最新1000行」廃止、2万行制限、検索内容の表示
-->
<title>Lograhack(Beta) LH1C</title>
</head>
<body>
<h1>Lograhack(Beta) LH1C</h1>
END;
	
	
	}

	function foot(){
 print <<<END

</body>
</html>

END;
	}

	function top(){
$latestline = file_get_contents("http://81.la/c/chalog.php?lastid=loremipsum");
$latestline = json_decode($latestline,true);
if($latestline["error"]){
die("Log system goes wrong. Retry it Later.");
}else{
echo "<p>なんかね、".$latestline['lastid']."行あるみたいですよ。</p>";
}
 print <<<END

<form action="chalog.php" method="POST">
<fieldset><legend>取得範囲</legend>
<p><label><input type="radio" name="searcharea" value="time">発言時間で検索</label>：始点時間<input type="text" name="minyear" value="" size="4">年<input type="text" name="minmonth" value="" size="2">月<input type="text" name="minday" value="" size="2">日<input type="text" name="minhour" value="" size="2">時<input type="text" name="minminute" value="" size="2">分
終点時間<input type="text" name="maxyear" value="" size="4">年<input type="text" name="maxmonth" value="" size="2">月<input type="text" name="maxday" value="" size="2">日<input type="text" name="maxhour" value="" size="2">時<input type="text" name="maxminute" value="" size="2">分<br>
am/pmの12時間制ではなく24時間制で入力をお願いします。<br>最小に空白の欄があると最大までの24時間分を取得します<br>最大に空白の欄があると最小からの24時間分を取得します。<br>両方空白の欄があると最新24時間分を取得します。</p>
<p><label><input type="radio" name="searcharea" value="latest" checked>最新<input type="text" name="latest" value="1000" size="8">発言まで</label><br>
空欄だと<s>全発言</s>2万行を取得しますが、<strong>その場合如何なる影響が81.laに及ぼされても当方は責任を持ちません。</strong><br>
追記：5万行だとサイズが割り当てられたメモリを超してFATAL ERRORを出します。</p>
</fieldset>
<fieldset><legend>検索条件</legend>
<p><input type="checkbox" name="searchtype_name_ip"><label><input type="radio" name="nameorip" value="name" checked>名前</label>or<label><input type="radio" name="nameorip" value="ip">IPアドレス</label>で検索：<input type="text" name="name_ip" value="" size="15"></p>
<p>実在しないIPアドレスの場合は名前による検索に切り替わる。</p>
<p><label><input type="checkbox" name="searchtype_message">コメントで検索</label>：<input type="text" name="message" value="" size="30"></p>
<p>上の「名前で検索」と併用すると「特定人物の特定内容の発言」が抜き出されます。</p>
<p>ここでいずれも指定しない場合、上記範囲の全文が表示されます。</p>
</fieldset>
<input type="submit" value="送信">
</form>
END;
	}

}

class result{
	private $minid;
	private $maxid;

	function view(){
	$line['searchtype_name_ip'] = "";
	$line['name_ip'] = "";
	$line['nameorip'] = "";
	$line['searchtype_message'] = "";
	$line['message'] = "";
		foreach($_POST as $key => $string){
			$line[$key] = htmlspecialchars($string,ENT_QUOTES);
		}
//	var_dump($line);
		if($line['searcharea']==="latest"){
			if(empty($line['latest'])){
			$latestline = file_get_contents("http://81.la/c/chalog.php?lastid=loremipsum");
			$latestline = json_decode($latestline,true);
				if($latestline["error"]){
				die("Log system goes wrong. Retry it Later.");
				}
			$maxpage = ceil($latestline['lastid']/5000);
			$minpage = ceil(($latestline['lastid']-20000)/5000);
			$refer_minid = (($latestline['lastid']-20000)%5000);
			$lastpage_line = $latestline['lastid']%5000;
				$logdata[$minpage] = file_get_contents("http://81.la/c/chalog.php?chalog=".$minpage);
				$logdata_array[$minpage] = json_decode($logdata[$minpage],true);
					if($logdata_array[$minpage]['error']===true){
					die("Log system goes wrong. Retry it later.");
					}
				$maxpage = $logdata_array[$minpage]['maxpage'];
				$commentdata[$minpage] = $logdata_array[$minpage]['newcomments'];
				$refer_minid = 0;
				$a = $minpage;
					while($a<=$maxpage){
						if($a!==$minpage){
						$logdata[$a] = file_get_contents("http://81.la/c/chalog.php?chalog=".$a);
						$logdata_array[$a] = json_decode($logdata[$a],true);
							if($logdata_array[$a]['error']===true){
							die("Log system goes wrong. Retry Later.");
							}
						$commentdata[$a] = $logdata_array[$a]['newcomments'];
						}
						if($a==$minpage){
						$reference[$a] = array_slice($commentdata[$minpage],$refer_minid);
						}else if($a==$maxpage){
						$refer_maxid = count($commentdata[$maxpage]);
						$refer_maxid--;
						$reference[$a] = array_slice($commentdata[$maxpage],0,$refer_maxid);
						$reference = array_merge($reference[$maxpage-1],$reference[$maxpage]);
						}else{
						$reference[$a] = array_slice($commentdata[$a],0);
						$reference[$a] = array_merge($reference[$a-1],$reference[$a]);
						}
					$a++;
					}
				$display = new display($line['searchtype_name_ip'],$line['name_ip'],$line['nameorip'],$line['searchtype_message'],$line['message'],$reference);
				$display->view();
			}else{
			//emptyじゃないとき
			$line['latest'] = mb_convert_kana($line['latest'],"n","UTF-8");
			if($line['latest']>20000){
			$line['latest'] = 20000;
			}
			$latestline = file_get_contents("http://81.la/c/chalog.php?lastid=loremipsum");
			$latestline = json_decode($latestline,true);
				if($latestline["error"]){
				die("Log system goes wrong. Retry it Later.");
				}
			$minpage = ceil(($latestline['lastid'] - $line['latest'])/5000);
			$refer_minid = ($latestline['lastid'] - $line['latest'])%5000;
			$logdata[$minpage] = file_get_contents("http://81.la/c/chalog.php?chalog=".$minpage);
			$logdata_array[$minpage] = json_decode($logdata[$minpage],true);
				if($logdata_array[$minpage]['error']===true){
				die("Log system goes wrong. Retry Later.");
				}
			$maxpage = $logdata_array[$minpage]['maxpage'];
			$lastpage_line = $latestline['lastid']%5000;
			$commentdata[$minpage] = $logdata_array[$minpage]['newcomments'];
				if($minpage==$maxpage){
				$reference = array_slice($commentdata[$minpage],$refer_minid);
				}else{
				$a = $minpage;
					while($a<=$maxpage){
						if($a!==$minpage){
						$logdata[$a] = file_get_contents("http://81.la/c/chalog.php?chalog=".$a);
						$logdata_array[$a] = json_decode($logdata[$a],true);
							if($logdata_array[$a]['error']===true){
							die("Log system goes wrong. Retry Later.");
							}
						$commentdata[$a] = $logdata_array[$a]['newcomments'];
						}
						if($a==$minpage){
						$reference[$a] = array_slice($commentdata[$minpage],$refer_minid);
						}else if($a==$maxpage){
						$refer_maxid = count($commentdata[$maxpage]);
						$refer_maxid--;
						$reference[$a] = array_slice($commentdata[$maxpage],0,$refer_maxid);
						$reference = array_merge($reference[$maxpage-1],$reference[$maxpage]);
						}else{
						$reference[$a] = array_slice($commentdata[$a],0);
						$reference[$a] = array_merge($reference[$a-1],$reference[$a]);
						}
					$a++;
					}
				}
				$display = new display($line['searchtype_name_ip'],$line['name_ip'],$line['nameorip'],$line['searchtype_message'],$line['message'],$reference);
				$display->view();
			}
			
		}else if($line['searcharea']==="time"){
		$line['minhour'] = mb_convert_kana($line['minhour'],"n","UTF-8");
		$line['minminute'] = mb_convert_kana($line['minminute'],"n","UTF-8");
		$line['minmonth'] = mb_convert_kana($line['minmonth'],"n","UTF-8");
		$line['minday'] = mb_convert_kana($line['minday'],"n","UTF-8");
		$line['minyear'] = mb_convert_kana($line['minyear'],"n","UTF-8");
		$line['maxhour'] = mb_convert_kana($line['maxhour'],"n","UTF-8");
		$line['maxminute'] = mb_convert_kana($line['maxminute'],"n","UTF-8");
		$line['maxmonth'] = mb_convert_kana($line['maxmonth'],"n","UTF-8");
		$line['maxday'] = mb_convert_kana($line['maxday'],"n","UTF-8");
		$line['maxyear'] = mb_convert_kana($line['maxyear'],"n","UTF-8");
		$starttime = mktime($line['minhour'],$line['minminute'],0,$line['minmonth'],$line['minday'],$line['minyear']);
		$endtime = mktime($line['maxhour'],$line['maxminute'],0,$line['maxmonth'],$line['maxday'],$line['maxyear']);
		$display = new display($line['searchtype_name_ip'],$line['name_ip'],$line['nameorip'],$line['searchtype_message'],$line['message'],"");
		$display->view4time($starttime,$endtime);
		}else{
		die("Something goes wrong!");
		}
	
	}

}



class display{
	private $target_name;
	private $target_ip;
	private $target_message;
	private $searchtype_name_ip;
	private $searchtype_message;
	private $reference;
	
	function __construct($searchtype_name_ip,$name_ip,$nameorip,$searchtype_message,$message,$reference){
		if(isset($searchtype_name_ip)&&($nameorip=="ip")){
			$this->searchtype_name_ip = true;
			$ipcheck = long2ip(ip2long($name_ip));
				if(!$ipcheck){
				$this->target_name = $name_ip;
				$this->target_ip = NULL;
				}else{
				$this->target_name = NULL;
				$this->target_ip = $name_ip;
				}
			}else if(isset($searchtype_name_ip)&&($nameorip=="name")){
				$this->searchtype_name_ip = true;
				$this->target_name = $name_ip;
				$this->target_ip = NULL;
			}else{
				$this->searchtype_name_ip = false;
				$this->target_name = NULL;
				$this->target_ip = NULL;
			}
			if(isset($searchtype_message)){
				$this->searchtype_name_ip = true;
				$this->target_message = $message;
			}else{
				$this->searchtype_name_ip = false;
				$this->target_message = NULL;
			}
		$this->reference = $reference;
	}
	function view(){
		$number = count($this->reference);
		echo "<p>取得範囲：".$number."行、取得条件：名前「".$this->target_name."」IPアドレス「".$this->target_ip."」コメント「".$this->target_message."」</p>";
		echo "<p>";
			$n = 1;
			foreach($this->reference as $string){
			$ip = long2ip($string['ip']);
				if(!empty($this->searchtype_name_ip)&&!empty($this->searchtype_message)){
					if(!empty($this->target_name)&&($string['name']!==$this->target_name)){
//					unset($string);
					continue;
					}else if(!empty($this->target_ip)&&($ip!==$this->target_ip)){
//					unset($string);
					continue;
					}
					if(!empty($this->target_message)&&(strpos($string['comment'],$this->target_message)===false)){
//					unset($string);
					continue;
					}
				}else{
					if(!empty($this->target_name)&&($string['name']!==$this->target_name)){
//					unset($string);
					continue;
					}else if(!empty($this->target_ip)&&($ip!==$this->target_ip)){
//					unset($string);
					continue;
					}else if(!empty($this->target_message)&&(strpos($string['comment'],$this->target_message)===false)){
//					unset($string);
					continue;
					}
				}
				$color = explode(".",$ip);
				$r = intval($color[0]/1.33);
				$g = intval($color[1]/1.33);
				$b = intval($color[2]/1.33);
				$date = date("Y-m-d H:i:s",$string['date']);
				echo $n."<span style='color:rgb(".$r.",".$g.",".$b.");'>".$string['name'].">".$string['comment']."</span><span style='color:gray;font-size:small;'>(".$date.",".$ip.")</span><br>";
				$n++;
				}
		echo "</p>";
	}
	
	
	function view4time($mintime,$maxtime){
		if($mintime===false){
		$mintime = $maxtime - 60*60*24;
		}
		if($maxtime===false){
		$maxtime = $mintime + 60*60*24;
		}
		if(($mintime===false)&&($maxtime===false)){
		$maxtime = time();
		$mintime = $maxtime - 60*60*24;
		}
			if($mintime>$maxtime){
			echo "<p>取得範囲：始点時刻「".date("Y-n-j H:i:s",$maxtime)."」～終点時刻「".date("Y-n-j H:i:s",$mintime)."」";
			$queryurl = "http://81.la/c/chalog.php?starttime=".$maxtime."&endtime=".$mintime."&value=10000";
			}else{
			echo "<p>取得範囲：始点時刻「".date("Y-n-j H:i:s",$mintime)."」～終点時刻「".date("Y-n-j H:i:s",$maxtime)."」";
			$queryurl = "http://81.la/c/chalog.php?starttime=".$mintime."&endtime=".$maxtime."&value=10000";
			}
			echo "取得条件：";
				if(!empty($this->target_name)){
				echo "名前「".$this->target_name."」";
				$queryurl .= "&name=".$this->target_name;
				}
				if(!empty($this->target_ip)){
				echo "IPアドレス「".$this->target_ip."」";
				$queryurl .= "&ip=".$this->target_ip;
				}
				if(!empty($this->target_message)){
				echo "コメント「".$this->target_message."」";
				$queryurl .= "&comment=".$this->target_message;
				}
				echo "</p>";
		$logdata = file_get_contents($queryurl);
		$logdata_array = json_decode($logdata,true);
			if($logdata_array['error']===true){
			die("Log system goes wrong. Retry it later.");
			}
			if(empty($logdata_array['newcomments'])){
			die("Such data doesn't exist!");
			}else{
			$this->reference = $logdata_array['newcomments'];
			$n = 1;
				foreach($this->reference as $string){
				$ip = long2ip($string['ip']);
				$color = explode(".",$ip);
				$r = intval($color[0]/1.33);
				$g = intval($color[1]/1.33);
				$b = intval($color[2]/1.33);
				$date = date("Y-m-d H:i:s",$string['date']);
				echo $n."<span style='color:rgb(".$r.",".$g.",".$b.");'>".$string['name'].">".$string['comment']."</span><span style='color:gray;font-size:small;'>(".$date.",".$ip.")</span><br>";
				$n++;
				}
			}
	}
}
