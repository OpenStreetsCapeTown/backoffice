<?php
$skip_login = true;
require_once 'functions.php';
$categories = $db->query("SELECT * FROM whiteboard_options WHERE active = 1 ORDER BY name");
$category = (int)$_GET['category'];
$id = (int)$_GET['id'];

$info = $db->query("SELECT * FROM whiteboard_options WHERE id = $category");

if ($_POST) {
  $forbidden = array("a href", "[url");
  foreach ($forbidden as $value) {
    if (strpos($_POST['message'], $value) > -1) {
      die("Sorry, no URLs allowed");
    }
  }

  $post = array(
    'name' => html($_POST['name']),
    'message' => html($_POST['message']),
    'date' => "NOW()",
    'ip' => mysql_clean($_SERVER["REMOTE_ADDR"]),
    'category' => $category,
    'subject' => html($_POST['subject']),
    'parent_message' => $id ? $id : "NULL",
  );
  $db->insert("whiteboard",$post);
  $post_id = $db->insert_id;
  $print = "Your message has been posted!";
  $getinfo = $db->query("SELECT * FROM whiteboard_options WHERE id = $category");
  $mail = 
"A message has been posted on the Open Streets Whiteboard ({$getinfo->name}). 

Name: " . $_POST['name'] . "
Date: " . date("r") . "
Message:

" . $_POST['message'] . "

-------------
To go to this whiteboard, click here:
" . URL . "whiteboard/$category/" . flatten($getinfo->name) . "/$id";

  if ($getinfo->email) {
    mail($getinfo->email, "New message on whiteboard", $msg, "automail@friends.openstreets.co.za");
  }
} 

