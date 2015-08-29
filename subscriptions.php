<?php
require_once 'functions.php';
require_once 'mailchimp.php';
$MailChimp = new \Drewm\MailChimp('4ffc6540b34178eda24956a17c0fd057-us4');

$return = $MailChimp->call('lists/segment-add',array(
  'id' => 'b343acd933',
  'opts' => array(
    'type' => 'static',
    'name' => 'OS Newsletter Details',
  ),
));

die(var_dump($return));

?>
<!doctype html>
<html>
<head>
<title><?php echo SITENAME ?></title>
<?php echo $head ?>
</head>
<body>

<?php require_once 'include.header.php'; ?>

<?php require_once 'include.footer.php'; ?>

</body>
</html>
