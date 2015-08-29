<?php
require_once 'functions.php';

//27 = OS newsletter
//28 = talkinstreet
//29 = friends

$list = $db->query("SELECT * FROM people_preferences WHERE preference IN (27,28,29)");
while ($row = $list->fetch()) {
  $people = $row['people'];
  $option = $row['preference'];
  if ($option == 27) {
    $mailinglist = 1;
  } elseif ($option == 28) {
    $mailinglist = 2;
  } elseif ($option == 29) {
    $mailinglist = 3;
  } else {
    die("WTF is that about man?!");
  }
  $post = array(
    'people' => $people,
    'mailinglist' => $mailinglist,
  );
  $db->insert("people_mailinglists",$post);
}
