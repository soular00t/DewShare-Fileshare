<?php require_once "src/scripts/php/functions.php"; //error_reporting(E_ERROR | E_WARNING | E_PARSE);
$uploadOK = false;

/* FORGED MAPS */
 // Check if attempting to publish data
if ( (isset($_POST['publish']) OR isset($_POST['personal']) && isset($_GET['map'])) && (isset($_AUTH) || isset($_GET['demo'])) ) {
    $name = $_SQL->real_escape_string(strip_tags($_POST['mapName']));
    $omap = strtolower($_SQL->real_escape_string(strip_tags($_POST['baseMap'])));
    $gtype = strtolower($_SQL->real_escape_string(strip_tags($_POST['mapGtype'])));
    $creator = $_SQL->real_escape_string(strip_tags($_POST['mapAuth']));
    $desc = $_SQL->real_escape_string($_POST['mapDesc']);
    $img = $_SQL->real_escape_string(strip_tags($_POST['mapImg']));
    $share = $_SQL->real_escape_string(str_ireplace('share.php?map=','',$_POST['share_api'])); 
    $urls=[]; $extLinks=''; //check for URLs within the body text to offer as external links
    if (preg_match_all('/https?:\/\/drive.google.com.*?(id\=|folders\/|d\/)([0-9A-Za-z-_]+)/si', $desc, $matches)) {
        foreach(array_unique($matches[2]) as $match) $urls[] = "https://drive.google.com/uc?export=download&id=" .$match;  
    }
    if (preg_match_all('/https?:\/\/mega.nz\/#!([0-9A-Za-z-_]+)/si', $desc, $_matches)) {
        foreach(array_unique($_matches[1]) as $_match) $urls[] = "https://mega.nz/#!" .$_match;  
    } 
    $DEW['PlayerID'] = (!isset($DEW)) ? 0 : $DEW['PlayerID'];
    if (!empty($name) && !empty($gtype) && isset($_AUTH)) {
        if (isset($_POST['personal']) && !isSpamming($_AUTH->id, 2, '4 MIUTE')) 
        	$_SQL->query("INSERT INTO `maps` (uid,dewid,title,map,gametype,creator,info,directURL,external_links,img,public,`date`)
          	 VALUES ('{$_AUTH->id}','{$DEW['PlayerID']}','{$name}','{$omap}','{$gtype}','{$creator}','{$desc}','{$share}','{$extLinks}','{$img}','p',CURRENT_TIMESTAMP)") or die($_SQL->error);
        elseif (isSpamming($_AUTH->id, 2, '4 MIUTE')!==TRUE) 
        	$_SQL->query("INSERT INTO `maps` (uid,dewid,title,map,gametype,creator,info,directURL,external_links,img,public,`date`)
         	 VALUES ('{$_AUTH->id}','{$DEW['PlayerID']}','{$name}','{$omap}','{$gtype}','{$creator}','{$desc}','{$share}','{$extLinks}','{$img}','y',CURRENT_TIMESTAMP)") or die($_SQL->error);
        echo "<script>alert('Content has been published globally!'); window.close();</script>";
    }
    else die('You are either not authenticated, or left a field blank.');
} //Present details page to edit upon uploading
elseif (isset($_POST["upload"]) && isset($_GET['map'])) {
	// Define basic variables
	$temp_file = $_FILES['map']['tmp_name'];
	$enc_file = "../DATA/enc/maps/".md5($temp_file.time());
	// Check if file is actually a forge map & double check the sandbox.map by scaning it with API
	if ($_FILES['map']['size'] == 61440) { $uploadOK = true; }
	$scan = file_get_contents("http://files.dewsha.re/src/scripts/php/file_details.php?upload&map=".base64_encode($temp_file)); 
	$_map = json_decode($scan);
	if ($uploadOK==true && isset($_map) && $_map->isMap==true) {
		if (isset($_GET['json'])) {
			header('Content-type: application/json; charset=utf8'); header("Access-Control-Allow-Origin: *");
			die($scan);
		}
        // rename, encrypt, offer data/options
        rename($temp_file, $enc_file);
        $enc_api = "file_details.php?enc&map=".base64_encode($enc_file);
        $share_api = "share.php?map=".base64_encode($enc_file);
        $selectGame = "<span title='Leave untouched if map is compatible with multiple gametypes.'>
        <select dir='rtl' name='mapGtype'>
        <option value='multiple'>Any</option>
        <option value='infection'>Infection</option>
        <option value='slayer'>Slayer</option>
        <option value='juggernaut'>Juggernaut</option>
        <option value='vip'>VIP</option>
        <option value='territories'>Territories</option>
        <option value='assault'>Assault</option>
        <option value='ctf'>Capture The Flag</option>
        <option value='oddball'>Oddball</option>
        <option value='koth'>King of The Hill</option>
        </select></span>";
		echo $_HEAD.
        "<form method='post'><table class='uploadDetails'><tr>
        <td width='65%' class='leftDetails'><div style='margin-top:-30;'>
        <div class='topLeftDetails'>
            <div style='position:relative; display:table-cell; left:0px;'><h2>{$_map->Name}</h2></div> <div style='position:relative; display:table-cell; right:0px;'>Uploaded on {$_map->UploadDate}</div>
        </div>
        <img onerror=\"this.src='/site/src/images/maps/unknown.png';\" class='mapImg' src=\"{$_map->mapImage}\" alt='' /><br />
        <center><input type='text' placeholder=\"Image Link - {$_map->mapImage}\" id='imgurl' name='mapImg' title=\"paste an image URL here if you'd like\" /></center>
        </div></td>
        <td class='rightDetails'><ul>
        <li class='pencil'><b>Name</b>:&nbsp;✎<input type='text' value=\"{$_map->Name}\" name='mapName' /></li>
        <li class='pencil'><b>Author</b>:&nbsp;✎<input type='text' value=\"{$_map->Author}\" name='mapAuth' /></li>
        <li><b>Base Map</b>:&nbsp;<input type='text' value=\"{$_map->map}\" name='baseMap' readonly /></li>
        <li class='pencil'><b>Gametype</b>:&nbsp;✎{$selectGame}</li>
        <li><b>Objects Left</b>:<input type='text' value=\"{$_map->TotalObjectsLeft}\" readonly /></li>
        <li class='pencil'><b>Description</b>:&nbsp;✎<br /><center><textarea id='mapDesc' name='mapDesc'>{$_map->Description}</textarea></center></li>
        <li><b>Created</b>:&nbsp;<input type='text' value=\"{$_map->CreationDate}\" readonly /></li>
        <li><center>
            <input type='hidden' name='enc_api' value='{$enc_api}' />    
            <input type='hidden' name='share_api' value='{$share_api}' />
            <input type='button' class='orange' style='cursor:pointer;' onclick=\"location='{$enc_api}';\" value='API Access' />
            <input type='button' class='orange' style='cursor:pointer;' onclick=\"location='{$share_api}';\" value='Share Link' />
            <input type='submit' name='publish' class='orange' style='cursor:pointer;' value='Publish' /></center>
        </center></li></ul>
        </td></tr></table></form>\n"; 
		print "<!-- DEBUG:\n"; print_r($scan); echo"\n-->".$_FOOT;
    } 
    else {
        echo $_HEAD."<table class='uploader'><tr>
        <td width='100%' style='font-size:xx-large; vertical-align:center; text-align:center;'>
        Uploaded content is not a valid sandbox.map file. <a href='javascript:history.back();'>Try Again</a>
        </td></tr></table></form>\n";
        print "<!-- DEBUG:\n"; print_r($scan); echo"-->".$_FOOT;
    }
} 

/* GAME VARIANTS */
 // Check if attempting to publish data
if ( (isset($_POST['publish']) OR isset($_POST['personal']) && isset($_GET['variant'])) && (isset($_AUTH) || isset($_GET['demo'])) ) {
    $name = $_SQL->real_escape_string(strip_tags($_POST['varName']));
    $creator = $_SQL->real_escape_string(strip_tags($_POST['varAuth']));
    $desc = $_SQL->real_escape_string($_POST['varDesc']);
    $share = $_SQL->real_escape_string(str_ireplace('share.php?enc&variant=','',$_POST['share_api']));
	if (!isset($DEW)) $DEW['PlayerID']=0;
    if (!empty($name) && !empty($creator) && isset($_AUTH)) {
        if (isset($_POST['personal'])) {
        	$_SQL->query("INSERT INTO `files` (uid,dewid,title,creator,info,directURL,public,type,`date`) VALUES 
        		('{$_AUTH->id}','{$DEW['PlayerID']}','{$name}','{$creator}','{$desc}','{$share}','p','variant',CURRENT_TIMESTAMP)") or die($_SQL->error);
        }
        else { 
        	$_SQL->query("INSERT INTO `files` (uid,dewid,title,creator,info,directURL,public,type,`date`) VALUES 
        		('{$_AUTH->id}','{$DEW['PlayerID']}','{$name}','{$creator}','{$desc}','{$share}','y','variant',CURRENT_TIMESTAMP)") or die($_SQL->error);
        	echo "<script>alert('Content has been published globally!'); window.close();</script>";
        }
    }
    else die('You are either not authenticated, or left a field blank.');
} //Present details page to edit upon uploading
elseif(isset($_POST["upload"]) && isset($_GET['variant'])) {
	// Define basic variables
	$temp_file = $_FILES['variant']['tmp_name'];
	$enc_file = "../DATA/enc/variants/".md5($temp_file.time());
	// Check if file is actually a variant & double check the game variant by scaning it with API
	if ($_FILES['variant']['size'] == 4096) { $uploadOK = true; }
	$scan = file_get_contents("http://files.dewsha.re/src/scripts/php/file_details.php?upload&variant=".base64_encode($temp_file)); 
	$_var = json_decode($scan);
	if ($uploadOK==true && isset($_var) && $_var->isVariant==true) {
		if (isset($_GET['json'])) {
			header('Content-type: application/json; charset=utf8'); header("Access-Control-Allow-Origin: *");
			die($scan);
		}
		// rename, encrypt, offer data/options
        $WeapPick = $_var->WeapPickup = 1 ? "Enabled" : "Disabled";
        rename($temp_file, $enc_file);
        $enc_api = "file_details.php?enc&variant=".base64_encode($enc_file);
        $share_api = "share.php?enc&variant=".base64_encode($enc_file);
		echo $_HEAD."<form method='post'><table class='uploadDetails'><tr>
        <td width='60%' class='leftDetails'>
        <img onerror=\"this.src='http://haloshare.org/css/images/variants/forge.png';\" align='center' src=\"{$_var->GameImage}\" />
        </td>
        <td class='rightDetails'>
        <ul>
        <li><b>Name:</b>&nbsp;✎<input type='text' name='varName' value=\"{$_var->Name}\" /></li>
        <li><b>Author:</b>&nbsp;✎<input type='text' title='Currently this will list the last editor of the variant when it was saved.' name='varAuth' value=\"{$_var->LastSavedBy}\" /></li>
        <li><b>Gametype:</b><input type='text' value='".ucwords($_var->GameType)."' readonly/></li>
        <li><b>Damage Dealer:</b> <input type='text' value='{$_var->DmgDealer}' readonly/></li>
        <li><b>Damage Resistance:</b> <input type='text' value='{$_var->DmgResistance}' readonly/></li>
        <li><b>Shield Multiplyer:</b> <input type='text' value='{$_var->ShieldMulti}' readonly/></li>
        <li><b>Player Speed:</b> <input type='text' value='{$_var->PlayerSpeed}' readonly/></li>
        <li><b>Weapon Pickup:</b> <input type='text' value='{$WeapPick}' readonly/></li>
        <li><b>Primary Weapon:</b> <input type='text' value='{$_var->PrimeWeapon}' readonly/></li>
        <li><b>Secondary Weapon:</b> <input type='text' value='{$_var->SecondWeapon}' readonly/></li>";
        if (stripos($_var->GameType, 'infection')!==FALSE) echo "<li><b>Alpha Prime Weapon:</b> <input type='text' value='{$_var->AlphaPrimeWeap}' readonly/></li>";
        echo "<li><b>Last Saved:</b> <input type='text' value='{$_var->LastSaveDate}' readonly/></li>
        <li><b>Uploaded:</b> <input type='text' value='{$_var->ModifiedOnServer}' readonly/></li>
        <li class='pencil'><b>Description</b>:&nbsp;✎<br /><center><textarea style='padding:5px;' id='varDesc' name='varDesc'>$_var->Description</textarea></center></li>
            <input type='hidden' name='enc_api' value='{$enc_api}' />    
            <input type='hidden' name='share_api' value='{$share_api}' />
        <li><center>
        <input type='submit' class='orange' name='personal' style='cursor:pointer;' value='Save' />&nbsp;
        <input type='submit' class='orange' name='publish' style='cursor:pointer;' value='Publish' />&nbsp;
        <a target='_blank' href='{$enc_api}'><input class='orange' style='cursor:pointer;' value='API Access' /></a>
        </center></li></ul></td></tr></table></form>\n".$_FOOT;
        print "<!--"; print_r($scan); echo"-->\n\n"; 
    } 
    else {
        echo $_HEAD."<table class='uploader'><tr>
        <td width='100%' style='font-size:xx-large; vertical-align:center; text-align:center;'>
        Uploaded content is not a valid variant file. <a href='javascript:history.back();'>Try Again</a>
        </td></tr></table></form>\n";
        print "<!-- DEBUG:\n"; print_r($scan); echo"\n-->\n".$_FOOT; 
    }
}

/* SCREENSHOTS */
 // Check if attempting to publish data
if ( (isset($_POST['publish']) OR isset($_POST['personal']) && isset($_GET['img'])) && (isset($_AUTH) || isset($_GET['demo'])) ) {
    if (!empty($_POST['imgName']) && !empty($_POST['imgAuth'])) {
        $share = str_ireplace('share.php?enc&img=','', $_SQL->real_escape_string($_POST['share_api']));
        $name = $_SQL->real_escape_string(strip_tags($_POST['imgName']));
        $auth = $_SQL->real_escape_string(strip_tags($_POST['imgAuth']));
        $desc = $_SQL->real_escape_string($_POST['caption']);
        if (isset($_POST['personal'])) {
        	$_SQL->query("INSERT INTO `media` (`uid`, `url`, `type`, `name`, `desc`, `author`, `public`, `posted`) VALUES ('{$_AUTH->id}', '{$share}' ,'s', '{$name}', '{$desc}', '{$auth}', 'p', CURRENT_TIMESTAMP)") or die($_SQL->error);
        	echo "<script>alert('Content has been posted to your personal fileshare! Edit the post to publish it globally if you wish.'); window.close();</script>";
        }
        else {
        	$_SQL->query("INSERT INTO `media` (`uid`, `url`, `type`, `name`, `desc`, `author`, `public`, `posted`) VALUES ('{$_AUTH->id}', '{$share}' ,'s', '{$name}', '{$desc}', '{$auth}', 'y', CURRENT_TIMESTAMP)") or die($_SQL->error);
        	echo "<script>alert('Content has been published globally!'); window.close();</script>";
        }
    } 
    else die('You are either not authenticated, or left a field blank.');
} //Present details page to edit upon uploading
elseif (isset($_POST["upload"]) && isset($_GET['img'])) {
    // Define basic variables
    $imgName = $_FILES['img']['name'];
    $temp_file = $_FILES['img']['tmp_name'];
    $enc_file = "../DATA/enc/screenshots/".md5($temp_file.time());
    // Check if file is actually an screenshot file from the BLAM folder by scaning it with API
    if (@getimagesize($_FILES['img']['tmp_name']) !== FALSE) $uploadOK=true;
    $scan = file_get_contents("http://files.dewsha.re/src/scripts/php/file_details.php?upload&img=".base64_encode($temp_file)); 
    $_img = json_decode($scan);
    if ($uploadOK==true && isset($_img) && $_img->isScreenshot==true) {
        if (isset($_GET['json'])) {
            header('Content-type: application/json; charset=utf8'); header("Access-Control-Allow-Origin: *");
            die($scan);
        }
        // rename, encrypt, offer data/options
        $imageData = file_get_contents($temp_file);
        rename($temp_file, $enc_file);
        $enc_api = "file_details.php?enc&img=".base64_encode($enc_file);
        $share_api = "share.php?enc&img=".base64_encode($enc_file);
        $nameofuser = (isset($_AUTH->uname)) ? $_AUTH->uname : 'Guest';
        if (isset($_GET['demo'])) $_AUTH->uname = '';
        echo $_HEAD."
		<form method='post' enctype=\"multipart/form-data\"><table class='uploadDetails'><tr>
        <td width='65%' class='leftDetails'><div style='margin-top:-30;'>
        <div class='topLeftDetails'>
            <div style='position:relative; display:table-cell; left:0px;'><h2>{$imgName}</h2></div> 
            <div style='position:relative; display:table-cell; right:0px;'>Uploaded on {$_img->UploadTime}</div>
        </div>
        ".sprintf('<img class="mapImg" src="data:image/jpeg;base64,%s" onerror="this.src=\'/site/src/images/maps/unknown.png\';" />', base64_encode($imageData))."
        </div></td>
        <td class='rightDetails'><ul>
        <li class='pencil'><b>Image Name</b>:&nbsp;✎ <input type='text' value=\"{$imgName}\" name='imgName' /></li>
        <li class='pencil'><b>File Name</b>: <input type='text' value=\"{$_img->FileName}\" name='fileName' readonly /></li>
        <li class='pencil'><b>Photographer</b>:&nbsp;✎ <input type='text' placeholder='Enter Name' value=\"{$nameofuser}\" name='imgAuth' /></li>
        <li><b>Dimensions</b>: <input type='text' placeholder='Enter Name' value=\"{$_img->PhotoWidth} x {$_img->PhotoHeight}\" name='imgSize' readonly /></li>
        <li><b>File Size</b>: <input type='text' value=\"{$_img->FileSize}\" readonly /></li>
        <li class='pencil'><b>Description</b>:&nbsp;✎<br /><center><textarea id='UmapDesc' name='caption'></textarea></center></li>
        <li><center> 
            <input type='hidden' name='enc_api' value='{$enc_api}' /> <input type='hidden' name='share_api' value='{$share_api}' id='share' /> 
            <a target='_blank' href=\"{$enc_api}\"><input class='orange' style='cursor:pointer;' value='Details API' type='button' /></a>&nbsp;
            <!--<a target='_blank' href=\"{$share_api}\">--><input data-copytarget='#share' class='orange' style='cursor:pointer;' value='Temp Link' type='button' /></a>&nbsp;
            <input type='submit' class='orange' name='personal' style='cursor:pointer;font-weight:bold;' value='Save' />&nbsp;
            <input type='submit' class='orange' name='publish' style='cursor:pointer;font-weight:bold;' value='Publish' /></center>
        </li></ul>
        </td></tr></table></form>\n";
        print "<!-- DEBUG:\n\t"; print_r($scan); echo"-->\n\n".$_FOOT; 
    } 
    else {
        if (isset($_img) && $_img->isImage==TRUE) {
            echo $_HEAD."<table class='uploader'><tr>
            <td width='100%' style='font-size:xx-large; vertical-align:center; text-align:center;'>
            This file is a valid image, but doesnt appear as if it was taken in-game using: 
            <img src='/site/src/images/icons/prntsc.png' style='margin-bottom:-5px; filter:invert(100%);' height='27' />
            <br />Only use valid screenshot files within <i>~\Pictures\Screenshots\blam</i>. - <a href='javascript:history.back();'>Try Again</a>
            </td></tr></table>"; print "<!--"; print_r($scan); echo"-->\n\n".$_FOOT; 
        } 
        else {
            echo $_HEAD."<table class='uploader'><tr>
            <td width='100%' style='font-size:xx-large; vertical-align:center; text-align:center;'>
            Uploaded content is not a valid image file. - <a href='javascript:history.back();'>Try Again</a></td></tr></table>";
            print "<!--"; print_r($scan); echo"-->\n\n".$_FOOT; 
        }
    }
} 

// Present ALL upload options
if (!isset($_POST['upload']) && !isset($_POST['publish'])) { 
    echo $_HEAD; 
    if (isset($_SESSION['key']) || !empty($_COOKIE['dewUID']) || isset($_GET['demo'])) { 
        $mapForm = "<td width='33%' style='vertical-align:center; text-align:center;'>
        <form method=\"post\" action=\"upload.php?map\" enctype=\"multipart/form-data\">
        <h2>Upload Forged Map</h2><br /><p>
        <input type=\"file\" name=\"map\" id=\"map\" /><input type=\"submit\" value=\"Upload Map\" class='orange' name=\"upload\" />
        </p></form></td>\n";
         $imgForm = "<td width='34%' style='vertical-align:center; text-align:center;'>
        <form method=\"post\" action=\"upload.php?img\" enctype=\"multipart/form-data\">
        <h2>Upload Screenshot</h2><br /><p>
        <input type=\"file\" name=\"img\" id=\"screenshot\" /><input type=\"submit\" value=\"Upload Photo\" class='orange' name=\"upload\" />
        </p></form></td>\n";
        $gameForm = "<td width='33%' style='vertical-align:center; text-align:center;'>
        <form method=\"post\" action=\"upload.php?variant\" enctype=\"multipart/form-data\">
        <h2>Upload Game Variant</h2><br /><p>
        <input type=\"file\" name=\"variant\" id=\"variant\" /><input type=\"submit\" value=\"Upload Variant\" class=\"orange\" name=\"upload\" />
        </p></form></td>\n";
        if (isset($_GET['map']))  echo "<table class='uploader'><tr>".$mapForm."</tr></table>"; 
        elseif (isset($_GET['img']))  echo "<table class='uploader'><tr>".$imgForm."</tr></table>"; 
        elseif (isset($_GET['variant']))  echo "<table class='uploader'><tr>.$gameForm"."</tr></table>"; 
        else echo "<table class='uploader'><tr>.$mapForm.$imgForm.$gameForm"."</tr></table>"; 
    } 
    else { ?>
    <table class='loginForm'><tr>
      <td width='100%' style='vertical-align:center; text-align:center;'>
      <form method="post" id="loginForm" action="http://haloshare.org/inc/authorize.php?tf" enctype="multipart/form-data"><center>
        <p style='font-size:small;'>Please authenticate through ElDewrito or HaloVault.</p>
        <p><input type="text" id='uname' name="uname" placeholder='Username' /></p>
        <p><input type="password" id='pass' name="pass" placeholder='Password' /></p>
        <p><input type="button" value="Login" class='orange' onclick="tryLogin();" name="login" id="loginClick" />&nbsp;<a href='//haloshare.org/reg.php'>
        <input type='button' class='orange' value='Register' /></a></p><div id="add_err"></div>
      </form></center></td>
    </tr></table>
    <?php }
    if (isset($_GET['loop'])) echo "<meta http-equiv=\"refresh\" content=\"0;url=/upload.php?auth\" />";
    if (isset($_GET['auth'])) echo "<meta http-equiv=\"refresh\" content=\"0;url=/upload.php\" />";
    print $_FOOT;
 }

//refreshes the page, hopefully giving the client time to authorize from HaloVault
if (isset($_GET['logSuccess'])) header("Refresh:0; url=/upload.php"); ?>