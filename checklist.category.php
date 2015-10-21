<?php
require_once 'functions.php';
$id = (int)$_GET['id'];
$event = (int)$_GET['event'];
$info = $db->query("SELECT * FROM planning_categories WHERE id = $id");

if (!$id) {
  $max = $db->query("SELECT MAX(position) AS max FROM planning_categories WHERE active = 1");
  $position = $max->max+1;
}

if ($_POST) {
  $post = array(
    'name' => html($_POST['name']),
    'position' => (int)$_POST['position'],
  );
  if ($id) {
    $db->update("planning_categories",$post,"id = $id");
  } else {
    $db->insert("planning_categories",$post);
    $id = $db->insert_id;
  }
  if ($event) {
    header("Location: " . URL . "events/checklist/" . $event . "#cat".$id);
    exit();
  } else {
    header("Location: " . URL . "checklist/template#cat".$id);
    exit();
  }
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

<h1>Checklist category</h1>

<form method="post" class="form form-horizontal">

  <div class="form-group">
    <label class="col-sm-2 control-label">Name</label>
    <div class="col-sm-10">
      <input class="form-control" type="text" name="name" value="<?php echo $info->name ?>" />
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">Position</label>
    <div class="col-sm-10">
      <input class="form-control" type="number" name="position" value="<?php echo $position ?>" />
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
