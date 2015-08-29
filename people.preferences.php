<?php
require_once 'functions.php';

$id = (int)$_GET['id'];
$name = getName($id);
if (!$name) {
  $name = "#$id";
}
$info = $db->query("SELECT volunteer FROM people WHERE id = $id");

if ($_POST) {
  $db->query("DELETE FROM people_preferences WHERE people = $id");
  foreach ($_POST['preference'] as $key => $value) {
    if (is_array($value)) {
      foreach ($value as $subkey => $subvalue) {
        if ($subvalue) {
          $post = array(
            'people' => $id,
            'preference' => (int)$subvalue,
          );
          $db->insert("people_preferences",$post);
        }
      }
    } elseif ($value) {
      $post = array(
        'people' => $id,
        'preference' => (int)$value,
      );
      $db->insert("people_preferences",$post);
    }
  }
  logThis(3, $id);
  header("Location: " . URL . "people/$id/saved");
  exit();
}

$applicable = $info->volunteer ? 'volunteers' : 'nonvolunteers';

$list = $db->query("SELECT * FROM preference_types WHERE applicable_to = 'all' OR applicable_to = '$applicable' ORDER BY id");
$answerlist = $db->query("SELECT * FROM preference_options WHERE active = 1");

while ($row = $answerlist->fetch()) {
  $answers[$row['type']][$row['id']] = $row['name'];
}

$settings = $db->query("SELECT * FROM people_preferences WHERE people = $id");
while ($row = $settings->fetch()) {
  $active[$row['preference']] = true;
}
?>
<!doctype html>
<html>
<head>
<title><?php echo $name ?> | Preferences | <?php echo SITENAME ?></title>
<?php echo $head ?>
</head>
<body>

<?php require_once 'include.header.php'; ?>

  <h1>Preferences</h1>

  <div class="alert alert-info">
    <p>Contact: <a href="people/<?php echo $id ?>"><?php echo $name ?></a></p>
  </div>

  <form method="post" class="form" role="form">

    <?php while ($row = $list->fetch()) { ?>
      <div class="form-group">
        <label><?php echo $row['name'] ?></label>
        <select name="preference[<?php echo $row['id'] ?>]<?php if ($row['answers'] == "multiple") { echo '[]'; } ?>" class="form-control" <?php if ($row['answers'] == "multiple") { echo 'multiple'; } ?>>
          <?php 
          // Show an empty option only for single answers because multiple answers can simply be left open
          if ($row['answers'] == "single") { ?>
            <option value=""></option>
          <?php } ?>
        <?php foreach ($answers[$row['id']] as $key => $value) { ?>
          <option value="<?php echo $key ?>"<?php if ($active[$key]) { echo ' selected'; } ?>><?php echo $value ?></option>
        <?php } ?>
        </select>
      </div>
    <?php } ?>

    <div class="form-group">
      <button type="submit" class="btn btn-primary">Save</button>
    </div>

  </form>

<?php require_once 'include.footer.php'; ?>

</body>
</html>
