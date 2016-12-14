<?php
require_once 'functions.php';
$id = (int)$_GET['event'];
$info = $db->query("SELECT * FROM events WHERE id = $id");

$relationship = (int)$_GET['relationship'];
$parent_event = (int)$_GET['parent_event'];

$relinfo = $db->query("SELECT * FROM event_relationships WHERE id = $relationship");

$relationships = $db->query("SELECT * FROM event_relationships");
while ($row = $relationships->fetch()) {
 $relationship_names[$row['id']] = $row['name'];
}

if ($_GET['delete']) {
  $delete = (int)$_GET['delete'];
  $db->query("DELETE FROM people_events WHERE event = $id AND people = $delete LIMIT 1");
  $print = "Relationship was severed";
}

if ($relationship) {
  // Find all children of this relationship and include them as well
  // We should perhaps do a join in the query itself but because the query
  // is not only used for relationships this is easier
  $children = $db->query("SELECT * FROM event_relationships WHERE parent = $relationship");
  $relationship_sql = $relationship;
  while ($row = $children->fetch()) {
    $relationship_sql .= ", " . $row['id'];
  }
  $sql = "people_events.event = $id AND people_events.relationship IN ($relationship_sql)";
} elseif ($parent_event) {
  $sql = "events.parent_event = $parent_event";
} else {
  $sql = "people_events.event = $id";
}

$list = $db->query("SELECT people.*, people_events.comments, people_events.relationship
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
<style type="text/css">
a:hover .fa-remove{color:red}
</style>
</head>
<body>

<?php require_once 'include.header.php'; ?>

<h1><a href="events/dashboard/<?php echo $id ?>"><?php echo $info->name ?></a></h1>

<?php if ($print) { echo "<div class=\"alert alert-success\">$print</div>"; } ?>

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
    <th>Relationship</th>
    <th colspan="2">Details</th>
  </tr>
<?php while ($row = $list->fetch()) { ?>
  <tr>
    <td>
    <a href="people/<?php echo $row['id'] ?>"><?php echo $row['id'] ?></a></td>
    <td><?php echo ($row['firstname'] || $row['lastname'] || $row['organization']) ? $row['firstname'] . " " . $row['lastname'] : $row['email']; ?></td>
    <td><?php echo $row['organization'] ?></td>
    <td><?php echo $row['email'] ?></td>
    <td><?php echo $row['cell'] ?></td>
    <td><?php echo $relationship_names[$row['relationship']] ?></td>
    <td><?php echo $row['comments'] ?></td>
    <td>
      <a href="event.contactlink.php?event=<?php echo $id ?>&amp;contact=<?php echo $row['id'] ?>&amp;return=<?php echo urlencode("event.contacts.php?".$_SERVER['QUERY_STRING']); ?>">
        <i class="fa fa-edit"></i>
      </a>
    <a href="event.contacts.php?event=<?php echo $id ?>&amp;delete=<?php echo $row['id'] ?>&amp;relationship=<?php echo $relationship ?>">
      <i class="fa fa-remove" onclick="javascript:return confirm('Are you sure you want to remove the event relationship of <?php echo $row['firstname'] ?: 'this person'; ?>?')"></i>
    </a>
    </td>
  </tr>
<?php } ?>
</table>

<?php require_once 'include.footer.php'; ?>

</body>
</html>
