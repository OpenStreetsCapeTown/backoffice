<?php
require_once 'functions.php';

$id = (int)$_GET['id'];
if ($id) {
  $info = $db->query("SELECT * FROM suburbs WHERE id = $id");
}

$areas = $db->query("SELECT * FROM areas WHERE active = 1 ORDER BY name");

if ($_POST) {
  $post = array(
    'name' => html($_POST['name']),
    'area' => (int)$_POST['area'],
  );
  if ($id) {
    $db->update("suburbs",$post,"id = $id");
  } else {
    $db->insert("suburbs",$post);
    $id = $db->insert_id;
  }
  header("Location: " . URL . "info/suburbs/saved");
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

<h1>Suburb</h1>

<form method="post" class="form form-horizontal">

  <div class="form-group">
    <label class="col-sm-2 control-label">Name</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="name" value="<?php echo $info->name ?>" />
    </div>
  </div>

	<div class="form-group">
		<label class="col-sm-2 control-label">Area</label>
		<div class="col-sm-10">
			<select name="area" class="form-control">
      <?php while ($row = $areas->fetch()) { ?>
				<option value="<?php echo $row['id'] ?>"<?php if ($row['id'] == $info->area) { echo ' selected'; } ?>><?php echo $row['name'] ?></option>
			<?php } ?>
			</select>
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
