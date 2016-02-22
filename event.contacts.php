<?php
require_once 'functions.php';
$id = (int)$_GET['event'];
$info = $db->query("SELECT * FROM events WHERE id = $id");

$relationship = (int)$_GET['relationship'];
$parent_event = (int)$_GET['parent_event'];

$relinfo = $db->query("SELECT * FROM event_relationships WHERE id = $relationship");

if ($relationship) {
  $sql = "people_events.event = $id AND people_events.relationship = $relationship";
} elseif ($parent_event) {
  $sql = "events.parent_event = $parent_event";
} else {
  $sql = "people_events.event = $id";
}

$list = $db->query("SELECT people.*, people_events.comments
  FROM people_events 
  JOIN people ON people_events.people = people.id
  JOIN events ON people_events.event = events.id
WHERE $sql
ORDER BY people.firstname, people.lastname, people.organization, people.email");
?>
<!doctype html>
<html>
<head>
<title><?php echo $info->name ? $info->name : 'Event list' ?> | <?php echo SITENAME ?></title>
<?php echo $head ?>
</head>
<body>

<?php require_once 'include.header.php'; ?>

<h1><a href="events/dashboard/<?php echo $id ?>"><?php echo $info->name ?></a></h1>

<div class="alert alert-info">
  <?php echo $list->num_rows ?> contacts found related to this event
</div>

<?php if ($relationship) { ?>
  <p>Relationship: <strong><?php echo $relinfo->name ?></strong></p>
<?php } ?>

<table class="table table-striped">
  <tr>
    <th>ID</th>
    <th>Name</th>
    <th>Organisation</th>
    <th>Email</th>
    <th>Cell Phone</th>
    <th colspan="2">Details</th>
  </tr>
<?php while ($row = $list->fetch()) { ?>
  <tr>
    <td><a href="people/<?php echo $row['id'] ?>"><?php echo $row['id'] ?></a></td>
    <td><?php echo ($row['firstname'] || $row['lastname'] || $row['organization']) ? $row['firstname'] . " " . $row['lastname'] : $row['email']; ?></td>
    <td><?php echo $row['organization'] ?></td>
    <td><?php echo $row['email'] ?></td>
    <td><?php echo $row['cell'] ?></td>
    <td><?php echo $row['comments'] ?></td>
    <td>
      <a href="event.contactlink.php?event=<?php echo $id ?>&amp;contact=<?php echo $row['id'] ?>">
        <i class="fa fa-edit"></i>
      </a>
    </td>
  </tr>
<?php } ?>
</table>

<?php require_once 'include.footer.php'; ?>

</body>
</html>
