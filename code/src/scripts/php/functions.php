<?php $_URL = "http://{$_SERVER['HTTP_HOST']}"; // set base URL variable & connect to HaloVault SQL database
$_SQL=new mysqli("localhost","root","".base64_decode(base64_decode('WVd4c01IVjBiMlpqYjI5c2NHRnpjMlZ6'))."","vault") or die("<!--{$_SQL->error}-->");

/* sad attempt to auto-sanitize SQL functions, ignore this
function sqlQuery($definitions, $table, $col1, $col2, $col3, $col4) {
	return $_SQL->query("SELECT {$definitions} FROM {$table} WHERE ");
} */

//define header and footer
$_HEAD="<!DOCTYPE html>
<html><head>
	<title>File Share</title>
	<script src=\"/src/scripts/external/jquery.min.js\"></script>
	<script src=\"/src/scripts/vault/main.js\"></script>
	<link rel='stylesheet' type='text/css' href='/src/css/upload.css' />
</head>
<body><!--<a style='position:absolute;' href='javascript:alert(document.cookie);'>DEBUG: check-cookie</a>-->";  
$_FOOT="\n</body></html>";

// test rabid's api
function grabPlayerData($name, $uid) {
	$context = stream_context_create(array(
	  'http' => array(
	    'method' => 'GET',
	    'header' => "Content-Type: type=application/json\r\n"
	    )
	  )
	);
	$url = "http://new.halostats.click/api/player?name={$name}&uid={$uid}"; //replace this IP with rabid's final URL
	if (empty($name) || empty($uid)) $url='http://173.16.48.132/api/player?name=KrazyKlown77&uid=8e42b4fabdf0b3a5';
	$api = json_decode(file_get_contents($url, false, $context));
	return $api;
}

// Start User Session
@session_start();
// if using overlay and the uid is alpha-numeric check if playerID matches on HaloVault, if so, set playerID session and auto authorize
if (!isset($_SESSION['dewPlayerID']) && isset($_COOKIE['dewUID']) && isset($_COOKIE['dewName']) && ctype_alnum($_COOKIE['dewUID'])) {
	$DEW = getPlayerData($_COOKIE['dewName'], $_COOKIE['dewUID']);
	$chkuser = $_SQL->query("SELECT * FROM users WHERE `playerid` = '{$DEW->playerID}' LIMIT 1");
	if ($chkuser->num_rows == 1) {
		$_DEW = $chkuser->fetch_object();
		if (!isset($_SESSION['dewPlayerID'])) $_SESSION['dewPlayerID'] = $_AUTH->playerid;
	}
}
else {
	unset($_AUTH); $_SESSION['dewPlayerID']=null;
}

// Check if already logged into HaloVault
if (isset($_COOKIE['hvAuth']) && !empty($_COOKIE['hvAuth'])) {
	$_AUTH = @json_decode(base64_decode(base64_decode($_COOKIE['hvAuth']))); $aid=(int)$_AUTH->id;
	$checkKey=$_SQL->query("SELECT sodium FROM users WHERE id = '{$aid}'") or die($_SQL->error);
	$assoc=$checkKey->fetch_object();
	if ($checkKey->num_rows==1 && $_AUTH->apiKey==sha1($assoc->sodium)) {
		if (!isset($_SESSION['key'])) $_SESSION['key'] = $_AUTH->apiKey;
	} else unset($_AUTH);
} 
else {
	unset($_AUTH); $_SESSION['key']=null;
}

// Define basic upload directories for content
$_root = str_replace('/code','/DATA',$_SERVER['DOCUMENT_ROOT']);
$DIR_MAPS = $_root."/enc/maps/";
$DIR_VARIANTS = $_root."/enc/variants/";
$DIR_SCREENSHOTS = $_root."/enc/screenshots/";

// cURL to grab external page contents
function getContents($url) {
	$ch = curl_init();	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$data = curl_exec($ch);
	curl_close($ch);
	if ($data == FALSE or empty($data))	return false;
	else return $data; 
}

