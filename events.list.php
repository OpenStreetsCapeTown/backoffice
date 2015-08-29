<?php
require_once 'functions.php';

$list = $db->query("SELECT * FROM events WHERE active = 1 ORDER BY date");
?>
<!doctype html>
<html>
<head>
<title>List of Events | <?php echo SITENAME ?></title>
<?php echo $head ?>
</head>
<body>

<?php require_once 'include.header.php'; ?>

  <a href="info/event" class="btn btn-info pull-right">Add Event</a>

  <h1>List of Events</h1>

  <table class="table table-striped">
    <tr>
      <th>Date</th>
      <th>Event</th>
      <th>Actions</th>
    </tr>
  <?php while ($row = $list->fetch()) { ?>
    <tr>
      <td><?php echo format_date("M d, Y", $row['date']) ?></td>
      <td><a href="events/dashboard/<?php echo $row['id'] ?>"><?php echo $row['name'] ?></a></td>
      <td><a href="edit/event/<?php echo $row['id'] ?>">Edit event</a></td>
    </tr>
  <?php } ?>
  </table>

<?php require_once 'include.footer.php'; ?>

</body>
</html>
