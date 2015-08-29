<?php
function getinfo($option='all'){ 
$remoteinfo="================= DETAILS ================ 
Time => ".date("r")."
IP => ". $_SERVER['REMOTE_ADDR']."
Browser => " . $_SERVER['HTTP_USER_AGENT'] . "
File => " . $_SERVER['PHP_SELF'] . "
Server => " . $_SERVER['SERVER_NAME'] . "
Method => " . $_SERVER['REQUEST_METHOD'] . "
Query => " . $_SERVER['QUERY_STRING'] . "
HTTP Language => " . $_SERVER['HTTP_ACCEPT_LANGUAGE'] . "
Referer => " . $_SERVER['HTTP_REFERER'] . "
URL => " . $_SERVER['REQUEST_URI']."\n\n";

$post = "============== POST VARIABLES ============ \n".
(($_POST) ? print_r($_POST, true) . "\n\n" : $post="POST IS EMPTY\n\n");

$files = "============== FILES VARIABLES ============ \n".
(($_FILES) ? print_r($_FILES, true) . "\n\n" : $files="FILES IS EMPTY\n\n");

$cookies = "============ COOKIE VARIABLES =========== \n".
(($_COOKIE) ? print_r($_COOKIE, true) . "\n\n": $cookies ="NO COOKIES\n\n");

$sql=(mysql_error()) ? "================ SQL ERROR ================ \n".mysql_error() : '';

$backtrace="================== PHP DEBUG ================ \n".print_r(debug_backtrace(),true); 

$lasterror="============== PHP LAST ERROR ============ \n".print_r(error_get_last(),true);
	
		switch($option){
				case 'url':
						$info = "URL: {$_SERVER['REQUEST_URI']}";
						break;
						
				case 'post':
						$info = $post;
						break;
		
				case 'cookies':
						$info = $cookies;
						break;
						
				case 'remote':
						$info = $remoteinfo;
						break;
						
				case 'php':
						$info = $lasterror.$backtrace;
						break;
						
				case 'sql':
						$info = $sql;
						break;

				case 'files':
						$info = $files;
						break;
						
				case 'all':
				default:
						$info = $url.$post.$remoteinfo.$cookies.$files;
						break;
		}
		
		return $info;

} 

if (!function_exists("kill")) {
function kill($description=false, $type='norecord') {
	global $basedir, $external_server, $sysadmin_email;
	$basedir = defined("BASEDIR") ? BASEDIR : URL;
	if (!$sysadmin_email) {
	  $sysadmin_email = SYSADMIN;
	}

	/* Following types exist: 
	1: No Record, used when a MySQL record is requested but does not exist. 
	2: Spam, used when spam terms are used in sending a form
	3: MySQL Error, used when a Query does not run properly
	4: Mail Injection
	8: Other Critical Errors
	9: Other non-critical Errors
	*/
	
	$convert_type = array(
	  'norecord' => 1,
	  'onpage' => 9,
	  'inline' => 9,
	  '404' => 1,
	  'spam' => 2,
	  'mysql' => 3,
	  'other' => 9,
	  'mail injection' => 4,
	  'critical' => 8,
	);

	// Only log this in central db when in production, and when not working on an external, 
	// non-IBIS server

	if (!file_exists("/sites/local") && !$external_server) {
	  require '/home/global/crons/conn/online.logs.php';
	  $db = new DB(SERVER_LOGS, USER_LOGS, PASSWORD_LOGS, DATABASE_LOGS, "mailerror");

	  $post = array(
		'type' => $convert_type[$type] ? (int)$convert_type[$type] : 4,
		'description' => html($description),
		'website' => mysql_clean($basedir),
		'url' => mysql_clean('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']),
		'post' => mysql_clean(getinfo("post")),
		'cookies' => mysql_clean(getinfo("cookies")),
		'files' => mysql_clean(getinfo("files")),
		'referer' => mysql_clean($_SERVER['HTTP_REFERER']),
		'browser' => mysql_clean($_SERVER['HTTP_USER_AGENT']),
		'path' => mysql_clean($_SERVER['SCRIPT_FILENAME']),
		'ip' => mysql_clean($_SERVER["REMOTE_ADDR"]),
	  );

	  // MySQL errors should be sent immediately; the other errors are grouped and sent through a 
	  // cron when a certain number of errors is reached
	  if ($type == 'mysql' || $type == "critical") {
		mail($sysadmin_email, "MySQL Error - $basedir", $description . "\n\n" . getinfo(), "From:" . EMAIL);
		$post['sent'] = 1;
	  }

	  $db->insert("errors",$post, false, 'mailerror');
	} elseif ($external_server) {
	  mail($sysadmin_email, "Registered Error - $basedir", $description . "\n\n" . getinfo(), "From:" . EMAIL);
	}

	switch ($type) {

	  case 'onpage':
		echo "<h3>{$description}</h3>";
		die();
		break;

	  case 'error':
		$showerror=true;
		break;

	  case 'inline' :
		$print = $description;
		header("Location: " . URL . "error/?error=" . urlencode($print));
		exit();
		break;

	  default:
	  // Set this condition to whatever ensures you are working locally
		if (file_exists("/sites/local")) {
		  header("HTTP/1.0 404 Not Found");
		  echo '<p><strong>' . nl2br($description) .'</strong></p>' . nl2br(getinfo());
		  die();
		} else {
		  header("HTTP/1.0 404 Not Found");
		  header("Location: {$basedir}error.php");
		  die();
		}
	 }
}
}

