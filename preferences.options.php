<?php
require_once 'functions.php';
$id = (int)$_GET['id'];
$type = (int)$_GET['type'];
$typeinfo = $db->query("SELECT * FROM preference_types WHERE id = $type");

if ($_GET['delete']) {
  $delete = (int)$_GET['delete'];
  $db->query("UPDATE preference_options SET active = 0 WHERE id = $delete LIMIT 1");
  $print = "Option was deleted";
}

if ($_POST) {
  $post = array(
    'name' => html($_POST['name']),
    'type' => $type,
  );
  if ($id) {
    $db->update("preference_options",$post,"id = $id");
  } else {
    $db->insert("preference_options",$post);
    $id = $db->insert_id;
  }
  header("Location: " . URL . "preference/$type/saved");
  exit();
}

$list = $db->query("SELECT * FROM preference_options WHERE type = $type AND active = 1 ORDER BY id");

if ($id) {
  $info = $db->query("SELECT * FROM preference_options WHERE id = $id");
}

if ($_GET['saved']) {
  $print = "Information has been saved";
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

  <?php if ($print) { echo "<div class=\"alert alert-success\">$print</div>"; } ?>

  <h1><?php echo $typeinfo->name ?> Options</h1>

  <table class="table table-striped">
    <tr>
      <th>Name</th>
      <th>Edit</th>
      <th>Delete</th>
    </tr>
  <?php while ($row = $list->fetch()) { ?>
    <tr>
      <td><?php echo $row['name'] ?></td>
      <td><a href="preference/<?php echo $type ?>/edit/<?php echo $row['id'] ?>">Edit</a></td>
      <td><a href="preference/<?php echo $type ?>/delete/<?php echo $row['id'] ?>" onclick="javascript:return confirm('Are you sure?')">Delete</a></td>
    </tr>
  <?php } ?>
  </table>

  <h1><?php echo $id ? 'Edit' : 'Add' ?> <?php echo $label ?> Option</h1>

<form method="post" class="form form-horizontal">

  <div class="form-group">
    <label class="col-sm-2 control-label">Name</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="name" value="<?php echo $info->name ?>" />
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
