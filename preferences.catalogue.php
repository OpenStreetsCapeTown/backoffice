<?php
require_once 'functions.php';

$list = $db->query("SELECT * FROM preference_types ORDER BY id");

?>
<!doctype html>
<html>
<head>
<title>Preferences | <?php echo SITENAME ?></title>
<?php echo $head ?>
</head>
<body>

<?php require_once 'include.header.php'; ?>

<h1>Preference Types</h1>

<ul>
<?php while ($row = $list->fetch()) { ?>
  <li><a href="preference/<?php echo $row['id'] ?>"><?php echo $row['name'] ?></a></li>
<?php } ?>
</ul>

<?php require_once 'include.footer.php'; ?>

</body>
</html>
