<?php
require_once 'functions.php';
$id = (int)$_GET['id'];
$info = $db->query("SELECT * FROM events WHERE id = $id");
if ($info->location) { 
  $locationinfo = $db->query("SELECT * FROM locations WHERE id = ".$info->location);
  $list = $db->query("SELECT * FROM events WHERE location = {$info->location} AND id != $id AND active = 1 ORDER BY date DESC LIMIT 5");
}


$import = (int)$_GET['import'];
if ($import) { 
  $relationships = $db->query("SELECT * FROM event_relationships WHERE active = 1 ORDER BY name");
}

$relationship = (int)$_GET['relationship'];

$count = 0;
if ($relationship) {
  $to_copy = $db->query("SELECT * FROM people_events WHERE event = $import AND relationship = $relationship");
  while ($row = $to_copy->fetch()) {
    $check = $db->query("SELECT * FROM people_events WHERE event = $id AND relationship = $relationship AND people = ".$row['people']);
    if (!$check->num_rows) { 
      // We only add a new record if there is no relationship yet between this event and the person
      $post = array(
        'people' => $row['people'],
        'event' => $id,
        'relationship' => $row['relationship'],
        'comments' => mysql_clean($row['comments']),
      );
      $db->insert("people_events",$post);
      $count++;
    }
  }
}
?>
<!doctype html>
<html>
<head>
<title>Import Contacts: <?php echo $info->name ?> | <?php echo SITENAME ?></title>
<?php echo $head ?>
</head>
<body>

<?php require_once 'include.header.php'; ?>

<h1>
Import Contacts: <?php echo $info->name ?>
</h1>

<?php if ($import) { ?>

  <?php if ($relationship) { ?>
  <div class="alert alert-success">
    We have imported <strong><?php echo $count ?></strong> contact. Click another category below if you want
    to import more contacts.
  </div>
  <?php } ?>

  <p>Which contact type do you want to import? </p>

  <ul class="nav nav-list">
  <?php while ($row = $relationships->fetch()) { ?>
      <li><a href="event.import.php?id=<?php echo $id ?>&amp;import=<?php echo $import ?>&amp;relationship=<?php echo $row['id'] ?>"><?php echo $row['name'] ?></a></li>
  <?php } ?>
  </ul>

<?php } elseif (!$info->location) { ?>

  <div class="alert alert-danger">Location was not set. Please set the location of this event first.</div>

<?php } else { ?>

  <div class="alert alert-info">
    The location of this event is: <?php echo $locationinfo->name ?>. 
    Please select the related event from which you would like to import contacts.
  </div>

  <?php if (!$list->num_rows) { ?>

  <div class="alert alert-danger">No previous events found at this location</div>

  <?php } ?>

  <ul class="nav nav-list">
    <?php while ($row = $list->fetch()) { ?>
      <li><a href="event.import.php?id=<?php echo $id ?>&amp;import=<?php echo $row['id'] ?>"><?php echo $row['name'] ?></a></li>
    <?php } ?>
  </ul>

<?php } ?>

<p style="margin-top:60px"><a href="events/dashboard/<?php echo $id ?>" class="btn btn-primary">&laquo; Back to Dashboard</a></p>

<?php require_once 'include.footer.php'; ?>

</body>
</html>
