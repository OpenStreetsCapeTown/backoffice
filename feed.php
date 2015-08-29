<?php
$skip_login = true;
require_once 'functions.php';
$categories = $db->query("SELECT * FROM whiteboard_options WHERE active = 1 ORDER BY name");
$category = (int)$_GET['category'];
$id = (int)$_GET['id'];

  $list = $db->query("SELECT DISTINCT whiteboard.category, whiteboard.subject, o.name AS section,
    (SELECT subject FROM whiteboard w WHERE w.id = whiteboard.parent_message) AS alt_subject,
    whiteboard.category, parent_message
  FROM whiteboard
    JOIN whiteboard_options o ON whiteboard.category = o.id
  WHERE parent_message IS NOT NULL
  ORDER BY date DESC LIMIT 3");
?>

<table class="table table-striped">
  <tr>
    <th>Topic</th>
    <th>Section</th>
  </tr>
<?php while ($row = $list->fetch()) { ?>
  <tr>
    <td><a href="<?php echo URL ?>whiteboard/<?php echo $row['category'] ?>/<?php echo flatten($row['section']) ?>/<?php echo $row['parent_message'] ?>"><?php echo $row['subject'] ? $row['subject'] : $row['alt_subject'] ?></a></td>
    <td><?php echo $row['section'] ?></td>
  </tr>
<?php } ?>
</table>