//OPTIMIZED OCT 2010 FOR NOT OPENING CONNECTION PROBLEM
//THIS SHOULD BE IMPROVED
function mysql_clean($string, $type=false) {
  if (DATABASESYSTEM == "postgre") {
	return $string;
  }
  require_once CONNECTION; 
  $string = (get_magic_quotes_gpc()) ? stripslashes($string) : $string;
	$string = mysqli_real_escape_string(mysqli_connect(SERVER,USER,PASSWORD,DATABASE),$string);
	
	if ($type == "wildcard")
	{
		return addcslashes($string, "%_"); 
	}
	else
	{
		return "'{$string}'";
	}
}

function html($string,$clean=true) {
  if (!mb_check_encoding($string, "UTF-8")) {
	$string = utf8_decode($string);
  }
  $convert = array('’' => "'", '“' => "'", '”' => "'", '–' => "-");
  $string = strtr($string, $convert);
  $string = nl2br(htmlspecialchars($string, ENT_QUOTES, "UTF-8"));
	if ($clean) {
		$string = mysql_clean($string);
	}
	return($string);
}

function html_clean($string,$clean=true) {
  if (!is_defined(TAGS)) {
	die("Please define allowed tags first");
  }
  $string = strip_tags($string, TAGS);
	if ($clean) {
		$string = mysql_clean($string);
	}
	return($string);
}

function smartcut($string, $length,$suffix="...") {
  if (!$string) {
	return $string;
  }
  $string_length = strlen($string);
  if ($string_length <= $length) {
	return $string;
  }
  $pos = strpos($string, ' ', $length);
  if ($string_length > $length && !$pos) {
	$result = substr($string, 0, $length);
  } else {
	$result = substr($string,0,$pos);
  }
  if ($result != $string) {
	$result .= $suffix;
  }
  return $result;
}

function get_file_extension($source){
	return strtolower(substr($source, strrpos($source, '.') + 1));
}

function check_mail($email)
{        
  return (filter_var($email,FILTER_VALIDATE_EMAIL)) ? true : false;
}

// Fix it at some point, please. 
function flatten($string) {
	$string = utf8_decode($string);
	$string = remove_accents($string);
	$array_rewrite = array("amp-amp" => "and", "-s-" => "s-", "-amp-oacute-" => "o", "amp-" => "");
	$string = strtolower($string);
	$string = preg_replace("/&(.)(acute|cedil|circ|ring|tilde|uml);/", "$1", $string);
	$string = preg_replace("/([^a-z0-9]+)/", "-", $string);
	$string = trim($string, "-");
	$string = strtr($string, $array_rewrite);
	
	return $string;
}


