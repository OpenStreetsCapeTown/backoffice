<?php
require_once 'functions.php';

$row[] = array();

foreach ($row as $key) {
  $email = $key[3];
  $first = $key[1];
  $last = $key[2];
  $organization = $key[0];
  $phone = $key[4];
  $address = $key[5];
  $notes = $key[6];
  if ($email) {
    $info = $db->query("SELECT * FROM people WHERE email = '$email'");
    $id = $info->id;
  } else {
    $info = false;
    $id = false;
  }
  $post = array(
    'firstname' => mysql_clean($info->firstname ? $info->firstname : $first),
    'lastname' => mysql_clean($info->lastname ? $info->lastname : $last),
    'email' => mysql_clean($email),
    'organization' => mysql_clean($info->organization ? $info->organization : $organization),
    'address' => mysql_clean($address),
    'phone' => mysql_clean($phone),
    'comments' => html($notes ? $notes : $info->notes),
  );
  if ($id) {
    $db->update("people",$post,"id = $id");
  } else {
    $db->insert("people",$post);
    $id = $db->insert_id;
  }
  $check = $db->query("SELECT * FROM log WHERE people = $id AND log_action  IN(1,9)");
  if (!$check->num_rows) {
    $post = array(
      'log_action' => 1,
      'date' => 'CURDATE()',
      'people' => $id,
      'user' => 1,
    );
    $db->insert("log",$post);
  }
    $post = array(
      'log_action' => 16,
      'details' => html("Linked with event: Open Streets Langa. Relationship: other."),
      'date' => 'CURDATE()',
      'people' => $id,
      'user' => 1,
    );
    $db->insert("log",$post);
    $post = array(
      'event' => 4,
      'people' => $id,
      'relationship' => 5,
    );
    $db->insert("people_events",$post);

  /*
  $post = array(
    'tag' => 3, // OS Launch event interest
    'people' => $id,
  );
  $db->insert("people_tags",$post);
  if ($present == "x") {
    $post = array(
      'event' => 1,
      'people' => $id,
    );
    $db->insert("event_attendance",$post);
  }
  */
  $check = $db->query("SELECT * FROM people_mailinglists WHERE people = $id AND mailinglist = 1");
  if (!$check->num_rows) {
    $post = array(
      'people' => $id,
      'mailinglist' => 1,
    );
    $db->insert("people_mailinglists",$post);
    logThis(10, $id);
  }
}

echo "All good buddy";

?>
