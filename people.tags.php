<?php
require_once 'functions.php';

$id = (int)$_GET['id'];
$name = getName($id);

if ($_POST) {
  $db->query("DELETE FROM people_tags WHERE people = $id");
  foreach ($_POST['tags'] as $key => $value) {
    if ($value) {
      $post = array(
        'people' => $id,
        'tag' => (int)$value,
      );
      $db->insert("people_tags",$post);
    }
  }
  logThis(14, $id);
  header("Location: " . URL . "people/$id/saved");
  exit();
}

$list = $db->query("SELECT * FROM tags_options WHERE active = 1 ORDER BY name");

$peopletags = $db->query("SELECT tag FROM people_tags WHERE people = $id");
while ($row = $peopletags->fetch()) {
  $active[$row['tag']] = true;
}
?>
<!doctype html>
<html>
<head>
<title><?php echo $name ?> | Tags | <?php echo SITENAME ?></title>
<?php echo $head ?>
<script type="text/javascript" src="js/select2.min.js"></script>
<link rel="stylesheet" href="css/select2.min.css" />
<script type="text/javascript">
$(function(){
    $("select").select2();
});
</script>
</head>
<body>

<?php require_once 'include.header.php'; ?>

  <h1>Tags</h1>

  <div class="alert alert-info">
    <p>Contact: <a href="people/<?php echo $id ?>"><?php echo $name ?></a></p>
  </div>

  <form method="post" class="form" role="form">

    <div class="form-group">
      <label class="col-sm-2 control-label">Select tag(s)</label>
      <div class="col-sm-10">
        <select name="tags[]" class="form-control" multiple size="<?php echo $list->num_rows ?>" >
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
