<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<!DOCTYPE html>
<html>
<head>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">


<!-- include summernote css/js -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">

<?php	$this->load->view("account/inc/head");?>
</head>
<body class="hold-transition skin-blue layout-top-nav" style="height: auto;">
<div class="wrapper">
<?php	$this->load->view("Site/inc/header");?>

<?php
$user = $GLOBALS["loguser"];
if ($user['image'] != '' && $user['image'] != ' ') {$prf_img = $user['image'];} else { $prf_img = 'assets/crm/dist/img/user4-128x128.jpg';}
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><strong>Reminder</strong></h1>
    </section>

    <!-- Main content -->
    <section class="content">

      <div class="row">

        <div class="col-md-12">
          <div class="nav-tabs-custom">
<ul class="nav nav-tabs">
<?php	if ($GLOBALS["loguser"]["role"] == "Company") {?>
<li><a href="<?php echo base_url("account/company"); ?>"><i class="fa fa-globe"></i> Company</a></li>
<li><a href="<?php echo base_url("account/team"); ?>"><i class="fa fa-user"></i> Users</a></li>
<li><a href="<?php echo base_url("account/emails"); ?>"><i class="fa fa-envelope-o"></i> SMTP Emails</a></li>
<li><a href="<?php echo base_url("account/billing"); ?>"><i class="fa fa-credit-card-alt"></i> Payment</a></li>
<li><a href="<?php echo base_url("integration"); ?>"><i class="fa fa-share"></i> Integrations</a></li>
<li class="active"><a href="<?php echo base_url("account/reminders"); ?>"><i class="fa fa-credit-card-alt"></i> Reminders</a></li>
<?php	}?>
<li ><a href="<?php echo base_url("account/profile"); ?>"><i class="fa fa-pencil"></i> My Profile</a></li>
<li><a href="<?php echo base_url("account/cp"); ?>"><i class="fa fa-lock"></i> Change Password</a></li>

</ul>
            <div class="tab-content">

              <div class="active tab-pane" id="settings">

<?php	$this->load->view("template/alert.php");?>
<?php echo form_open(current_url(), array('enctype' => 'multipart/form-data')); ?>
<?php

if ($_POST) {
	// print_r($_POST);
	// die();
	$reminder_id = $_POST['reminder_name'];
	$temp = 1;
}
?>
<div class="row">
    <div class="form-group col-md-4">
        <label>Program Name</label>
        <select name="program_name" class="form-control" id="programDropdown" required>
            <option value="">Select Program Name</option>
            <?php foreach ($programs as $program): ?>
                <option value="<?php echo $program['program_title'] ?? 0 ?>"><?php echo $program['program_title'] ?? 'No Program' ?></option>
            <?php endforeach;?>
        </select>
    </div>
    <div class="form-group col-md-4" id="remindercol" style="display:none;">
        <label>Reminder Name</label>
        <select name="reminder_name" class="form-control" id="reminderDropdown" required>

        </select>
    </div>
    <div class="clr"></div>

    <div id="result">
        <div class="reminder-fields <?php echo $reminders_data['reminder_rule_id'] ?>">

            <!-- <input type="hidden" name="reminder_rule_id" value="<?php echo $reminders_data["reminder_rule_id"] ?>" /> -->

                <div class="form-group col-md-4">
                    <label for="e">Reminder Description</label>
                    <div name="reminder_desc"><?php echo $reminders_data['reminder_desc'] ?></div>
                </div>


                <div class="form-group col-md-4">
                    <label for="e">Program Name</label>
                    <div name="program_title"><?php echo $reminders_data['program_title'] ?? '-' ?></div>
                </div>
                <div class="clr"></div>
                <div class="form-group col-md-4">
                    <label for="e">Step Name</label>
                    <div name="step_name"><?php echo $reminders_data['step_name'] ?? '-' ?></div>
                </div>


                <div class="form-group col-md-4">
                    <label for="e">Step Number</label>
                    <div name="step_id"><?php echo $reminders_data['step_id'] ?? '-' ?></div>
                </div>
                <div class="clr"></div>
                <div class="form-group col-md-4">
                    <label for="e">Days to send *</label>
                    <?php echo form_input(['type' => 'number', 'name' => 'days_to_send', 'value' => $_POST['days_to_send'], 'class' => 'form-control', 'required' => 'required', 'min' => '1']); ?>
                    <span>Specify the number of days after this step starts that you want the reminder email to start sending.</span>
                </div>



                <div class="form-group col-md-4">
                    <label for="e">Send Frequency</label>
                    <?php echo form_input(['type' => 'number', 'name' => 'send_frequency', 'value' => $_POST['send_frequency'], 'class' => 'form-control', 'required' => 'required', 'min' => '0']); ?>
                    <span>Once the reminder has started, how often do you want the reminder to be re-sent?</span>
                </div>
                <div class="clr"></div>
                <div class="form-group col-md-4">
                    <label for="e">Stop Sending Days</label>
                    <?php echo form_input(['type' => 'number', 'name' => 'stop_sending_days', 'value' => $_POST['stop_sending_days'], 'class' => 'form-control', 'required' => 'required', 'min' => '0']); ?>
                    <span>The max number of days to go by before the reminder should stop being sent. Note: Entering 0 will indicate you do not want any reminders to be sent.</span>
                </div>


                <div class="form-group col-md-4">
                    <label for="e">Email Subject</label>
                    <?php echo form_input(['type' => 'text', 'name' => 'reminder_email_subject', 'value' => $_POST['reminder_email_subject'], 'class' => 'form-control', 'required' => 'required']); ?>
                </div>

                <!-- <div class="form-group col-md-4">
                    <label for="e">Email Body</label>
                    <?php echo form_input(['type' => 'text', 'name' => 'reminder_email_body', 'value' => $reminders_data['reminder_email_body'], 'class' => 'form-control', 'required' => 'required']); ?>
                </div> -->
                <div class="clr"></div>
                <div id="showmsg" class="col-md-12" style="color:red;margin:10px 0;display:none;"></div>
                <div class="clr"></div>
                <div class="form-group col-md-12">
                    <label for="e">Email Body</label>
                    <textarea id="summernote" name="reminder_email_body"><?php echo $_POST['reminder_email_body']; ?></textarea>
                </div>

            </div>
        </div>

    </div>

