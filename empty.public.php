<?php
$skip_login = true;
require_once 'functions.php';
?>
<!doctype html>
<html>
<head>
<title>XXXX | Friends of Open Streets</title>
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
</style>
</head>
<body>

<header>
  <a href="http://www.openstreets.co.za"><img src="img/logo.png?refresh" alt="" /></a>
  <h1><a href="./">Friends of Open Streets</a></h1>
</header>

<div class="container" id="content">

    <div class="jumbotron">
      <div class="container">
        <h1>Brainstorm with Open Streets!</h1>
      </div>
    </div>

    <div class="container">

      <h1>Here is the content</h1>
      <p>Welcome!</p>


    </div>

</div>

</body>
</html>
