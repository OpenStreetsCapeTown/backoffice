<?php
require_once 'functions.php';
$id = (int)$_GET['id'];

if ($_POST['done']) {
  $done_id = (int)$_POST['done'];
  if ($_POST['type'] != "regular") {
    $check = $db->query("SELECT * FROM planning_providers WHERE checklist = $done_id");
    $post = array(
      'checklist' => $done_id,
    );
    if ($_POST['type'] == "provider") {
      $post['provider'] = html($_POST['provider']);
    } elseif ($_POST['type'] == "invoice") {
      $post['invoice'] = 1;
    } elseif ($_POST['type'] == "quotes") {
      $post['quotes'] = 1;
    }
    if ($check->id) {
      $db->update("planning_providers",$post,"id = $check->id");
    } else {
      $db->insert("planning_providers",$post);
    }
    $print = "Information was saved.";
  } 
  if ($_POST['type'] == "regular" || $_POST['type'] == "provider") {
    $post = array(
      'finished_date' => mysql_clean(format_date("Y-m-d", $_POST['date'])),
      'finished_user' => OPENID_USERID,
      'comments' => html($_POST['comments']),
    );
    $db->update("planning_checklist",$post,"id = $done_id AND finished_date IS NULL");
    $print = "Item was marked as finished";
  }
}

if ($_GET['delete']) {
  $delete = (int)$_GET['delete'];
  $db->query("DELETE FROM planning_checklist WHERE id = $delete LIMIT 1");
  $print = "Item was removed";
}

$info = $db->query("SELECT * FROM events WHERE id = $id");

