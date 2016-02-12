<?php
require_once 'global.functions.php';
require_once 'config.php';
$db = new DB;

// These are the tags that are allowed (when filtering user input)
define ("TAGS", "<p><strong><a><ul><li><em><ol><span><table><tr><th><td><img><h1><h2><h3><h4><h5><h6><div><iframe><b><i><hr><pre><br>");

if ($_COOKIE["openid_session"]) {
  require_once 'openid.php';
  openid_validate();
  if (defined("OPENID_USERID")) {
    $permissions = $db->query("SELECT * FROM openid_permissions WHERE openid_user = " . OPENID_USERID . " AND status = 1 LIMIT 1");
    if ($permissions->num_rows) {
      define ("CMS_LOGIN", true);
    }
  }
}
if (!defined("CMS_LOGIN") && !$skip_login) {
  header("Location: " . URL . "login.php");
  exit();
}

$css_version = filesize("css/styles.css");

$head = '
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<base href="' . URL . '" />
<link rel="shortcut icon" href="favicon.ico" />
<link rel="apple-touch-icon" href="img/apple-touch-icon.png" />
<link rel="stylesheet" href="css/bootstrap.' . $css_version . '.css" />
<link rel="stylesheet" href="css/font-awesome.' . $css_version . '.css" />
<link rel="stylesheet" href="css/styles.' . $css_version . '.css" />';

$head .= LOCAL ? 
  '<link rel="stylesheet" href="css/droid.css" />' :
  '<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Droid+Sans:400,700" />';

$head .= '<script src="js/modernizr.2.7.1.js"></script>';

$head .= PRODUCTION ? 
  '<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>' :
  '<script src="js/jq.2.1.0.js"></script>';

$head .= '<script src="js/bootstrap.js"></script>';

function getName($id) {
  global $db;
  $info = $db->query("SELECT firstname, lastname, organization, email FROM people WHERE id = $id");
  if (!$info->firstname && !$info->lastname && !$info->organization) {
    return $info->email;
  } elseif ($info->firstname || $info->lastname) {
    return $info->firstname . " " . $info->lastname;
  } else {
    return $info->organization;
  }
}

function findContact($email) {
  global $db;
  $email = mysql_clean($email);
  $info = $db->query("SELECT id FROM people WHERE email = $email");
  return $info->id;
}

function logThis($action, $people = false, $openid_user = false, $details = false) {
  global $db;
  $post = array(
    'log_action' => $action,
    'people' => $people ? $people : "NULL",
    'user' => defined("OPENID_USERID") ? OPENID_USERID : "NULL",
    'details' => $details ? html($details) : "NULL",
  );
  $db->insert("log",$post);
  return true;
}
?>
