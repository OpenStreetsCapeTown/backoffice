<?php
require_once 'functions.php';

$list = $db->query("SELECT surveys.*, people.firstname, people.lastname
FROM surveys 
LEFT JOIN people ON surveys.people = people.id
ORDER BY surveys.id");
?>
<!doctype html>
<html>
<head>
<title><?php echo SITENAME ?></title>
<?php echo $head ?>
</head>
<body>

<?php require_once 'include.header.php'; ?>

<h1>Surveys</h1>

<div class="alert alert-info"><?php echo $list->num_rows ?> surveys found.</div>

<table class="table table-striped">
  <tr>
    <th>#</th>
    <th>Date</th>
    <th>Name</th>
    <th>Details</th>
  </tr>
<?php while ($row = $list->fetch()) { ?>
  <tr>
    <td><?php echo $row['id'] ?></td>
    <td><?php echo $row['date'] ?></td>
    <td><?php echo $row['lastname'] ?>, <?php echo $row['firstname'] ?></td>
    <td><a href="survey/results/<?php echo $row['id'] ?>">View Details</a></td>
  </tr>
<?php } ?>
</table>

<p><a href="survey.overview.php" class="btn btn-info">View full survey overview</a></p>

<?php require_once 'include.footer.php'; ?>

</body>
</html>
