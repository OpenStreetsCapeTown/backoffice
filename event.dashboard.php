<?php
require_once 'functions.php';
$id = (int)$_GET['id'];

if ($_POST) {
  $post = array(
    'notes' => mysql_clean($_POST['notes']),
  );
  $db->update("events",$post,"id = $id");
  $print = "Notes were saved";
}

if ($_GET['active']) {
  $id = (int)$_GET['id'];
  $active=$_GET['active'];
  $db->query("UPDATE events SET active = $active WHERE id = $id");
}

$info = $db->query("SELECT * FROM events WHERE id = $id");

$linked = $db->query("SELECT 
  COUNT(people_events.id) AS total,
  people_events.relationship,
  event_relationships.name AS name  
  FROM people_events 
    JOIN event_relationships ON people_events.relationship = event_relationships.id
  WHERE event = $id 
    GROUP BY people_events.relationship
    ORDER BY event_relationships.name");


$events = $db->query("SELECT *,
  (SELECT COUNT(*) FROM people_events WHERE people_events.event = events.id) AS attendance
  FROM events
  WHERE parent_event = $id
  ORDER BY date");

?>
<!doctype html>
<html>
<head>
<title><?php echo SITENAME ?></title>
<?php echo $head ?>
<style type="text/css">
.movedown{margin-top:40px}
#notes textarea{height:300px;margin-bottom:10px}
footer{margin-top:30px}
</style>
</head>
<body>

<?php require_once 'include.header.php'; ?>

<div class="jumbotron">
  <p>
    <?php if ($info->active == FALSE) { echo "This event has been archived"; } ?>
  </p>
  <h1>
    <i class="fa fa-dashboard"></i>
    Dashboard
  </h1>
  <p>
    <strong><?php echo $info->name ?></strong> | <?php echo format_date("M d, Y", $info->date) ?> | 
    Event #<?php echo $id ?> | <a href="events/checklist/<?php echo $id ?>"><i class="fa fa-check"></i> Checklist</a>
  </p>
</div>

<?php if ($print) { echo "<div class=\"alert alert-success\">$print</div>"; } ?>

<div class="container">
  
  <div class="row">
  
    <section class="col-md-4" id="notes">
      <h1>Internal Notes</h1>
      <form method="post">
        <textarea name="notes" class="form-control"><?php echo $info->notes ?></textarea>
        <button type="submit" class="btn btn-primary">Save</button>
      </form>
    </section>

    <section class="col-md-4">
      <h1>Linked Contacts</h1>
      <?php if (!$linked->num_rows) { ?>
        <div class="alert alert-info">
          No linked contacts
        </div>
      <?php } else { ?>
        <ul>
        <?php while ($row = $linked->fetch()) { ?>
          <li>
          <a href="event.contacts.php?event=<?php echo $id ?>&amp;relationship=<?php echo $row['relationship'] ?>">
            <?php echo $row['name'] ?>
          </a>
          <strong>
          <i class="fa fa-users"></i>
          <?php echo $row['total'] ?></strong>
          </li>
        <?php } ?>
        </ul>
      <?php } ?>
      <p>
        <a class="btn btn-default" href="event.contacts.php?event=<?php echo $id ?>">
          <i class="fa fa-users"></i>
          View all contacts
        </a>
      </p>
      <p class="movedown"><a href="people/search/<?php echo $id ?>" class="btn btn-primary">Add Contacts</a></p>
      <?php if ($info->active == TRUE) { ?>
        <a href="event.dashboard.php?id=<?php echo $id ?>&amp;active=false" onclick="javascript:return confirm('Are you sure?')" class="btn btn-warning right">Archive Event</a>
      <?php } else { ?>
        <a href="event.dashboard.php?id=<?php echo $id ?>&amp;active=true" onclick="javascript:return confirm('Are you sure?')" class="btn btn-warning right">Unarchive Event</a>
      <?php } ?>
    </section>
  
    <section class="col-md-4">
      <h1>Related Events</h1>

      <?php if (!$events->num_rows) { ?>
        <div class="alert alert-warning">
          No related events found.
        </div>
      <?php } else { ?>
        <ul>
        <?php while ($row = $events->fetch()) { ?>
          <li>
            <strong><?php echo format_date("M d", $row['date']) ?></strong>
            <a href="events/dashboard/<?php echo $row['id'] ?>"><?php echo $row['name'] ?></a>
            <a class="badge" href="event.contacts.php?event=<?php echo $row['id'] ?>">
              <i class="fa fa-users"></i>
              <?php echo $row['attendance'] ?>
            </a>

          </li>
        <?php } ?>
        </ul>
        <p>
          <a class="btn btn-default" href="event.contacts.php?parent_event=<?php echo $id ?>">
            <i class="fa fa-users"></i>
            View all contacts
          </a>
        </p>
      <?php } ?>

    </section>

  </div>

</div>

<?php require_once 'include.footer.php'; ?>

</body>
</html>