// function to resize screenshot images based on GET values
function resize($file, $w, $h, $crop=FALSE) {
    list($width, $height) = getimagesize($file);
    $r = $width / $height;
    if ($crop) {
        if ($width > $height) $width=ceil($width-($width*abs($r-$w/$h)));
        else $height = ceil($height-($height*abs($r-$w/$h)));
        $newwidth = $w; $newheight = $h;
    } else {
        if ($w/$h > $r) {
            $newwidth = $h*$r;
            $newheight = $h;
        } else {
            $newheight = $w/$r;
            $newwidth = $w;
        }
    }
    $src = imagecreatefromjpeg($file); $dst = imagecreatetruecolor($newwidth, $newheight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
    return $dst;
}

// method of converting specific hex data
class int_helper {
    public static function int8($i) {
        return is_int($i) ? pack("c", $i) : unpack("c", $i)[1];
    }
    public static function uInt8($i) {
        return is_int($i) ? pack("C", $i) : unpack("C", $i)[1];
    }
    public static function int16($i) {
        return is_int($i) ? pack("s", $i) : unpack("s", $i)[1];
    }
    public static function uInt16($i, $endianness=false) {
        $f = is_int($i) ? "pack" : "unpack";
        if ($endianness === true)     $i = $f("n", $i); //big-endian
        elseif ($endianness===false)  $i = $f("v", $i); //little-endian
        elseif ($endianness===null)   $i = $f("S", $i); //machine-byte order
        return is_array($i) ? $i[1] : $i;
    }
    public static function int32($i) {
        return is_int($i) ? pack("l", $i) : unpack("l", $i)[1];
    }
    public static function uInt32($i, $endianness=false) {
		$f = is_int($i) ? "pack" : "unpack";
		if ($endianness === true)     $i = $f("N", $i); // big-endian
		elseif ($endianness===false)  $i = $f("V", $i); // little-endian
		elseif ($endianness===null)   $i = $f("L", $i); // machine byte order
		return is_array($i) ? $i[1] : $i;
    }
    public static function int64($i) {
        return is_int($i) ? pack("q", $i) : unpack("q", $i)[1];
    }
    public static function uInt64($i, $endianness=false) {
		$f = is_int($i) ? "pack" : "unpack";
		if ($endianness === true)     $i = $f("J", $i); // big-endian
		elseif ($endianness===false)  $i = $f("P", $i); // little-endian
		elseif ($endianness===null)   $i = $f("Q", $i); // machine byte order
		return is_array($i) ? $i[1] : $i;
    }
}

// prints the image and sanitizes
function printImg($filepath) { 
    $type = exif_imagetype($filepath); // [] if you don't have exif you could use getImageSize() 
    $allowedTypes = array( 
        1,  // [] gif 
        2,  // [] jpg 
        3,  // [] png 
        6   // [] bmp 
    ); 
    if (!in_array($type, $allowedTypes)) return false; 
    switch ($type) { 
        case 1 : $im = imageCreateFromGif($filepath);  break; 
        case 2 : $im = imageCreateFromJpeg($filepath); break; 
        case 3 : $im = imageCreateFromPng($filepath);  break; 
        case 6 : $im = imageCreateFromBmp($filepath);  break; 
    }
    return $im;  
} 

//get thumbnail of basic media hosting services.
function Thumb($video) {
	$mediaURL = parse_url($video); 
	if (@stripos($mediaURL['host'], 'youtube.com') !== FALSE) {
		parse_str($mediaURL['query'], $videoquery); 
		$_thumb = "//i1.ytimg.com/vi/".$videoquery['v']."/mqdefault.jpg";
	} elseif (stripos($video, 'imgur.com') !== false) {
		$image1 = str_replace('.jpg','m.jpg', $video);
		$image2 = str_replace('.png','m.png', $image1);
		$image3 = str_replace('.gif','m.gif', $image2);
		$image4 = str_replace('.svg','m.svg', $image3);
		$_video = str_replace('.jpeg','m.jpeg', $image4);
		$_thumb=$_video;
	} else { $_thumb=$video; } return $_thumb;
}

// cleans up the actual file size to be displayed
function byteSize($bytes) {
    if ($bytes >= 1073741824)  $bytes = number_format($bytes / 1073741824, 2) . ' GB';
	elseif ($bytes >= 1048576) $bytes = number_format($bytes / 1048576, 2) . ' MB';
	elseif ($bytes >= 1024)    $bytes = number_format($bytes / 1024, 2) . ' KB';
	elseif ($bytes > 1)        $bytes = $bytes . ' bytes';
	elseif ($bytes == 1)       $bytes = $bytes . ' byte';
    else                       $bytes = '0 bytes';
    return $bytes;
}

// MAP VARIANTS, multiple functions included. Hopefully self-explainatory
function getMapName($mid) {
	if ($mid==30)	  	return "Last Resort";	 
	elseif ($mid==310)	return "High Ground";   
	elseif ($mid==320)	return "Guardian";	    
	elseif ($mid==340)	return "Valhalla";	    
	elseif ($mid==380)	return "Narrows";	    
	elseif ($mid==390)	return "The Pit";	    
	elseif ($mid==400)	return "Sandtrap";	    
	elseif ($mid==410)	return "Standoff";	   
	elseif ($mid==700)	return "Reactor";	   
	elseif ($mid==705)	return "Diamondback";   
	elseif ($mid==31) 	return "Icebox";	    
	elseif ($mid==703)	return "Edge";	        
	elseif ($mid==413)	return "Flatgrass";	   
	elseif ($mid==415)	return "Station";	   
	elseif ($mid==414)	return "Lockout";	   
	elseif ($mid==416)	return "Hang 'Em High"; 
	else return "Custom";
} 
function ogMapName($map) {
	$map = strtolower($map);
	if ($map == 'diamondback')     $oName='s3d_avalanche';
	elseif ($map == 'edge')        $oName='s3d_edge';
	elseif ($map == 'guardian')    $oName='guardian';
	elseif ($map == 'icebox')      $oName='s3d_turf';
	elseif ($map == 'narrows')     $oName='chill';
	elseif ($map == 'reactor')     $oName='s3d_reactor';
	elseif ($map == 'standoff')    $oName='bunkerworld';
	elseif ($map == 'the pit')     $oName='cyberdyne';
	elseif ($map == 'valhalla')    $oName='riverworld';
	elseif ($map == 'last resort') $oName='zanzibar';
	elseif ($map == 'high ground') $oName='deadlock';
	elseif ($map == 'sandtrap')    $oName='shrine';
	elseif ($map == 'flatgrass')   $oName='flatgrass';
	elseif ($map == 'station')     $oName='station';
	elseif ($map == 'lockout')     $oName='lockout';
	elseif ($map=="hang 'em high") $oName='hangem-high';
	else                           $oName='unknown';
	return $oName;
} 
function getMapImg($mid) { 
	$nme=getMapName($mid);
	$mIMG = "http://files.dewsha.re/src/images/maps/large/".ogMapName($nme).".png";
	return $mIMG;
} 
function getMapQuote($map) {
	$map = strtolower($map);
	if ($map == 'diamondback') 		$forgeQuote = "Hot winds blow over what should be a dead moon. A reminder of the power Forerunners once wielded."; 
	elseif ($map == 'edge') 		$forgeQuote = "The remote frontier world of Partition has provided this ancient databank with the safety of seclusion."; 
	elseif ($map == 'guardian') 	$forgeQuote = "Millennia of tending has produced trees as ancient as the Forerunner structures they have grown around."; 
	elseif ($map == 'icebox') 		$forgeQuote = "Downtown Tyumen's Precinct 13 offers an ideal context for urban combat training."; 
	elseif ($map == 'narrows') 		$forgeQuote = "Without cooling systems such as these, excess heat from the Ark's forges would render the construct uninhabitable."; 
	elseif ($map == 'reactor') 		$forgeQuote = "Being constructed just prior to the Invasion, its builders had to evacuate before it was completed."; 
	elseif ($map == 'standoff') 	$forgeQuote = "Once, nearby telescopes listened for a message from the stars. Now, these silos contain our prepared response."; 
	elseif ($map == 'the pit') 		$forgeQuote = "Software simulations are held in contempt by the veteran instructors who run these training facilities."; 
	elseif ($map == 'valhalla') 	$forgeQuote = "The crew of V-398 barely survived their unplanned landing in this gorge, but they know they are not alone."; 
	elseif ($map == 'last resort')	$forgeQuote = "Remote industrial sites like this one are routinely requisitioned & used as part of Spartan training exercises."; 
	elseif ($map == 'high ground')	$forgeQuote = "A relic of older conflicts, this base was reactivated after the New Mombasa Slipspace Event."; 
	elseif ($map == 'sandtrap') 	$forgeQuote = "Although the Brute occupiers have been driven from this ancient structure, they left plenty to remember them by."; 
	elseif ($map == 'flatgrass')	$forgeQuote = "Modders offering a plain flat map with an extended pallet of items ideal for Forge."; 
	elseif ($map == 'lockout')		$forgeQuote = "Some believe this remote facility was once used to study the Flood. But few clues remain amidst the snow and ice."; 
	else 							$forgeQuote = "A custom map imported by a modder of the Halo Online community."; 
	return $forgeQuote;
}

// GAME VARIANTS, multiple functions included. Hopefully self-explainatory
function getGameType($gid) {
	if ($gid=='03')    	return "Oddball";		
	elseif ($gid=='02')	return "Slayer";	      
	elseif ($gid=='0a')	return "Infection";      
	elseif ($gid=='09')	return "Assault";       
	elseif ($gid=='04')	return "King of the Hill";
	elseif ($gid=='07')	return "Juggernaut";	  
	elseif ($gid=='08')	return "Territories";	  
	elseif ($gid=='06')	return "VIP";	          
	elseif ($gid=='01')	return "Capture the Flag";
	elseif ($gid=='05')	return "Forge";	           
	else return "None";
} 
function getVarExt($gid) {
	if ($gid == '03')   return "variant.oddball";		
	elseif ($gid=='02')	return "variant.slayer";	      
	elseif ($gid=='0a')	return "variant.zombiez";      
	elseif ($gid=='09')	return "variant.assault";       
	elseif ($gid=='04')	return "variant.koth";
	elseif ($gid=='07')	return "variant.jugg";	  
	elseif ($gid=='08')	return "variant.terries";	  
	elseif ($gid=='06')	return "variant.vip";	          
	elseif ($gid=='01')	return "variant.ctf";
	else				return "variant.forge";
} 
function getDmgResist($dmg) {
	if ($dmg=='00')     return "100%";	
	elseif ($dmg=='01') return "10%";		
	elseif ($dmg=='02') return "50%";		
	elseif ($dmg=='03') return "90%";		
	elseif ($dmg=='04') return "100%";		
	elseif ($dmg=='05') return "110%";		
	elseif ($dmg=='06') return "150%";		
	elseif ($dmg=='07') return "200%";		
	elseif ($dmg=='08') return "300%";		
	elseif ($dmg=='09') return "500%";		
	elseif ($dmg=='0a') return "1000%";		
	elseif ($dmg=='0b') return "2000%";		
	elseif ($dmg=='0c') return "Invulnerable";
	else return "Unknown";
} 
function getDmgDeal($dmg) {
	if ($dmg=='00') 	return "100%";			
	elseif ($dmg=='01') return "0%";			
	elseif ($dmg=='02') return "25%";			
	elseif ($dmg=='03') return "50%";			
	elseif ($dmg=='04') return "75%";			
	elseif ($dmg=='05') return "90%";			
	elseif ($dmg=='06') return "100%";			
	elseif ($dmg=='07') return "110%";			
	elseif ($dmg=='08') return "125%";			
	elseif ($dmg=='09') return "150%";			
	elseif ($dmg=='0a') return "200%";			
	elseif ($dmg=='0b') return "300%";			
	elseif ($dmg=='0c') return "Instant Kill";	
	else return "Unknown";
} 
function getWeap($wea) {
	if ($wea=='01' || $wea=='ff' | $wea=='fe') return "Assault Rifle";
	elseif ($wea=='00')	return "Battle Rifle";	
	elseif ($wea=='0d')	return "Brute Shot";	
	elseif ($wea=='12')	return "Gravity Hammer";
	elseif ($wea=='07')	return "Magnum";		
	elseif ($wea=='08')	return "Needler";		
	elseif ($wea=='02')	return "Plasma Pistol";	
	elseif ($wea=='0a')	return "Rocket Launcher";
	elseif ($wea=='0b')	return "Shotgun";		
	elseif ($wea=='04')	return "S.M.G.";		
	elseif ($wea=='0c')	return "Sniper Rifle";	
	elseif ($wea=='10')	return "Spartan Laser";	
	elseif ($wea=='06')	return "Energy Sword";	
	elseif ($wea=='03')	return "Spiker";		
	elseif ($wea=='05')	return "Carbine";		
	elseif ($wea=='0f')	return "Beam Rifle";	
	elseif ($wea=='13')	return "Mauler";		
	elseif ($wea=='17')	return "Fuel-Rod";		
	elseif ($wea=='16')	return "Sentinel Beam";	
	elseif ($wea=='18')	return "D.M.R.";		
	elseif ($wea=='1d')	return "Assault Rifle ACC";
	elseif ($wea=='1a')	return "Assault Rifle DMG";
	elseif ($wea=='1b')	return "Assault Rifle ROF";
	elseif ($wea=='1e')	return "Assault Rifle PWR";
	elseif ($wea=='20')	return "Battle Rifle ACC";
	elseif ($wea=='22')	return "Battle Rifle DMG";
	elseif ($wea=='21')	return "Battle Rifle MAG";
	elseif ($wea=='23')	return "Battle Rifle RNG";
	elseif ($wea=='1f')	return "Battle Rifle ROF";
	elseif ($wea=='24')	return "Battle Rifle PWR";
	elseif ($wea=='3b')	return "Carbine ACC";	
	elseif ($wea=='3a')	return "Carbine DMG";	
	elseif ($wea=='38')	return "Carbine MAG";	
	elseif ($wea=='39')	return "Carbine RNG";	
	elseif ($wea=='37')	return "Carbine ROF";	
	elseif ($wea=='3c')	return "Carbine PWR";	
	elseif ($wea=='26')	return "D.M.R. ACC";	
	elseif ($wea=='28')	return "D.M.R. DMG";	
	elseif ($wea=='29')	return "D.M.R. MAG";	
	elseif ($wea=='25')	return "D.M.R. RNG";	
	elseif ($wea=='27')	return "D.M.R. ROF";	
	elseif ($wea=='2a')	return "D.M.R. PWR";	
	elseif ($wea=='2c')	return "S.M.G. ACC";	
	elseif ($wea=='2e')	return "S.M.G. DMG";	
	elseif ($wea=='2b')	return "S.M.G. ROF";	
	elseif ($wea=='30')	return "S.M.G. PWR";		
	elseif ($wea=='41')	return "Magnum DMG";			
	elseif ($wea=='42')	return "Magnum PWR";			
	elseif ($wea=='36')	return "Plasma Rifle PWR";			
	elseif ($wea=='3f')	return "Mauler PWR";			
	elseif ($wea=='fd')	return "Random";	
	elseif ($wea=='43')	return "None";			
	else return "Unknown";
} 
function getPlayerSpeed($spd) {
	if ($spd=='00') 	return "100%";
	elseif ($spd=='01') return "25%";
	elseif ($spd=='02') return "50%";
	elseif ($spd=='03') return "75%";
	elseif ($spd=='04') return "90%";
	elseif ($spd=='05') return "100%";
	elseif ($spd=='06') return "110%";
	elseif ($spd=='07') return "125%";
	elseif ($spd=='08') return "150%";
	elseif ($spd=='09') return "200%";
	elseif ($spd=='0a') return "300%";
	else return "Unknown";
} 
function getShieldMulti($shm) {
	if ($shm=='00') 	return "Normal";	
	elseif ($shm=='01')	return "None";		
	elseif ($shm=='02')	return "Normal";	
	elseif ($shm=='03')	return "Overshield x2";
	elseif ($shm=='04')	return "Overshield x3";
	elseif ($shm=='05')	return "Overshield x4";
	else return "Unknown";
}
 function getWeapPick($wea) {
	if ($wea=='02') return false;
	else return true;
}
function getGameDesc($g) {
    if ($g=="Slayer") 				return "Kill your enemies. Kill your friends' enemies. Kill your friends.";
    elseif ($g=='Oddball') 			return "Hold the skull to earn points. It's like Hamlet, with guns.";
    elseif ($g=="Infection") 		return "The timeless struggle of human vs zombie. If you die by a zombie's hand, you join their ranks.";
    elseif ($g=="Assault") 			return "Carry your bomb to the enemy base, plant it, & defend until it detonates.";
    elseif ($g=="King Of The Hill") return "Control the hill to earn points. Earn points to win. It's good to be king.";
    elseif ($g=="Juggernaut")       return "If you meet the Juggernaut, kill the juggernaut.";
    elseif ($g=="Territories")      return "Defend your territory & control the land. Teams earn points for territories they control.";
    elseif ($g=="VIP")            	return "One Player on each team is Very Important. Take down the enemy VIP for points, but take care of your own.";
    elseif ($g=="Capture The Flag") return "Invade your opponet's stronghold, sieze their flag, & return it to your base to score.";
    elseif ($g=="Forge")            return "Collaborate in real time to edit & play variations of your favorite maps, from the subtle to the insane.";
    else 						return "Kill your enemies. Kill your friends' enemies. Kill your friends.";
}

// used in-place of get_file_contents('127.0.0.1')
function getIncludeContents($filename) {
    if (is_file($filename)) {
        ob_start();
        include $filename;
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }
    return false;
} 
// check for spamming against the database
function isSpamming($authid, $numToCheck, $min) {
	$_CHECK = new mysqli("localhost", "root", "all0utofcoolpasses", "vault") or die($_SQL->error);
	if (empty($interval) || !isset($interval)) $interval = '8 MINUTE';
	if (empty($numToCheck) || !isset($numToCheck)) $numToCheck = 2;
	$checkMapSpam = $_CHECK->query("SELECT id FROM maps WHERE (dewid='{$authid}' OR uid='{$authid}') AND (`date` > date_sub(CURRENT_TIMESTAMP, INTERVAL {$interval}))");
	$checkFileSpam = $_CHECK->query("SELECT id FROM files WHERE (dewid='{$authid}' OR uid='{$authid}') AND (`date` > date_sub(CURRENT_TIMESTAMP, INTERVAL {$interval}))");
	$checkMedSpam = $_CHECK->query("SELECT id FROM media WHERE (dewid='{$authid}' OR uid='{$authid}') AND (`date` > date_sub(CURRENT_TIMESTAMP, INTERVAL {$interval}))");
	$checkComSpam = $_CHECK->query("SELECT id FROM community WHERE (dewid='{$authid}' OR uid='{$authid}') AND (`date` > date_sub(CURRENT_TIMESTAMP, INTERVAL {$interval}))");
	$checkSpam = $checkMapSpam->num_rows + $checkFileSpam->num_rows + $checkComSpam->num_rows + $checkMedSpam->num_rows;
	if ($checkSpam > $numToCheck) return true;
	else return false;
}
// used to sort arrays, intelligently
function array_msort($array, $cols) {
    $colarr = array();
    foreach ($cols as $col => $order) {
        $colarr[$col] = array();
        foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
    }
    $eval = 'array_multisort(';
    foreach ($cols as $col => $order) {
        $eval .= '$colarr[\''.$col.'\'],'.$order.',';
    }
    $eval = substr($eval,0,-1).');';
    eval($eval);
    $ret = array();
    foreach ($colarr as $col => $arr) {
        foreach ($arr as $k => $v) {
            $k = substr($k,1);
            if (!isset($ret[$k])) $ret[$k] = $array[$k];
            $ret[$k][$col] = $array[$k][$col];
        }
    }
    return $ret;
}
// Transform BBcode into HTML
function bb_parse($string) { 
	$string = str_replace('[u]', '[underline]', $string);
	$string = str_replace('[/u]', '[/underline]', $string);
	$tags = 'b|underline|i|strike|size|color|font|center|quote|code|url|img|media'; 
	while (preg_match_all('/\[('.$tags.')=?(.*?)\](.+?)\[\/\1\]/is', $string, $matches)) foreach ($matches[0] as $key => $match) { 
		list($tag, $param, $innertext) = array($matches[1][$key], $matches[2][$key], $matches[3][$key]); 
		switch ($tag) { 
			case 'b': $replacement = "<strong>$innertext</strong>"; break; 
			case 'underline': $replacement = "<span style='text-decoration:underline;'>$innertext</span>"; break; 
			case 'i': $replacement = "<em>$innertext</em>"; break; 
			case 'strike': $replacement = "<del>$innertext</del>"; break; 
			case 'size': $replacement = "<span style=\"font-size: $param;\">$innertext</span>"; break; 
			case 'color': $replacement = "<span style=\"color: $param;\">$innertext</span>"; break; 
			case 'font': $replacement = "<span style=\"font-family: $param;\">$innertext</span>"; break; 
			case 'center': $replacement = "<center>$innertext</center>"; break; 
			case 'quote': $replacement = "<blockquote>$innertext</blockquote>"; break; 
			case 'code': $replacement = "<code>$innertext</code>"; break; 
			case 'url': $replacement = '<a href="' . ($param? $param : $innertext) . "\" target='_blank'>$innertext</a>"; $replacement = str_replace('javascript:', '', $replacement); break; 
			case 'img': 
			list($width, $height) = preg_split('`[Xx]`', $param); 
			$replacement = "<a href=\"$innertext\" target='_blank'><img " .(isImage($innertext)? "src=\"$innertext\" " : '') . (is_numeric($width)? "width=\"$width\" " : '') . (is_numeric($height)? "height=\"$height\" " : '') . 'style="max-width:80%;" /></a>'; 
			break; 
			case 'media': 
			$mediaURL = parse_url($innertext); 
			parse_str($mediaURL['query'], $videoquery); 
			if (stripos($mediaURL['host'], 'vid.me') !== FALSE) {
				$vidme = substr($innertext, strrpos($innertext, '/') + 1);
				$replacement = '<iframe src="//vid.me/e/' . $vidme . '" width="70%" height="350" frameborder="0" allowfullscreen></iframe>'; 
			} elseif (stripos($mediaURL['host'], 'imgur.com') !== FALSE) {
				$album = substr($innertext, strrpos($innertext, '/') + 1);
				$replacement = '<blockquote class="imgur-embed-pub" style="color:white; background-color:black;" lang="en" data-id="a/' . $album . '"></blockquote><script async src="//s.imgur.com/min/embed.js" charset="utf-8\"></script>';
			} elseif (stripos($mediaURL['host'], 'gfycat.com') !== FALSE) {
				$gyf = substr($innertext, strrpos($innertext, '/') + 1);
				$replacement = '<div style="position:relative;padding:10px;"><iframe src="https://gfycat.com/ifr/'.$gyf.'" frameborder="0" scrolling="no" width="70%" height="350" allowfullscreen></iframe></div>';
			} elseif (stripos($mediaURL['host'], 'youtube.com') !== FALSE) {
				$replacement = '<iframe src="http://www.youtube.com/embed/' . $videoquery['v'] . '" width="70%" height="350" frameborder="0" allowfullscreen></iframe>'; 
			} elseif (stripos($mediaURL['host'], 'google.com') !== FALSE) {
				$replacement = '<embed src="http://video.google.com/googleplayer.swf?docid=' . $videoquery['docid'] . '" width="70%" height="350" type="application/x-shockwave-flash"></embed>';
			} 
			break; 
		} $string = str_ireplace($match, $replacement, $string); 
	} 
	return emojify($string);
}
// removes BB-Code
function removeBB($string) { 
	$string = str_replace('[hr]', '', $string); $replacement='';
	$tags = 'b|u|i|size|color|font|center|quote|code|url|img|media|B|U|I|SIZE|COLOR|FONT|CENTER|QUOTE|CODE|URL|IMG|MEDIA'; 
	while (preg_match_all('/\[('.$tags.')=?(.*?)\](.+?)\[\/\1\]/is', $string, $matches)) foreach ($matches[0] as $key => $match) { 
		list($tag, $param, $innertext) = array($matches[1][$key], $matches[2][$key], $matches[3][$key]); 
		switch ($tag) { 
			case 'b': $replacement = $innertext; break; 
			case 'u': $replacement = $innertext; break; 
			case 'i': $replacement = $innertext; break; 
			case 'size': $replacement = $innertext; break; 
			case 'color': $replacement = $innertext; break; 
			case 'font': $replacement = $innertext; break; 
			case 'center': $replacement = $innertext; break; 
			case 'media': $replacement = ''; break; 
			case 'img': $replacement = ''; break; 
			case 'quote': $replacement = $innertext; break; 
			case 'code': $replacement = $innertext; break; 
			case 'url': $replacement = $innertext; break; 
			break; 
		} $string = str_ireplace($match, $replacement, $string); 
	} 
	return $string; 
} ?>