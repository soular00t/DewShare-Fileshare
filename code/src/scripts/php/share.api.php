<?php include_once "functions.php"; error_reporting(E_ERROR | E_WARNING | E_PARSE);

//UPDATE SUPERTABLE to reflect maps/media/files SQL table data
if (isset($_GET['table']) && $_GET['table']=='super') {
	$q = $_SQL->query("CREATE OR REPLACE VIEW `super_table` AS 
	SELECT id as mod_id,
		'0' as `media_id`, 
		'0' as `map_id`, 
		uid,
		dewid,
		public,
		title,
		support,
		url,
		info,
		directURL,
		votes,
		replies,
		downloads,
		type,
		edited,
		updated,
		`date`, 
		'' as `gametype`, 
		'' as `map`, 
		'' as `external_links`, 
		'' as `img`, 
		`creator` as `author` 
	FROM `files` WHERE `type`='variant' 
	UNION ALL SELECT '0' as `mod_id`, 
		'0' as `media_id`, 
		id as map_id, 
		uid, 
		dewid, 
		public, 
		title, 
		'' as `support`, 
		url, 
		info, 
		directURL, 
		votes, 
		replies, 
		downloads, 
		'map' AS `type`, 
		edited, 
		updated, 
		`date`, 
		gametype, 
		map, 
		`external_links`, 
		img, 
		`creator` as `author` 
	FROM `maps` WHERE `public` != 'n' 
	UNION ALL SELECT '0' as `mod_id`, 
		id as media_id, 
		'0' as `map_id`, 
		uid, 
		dewid, 
		public,
		`name` as `title`, 
		'' as `support`,
		'' as `url`, 
		`desc` as `info`,
		`url` as `directURL`,  
		votes, 
		replies, 
		'0' as `downloads`, 
		'screenshot' AS `type`, 
		edited, 
		updated, 
		`posted` as `date`,
		'' as `gametype`, 
		'' as `map`, 
		'' as `external_links`, 
		'' as `img`, 
		author 
	FROM `media` WHERE `type`='s';") or die($_SQL->error); //DEBUG: var_dump($q);
}

/* CHECK & ASSIGN VARIABLES FOR FILTERS IN URL ATTIBUTES */
$filter=''; $and=''; $WHERE="WHERE (`public`!='n')"; $LIMIT='LIMIT 0,50'; $_PAGE[]=''; $ORDER='ORDER BY `date` DESC';
if (isset($_GET['o']) && $_GET['o'] != 'views') {
	$oBY = $_SQL->real_escape_string($_GET['o']);
	$ORDER = "ORDER BY `{$oBY}` DESC";

}if (isset($_GET['gametype']) && !empty($_GET['gametype'])) {
	$filter = $_SQL->real_escape_string($_GET['gametype']);
	$WHERE .= " AND `map` = '{$filter}'";
}
if (isset($_GET['type']) && !empty($_GET['type'])) {
	$filter = $_SQL->real_escape_string($_GET['type']);
	if ($filter==0) $filter='map';
	if ($filter==1) $filter='variant';
	if ($filter==2) $filter='screenshot';
	$WHERE .= " AND `type` = '{$filter}'";
}
if (isset($_GET['map']) && !empty($_GET['map'])) {
	$filter = $_SQL->real_escape_string($_GET['map']);
	$WHERE .= " AND `map` = '{$filter}'";
}
if ( (isset($_GET['creator']) && !empty($_GET['creator']) ) || (isset($_GET['author']) && !empty($_GET['author'])) ) {
	$filter = $_SQL->real_escape_string($_GET['creator']);
	if (!empty($_GET['author'])) $filter=$_SQL->real_escape_string($_GET['author']);
	$WHERE .= " AND `author` = '{$filter}'";
}
if (isset($_GET['user']) && !empty($_GET['user'])) {
	$filter = $_SQL->real_escape_string($_GET['user']);
	if (!is_numeric($_GET['user'])) {
		$getuid = $_SQL->query("SELECT id FROM users WHERE `uname` = '{$filter}' OR `alias` = '{$filter}'")->fetch_object();
		$uid = (int) $getuid->id;
		$WHERE .= " AND `uid` = '{$uid}'";
	}else {
		$uid = (int) $_GET['user'];
		$WHERE .= " AND `uid` = '{$uid}'";
	}
} 
if (isset($_GET['media_id'])) {
	$filter = (int) $_GET['media_id'];
	$WHERE .= " AND `media_id` = '{$filter}'";
}
elseif (isset($_GET['map_id'])) {
	$filter = (int) $_GET['map_id'];
	$WHERE .= " AND `map_id` = '{$filter}'";
}
elseif (isset($_GET['var_id'])) {
	$filter = (int) $_GET['var_id'];
	$WHERE .= " AND `mod_id` = '{$filter}'";
}
/* PAGINATE */
$p = (isset($_GET['p'])) ? (int) $_GET['p'] -1 : 0;
$r = (isset($_GET['r'])) ? (int) $_GET['r'] : 100; 
$s = ($p) ?  ($p * $r) : 0;
$LIMIT = "LIMIT {$s},{$r}";
$tcount = $_SQL->query("SELECT title FROM `super_table` {$WHERE}") or die($_SQL->error);
$total = $tcount->num_rows;
$page['totalEntries'] = "$total";
$page['currentPage'] = "".($p+1)."";
$begResult = $s + 1;
$endResult = $s + $r; if ($endResult > $total) { $endResult = $total; }
$tc = $_SQL->query("SELECT uid FROM `super_table` {$WHERE} {$LIMIT}") or die($_SQL->error); 
$t = $tc->num_rows;
$page['EntriesOnPage'] = "$t";
if ($page['EntriesOnPage'] > 0 && $page['EntriesOnPage'] < $r) {$page['pagesPossible'] = $page['currentPage'];}
else { $page['pagesPossible'] = "".ceil($total / $r).""; }
$page['nextPage'] = true; 
$page['prevPage'] = true; 
if ( (isset($_GET['p']) && $page['pagesPossible'] == $_GET['p']) || ($page['pagesPossible'] == $p) ) $page['nextPage'] = false; 
if ( (isset($_GET['p']) && $_GET['p'] == 1) || ($p == 0) ) $page['prevPage'] = false; 
$page['results'] = "".$begResult."-".$endResult." out of ".$page['totalEntries'];
$_PAGE['PAGES'][] = $page ? $page : array();

$sql = $_SQL->query("SELECT * FROM `super_table` {$WHERE} {$ORDER} {$LIMIT}") or die($_SQL->error);
if (!isset($_GET['map']) && !isset($_GET['creator']) && !isset($_GET['user']) 
&& !isset($_GET['gametype']) && isset($_GET['search']) && preg_match('/^[a-z0-9.\-\_]+$/i', $_GET['search'])) { 
	$sql = $_SQL->query("SELECT * FROM `super_table` WHERE 
		`title` LIKE '%{$_GET['search']}%' OR 
		`author` LIKE '%{$_GET['search']}%' OR 
		`map` LIKE '%{$_GET['search']}%' OR 
		`gametype` LIKE '%{$_GET['search']}%' {$ORDER} {$LIMIT}") 
	or die($_SQL->error);	
	if ($sql->num_rows < 1) { 	$sql = $_SQL->query("SELECT * FROM `super_table` {$WHERE} {$ORDER} {$LIMIT}") or die($_SQL->error);	}
} 

/* FETCH MULTIPLE TABLES AS SUPER TABLE */
$super_Table=[];  $sTable=[];
while ($superTable = $sql->fetch_assoc())	{
	$super_Table["type"] = strtoupper($superTable["type"]);
	$super_Table["mod_id"] = (int) $superTable["mod_id"];
	$super_Table["media_id"] = (int) $superTable["media_id"];
	$super_Table["map_id"] = (int) $superTable["map_id"];
	$super_Table["uid"] = (int) $superTable["uid"];
	$super_Table["dewid"] = $superTable["dewid"];
	$super_Table["author"] = $superTable["author"];
	$sub=$_SQL->query("SELECT uname FROM users WHERE id = '{$superTable['uid']}' LIMIT 1")->fetch_object();
	$super_Table['submitter'] = $sub->uname;
	$super_Table["public"] = $superTable["public"];
	$super_Table["title"] = $superTable["title"];
	$super_Table["support"] = $superTable["support"];
	$super_Table["url"] = $superTable["url"];
	$super_Table["directURL"] = $superTable["directURL"];
	$super_Table['votes'] = $superTable["votes"];
	$super_Table['replies'] = $superTable["replies"];
	$super_Table['downloads'] = $superTable["downloads"];
	$super_Table['info'] = str_replace("\\\"", '"', htmlentities($superTable['info']));
	// FORGED MAPS
	if ($superTable['type'] == 'map') {
		$VIEWS = $_SQL->query("SELECT `id` FROM views WHERE map_id = '{$superTable['map_id']}'");
		$super_Table['views'] = $VIEWS->num_rows;
		$super_Table["img"] = $superTable["img"];
		$super_Table['url'] = $superTable['url'];
		$super_Table["external_links"] = $superTable["external_links"];
		$super_Table['thread'] = "http://haloshare.org/forge.php?id=".$superTable['map_id'].""; $forgeIMG = $superTable['img'];
		 if (empty($superTable['img'])) $forgeIMG = "http://haloshare.org/css/images/".preg_replace('/\s+/', '', $superTable['map']).".jpg";
		 if ($superTable['img'] != Thumb($superTable['img'])) $super_Table['thumbnail'] = Thumb($superTable['img']);
		$super_Table['img'] = $forgeIMG;
		$forgecaption = str_ireplace('Posted on HaloShare', '', str_ireplace(':controller:','🎮',removeBB($superTable['info'])));
		$super_Table['caption'] = preg_replace('/:[\s\S]+?:/', '', $forgecaption);
		 if (isset($_GET['map_id']) || isset($_GET['id'])) $superTable['info'] = nl2br(bb_parse(htmlspecialchars($superTable['info'])));
		$super_Table['gametype'] = ucwords($superTable['gametype']); $isDrive = false;
		 if (stripos($superTable['title'],':')!==FALSE) $super_Table['subTitle'] = strtok($superTable['title'], ':');
		$super_Table['map'] = ucwords($superTable['map']);
		$forgeV = $_SQL->query("SELECT id FROM views WHERE map_id = '".$superTable['map_id']."'");
		$super_Table['views'] = "$forgeV->num_rows";
		$toRemove = (isset($super_Table['subTitle'])) ? $super_Table['subTitle'] : '';
		$super_Table['title'] = str_replace($toRemove, '', $superTable['title']);
		$super_Table["gametype"] = $superTable["gametype"];
		$super_Table["map"] = ucwords($superTable["map"]);
		if (!empty($superTable['directURL'])) { 
			if (stripos($superTable['directURL'], "content/maps") !== FALSE) {
				$path = str_ireplace('//sandbox.map', '/sandbox.map', "".$superTable['directURL']."/sandbox.map");
				$xtra = json_decode(file_get_contents("http://files.dewsha.re/src/scripts/php/file_details.php?enc&map=".base64_encode($path)));
				if ($xtra==null) $super_Table['forgeData']="http://files.dewsha.re/src/scripts/php/file_details.php?enc&map=".base64_encode($path);
				$super_Table['directURL']="http://files.dewsha.re/share.php?map=".$superTable['map_id'];
			} 
			else {
				$xtra = json_decode(file_get_contents("http://files.dewsha.re/src/scripts/php/file_details.php?enc&map=".$superTable['directURL']));
				if ($xtra==null) $super_Table['forgeData']="http://files.dewsha.re/src/scripts/php/file_details.php?enc&map=".$superTable['directURL'];
				$super_Table['directURL']="http://files.dewsha.re/share.php?map=".$superTable['directURL'];
			} /*
			if ($xtra) {
				$super_Table['forgeData']['mapID']=$xtra->ID; 
				$super_Table['forgeData']['FileSize']=$xtra->FileSize; 
				$super_Table['forgeData']['StartingObjCount']=$xtra->StartingObjCount; 
				$super_Table['forgeData']['UserObjectsPlaced']=$xtra->UserObjectsPlaced; 
				$super_Table['forgeData']['TotalObjectsPlaced']=$xtra->TotalObjectsPlaced; 
				$super_Table['forgeData']['TotalObjectsLeft']=$xtra->TotalObjectsLeft; 
				$super_Table['forgeData']['LastSaved']=$xtra->CreationDate; 
				$super_Table['forgeData']['LastEditor']=$xtra->Author; 
			}*/
		} 
		$super_Table['screenshotData'] = null;
		$super_Table['variantData'] = null;
		$super_Table['quote'] = getMapQuote($superTable['map']);
	}
	// GAMETYPE VARIANTS (currently brokem, not sure why)
	elseif ($superTable['type'] == 'variant') {
		$VIEWS = $_SQL->query("SELECT `id` FROM views WHERE mod_id = '{$superTable['mod_id']}'");
		$super_Table['views'] = $VIEWS->num_rows;
		$super_Table['url'] = $superTable['url'];
		$super_Table['thread'] = "http://haloshare.org/files.php?id=".$superTable['mod_id'].""; $fileIMG = $superTable['img'];
		$fileType = preg_replace('/[^ \w0-9a-zA-Z_-]/', '', $superTable['type']);
		$modimg = "http://haloshare.org/css/images/file_icons/".$fileType."/".$_AUTH->theme.".png";  $super_Table['img']=$modimg;
		$filePub = removeBB($superTable['info']); $filePub=str_replace('Posted on HaloShare','',$filePub);
		$super_Table['caption'] = $filePub;
		 if (isset($_GET['id']) OR isset($_GET['mod_id'])) $super_Table['info'] = bb_parse(htmlentities($fileI));
		 if (stripos($superTable['title'],':')!==FALSE) $super_Table['subTitle'] = strtok($superTable['title'], ':');
		$fileV = $_SQL->query("SELECT * FROM views WHERE mod_id = '".$superTable['mod_id']."'");
		$super_Table['views'] = "$fileV->num_rows";
		$toRemove = $super_Table['subTitle'] ? $super_Table['subTitle'] : '';
		$super_Table['title'] = str_replace($toRemove, '', $superTable['title']);
		if (!empty($superTable['directURL'])) {
			if (stripos($superTable['directURL'], "content/variants") !== TRUE) {
				$xtra = json_decode(file_get_contents("http://files.dewsha.re/src/scripts/php/file_details.php?enc&variant={$superTable['directURL']}"));
				if ($xtra==null) $superTable['variantData']="http://files.dewsha.re/src/scripts/php/file_details.php?enc&variant={$superTable['directURL']}";
			} 
			else {
				$xtra = json_decode(file_get_contents("http://files.dewsha.re/src/scripts/php/file_details.php?enc&variant=".base64_encode($superTable['directURL'])));
				if ($xtra==null) $superTable['variantData']="http://files.dewsha.re/src/scripts/php/file_details.php?enc&variant=".base64_encode($superTable['directURL']);
				if((isset($_USER['name'])) && ($superTable['public'] != 'r' || $_USER['group'] > 2)) 
					$super_Table['directURL']="http://files.dewsha.re/share.php?variant=".$superTable['directURL'];
			}
			if ($xtra) {
				$super_Table['gametype'] = $xtra->GameType;
				$super_Table['quote'] = $xtra->GameQuote;
				$super_Table['variantData']=$xtra;
			} else {
				$super_Table['gametype'] = "Forge";
				$super_Table['quote'] = "This mainly exists as a way to submit gametypes, or variant packs.";
			}
			$super_Table['directURL'] = "http://files.dewsha.re/share.php?variant=".$superTable['mod_id'];
		}
		$super_Table['screenshotData'] = null;
		$super_Table['forgeData'] = null;
	} 
	// SCREENSNOTS & VIDEO
	elseif ($superTable['type'] == 'screenshot' || $superTable['type'] == 'video') {
		$VIEWS = $_SQL->query("SELECT `id` FROM views WHERE media_id = '{$superTable['media_id']}'");
		$VOTES = $_SQL->query("SELECT `id` FROM notifications WHERE media_id = '{$superTable['media_id']}' AND type= 'vote'");
		$CMNTS = $_SQL->query("SELECT `id` FROM notifications WHERE media_id = '{$superTable['media_id']}' AND type= 'media'");
		$super_Table['views'] = $VIEWS->num_rows;
		$super_Table['votes'] = $VOTES->num_rows;
		$super_Table['replies'] = $CMNTS->num_rows;
		$super_Table['downloads'] = null;
		 if ($superTable['type']=='screenshot') $super_Table['type'] = "SCREENSHOT";
		 else $super_Table['type'] = "VIDEO";
		if (stripos($superTable['title'],':')!==FALSE) $super_Table['subTitle'] = strtok($superTable['title'], ':');
		$toRemove = (isset($super_Table['subTitle'])) ? $super_Table['subTitle'] : '';
		$super_Table['title'] = str_replace($toRemove, '', $superTable['title']);
		$super_Table['thread'] = "http://haloshare.org/media.php?id=".$superTable['media_id'];
		$fsData = json_decode(file_get_contents('http://files.dewsha.re/src/scripts/php/file_details.php?enc&img='.$superTable['directURL']), true);
		if ($fsData) {
			$super_Table['screenshotData']['MimeType'] = (isset($fsData['exif']['MimeType'])) ? $fsData['exif']['MimeType'] : $fsData['MimeType'] ? $fsData['MimeType'] : '';
			$super_Table['screenshotData']['PhotoWidth'] = $fsData['PhotoWidth'];
			$super_Table['screenshotData']['PhotoHeight'] = $fsData['PhotoHeight'];
			$super_Table['screenshotData']['FileSize'] = $fsData['FileSize'];
		}
		$super_Table['directURL'] = "http://files.dewsha.re/share.php?img=".$superTable['media_id'];
		$super_Table['variantData'] = null;
		$super_Table['forgeData'] = null;
		$super_Table['downloads'] = null;
		$super_Table['quote'] = "Halo Online screenshot, taken in-game using: Game.TakeScreenShot";
		$super_Table['caption'] = strip_tags(removeBB($superTable['info']));
		$super_Table['info'] = null;
		$super_Table['img'] = null;
		$super_Table['thumbnail'] = $super_Table['directURL']."&w=204&h=116";
		$super_Table['gametype'] = "";
	} 
	$super_Table["date"] = $superTable["date"];
	$super_Table['edited'] = $superTable['edited'];
	$super_Table["activity"] = $superTable["updated"];
	$sTable['ENTRIES'][] = $super_Table;
}
if (isset ($_GET['o']) && $_GET['o'] == 'views') {
	foreach ($sTable as $key => $value) { $views[$key] = (int) $value['views']; }
	array_multisort($views, SORT_DESC, $sTable);
}

// Clean up the JSON output
$xsTable = array_merge($_PAGE, $sTable);
$ssTable = json_encode($xsTable, JSON_PRETTY_PRINT);
$ssTable = str_ireplace('"variantData": null,', '', $ssTable);
$ssTable = str_ireplace('"forgeData": null,', '', $ssTable);
$ssTable = str_ireplace('"screenshotData": null,', '', $ssTable);
$ssTable = str_ireplace('"caption": null,', '', $ssTable);
$ssTable = str_ireplace('"downloads": null,', '', $ssTable);
$ssTable = str_ireplace('"caption": null,', '', $ssTable);
$ssTable = str_ireplace('"info": null,', '', $ssTable);
$ssTable = str_ireplace('"fileQuote": null,', '', $ssTable);
$ssTable = str_ireplace('"thumbnail": null,', '', $ssTable);
$ssTable = str_ireplace('"img": null,', '', $ssTable);
$xTable = str_ireplace('"map_id": 0,', '', $ssTable);
$xTable = str_ireplace('"mod_id": 0,', '', $xTable);
$xTable = str_ireplace('"media_id": 0,', '', $xTable);
$xTable = str_ireplace('"dewid": 0,', '', $xTable);
$xTable = str_ireplace('"uid": 0,', '', $xTable);
$xTable = str_ireplace('"map": "",', '', $xTable);
$xTable = str_ireplace('"gametype": "",', '', $xTable);
$xTable = str_ireplace('"img": "",', '', $xTable);
$xTable = str_ireplace('"support": "",', '', $xTable);
$xTable = str_ireplace('"url": "",', '', $xTable);
$xTable = str_ireplace('"url": ', '"mirror": ', $xTable);
$xTable = str_ireplace('"external_links": "",', '', $xTable);
$xTable = str_ireplace('"0": "",', '', $xTable);
$xTable = str_ireplace('"edited": "",', '"edited": "0000-00-00 00:00:00",', $xTable);
header('Content-type: application/json; charset=utf8'); header("Access-Control-Allow-Origin: *");
print_r(json_encode(json_decode($xTable), JSON_PRETTY_PRINT)); ?>