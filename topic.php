<?php
$skip_login = true;
require_once 'functions.php';
$categories = $db->query("SELECT * FROM whiteboard_options WHERE active = 1 ORDER BY name");
$category = (int)$_GET['category'];
$info = $db->query("SELECT * FROM whiteboard_options WHERE id = $category");

if ($_POST) {
  $post = array(
    'name' => html($_POST['name']),
    'message' => html($_POST['message']),
    'date' => "NOW()",
    'ip' => mysql_clean($_SERVER["REMOTE_ADDR"]),
    'category' => $category,
  );
  $db->insert("whiteboard",$post);
  $print = "Your message has been posted!";
  $info = $db->query("SELECT * FROM whiteboard_options WHERE id = $category");
  $mail = 
"A message has been posted on the Open Streets Whiteboard ({$info->name}). 

Name: " . $_POST['name'] . "
Date: " . date("r") . "
Message:

" . $_POST['message'] . "

-------------
To go to this whiteboard, click here:
" . URL . "whiteboard/$category/" . flatten($info->name);

  if ($info->email) {
    mail($info->email, "New message on whiteboard", $msg, "automail@friends.openstreets.co.za");
  }
} 

if ($category) {
  $list = $db->query("SELECT * FROM whiteboard WHERE category = $category ORDER BY date DESC");
}
?>
<!doctype html>
<html>
<head>
<title><?php echo SITENAME ?></title>
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

          <?php } else { ?>
            <h1><?php echo $info->name ?></h1>
            <?php if ($list->num_rows) { ?>
            <?php if ($print) { echo "<div class=\"alert alert-success\">$print</div>"; } ?>
            <?php while ($row = $list->fetch()) { ?>
              <div class="well">
              <em><?php echo $row['name'] ?> wrote on <?php echo $row['date'] ?>:</em><br />
              <?php echo $row['message'] ?>
              </div>
            <?php } ?>

          <?php } else { ?>
            <p><em>There are no messages yet, be the first!</em></p>
          <?php } ?>
          <h3>Place a message</h3>
          <p class="alert alert-info">We want YOUR ideas and links. Please add them to our whiteborad.</p>
            <form method="post">
              <div class="form-group">
                <label class="col-sm-2 control-label">Your name</label>
                <div class="col-sm-10">
                  <input class="form-control" type="text" name="name" />
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-2 control-label">Your message</label>
                <div class="col-sm-10">
                  <textarea class="form-control" name="message"></textarea>
                </div>
              </div>

              <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                  <button type="submit" class="btn btn-primary">Send</button>
                </div>
              </div>
            </form>
          <?php } ?>
        </div>
        <div class="col-md-4">
          <h2>Categories</h2>
          <ul class="nav nav-pills nav-stacked">
          <?php while ($row = $categories->fetch()) { ?>
            <li<?php if ($row['id'] == $category) { echo ' class="active"'; } ?>><a href="whiteboard/<?php echo $row['id'] ?>/<?php echo flatten($row['name']) ?>"><?php echo $row['name'] ?></a></li>
          <?php } ?>
          </ul>
        </div>
      </div>




</div>

</body>
</html>