function seems_utf8($str)
{
	$length = strlen($str);
	for ($i=0; $i < $length; $i++) {
		$c = ord($str[$i]);
		if ($c < 0x80) $n = 0; # 0bbbbbbb
		elseif (($c & 0xE0) == 0xC0) $n=1; # 110bbbbb
		elseif (($c & 0xF0) == 0xE0) $n=2; # 1110bbbb
		elseif (($c & 0xF8) == 0xF0) $n=3; # 11110bbb
		elseif (($c & 0xFC) == 0xF8) $n=4; # 111110bb
		elseif (($c & 0xFE) == 0xFC) $n=5; # 1111110b
		else return false; # Does not match any model
		for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
			if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
				return false;
		}
	}
	return true;
}

/**
 * Converts all accent characters to ASCII characters.
 *
 * If there are no accent characters, then the string given is just returned.
 *
 * @param string $string Text that might have accent characters
 * @return string Filtered string with replaced "nice" characters.
 */
function remove_accents($string) {
	if ( !preg_match('/[\x80-\xff]/', $string) )
		return $string;

	if (seems_utf8($string)) {
		$chars = array(
		// Decompositions for Latin-1 Supplement
		chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
		chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
		chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
		chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
		chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
		chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
		chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
		chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
		chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
		chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
		chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
		chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
		chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
		chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
		chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
		chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
		chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
		chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
		chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
		chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
		chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
		chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
		chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
		chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
		chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
		chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
		chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
		chr(195).chr(191) => 'y',
		// Decompositions for Latin Extended-A
		chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
		chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
		chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
		chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
		chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
		chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
		chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
		chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
		chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
		chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
		chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
		chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
		chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
		chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
		chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
		chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
		chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
		chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
		chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
		chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
		chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
		chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
		chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
		chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
		chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
		chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
		chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
		chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
		chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
		chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
		chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
		chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
		chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
		chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
		chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
		chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
		chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
		chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
		chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
		chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
		chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
		chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
		chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
		chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
		chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
		chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
		chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
		chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
		chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
		chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
		chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
		chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
		chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
		chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
		chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
		chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
		chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
		chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
		chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
		chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
		chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
		chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
		chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
		chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
		// Euro Sign
		chr(226).chr(130).chr(172) => 'E',
		// GBP (Pound) Sign
		chr(194).chr(163) => '');

		$string = strtr($string, $chars);
	} else {
		// Assume ISO-8859-1 if not UTF-8
		$chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
			.chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
			.chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
			.chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
			.chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
			.chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
			.chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
			.chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
			.chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
			.chr(252).chr(253).chr(255);

		$chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

		$string = strtr($string, $chars['in'], $chars['out']);
		$double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
		$double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
		$string = str_replace($double_chars['in'], $double_chars['out'], $string);
	}

	return $string;
}

function mail_clean($string, $type=NULL, $msafe="no") // check body elements of message
{
	$bad_strings = array("content-","mime-","multipart/mixed","bcc:","acc:","@yourdomain.com", ".txt");
	$spam_strings = array("a href", "[url]", "viagra");
	
	foreach($bad_strings as $bad_string)
	{
			if(stristr($string, $bad_string))
					kill ("bsafe injection", "mail injection");
	}
	
	foreach($spam_strings as $bad_string)
	{
			if(stristr($string, $bad_string))
					kill ("This is a spam message", "spam");
	}
	
	if ($type != "box") // if the input is a large box, then new lines are accepted
	{
			if(preg_match("/(%0A|%0D|\n+|\r+)/i", $string))
					kill ("bsafe injection newlines", "mail injection");
	
	}
	
	return $string;
}

