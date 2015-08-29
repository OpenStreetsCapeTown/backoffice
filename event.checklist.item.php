<?php
require_once 'functions.php';
$id = (int)$_GET['id'];
$info = $db->query("SELECT * FROM planning_checklist WHERE id = $id");

if ($_POST) {
  $post = array(
    'name' => html($_POST['name']),
  );
  if ($id) {
    $db->update("planning_checklist",$post,"id = $id");
  } else {
    $event = (int)$_GET['event'];
    $category = (int)$_GET['category'];
    $last = $db->query("SELECT MAX(position) AS max FROM planning_checklist WHERE event = $event AND category = $category");
    $post = array(
      'name' => html($_POST['name']),
      'event' => (int)$_GET['event'],
      'category' => (int)$_GET['category'],
      'position' => $last->max+1,
    );
    $db->insert("planning_checklist",$post);
    $info->event = (int)$_GET['event'];
    $info->category = (int)$_GET['category'];
  }
  header("Location: " . URL . "events/checklist/" . $info->event . "#cat".$info->category);
  exit();
}
?>
<!doctype html>
<html>
<head>
<title><?php echo SITENAME ?></title>
<?php echo $head ?>
</head>
<body>

<?php require_once 'include.header.php'; ?>

<h1>Checklist item</h1>

<form method="post" class="form form-horizontal">

  <div class="form-group">
    <label class="col-sm-2 control-label">Name</label>
    <div class="col-sm-10">
      <input class="form-control" type="text" name="name" value="<?php echo $info->name ?>" />
    </div>
  </div>

  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <button type="submit" class="btn btn-primary">Save</button>
    </div>
  </div>

</form>

<?php require_once 'include.footer.php'; ?>

</body>
</html>
