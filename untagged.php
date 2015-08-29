<?php
require_once 'functions.php';
$list = $db->query("SELECT * FROM people WHERE 
  NOT EXISTS (SELECT * FROM people_tags WHERE people = people.id) AND
  NOT EXISTS (SELECT * FROM people_mailinglists WHERE people = people.id)
");
?>
<!doctype html>
<html>
<head>
<title><?php echo SITENAME ?></title>
<?php echo $head ?>
</head>
<body>

<?php require_once 'include.header.php'; ?>

<h1>Untagged people</h1>

<p>This page provides the list of contacts who are not tagged, nor are they
signed up for any mailing list.</p>

<table class="table table-striped">
  <tr>
    <th>ID</th>
    <th>Name</th>
    <th>E-mail</th>
    <th>Details</th>
  </tr>
<?php while ($row = $list->fetch()) { ?>
  <tr>
    <td><?php echo $row['id'] ?></td>
    <td><?php echo $row['firstname'] || $row['lastname'] ? $row['firstname'] . " " . $row['lastname'] : $row['organization']; ?></td>
    <td><?php echo $row['email'] ?></td>
    <td><a href="people/<?php echo $row['id'] ?>">View details</a></td>
  </tr>
<?php } ?>
</table>

<?php require_once 'include.footer.php'; ?>

</body>
</html>
