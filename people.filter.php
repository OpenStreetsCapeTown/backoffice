<?php
require_once 'functions.php';

if ($_GET['mailinglist']) {
  $_POST['mailinglist'][] = $_GET['mailinglist'];
  $mailinglist = (int)$_GET['mailinglist'];
  $segmentinfo = $db->query("SELECT mailchimp_id AS segment FROM mailinglist_options WHERE id = $mailinglist");
}

$segment = (int)$_GET['segment'];
if ($segment) {
  $info = $db->query("SELECT * FROM segments WHERE id = $segment");
  $post = convert_quotation_marks($info->post_array, true);
  $post = unserialize($post);
  foreach ($post as $key => $value) {
    $_POST[$key] = $value;
  }
  if ($_POST['segmentupdate']) {
    $_POST['sync'] = $info->mailchimp_id;
    $post = array(
      'date' => 'NOW()',
      'people' => (int)count($_POST['email']),
    );
    $db->update("segments",$post,"id = $segment");
  }
}

if ($_POST['segmentname']) {
  require_once 'mailchimp.php';
  $MailChimp = new \Drewm\MailChimp(MAILCHIMP_API_KEY);

  $return = $MailChimp->call('lists/segment-add',array(
    'id' => MAILCHIMP_LIST,
    'opts' => array(
      'type' => 'static',
      'name' => $_POST['segmentname'],
    ),
  ));
  if ($return['id']) {
    $post = array(
      'name' => html($_POST['segmentname']),
      'post_array' => mysql_clean($_POST['post']),
      'mailchimp_id' => mysql_clean($return['id']),
      'date' => 'NOW()',
      'people' => (int)count($_POST['email']),
    );
    $db->insert("segments",$post);
    $segment = $db->insert_id;
    $print = "Segment was created and saved<br />";
    $post = convert_quotation_marks($_POST['post'], true);
    $post = unserialize($post);
    foreach ($post as $key => $value) {
      $_POST[$key] = $value;
      $_POST['sync'] = $return['id'];
    }
    $info = $db->query("SELECT * FROM segments WHERE id = $segment");
  } else {
    $error .= "Segment was not created! <br />Error: " . $return['error'];
  }
}

if ($_POST['sync']) {
  $segment = $_POST['sync'];
  require_once 'mailchimp.php';
  $MailChimp = new \Drewm\MailChimp(MAILCHIMP_API_KEY);

  foreach ($_POST['email'] as $value) {
    $batch[] = array('email' => $value);
    $batch_subscribe[] = array('email' => array('email' => $value));
  }

  // First, let's reset this segment
  $return = $MailChimp->call('lists/static-segment-reset',array(
    'id' => MAILCHIMP_LIST,
    'seg_id' => $segment,
  ));
  if (!$return['complete']) {
    $error .= "Segment was not reset properly! <br />Error: " . $return['error'];
  }

  // Now, let's ensure those mail addresses are on the master list
  $return = $MailChimp->call('lists/batch-subscribe',array(
    'id' => MAILCHIMP_LIST,
    'seg_id' => $segment,
    'batch' => $batch_subscribe,
    'double_optin' => false,
    'update_existing' => true,
  ));
  $good = $return['add_count'];
  if ($good) {
    $print .= "A total of $good e-mail addresses were newly added to the Master list<br />";
  }
  $bad = $return['errors'];
  if (count($bad)) {
    $error .= 'There were ' . count($bad) . ' errors adding people to the master list!<br />';
    foreach ($bad as $key => $value) {
      if (is_array($value['email'])) {
        $add_this_to_error = false;
        foreach ($value['email'] as $subvalue) {
          $add_this_to_error .= $subvalue . ", ";
        }
        $add_this_to_error = $add_this_to_error ? substr($add_this_to_error, 0, -2) : false;
      }
      if ($value['code'] == 215) {
        $value['error'] = "E-mail address is not permitted. Revise this in MailChimp and then deactivate in the database";
      }
      $error .= "<strong>Error {$value['cide']}:</strong> {$value['error']} ($add_this_to_error)<br />";
    }
  }

  // Let's now add them to the segment
  $return = $MailChimp->call('lists/static-segment-members-add',array(
    'id' => MAILCHIMP_LIST,
    'seg_id' => $segment,
    'batch' => $batch,
  ));
  $good = $return['success_count'];
  if ($good) {
    $print .= "A total of $good e-mail addresses were successfully synced";
  }
  $bad = $return['errors'];
  if (count($bad)) {
    $error .= 'There were <strong>' . count($bad) . '</strong> errors adding people to this segment!<br />';
    foreach ($bad as $key => $value) {
      if (is_array($value['email'])) {
        $add_this_to_error = false;
        foreach ($value['email'] as $subvalue) {
          $add_this_to_error .= $subvalue . ", ";
        }
        $add_this_to_error = $add_this_to_error ? substr($add_this_to_error, 0, -2) : false;
      }
      if ($value['code'] == 215) {
        $value['error'] = "E-mail address is not permitted. Revise this in MailChimp and then deactivate in the database";
      }
      $error .= "<strong>Error {$value['cide']}:</strong> {$value['error']} ($add_this_to_error)<br />";
    }
  }
}

