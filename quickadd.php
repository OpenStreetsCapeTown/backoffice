<?php
require_once 'functions.php';
$events = $db->query("SELECT * FROM events WHERE active = 1 ORDER BY date DESC");
$relationships = $db->query("SELECT * FROM event_relationships WHERE active = 1 ORDER BY name");
$types = $db->query("SELECT * FROM event_types WHERE active = 1 ORDER BY name");
$mailinglists = $db->query("SELECT * FROM mailinglist_options WHERE active = 1 ORDER BY name");

if ($_POST) {
  $relationship = (int)$_POST['relationship'];
  if ($_POST['link'] == "new") {
    if (!$_POST['eventname']) {
      die("You wanted to add a new event but did not enter a name. Please go back and review.");
    }
    if (!$_POST['type']) {
      die("You wanted to add a new event but did not select an event type. Please go back and review.");
    }
    if (!$relationship) {
      die("You must set a relationship. Please go back and review.");
    }
    $post = array(
      'name' => html($_POST['eventname']),
      'place' => html($_POST['place']),
      'date' => mysql_clean(format_date("Y-m-d", $_POST['date'])),
      'type' => (int)$_POST['type'],
      'parent_event' => $_POST['parent_event'] ? (int)$_POST['parent_event'] : "NULL",
    );
    $db->insert("events",$post);
    $event = $db->insert_id;
  } elseif ($_POST['link'] == "existing") {
    $event = (int)$_POST['event'];
    if (!$event) {
      die("You must select an event!");
    }
  }
  foreach ($_POST['firstname'] as $key => $value) {
    if ($_POST['firstname'][$key] || $_POST['lastname'][$key] || $_POST['organization'][$key] || $_POST['email'][$key]) {
      $id = findContact($_POST['email'][$key]);
      if ($id) {
        $post = false;
        $print[$key] = "<strong>Contact <a href='people/$id'>#$id</a> identified by e-mail ({$_POST['email'][$key]}).</strong><br />";
        if ($_POST['firstname'][$key]) {
          $post['firstname'] = html($_POST['firstname'][$key]);
          $print[$key] .= "First name was updated.<br />";
        }
        if ($_POST['lastname'][$key]) {
          $post['lastname'] = html($_POST['lastname'][$key]);
          $print[$key] .= "Last name was updated.<br />";
        }
        if ($_POST['organization'][$key]) {
          $post['organization'] = html($_POST['organization'][$key]);
          $print[$key] .= "Organization was updated.<br />";
        }
        if ($_POST['street'][$key]) {
          $post['address'] = html($_POST['street'][$key]);
          $print[$key] .= "Address was updated.<br />";
        }
        if ($_POST['phone'][$key]) {
          $post['phone'] = html($_POST['phone'][$key]);
          $print[$key] .= "Phone was updated.<br />";
        }
        if ($_POST['comments'][$key]) {
          $_POST['comments'] = 'CONCAT(comments, ' . html("\n" . $_POST['comments'][$key]) . ')';
          $print[$key] .= "Comments were added.<br />";
        }
        if ($post) {
          $db->update("people",$post,"id = $id");
          logThis(6, $id);
        }

      } else {
        $post = array(
          'firstname' => html($_POST['firstname'][$key]),
          'lastname' => html($_POST['lastname'][$key]),
          'organization' => html($_POST['organization'][$key]),
          'address' => html($_POST['street'][$key]),
          'phone' => html($_POST['phone'][$key]),
          'email' => html($_POST['email'][$key]),
          'comments' => html($_POST['comments'][$key]),
        );
        $db->insert("people",$post);
        $id = $db->insert_id;
        logThis(1, $id);
        $print[$key] = "<strong>Contact <a href='people/$id'>#$id</a> added! E-mail: {$_POST['email'][$key]}.</strong><br />";
      }
      if ($event) {
        $post = array(
          'people' => $id,
          'event' => $event,
          'relationship' => $relationship,
        );
        $db->insert("people_events",$post);
        logThis(16, $id);
      }
      if ($_POST['mailing']) {
        foreach ($_POST['mailing'] as $key => $list) {
          $list = (int)$list;
          // We need to make sure this person is not already subscribed
          $check = $db->query("SELECT * FROM people_mailinglists WHERE people = $id AND mailinglist = $list");
          if (!$check->num_rows) {
            $post = array(
              'people' => $id,
              'mailinglist' => $list,
            );
            $db->insert("people_mailinglists",$post);
            logThis(10, $id);
          }
        }
      }
    }
  }
}

?>
<!doctype html>
<html>
<head>
<title><?php echo SITENAME ?></title>
<?php echo $head ?>
<script type="text/javascript">
$(function(){
  $("select[name='link']").change(function(){
    if ($(this).val() == "no") {
      $(".newevent").hide('fast');
      $(".existingevent").hide('fast');
      $(".relationship").hide('fast');
    } else if ($(this).val() == "existing") {
      $(".newevent").hide('fast');
      $(".existingevent").show('fast');
      $(".relationship").show('fast');
    } else if ($(this).val() == "new") {
      $(".newevent").show('fast');
      $(".existingevent").hide('fast');
      $(".relationship").show('fast');
    }
  });
  $("select[name='link']").change();
});
</script>
</head>
<body>

