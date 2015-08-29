<?php
session_start();
$skip_login = true;
require_once 'functions.php';

$expiration = (int)$_GET['expiration'];
$get_hash = $_GET['hash'];

$hash = encrypt($expiration);

if ($hash != $get_hash && !$_GET['state']) { 
  kill();
}

$expiration_date = date("Y-m-d", $expiration);
$yesterday = date("Y-m-d", strtotime(date("Y-m-d" . " -1day")));

if ($expiration_date < date("Y-m-d") && !$_GET['state']) {
  kill("Expiration date can not be before yesterday or after tomorrow");
}

if (LOCAL) {
  $redirect = 'http://ib.is/openstreets/register.php';
} else {
  $redirect = 'http://friends.openstreets.co.za/register.php';
}

if ($_GET['state']) {
  if ($_GET['state'] != $_SESSION['state']) {
    $error = "Invalid session";
  }
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL,"https://www.googleapis.com/oauth2/v3/token");
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, "code={$_GET['code']}&client_id={$client_id}&client_secret={$client_secret}&redirect_uri={$redirect}&grant_type=authorization_code");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $output = curl_exec ($ch);
  curl_close ($ch);
  $output = json_decode($output);
  $info = $output->id_token;
  if (!$info) {
    $error = "Invalid token";
  } else {
    require_once 'JWT.php';
    $final = JWT::decode($info, $client_secret, false);
    $email = $final->email;
    if ($final->sub) {
      $link_identifier = "accounts.google.com/{$final->sub}";
      if (openid_login($link_identifier)) {
        $error = "User already exists";
        $user_already_exists = true;
      } else {
        // Let's register this user.
        $post = array(
            "screenname" => mysql_clean($final->email),
            "fullname" => mysql_clean($final->email),
            "mail" => mysql_clean($final->email),
            "creation_date" => "NOW()",
            "last_login" => "NOW()",
            "status" => 1,
        );

        $db->insert("openid_users",$post);
        $openid_user = $db->insert_id;

        $url = $final->iss . "/" . $final->sub;

        $post = array(
          "userid" => $openid_user,
          "url" => mysql_clean($url),
        );

        $db->insert("openid_identities",$post);
        openid_login($url);
        header("Location: " . URL . "info/dashboard");
        exit();
      }
    } else {
      $error = "Invalid response.";
    }
  }
} else {
  $state = md5(rand());
  $_SESSION['state'] = $state;
}
?>
<!doctype html>
<!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7"><![endif]-->
<!--[if IE 7]><html class="no-js lt-ie9 lt-ie8"><![endif]-->
<!--[if IE 8]><html class="no-js lt-ie9"><![endif]-->
<!--[if gt IE 8]><!--><html class="no-js"><!--<![endif]-->
<head>
    <title><?php echo _('Login'); ?> | <?php echo SITENAME ?></title>
    <?php echo $head ?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<style>
  .grid .col.six {
    width:60%;
  }
  .grid .col {
    float: left;
  }
  .grid {
    margin: 0px auto;
    max-width: 1200px;
    min-width: 980px;
    width: 100%;
  }
  #wrapper{position:relative;top:120px;}
  .btn,.google,.google:hover,.google:active,.google:focus{background:#4285F4}
  #openid_choice .btn {
    color:#fff;
    font-weight:bold;
    text-align: center;
    line-height:29px;
    display:block;
    text-shadow:none;
  }
  #openid_choice i{margin-right:20px;font-size:30px;float:left}
  #openid_choice .btn span{position:relative;left:-10px;}
  .six {
    width:500px;
  }
  .grid {
    min-width:550px;
  }
  .alert-danger {
    padding:10px;
    font-weight:bold;
    font-size:120%;
    color: #A94442;
    background-color: #F2DEDE;
    border-color: #EBCCD1;
  }
  #wrapper { font:200 100%/1 Ubuntu,sans-serif }
header {
    height: 115px;
    width: 100%;
    background: url("img/people.png") no-repeat scroll center bottom #333;
    position: relative;
    box-shadow: 1px 2px 5px 1px #D5D5D5;
}
</style>
</head>
<body>
<header>
  <img src="img/logo.png?v=1310" alt="Open Streets">
</header>

<div id="wrapper" class="grid">

    <div class="col six center">

        <h2>Register your account</h2>

        <?php if ($error) { ?>

        <?php if ($user_already_exists) { ?>
        <h2 class="ok"><?php echo $error ?></h2>
        <p><a href="login.php" class="button button-blue">Log In Now</a></p>

        <?php } else { ?>
        <h2>Registration failed</h2>
        <p><?php echo $error ?></p>
        <?php } ?>
        <?php } else { ?>

        <form action="register.php" method="get" id="openid_form">
            <input type="hidden" name="action" value="verify" />
            <input type="hidden" name="login" value="1" />
            <input type="hidden" name="hash" value="<?php echo $hash ?>" />
            <input type="hidden" name="expiration" value="<?php echo $expiration ?>" />
            <fieldset>
                <div id="openid_choice">
                  <p>
                    <a href="https://accounts.google.com/o/oauth2/auth?client_id=<?php echo $client_id ?>&response_type=code&scope=openid%20email&redirect_uri=<?php echo $redirect ?>&state=<?php echo $state ?>" class="btn btn-large btn-default google">
                      <i class="fa fa-google"></i>
                      <span>
                        Register using <strong>Google</strong>
                      </span>
                    </a>
                  </p>
                </div>
            </fieldset>

        </form>

        <?php } ?>

    </div>
</div>


</body>
</html>