$list = $db->query("SELECT prov.*, c.*, cat.name AS category, cat.id AS category_id
FROM 
  planning_checklist c
    JOIN planning_categories cat ON c.category = cat.id
    LEFT JOIN planning_providers prov ON prov.checklist = c.id
WHERE event = $id
ORDER BY cat.position, c.position, c.id
");

while ($row = $list->fetch()) {
  $category_name[$row['category_id']] = $row['category'];
  $total[$row['category_id']] ++;
  $done[$row['category_id']] += $row['finished_date'] ? 1 : 0;
  $overall['total']++;
  if ($row['finished_date']) {
    $overall['done']++;
  }
}

$list->reset();

if (!$list->num_rows && $_GET['load']) {
 $db->query("INSERT INTO planning_checklist (
   category, name, position, type, event
   ) 
 SELECT category, name, position, type, $id FROM planning_checklist_template ORDER BY position");
 header("Location: " . URL . "events/checklist/$id");
 exit();
}

?>
<!doctype html>
<html>
<head>
<title>Checklist | <?php echo $info->name ?> | <?php echo SITENAME ?></title>
<?php echo $head ?>
<style type="text/css">
.checklist ul{list-style:none;margin:0 0 40px 0;padding:0}
.checklist ul ul{list-style:none;margin:0 0 0 30px;padding:0;font-size:13px;opacity:0.56}
.checklist .regular .fa-check{opacity:0.3}
.fa-comments,.checklist .regular .fa-check:hover{opacity:1;cursor:pointer}
.checklist .done{color:#3C763D}
.checklist .regular{color:#333}
.provider{display:none}
.checklist .modal{color:#333}
.checklist li .badge{display:none}
.well{text-align:center}
.btn-group{position:relative;top:-6px}
li .fa-remove,li .fa-pencil{display:none}
meter{width:100%}
</style>
<script type="text/javascript">
$(function(){

  $(".fa-comments").click(function(){
    var id = $(this).data("id");
    $("#comments"+id).modal();
  });
  $(".fa-check").click(function(){
    $("#done").modal();
    var id = $(this).data("id");
    $("#id").val(id);
    var type = $(this).data("type");
    $("#type").val(type);
    if (type == "provider") {
      $(".provider").show();
    } else {
      $(".provider").hide();
    }
    if (type == "invoice" || type == "quotes") {
      $(".comment").hide();
    } else {
      $(".comment").show();
    }
    $("#doneform").attr("action", "<?php echo URL ?>events/checklist/<?php echo $id ?>#cat"+$(this).data("category"));
  });
  $(".well a.calendar").click(function(e){
    e.preventDefault();
    $(this).parents("div.well").next("ul").find(".badge").toggle();
    $(this).toggleClass("btn-primary");
  });
  $(".well a.remove").click(function(e){
    e.preventDefault();
    $(this).parents("div.well").next("ul").find(".fa-check").toggle();
    $(this).parents("div.well").next("ul").find(".fa-remove").toggle();
    $(this).toggleClass("btn-primary");
  });
  $(".well a.pencil").click(function(e){
    e.preventDefault();
    $(this).parents("div.well").next("ul").find(".fa-check").toggle();
    $(this).parents("div.well").next("ul").find(".fa-pencil").toggle();
    $(this).toggleClass("btn-primary");
  });
});
</script>
</head>
<body>

<?php require_once 'include.header.php'; ?>

<h1>Checklist <?php echo $info->name ?></h1>

<span>
<?php echo (int)($overall['done']/$overall['total']*100) ?>%
completed
</span>
<meter 
  max="<?php echo $overall['total'] ?>" 
  low="1"
  high="1"
  optimum="1"
  value="<?php echo $overall['done'] ?>"
>
<?php echo $overall['done']/$overall['total']*100 ?>%
</meter>

<?php if ($print) { echo "<div class=\"alert alert-success\">$print</div>"; } ?>

<?php if ($list->num_rows) { ?>
<div class="alert alert-info">
  <h2>Index</h2>
  <ul>
  <?php foreach ($category_name as $key => $value) { ?>
    <li><a href="events/checklist/<?php echo $id ?>#cat<?php echo $key ?>"><?php echo $value ?></a></li>
  <?php } ?>
  </ul>
</div>

<div class="checklist">
  <?php while ($row = $list->fetch()) { ?>
    <?php if ($row['category'] != $category) { ?>
      <?php if ($category) { ?>
        </ul>
      <?php } ?>
      <h2 id="cat<?php echo $row['category_id'] ?>"><?php echo $row['category'] ?></h2>
      <div class="well">
        <span class="items pull-left">
          <strong><?php echo $total[$row['category_id']] ?></strong> items found.
        </span>
        <div class="btn-group pull-right">
          <a class="btn btn-default remove"><i class="fa fa-remove"></i></a>
          <a class="btn btn-default pencil"><i class="fa fa-pencil"></i></a>
          <a class="btn btn-default plus" href="event.checklist.item.php?event=<?php echo $id ?>&amp;category=<?php echo $row['category_id'] ?>"><i class="fa fa-plus"></i></a>
          <a class="btn btn-default calendar"><i class="fa fa-calendar"></i></a>
        </div>
        <span class="badge">
          <?php echo number_format($done[$row['category_id']]/$total[$row['category_id']]*100) ?>% finished
        </span>
      </div>
      <ul>
    <?php } ?>
      <li class="<?php echo $row['finished_date'] ? 'done' : 'regular'; ?>">
        <strong><i class="fa fa-check" data-category="<?php echo $row['category_id'] ?>" data-type="<?php echo $row['type'] ?>" data-id="<?php echo $row['id'] ?>"></i></strong>
        <a href="event.checklist.php?id=<?php echo $id ?>&amp;delete=<?php echo $row['id'] ?>#cat<?php echo $row['category_id'] ?>" onclick="javascript:return confirm('Are you sure?')">
          <i class="fa fa-remove"></i>
        </a>
        <a href="event.checklist.item.php?id=<?php echo $row['id'] ?>">
          <i class="fa fa-pencil"></i>
        </a>
        <?php echo $row['name'] ?>

        <?php if ($row['provider']) { ?>
        - <strong><?php echo $row['provider'] ?></strong>
        <?php } ?>

        <?php if ($row['comments']) { ?>
          <i class="fa fa-comments" data-id="<?php echo $row['id'] ?>"></i>
          <div class="modal fade" id="comments<?php echo $row['id'] ?>" tabindex="-1" role="dialog">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="mlabel">Comments</h4>
                  </div>
                  <div class="modal-body">
                    <?php echo $row['comments'] ?>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                  </div>
                </div>
              </div>
          </div>

        <?php } ?>

        <?php if ($row['finished_date']) { ?>
          <span class="badge">
            <?php echo format_date("M d, Y", $row['finished_date']) ?>
          </span>
        <?php } ?>

        <?php if ($row['type'] == "provider") { ?>
          <ul>
            <li class="<?php echo $row['quotes'] ? 'done' : 'regular'; ?>">
              <strong><i class="fa fa-check" data-type="quotes" data-id="<?php echo $row['id'] ?>"></i></strong>
              Quotes
            </li>
            <li class="<?php echo $row['invoice'] ? 'done' : 'regular'; ?>">
              <strong><i class="fa fa-check" data-type="invoice" data-id="<?php echo $row['id'] ?>"></i></strong>
              Invoice
            </li>
          </ul>
        <?php } ?>
      </li>
    <?php $category = $row['category']; } ?>
  </ul>
</div>

<div class="modal fade" id="done" tabindex="-1" role="dialog" aria-labelledby="mlabel">
  <form method="post" class="form form-horizontal" action="events/checklist/<?php echo $id ?>" id="doneform">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="mlabel">Mark as done</h4>
        </div>
        <div class="modal-body">
            <div class="form-group provider">
              <label class="col-sm-2 control-label">Provider</label>
              <div class="col-sm-10">
                <input class="form-control" type="text" name="provider" value="<?php echo $info->provider ?>" />
              </div>
            </div>
            <div class="form-group comment">
              <label class="col-sm-2 control-label">Comments</label>
              <div class="col-sm-10">
                <input class="form-control" type="text" name="comments" value="<?php echo $info->comments ?>" />
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-2 control-label">Date</label>
              <div class="col-sm-10">
                <input class="form-control" type="date" name="date" value="<?php echo $info->finished_date ?: date("Y-m-d"); ?>" />
              </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save</button>
          <input type="hidden" name="done" id="id" value="" />
          <input type="hidden" name="type" id="type" value="" />
        </div>
      </div>
    </div>
  </form>
</div>

<?php } else { ?>

  <div class="alert alert-danger">
    The checklist was not yet loaded. 
  </div>
  <p><a href="event.checklist.php?id=<?php echo $id ?>&load=1" class="btn btn-primary btn-lg">Load Checklist</a></p>

<?php } ?>

<?php require_once 'include.footer.php'; ?>

<?php if (false) { ?>

<script src="//cdn.jsdelivr.net/webshim/1.12.4/extras/modernizr-custom.js"></script>
<script src="//cdn.jsdelivr.net/webshim/1.12.4/polyfiller.js"></script>
<script>
  webshims.setOptions('waitReady', false);
  webshim.setOptions("forms-ext", {
    "widgets": {
      "startView": 2,
      "openOnFocus": true
    }
  });
  webshims.polyfill('forms forms-ext');
</script>

<?php } ?>

</body>
</html>
