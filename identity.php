<?php
require_once 'functions.php';

switch ($_GET['table']) {
  case 'identity':
    $table = 'identity';
    $label = 'Identity';
  case 'income':
    $table = 'identity';
    $label = 'Identity';
  case 'transport':
    $table = 'transport';
    $label = 'Transport';
  case 'language':
    $table = 'language';
    $label = 'Language';
}

$id = (int)$_GET['id'];

if ($_GET['delete']) {
  $delete = (int)$_GET['delete'];
  $db->query("UPDATE identity_options SET active = 0 WHERE id = $delete LIMIT 1");
  $print = "Option was deleted";
}

if ($_POST) {
  $post = array(
    'name' => html($_POST['name']),
  );
  if ($id) {
    $db->update("identity_options",$post,"id = $id");
  } else {
    $db->insert("identity_options",$post);
    $id = $db->insert_id;
  }
  header("Location: " . URL . "info/identity/saved");
  exit();
}

$list = $db->query("SELECT * FROM identity_options WHERE active = 1 ORDER BY id");

if ($id) {
  $info = $db->query("SELECT * FROM identity_options WHERE id = $id");
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

  <h1>Identity Options</h1>

  <table class="table table-striped">
    <tr>
      <th>Name</th>
      <th>Edit</th>
      <th>Delete</th>
    </tr>
  <?php while ($row = $list->fetch()) { ?>
    <tr>
      <td><?php echo $row['name'] ?></td>
      <td><a href="edit/identity/<?php echo $row['id'] ?>">Edit</a></td>
      <td><a href="identity.php?delete=<?php echo $row['id'] ?>" onclick="javascript:return confirm('Are you sure?')">Delete</a></td>
    </tr>
  <?php $area[$row['id']] = $row['name']; } ?>
  </table>

  <h1><?php echo $id ? 'Edit' : 'Add' ?> Identity Option</h1>

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