<?php require_once 'include.header.php'; ?>

  <h1>Quick Add</h1>

  <?php if ($_POST) { ?>

  <div class="alert alert-info">
    <?php foreach ($print as $key => $value) { ?>
      <?php echo $key+1 ?>. <?php echo $value ?>
    <?php } ?>
  </div>

  <?php } else { ?>

  <p>
    Note: if you provide new contact information for an existing contact, then the information
    will be updated. If you just want to link an existing contact to an event you only need to 
    provide her/his e-mail address.
  </p>

  <form method="post" class="form form-horizontal">

  <table>
    <tr>
      <th>First Name</th>
      <th>Last Name</th>
      <th>Organisation</th>
      <th>Street</th>
      <th>E-mail</th>
      <th>Phone</th>
      <th>Notes</th>
    </tr>
    <?php for ($i = 1; $i <= 20; $i++) { ?>
      <tr>
        <td><input type="text" name="firstname[]" class="form-control" placeholder="<?php echo $i ?>" /></td>
        <td><input type="text" name="lastname[]" class="form-control" /></td>
        <td><input type="text" name="organization[]" class="form-control" /></td>
        <td><input type="text" name="address[]" class="form-control" /></td>
        <td><input type="email" name="email[]" class="form-control" /></td>
        <td><input type="text" name="phone[]" placeholder="000 000 0000" class="form-control" /></td>
        <td><input type="text" name="notes[]" class="form-control" /></td>
      </tr>
    <?php } ?>

  </table>

  <h2>Relate to event</h2>

  <div class="form-group">
    <label class="col-sm-2 control-label">Link to event?</label>
    <div class="col-sm-10">
      <select name="link" class="form-control">
        <option value="no">No</option>
        <option value="new">Yes, create a new event</option>
        <option value="existing">Yes, select existing event</option>
      </select>
    </div>
  </div>
  
  <div class="form-group existingevent">
    <label class="col-sm-2 control-label">Event</label>
    <div class="col-sm-10">
      <select name="event" class="form-control">
        <option value=""></option>
        <?php while ($row = $events->fetch()) { ?>
          <option value="<?php echo $row['id'] ?>"<?php if ($row['id'] == $info->event) { echo ' selected'; } ?>><?php echo $row['name'] ?></option>
        <?php } $events->reset(); ?>
      </select>
    </div>
  </div>

  <div class="form-group newevent">
    <label class="col-sm-2 control-label">Event name</label>
    <div class="col-sm-10">
      <input class="form-control" type="text" name="eventname" />
    </div>
  </div>

  <div class="form-group newevent">
    <label class="col-sm-2 control-label">Parent event</label>
    <div class="col-sm-10">
      <select name="parent_event" class="form-control">
        <option value=""></option>
      <?php while ($row = $events->fetch()) { ?>
        <option value="<?php echo $row['id'] ?>"><?php echo $row['name'] ?></option>
      <?php } ?>
      </select>
    </div>
  </div>

  <div class="form-group newevent">
    <label class="col-sm-2 control-label">Place</label>
    <div class="col-sm-10">
      <input class="form-control" type="text" name="place" value="<?php echo $info->place ?>" />
    </div>
  </div>

  <div class="form-group newevent">
    <label class="col-sm-2 control-label">Date</label>
    <div class="col-sm-10">
      <input class="form-control" type="date" name="date" value="<?php echo $info->date ?>" />
    </div>
  </div>

  <div class="form-group newevent">
    <label class="col-sm-2 control-label">Type</label>
    <div class="col-sm-10">
      <select name="type" class="form-control">
        <option value=""></option>
        <?php while ($row = $types->fetch()) { ?>
          <option value="<?php echo $row['id'] ?>"<?php if ($row['id'] == $info->type) { echo ' selected'; } ?>><?php echo $row['name'] ?></option>
        <?php } ?>
      </select>
    </div>
  </div>

  <div class="form-group relationship">
    <label class="col-sm-2 control-label">Relationship</label>
    <div class="col-sm-10">
      <select name="relationship" class="form-control">
        <?php while ($row = $relationships->fetch()) { ?>
          <option value="<?php echo $row['id'] ?>"<?php if ($row['id'] == $info->relationship) { echo ' selected'; } ?>><?php echo $row['name'] ?></option>
        <?php } ?>
      </select>
    </div>
  </div>

  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
    <?php while ($row = $mailinglists->fetch()) { ?>
      <div class="checkbox">
        <label>
          <input type="checkbox" name="mailing[]" value="<?php echo $row['id'] ?>" /> 
            <?php echo $row['name'] ?>
        </label>
      </div>
    <?php } ?>
    </div>
  </div>

  <div class="form-group">
      <button type="submit" class="btn btn-primary">Save</button>
  </div>

  </form>

  <?php } ?>

<?php require_once 'include.footer.php'; ?>
<script src="//cdn.jsdelivr.net/webshim/1.12.4/extras/modernizr-custom.js"></script>
<script src="//cdn.jsdelivr.net/webshim/1.12.4/polyfiller.js"></script>
<script>
  webshims.setOptions('waitReady', false);
  webshim.setOptions("forms-ext", {
    "widgets": {
      "startView": 2,
      "openOnFocus": true
    }
  });
  webshims.polyfill('forms forms-ext');
</script>

</body>
</html>
