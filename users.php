<?php
require_once 'functions.php';

if (OPENID_USERID != 1 && OPENID_USERID != 4 && OPENID_USERID != 2) {
  die("No access");
}

$reactivate = (int)$_GET['reactivate'];
if ($reactivate) {
  $db->query("UPDATE openid_users SET status = 1 WHERE id = $reactivate");
}

$delete = (int)$_GET['delete'];
if ($delete) {
  $db->query("UPDATE openid_users SET status = 0 WHERE id = $delete");
}

$list = $db->query("SELECT * FROM openid_users ORDER BY id");
?>
<!doctype html>
<html>
<head>
<title><?php echo SITENAME ?></title>
<?php echo $head ?>
<style type="text/css">
.status-0{opacity:0.5}
</style>
</head>
<body>

<?php require_once 'include.header.php'; ?>

<table class="table table-striped">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Mail</th>
        <th>Creation Date</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
<?php while ($row = $list->fetch()) { ?>
    <tr class="status-<?php echo $row['status'] ?>">
        <td><?php echo $row['id'] ?></td>
        <td><?php echo $row['screenname'] ?></td>
        <td><?php echo $row['mail'] ?></td>
        <td><?php echo format_date("M d, Y", $row['creation_date']) ?></td>
        <td><?php echo $row['status'] ? "Active" : "Inactive" ?></td>
        <td>
          <?php if ($row['status']) { ?>
            <a href="users.php?delete=<?php echo $row['id'] ?>" class="btn btn-danger">Deactivate</a>
          <?php } else { ?>
            <a href="users.php?reactivate=<?php echo $row['id'] ?>" class="btn btn-success">Reactivate</a>
          <?php } ?>
        </td>
    </tr>
<?php } ?>
</table>

<?php require_once 'include.footer.php'; ?>

</body>
</html>
