<?php
require_once 'functions.php';

if ($_GET['delete']) {
  $delete = (int)$_GET['delete'];
  $db->query("DELETE FROM planning_checklist_template WHERE id = $delete LIMIT 1");
  $print = "Item was removed";
}

$list = $db->query("SELECT prov.*, c.*, cat.name AS category, cat.id AS category_id
FROM 
  planning_checklist_template c
    JOIN planning_categories cat ON c.category = cat.id
    LEFT JOIN planning_providers prov ON prov.checklist = c.id
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

// Let's also get those categories with no items in it yet.
$categories = $db->query("SELECT * FROM planning_categories 
  WHERE NOT EXISTS(SELECT * FROM planning_checklist_template WHERE category = planning_categories.id)");

while ($row = $categories->fetch()) {
  $category_name[$row['id']] = $row['name'];
}

$categories->reset();

?>
<!doctype html>
<html>
<head>
<title>Checklist | Template | <?php echo SITENAME ?></title>
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
.well{text-align:center;min-height:60px}
.btn-group{position:relative;top:-6px}
li .fa-remove,li .fa-pencil{display:none}
meter{width:100%}
</style>
<script type="text/javascript">
$(function(){

  $(".well a.remove").click(function(e){
    e.preventDefault();
    $(this).parents("div.well").next("ul").find(".fa-check").toggle();
    $(this).parents("div.well").next("ul").find(".fa-remove").toggle();
    $(this).toggleClass("btn-primary");
  });
  $(".well a.pencil").click(function(e){
    e.preventDefault();
    $(this).parents("div.well").next("ul").find(".fa-pencil").toggle();
    $(this).toggleClass("btn-primary");
  });
});
</script>
</head>
<body>

<?php require_once 'include.header.php'; ?>

<h1>Checklist Template</h1>

<?php if ($print) { echo "<div class=\"alert alert-success\">$print</div>"; } ?>

<div class="alert alert-info">
  <a href="checklist/template/category/add" class="btn btn-default pull-right"><i class="fa fa-plus"></i></a>
  <h2>Index</h2>
  <ul>
  <?php foreach ($category_name as $key => $value) { ?>
    <li><a href="checklist/template#cat<?php echo $key ?>"><?php echo $value ?></a></li>
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
          <a class="btn btn-default plus" href="checklist.template.item.php?category=<?php echo $row['category_id'] ?>"><i class="fa fa-plus"></i></a>
        </div>
      </div>
      <ul>
    <?php } ?>
      <li class="<?php echo $row['finished_date'] ? 'done' : 'regular'; ?>">
        <a href="checklist.template.php?id=<?php echo $id ?>&amp;delete=<?php echo $row['id'] ?>#cat<?php echo $row['category_id'] ?>" onclick="javascript:return confirm('Are you sure?')">
          <i class="fa fa-remove"></i>
        </a>
        <a href="checklist.template.item.php?id=<?php echo $row['id'] ?>">
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

        <?php if ($row['type'] == "provider") { ?>
              - <span class="badge">Provider item</span>
        <?php } ?>
      </li>
    <?php $category = $row['category']; } ?>
  </ul>

  <?php while ($row = $categories->fetch()) { ?>
    <h2 id="cat<?php echo $row['id'] ?>"><?php echo $row['name'] ?></h2>
    <div class="well">
      <span class="items pull-left">
        <strong>0</strong> items found.
      </span>
      <div class="btn-group pull-right">
        <a class="btn btn-default plus" href="checklist.template.item.php?category=<?php echo $row['id'] ?>"><i class="fa fa-plus"></i></a>
      </div>
    </div>
  <?php } ?>

</div>

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
