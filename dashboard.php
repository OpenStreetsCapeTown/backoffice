<?php
require_once 'functions.php';
$name = $db->query("SELECT screenname FROM openid_users WHERE id = " . OPENID_USERID);
$events = $db->query("SELECT * FROM events WHERE active = 1 AND date >= CURDATE() ORDER BY date");
?>
<!doctype html>
<html>
<head>
<title>Dashboard | <?php echo SITENAME ?></title>
<?php echo $head ?>
</head>
<body>

<?php require_once 'include.header.php'; ?>

<h1>Open Streets Motherboard</h1>

<div class="alert alert-info">
  Welcome, <?php echo $name->screenname ?>
</div>

<p>Use the navigation menu to browse the Open Streets Motherboard.</p>

<h2>Quick Links</h2>

<p>
  <a href="people.php" class="btn btn-primary btn-lg">Add contact</a>
  <a href="people/search" class="btn btn-primary btn-lg">Search</a>
  <?php while ($row = $events->fetch()) { ?>
    <a href="events/dashboard/<?php echo $row['id'] ?>" class="btn btn-primary btn-lg"><?php echo $row['name'] ?></a>
  <?php } ?>
</p>

<?php require_once 'include.footer.php'; ?>

</body>
</html>
