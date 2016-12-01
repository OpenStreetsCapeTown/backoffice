<?php
require_once 'functions.php';
$id = (int)$_GET['id'];
$info = $db->query("SELECT * FROM people WHERE id = $id");

if ($_POST) {
  if (!$id) {
    $mail = html($_POST['email']);
    $check = $db->query("SELECT * FROM people WHERE email = $mail");
    if ($check->id && $_POST['email']) {
      die("A contact with this e-mail already exists in the database. It is recommended that you update this person instead of adding a new one. 
      You can review the profile here: 
      <a href='" . URL . "people/" . $check->id."'>
      " . URL . "people/" . $check->id . "</a>");

    }
  }
  $post = array(
    'firstname' => html($_POST['firstname']),
    'lastname' => html($_POST['lastname']),
    'email' => html($_POST['email']),
    'email_additional' => html($_POST['email_additional']),
    'address' => html($_POST['address']),
    'city' => html($_POST['city']),
    'country' => html($_POST['country']),
    'twitter' => html($_POST['twitter']),
    'website' => html($_POST['website']),
    'comments' => html($_POST['comments']),
    'organization' => html($_POST['organization']),
    'phone' => html($_POST['phone']),
    'cell' => html($_POST['cell']),
    'affiliation' => html($_POST['affiliation']),
    'age' => $_POST['age'] ? (int)$_POST['age'] : "NULL",
    //'referral' => $_POST['referral'] ? (int)$_POST['referral'] : "NULL",
    'active_mailings' => $_POST['email'] ? (int)$_POST['active_mailings'] : 0,
    'suburb' => $_POST['suburb'] ? (int)$_POST['suburb'] : "NULL",
    'organization_type' => $_POST['organization_type'] ? (int)$_POST['organization_type'] : "NULL",
  );
  if ($id) {
    $db->update("people",$post,"id = $id");
    logThis(6, $id); 
  } else {
    $post['active'] = 1;
    $db->insert("people",$post);
    $id = $db->insert_id;
    logThis(1, $id); 
  }
  header("Location: " . URL . "people/$id/saved");
  exit();
}

$ages = $db->query("SELECT * FROM ages ORDER BY id");
$referrals = $db->query("SELECT * FROM referral_sources WHERE active = 1 ORDER BY name");

$suburbs = $db->query("SELECT * FROM suburbs WHERE active = 1 ORDER BY area, name");
$areas = $db->query("SELECT suburbs.*, areas.name AS area
FROM suburbs JOIN areas ON suburbs.area = areas.id
WHERE suburbs.active = 1 ORDER BY areas.name, suburbs.name");

$organization_types = $db->query("SELECT t.*, m.name AS main 
FROM organization_types t 
  LEFT JOIN organization_main_types m ON t.main_organization = m.id
WHERE t.active = 1 ORDER BY m.name, t.name");
?>
<!doctype html>
<html>
<head>
<title><?php echo SITENAME ?></title>
<?php echo $head ?>
<script type="text/javascript">
$(function(){
  $("#organization").keyup(function(){
    if ($("#organization").val()) {
      $("#organization_type").show('fast');
    } else {
      $("#organization_type").hide('fast');
    }
  });
  $("#organization").keyup();
});
</script>
</head>
<body>

<?php require_once 'include.header.php'; ?>

  <h1>People</h1>

  <form method="post" class="form form-horizontal" role="form">

    <div class="form-group">
      <label class="col-sm-2 control-label">First Name</label>
      <div class="col-sm-10">
        <input class="form-control" type="text" name="firstname" value="<?php echo $info->firstname ?>" />
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">Surname</label>
      <div class="col-sm-10">
        <input class="form-control" type="text" name="lastname" value="<?php echo $info->lastname ?>" />
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">Organisation</label>
      <div class="col-sm-10">
        <input class="form-control" type="text" name="organization" value="<?php echo $info->organization ?>" id="organization" />
      </div>
    </div>

    <div class="form-group" id="organization_type">
      <label class="col-sm-2 control-label">Type</label>
      <div class="col-sm-10">
        <select name="organization_type" class="form-control">
          <option value=""></option>
          <?php 
          $prev_main = "Nothing";
          while ($row = $organization_types->fetch()) { ?>
            <?php if ($row['main'] != $prev_main) { ?>
              <?php if ($prev_main) { ?>
                </optgroup>
              <?php } ?>
              <optgroup label="<?php echo $row['main'] ? $row['main'] : 'General'; ?>">
            <?php } $prev_main = $row['main']; ?>
            <option value="<?php echo $row['id'] ?>"<?php if ($row['id'] == $info->organization_type) { echo ' selected'; } ?>><?php echo $row['name'] ?></option>
          <?php } ?>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">E-mail 1</label>
      <div class="col-sm-10">
        <input class="form-control" type="email" name="email" value="<?php echo $info->email ?>" />
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">E-mail 2</label>
      <div class="col-sm-10">
        <input class="form-control" type="email" name="email_additional" value="<?php echo $info->email_additional ?>" />
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">Suburb</label>
      <div class="col-sm-10">
        <select class="form-control" name="suburb">
          <option value=""></option>
        <?php while ($row = $areas->fetch()) { ?>
          <?php if ($area != $row['area']) { ?>
          <?php if ($area) { ?>
          </optgroup>
          <?php } ?>
          <optgroup label="<?php echo $row['area'] ?>">
        <?php } ?>
            <option value="<?php echo $row['id'] ?>" <?php if ($row['id'] == $info->suburb) { echo ' selected'; } ?>><?php echo $row['name'] ?></option>
          <?php $area = $row['area']; } ?>
          </optgroup>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">City</label>
      <div class="col-sm-10">
        <input class="form-control" type="text" name="city" value="<?php echo $id ? $info->city : 'Cape Town' ?>" />
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">Street address</label>
      <div class="col-sm-10">
        <input class="form-control" type="text" name="address" value="<?php echo $info->address ?>" />
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">Country</label>
      <div class="col-sm-10">
        <input class="form-control" type="text" name="country" value="<?php echo $id ? $info->country : 'South Africa' ?>" />
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">Phone</label>
      <div class="col-sm-10">
        <input class="form-control" type="text" name="phone" value="<?php echo $info->phone ?>" placeholder="000 000 0000" />
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">Mobile</label>
      <div class="col-sm-10">
        <input class="form-control" type="text" name="cell" value="<?php echo $info->cell ?>" placeholder="000 000 0000" />
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">Position</label>
      <div class="col-sm-10">
        <input class="form-control" type="text" name="affiliation" value="<?php echo $info->affiliation ?>" />
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">Twitter</label>
      <div class="col-sm-10">
        <input class="form-control" type="text" name="twitter" value="<?php echo $info->twitter ?>" />
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">Website</label>
      <div class="col-sm-10">
        <input class="form-control" type="url" name="website" value="<?php echo $info->website ?>" />
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">Mailings active?</label>
      <div class="col-sm-10">
        <input type="checkbox" name="active_mailings" value="1" <?php if ($info->active_mailings || !$id) { echo 'checked'; } ?> />
        Yes (deactivate if this person should NEVER receive mailings)
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-2 control-label">Age</label>
      <div class="col-sm-10">
        <select name="age" class="form-control">
          <option value=""></option>
          <?php while ($row = $ages->fetch()) { ?>
            <option value="<?php echo $row['id'] ?>"<?php if ($row['id'] == $info->age) { echo ' selected'; } ?>><?php echo $row['age'] ?></option>
          <?php } ?>
        </select>
      </div>
    </div>

<!--      <div class="form-group">
      <label class="col-sm-2 control-label">Referral</label>
      <div class="col-sm-10">
        <select name="referral" class="form-control">
          <option value=""></option>
          <?php while ($row = $referrals->fetch()) { ?>
            <option value="<?php echo $row['id'] ?>"<?php if ($row['id'] == $info->referral) { echo ' selected'; } ?>><?php echo $row['name'] ?></option>
          <?php } ?>
        </select>
      </div>
    </div> -->

    <div class="form-group">
      <label class="col-sm-2 control-label">Comments</label>
      <div class="col-sm-10">
        <textarea class="form-control" name="comments"><?php echo br2nl($info->comments) ?></textarea>
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
