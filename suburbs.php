<?php
require_once 'functions.php';

$areas = $db->query("SELECT * FROM areas WHERE active = 1 ORDER BY name");
$suburbs = $db->query("SELECT suburbs.*, areas.name AS area
FROM suburbs 
JOIN areas ON suburbs.area = areas.id
WHERE suburbs.active = 1 AND areas.active = 1
ORDER BY areas.name, suburbs.name");

if ($_GET['saved']) {
  $print = "Information has been saved";
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

  <?php if ($print) { echo "<div class=\"alert alert-success\">$print</div>"; } ?>

  <h1>Areas</h1>

  <table class="table table-striped">
    <tr>
      <th>Area</th>
      <th>Edit</th>
      <th>Delete</th>
    </tr>
  <?php while ($row = $areas->fetch()) { ?>
    <tr>
      <td><?php echo $row['name'] ?></td>
      <td><a href="edit/area/<?php echo $row['id'] ?>">Edit</a></td>
      <td><a href="suburbs.php?delete-area=<?php echo $row['id'] ?>" onclick="javascript:return confirm('Are you sure?')">Delete</a></td>
    </tr>
  <?php $area[$row['id']] = $row['name']; } ?>
  </table>

  <p><a href="info/area" class="btn btn-primary">Add Area</a></p>

  <h1>Suburbs</h1>

  <table class="table table-striped">
    <tr>
      <th>Suburb</th>
      <th>Area</th>
      <th>Edit</th>
      <th>Delete</th>
    </tr>
  <?php while ($row = $suburbs->fetch()) { ?>
    <tr>
      <td><?php echo $row['name'] ?></td>
      <td><?php echo $row['area'] ?></td>
      <td><a href="edit/suburb/<?php echo $row['id'] ?>">Edit</a></td>
      <td><a href="suburbs.php?delete-area=<?php echo $row['id'] ?>" onclick="javascript:return confirm('Are you sure?')">Delete</a></td>
    </tr>
  <?php } ?>
  </table>

  <p><a href="info/suburb" class="btn btn-primary">Add Suburb</a></p>

<?php require_once 'include.footer.php'; ?>

</body>
</html>
