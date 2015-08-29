<?php
require_once 'functions.php';

$list = $db->query("SELECT * FROM surveys");

function printQuestion($id, $label, $table = false, $name = 'name') {
  global $db;
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
<style type="text/css">
table{
  table-layout:fixed;
}
</style>
</head>
<body>

<?php require_once 'include.header.php'; ?>

<h1>Survey Results</h1>

<table class="table table-striped">
  <tr>
    <th width="100">First Name</th>
    <th width="100">Last Name</th>
    <th width="200">E-mail</th>
    <th width="100">Phone</th>
    <th width="100">Age</th>
    <th width="100">Manifesto</th>
    <th width="300">What did you enjoy?</th>
    <th width="300">What did you dislike?</th>
    <th width="300">What would you like to see in the future?</th>
    <th width="100">Where do you live?</th>
    <th width="100">Other</th>
    <th width="100">Where do you work?</th>
    <th width="100">Other</th>
    <th width="150">How do you travel to work?</th>
    <th width="150">Other transportation used for non work</th>
    <th width="100">How would you like to communicate with Open Streets?</th>
    <th width="300">How regularly do you want to be contacted?</th>
    <th width="300">How have you been involved</th>
    <th width="300">How would you like to be involved?</th>
    <th width="300">Please specify</th>
  </tr>

<?php while ($row = $list->fetch()) { $id = $row['id']; ?>
<tr>
      <td><?php printQuestion($id, 'firstname') ?></td>
      <td><?php printQuestion($id, 'lastname') ?></td>
      <td><?php printQuestion($id, 'email') ?></td>
      <td><?php printQuestion($id, 'phone') ?></td>
      <td><?php printQuestion($id, 'age', 'ages', 'age') ?></td>
      <td><?php printQuestion($id, 'manifesto', 'bool') ?></td>
      <td><?php printQuestion($id, 'enjoy') ?></td>
      <td><?php printQuestion($id, 'dislike') ?></td>
      <td><?php printQuestion($id, 'future') ?></td>
      <td><?php printQuestion($id, 'live', 'suburbs') ?></td>
      <td><?php printQuestion($id, 'live-specify') ?></td>
      <td><?php printQuestion($id, 'work', 'suburbs') ?></td>
      <td><?php printQuestion($id, 'work-specify') ?></td>
      <td><?php printQuestion($id, 'transport-work', 'transport_options') ?></td>
      <td><?php printQuestion($id, 'transport-notwork', 'transport_options') ?></td>
      <td><?php printQuestion($id, 'communicate', 'os_communication_options') ?></td>
      <td><?php printQuestion($id, 'subscriptions', 'preference_options') ?></td>
      <td><?php printQuestion($id, 'interaction', 'interaction_options') ?></td>
      <td><?php printQuestion($id, 'involve', 'involvement_options') ?></td>
      <td><?php printQuestion($id, 'involve-specify') ?></td>
</tr>
<?php } ?>
</table>

<?php require_once 'include.footer.php'; ?>

</body>
</html>
