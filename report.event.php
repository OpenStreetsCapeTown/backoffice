<?php
require_once 'functions.php';

$id = (int)$_GET['id'];
$info = $db->query("SELECT * FROM events WHERE id = $id");

$list = $db->query("SELECT 
  people.*
FROM event_attendance e
  JOIN people ON e.people = people.id
WHERE e.event = $id ORDER BY people.lastname, people.firstname, people.email");
?>
<!doctype html>
<html>
<head>
<title><?php echo SITENAME ?></title>
<?php echo $head ?>
</head>
<body>

<?php require_once 'include.header.php'; ?>

<h1>Attendees: <?php echo $info->name ?></h1>

<div class="alert alert-info">
  <strong><?php echo $list->num_rows ?></strong> attendees
</div>

<table class="table table-striped">
  <tr>
    <th>#</th>
    <th>First Name</th>
    <th>Last Name</th>
    <th>E-mail</th>
    <th>View details</th>
  </tr>
<?php $count = 1; while ($row = $list->fetch()) { ?>
  <?php
  
  $post = array(
    'event' => 1,
    'people' => $row['id'],
    'relationship' => 7,
  );
  $db->insert("people_events",$post);
  ?>
  <tr>
    <td><?php echo $count++; ?></td>
    <td><?php echo $row['firstname'] ?></td>
    <td><?php echo $row['lastname'] ?></td>
    <td><?php echo $row['email'] ?></td>
    <td><a href="people/<?php echo $row['id'] ?>">View details</a></td>
  </tr>
<?php } ?>
</table>

<?php require_once 'include.footer.php'; ?>

</body>
</html>
