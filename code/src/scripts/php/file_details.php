<?php require_once "functions.php"; $_FILE='';
// API for forged maps
if(isset($_GET['map'])) {
	$path = $_SERVER['DOCUMENT_ROOT']."../DATA/content/maps/{$_GET['map']}";
	$f=@scandir($path,1); $fullPath=$path.$f[0]; $fullPath=str_ireplace('sandbox.map','',$fullPath)."/sandbox.map";
	//die($fullPath);
	if (isset($_GET['upload']) || isset($_GET['enc'])) {
		$fullPath=$_SERVER['DOCUMENT_ROOT'].'/'.str_replace('FILESHARE', 'DATA', base64_decode($_GET['map']));
	}
	$file = @fopen($fullPath, "rb"); $_FILE=[];  //DEBUG: die($fullPath);
	if ($file) { 
		if (isset($_GET['hex'])) { die(bin2hex(include($fullPath))); }
		fseek($file, 0x120, SEEK_SET);    $mapID = fread($file,  4);
		fseek($file, 0x14f, SEEK_SET);    $mapName = fread($file,  32);
		fseek($file, 0x170, SEEK_SET);    $mapDesc = fread($file,  128);
		fseek($file, 0x1f0, SEEK_SET);    $mapAuth = fread($file,  16);
		fseek($file, 0x242, SEEK_SET);    $mapPlacementCount = fread($file,  2);
		fseek($file, 0x244, SEEK_SET);    $mapPlacementsUsed = fread($file,  2);
		fseek($file, 0x218, SEEK_SET);    $createDate = fread($file,  32);
		fseek($file, 0x100, SEEK_SET);    $chdr = fread($file,  8);
		fseek($file, 0x208, SEEK_SET);    $mapv = fread($file,  8);
		fclose($file);
		$_FILE['ID'] = (int) int_helper::uInt16($mapID);
		$_FILE['map'] = getMapName($_FILE['ID']);
		$_FILE['Name'] = utf8_encode($mapName);
		$_FILE['Description'] = trim(utf8_encode($mapDesc));
		if (empty($_FILE['Description'])) $_FILE['Description'] = getMapQuote($_FILE['map']);
		$_FILE['Author'] = trim(utf8_encode($mapAuth), "\x00..\x1F");
		$_FILE['mapImage'] = getMapImg($_FILE['ID']);
		$_FILE['StartingObjCount'] = int_helper::uInt16($mapPlacementCount);
		$_FILE['UserObjectsPlaced'] = int_helper::uInt16($mapPlacementsUsed)-$_FILE['StartingObjCount'];
		$_FILE['TotalObjectsPlaced'] = int_helper::uInt16($mapPlacementsUsed);
		$_FILE['TotalObjectsLeft'] = 640 - $_FILE['TotalObjectsPlaced'];
		$_FILE['CreationDate'] = date("D M d, Y G:i:s", int_helper::uInt32($createDate));
		$_FILE['UploadDate'] = date("D M d, Y G:i:s", filemtime($fullPath));
		$_FILE['FileSize'] = byteSize($fullPath);
		$_FILE['isMap']=false; if ($chdr==$mapv) { $_FILE['isMap'] = true; }
	}
}// API for game variants
elseif (isset($_GET['variant'])) {
	$path = $_SERVER['DOCUMENT_ROOT']."../DATA/content/variants/{$_GET['variant']}";
	$f=@scandir($path,1); $fullPath=$path.$f[0];
	
	if (isset($_GET['upload']) || isset($_GET['enc'])) {
		$fullPath=$_SERVER['DOCUMENT_ROOT']."/".str_replace('FILESHARE', 'DATA', base64_decode($_GET['variant']));
	} //die($fullPath);
	$file = @fopen($fullPath, "rb"); $_FILE=[]; 
	if ($file) { 
		fseek($file, 0x48, SEEK_SET);     $varName = fread($file,  32);
		fseek($file, 0x64, SEEK_SET);     $varDesc = fread($file,  128);
		fseek($file, 0x0e0, SEEK_SET);    $varAuth = fread($file,  32);
		fseek($file, 0x124, SEEK_SET);    $varType = fread($file,  1);
		fseek($file, 0x2f4, SEEK_SET);    $varDmgRes = fread($file,  1);
		fseek($file, 0x2f8, SEEK_SET);    $varShieldx = fread($file,  1);
		fseek($file, 0x300, SEEK_SET);    $varDmgDeal = fread($file,  1);
		fseek($file, 0x303, SEEK_SET);    $varWeapSwap = fread($file,  1);
		fseek($file, 0x304, SEEK_SET);    $varPlaySpeed = fread($file,  1);
		fseek($file, 0x2a6, SEEK_SET);    $var1stWeap = fread($file,  1);
		fseek($file, 0x2a7, SEEK_SET);    $var2ndWeap = fread($file,  1);
		fseek($file, 0x34a, SEEK_SET);    $varAlphaPrim = fread($file, 1);
		fseek($file, 0x240, SEEK_SET);    $createDate = fread($file,  32);
		fseek($file, 0x000, SEEK_SET);    $_blf = fread($file,  4);
		fseek($file, 0x3A0, SEEK_SET);    $_eof = fread($file,  16);
		fclose($file);
		$_blf = trim(utf8_encode($_blf), "\x00..\x1F");
		$_eof = trim(utf8_encode($_eof), "\x00..\x1F");
		$desc = trim(utf8_encode($varDesc));
		$time = trim(substr($desc, strpos($desc, ", ") + 1));
		$_FILE['Name'] = utf8_encode($varName);
		$_FILE['GameType'] = strtolower(getGameType(bin2hex($varType)));
		$_FILE['Description'] = trim(str_ireplace(", ".$time, '', $desc));
		$_FILE['GameQuote'] = getGameDesc($_FILE['GameType']);
		$_FILE['LastSavedBy'] = trim(utf8_encode($varAuth), "\x00..\x1F");
		$_FILE['GameImage'] = "http://dewsha.re/site/src/images/gametypes/".strtolower($_FILE['GameType']).".png"; //temp until we get larger images locally
		$_FILE['WeapPickup'] = getWeapPick(bin2hex($varWeapSwap));
		$_FILE['DmgDealer'] = getDmgDeal(bin2hex($varDmgDeal));
		$_FILE['DmgResistance'] = getDmgResist(bin2hex($varDmgRes));
		$_FILE['ShieldMulti'] = getShieldMulti(bin2hex($varShieldx));
		$_FILE['PlayerSpeed'] = getPlayerSpeed(bin2hex($varPlaySpeed));
		$_FILE['PrimeWeapon'] = getWeap(bin2hex($var1stWeap));
		if ($_FILE['GameType']=='Infection') $_FILE['AlphaPrimeWeap'] = getWeap(bin2hex($varAlphaPrim));
		$_FILE['SecondWeapon'] = getWeap(bin2hex($var2ndWeap));
		$_FILE['LastSaveDate'] = date("D M d, Y G:i:s", int_helper::uInt32($createDate));
		$_FILE['ModifiedOnServer'] = date("D M d, Y G:i:s", filemtime($fullPath));
		$_FILE['FileSize'] = byteSize(files.dewsha.reize($fullPath));
		$_FILE['FileExtension'] = getVarExt(bin2hex($varType));
		$_FILE['isVariant']=false; if ($_blf=='_blf' && $_eof=='_eof') { $_FILE['isVariant'] = true; }
	}
}// API for screenshots
if(isset($_GET['img'])) {
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
	$fullPath=$_SERVER['DOCUMENT_ROOT']."../".str_replace('FILESHARE', 'DATA', base64_decode($_GET['img']));
	$file = @fopen($fullPath, "rb"); $_FILE=[]; 
	if ($file) { 
		if (isset($_GET['hex'])) { die(bin2hex(include($fullPath))); }
		fseek($file, 0x0, SEEK_SET);     $imgHex = fread($file,  162);
		fseek($file, 0x1B, SEEK_SET);    $imgHex2 = fread($file,  64);
		fclose($file);
		$hash = md5(utf8_encode(bin2hex($imgHex)));
		$hash2 = md5(utf8_encode(bin2hex($imgHex2)));
		$_exif = array(@exif_read_data($fullPath));
		$_FILE['FileName'] = basename($fullPath);
		$_FILE['MimeType'] = $_exif[0]['MimeType'];
		$_FILE['ImgURL'] = $_URL."/share.php?img=".$_GET['img'];		
		$_FILE['UploadTime'] = date("D M d, Y G:i:s", filemtime($fullPath));
		$_FILE['PhotoWidth'] = $_exif[0]['COMPUTED']['Width'];
		$_FILE['PhotoHeight'] = $_exif[0]['COMPUTED']['Height'];
		$_FILE['FileSize']=byteSize($fullPath); $exifSize=byteSize($_exif[0]['FileSize']);
		$_FILE['Checksum']=$hash; $_FILE['Checksum2']=$hash2;
		$_FILE['isImage']=false;  $_FILE['isScreenshot']=false;	
		if (@getimagesize($fullPath)!==FALSE) {
			$_FILE['isImage']=true;
		}
		if ($hash=='84e8ba67f243cb6ad53c427d3141a88d' && $hash2=='b3c76cdc86b8acd6e75435384cd00024') {
			if ($_FILE['isImage']!==FALSE && $_exif[0]['FileType']==2) $_FILE['isScreenshot']=true;
		} $_FILE['exif']=$_exif; //DEBUG: $_FILE['Hexes'] = utf8_encode(bin2hex($imgHex.$imgHex2));
	} 
	else echo "Image does not exist.";
}
header('Content-type: application/json; charset=utf8'); header("Access-Control-Allow-Origin: *");
if (isset($file)) { print(str_ireplace('\\u0000', "", json_encode($_FILE, JSON_PRETTY_PRINT))); } ?>