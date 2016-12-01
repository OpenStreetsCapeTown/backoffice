<?php
require_once 'functions.php';
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
        <th>Name</th>
        <th>Mail</th>
        <th>Creation Date</th>
        <th>Last Login</th>
        <th>Status</th>
    </tr>
<?php while ($row = $list->fetch()) { ?>
    <tr class="status-<?php echo $row['status'] ?>">
        <td><?php echo $row['screenname'] ?></td>
        <td><?php echo $row['mail'] ?></td>
        <td><?php echo format_date("M d, Y", $row['creation_date']) ?></td>
        <td><?php echo format_date("M d, Y", $row['last_login']) ?></td>
        <td><?php echo $row['status'] ? "Active" : "Inactive" ?></td>
    </tr>
<?php } ?>
</table>

<?php require_once 'include.footer.php'; ?>

</body>
</html>
