<?php
require_once 'functions.php';

$id = (int)$_GET['id'];
$name = getName($id);

if ($_POST) {
  $db->query("DELETE FROM people_mailinglists WHERE people = $id");
  foreach ($_POST['mailinglist'] as $key => $value) {
    if ($value) {
      $post = array(
        'people' => $id,
        'mailinglist' => (int)$value,
      );
      $db->insert("people_mailinglists",$post);
    }
  }
  logThis(10, $id);
  header("Location: " . URL . "people/$id/saved");
  exit();
}

$list = $db->query("SELECT * FROM mailinglist_options WHERE active = TRUE ORDER BY name");

$peoplemailinglist = $db->query("SELECT mailinglist FROM people_mailinglists WHERE people = $id");
while ($row = $peoplemailinglist->fetch()) {
  $active[$row['mailinglist']] = true;
}
?>
<!doctype html>
<html>
<head>
<title><?php echo $name ?> | Mailing Lists | <?php echo SITENAME ?></title>
<?php echo $head ?>
</head>
<body>

<?php require_once 'include.header.php'; ?>

  <h1>Mailing Lists</h1>

  <div class="alert alert-info">
    <p>Contact: <a href="people/<?php echo $id ?>"><?php echo $name ?></a></p>
  </div>

  <form method="post" class="form" role="form">

    <div class="form-group">
      <label class="col-sm-2 control-label">Select Mailing List(s)</label>
      <div class="col-sm-10">
        <select name="mailinglist[]" class="form-control" multiple size="<?php echo $list->num_rows ?>" >
        <?php while ($row = $list->fetch()) { ?>
          <option value="<?php echo $row['id'] ?>"<?php if ($active[$row['id']]) { echo ' selected'; } ?>><?php echo $row['name'] ?></option>
        <?php } ?>
        </select>
      </div>
    </div>

    <div class="form-group">
      <button type="submit" class="btn btn-primary" name="save" value="true">Save</button>
    </div>

  </form>

<?php require_once 'include.footer.php'; ?>

</body>
</html>
