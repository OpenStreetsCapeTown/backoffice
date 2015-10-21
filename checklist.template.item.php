<?php
require_once 'functions.php';
$id = (int)$_GET['id'];
$info = $db->query("SELECT * FROM planning_checklist_template WHERE id = $id");

if ($_POST) {
  $post = array(
    'name' => html($_POST['name']),
    'type' => $_POST['provider'] ? mysql_clean('provider') : mysql_clean('regular'),
  );
  if ($id) {
    $db->update("planning_checklist_template",$post,"id = $id");
  } else {
    $category = (int)$_GET['category'];
    $last = $db->query("SELECT MAX(position) AS max FROM planning_checklist_template WHERE category = $category");
    $post = array(
      'name' => html($_POST['name']),
      'category' => (int)$_GET['category'],
      'position' => $last->max+1,
      'type' => $_POST['provider'] ? mysql_clean('provider') : mysql_clean('regular'),
    );
    $db->insert("planning_checklist_template",$post);
    $info->category = (int)$_GET['category'];
  }
  header("Location: " . URL . "checklist/template#cat".$info->category);
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
    <label class="col-sm-2 control-label">
    Provider item?
    </label>
    <div class="col-sm-10">
      <input type="checkbox" 
        name="provider" value="1" <?php if ($info->type == 'provider') { echo 'checked'; } ?> />
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