function format_date($format,$date,$translate = false){
	
	if($date == "0000-00-00" || $date == "0000-00-00 00:00:00" || $date == "1969-12-31"){
	  return "";  
	}

	$spanish = array("January" => "Enero", "February" => "Febrero", "March" => "Marzo", "April" => "Abril", "May" => "Mayo", "June" => "Junio", "July" => "Julio", "August" => "Agosto", "September" => "Septiembre", "October" => "Octubre", "November" => "Noviembre", "December" => "Diciembre", "Jan" => "Ene", "Apr" => "Abr", "Aug" => "Ago", "Dec" => "Dic");
	
	$transform = array_flip($spanish);
	$date = strtr($date,$transform);
	$return = date($format,strtotime($date));
	if ($translate && $translate != "en") { $return = strtr($return,$spanish); }
	return $return;
}

function br2nl($string){
	return str_replace("<br />","",$string);
}

function encrypt($string)
{
	if (!defined("SALT") || SALT == "") {
		kill("SALT not defined", "critical");
		return false;
	} else {
		return hash("sha512", $string.SALT);
	}
}

function load_image($src,$wrapper = false){ 
	if(file_exists($src)){
	  if($wrapper){
		  $html = str_replace('{image}',"<img src='$src' alt='' />",$wrapper);
	  } else {
		  $html = "<p class='image' ><img src='$src?id=".date ("s", filemtime($src))."' alt='' /></p>";
	  }
	  return $html;
	} else {
	  return false;
	}
}

function parse_is ($url)
{
		return strpos($url,"|") ? str_replace("|","&",$url) : str_replace("&","|",$url);
}

/*
* File: SimpleImage.php
* Author: Simon Jarvis
* Copyright: 2006 Simon Jarvis
* Date: 08/11/06
* Link: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
* 
* This program is free software; you can redistribute it and/or 
* modify it under the terms of the GNU General Public License 
* as published by the Free Software Foundation; either version 2 
* of the License, or (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful, 
* but WITHOUT ANY WARRANTY; without even the implied warranty of 
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the 
* GNU General Public License for more details: 
* http://www.gnu.org/licenses/gpl.html
* LAST CHANGE: PAUL, JAN 2011
*/
 
class SimpleImage {
   
   var $image;
   var $image_type;
 
   function load($filename) {
	  $image_info = getimagesize($filename);
	  $this->image_type = $image_info[2];
	  if( $this->image_type == IMAGETYPE_JPEG ) {
		 $this->image = imagecreatefromjpeg($filename);
	  } elseif( $this->image_type == IMAGETYPE_GIF ) {
		 $this->image = imagecreatefromgif($filename);
	  } elseif( $this->image_type == IMAGETYPE_PNG ) {
		 $this->image = imagecreatefrompng($filename);
	  }
   }
   
   function save($filename, $image_type=IMAGETYPE_JPEG, $compression=85, $permissions=null) {
	  if( $image_type == IMAGETYPE_JPEG ) {
		 imagejpeg($this->image,$filename,$compression);
	  } elseif( $image_type == IMAGETYPE_GIF ) {
		 imagegif($this->image,$filename);         
	  } elseif( $image_type == IMAGETYPE_PNG ) {
		 imagepng($this->image,$filename);
	  }   
	  if( $permissions != null) {
		 chmod($filename,$permissions);
	  }
   }
   function output($image_type=IMAGETYPE_JPEG) {
	  if( $image_type == IMAGETYPE_JPEG ) {
		 imagejpeg($this->image);
	  } elseif( $image_type == IMAGETYPE_GIF ) {
		 imagegif($this->image);         
	  } elseif( $image_type == IMAGETYPE_PNG ) {
		 imagepng($this->image);
	  }   
   }
   function getWidth() {
	  return imagesx($this->image);
   }
   function getHeight() {
	  return imagesy($this->image);
   }
   function resizeToHeight($height) {
	  $ratio = $height / $this->getHeight();
	  $width = $this->getWidth() * $ratio;
	  if ($height < $this->getHeight()) {
		  $this->resize($width,$height);
	  }
   }
   function resizeToWidth($width) {
	  $ratio = $width / $this->getWidth();
	  $height = $this->getheight() * $ratio;
	  if ($width < $this->getWidth()) {
		   $this->resize($width,$height);
	  }
   }
   function scale($scale) {
	  $width = $this->getWidth() * $scale/100;
	  $height = $this->getheight() * $scale/100; 
	  $this->resize($width,$height);
   }
   function resize($width,$height) {
	  $new_image = imagecreatetruecolor($width, $height);
	  imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
	  $this->image = $new_image;   
   }

