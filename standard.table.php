<?php
require_once 'functions.php';

$table_prefix = "_options";

switch ($_GET['table']) {
  case 'identity':
    $table = 'identity';
    $label = 'Identity';
    break;
  case 'income':
    $table = 'identity';
    $label = 'Identity';
    break;
  case 'transport':
    $table = 'transport';
    $label = 'Transport';
    break;
  case 'language':
    $table = 'language';
    $label = 'Language';
    break;
  case 'communication':
    $table = 'communication';
    $label = 'Communication';
    break;
  case 'os_communication':
    $table = 'os_communication';
    $label = 'Open Streets Communication';
    break;
  case 'interaction':
    $table = 'interaction';
    $label = 'Interaction';
    break;
  case 'tags':
    $table = 'tags';
    $label = 'Tags';
    $description = true;
    break;
  case 'mailinglist':
    $table = 'mailinglist';
    $label = 'Mailing Lists';
    break;
  case 'whiteboard':
    $table = 'whiteboard';
    $label = 'Whiteboard';
    break;
  case 'skills':
    $table = 'skills';
    $label = 'Skills';
    $table_prefix = "";
    break;
  case 'event':
    $table = 'event_types';
    $label = 'Events';
    $table_prefix = "";
    break;
  case 'types':
    $table = 'types';
    $label = 'Relationship Types';
    $table_prefix = "";
    $description = true;
    break;
  // case 'donations':
  //   $table = 'donation_events';
  //   $label = 'Fundraising Events';
  //   $table_prefix = "";
  //   break;
  case 'referral':
    $table = 'referral_sources';
    $label = 'Referral';
    $table_prefix = "";
    break;
  case 'eventrelationships':
    $table = 'event_relationships';
    $label = 'Event Relationships';
    $description = true;
    $table_prefix = "";
    break;
  case 'organizationtypes':
    $table = 'organization_types';
    $label = 'Organization Types';
    $table_prefix = "";
    break;
  case 'organizationmaintypes':
    $table = 'organization_main_types';
    $label = 'Organization Main Types';
    $table_prefix = "";
    break;
  case 'locations':
    $table = 'locations';
    $label = 'Locations';
    $table_prefix = "";
    break;
}

$table_name = $table . $table_prefix;

$id = (int)$_GET['id'];

if ($_GET['delete']) {
  $delete = (int)$_GET['delete'];
  $db->query("UPDATE $table_name SET active = 0 WHERE id = $delete LIMIT 1");
  $print = "Option was deleted";
}

if ($_POST) {
  $post = array(
    'name' => html($_POST['name']),
  );
  if ($table == "whiteboard") {
    $post['email'] = html($_POST['email']);
  }
  if ($table == "organization_types") {
    $post['main_organization'] = $_POST['main_organization'] ? (int)$_POST['main_organization'] : "NULL";
  }
  if ($table == "event_relationships") {
    $post['parent'] = $_POST['parent'] ? (int)$_POST['parent'] : "NULL";
  }
  if ($description) {
    $post['description'] = html($_POST['description']);
  }
  if ($id) {
    $db->update("$table_name",$post,"id = $id");
  } else {
    $db->insert("$table_name",$post);
    $id = $db->insert_id;
  }
  header("Location: " . URL . "standard/{$_GET['table']}/saved");
  exit();
}

$list = $db->query("SELECT * FROM $table_name WHERE active = 1 ORDER BY name");

if ($id) {
  $info = $db->query("SELECT * FROM $table_name WHERE id = $id");
}

if ($_GET['saved']) {
  $print = "Information has been saved";
}

if ($_GET['action'] == "mailchimp") {
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
    $db->update($table_name,$post,"id = $id",1);
    header("Location: " . URL . "standard/mailinglist/edit/$id");
    exit();
  } else {
    $error = "Segment was not created! <br />Error: " . $return['error'];
  }
}

