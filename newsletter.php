<?php
$skip_login = true;
require_once 'functions.php';

$list = $db->query("SELECT * FROM mailinglist_options WHERE active = 1 ORDER BY name");

if ($_GET['retry']) {
  $id = (int)$_GET['id'];
  $gethash = $_GET['hash'];
  $info = $db->query("SELECT * FROM people WHERE id = $id");
  $hash = substr(encrypt($id . $info->email), 0, 10);
  if ($hash != $gethash) {
    kill("Hash is invalid");
  }
  require_once 'mailchimp.php';
  $MailChimp = new \Drewm\MailChimp(MAILCHIMP_API_KEY);

  $return = $MailChimp->call('lists/subscribe',array(
    'id' => MAILCHIMP_LIST,
    'email' => array(
      'email' => $info->email,
    ),
  ));
  if ($return['status'] == 'error') {
    $error = "Sorry, there was a problem confirming your registration. Please contact info@openstreets.co.za";
  }
  $print = "Confirmation mail was sent. Please check your e-mail in a few minutes to confirm your account";
}

if (trim($_POST['email'])) {
  $email = mysql_clean(trim($_POST['email']));
  $user = $db->query("SELECT * FROM people WHERE email = $email LIMIT 1");
  $check = trim(strtolower($_POST['humancheck']));
  if ($check != "open streets") {
    $error = "Error! Please type 'Open Streets' in the human verification box<br /> <a href='javascript:history.back(1)'>Click here to go back.</a>";
  } 
  if ($user->num_rows) {
    $id = $user->id;
    $check = $db->query("SELECT * FROM people_mailinglists WHERE id = $id");
  }
  if (!check_mail($_POST['email'])) {
    $error = "Sorry, you did not provide a valid e-mail address. <a href='javascript:history.back(1)'>Click here to go back.</a>";
  } elseif (!is_array($_POST['list'])) { 
    $error = "Sorry, you did not mark any of the mailing lists. <a href='javascript:history.back(1)'>Click here to go back.</a>";
  } elseif ($check->num_rows) {
    $error = "You are already subscribed to our mailing lists. To change your subscription options, use the links provided in the footer of our newsletters. If you have any questions or comments you can also let us know by writing to <a href='mailto:info@openstreets.co.za'>info@openstreets.co.za</a>";
  }
  if (!$error) {
    if (!$user->num_rows) {
      $post = array(
        'email' => mysql_clean($_POST['email']),
        'firstname' => html($_POST['firstname']),
        'lastname' => html($_POST['lastname']),
        'active' => 0,
      );
      $db->insert("people",$post);
      $id = $db->insert_id;
      logThis(1, $id);
    }
    $details = $_SERVER["REMOTE_ADDR"] . " // " . $_SERVER["HTTP_USER_AGENT"];
    logThis(11, $id, false, $details);
    foreach ($_POST['list'] as $key => $value) {
      $list = (int)$key;
      $post = array(
        'people' => $id,
        'mailinglist' => $list,
      );
      $db->insert("people_mailinglists",$post);
    }
    $hash = encrypt($id . $_POST['email']);
    $link = URL . "confirm/$id/$hash";

    $message = 

    "<!doctype html>
    <html>
    <head>
    <title>Open Streets Newsletter</title>
    <meta charset='UTF-8' />
    </head>

    <style type='text/css'>
    body, html{
      font-family: 'Open Sans',Helvetica,Arial,sans-serif;
    }
    a {
      font-weight:bold;
      color:#4EB542;
    }
    </style>
    <body>

    <h1>Stay informed with Open Streets!</h1>

    <p>
      To complete your subscription, please click the link below:
    </p>

    <p>
      <a href='{$link}'>Confirm your subscription</a>
    </p>

    <p>
      If you have any questions or comments, please write us at info@openstreets.co.za
    </p>

    </body>
    </html>";

    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
    $headers .= 'From: noreply@friends.openstreets.co.za' . "\r\n" .
    'Reply-To: info@openstreets.co.za' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

    mail($_POST['email'], "Open Streets Newsletter Confirmation", $message, $headers);

    $mail_sent = true;

  }

}

?>
<!doctype html>
<html>
<head>
<title>Newsletter Subscription | Friends of Open Streets</title>
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
h2 {
  margin-top:90px;
}
.col-sm-8 {
  margin-top:9px;
}
</style>
</head>
<body>

<header>
  <a href="http://www.openstreets.co.za"><img src="img/logo.png?refresh" alt="" /></a>
  <h1><a href="./">Friends of Open Streets</a></h1>
</header>

<div class="container" id="content">

    <div class="container">

      <h1>Open Streets Newsletter</h1>

      <?php 
      if ($_POST || $_GET['retry']) {
      if ($error) { echo "<div class=\"alert alert-danger\">$error</div>"; ?>
      <?php } elseif ($mail_sent) { ?>

        <div class="alert alert-info">
          Thanks! We have received your subscription information. To confirm your subscription, 
          please confirm your e-mail by clicking the link we sent to you. 
        </div>

        <p>
          If you do not receive the e-mail within a few minutes, please <a href="newsletter.php?id=<?php echo $id ?>&amp;hash=<?php echo substr($hash, 0, 10) ?>&amp;retry=1">click here</a>.
        </p>

        <?php if ($_POST['interested']) { ?>
          <h2>Volunteer with Open Streets!</h2>

          <p>We can use your help! We are looking for various types of volunteers. 
          </p>
          <p>
          <a href="http://openstreets.co.za/volunteer" class="btn btn-primary
          btn-large">Click here to learn more about volunteering in our upcoming
          event</a>         
          </p>

        <?php } ?>

      <?php } elseif ($print) { echo "<div class=\"alert alert-success\">$print</div>"; } ?>
      
      <?php } else { ?>

        <form method="post" class="form form-horizontal">

          <div class="form-group">
            <label class="col-sm-2 control-label">E-mail</label>
            <div class="col-sm-10">
              <input class="form-control" type="email" name="email" required />
            </div>
          </div>
        
          <div class="form-group">
            <label class="col-sm-2 control-label">First name</label>
            <div class="col-sm-10">
              <input class="form-control" type="text" name="firstname" />
            </div>
          </div>
        
          <div class="form-group">
            <label class="col-sm-2 control-label">Last name</label>
            <div class="col-sm-10">
              <input class="form-control" type="text" name="lastname" />
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label">Human check</label>
            <div class="col-sm-10">
              <input class="form-control" type="text" name="humancheck" />
              To prevent spam, please enter the phrase 'Open Streets' in this box
            </div>
          </div>

          <h2>Which newsletter(s) would you like to subscribe to?</h2>

          <?php while ($row = $list->fetch()) { ?>

            <div class="form-group">
              <label class="col-sm-3 control-label"><?php echo $row['name'] ?></label>
              <div class="col-sm-1">
                <input class="form-control" type="checkbox" name="list[<?php echo $row['id'] ?>]" checked value="1" />
              </div>
              <div class="col-sm-8">
                <?php echo $row['description'] ?>
              </div>
            </div>

          <?php } ?>

          <h2>Are you interested in volunteering during the upcoming Open Streets event?</h2>

            <div class="form-group">
              <label class="col-sm-3 control-label">Yes, I am interested</label>
              <div class="col-sm-1">
                <input class="form-control" type="checkbox" name="interested" value="1" />
              </div>
              <div class="col-sm-8">
                If you mark this box, we will provide more details on the next page.
              </div>
            </div>

          <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
              <button type="submit" class="btn btn-primary">Subscribe</button>
            </div>
          </div>

        </form>

      <?php } ?>


    </div>

</div>

</body>
</html>
