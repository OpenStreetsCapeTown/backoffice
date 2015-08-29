<?php
$skip_login = true;
require_once 'functions.php';

header("Location: " . URL);
exit();

if (!$_POST) {
  $transport = $db->query("SELECT * FROM transport_options WHERE active = 1 ORDER BY id");
  $interaction = $db->query("SELECT * FROM interaction_options WHERE active = 1 ORDER BY id");
  $languages = $db->query("SELECT * FROM language_options WHERE active = 1 AND user_generated = 0 ORDER BY id");
  $communication = $db->query("SELECT * FROM communication_options WHERE active = 1 AND user_generated = 0 ORDER BY id");
  $os_communication = $db->query("SELECT * FROM os_communication_options WHERE active = 1 AND user_generated = 0 ORDER BY id");
  $income = $db->query("SELECT * FROM income_options WHERE active = 1");
  $suburbs = $db->query("SELECT * FROM suburbs WHERE active = 1 ORDER BY area, name");
  $areas = $db->query("SELECT suburbs.*, areas.name AS area
  FROM suburbs JOIN areas ON suburbs.area = areas.id
  WHERE suburbs.active = 1 ORDER BY areas.name, suburbs.name");
  $subscriptions = $db->query("SELECT * FROM preference_options WHERE type = 1 AND active = 1 ORDER BY id");
  $ages = $db->query("SELECT * FROM ages ORDER BY id");
}

if ($_POST) {
  $post = array(
    'ip' => mysql_clean($_SERVER["REMOTE_ADDR"]),
    'post' => mysql_clean(getinfo()),
    'date' => 'NOW()',
    'survey' => 1,
  );
  $db->insert("surveys",$post);
  $id = $db->insert_id;
  
  foreach ($_POST['question'] as $key => $value) {
    $post = array(
      'survey' => $id,
      'label' => mysql_clean($key),
      'answer' => mysql_clean($value),
    );
    $db->insert("survey_answers",$post);
  }
  
  foreach ($_POST['checklist'] as $key => $value) {
    foreach ($value as $subkey => $subvalue) {
      $post = array(
        'survey' => $id,
        'checklist' => mysql_clean($key),
        'answer' => mysql_clean($subvalue),
      );
      $db->insert("survey_answers",$post);
    }
  }
}

$frequency = array('day', 'week', 'month', 'year');

$involve = array(
  1 => "Talk about us on social media",
  2 => "Provide professional services, please specify",
  3 => "Volunteer on the day of an Open Streets activity",
  4 => "Help with fundraising",
  5 => "Initiate an activity in your area, please specify",
);

$ctrl = '<span>Use CTRL to select multiple options</span>';
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
textarea.form-control {
  height:90px;
}
.suburbgroupa, .suburbgroupb, .suburbgroupc {
  display:none;
}
.boxing {
  padding-bottom:15px;
}
.form-group {
  clear:both;
}
label.margin {
  margin-top:20px;
}
.tpline {
  line-height:30px;
  clear:both;
  margin-bottom:10px;
}
.ialine {
  line-height:30px;
  clear:both;
  margin-bottom:10px;
}
.ialine label {
  font-weight:normal;
}
.nodisplay, .hide99 {
  display:none;
}
select.show99, select.makesmall {
  width:400px;
}
input.hide99 {
  width:400px;
  float:left;
}
label span {
  font-weight:normal;
  display:inline-block;
  margin-left:20px;
  font-size:90%;
  color:#333;
}
<?php if ($_POST) { ?>
.form-group{overflow:hidden}
<?php } ?>
label.regular{font-weight:normal}
.makeblock{display:block}
</style>
<script type="text/javascript">
$(function(){
  $(".setarea").change(function(){
    var name = $(this).attr("id");
    $(".suburb"+name).hide();
    var id = $(this).val();
    $("#"+name+id).show('fast');
  });

  $(".show99").change(function(){
  var check = $(this).find('option[value="9999"]').prop('selected');
  if (check) {
      $(this).next(".hide99").show();
    } else {
      $(this).next(".hide99").hide();
    }
  });

  $("input[name='transport[]']").change(function(){
    var id = $(this).val();
    if ($(this).is(':checked')) {
      $("select[name='freq["+id+"]'").show('fast');
      $("select[name='period["+id+"]'").show('fast');
    } else {
      $("select[name='freq["+id+"]'").hide('fast');
      $("select[name='period["+id+"]'").hide('fast');
    }
  });

  $("input[name='share']").change(function(){
    var id = $(this).val();
    if ($(this).is(':checked')) {
      $(".contactinfo").show('fast');
    } else {
      $(".contactinfo").hide('fast');
    }
  });

});
</script>
</head>
<body>

<header>
  <img src="img/logo.png?refresh" alt="" />
  <h1>Open Streets Survey</h1>
</header>

<div class="container" id="content">

<h1>Survey</h1>

<?php if ($_POST['demographics']) { ?>

  <p>
   <strong>Thank you for your time. All this will go into making Open Streets work better for Cape Town! </strong>
  </p>

  <h2>We can use more of your help!</h2>

  <h3>Brainstorm with Open Streets Cape Town!</h3>

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

  <p><a class="btn btn-primary btn-large" href="./" target="_blank">Brainstorm with us!</a></p>


<?php } else { ?>

<p>Please note this information will remain confidential. We just want to get
to know you better and use this information to help inform our activities.</p>

<form method="post" class="form">

  <div class="form-group">
    <label class="margin makeblock">Do you endorse our <a href="http://openstreets.co.za/about/manifesto-open-streets-cape-town" target="_blank">manifesto</a>?</label>
    <label class="makeblock"><input type="radio" name="question[manifesto]" value="1" /> Yes</label>
    <label class="makeblock"><input type="radio" name="question[manifesto]" value="0" /> No</label>
  </div>

  <div class="form-group">
    <label class="margin">How have you been involved with Open Streets Cape Town so far</label>
    <?php while ($row = $interaction->fetch()) { ?>
    <div class="ialine">
      <div class="col-sm-7">
        <label>
          <input type="checkbox" name="checklist[interaction][]" value="<?php echo $row['id'] ?>" />
          <?php echo $row['name'] ?>
        </label>
      </div>
    <?php } ?>

    </div>

  <div class="form-group">
    <label>What did you enjoy the most at the Open Streets activitity you attended?</label>
      <textarea class="form-control" name="question[enjoy]"></textarea>
  </div>

  <div class="form-group">
    <label>What did you not like about the Open Streets activitiy you attended?</label>
      <textarea class="form-control" name="question[dislike]"></textarea>
  </div>

  <div class="form-group">
    <label>What would you like to see in future Open Streets activities?</label>
      <textarea class="form-control" name="question[future]"></textarea>
  </div>

  <div class="form-group">
    <label>How would you like to be involved? </label>

  <?php foreach ($involve as $key => $value) { ?>
  <div class="tpline">
    <div class="col-sm-10">
      <label>
        <input type="checkbox" name="checklist[involve][]" value="<?php echo $key ?>" />
        <?php echo $value ?>
      </label>
    </div>
  </div>
  <?php } ?>

      <label class="margin" style="display:block;clear:both">Please specify (if applicable):</label>
      <textarea class="form-control" name="question[involve-specify]"><?php echo $info->name ?></textarea>
  </div>



  <div class="form-group">
    <label class="margin">In which area do you live?</label>
      <select size="10" class="form-control show99" name="checklist[live][]">
      <?php while ($row = $areas->fetch()) { ?>
        <?php if ($area != $row['area']) { ?>
        <?php if ($area) { ?>
        </optgroup>
        <?php } ?>
        <optgroup label="<?php echo $row['area'] ?>">
      <?php } ?>
          <option value="<?php echo $row['id'] ?>"><?php echo $row['name'] ?></option>
        <?php $area = $row['area']; } ?>
        </optgroup>
        <optgroup label="Other">
          <option value="9999">Other</option>
        </optgroup>
      </select>

      <input type="text" name="question[live-specify]" placeholder="Please specify" class="form-control hide99" />
  </div>


<?php unset($area); $areas->reset(); $suburbs->reset(); ?>

<div class="form-group">
  <label class="margin">In which area(s) do you work / attend school / otherwise spend your day? <?php echo $ctrl ?></label>
    <select multiple size="10" class="form-control show99" name="checklist[work][]">
    <?php while ($row = $areas->fetch()) { ?>
      <?php if ($area != $row['area']) { ?>
      <?php if ($area) { ?>
      </optgroup>
      <?php } ?>
      <optgroup label="<?php echo $row['area'] ?>">
    <?php } ?>
        <option value="<?php echo $row['id'] ?>"><?php echo $row['name'] ?></option>
      <?php $area = $row['area']; } ?>
      </optgroup>
      <optgroup label="Other">
        <option value="9999">Other</option>
      </optgroup>
    </select>

    <input type="text" name="question[work-specify]" placeholder="Please specify" class="form-control hide99" />
</div>

<?php unset($area); $areas->reset(); $suburbs->reset(); ?>

<?php if (false) { ?>

<div class="form-group">
  <label class="margin">In which other area(s) do you exercise, relax, visit or look for entertainment in (everything besides work)? 
  <?php echo $ctrl ?>
  </label>
    <select multiple size="10" class="form-control show99">
    <?php while ($row = $areas->fetch()) { ?>
      <?php if ($area != $row['area']) { ?>
      <?php if ($area) { ?>
      </optgroup>
      <?php } ?>
      <optgroup label="<?php echo $row['area'] ?>">
    <?php } ?>
        <option value="<?php echo $row['id'] ?>"><?php echo $row['name'] ?></option>
      <?php $area = $row['area']; } ?>
      </optgroup>
      <optgroup label="Other">
        <option value="9999">Other</option>
      </optgroup>
    </select>

    <input type="text" name="other" placeholder="Please specify" class="form-control hide99" />
</div>


<div class="form-group">
  <label class="margin">What kind of transport do you use to get around Cape Town?</label>
  <?php while ($row = $transport->fetch()) { ?>
  <div class="tpline">
    <div class="col-sm-2">
      <label>
        <input type="checkbox" name="checklist[transport][]" value="<?php echo $row['id'] ?>" />
        <?php echo $row['name'] ?>
      </label>
    </div>

    <div class="col-sm-3">
      <select name="freq[<?php echo $row['id'] ?>]" class="form-control nodisplay">
        <option value="0">Select Frequency</option>
      <?php for ($i = 1; $i <= 10; $i++) { ?>
        <option value="<?php echo $i ?>"><?php echo $i ?> trip<?php echo $i > 1 ? 's' : ''; ?></option>
      <?php } ?>
      </select>
    </div>

    <div class="col-sm-3">
      <select name="period[<?php echo $row['id'] ?>]" class="form-control nodisplay">
      <?php foreach ($frequency as $value) { ?>
        <option value="<?php echo $value ?>">per <?php echo $value ?></option>
      <?php } ?>
      </select>
    </div>   

  </div>

  <?php } ?>

</div>

<?php } ?>

<div class="form-group">
  <label class="margin">How do you travel to work?</label>
  <?php while ($row = $transport->fetch()) { ?>
  <div class="tpline">
    <div class="col-sm-2">
      <label>
        <input type="checkbox" name="checklist[transport-work][]" value="<?php echo $row['id'] ?>" />
        <?php echo $row['name'] ?>
      </label>
    </div>
  </div>
  <?php } ?>
</div>

<div class="form-group">
  <label class="margin">What other modes of transport do you use when you are not traveling to work?</label>
  <?php $transport->reset(); while ($row = $transport->fetch()) { ?>
  <div class="tpline">
    <div class="col-sm-2">
      <label>
        <input type="checkbox" name="checklist[transport-notwork][]" value="<?php echo $row['id'] ?>" />
        <?php echo $row['name'] ?>
      </label>
    </div>
  </div>
  <?php } ?>
</div>

  <div class="form-group">
    <label class="margin">Age</span></label>
      <select class="form-control makesmall" name="question[age]" size="1">
        <option value=""></option>
          <?php while ($row = $ages->fetch()) { ?>
           <option value="<?php echo $row['id'] ?>"><?php echo $row['age'] ?></option>
          <?php } ?>
      </select>
  </div>


  <?php if (false) { ?>

  <div class="form-group">
    <label class="margin">Monthly household income</label>
    <select class="form-control" name="income">
      <option value=""></option>
        <?php while ($row = $income->fetch()) { ?>
         <option value="<?php echo $row['id'] ?>"><?php echo $row['name'] ?></option>
        <?php } ?>
    </select>
  </div>

  <div class="form-group">
    <label class="margin">Number of people in your household</label>
    <select class="form-control" name="householdsize">
      <option value=""></option>
        <?php for ($i = 1; $i <= 6; $i++) { ?>
         <option value="<?php echo $i ?>"><?php echo $i ?></option>
        <?php } ?>
          <option value="9999">More</option>
    </select>
  </div>

  <div class="form-group">
    <label class="margin">Home language <?php echo $ctrl ?></label>
      <select class="form-control show99" name="language" multiple size="4">
          <?php while ($row = $languages->fetch()) { ?>
           <option value="<?php echo $row['id'] ?>"><?php echo $row['name'] ?></option>
          <?php } ?>
            <option value="9999">Other</option>
      </select>
      <input type="text" class="form-control hide99" name="language_other" placeholder="Please specify" />
  </div>


  <div class="form-group">
    <label class="margin">Other spoken languages <span>Use CTRL to select multiple options</span></label>
      <select class="form-control show99" name="spoken_language" multiple size="4">
          <?php $languages->reset(); while ($row = $languages->fetch()) { ?>
           <option value="<?php echo $row['id'] ?>"><?php echo $row['name'] ?></option>
          <?php } ?>
            <option value="9999">Other</option>
      </select>
      <input type="text" class="form-control hide99" name="spoken_language_other" placeholder="Please specify" />
  </div>

  <div class="form-group">
    <label class="margin">To communicate, do you use a: <span>Use CTRL to select multiple options</span></label>
      <select class="form-control makesmall" name="communication" multiple size="6">
          <?php while ($row = $communication->fetch()) { ?>
           <option value="<?php echo $row['id'] ?>"><?php echo $row['name'] ?></option>
          <?php } ?>
      </select>
  </div>

  </div>

  <?php } ?>

  <div class="form-group">
    <label class="margin">How would you like to communicate with Open Streets Cape Town? <?php echo $ctrl ?></label>
      <select class="form-control makesmall" name="checklist[communicate][]" multiple size="3">
          <?php while ($row = $os_communication->fetch()) { ?>
           <option value="<?php echo $row['id'] ?>"><?php echo $row['name'] ?></option>
          <?php } ?>
      </select>
  </div>

  <?php if (false) { ?>


  <div class="form-group">
    <label class="col-sm-10 control-label">
      Would you be willing to let us link your details with your name and contact
      number? This would be used to understand and organise Friends of Open Streets
      better and help us notify you only of information that interests you. Your
      private information will not be shared with commercial parties.
    </label>
    <div class="ialaine">
      <div class="col-sm-12">
        <label class="regular">
          <input class="control" type="checkbox" name="share" value="1" />
          Yes, I am willing to share my contact information.
        </label>
      </div>
    </div>
  </div>

  <?php } ?>

  <div class="form-group">
    <label class="margin">How regularly are you happy to be contacted by Open Streets (tick one or more)?</label>
    <?php while ($row = $subscriptions->fetch()) { ?>
    <div class="tpline">
      <div class="col-sm-10">
        <label>
          <input type="checkbox" name="checklist[subscriptions][]" value="<?php echo $row['id'] ?>" />
          <?php echo $row['name'] ?>
        </label>
      </div>
    </div>
    <?php } ?>
  </div>


  <div class="contactinfo">
    <div class="form-group">
      <label class="margin">First Name</label>
      <input class="form-control" type="text" name="question[firstname]" />
    </div>

    <div class="form-group">
      <label class="margin">Surname</label>
      <input class="form-control" type="text" name="question[lastname]" />
    </div>

    <div class="form-group">
      <label class="margin">E-mail</label>
      <input class="form-control" type="email" name="question[email]" />
    </div>

    <div class="form-group">
      <label class="margin">Phone (optional)</label>
      <input class="form-control" type="text" name="question[phone]" />
    </div>
  </div>


<div class="form-group">
    <button type="submit" class="btn btn-primary" name="demographics" value="1">Send</button>
</div>

</form>

<?php } ?>

</div>

</body>
</html>
