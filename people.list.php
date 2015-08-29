<?php
require_once 'functions.php';

if ($_POST['email']) {
  $email = mysql_clean($_POST['email']);
  $sql .= "email = $email AND ";
}

if ($_POST['name']) {
  $name = mysql_clean($_POST['name'], "wildcard");
  $sql .= "CONCAT_WS(' ',firstname,lastname) LIKE '%$name%' AND ";
}

if ($_POST) {
  $_GET['search'] = false;
}

$sql = $sql ? "WHERE " . substr($sql, 0, -4) : '';

if (!$_GET['search']) {
  $list = $db->query("SELECT * FROM people $sql ORDER BY lastname, firstname");
}

if ($_GET['deleted']) {
  $print = "Person has been deleted from the database";
}

$event = (int)$_GET['event'];
if ($event) {
  $info = $db->query("SELECT * FROM events WHERE id = $event");
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

<h1>People</h1>

<?php if ($event) { ?>
  <div class="well">Adding people to event: <strong><?php echo $info->name ?></strong></div>
<?php } ?>

<?php if ($print) { echo "<div class=\"alert alert-success\">$print</div>"; } ?>

<form method="post" class="form form-horizontal">

  <div class="form-group">
    <label class="col-sm-2 control-label">Search by E-mail</label>
    <div class="col-sm-10">
      <input class="form-control" type="email" name="email" value="<?php echo html($_POST['email'], false) ?>" />
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">Search by (part of a) name</label>
    <div class="col-sm-10">
      <input class="form-control" type="text" name="name" value="<?php echo html($_POST['name'], false) ?>" />
    </div>
  </div>

  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <button type="submit" class="btn btn-primary">Search</button>
    </div>
  </div>

</form>

<?php if (!$_GET['search']) { ?>

  <div class="alert alert-info"><?php echo $list->num_rows ?> people found.</div>

  <table class="table table-striped">
    <tr>
      <th>ID</th>
      <th>Surname</th>
      <th>Firstname</th>
      <th>E-mail</th>
      <th colspan="3">
        Actions
      </th>
    </tr>
  <?php while ($row = $list->fetch()) { ?>
    <tr>
      <td><a href="people/<?php echo $row['id'] ?>"><?php echo $row['id'] ?></a></td>
      <td><?php echo $row['lastname'] ?></td>
      <td><?php echo $row['firstname'] ?></td>
      <td><?php echo $row['email'] ?></td>
      <td>
        <a href="people/<?php echo $row['id'] ?>" title="View"><i class="fa fa-eye"></i></a>
      </td>
      <td>
        <a href="people.php?id=<?php echo $row['id'] ?>" title="Edit"><i class="fa fa-edit"></i></a>
      </td>
      <td>
        <a href="event.contactlink.php?event=<?php echo $event ?>&amp;contact=<?php echo $row['id'] ?>" title="Link to event"><i class="fa fa-link"></i></a>
      </td>
    </tr>
  <?php } ?>
  </table>

<?php } ?>

<?php require_once 'include.footer.php'; ?>

</body>
</html>
