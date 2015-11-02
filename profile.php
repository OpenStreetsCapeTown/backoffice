<?php
require_once 'functions.php';
$id = (int)$_GET['id'];

if ($_GET['delete']) {
  $delete = (int)$_GET['id'];
  $db->query("DELETE FROM people WHERE id = $delete LIMIT 1");
  header("Location: " . URL . "people.list.php?deleted=true");
  exit();
}

if ($_GET['activate']) {
  $db->query("UPDATE people SET active = 1 WHERE id = $id LIMIT 1");
  logThis(13, $id, OPENID_USERID);
  $print = "User has been activated";
}

$info = $db->query("SELECT people.*, suburbs.name AS suburb, ages.age, referral_sources.name AS referral,
  organization_types.name AS organization_type
FROM people 
  LEFT JOIN suburbs ON people.suburb = suburbs.id
  LEFT JOIN ages ON people.age = ages.id
  LEFT JOIN referral_sources ON people.referral = referral_sources.id
  LEFT JOIN organization_types ON people.organization_type = organization_types.id
WHERE people.id = $id");

if (!$info->num_rows) {
  header("HTTP/1.0 404 Not Found");
  header("Location: " . URL . "404");
  exit();
}

$surveys = $db->query("SELECT surveys.id, surveys.date, survey_list.name FROM surveys
  JOIN survey_list ON surveys.survey = survey_list.id
WHERE surveys.people = $id");

$preferences = $db->query("SELECT po.name AS answer, pt.name AS `option`
  FROM people_preferences pp
  JOIN preference_options po ON pp.preference = po.id
  JOIN preference_types pt ON po.type = pt.id
  WHERE pp.people = $id");

if ($_GET['message'] == "saved") {
  $print = "Information was saved";
}

$relationships = $db->query("SELECT types.name 
FROM people_types 
JOIN types ON people_types.type = types.id
WHERE people_types.people = $id ORDER BY types.name");

$tags = $db->query("SELECT DISTINCT tags_options.name 
FROM people_tags 
JOIN tags_options ON people_tags.tag = tags_options.id
WHERE people_tags.people = $id ORDER BY tags_options.name");

$mailinglists = $db->query("SELECT mailinglist_options.name 
FROM people_mailinglists 
JOIN mailinglist_options ON people_mailinglists.mailinglist = mailinglist_options.id
WHERE people_mailinglists.people = $id ORDER BY mailinglist_options.name");

$events = $db->query("SELECT events.name, events.date, events.id
FROM event_attendance a 
JOIN events ON a.event = events.id
WHERE a.people = $id ORDER BY events.date");

$skills = $db->query("SELECT skills.name 
FROM people_skills 
JOIN skills ON people_skills.skill = skills.id
WHERE people_skills.people = $id ORDER BY skills.name");

$log = $db->query("SELECT log.*, openid_users.fullname, log_actions.action
FROM log 
  JOIN log_actions ON log.log_action = log_actions.id
  LEFT JOIN openid_users ON log.user = openid_users.id
WHERE log.people = $id ORDER BY log.date");

$event_links = $db->query("SELECT 
  p.*,
  events.name, 
  event_relationships.name AS relationship
FROM people_events p
  JOIN events ON p.event = events.id
  JOIN event_relationships ON p.relationship = event_relationships.id    
WHERE p.people = $id");
?>
<!doctype html>
<html>
<head>
<title><?php echo getName($id) ?> | #<?php echo $id ?> | <?php echo SITENAME ?></title>
<?php echo $head ?>
<style type="text/css">
.right{float:right;position:relative;top:-8px}
.hidethis{display:none}
a.btn-danger{margin-top:4px}
#details{clear:both}
<?php if (!$info->active) { ?>
#details{opacity:0.6}
<?php } ?>
</style>
<script type="text/javascript">
$(function(){
  $(".toggle").click(function(e){
    e.preventDefault();
    $(this).toggleClass("btn-primary");
    var show = $(this).data("show");
    $("#"+show).toggle('fast');
  });
});
</script>
</head>
<body>

<?php require_once 'include.header.php'; ?>

<a href="profile.php?id=<?php echo $id ?>&amp;delete=true" onclick="javascript:return confirm('Are you sure?')" class="btn btn-danger right">Delete person</a>

<h1><?php echo $info->lastname ? $info->lastname . ", " : ''?> <?php echo $info->firstname ?></h1>

<?php if (!$info->active) { ?>
  <div class="alert alert-info">
    <p>This person is not confirmed.</p>
    <p><a href="profile.php?id=<?php echo $id ?>&amp;activate=1" class="btn btn-primary">Activate person</a></p>
  </div>
<?php } ?>

<?php if ($print) { echo "<div class=\"alert alert-success\">$print</div>"; } ?>

<div id="details">

<div class="panel panel-default">
  <div class="panel-heading">
    <a href="people.php?id=<?php echo $id ?>" class="btn btn-default right">Edit</a>
    <h3 class="panel-title">General Information</h3>
  </div>
  <div class="panel-body">
    <dl class="dl-horizontal">

      <dt>ID</dt>
      <dd><?php echo $id ?></dd>

      <?php if ($info->email) { ?>
        <dt>E-mail</dt>
        <dd>
          <a href="mailto:<?php echo $info->email ?>"><?php echo $info->email ?></a>
          <?php if ($info->email_additional) { ?>
            | <a href="mailto:<?php echo $info->email_additional ?>"><?php echo $info->email_additional ?></a>
          <?php } ?>
        </dd>
      <?php } ?>


      <?php if ($info->suburb) { ?>
        <dt>Suburb</dt>
        <dd><?php echo $info->suburb ?></dd>
      <?php } ?>

      <?php if ($info->address) { ?>
        <dt>Address</dt>
        <dd><?php echo $info->address ?></dd>
      <?php } ?>

      <?php if ($info->city) { ?>
        <dt>City</dt>
        <dd><?php echo $info->city ?>
          <?php if ($info->country) { ?>, <?php echo $info->country; } ?>
        </dd>
      <?php } ?>

      <?php if ($info->twitter) { ?>
        <dt><img src="img/twitter.png" alt="Twitter" /></dt>
        <dd><a href="https://twitter.com/<?php echo $info->twitter ?>"><?php echo $info->twitter ?></a></dd>
      <?php } ?>

      <?php if ($info->website) { ?>
        <dt>Website</dt>
        <dd><a href="<?php echo $info->website ?>"><?php echo $info->website ?></a></dd>
      <?php } ?>

      <?php if ($info->organization) { ?>
        <dt>Organisation</dt>
        <dd><?php echo $info->organization ?></dd>

        <?php if ($info->organization_type) { ?>
          <dt>Type</dt>
          <dd><?php echo $info->organization_type ?></dd>
        <?php } ?>
      <?php } ?>

      <?php if ($info->affiliation) { ?>
        <dt>Affiliation</dt>
        <dd><?php echo $info->affiliation ?></dd>
      <?php } ?>

      <?php if ($info->comments) { ?>
        <dt>Comments</dt>
        <dd><?php echo $info->comments ?></dd>
      <?php } ?>

      <?php if ($info->age) { ?>
        <dt>Age</dt>
        <dd><?php echo $info->age ?></dd>
      <?php } ?>

      <?php if ($info->referral) { ?>
        <dt>Referral</dt>
        <dd><?php echo $info->referral ?></dd>
      <?php } ?>

      <?php if ($info->phone) { ?>
        <dt>Phone</dt>
        <dd><?php echo $info->phone ?></dd>
      <?php } ?>

      <?php if ($info->cell) { ?>
        <dt>Mobile</dt>
        <dd><?php echo $info->cell ?></dd>
      <?php } ?>

      <dt>Mailings</dt>
      <dd><?php echo $info->active_mailings ? "Active" : "Inactive"; ?></dd>

    </dl>
  </div>
</div>

<div class="panel panel-default">
  <div class="panel-heading">
    <a href="people.relationship.php?id=<?php echo $id ?>" class="btn btn-default right">Edit</a>
    <h3 class="panel-title">Relationship(s)</h3>
  </div>
  <?php if ($relationships->num_rows) { ?>
    <div class="panel-body">
      <ul>
      <?php while ($row = $relationships->fetch()) { ?>
        <li><?php echo $row['name'] ?></li>
      <?php } ?>
      </ul>
    </div>
  <?php } ?>
</div>

<div class="panel panel-default">
  <div class="panel-heading">
    <a href="people.preferences.php?id=<?php echo $id ?>" class="btn btn-default right">Edit</a>
    <h3 class="panel-title">Preferences</h3>
  </div>
  <?php if ($preferences->num_rows) { ?>
    <div class="panel-body">
      <dl class="dl-horizontal">
      <?php while ($row = $preferences->fetch()) { ?>
        <?php if ($row['option'] != $previous) { ?>
          <dt><?php echo $row['option'] ?></dt>
        <?php } $previous = $row['option']; ?>
        <dd><?php echo $row['answer'] ?></dd>
      <?php } ?>
      </dl>
    </div>
  <?php } ?>
</div>

<div class="panel panel-default">
  <div class="panel-heading">
    <a href="people.skills.php?id=<?php echo $id ?>" class="btn btn-default right">Edit</a>
    <h3 class="panel-title">Skill(s)</h3>
  </div>
  <?php if ($skills->num_rows) { ?>
    <div class="panel-body">
      <ul>
      <?php while ($row = $skills->fetch()) { ?>
        <li><?php echo $row['name'] ?></li>
      <?php } ?>
      </ul>
    </div>
  <?php } ?>
</div>

<div class="panel panel-default">
  <div class="panel-heading">
    <a href="people.tags.php?id=<?php echo $id ?>" class="btn btn-default right">Edit</a>
    <h3 class="panel-title">Tag(s)</h3>
  </div>
  <?php if ($tags->num_rows) { ?>
    <div class="panel-body">
      <ul>
      <?php while ($row = $tags->fetch()) { ?>
        <li><?php echo $row['name'] ?></li>
      <?php } ?>
      </ul>
    </div>
  <?php } ?>
</div>

<div class="panel panel-default">
  <div class="panel-heading">
    <a href="people.mailinglist.php?id=<?php echo $id ?>" class="btn btn-default right">Edit</a>
    <h3 class="panel-title">Mailinglist(s)</h3>
  </div>
  <?php if ($mailinglists->num_rows) { ?>
    <div class="panel-body">
      <ul>
      <?php while ($row = $mailinglists->fetch()) { ?>
        <li><?php echo $row['name'] ?></li>
      <?php } ?>
      </ul>
    </div>
  <?php } ?>
</div>

<div class="panel panel-default">
  <div class="panel-heading">
    <a href="event.contactlink.php?return=profile&amp;contact=<?php echo $id ?>" class="btn btn-default right">Edit</a>
    <h3 class="panel-title">Event relationship(s)</h3>
  </div>
  <?php if ($event_links->num_rows) { ?>
    <div class="panel-body">
      <table class="table table-striped">
        <tr>
          <th>Event</th>
          <th>Relationship</th>
          <th colspan="2">Notes</th>
        </tr>
      <?php while ($row = $event_links->fetch()) { ?>
        <tr>
          <td><a href="events/dashboard/<?php echo $row['event'] ?>"><?php echo $row['name'] ?></a></td>
          <td><?php echo $row['relationship'] ?></td>
          <td><?php echo $row['comments'] ?></td>
          <td>
            <a href="event.contactlink.php?id=<?php echo $row['id'] ?>&amp;event=<?php echo $row['event'] ?>&amp;contact=<?php echo $id ?>&amp;return=profile">
              <i class="fa fa-edit"></i>
            </a>
          </td>
        </tr>
      <?php } ?>
      </table>
    </div>
  <?php } ?>
</div>

<?php if ($events->num_rows) { ?>

  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">Event(s)</h3>
    </div>
    <?php if ($events->num_rows) { ?>
      <div class="panel-body">
        <table class="table table-striped">
          <tr>
            <th>Event</th>
            <th>Date</th>
            <th>View full list</th>
          </tr>
        <?php while ($row = $events->fetch()) { ?>
          <tr>
            <td><?php echo $row['name'] ?></td>
            <td><?php echo format_date("M d, Y", $row['date']) ?></td>
            <td><a href="report/event/<?php echo $row['id'] ?>">View list</a></td>
          </tr>
        <?php } ?>
        </table>
      </div>
    <?php } ?>
  </div>

<?php } ?>

<?php if ($surveys->num_rows) { ?>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">Surveys</h3>
    </div>
    <div class="panel-body">
    <?php while ($row = $surveys->fetch()) { ?>
      <li><a href="survey/results/<?php echo $row['id'] ?>"><?php echo $row['name'] ?></a>
      (<?php echo format_date("M d, Y", $row['date']) ?>)
      </li>
    <?php } ?>
    </ul>
    </div>
  </div>
<?php } ?>

<?php if ($log->num_rows) { ?>
  <div class="panel panel-default">
    <div class="panel-heading">
      <a href="#log" data-show="log" class="btn btn-default right toggle">Show</a>
      <h3 class="panel-title">Log</h3>
    </div>
    <div class="panel-body hidethis" id="log">
      <table class="table table-striped">
        <tr>
          <th>Date</th>
          <th>Action</th>
          <th>User</th>
        </tr>
    <?php while ($row = $log->fetch()) { ?>
      <tr>
        <td><?php echo format_date("M d, Y", $row['date']) ?></td>
        <td><?php echo $row['action'] ?></td>
        <td><?php echo $row['fullname'] ?></td>
      </tr>
    <?php } ?>
    </table>
    </div>
  </div>
<?php } ?>

</div>

<?php require_once 'include.footer.php'; ?>

</body>
</html>
