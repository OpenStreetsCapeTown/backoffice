<?php
require_once 'functions.php';
require_once 'functions.mail.php';
require_once 'openid.php';

$expiration = (int)$_GET['expiration'];
$get_hash = $_GET['hash'];

if ($_POST) {
  $_POST['email'] = trim($_POST['email']);
  if (check_mail($_POST['email'])) {
    $tomorrow = date("M d, Y", strtotime(date("Y-m-d") . " +1day"));
    $expiration = strtotime($tomorrow);
    $hash = encrypt($expiration);
    $link = URL . "register.php?expiration=$expiration&hash=$hash";
    $text = _('You have been invited to access the following site: ' . SITENAME . '. ');
    $text .= _('In order to access this website, you need to register your account. ');
    $text .= _('This can be done by clicking the link below and following the instructions. ');
    $text .= _('This link will be valid until ' . $tomorrow . ". ");
    $html = $text;
    $text .= "\n" . _('Link: ') . $link;
    $html .= "<br /><br />" . _('Link: <a href='.$link.'>' . _('Register your account'));
    pearMail($_POST['email'], _('Register your account for ') . SITENAME, $html, $text);
    $print = _("Email was sent");
  } else {
    $print = _("Mail address not valid");
  }
}

?>
<!doctype html>
<html>
<head>
<title><?php echo SITENAME ?></title>
<?php echo $head ?>
</head>
<body>

<?php require_once 'include.header.php'; ?>
        <form method="post">
            <h1><?php echo _('Authorize Database System Usage'); ?></h1>
            <?php if ($print) { ?>
                <div class="alert"><?php echo $print ?></div>
            <?php } ?>
            <p>
                <label><?php echo _('E-mail'); ?></label>
                <input type="email" name="email" value="<?php echo $info->name ?>" />
            </p>
            <p><button class="btn btn-primary" type="submit"><?php echo _('E-mail authorization link'); ?></button></p>
        </form>

<?php require_once 'include.footer.php'; ?>
