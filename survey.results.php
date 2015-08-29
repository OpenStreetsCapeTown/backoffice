<?php
require_once 'functions.php';

$id = (int)$_GET['id'];
$info = $db->query("SELECT * FROM surveys WHERE id = $id");
$answers = $db->query("SELECT * FROM survey_answers WHERE survey = $id ORDER BY id");

function printQuestion($question, $label, $table = false, $name = 'name') {
  global $db, $id;
  $info = $db->query("SELECT * FROM survey_answers WHERE survey = $id AND label = '$label'");
  if (!$info->num_rows) {
    $info = $db->query("SELECT * FROM survey_answers WHERE survey = $id AND checklist = '$label'");
  }
  if (!$info->num_rows) { return false; }
  if ($info->num_rows > 1) { 
    echo '<dt>'.$question.'</dt>';
    while ($row = $info->fetch()) {
      $answer = $row['answer'];
      $list = $db->query("SELECT $name AS name FROM $table WHERE id = $answer");
      echo '<dd>'.$list->name.'</dd>';
    }
  } else { 
    $answer = $info->answer;
    if ($table == 'bool') {
      $answer = $answer == 1 ? "Yes" : "No"; 
    } elseif ($table) {
      $info = $db->query("SELECT $name AS name FROM $table WHERE id = $answer");
      $answer = $info->name;
    }
    if ($answer) { echo '<dt>'.$question.'</dt><dd>'.$answer.'</dd>'; }
  }
  return true;
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

<h1>Survey Results</h1>

<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title">General Information</h3>
  </div>
  <div class="panel-body">
    <dl class="dl-horizontal">
      <?php printQuestion('First Name', 'firstname') ?>
      <?php printQuestion('Last Name', 'lastname') ?>
      <?php printQuestion('E-mail', 'email') ?>
      <?php printQuestion('Phone', 'phone') ?>
      <?php printQuestion('Age', 'age', 'ages', 'age') ?>
      <?php printQuestion('Manifesto endorsed?', 'manifesto', 'bool') ?>
    </dl>
  </div>
</div>


<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title">Event Feedback</h3>
  </div>
  <div class="panel-body">
    <dl>
      <?php printQuestion('What did you enjoy?', 'enjoy') ?>
      <?php printQuestion('What did you dislike?', 'dislike') ?>
      <?php printQuestion('What would you like to see in the future?', 'future') ?>
    </dl>
  </div>
</div>


<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title">Mobility</h3>
  </div>
  <div class="panel-body">
    <dl>
      <?php printQuestion('Where do you live?', 'live', 'suburbs') ?>
      <?php printQuestion('Other', 'live-specify') ?>
      <?php printQuestion('Where do you work?', 'work', 'suburbs') ?>
      <?php printQuestion('Other', 'work-specify') ?>
      <?php printQuestion('How do you travel to work?', 'transport-work', 'transport_options') ?>
      <?php printQuestion('What other modes of transport do you use when you are not traveling to work?', 'transport-notwork', 'transport_options') ?>
    </dl>
  </div>
</div>

<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title">Preferences</h3>
  </div>
  <div class="panel-body">
    <dl>
      <?php printQuestion('How would you like to communicate with Open Streets Cape Town?', 'communicate', 'os_communication_options') ?>
      <?php printQuestion('How regularly are you happy to be contacted by Open Streets', 'subscriptions', 'preference_options') ?>
    </dl>
  </div>
</div>

<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title">Volunteering</h3>
  </div>
  <div class="panel-body">
    <dl>
      <?php printQuestion('How have you been involved with Open Streets Cape Town so far?', 'interaction', 'interaction_options') ?>
      <?php printQuestion('How would you like to be involved?', 'involve', 'involvement_options') ?>
      <?php printQuestion('Please specify', 'involve-specify') ?>
    </dl>
  </div>
</div>



<?php require_once 'include.footer.php'; ?>

</body>
</html>
