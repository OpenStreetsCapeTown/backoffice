<?php
require_once 'functions.php';
header("HTTP/1.0 404 Not Found");
?>
<!doctype html>
<html>
<head>
<title><?php echo SITENAME ?></title>
<?php echo $head ?>
</head>
<body>

<?php require_once 'include.header.php'; ?>

<h1>404</h1>

<p>Page not found, that is.</p>

<?php require_once 'include.footer.php'; ?>

</body>
</html>
