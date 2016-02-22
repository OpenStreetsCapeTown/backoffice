<?php
require_once 'functions.php';
$event = (int)$_GET['event'];
$contact = (int)$_GET['contact'];

if ($_GET['delete']) {
  $delete = (int)$_GET['delete'];
  $db->query("DELETE FROM people_events WHERE id = $delete LIMIT 1");
  header("Location: " . URL . "people/$contact");
  exit();
}

if ($_POST) {
  $post = array(
    'people' => (int)$contact,
    'event' => (int)$_POST['event'],
    'comments' => html($_POST['comments']),
    'relationship' => (int)$_POST['relationship'],
  );
  $id = (int)$_POST['id'];
  if ($id) {
    $db->update("people_events",$post,"id = $id");
  } else {
    $db->insert("people_events",$post);
    logThis(16, $contact);
  }
  $print = "Information was saved";
  if ($_GET['return'] == "profile") {
    header("Location: " . URL . "people/$contact");
    exit();
  }
}

$info = $db->query("SELECT * FROM people WHERE id = $contact");
$eventinfo = $db->query("SELECT * FROM events WHERE id = $event");
$relationships = $db->query("SELECT * FROM event_relationships WHERE active = 1 ORDER BY name");
$details = $db->query("SELECT * FROM people_events WHERE people = $contact AND event = $event");
$id = (int)$_GET['id'];
if ($id) {
  $details = $db->query("SELECT * FROM people_events WHERE id = $id");
}
$events = $db->query("SELECT * FROM events WHERE active = 1 ORDER BY date DESC");

if ($_GET['event'] && !$details->event) {
  $details->event = (int)$_GET['event'];
}
?>
<!doctype html>
<html>
<head>
<title>Link with event | <?php echo SITENAME ?></title>
<?php echo $head ?>
<style type="text/css">


.form textarea{height:300px}
</style>
</head>
<body>

<?php require_once 'include.header.php'; ?>

<?php if ($details->id) { ?>
<a href="event.contactlink.php?contact=<?php echo $contact ?>&amp;delete=<?php echo $details->id ?>" onclick="javascript:return confirm('Are you sure?')"
  class="btn btn-danger pull-right"
>
  Remove this link
</a>
<?php } ?>

<h1>Link with event</h1>

<form method="post" class="form form-horizontal">

  <div class="alert alert-info">
    Contact: 
    #<?php echo $contact ?> - 
    <strong><?php echo getName($contact) ?></strong>
  </div>

  <?php if ($print) { echo "<div class=\"alert alert-success\">$print</div>"; } ?>

  <div class="form-group">
    <label class="col-sm-2 control-label">Event</label>
    <div class="col-sm-10">
      <select name="event" class="form-control" required>
        <option value=""></option>
        <?php while ($row = $events->fetch()) { ?>
          <option value="<?php echo $row['id'] ?>"<?php if ($row['id'] == $details->event) { echo ' selected'; } ?>><?php echo $row['name'] ?></option>
        <?php } ?>
      </select>
    </div>
  </div>  

  <div class="form-group">
    <label class="col-sm-2 control-label">Relationship</label>
    <div class="col-sm-10">
      <select name="relationship" class="form-control" required>
        <option value=""></option>
        <?php while ($row = $relationships->fetch()) { ?>
          <option value="<?php echo $row['id'] ?>"<?php if ($row['id'] == $details->relationship) { echo ' selected'; } ?>><?php echo $row['name'] ?></option>
        <?php } ?>
      </select>
    </div>
  </div>  

  <div class="form-group">
    <label class="col-sm-2 control-label">Details</label>
    <div class="col-sm-10">
      <textarea class="form-control" name="comments"><?php echo br2nl($details->comments) ?></textarea>
    </div>
  </div>

  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <button type="submit" class="btn btn-primary">Save</button>
      <input type="hidden" name="id" value="<?php echo $details->id ?>" />
    </div>
  </div>

</form>

<?php 
$event_id = $_GET['event'];
if ($event_id != 0) {
?>
<a href="events/dashboard/<?php echo $_GET['event'] ?>">Back to Event</a>

<?php ; } ?>

<?php require_once 'include.footer.php'; ?>

</body>
</html>
