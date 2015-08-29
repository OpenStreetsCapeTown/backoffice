<?php
require_once 'functions.php';

$id = (int)$_GET['id'];
$info = $db->query("SELECT * FROM events WHERE id = $id");

if ($_POST) {
  $post = array(
    'name' => html($_POST['name']),
    'place' => html($_POST['place']),
    'date' => mysql_clean(format_date("Y-m-d", $_POST['date'])),
    'parent_event' => $_POST['parent_event'] ? (int)$_POST['parent_event'] : "NULL",
    'details' => html($_POST['details']),
    'type' => (int)$_POST['type'],
  );
  if ($id) {
    $db->update("events",$post,"id = $id");
  } else {
    $db->insert("events",$post);
    $id = $db->insert_id;
  }
  header("Location: " . URL . "events/list");
  exit();
}

$types = $db->query("SELECT * FROM event_types WHERE active = 1 ORDER BY name");
$events = $db->query("SELECT * FROM events WHERE active = 1 ORDER BY name");
?>
<!doctype html>
<html>
<head>
<title><?php echo $id ? "Edit" : "Add" ?> Event | <?php echo SITENAME ?></title>
<?php echo $head ?>
</head>
<body>

<?php require_once 'include.header.php'; ?>

<h1><?php echo $id ? "Edit" : "Add" ?> Event</h1>

<form method="post" class="form form-horizontal">

  <div class="form-group">
    <label class="col-sm-2 control-label">Name</label>
    <div class="col-sm-10">
      <input class="form-control" type="text" name="name" value="<?php echo $info->name ?>" required />
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">Place</label>
    <div class="col-sm-10">
      <input class="form-control" type="text" name="place" value="<?php echo $info->place ?>" />
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">Date</label>
    <div class="col-sm-10">
      <input class="form-control" type="date" name="date" value="<?php echo $info->date ?>" required />
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">Type</label>
    <div class="col-sm-10">
      <select name="type" class="form-control">
      <?php while ($row = $types->fetch()) { ?>
        <option value="<?php echo $row['id'] ?>"<?php if ($row['id'] == $info->type) { echo ' selected'; } ?>><?php echo $row['name'] ?></option>
      <?php } ?>
      </select>
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">Parent event</label>
    <div class="col-sm-10">
      <select name="parent_event" class="form-control">
        <option value=""></option>
      <?php while ($row = $events->fetch()) { ?>
        <option value="<?php echo $row['id'] ?>"<?php if ($row['id'] == $info->parent_event) { echo ' selected'; } ?>><?php echo $row['name'] ?></option>
      <?php } ?>
      </select>
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">Details</label>
    <div class="col-sm-10">
      <textarea class="form-control" name="details"><?php echo $info->details ?></textarea>
    </div>
  </div>

  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <button type="submit" class="btn btn-primary">Save</button>
    </div>
  </div>

</form>

<?php require_once 'include.footer.php'; ?>
<script src="//cdn.jsdelivr.net/webshim/1.12.4/extras/modernizr-custom.js"></script>
<script src="//cdn.jsdelivr.net/webshim/1.12.4/polyfiller.js"></script>
<script>
  webshims.setOptions('waitReady', false);
  webshim.setOptions("forms-ext", {
    "widgets": {
      "startView": 2,
      "openOnFocus": true
    }
  });
  webshims.polyfill('forms forms-ext');
</script>

</body>
</html>