$selector = $_POST['selector'] == "OR" ? "OR" : "AND";

if ($_POST['email_required']) {
  $sql .= "email != '' AND active_mailings = 1 AND ";
}

if (is_array($_POST['type'])) {
  $in = false;
  foreach ($_POST['type'] as $key => $value) {
    $in .= (int)$value . ",";
  }
  $in = substr($in, 0, -1);
  $exists .= $in ? "EXISTS (SELECT * FROM people_types WHERE people = people.id AND type IN ($in)) $selector " : '';
}

if (is_array($_POST['skills'])) {
  $in = false;
  foreach ($_POST['skills'] as $key => $value) {
    $in .= (int)$value . ",";
  }
  $in = substr($in, 0, -1);
  $exists .= $in ? "EXISTS (SELECT * FROM people_skills WHERE people = people.id AND skill IN ($in)) $selector " : '';
}

if (is_array($_POST['subscription'])) {
  $in = false;
  foreach ($_POST['subscription'] as $key => $value) {
    $in .= (int)$value . ",";
  }
  $in = substr($in, 0, -1);
  $exists .= $in ? "EXISTS (SELECT * FROM people_preferences WHERE people = people.id AND preference IN ($in)) $selector " : '';
}

if (is_array($_POST['mailinglist'])) {
  $in = false;
  foreach ($_POST['mailinglist'] as $key => $value) {
    $in .= (int)$value . ",";
  }
  $in = substr($in, 0, -1);
  $exists .= $in ? "EXISTS (SELECT * FROM people_mailinglists WHERE people = people.id AND mailinglist IN ($in)) $selector " : '';
}

if (is_array($_POST['event'])) {
  $exists .= "( ";
  foreach ($_POST['event'] as $key => $value) {
    $explode = explode(".", $value);
    $event = (int)$explode[0];
    $exists .= "EXISTS (SELECT * FROM people_events WHERE people = people.id AND event = $event) OR ";
  }
  $exists = substr($exists, 0, -3);
  $exists .= ") $selector ";
}

if (is_array($_POST['event_relationship'])) {
  $exists .= "( ";
  foreach ($_POST['event_relationship'] as $key => $value) {
    $explode = explode(".", $value);
    $relationship = (int)$explode[0];
    $exists .= "EXISTS (SELECT * FROM people_events WHERE people = people.id AND relationship = $relationship) OR ";
  }
  $exists = substr($exists, 0, -3);
  $exists .= ") $selector ";
}

if (is_array($_POST['tags'])) {
  $in = false;
  foreach ($_POST['tags'] as $key => $value) {
    $in .= (int)$value . ",";
  }
  $in = substr($in, 0, -1);
  $exists .= $in ? "EXISTS (SELECT * FROM people_tags WHERE people = people.id AND tag IN ($in)) $selector " : '';
}
if (is_array($_POST['suburbs'])) {
  $in = false;
  foreach ($_POST['suburbs'] as $key => $value) {
    $in .= (int)$value . ",";
  }
  $in = substr($in, 0, -1);
  $exists .= $in ? "people.suburb IN ($in) $selector " : '';
}

$sql = $sql ? "AND " . substr($sql, 0, -4) : '';
$sql .= $exists ? " AND (" . substr($exists, 0, -4) . ")" : '';

