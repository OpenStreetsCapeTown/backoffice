<?php
require_once 'functions.php';

$list = $db->query("SELECT * FROM surveys");

function ga($survey, $question, $checklist = false) {
  global $db;
  $field = $checklist ? "checklist" : "label";
  $info = $db->query("SELECT * FROM survey_answers WHERE survey = $survey AND $field = '$question'");
  return $info->answer;
}


while ($row = $list->fetch()) {
  $id = $row['id'];
  $mail = ga($id, 'email');
  if (!$mail) {
    if ($row['people']) {
      $db->query("DELETE FROM people WHERE id = " . $row['people']);
    }
  } else {
    if ($row['people']) {
      $post = false;
      $suburb = ga($id, 'live', true);
      if ($suburb) {
        $post['suburb'] = $suburb;
        $post['city'] = mysql_clean("Cape Town");
      }
      $age = ga($id, 'age');
      if ($age) {
        $post['age'] = $age;
      }
      if ($post) {
        $db->update("people",$post,"id = " . $row['people']);
      }
    } else {
      $q = ga($id, 'firstname');
      if ($q) { 
        $post = array(
          'firstname' => mysql_clean(ga($id, 'firstname')),
          'lastname' => mysql_clean(ga($id, 'lastname')),
          'email' => mysql_clean(ga($id, 'email')),
        );
        $db->insert("people",$post);
        $people = $db->insert_id;
        logThis(1,$people);
        $db->query("UPDATE surveys SET people = $people WHERE id = $id LIMIT 1");
      }
    }
  }
}

?>
