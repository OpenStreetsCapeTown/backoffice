<?php
require_once 'functions.php';

$id = (int)$_GET['id'];

if ($_GET['delete']) {
  $delete = (int)$_GET['delete'];
  $db->query("UPDATE income_options SET active = 0 WHERE id = $delete LIMIT 1");
  $print = "Option was deleted";
}

if ($_POST) {
  $post = array(
    'name' => html($_POST['name']),
  );
  if ($id) {
    $db->update("income_options",$post,"id = $id");
  } else {
    $db->insert("income_options",$post);
    $id = $db->insert_id;
  }
  header("Location: " . URL . "info/income/saved");
  exit();
}

$list = $db->query("SELECT * FROM income_options WHERE active = 1 ORDER BY id");

if ($id) {
  $info = $db->query("SELECT * FROM income_options WHERE id = $id");
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

  <h1>Income Options</h1>

  <table class="table table-striped">
    <tr>
      <th>Name</th>
      <th>Edit</th>
      <th>Delete</th>
    </tr>
  <?php while ($row = $list->fetch()) { ?>
    <tr>
      <td><?php echo $row['name'] ?></td>
      <td><a href="edit/income/<?php echo $row['id'] ?>">Edit</a></td>
      <td><a href="income.php?delete=<?php echo $row['id'] ?>" onclick="javascript:return confirm('Are you sure?')">Delete</a></td>
    </tr>
  <?php $area[$row['id']] = $row['name']; } ?>
  </table>

  <h1><?php echo $id ? 'Edit' : 'Add' ?> Income Option</h1>

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
