<?php
require_once 'functions.php';

if ($_GET['delete']) {
  $delete = (int)$_GET['delete'];
  $info = $db->query("SELECT * FROM segments WHERE id = $delete");

  require_once 'mailchimp.php';
  $MailChimp = new \Drewm\MailChimp(MAILCHIMP_API_KEY);

  $print .= "Segment was successfully deleted from the database<br />";

  if ($info->mailchimp_id) {
    $return = $MailChimp->call('lists/segment-del',array(
      'id' => MAILCHIMP_LIST,
      'seg_id' => $info->mailchimp_id,
    ));
    if ($return['complete']) {
      $print .= "<br />Mailchimp segment was also removed";
    } else {
      $error .= "Mailchimp segment was <strong>NOT</strong> removed. Error message: <br />" . $return['error'];
    }
  } else {
    $print .= "No Mailchimp segment was found and therefore not deleted<br />";
  }
  $db->query("DELETE FROM segments WHERE id = $delete LIMIT 1");
}
$list = $db->query("SELECT * FROM segments ORDER BY date");
?>
<!doctype html>
<html>
<head>
<title><?php echo SITENAME ?></title>
<?php echo $head ?>
</head>
<body>

<?php require_once 'include.header.php'; ?>

  <h1>Segments</h1>

  <?php if ($print) { echo "<div class=\"alert alert-success\">$print</div>"; } ?>
  <?php if ($error) { echo "<div class=\"alert alert-danger\">$error</div>"; } ?>

  <table class="table table-striped">
    <tr>
      <th>Name</th>
      <th>Mailchimp ID</th>
      <th>People</th>
      <th>Last synced</th>
      <th>View</th>
      <th>Delete</th>
    </tr>
  <?php while ($row = $list->fetch()) { ?>
    <tr>
      <td><?php echo $row['name'] ?></td>
      <td><?php echo $row['mailchimp_id'] ?></td>
      <td><?php echo $row['people'] ?></td>
      <td><?php echo format_date("M d, Y", $row['date']) ?></td>
      <td><a href="people.filter.php?segment=<?php echo $row['id'] ?>">View segment</a></td>
      <td><a href="people.segments.php?delete=<?php echo $row['id'] ?>" onclick="javascript:return confirm('Are you sure?')" class="btn btn-danger">Delete segment</a></td>
    </tr>
  <?php } ?>
  </table>

<?php require_once 'include.footer.php'; ?>

</body>
</html>
