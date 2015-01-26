<?php

/**
 * Odevzdavac uloh, primarne na BI-CAO, v1.2
 * 2012-2015, Jan Pospisil, FIT, CVUT
 * jan.pospisil@fit.cvut.cz
 * fosfor.software@seznam.cz
 *
 * https://github.com/AakaFosfor/fit-odevzdavac
 * pull requesty vitam!
 */

if(!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on") {
	header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
	die();
}

// SQLite DB file
define('LOGGER_DB_FILE', './data/log.db');

// maximum file size, bytes
$maxSize = 512 * 1024;

$lessons = array(
		'0730' => '1',
		'0915' => '3',
		'1100' => '5',
		'1245' => '7',
		'1430' => '9',
		'1615' => '11',
		'1800' => '13',
		'2030' => '15'
	);
ksort($lessons);


$teachers = array();
$teachers[] = 'username1';
$teachers[] = 'username2';


function getVariable($name, $number = false) {
	$result = '';
	if (isset($_COOKIE[$name])) $result = $_COOKIE[$name];
	if (isset($_GET[$name])) $result = $_GET[$name];
	if (isset($_POST[$name])) $result = $_POST[$name];
	if ($number)
		$result *= 1;
	return $result;
}

function saveFile($file, $user) {
	global $lessons, $maxSize, $messageType;

	if ($file['size'] > $maxSize)
		return 'Chyba ve velikosti souboru!';
	$destDir = './data/'.date('N').'/';
	$lessonId = '0';
	foreach ($lessons as $time => $id) {
		if ($time < date('Hi'))
			$lessonId = $id;
	}
	$destDir .= $lessonId.'/';
	if (!is_dir($destDir))
		if (!@mkdir($destDir, 0777, true))
			return 'Chyba adresáře!';
	$newName = $user.'_'.time().'_'.date('Y-m-d_H-i-s').'.nb';
	if (!@move_uploaded_file($file['tmp_name'], $destDir.$newName))
		return 'Chyba při uložení!';
	logAccess($user, $file['name']);
	$messageType = 'OK';
	return 'Soubor v pořádku přijat.';
}

function getFiles($id) {
	if (!preg_match('#^([1-7])/([0-9]{1,2})$#', $id))
		return 'Špatné ID! (má být ve formátu "den/první-hodina-cvičení", např. "2/5" pro úterní hodinu od 11:00)';
	$dirPath = './data/'.$id.'/';
	if (!is_dir($dirPath))
		return 'Chyba adresáře! (Byl již odevzdán nějaký soubor pro toto cvičení?)';
	$zipName = tempnam(sys_get_temp_dir(), 'CAO');
	$zip = new ZipArchive();
	if (!($zipRes = $zip->open($zipName, ZipArchive::CREATE)))
		return 'Chyba v ZIPu!';
	$dir = opendir($dirPath);
	while (($file = readdir($dir)) !== false) {
		if (substr($file, 0, 1) == '.')
			continue;
		$zip->addFile($dirPath.$file, $file);
	}
	$zip->close();
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename=cao-'.str_replace('/', '_',$id).'_'.time().'.zip');
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . filesize($zipName));
	@ob_clean();
	@flush();
	readfile($zipName);
	unlink($zipName);
	die();
}

// made by Adam Horcica
function logAccess($user, $file_name) {  
    $db = new SQLite3(LOGGER_DB_FILE);
    
    $cmd = $db->prepare("INSERT INTO log (date, ip, user, filename) VALUES (:date, :ip, :user, :filename)");
    
    $cmd->bindValue(':date', time(), SQLITE3_INTEGER);
    $cmd->bindValue(':ip', $_SERVER['REMOTE_ADDR'], SQLITE3_TEXT);
    $cmd->bindValue(':user', $user, SQLITE3_TEXT);
    $cmd->bindValue(':filename', $file_name, SQLITE3_TEXT);
    
    $cmd->execute();
}
	
$pass = getVariable('pass');
$user = getVariable('user');
$id = getVariable('id');

$message = '';
$messageType = 'KO';

// http://code.activestate.com/recipes/101525-ldap-authentication/
if ($user != '' && $pass != '') {
	$message = 'Neplatné přihlášení - špatné jméno a/nebo heslo!';
	$ds = @ldap_connect('ldap.fit.cvut.cz');
	if (!$ds)
		$message = 'Chyba s LDAP serverem!';
	else {
		$r = @ldap_search($ds, 'ou=People,o=fit.cvut.cz', 'uid='.$user);
		if ($r) {
			$result = @ldap_get_entries($ds, $r);
			if (isset($result[0]) && $result[0]) {
				if (@ldap_bind($ds, $result[0]['dn'], $pass)) {
					if (array_search($user, $teachers) !== false)
						$message = getFiles($id);
					else
						$message = saveFile($_FILES['file'], $user);
				}
			}
		} else
			$message = 'Chyba s LDAP serverem!';
	}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title>BI-CAO odevzdávač</title>
	<meta name="generator" content="EditPlus">
	<meta name="author" content="Aaka Fosfor; FOSFOR software; fosfor.software@seznam.cz; 2012">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="hl.css">
</head>
<body>
	<script type="text/javascript">
	<!--
		function init() {
			document.getElementById('cvicici-input').style.display = 'none';
		}

		function show() {
			document.getElementById('cvicici-input').style.display = '';
			document.getElementById('cvicici-info').style.display = 'none';
			return false;
		}
	//-->
	</script>
	<div>
		<h1>BI-CAO odevzdávač</h1>
		<form method="post" action="" enctype="multipart/form-data">
			<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $maxSize; ?>">
			<table>
				<tr><td style="text-align: right;">Login:</td><td style="text-align: left;"><input type="text" name="user"></td></tr>
				<tr><td style="text-align: right;">Heslo ČVUT:</td><td style="text-align: left;"><input type="password" name="pass"></td></tr>
				<tr><td style="text-align: right;">Soubor k odeslání:</td><td style="text-align: left;"><input type="file" name="file"> (max. <?php echo ($maxSize/1024); ?> kB)</td></tr>
				<tr><td colspan="2"><input type="submit" value="Odeslat soubor"></td></tr>
				<tr id="cvicici-info"><td colspan="2"><a href="#" onclick="return show();">Jsem cvičící.</a></td></tr>
				<tr id="cvicici-input"><td style="text-align: right;">ID:</td><td style="text-align: left;"><input type="text" name="id"> <span style="color: red;">(vyplňuje jen cvičící!)</span></td></tr>
			</table>
		</form>
		<?php
			if ($message) {
				if ($messageType == 'OK')
					echo '<div id="message" class="ok">';
				else
					echo '<div id="message" class="ko">';
				echo $message;
				echo '</div>';
			}
		?>
	</div>
	<script type="text/javascript">
	<!--
		init();
	//-->
	</script>
</body>
</html>