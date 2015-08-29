<?php
require_once 'functions.php';

$id = (int)$_GET['id'];

if ($_GET['action'] == 'delete') {
  $delete = $id;
  $db->query("UPDATE mailinglist_options SET active = 0 WHERE id = $delete LIMIT 1");
  $print = "Mailing list was deleted";
  require_once 'mailchimp.php';
  $MailChimp = new \Drewm\MailChimp(MAILCHIMP_API_KEY);

  $details = $db->query("SELECT * FROM mailinglist_options WHERE id = $delete");

  if ($details->mailchimp_id) {
    $return = $MailChimp->call('lists/segment-del',array(
      'id' => MAILCHIMP_LIST,
      'seg_id' => $details->mailchimp_id,
    ));
    if ($return['complete']) {
      $print .= "<br />Mailchimp segment was also removed";
    } else {
      $error .= "Mailchimp segment was <strong>NOT</strong> removed. Error message: <br />" . $return['error'];
    }
  }
  $id = false;
}

if ($_POST) {
  $post = array(
    'name' => html($_POST['name']),
    'description' => html($_POST['description']),
  );
  if ($id) {
    $db->update("mailinglist_options",$post,"id = $id");
  } else {
    $db->insert("mailinglist_options",$post);
    $id = $db->insert_id;
  }
  header("Location: " . URL . "info/mailinglists/saved");
  exit();
}

$list = $db->query("SELECT * FROM mailinglist_options WHERE active = 1 ORDER BY id");

if ($id) {
  $info = $db->query("SELECT * FROM mailinglist_options WHERE id = $id");
}

if ($_GET['saved']) {
  $print = "Information has been saved";
}

if ($_GET['action'] == 'segment') {
  require_once 'mailchimp.php';
  $MailChimp = new \Drewm\MailChimp(MAILCHIMP_API_KEY);

  $return = $MailChimp->call('lists/segment-add',array(
    'id' => MAILCHIMP_LIST,
    'opts' => array(
      'type' => 'static',
      'name' => $info->name,
    ),
  ));
  if ($return['id']) {
    $post = array(
      'mailchimp_id' => mysql_clean($return['id']),
    );
    $db->update('mailinglist_options',$post,"id = $id");
    $print = "Segment was created and saved";
  } else {
    $error = "Segment was not created! <br />Error: " . $return['error'];
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

  <?php if ($print) { echo "<div class=\"alert alert-success\">$print</div>"; } ?>
  <?php if ($error) { echo "<div class=\"alert alert-danger\">$error</div>"; } ?>

  <h1>Mailing List Options</h1>

  <table class="table table-striped">
    <tr>
      <th>Name</th>
      <th>Segment ID</th>
      <th>View list</th>
      <th>Edit</th>
      <th>Delete</th>
    </tr>
  <?php while ($row = $list->fetch()) { ?>
    <tr>
      <td><?php echo $row['name'] ?></td>
      <td><?php echo $row['mailchimp_id'] ? $row['mailchimp_id'] : '<a href="segment/mailinglists/'.$row['id'].'">Create segment</a>'; ?></td>
      <td><a href="people.filter.php?mailinglist=<?php echo $row['id'] ?>">View list</a></td>
      <td><a href="edit/mailinglists/<?php echo $row['id'] ?>">Edit</a></td>
      <td><a href="delete/mailinglists/<?php echo $row['id'] ?>" onclick="javascript:return confirm('Are you sure?')">Delete</a></td>
    </tr>
  <?php } ?>
  </table>

  <h1><?php echo $id ? 'Edit' : 'Add' ?> Mailing List</h1>

  <form method="post" class="form form-horizontal">

    <div class="form-group">
      <label class="col-sm-2 control-label">Name</label>
      <div class="col-sm-10">
        <input type="text" class="form-control" name="name" value="<?php echo $info->name ?>" />
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">Description</label>
      <div class="col-sm-10">
        <input class="form-control" type="text" name="description" value="<?php echo $info->description ?>" />
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