   function rotate($angle) {
		$new_image = imagerotate($this->image,$angle,imageColorAllocateAlpha($this->image, 250, 250, 250,0));
		imagealphablending($new_image, false);
		imagesavealpha($new_image, true);
		$this->image = $new_image;
   } //fin de la funcción rotate 
}

if (!defined("OPENID_TEMPLATE")) {

  function openid_login($url, $update = false) {
	$db = new DB;
	$mysql_url = mysql_clean($url);
	if (defined("IPCHECK") && !IPCHECK) {
	  $ip = $_SERVER["REMOTE_ADDR"];
	} else {
	  $ip = "123.123.123.123"; // Use a static IP; i.e. do not check for a constant IP
	}
	$user_agent = $_SERVER["HTTP_USER_AGENT"];
	$login = $db->query("SELECT * FROM openid_identities WHERE url = $mysql_url");
	if($login->num_rows){
	  $data = encrypt($url.$user_agent.$ip).$login->id;
	  setcookie("openid_session",$data, false,"/");
    if ($update) {
      $db->query("UPDATE openid_identities SET url = '$update' WHERE id = " . $login->id);
    }
    $db->query("UPDATE openid_users SET last_login = NOW() WHERE id = " . $login->id);
	  return $login->userid;
	}
	else{
	  return false;
	}
  }
  function openid_logout() {
	  setcookie("openid_session",false, time()-1,"/");
  }
  function openid_validate(){	
	global $db;
	$data = $_COOKIE["openid_session"];
	if($data){
	  $id = (int)substr($data,128,strlen($data)-128);
	  $login = $db->query("SELECT identities.url,users.id,users.screenname FROM openid_identities AS identities 
	  LEFT JOIN openid_users AS users ON identities.userid = users.id WHERE identities.id = $id");
	  if($login->num_rows){
		if (defined("IPCHECK") && !IPCHECK) {
		  $ip = $_SERVER["REMOTE_ADDR"];
		} else {
		  $ip = "123.123.123.123"; // Use a static IP; i.e. do not check for a constant IP
		}
		$url = $login->url;
		$user_agent = $_SERVER["HTTP_USER_AGENT"];
		$hash = encrypt($url.$user_agent.$ip).$id;
		if($hash == $data){
		  define("OPENID_USERID",$login->id);
		  define("OPENID_USERNAME",$login->screenname);
		  return true;
		} else {
		  return false;
		}
	  }
	  else{
		return false;
	  }		
	} else {
	  return false;
	}
  }
}
//converting ../ when in cms to / for the frontend
function convert_media_paths($string){
	$string = str_replace("../media/","media/",$string);
	$string = str_replace("../imgi/","imgi/",$string);
	return $string;	
}

function reverse_media_paths($string){
	$string = str_replace("imgi/","../imgi/",$string);	
	$string = str_replace("media/","../media/",$string);	
	return $string;	
}


class DB{
	private $connection;
  var $insert_id;
  var $affected_rows;

	function __construct($server = false,$user = false,$password = false,$database = false, $action = false) {
	if($server && $user && $password && $database && !defined('NON_STANDARD_DATABASE')) {      
		$this->connection = new mysqli("p:".$server,$user,$password,$database);
	} elseif (defined('NON_STANDARD_DATABASE')){
		if (defined('ALT_CONNECTION')) {
			require_once ALT_CONNECTION;
			$this->connection = new mysqli("p:".SERVER,USER,PASSWORD,NON_STANDARD_DATABASE);          
		} else {
			$this->connection = new mysqli("p:".$server,$user,$password,NON_STANDARD_DATABASE);          
		}  
	} elseif (defined('CONNECTION')){
		require_once CONNECTION; 
		$this->connection = new mysqli("p:".SERVER,USER,PASSWORD,DATABASE);          
	}
	if (mysqli_connect_errno()) {  
		if ($action == "mailerror") {
		  mail(SYSADMIN, "MySQL Connection Error", $this->connection->connect_error() . "\n\n" . getinfo(), 
			"From: " . EMAIL);
		} else {
		  kill("Connection Error: " . $this->connection->connect_error, "mysql");
		}
	}
	$this->connection->set_charset("utf8");
  }
   
  function query($query, $print = false, $action = false){		
	  if($query){
		$result = $this->connection->query($query) or 
		  $action == "mailerror" ? 
			mail(SYSADMIN, "MySQL Query Error", mysqli_error($this->connection) . "\n\n" . getinfo(), 
			  "From: " . EMAIL) : 
			kill("Query:\n$query\n\nError:\n" . mysqli_error($this->connection) . "\n\n", "mysql");

		$this->insert_id = $this->connection->insert_id;
		$this->affected_rows = $this->connection->affected_rows;
		
		if($print && LOCAL && defined("LOCAL")) {
			echo "<div style='
			background:#333; 
			border:2px solid #fff;
			padding:5px; 
			position:fixed;
			font-weight:bold;                  
			top:0;
			color:#fff;
			width:100%;
			left:0;
			z-index:100000;
			'>
				$query
			  </div>";
		}
	  
		return new DB_Result($result);
	  } else {	    
		trigger_error("Function query(): The query is empty smart*ss. ",E_USER_ERROR);
	  }

  }	
  
  function insert ($table,$data,$print = false, $action = false){
	  
	  //Prepare columns in query
	  foreach($data as $key => $value) {
		$columns.= "`" . $key . "`,";
	  }
	  $columns = substr($columns, 0, -1);

	  //Prepare values in query
	  foreach($data as $key => $value) {
		$values.= $value.",";
	  }
	  $values = substr($values, 0, -1);

	  //Order columns and values in query
	  $query=	"INSERT INTO $table ( $columns ) VALUES ( $values )";
	  self::query($query,$print, $action);
  }
  
  function update($table,$data,$condition,$print = false){

	  foreach($data as $key => $value) {
		$values .= "`$key` = $value,";
	  }
	  $values = substr($values, 0, -1);
	  

	  $query=	"UPDATE $table SET $values WHERE $condition";
	  self::query($query,$print);
  }

  function prepare($string,$wildcard = false){
	$string = nl2br(htmlentities($string, ENT_QUOTES, "UTF-8"));
	$string = $this->connection->real_escape_string($string);

	if ($wildcard){
	  return addcslashes($string, "%_"); 
	} else {
	  return "'$string'";
	}
  }
}

class DB_Result{
	var $resource;
	var $pointer = 0;
	var $num_rows;
	
	function __construct($resource){
		$this->num_rows = $resource->num_rows;
		$this->affected_rows = $resource->affected_rows;
		if($resource->num_rows==1){
			$this->resource = $resource->fetch_assoc();		
		} else {
			$this->resource = $resource;
		}
	 }
	 function fetch(){
		 if($this->resource){
		  if(is_array($this->resource)){
			  if($this->pointer){
				  return false;
			  } else {
				  $this->pointer = 1;
				 return $this->resource;
			  }
		  } else {
			return $this->resource->fetch_assoc();		
		  }
	  } else {
		return false;
	  }
	}
	
	function reset(){
		if (!is_array($this->resource)) {
			$this->resource->data_seek(0);
	  return true;
		} else {
	  $this->pointer = 0;
	  return true;  
	}
	}

	public function  __get($name) {  
	// check if the named key exists in our array  
	if(is_array($this->resource) && array_key_exists($name, $this->resource)) {  
		// then return the value from the array  
		return $this->resource[$name];  
	}  else {
	  return false;  
	}
	}  
	
}

?>