</div>


<div class="form-group">
    <button type="submit" name="Submit_" class="btn btn-primary">Submit</button>
    <button type="submit" name="Submit_Preview " class="btn btn-primary">Preview </button>
</div>
<?php echo form_close(); ?>
              </div>
              <!-- /.tab-pane -->
            </div>
            <!-- /.tab-content -->
          </div>
          <!-- /.nav-tabs-custom -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->

    </section>
    <!-- /.content -->
  </div>

<?php	$this->load->view("account/inc/footer");?>

</div>
<?php	$this->load->view("account/inc/template_js.php");?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
<script>
    $(document).ready(function() {
    $('#summernote').summernote({
        fontSizes: ['8', '9', '10', '11', '12', '14', '18', '24', '36', '48' , '64', '82'],
        styleTags: [
            'p',
            { title: 'Blockquote', tag: 'blockquote', className: 'blockquote', value: 'blockquote' },
            'pre', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'
            ],
        toolbar: [
            // [groupName, [list of button]]
            ['style', ['style','bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['fontname', ['fontname']],
            ['insert', ['link', 'picture', 'video']],
            ['height', ['height']],
            ['view', ['fullscreen']],
        ],
        height: 700,
        minHeight: null, // set minimum height of editor
        maxHeight: null, // set maximum height of editor
        focus: false, // set focus to editable area after initializing summernote
        lineWrapping: true,
        prettifyHtml: true
    });
$('.dropdown-toggle').dropdown();
});
</script>
<script>
        $(document).ready(function() {
          var reminderPost = '<?php echo $temp ?>';
          if(reminderPost == 1){
            $('.reminder-fields').show();


          }else{
            $('.reminder-fields').hide();
          }

            $('#programDropdown').on('change', function() {
                var selectedprog = $(this).val();

                      $('.reminder-fields').hide();

                $.ajax({
                    type: 'POST',
                    url: "<?php echo site_url('account/ajaxprogrminder'); ?>",
                    data: { program: selectedprog },
                    dataType: 'json',
                    success: function(response) {
                        $('#remindercol').show();

                        var html = '<option value="">Select Reminder Name</option>';

                        $.each(response,function(index,item){
                            html += '<option value="' + item.reminder_rule_id + '" data-reminder-rule-id="' + item.reminder_rule_id + '">' + item.reminder_name + (item.program_title != null ? '(Step: ' + item.step_name + ')' : '') + '</option>';
                        });
                        $('[name="reminder_name"]').html(html);
                        // $('#result').html(response);
                    },

                });
            });

            $(document).on('change','#reminderDropdown', function() {
                var selectedReminder = $(this).val();

                // var selectedValue = $(this).find('option:selected');
                // alert(selectedReminder);
                $.ajax({
                    type: 'POST',
                    url: "<?php echo site_url('account/ajaxrminder'); ?>",
                    data: { reminder_rule_id: selectedReminder },
                    dataType: 'json',
                    success: function(response) {
                      $('.reminder-fields').show();
                        $('[name="reminder_desc"]').text(response.reminder_desc);
                        $('[name="program_title"]').text(response.program_title!=null?response.program_title:'-');
                        $('[name="step_name"]').text(response.step_name!=null?response.step_name:'-');
                        $('[name="step_id"]').text(response.step_id > 0?response.step_id:'-');
                        $('[name="days_to_send"]').val(response.days_to_send);
                        $('[name="send_frequency"]').val(response.send_frequency);
                        $('[name="stop_sending_days"]').val(response.stop_sending_days);
                        $('[name="reminder_email_subject"]').val(response.reminder_email_subject);

                        if(response.reminder_email_body.toLowerCase()=='include in digest'){
                            $('#showmsg').text("This reminder does not send an email and only appears n the case-manager's daily digest so you can only customize the day settings");
                            $('[name="reminder_email_body"]').closest('.form-group').hide();
                            $('#showmsg').show();
                        }else{
                            $('[name="reminder_email_body"]').val(response.reminder_email_body);
                            $('[name="reminder_email_body"]').closest('.form-group').show();
                            $('#showmsg').hide();

                            // For textarea, you should use .text() instead of .val()
                            $('#summernote').summernote('code', response.reminder_email_body);
                        }
                        // $('#result').html(response);
                    },
                    // error: function() {
                    //     alert('Error in AJAX request.');
                    // }
                    // error: function(jqXHR, textStatus, errorThrown) {
                    //     console.log(jqXHR.status); // Log the HTTP status code
                    //     console.log(textStatus);    // Log the error status
                    //     console.log(errorThrown);   // Log any error thrown by the server
                    //     alert('Error in AJAX request.');
                    // }

                });
            });
        });
    </script>

</body>
</html>