$types = $db->query("SELECT * FROM types WHERE active = TRUE ORDER BY name");
$skills = $db->query("SELECT * FROM skills WHERE active = TRUE ORDER BY name");
$events = $db->query("SELECT * FROM events WHERE active = TRUE ORDER BY date DESC");
$event_relationships = $db->query("SELECT * FROM event_relationships WHERE active = TRUE ORDER BY name");
$tags = $db->query("SELECT * FROM tags_options WHERE active = TRUE ORDER BY name");
$mailinglist = $db->query("SELECT * FROM mailinglist_options WHERE active = TRUE ORDER BY name");
$suburbs = $db->query("SELECT * FROM suburbs WHERE active = 1 ORDER BY area, name");
$areas = $db->query("SELECT suburbs.*, areas.name AS area
FROM suburbs JOIN areas ON suburbs.area = areas.id
WHERE suburbs.active = 1 ORDER BY areas.name, suburbs.name");

if ($sql) {
  $list = $db->query("SELECT * FROM people WHERE active = 1 $sql ORDER BY lastname, firstname, organization");
}

if (!$_POST['event']) {
  $_POST['event'] = array();
}

if (!$_POST['event_relationship']) {
  $_POST['event_relationship'] = array();
}

if (!$_POST['type']) {
  $_POST['type'] = array();
}

if (!$_POST['mailinglist']) {
  $_POST['mailinglist'] = array();
}

if (!$_POST['skills']) {
  $_POST['skills'] = array();
}

if (!$_POST['suburbs']) {
  $_POST['suburbs'] = array();
}

if (!$_POST['tags']) {
  $_POST['tags'] = array();
}

function convert_quotation_marks($string, $reverse = false) {
  $replace = array('"' => "'");
  if ($reverse) {
    $replace = array_flip($replace);
  }
  return strtr($string, $replace);
}
?>
<!doctype html>
<html>
<head>
<title><?php echo SITENAME ?></title>
<?php echo $head ?>
<style type="text/css">
table {border:1px solid #ccc; width:100%;table-layout: fixed;}
th, td { max-width:20%;white-space:nowrap; overflow:hidden; text-overflow: ellipsis; }
td.short,th.short{width:70px}
</style>
</head>
<body>

<?php require_once 'include.header.php'; ?>

<h1>Filter People</h1>

<?php if ($print) { echo "<div class=\"alert alert-success\">$print</div>"; } ?>
<?php if ($error) { echo "<div class=\"alert alert-danger\">$error</div>"; } ?>

<?php if (!$_GET['mailinglist']) { ?>

<form method="post" class="form form-horizontal">

  <div class="form-group">
    <label class="col-sm-2 control-label">Relationship</label>
    <div class="col-sm-10">
      <select name="type[]" class="form-control" multiple>
      <?php while ($row = $types->fetch()) { ?>
        <option value="<?php echo $row['id'] ?>"<?php if (in_array($row['id'], $_POST['type'])) { echo ' selected'; } ?>><?php echo $row['name'] ?></option>
      <?php } ?>
      </select>
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">Tags</label>
    <div class="col-sm-10">
      <select name="tags[]" class="form-control" multiple>
      <?php while ($row = $tags->fetch()) { ?>
        <option value="<?php echo $row['id'] ?>"<?php if (in_array($row['id'], $_POST['tags'])) { echo ' selected'; } ?>><?php echo $row['name'] ?></option>
      <?php } ?>
      </select>
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">Skills</label>
    <div class="col-sm-10">
      <select name="skills[]" class="form-control" multiple>
      <?php while ($row = $skills->fetch()) { ?>
        <option value="<?php echo $row['id'] ?>"<?php if (in_array($row['id'], $_POST['skills'])) { echo ' selected'; } ?>><?php echo $row['name'] ?></option>
      <?php } ?>
      </select>
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">Event</label>
    <div class="col-sm-10">
      <select class="form-control" name="event[]" multiple size="5">
      <?php while ($row = $events->fetch()) { ?>
          <option value="<?php echo $row['id'] ?>"<?php if (in_array($row['id'], $_POST['event'])) { echo ' selected'; } ?>><?php echo $row['name'] ?></option>
      <?php } ?>
      </select>
    </div>
  </div>

    <div class="form-group">
    <label class="col-sm-2 control-label">Event Relationship</label>
    <div class="col-sm-10">
      <select class="form-control" name="event_relationship[]" multiple size="5">
      <?php while ($row = $event_relationships->fetch()) { ?>
          <option value="<?php echo $row['id'] ?>"<?php if (in_array($row['id'], $_POST['event_relationship'])) { echo ' selected'; } ?>><?php echo $row['name'] ?></option>
      <?php } ?>
      </select>
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">Suburb</label>
    <div class="col-sm-10">
      <select class="form-control" name="suburbs[]" multiple size="5">
      <?php while ($row = $areas->fetch()) { ?>
        <?php if ($area != $row['area']) { ?>
        <?php if ($area) { ?>
        </optgroup>
        <?php } ?>
        <optgroup label="<?php echo $row['area'] ?>">
      <?php } ?>
          <option value="<?php echo $row['id'] ?>" <?php if (in_array($row['id'], $_POST['suburbs'])) { echo ' selected'; } ?>><?php echo $row['name'] ?></option>
        <?php $area = $row['area']; } ?>
        </optgroup>
      </select>
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">Mailing List Subscription</label>
    <div class="col-sm-10">
      <select name="mailinglist[]" class="form-control" multiple>
      <?php while ($row = $mailinglist->fetch()) { ?>
        <option value="<?php echo $row['id'] ?>"<?php if (in_array($row['id'], $_POST['mailinglist'])) { echo ' selected'; } ?>><?php echo $row['name'] ?></option>
      <?php } ?>
      </select>
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">How to apply filters</label>
    <div class="col-sm-10">
      <select name="selector" class="form-control">
        <option value="AND" <?php if ($selector == "AND") { echo 'selected'; } ?>>If you select multiple types of filters, filter people who have ALL of these (AND)</option>
        <option value="OR" <?php if ($selector == "OR") { echo 'selected'; } ?>>If you select multiple types of filters, filter people who have a match in any of these (OR)</option>
      </select>
    </div>
  </div>

  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <div class="checkbox">
        <label>
          <input type="checkbox" name="email_required" value="1" <?php echo $_POST['email_required'] ? 'checked' : ''; ?> /> 
            Only those with an e-mail and active for mailings
        </label>
      </div>
    </div>
  </div>

  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <button type="submit" class="btn btn-primary">Search</button>
    </div>
  </div>

</form>

<?php } ?>

<?php if ($_POST) { ?>
  <div class="alert alert-info"><?php echo (int)$list->num_rows ?> people found.</div>
<?php } ?>

<?php if ($list->num_rows) { ?>

<?php if ($_GET['mailinglist']) { ?>

  <h2>Segment</h2>
  <p>Segment ID: <strong><?php echo $segmentinfo->segment ?></strong></p>
  <form method="post">
    <p>
      <button type="submit" name="sync" value="<?php echo $segmentinfo->segment ?>" class="btn btn-primary btn-lg">
        <i class="glyphicon glyphicon-refresh"></i>
        Sync this segment
      </button>
    </p>
    <?php while ($row = $list->fetch()) { ?>
      <input type="hidden" name="email[]" value="<?php echo $row['email'] ?>" />
    <?php } $list->reset(); ?>
  </form>

<?php } else { ?>

  <h2><?php echo $segment ? "Update" : "Create" ?> Segment</h2>

  <form method="post" class="form form-horizontal">

    <div class="form-group">
      <label class="col-sm-2 control-label">Segment Name</label>
      <div class="col-sm-10">
        <?php if ($segment) { echo $info->name; } else { ?>
          <input class="form-control" type="text" name="segmentname" />
        <?php } ?>
      </div>
    </div>

    <div class="form-group">
      <div class="col-sm-offset-2 col-sm-10">
        <button type="submit" class="btn btn-primary" name="segmentupdate" value="1">
        <?php if ($segment) { ?>
          <i class="glyphicon glyphicon-refresh"></i>
        <?php } ?>
        <?php echo $segment ? "Update" : "Create" ?></button>
      </div>
    </div>

    <input type="hidden" name="post" value="<?php echo convert_quotation_marks(serialize($_POST)) ?>" />
    <?php while ($row = $list->fetch()) { ?>
      <input type="hidden" name="email[]" value="<?php echo $row['email'] ?>" />
    <?php } $list->reset(); ?>
    
  </form>

<?php } ?>

<table class="table table-striped">
  <tr>
    <th>Surname</th>
    <th>Firstname</th>
    <th>Organization</th>
    <th>E-mail</th>
    <th class="short">View</th>
    <th class="short">Edit</th>
  </tr>
<?php while ($row = $list->fetch()) { ?>
  <tr>
    <td><?php echo $row['lastname'] ?></td>
    <td><?php echo $row['firstname'] ?></td>
    <td><?php echo $row['organization'] ?></td>
    <td><?php echo $row['email']; $bcc .= $row['email'] . ";"; ?></td>
    <td class="short"><a href="people/<?php echo $row['id'] ?>">View</a></td>
    <td class="short"><a href="people.php?id=<?php echo $row['id'] ?>">Edit</a></td>
  </tr>
<?php } ?>
</table>

<?php if ($bcc) { ?>
  <p><a href="mailto:info@openstreets.co.za?bcc=<?php echo $bcc ?>" class="btn btn-primary btn-lg"><i class="fa fa-envelope"></i> Send regular mail (BCC all)</a></p>
<?php } ?>

<?php } ?>

<?php require_once 'include.footer.php'; ?>

</body>
</html>
