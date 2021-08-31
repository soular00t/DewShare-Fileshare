<?php require_once "src/scripts/php/functions.php"; $_KEY='twigzie'; $time=time();
// SCREENSHOT SHARING (doesnt need any type of authorization)
if (isset($_GET['img'])) {
    $img = "../DATA/enc/screenshots/{$_GET['img']}";
    if (isset($_GET['enc'])) $img = str_replace('FILESHARE','DATA',base64_decode($_GET['img']));
    if (is_numeric($_GET['img'])!==FALSE) {
        $findimg = $_SQL->query("SELECT url FROM media WHERE type='s' AND id='{$_GET['img']}' LIMIT 1") or die($_SQL->error);
        $findIMG = $findimg->fetch_object();
        $img = str_replace('FILESHARE','DATA',base64_decode($findIMG->url));
    }
    $getimg = $_SQL->real_escape_string($_GET['img']);
    if (is_numeric($_GET['img'])) $getimg = (int) $_GET['img'];
    $med = $_SQL->query("SELECT * FROM media WHERE url='{$getimg}' OR id='{$getimg}' LIMIT 1");
    if ($med->num_rows > 0) {
        $medi = $med->fetch_object();
        $grabViews = $_SQL->query("SELECT * FROM views WHERE media_id = '{$medi->id}' AND ip='{$_SERVER['REMOTE_ADDR']}'") or die($_SQL->error);
        $VIEW = $grabViews->fetch_assoc();
        $uVcount = $grabViews->num_rows;
        $Vth3n = $VIEW['last_viewed'];
        $Vthen = strtotime($Vth3n);
        $Vdifference = time() - $Vthen;
        if($Vdifference > 21600 AND $uVcount < 1)
           $_SQL->query("INSERT INTO views (media_id,ip) VALUES ('{$medi->id}','{$_SERVER['REMOTE_ADDR']}')") or die($_SQL->error);
    }
    header('Content-type: ' . 'image/jpeg');
    if (isset($_GET['w']) || isset($_GET['h'])) {
        //error_reporting(E_ERROR | E_WARNING | E_PARSE);
        $w = (int) $_GET['w'];
        $h = (int) $_GET['h'];
        if (@getimagesize($img)!==FALSE) { 
        	header('Content-type: ' . 'image/jpeg');
	        header("Content-Filename: 'share'.$time.'_'.$medi->name.'.jpg'");
        	$_img = resize($img, $w, $h);
        	imagejpeg($_img);
        }
    }
    elseif (isset($_GET['dl'])) {
        if (@getimagesize($img)!==FALSE) { 
	    	header('Content-type: ' . 'image/jpeg');
	        header("Content-Length: " .(string)(filesize($img)) );
	        header('Content-Disposition: attachment; filename="share'.$time.'_'.$medi->name.'.jpg"');
	        header("Content-Filename: 'share'.$time.'_'.$medi->name.'.jpg'");
	        header("Content-Transfer-Encoding: binary\n");
	        readfile($img);
	    }
    }
    elseif (@getimagesize($img)!==FALSE) { 
    	header('Content-type: ' . 'image/jpeg');  
	    header("Content-Filename: 'share'.$time.'_'.$medi->name.'.jpg'");
    	$_img = printImg($img);
    	imagejpeg($_img);
    }
    else { 
    	header('Content-type: ' . 'text/html'); 
    	echo "image is invalid"; 
    }
    imagedestroy();
} 
/* AUTHENTICATED SHARING */
if (isset($_SESSION['dewUID']) || isset($_AUTH) || isset($_GET['dl']) || isset($_GET['demo'])) {
    // FORGE MAP SHARING
    if (isset($_GET['map']) && !empty($_GET['map'])) {
		$enc=false; $loggedU=0;	if (isset($_USER['id'])) $loggedU = $_USER['id'];
    	$mapid = (int) $_GET['map']; 
        if (!is_numeric($_GET['map'])) { 
            $enc = true; 
            $m1 = $_SQL->query("SELECT id FROM maps WHERE directURL='".$_SQL->real_escape_string($_GET['map'])."' LIMIT 1");
            if ($m1->num_rows > 0) {
                $m = $m1->fetch_object();
                $mapid = $m->id;
            }
        }
        $grabMap=$_SQL->query("SELECT * FROM maps WHERE id = '{$mapid}' LIMIT 1");
		$grabViews = $_SQL->query("SELECT * FROM views WHERE map_id = '{$mapid}' AND (user = '{$loggedU}' OR ip='{$_SERVER['REMOTE_ADDR']}')");
		$grabDLs = $_SQL->query("SELECT * FROM downloads WHERE map_id = '{$mapid}' AND (user = '{$loggedU}' OR ip='{$_SERVER['REMOTE_ADDR']}')") or die($_SQL->error);
    	if (!isset($_USER['id'])) { 
			$grabViews = $_SQL->query("SELECT * FROM views WHERE map_id = '{$mapid}' AND ip = '{$_SERVER['REMOTE_ADDR']}'"); 
			$grabDLs = $_SQL->query("SELECT * FROM downloads WHERE map_id = '{$mapid}' AND ip = '{$_SERVER['REMOTE_ADDR']}'");
		} 
		$now=time(); $VIEW=$grabViews->fetch_assoc(); $uVcount=$grabViews->num_rows; $Vth3n=$VIEW['last_viewed']; 
		$Vthen=strtotime($Vth3n); $Vdifference=$now-$Vthen;
		$totalViewsSQL = $_SQL->query("SELECT * FROM views WHERE map_id = '{$mapid}'");
		$totalViews = $totalViewsSQL->num_rows;
		$MAP=$grabMap->fetch_assoc();	$mCnt=$grabMap->num_rows;
		$DL=$grabDLs->fetch_assoc(); $uDcount=$grabDLs->num_rows;
		if ($mCnt < 1) die("Sorry, the content you requested does not exist yet");
		$Dth3n=$DL['last_viewed']; 
		$Dthen=strtotime($Dth3n); $Ddifference=$now-$Dthen;
		if ($Vdifference > 21600 AND $uVcount < 1) { 
			$_SQL->query("INSERT INTO views (map_id, user, ip, last_viewed) VALUES ('{$mapid}', '{$loggedU}', '{$_SERVER['REMOTE_ADDR']}', CURRENT_TIMESTAMP)"); 
		}
		if ($Ddifference > 43200 AND $uDcount < 1) { 
			$_SQL->query("INSERT INTO downloads (map_id, user, ip, last_viewed) VALUES ('{$mapid}', '{$loggedU}', '{$_SERVER['REMOTE_ADDR']}', CURRENT_TIMESTAMP)"); 
		} $totalDLSQL = $_SQL->query("SELECT * FROM downloads WHERE map_id = '{$mapid}'");
		$totalDLs=$totalDLSQL->num_rows;	$totalDownloads=$totalDLs;
		$path = str_ireplace('//sandbox.map', '/sandbox.map', "../DATA".$MAP['directURL']."/sandbox.map");
        if ($enc!==false) $path = str_replace('FILESHARE','DATA',base64_decode($_GET['map']));
        $type = mime_content_type($path);
        $scan = file_get_contents("http://files.dewsha.re/src/scripts/php/file_details.php?enc&map=".base64_encode($path));
        $_map = json_decode($scan); //FETCH DOWNLOAD -- debug: die(print_r($_map));
        header("Expires: 0");
        header("Pragma: no-cache");
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: pre-check=0, post-check=0, max-age=0');
        header("Content-Description: File Transfer");
        header("Content-Type: " . $type);
        header("Content-Length: " .(string)(filesize($path)) );
        header('Content-Disposition: attachment; filename="'.$_map->Name.'.map"');
        header("Content-Transfer-Encoding: binary\n");
        readfile($path);
        exit();
		if ($MAP['public'] == 'y') {
			$_SQL->query("INSERT INTO downloads (map_id, user, ip, last_viewed) VALUES ('{$mapid}', '{$loggedU}', '{$_SERVER['REMOTE_ADDR']}', CURRENT_TIMESTAMP)"); 
			$_SQL->query("UPDATE maps set downloads='".$totalDownloads."' WHERE id = '".$mapid."'") or die($_SQL->error);
			die("Download tracked! This page should not be seen, but rather processed through the backend of FileShare");
		}
		else {
			die("Not available for download by creator request.");
		}
    } 
    // GAMETYPE SHARING
    elseif (isset($_GET['variant']) && !empty($_GET['variant']) && !isset($_GET['zip'])) {
        $enc=false; $loggedU=0; if (isset($_USER['id'])) $loggedU = $_USER['id'];
        $varid = (int) $_GET['variant']; 
        if (!is_numeric($_GET['variant'])) { 
            $enc = true; 
            $v1 = $_SQL->query("SELECT id FROM files WHERE directURL='".$_SQL->real_escape_string($_GET['variant'])."' AND type='variant' LIMIT 1");
            if ($v1->num_rows > 0) {
                $v = $v1->fetch_object();
                $varid = $v->id;
            }
        }
        $grabVar=$_SQL->query("SELECT * FROM files WHERE id = '{$varid}' LIMIT 1");
        $grabViews = $_SQL->query("SELECT * FROM views WHERE mod_id = '{$varid}' AND (user = '{$loggedU}' OR ip='{$_SERVER['REMOTE_ADDR']}')");
        $grabDLs = $_SQL->query("SELECT * FROM downloads WHERE mod_id = '{$varid}' AND (user = '{$loggedU}' OR ip='{$_SERVER['REMOTE_ADDR']}')") or die($_SQL->error);
        if (!isset($_USER['id'])) { 
            $grabViews = $_SQL->query("SELECT * FROM views WHERE mod_id = '{$varid}' AND ip = '{$_SERVER['REMOTE_ADDR']}'"); 
            $grabDLs = $_SQL->query("SELECT * FROM downloads WHERE mod_id = '{$varid}' AND ip = '{$_SERVER['REMOTE_ADDR']}'");
        } 
        $now=time(); $VIEW=$grabViews->fetch_assoc(); $uVcount=$grabViews->num_rows; $Vth3n=$VIEW['last_viewed']; 
        $Vthen=strtotime($Vth3n); $Vdifference=$now-$Vthen;
        $totalViewsSQL = $_SQL->query("SELECT * FROM views WHERE mod_id = '{$varid}'");
        $totalViews = $totalViewsSQL->num_rows;
        $Var=$grabVar->fetch_assoc();   $mCnt=$grabVar->num_rows;
        $DL=$grabDLs->fetch_assoc(); $uDcount=$grabDLs->num_rows;
        if ($mCnt < 1) die("Sorry, the content you requested does not exist yet");
        $Dth3n=$DL['last_viewed']; 
        $Dthen=strtotime($Dth3n); $Ddifference=$now-$Dthen;
        if ($Vdifference > 21600 AND $uVcount < 1) { 
            $_SQL->query("INSERT INTO views (mod_id, user, ip, last_viewed) VALUES ('{$varid}', '{$loggedU}', '{$_SERVER['REMOTE_ADDR']}', CURRENT_TIMESTAMP)"); 
        }
        if ($Ddifference > 43200 AND $uDcount < 1) { 
            $_SQL->query("INSERT INTO downloads (mod_id, user, ip, last_viewed) VALUES ('{$varid}', '{$loggedU}', '{$_SERVER['REMOTE_ADDR']}', CURRENT_TIMESTAMP)"); 
        } $totalDLSQL = $_SQL->query("SELECT * FROM downloads WHERE mod_id = '{$varid}'");
        $totalDLs=$totalDLSQL->num_rows;    $totalDownloads=$totalDLs;
        $path = str_replace('FILESHARE','DATA',base64_decode($Var['directURL']));
        if ($enc!==false) $path=str_replace('FILESHARE','DATA',base64_decode($_GET['variant']));
        $type = mime_content_type($path);
        $scan = file_get_contents("http://files.dewsha.re/src/scripts/php/file_details.php?enc&variant=".base64_encode($path));
        $_var = json_decode($scan); //FETCH DOWNLOAD -- debug: die(print_r($_var));
        header("Expires: 0");
        header("Pragma: no-cache");
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: pre-check=0, post-check=0, max-age=0');
        header("Content-Description: File Transfer");
        header("Content-Type: " . $type);
        header("Content-Length: " .(string)(filesize($path)) );
        header('Content-Disposition: attachment; filename="'.$_var->FileExtension.'"');
        header("Content-Transfer-Encoding: binary\n");
        readfile($path); exit();
    }
}
else die("You need some form of authentication to use this. Either from the game session, an api key, or HaloVault user account"); ?>