$mainorganizations = $db->query("SELECT * FROM organization_main_types WHERE active = 1 ORDER BY name");
$event_relationships = $db->query("SELECT * FROM event_relationships WHERE active = 1 AND PARENT IS NULL ORDER BY name");
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

  <?php if (!$id) { ?>

  <h1><?php echo $label ?> Options</h1>

  <table class="table table-striped">
    <tr>
      <th>Name</th>
      <th>Edit</th>
      <th>Delete</th>
    </tr>
  <?php while ($row = $list->fetch()) { ?>
    <tr>
      <?php if ($description) { ?>
        <td><strong><?php echo $row['name'] ?></strong>
        <?php if ($row['description']) { ?><br /><?php echo $row['description'] ?><?php } ?>
        </td>
      <?php } else { ?>
        <td><?php echo $row['name'] ?></td>
      <?php } ?>
      <td><a href="standard/<?php echo $_GET['table'] ?>/edit/<?php echo $row['id'] ?>">Edit</a></td>
      <td><a href="standard/<?php echo $_GET['table'] ?>/delete/<?php echo $row['id'] ?>" onclick="javascript:return confirm('Are you sure?')">Delete</a></td>
    </tr>
  <?php $area[$row['id']] = $row['name']; } ?>
  </table>

  <?php } ?>

  <h1><?php echo $id ? 'Edit' : 'Add' ?> <?php echo $label ?> Option</h1>

<form method="post" class="form form-horizontal">

  <div class="form-group">
    <label class="col-sm-2 control-label">Name</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="name" value="<?php echo $info->name ?>" />
    </div>
  </div>

  <?php if ($table == "whiteboard") { ?>

    <div class="form-group">
      <label class="col-sm-2 control-label">Moderator E-mail</label>
      <div class="col-sm-10">
        <input type="email" class="form-control" name="email" value="<?php echo $info->email ?>" />
      </div>
    </div>

  <?php } ?>

  <?php if ($table == "organization_types") { ?>

    <div class="form-group">
      <label class="col-sm-2 control-label">Main Type</label>
      <div class="col-sm-10">
        <select name="main_organization" class="form-control">
          <option value=""></option>
          <?php while ($row = $mainorganizations->fetch()) { ?>
            <option value="<?php echo $row['id'] ?>"<?php if ($row['id'] == $info->main_organization) { echo ' selected'; } ?>><?php echo $row['name'] ?></option>
          <?php } ?>
        </select>
      </div>
    </div>

  <?php } ?>

  <?php if ($table == "event_relationships") { ?>

    <div class="form-group">
      <label class="col-sm-2 control-label">Main Type</label>
      <div class="col-sm-10">
        <select name="parent" class="form-control">
          <option value=""></option>
          <?php while ($row = $event_relationships->fetch()) { ?>
            <option value="<?php echo $row['id'] ?>"<?php if ($row['id'] == $info->parent) { echo ' selected'; } ?>><?php echo $row['name'] ?></option>
          <?php } ?>
        </select>
      </div>
    </div>

  <?php } ?>

  <?php if ($description) { ?>
    <div class="form-group">
      <label class="col-sm-2 control-label">Description</label>
      <div class="col-sm-10">
        <textarea class="form-control" name="description"><?php echo br2nl($info->description) ?></textarea>
      </div>
    </div>
  <?php } ?>

  <?php if ($table == "mailinglist" && $id) { ?>

    <div class="form-group">
      <label class="col-sm-2 control-label">Mailchimp Segment</label>
      <div class="col-sm-10">
        <?php if ($info->mailchimp_id) { ?>
          <?php echo $info->mailchimp_id ?>
        <?php } else { ?>
          No Mailchimp Segment ID found! <a href="standard/mailinglist/edit/<?php echo $id ?>/mailchimp">Create segment</a>
        <?php } ?>
      </div>
    </div>

  <?php } ?>

  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <button type="submit" class="btn btn-primary">Save</button>
    </div>
  </div>

</form>


<?php require_once 'include.footer.php'; ?>

</body>
</html>
