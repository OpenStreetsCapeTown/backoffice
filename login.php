<?php
$skip_login = true;
session_start();

require_once 'functions.php';

if ($_GET['action'] == "logout") {
	$print = _("You have been logged out");
} else {
  openid_validate();
}

if (LOCAL) {
  $redirect = 'http://ib.is/openstreets/login.php';
  $realm = 'http://ib.is/openstreets/';
} else {
  $redirect = 'http://friends.openstreets.co.za/login.php';
  $realm = 'http://friends.openstreets.co.za/';
}

if ($_GET['state']) {
  var_dump($_GET['state']);
  if ($_GET['state'] != $_SESSION['state']) {
    $error = "Invalid session";
    echo "invalid";
  }
    echo "test";
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL,"https://www.googleapis.com/oauth2/v3/token");
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, "code={$_GET['code']}&client_id={$client_id}&client_secret={$client_secret}&redirect_uri={$redirect}&grant_type=authorization_code&openid.realm={$realm}");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    echo "output";
  $output = curl_exec ($ch);
  var_dump($output);
  curl_close ($ch);
  $output = json_decode($output);
    echo "info";
  $info = $output->id_token;
  var_dump($info);
  if (!$info) {
    $error = "Invalid token";
    $error .= "<br />The error returned from Google was: " . $output->error . "<br />" . 
    $output->error_description
  } else {
    require_once 'JWT.php';
    $final = JWT::decode($info, $client_secret, false);
    $email = $final->email;
        echo "0<br />";
    var_dump($final);
    if ($final->sub) {
      $link_identifier = "accounts.google.com/{$final->sub}";
      var_dump($final->sub);
      if (openid_login($link_identifier)) {
        var_dump($link_identifier);
        echo "1<br />";
        die(var_dump($_GET));
        header("Location: " . URL . "info/dashboard");
        exit();
      } elseif ($final->openid_id) {
          var_dump($final->openid_id);
        if (openid_login($final->openid_id, $link_identifier)) {
        echo "2<br />";
            var_dump('yes');
            die(var_dump($_GET));
          header("Location: " . URL . "info/dashboard");
          exit();
        } else {
          $error = "User not found.";
            var_dump($error);
        }
      } else {
        $error = "User not found.";
            var_dump($error);
      }
    } else {
      $error = "Invalid response.";
            var_dump($error);
    }
  }
} else {
  $state = md5(rand());
  $_SESSION['state'] = $state;
            var_dump($_SESSION);
}

var_dump($_SESSION);
var_dump($_GET);
if (!$_GET['force']) {
die();
}

?>
<!doctype html>
<!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7"><![endif]-->
<!--[if IE 7]><html class="no-js lt-ie9 lt-ie8"><![endif]-->
<!--[if IE 8]><html class="no-js lt-ie9"><![endif]-->
<!--[if gt IE 8]><!--><html class="no-js"><!--<![endif]-->
<head>
<title><?php echo _('Login'); ?> | <?php echo SITENAME ?></title>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
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
<body class="login">

<header>
  <img src="img/logo.png?v=1310" alt="Open Streets">
</header>

<div id="wrapper" class="grid">

    <div class="col six center">

        <?php if ($error) { ?>

        <h2 class="fail">Login Failed</h2>
        <p class="alert alert-danger"><strong><?php echo $error ?></strong></p>
        <p>
            <strong>Be sure to permit cookies
            <a href="http://www.google.com/support/accounts/bin/answer.py?&answer=61416">More about cookies</a>.</strong>
        </p>
        <p><a href="login.php">Try again.</a></p>

        <?php } else { ?>

        <h2>Login</h2>

        <?php if ($print) { echo "<h3 class='notice'>$print</h3>"; } ?>

        <form action="" method="get" id="openid_form">
            <input type="hidden" name="action" value="verify" />
            <input type="hidden" name="login" value="1" />
            <fieldset>
                <div id="openid_choice">
                  <p>
                    <a href="https://accounts.google.com/o/oauth2/auth?client_id=<?php echo $client_id ?>&response_type=code&scope=openid%20email&redirect_uri=<?php echo $redirect ?>&state=<?php echo $state ?>&openid.realm=<?php echo $realm ?>" class="btn btn-large btn-default google">
                      <i class="fa fa-google"></i>
                      <span>
                        Log in with Google
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
