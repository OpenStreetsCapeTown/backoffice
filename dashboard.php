<?php
require_once 'functions.php';
$name = $db->query("SELECT screenname FROM openid_users WHERE id = " . OPENID_USERID);
?>
<!doctype html>
<html>
<head>
<title>Dashboard | <?php echo SITENAME ?></title>
<?php echo $head ?>
</head>
<body>

<?php require_once 'include.header.php'; ?>

<h1>Open Streets Database</h1>

<div class="alert alert-info">
  Welcome, <?php echo $name->screenname ?>
</div>

<p>Use the navigation menu to browse the Open Streets Database.</p>

<h2>Quick Links</h2>

<p>
  <a href="people.php" class="btn btn-primary btn-lg">Add contact</a>
  <a href="people/search" class="btn btn-primary btn-lg">Search</a>
  <a href="events/dashboard/4" class="btn btn-primary btn-lg">Langa Dashboard</a>
</p>

<?php require_once 'include.footer.php'; ?>

</body>
</html>