if ($id) {
  $main = $db->query("SELECT * FROM whiteboard WHERE id = $id");
  $list = $db->query("SELECT * FROM whiteboard WHERE parent_message = $id ORDER BY date ASC");
} elseif ($category) {
  $list = $db->query("SELECT id, name, subject,
  (SELECT date FROM whiteboard w WHERE w.parent_message = whiteboard.id OR w.id = whiteboard.id ORDER BY date DESC LIMIT 1) 
    AS last_message
  FROM whiteboard WHERE category = $category AND parent_message IS NULL ORDER BY last_message DESC");
} else {
  $list = $db->query("SELECT DISTINCT whiteboard.category, whiteboard.subject, o.name AS section,
    (SELECT subject FROM whiteboard w WHERE w.id = whiteboard.parent_message) AS alt_subject,
    whiteboard.category, parent_message
  FROM whiteboard
    JOIN whiteboard_options o ON whiteboard.category = o.id
  WHERE parent_message IS NOT NULL
  ORDER BY date DESC LIMIT 5");
}
?>
<!doctype html>
<html>
<head>
<title><?php echo $id ? $main->subject : $info->name ?> <?php if ($category) { echo '|'; } ?> Open Streets Whiteboard</title>
<?php echo $head ?>
<style type="text/css">
header {
  height:115px;
  width:100%;
  background:#333333 url(img/people.png) no-repeat center bottom;
  position:relative;
  -webkit-box-shadow:1px 2px 5px 1px #d5d5d5;
  box-shadow:1px 2px 5px 1px #d5d5d5;
  -moz-box-shadow: 1px 2px 5px 1px #d5d5d5;
}
header h1 {
  color:#fff;
  text-align:center;
  font-size:43px;
  font-weight:bold;
  margin:0;
  padding:10px 0 0 0;
}
header img {
  position:absolute;
  top:5px;
  left:6px;
}
form div.form-group{padding-top:10px;clear:both;overflow:hidden}
textarea.form-control{height:200px}
.well em{color:#999}
a.right{float:right;margin-bottom:10px;}
<?php if ($id) { ?>
h1{font-size:18px;font-weight:700}
<?php } ?>
.oswebsite img{display:block;margin:30px 0 5px 0}
.oswebsite a{font-weight:bold;color:#333}
.nav > li.oswebsite > a:hover{background:#fff}
</style>
</head>
<body>

<header>
  <a href="http://www.openstreets.co.za"><img src="img/logo.png?refresh" alt="" /></a>
  <h1><a href="./">Friends of Open Streets</a></h1>
</header>

<div class="container" id="content">

    <?php if (!$category) { ?>
    <div class="jumbotron">
      <div class="container">
        <h1>Brainstorm with Open Streets!</h1>
      </div>
    </div>
    <?php } ?>

    <div class="container">
      <!-- Example row of columns -->
      <div class="row">
        <div class="col-md-8">
        <?php if (!$category) { ?>
          <h2>Instructions</h2>

          <p>Would you like your streets to be safer? More lively and interesting?
          Greener? Quieter? What else would you like your streets to become? Our streets
          have a history in a divided city. They have been a place for protest and
          violence and have become dominated by the car and other noisy, dangerous and
          dirty motorised vehicles. Our streets could be so much more than they are. Open
          Streets Cape Town would like to help you connect with enthusiastic others that
          share a passion for making small local changes to your streets that make a big
          difference to the City of Cape Town. Explore our idea boards, add in your ideas
          where your see a gap (Be anonymous if you must, but don't hold back!) and see
          if you can connect with others with ideas like yours. </p>

          <h2>Most recent responses...</h2>

            <table class="table table-striped">
              <tr>
                <th>Topic</th>
                <th>Section</th>
              </tr>
            <?php while ($row = $list->fetch()) { ?>
              <tr>
                <td><a href="whiteboard/<?php echo $row['category'] ?>/<?php echo flatten($row['section']) ?>/<?php echo $row['parent_message'] ?>"><?php echo $row['subject'] ? $row['subject'] : $row['alt_subject'] ?></a></td>
                <td><?php echo $row['section'] ?></td>
              </tr>
            <?php } ?>
            </table>


          <?php } elseif ($id) { ?>

            <h1><?php echo $main->subject ?></h1>

            <p><em><?php echo $main->name ?> wrote on <?php echo format_date("M d, Y H:i", $main->date) ?>:</em></p>
            <p><?php echo $main->message ?></p>

            <?php if ($list->num_rows) { ?>
            <?php while ($row = $list->fetch()) { ?>
              <div class="well">
              <em><?php echo $row['name'] ?> wrote on <?php echo format_date("M d, Y H:i", $row['date']) ?>:</em><br />
              <?php echo $row['message'] ?>
              </div>
            <?php } ?>

          <?php } ?>

          <?php } else { ?>
            <h1><?php echo $info->name ?></h1>
            <?php if ($list->num_rows) { ?>
            <?php if ($print) { echo "<div class=\"alert alert-success\">$print</div>"; } ?>

            <table class="table table-striped">
              <tr>
                <th>Topic</th>
                <th width="120">Last message</th>
                <th>Person</th>
              </tr>
            <?php while ($row = $list->fetch()) { ?>
              <tr>
                <td><a href="whiteboard/<?php echo $category ?>/<?php echo flatten($info->name) ?>/<?php echo $row['id'] ?>"><?php echo $row['subject'] ?></a></td>
                <td><?php echo format_date("M d, Y", $row['last_message']) ?></td>
                <td><?php echo $row['name'] ?></td>
              </tr>
            <?php } ?>
            </table>

            <?php while ($row = $list->fetch()) { ?>
              <div class="well">
              <em><?php echo $row['name'] ?> wrote on <?php echo $row['date'] ?>:</em><br />
              <?php echo $row['message'] ?>
              </div>
            <?php } ?>

          <?php } else { ?>
            <p><em>There are no messages yet, be the first!</em></p>
          <?php } ?>
          <?php } ?>

          <?php if ($category) { ?>
          <h3><?php echo $id ? "Post a response" : "Add a new topic" ?></h3>
          <?php if (!$id) { ?>
            <p class="alert alert-info">We want YOUR ideas and links. Please add them to our whiteborad.</p>
          <?php } elseif (!$list->num_rows) { ?>
            <p class="alert alert-info"><em>There are no responses yet, be the first!</em></p>
            <?php } ?>

            <form method="post">

            <?php if (!$id) { ?>
              <div class="form-group">
                <label class="col-sm-2 control-label">Subject</label>
                <div class="col-sm-10">
                  <input class="form-control" type="text" name="subject" required />
                </div>
              </div>
            <?php } ?>

              <div class="form-group">
                <label class="col-sm-2 control-label">Your name</label>
                <div class="col-sm-10">
                  <input class="form-control" type="text" name="name" required />
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-2 control-label">Your <?php echo $id ? "response" : "message" ?></label>
                <div class="col-sm-10">
                  <textarea class="form-control" name="message" required></textarea>
                </div>
              </div>

              <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                  <button type="submit" class="btn btn-primary">Send</button>
                </div>
              </div>
            </form>

            <?php if ($id) { ?>
            <p>
              <a href="whiteboard/<?php echo $category ?>/<?php echo flatten($info->name) ?>" class="btn btn-primary right">&laquo; Back to '<?php echo $info->name ?>'</a>
            </p>
            <?php } else { ?>
              <p>
                <a href="./" class="btn btn-primary right">&laquo; Back to the Whiteboard Homepage</a>
              </p>
            <?php } ?>

        <?php } ?>
        </div>
        <div class="col-md-4">
          <h2>Categories</h2>
          <ul class="nav nav-pills nav-stacked">
          <?php while ($row = $categories->fetch()) { ?>
            <li<?php if ($row['id'] == $category) { echo ' class="active"'; } ?>><a href="whiteboard/<?php echo $row['id'] ?>/<?php echo flatten($row['name']) ?>"><?php echo $row['name'] ?></a></li>
          <?php } ?>
            <li class="oswebsite"><a href="http://openstreets.co.za">
            <img src="img/logo.white.jpg" alt="" />
            Open Streets Website</a></li>
          </ul>
        </div>
      </div>




</div>

</body>
</html>
