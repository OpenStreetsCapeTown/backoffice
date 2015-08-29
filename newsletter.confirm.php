<?php
$skip_login = true;
require_once 'functions.php';

$id = (int)$_GET['id'];
if (!$id) {
  kill("Invalid link opened");
}
$hash = $_GET['hash'];
$gethash = $_GET['hash'];
$info = $db->query("SELECT * FROM people WHERE id = $id");
$hash = encrypt($id . $info->email);
if ($hash != $gethash) {
  die("Sorry, this link is invalid. Please click the link in your e-mail. If this does not work, contact us at info@openstreets.co.za.");
} else {
  logThis(12, $id);
  $db->query("UPDATE people SET active = 1 WHERE id = $id LIMIT 1");

  $date = date("r");

  $message = 

"A new user signed up for the mailing list and was registered in the database.

Name: {$info->firstname} {$info->lastname}
E-mail: {$info->email}
Date: {$date}

More details:
" . URL . "people/{$id}";
    $headers = 'From: noreply@friends.openstreets.co.za' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

    mail("info@openstreets.co.za", "Open Streets Subscription", $message, $headers);
}
?>
<!doctype html>
<html>
<head>
<title>Newsletter subscription | Friends of Open Streets</title>
<?php echo $head ?>
<style type="text/css">
header {
  height:115px;
  width:100%;
  background:#333333 url(img/people.png) no-repeat center bottom;
  position:relative;
  -webkit-box-shadow:1px 2px 5px 1px #d5d5d5;
  box-shadow:1px 2px 5px 1px #d5d5d5;
  -moz-box-shadow: 1px 2px 5px 1px #d5d5d5;
}
header h1 {
  color:#fff;
  text-align:center;
  font-size:43px;
  font-weight:bold;
  margin:0;
  padding:10px 0 0 0;

}
header img {
  position:absolute;
  top:5px;
  left:6px;
}
</style>
</head>
<body>

<header>
  <a href="http://www.openstreets.co.za"><img src="img/logo.png?refresh" alt="" /></a>
  <h1><a href="./">Friends of Open Streets</a></h1>
</header>

<div class="container" id="content">

    <div class="jumbotron">
      <div class="container">
        <h1>Your subscription is confirmed!</h1>
      </div>
    </div>

    <div class="container">

      <h1>Check out our whiteboard as well!</h1>
      <p>It's a place where you can discuss ideas and projects related to Open Streets and brainstorm with 
      other people about this!</p>

      <p><a href="<?php echo URL ?>" class="btn btn-primary btn-lg">Go to the whiteboard</a></p>


    </div>

</div>

</body>
</html>
