/**
 * AdminLTE Demo Menu
 * ------------------
 * You should not use this file in production.
 * This file is for demo purposes only.
 */
(function ($, AdminLTE) {

  "use strict";

  /**
   * List of all the available skins
   *
   * @type Array
   */
  var my_skins = [
    "skin-blue",
    "skin-black",
    "skin-red",
    "skin-yellow",
    "skin-purple",
    "skin-green",
    "skin-blue-light",
    "skin-black-light",
    "skin-red-light",
    "skin-yellow-light",
    "skin-purple-light",
    "skin-green-light"
  ];

  //Create the new tab
  var tab_pane = $("<div />", {
    "id": "control-sidebar-theme-demo-options-tab",
    "class": "tab-pane active"
  });

  //Create the tab button
  var tab_button = $("<li />", {"class": "active"})
      .html("<a href='#control-sidebar-theme-demo-options-tab' data-toggle='tab'>"
      + "<i class='fa fa-wrench'></i>"
      + "</a>");

  //Add the tab button to the right sidebar tabs
  $("[href='#control-sidebar-home-tab']")
      .parent()
      .before(tab_button);

  //Create the menu
  var demo_settings = $("<div />");

  //Layout options
  demo_settings.append(
      "<h4 class='control-sidebar-heading'>"
      + "Layout Options"
      + "</h4>"
        //Fixed layout
      + "<div class='form-group'>"
      + "<label class='control-sidebar-subheading'>"
      + "<input type='checkbox' data-layout='fixed' class='pull-right'/> "
      + "Fixed layout"
      + "</label>"
      + "<p>Activate the fixed layout. You can't use fixed and boxed layouts together</p>"
      + "</div>"
        //Boxed layout
      + "<div class='form-group'>"
      + "<label class='control-sidebar-subheading'>"
      + "<input type='checkbox' data-layout='layout-boxed'class='pull-right'/> "
      + "Boxed Layout"
      + "</label>"
      + "<p>Activate the boxed layout</p>"
      + "</div>"
        //Sidebar Toggle
      + "<div class='form-group'>"
      + "<label class='control-sidebar-subheading'>"
      + "<input type='checkbox' data-layout='sidebar-collapse' class='pull-right'/> "
      + "Toggle Sidebar"
      + "</label>"
      + "<p>Toggle the left sidebar's state (open or collapse)</p>"
      + "</div>"
        //Sidebar mini expand on hover toggle
      + "<div class='form-group'>"
      + "<label class='control-sidebar-subheading'>"
      + "<input type='checkbox' data-enable='expandOnHover' class='pull-right'/> "
      + "Sidebar Expand on Hover"
      + "</label>"
      + "<p>Let the sidebar mini expand on hover</p>"
      + "</div>"
        //Control Sidebar Toggle
      + "<div class='form-group'>"
      + "<label class='control-sidebar-subheading'>"
      + "<input type='checkbox' data-controlsidebar='control-sidebar-open' class='pull-right'/> "
      + "Toggle Right Sidebar Slide"
      + "</label>"
      + "<p>Toggle between slide over content and push content effects</p>"
      + "</div>"
        //Control Sidebar Skin Toggle
      + "<div class='form-group'>"
      + "<label class='control-sidebar-subheading'>"
      + "<input type='checkbox' data-sidebarskin='toggle' class='pull-right'/> "
      + "Toggle Right Sidebar Skin"
      + "</label>"
      + "<p>Toggle between dark and light skins for the right sidebar</p>"
      + "</div>"
  );
  var skins_list = $("<ul />", {"class": 'list-unstyled clearfix'});

  //Dark sidebar skins
  var skin_blue =
      $("<li />", {style: "float:left; width: 33.33333%; padding: 5px;"})
          .append("<a href='javascript:void(0);' data-skin='skin-blue' style='display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)' class='clearfix full-opacity-hover'>"
          + "<div><span style='display:block; width: 20%; float: left; height: 7px; background: #367fa9;'></span><span class='bg-light-blue' style='display:block; width: 80%; float: left; height: 7px;'></span></div>"
          + "<div><span style='display:block; width: 20%; float: left; height: 20px; background: #222d32;'></span><span style='display:block; width: 80%; float: left; height: 20px; background: #f4f5f7;'></span></div>"
          + "</a>"
          + "<p class='text-center no-margin'>Blue</p>");
  skins_list.append(skin_blue);
  var skin_black =
      $("<li />", {style: "float:left; width: 33.33333%; padding: 5px;"})
          .append("<a href='javascript:void(0);' data-skin='skin-black' style='display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)' class='clearfix full-opacity-hover'>"
          + "<div style='box-shadow: 0 0 2px rgba(0,0,0,0.1)' class='clearfix'><span style='display:block; width: 20%; float: left; height: 7px; background: #fefefe;'></span><span style='display:block; width: 80%; float: left; height: 7px; background: #fefefe;'></span></div>"
          + "<div><span style='display:block; width: 20%; float: left; height: 20px; background: #222;'></span><span style='display:block; width: 80%; float: left; height: 20px; background: #f4f5f7;'></span></div>"
          + "</a>"
          + "<p class='text-center no-margin'>Black</p>");
  skins_list.append(skin_black);
  var skin_purple =
      $("<li />", {style: "float:left; width: 33.33333%; padding: 5px;"})
          .append("<a href='javascript:void(0);' data-skin='skin-purple' style='display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)' class='clearfix full-opacity-hover'>"
          + "<div><span style='display:block; width: 20%; float: left; height: 7px;' class='bg-purple-active'></span><span class='bg-purple' style='display:block; width: 80%; float: left; height: 7px;'></span></div>"
          + "<div><span style='display:block; width: 20%; float: left; height: 20px; background: #222d32;'></span><span style='display:block; width: 80%; float: left; height: 20px; background: #f4f5f7;'></span></div>"
          + "</a>"
          + "<p class='text-center no-margin'>Purple</p>");
  skins_list.append(skin_purple);
  var skin_green =
      $("<li />", {style: "float:left; width: 33.33333%; padding: 5px;"})
          .append("<a href='javascript:void(0);' data-skin='skin-green' style='display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)' class='clearfix full-opacity-hover'>"
          + "<div><span style='display:block; width: 20%; float: left; height: 7px;' class='bg-green-active'></span><span class='bg-green' style='display:block; width: 80%; float: left; height: 7px;'></span></div>"
          + "<div><span style='display:block; width: 20%; float: left; height: 20px; background: #222d32;'></span><span style='display:block; width: 80%; float: left; height: 20px; background: #f4f5f7;'></span></div>"
          + "</a>"
          + "<p class='text-center no-margin'>Green</p>");
  skins_list.append(skin_green);
  var skin_red =
      $("<li />", {style: "float:left; width: 33.33333%; padding: 5px;"})
          .append("<a href='javascript:void(0);' data-skin='skin-red' style='display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)' class='clearfix full-opacity-hover'>"
          + "<div><span style='display:block; width: 20%; float: left; height: 7px;' class='bg-red-active'></span><span class='bg-red' style='display:block; width: 80%; float: left; height: 7px;'></span></div>"
          + "<div><span style='display:block; width: 20%; float: left; height: 20px; background: #222d32;'></span><span style='display:block; width: 80%; float: left; height: 20px; background: #f4f5f7;'></span></div>"
          + "</a>"
          + "<p class='text-center no-margin'>Red</p>");
  skins_list.append(skin_red);
  var skin_yellow =
      $("<li />", {style: "float:left; width: 33.33333%; padding: 5px;"})
          .append("<a href='javascript:void(0);' data-skin='skin-yellow' style='display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)' class='clearfix full-opacity-hover'>"
          + "<div><span style='display:block; width: 20%; float: left; height: 7px;' class='bg-yellow-active'></span><span class='bg-yellow' style='display:block; width: 80%; float: left; height: 7px;'></span></div>"
          + "<div><span style='display:block; width: 20%; float: left; height: 20px; background: #222d32;'></span><span style='display:block; width: 80%; float: left; height: 20px; background: #f4f5f7;'></span></div>"
          + "</a>"
          + "<p class='text-center no-margin'>Yellow</p>");
  skins_list.append(skin_yellow);

  //Light sidebar skins
  var skin_blue_light =
      $("<li />", {style: "float:left; width: 33.33333%; padding: 5px;"})
          .append("<a href='javascript:void(0);' data-skin='skin-blue-light' style='display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)' class='clearfix full-opacity-hover'>"
          + "<div><span style='display:block; width: 20%; float: left; height: 7px; background: #367fa9;'></span><span class='bg-light-blue' style='display:block; width: 80%; float: left; height: 7px;'></span></div>"
          + "<div><span style='display:block; width: 20%; float: left; height: 20px; background: #f9fafc;'></span><span style='display:block; width: 80%; float: left; height: 20px; background: #f4f5f7;'></span></div>"
          + "</a>"
          + "<p class='text-center no-margin' style='font-size: 12px'>Blue Light</p>");
  skins_list.append(skin_blue_light);
  var skin_black_light =
      $("<li />", {style: "float:left; width: 33.33333%; padding: 5px;"})
          .append("<a href='javascript:void(0);' data-skin='skin-black-light' style='display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)' class='clearfix full-opacity-hover'>"
          + "<div style='box-shadow: 0 0 2px rgba(0,0,0,0.1)' class='clearfix'><span style='display:block; width: 20%; float: left; height: 7px; background: #fefefe;'></span><span style='display:block; width: 80%; float: left; height: 7px; background: #fefefe;'></span></div>"
          + "<div><span style='display:block; width: 20%; float: left; height: 20px; background: #f9fafc;'></span><span style='display:block; width: 80%; float: left; height: 20px; background: #f4f5f7;'></span></div>"
          + "</a>"
          + "<p class='text-center no-margin' style='font-size: 12px'>Black Light</p>");
  skins_list.append(skin_black_light);
  var skin_purple_light =
      $("<li />", {style: "float:left; width: 33.33333%; padding: 5px;"})
          .append("<a href='javascript:void(0);' data-skin='skin-purple-light' style='display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)' class='clearfix full-opacity-hover'>"
          + "<div><span style='display:block; width: 20%; float: left; height: 7px;' class='bg-purple-active'></span><span class='bg-purple' style='display:block; width: 80%; float: left; height: 7px;'></span></div>"
          + "<div><span style='display:block; width: 20%; float: left; height: 20px; background: #f9fafc;'></span><span style='display:block; width: 80%; float: left; height: 20px; background: #f4f5f7;'></span></div>"
          + "</a>"
          + "<p class='text-center no-margin' style='font-size: 12px'>Purple Light</p>");
  skins_list.append(skin_purple_light);
  var skin_green_light =
      $("<li />", {style: "float:left; width: 33.33333%; padding: 5px;"})
          .append("<a href='javascript:void(0);' data-skin='skin-green-light' style='display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)' class='clearfix full-opacity-hover'>"
          + "<div><span style='display:block; width: 20%; float: left; height: 7px;' class='bg-green-active'></span><span class='bg-green' style='display:block; width: 80%; float: left; height: 7px;'></span></div>"
          + "<div><span style='display:block; width: 20%; float: left; height: 20px; background: #f9fafc;'></span><span style='display:block; width: 80%; float: left; height: 20px; background: #f4f5f7;'></span></div>"
          + "</a>"
          + "<p class='text-center no-margin' style='font-size: 12px'>Green Light</p>");
  skins_list.append(skin_green_light);
  var skin_red_light =
      $("<li />", {style: "float:left; width: 33.33333%; padding: 5px;"})
          .append("<a href='javascript:void(0);' data-skin='skin-red-light' style='display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)' class='clearfix full-opacity-hover'>"
          + "<div><span style='display:block; width: 20%; float: left; height: 7px;' class='bg-red-active'></span><span class='bg-red' style='display:block; width: 80%; float: left; height: 7px;'></span></div>"
          + "<div><span style='display:block; width: 20%; float: left; height: 20px; background: #f9fafc;'></span><span style='display:block; width: 80%; float: left; height: 20px; background: #f4f5f7;'></span></div>"
          + "</a>"
          + "<p class='text-center no-margin' style='font-size: 12px'>Red Light</p>");
  skins_list.append(skin_red_light);
  var skin_yellow_light =
      $("<li />", {style: "float:left; width: 33.33333%; padding: 5px;"})
          .append("<a href='javascript:void(0);' data-skin='skin-yellow-light' style='display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)' class='clearfix full-opacity-hover'>"
          + "<div><span style='display:block; width: 20%; float: left; height: 7px;' class='bg-yellow-active'></span><span class='bg-yellow' style='display:block; width: 80%; float: left; height: 7px;'></span></div>"
          + "<div><span style='display:block; width: 20%; float: left; height: 20px; background: #f9fafc;'></span><span style='display:block; width: 80%; float: left; height: 20px; background: #f4f5f7;'></span></div>"
          + "</a>"
          + "<p class='text-center no-margin' style='font-size: 12px;'>Yellow Light</p>");
  skins_list.append(skin_yellow_light);

  demo_settings.append("<h4 class='control-sidebar-heading'>Skins</h4>");
  demo_settings.append(skins_list);

  tab_pane.append(demo_settings);
  $("#control-sidebar-home-tab").after(tab_pane);

  setup();

  /**
   * Toggles layout classes
   *
   * @param String cls the layout class to toggle
   * @returns void
   */
  function change_layout(cls) {
    $("body").toggleClass(cls);
    AdminLTE.layout.fixSidebar();
    //Fix the problem with right sidebar and layout boxed
    if (cls == "layout-boxed")
      AdminLTE.controlSidebar._fix($(".control-sidebar-bg"));
    if ($('body').hasClass('fixed') && cls == 'fixed') {
      AdminLTE.pushMenu.expandOnHover();
      AdminLTE.layout.activate();
    }
    AdminLTE.controlSidebar._fix($(".control-sidebar-bg"));
    AdminLTE.controlSidebar._fix($(".control-sidebar"));
  }

  /**
   * Replaces the old skin with the new skin
   * @param String cls the new skin class
   * @returns Boolean false to prevent link's default action
   */
  function change_skin(cls) {
    $.each(my_skins, function (i) {
      $("body").removeClass(my_skins[i]);
    });

    $("body").addClass(cls);
    store('skin', cls);
    return false;
  }

  /**
   * Store a new settings in the browser
   *
   * @param String name Name of the setting
   * @param String val Value of the setting
   * @returns void
   */
  function store(name, val) {
    if (typeof (Storage) !== "undefined") {
      localStorage.setItem(name, val);
    } else {
      window.alert('Please use a modern browser to properly view this template!');
    }
  }

  /**
   * Get a prestored setting
   *
   * @param String name Name of of the setting
   * @returns String The value of the setting | null
   */
  function get(name) {
    if (typeof (Storage) !== "undefined") {
      return localStorage.getItem(name);
    } else {
      window.alert('Please use a modern browser to properly view this template!');
    }
  }

  /**
   * Retrieve default settings and apply them to the template
   *
   * @returns void
   */
  function setup() {
    var tmp = get('skin');
    if (tmp && $.inArray(tmp, my_skins))
      change_skin(tmp);

    //Add the change skin listener
    $("[data-skin]").on('click', function (e) {
      if($(this).hasClass('knob'))
        return;
      e.preventDefault();
      change_skin($(this).data('skin'));
    });

    //Add the layout manager
    $("[data-layout]").on('click', function () {
      change_layout($(this).data('layout'));
    });

    $("[data-controlsidebar]").on('click', function () {
      change_layout($(this).data('controlsidebar'));
      var slide = !AdminLTE.options.controlSidebarOptions.slide;
      AdminLTE.options.controlSidebarOptions.slide = slide;
      if (!slide)
        $('.control-sidebar').removeClass('control-sidebar-open');
    });

    $("[data-sidebarskin='toggle']").on('click', function () {
      var sidebar = $(".control-sidebar");
      if (sidebar.hasClass("control-sidebar-dark")) {
        sidebar.removeClass("control-sidebar-dark")
        sidebar.addClass("control-sidebar-light")
      } else {
        sidebar.removeClass("control-sidebar-light")
        sidebar.addClass("control-sidebar-dark")
      }
    });

    $("[data-enable='expandOnHover']").on('click', function () {
      $(this).attr('disabled', true);
      AdminLTE.pushMenu.expandOnHover();
      if (!$('body').hasClass('sidebar-collapse'))
        $("[data-layout='sidebar-collapse']").click();
    });

    // Reset options
    if ($('body').hasClass('fixed')) {
      $("[data-layout='fixed']").attr('checked', 'checked');
    }
    if ($('body').hasClass('layout-boxed')) {
      $("[data-layout='layout-boxed']").attr('checked', 'checked');
    }
    if ($('body').hasClass('sidebar-collapse')) {
      $("[data-layout='sidebar-collapse']").attr('checked', 'checked');
    }

  }
})(jQuery, $.AdminLTE);




// Popup window code
function newPopup(url) {
	popupWindow = window.open(
		url,'popUpWindow','height=600,width=600,left=10,top=10,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no,status=yes');
	popupWindow.focus();
return false;
}

function toggle_check_box(source)
{
  checkboxes = document.getElementsByClassName('chkbx_cls');
  for (i = 0; i < checkboxes.length; i++)	{	checkboxes[i].checked = source.checked;	}
}


function toggle_check_box_byclass(source, chkbx_cls)
{
  checkboxes = document.getElementsByClassName(chkbx_cls);
  for (i = 0; i < checkboxes.length; i++)	{	checkboxes[i].checked = source.checked;	}
  
  change_prong_2_3_0("prong_2_3_0");
}



//	Add in IFrame
function add_in_frame(frame_id,frame_url)
{
	document.getElementById(frame_id).src = frame_url;
}



//	Function Apply Coupon Code
function apply_coupon_code(apply_coupon_code_url, myform, msg_dip_id)
{
	$('.theme_overlay').show();
	
	var oForm = document.forms[myform];	
	oForm["Submit_"].innerHTML = 'Please Wait...';	
	var coupon_code = oForm["coupon_code"].value;
	$.post(apply_coupon_code_url,
	{
	  coupon_code: coupon_code
	},
	function(data,status){
	
	$('.theme_overlay').hide();
	oForm["Submit_"].innerHTML = 'Apply';
	
	//alert("Data: " + data + "\nStatus: " + status);
	var obj = JSON.parse(data)
	if(obj.status == "Success")
	{
		//swal('Congratulations!!',obj.message,'success');
		location.reload();
		$("#"+msg_dip_id).html(obj.message);
	}	else	{	$("#"+msg_dip_id).html(obj.message);	}
	
	});
	
	
	return false;
}


//	Function View SnapShot Bidy
function view_nslds_snapshot_body(snapshot_url, msg_disp_id)
{
	$("#"+msg_disp_id).html('Please wait...');
	$.post(snapshot_url,
	{
	  client_id: 'client_id'
	},
	function(data,status){	
	//alert("Data: " + data + "\nStatus: " + status);
	$("#"+msg_disp_id).html(data);
	});
	
	return false;
}




//	Function Run Current Analysis Scenario
function run_current_analysis_scenario(url, myform, client_id, msg_disp_id)
{
	var oForm = document.forms[myform];	
	var scenario_selected = oForm["scenario_selected"].value;
	var family_size = oForm["family_size"].value;
	var client_agi = oForm["client_agi"].value;
	var spouse_agi = oForm["spouse_agi"].value;
	var client_monthly = oForm["client_monthly"].value;
	var spouse_monthly = oForm["spouse_monthly"].value;

	$("#"+msg_disp_id).html('Please wait...');
	$.post(url,
	{
	  client_id: client_id,
	  scenario_selected: scenario_selected,
	  family_size: family_size,
	  client_agi: client_agi,
	  spouse_agi: spouse_agi,
	  client_monthly: client_monthly,
	  spouse_monthly: spouse_monthly
	},
	function(data,status){	
	//alert("Data: " + data + "\nStatus: " + status);
	$("#"+msg_disp_id).html(data);
	
	});
	
	return false;
}


//	Function Run Current Analysis Scenario Latest
function run_current_analysis_scenario_new(url, myform, client_id, k, msg_disp_id)
{
	var oForm = document.forms[myform];	
	var scenario_selected = oForm["scenario_selected__["+k+"]"].value;
	/*var family_size = oForm["family_size["+k+"]"].value;
	var client_agi = oForm["client_agi["+k+"]"].value;
	var client_monthly = oForm["client_monthly["+k+"]"].value;
	var spouse_agi = oForm["spouse_agi["+k+"]"].value;
	var spouse_monthly = oForm["spouse_monthly["+k+"]"].value;
	
	var marital_status = oForm["marital_status["+k+"]"].value;
	var file_joint_or_separate = oForm["file_joint_or_separate["+k+"]"].value;*/
	
	var family_size = oForm["family_size"].value;
	var client_agi = oForm["client_agi"].value;
	var client_monthly = oForm["client_monthly"].value;
	var spouse_agi = oForm["spouse_agi"].value;
	var spouse_monthly = oForm["spouse_monthly"].value;
	
	var marital_status = oForm["marital_status"].value;
	var file_joint_or_separate = oForm["file_joint_or_separate"].value;
	
	$("#"+msg_disp_id).html('Please wait...');
	$.post(url,
	{
	  client_id: client_id,
	  scenario_selected: scenario_selected,
	  family_size: family_size,
	  client_agi: client_agi,
	  spouse_agi: spouse_agi,
	  client_monthly: client_monthly,
	  spouse_monthly: spouse_monthly,
	  marital_status: marital_status,
	  file_joint_or_separate: file_joint_or_separate
	},
	function(data,status){	
	//alert("Data: " + data + "\nStatus: " + status);
	
	var result=data.split('<li>');
	var section_5_sppa = result[1];
	var section_5_sppa = section_5_sppa.replace('</li>','');
	change_section_5_sppa_text(section_5_sppa);
	
	
	var data = data.replace('<li>', '<li class="height_50">');
	var data = data.replace('<li>', '<li class="height_50">');
	var data = data.replace('<li>', '<li class="height_50">');
	var data = data.replace('<li>', '<li class="height_50">');
	var data = data.replace('<li>', '<li class="height_50">');
	var data = data.replace('<li>', '<li class="height_50">');
	var data = data.replace('<li>', '<li class="height_50">');
	var data = data.replace('<li>', '<li class="height_50">');
	$("#"+msg_disp_id).html(data);
	
	});
	
	return false;
}




//	Function View Intake Form Body
function view_intake_form_body(url, msg_disp_id, title_disp_id, title, aid)
{
	$("#"+msg_disp_id).html('Please wait...');
	$.post(url,
	{
	  aid:aid,
	  title:title
	},
	function(data,status){	
	//alert("Data: " + data + "\nStatus: " + status);
	$("#"+msg_disp_id).html(data);
	});
	
	return false;
}


//	Function View Intake Form Body
function approve_intake_form_body(url, aid, title)
{
	$("#"+aid).removeClass("btn-warning");
	$("#"+aid).addClass("btn-primary");
	
	$('#myModal_intake_form').modal('toggle');
	
	$.post(url,
	{
	  aid:aid
	},
	function(data,status){	
	//alert("Data: " + data + "\nStatus: " + status);
	});
	
	return false;
}


function hidemodel(divid) {
	$('#'+divid).modal('toggle');
	$(".modal-backdrop").removeClass("in");
}



//	Change Family Size
function change_cachangefjs()
{
	var fs = parseInt($('#family_size').val());	
	var ms = $('#ca_marital_status').val();	
  var intake = $('#intake_idtest').val(); 

  var q=0;var a=0;var a2=0;

  if(intake!=undefined&&intake!=1){
    q=96;
    a=74;
    a2=62;
  }
	
	if(ms == (parseInt(a)+parseInt("15")))
	{
		if(fs<2) {	$('#family_size').val((2));	}
	}
	else
	{
		if(fs<1) {	$('#family_size').val((1));	}
	}
}


//	Change camsfjs
function change_camsfjs()
{
	var msp = parseInt($('#marital_status_primary').val());
  var fsjp = parseInt($('#file_joint_or_separate_primary').val());
	var fs = parseInt($('#family_size').val());	
	var ms = $('#ca_marital_status').val();
	var fjs = $('#ca_file_joint_or_separate').val();
  var intake = $('#intake_idtest').val(); 

  var q=0;var a=0;var a2=0;

  if(intake!=undefined&&intake!=1){
    q=96;
    a=74;
    a2=62;
  }
	
	$('#marital_status_primary').val(ms);
  $('#file_joint_or_separate_primary').val(fjs);
	
	var spouse_agi = parseInt($('#spouse_agi').val());
	var spouse_monthly = parseInt($('#spouse_monthly').val());
	if(spouse_agi>0) {	var spouse_agi = $('#spouse_agi').val();	$('#id_spouse_agi').val(spouse_agi);	}
	if(spouse_monthly>0) {	var spouse_monthly = $('#spouse_monthly').val();	$('#id_spouse_monthly').val(spouse_monthly);	}
	
    $('#spouse_agi').val("0");
    $('#spouse_monthly').val("0");
	
	if(ms == (parseInt(a)+parseInt("15")))
	{
		if(fs<1) {	fs = 1;	}

    if(msp != (parseInt(a)+parseInt("15")) && fjs == (parseInt(a)+parseInt("18")))
      $('#family_size').val((fs+1));
		
		$('.ca_fjs').show();
		$('.ca_sps_incm').show();
		
		
    if(fjs == (parseInt(a)+parseInt("18"))){
      var spouse_agi = $('#id_spouse_agi').val();
      $('#spouse_agi').val(spouse_agi);
      var spouse_monthly = $('#id_spouse_monthly').val();
      $('#spouse_monthly').val(spouse_monthly);
    }
		
		
		//"18"==fjs?$(".ca_sps_incm").show():$(".ca_sps_incm").hide();		
	} else if(ms == (parseInt(a2)+parseInt("72")))
	{
		if(msp == (parseInt(a)+parseInt("15"))) { fs = fs-1; }
		if(fs<=0) {	fs = 1;	}
		$('#family_size').val(fs);
		
		/*$('.ca_fjs').show();
		$('.ca_sps_incm').show();
		$("#ca_file_joint_or_separate").val("18").change();*/
		
		$('.ca_fjs').hide();
		$('.ca_sps_incm').hide();
		$('.ca_sps_incm input').val("");
		$("#ca_file_joint_or_separate").val((parseInt(a)+parseInt("19")));
		
		
	} else if(ms == (parseInt(a2)+parseInt("73")))
	{
    if(msp == (parseInt(a)+parseInt("15"))) { fs = fs-1; }
    if(fs<=0) { fs = 1; }
    $('#family_size').val(fs);

		$('.ca_fjs').hide();
		$('.ca_sps_incm').hide();
		$('.ca_sps_incm input').val("");
		
		$("#ca_file_joint_or_separate").val((parseInt(a)+parseInt("19")));
	}
	else
	{
		if(msp == (parseInt(a)+parseInt("15"))) { fs = fs-1; }
		if(fs<=0) {	fs = 1;	}
		
		$('#family_size').val(fs);
		
		$('.ca_fjs').hide();
		$('.ca_sps_incm').hide();
		$('.ca_sps_incm input').val("");
		$("#ca_file_joint_or_separate").val((parseInt(a)+parseInt("19")));
	}

  if(fjs==(parseInt(a)+parseInt("19")) && fsjp==(parseInt(a)+parseInt("18")) && ms==(parseInt(a)+parseInt("15"))){
    var fs=$('#family_size').val()-1;
    if(fs<=0) { fs = 1; }
    $('#family_size').val(fs);
    $('#spouse_agi').val("0");
    $('#spouse_monthly').val("0");
  }
  if(fjs==(parseInt(a)+parseInt("18")) && fsjp==(parseInt(a)+parseInt("19")) && ms==(parseInt(a)+parseInt("15")))
    $('#family_size').val(parseInt($('#family_size').val())+parseInt(1));
}


//	Change Client Analysis PAYMENT SCENARIOS
function recalculate_ca_ps(url)
{
	//change_camsfjs();
	
	$('#car_tbl').html("<br />&nbsp; Loading...<br /><br />");
	
	setTimeout(function() {
   
	$.post(url, $("#current_analysis_form").serialize(), function(data) {
        $('#car_tbl').html(data);
		
		var section_5_sppa = $('#scnerio_plan_0').html();
		$('#section_5_sppa').html(section_5_sppa);
		
    });
	
	}, 2000);
}


//	Change Client Decision Received	 - Do you want to continue?
function change_cps6(val)
{
	var cps6_program_id = $("#cps6_program_id");
	if(val == "Select Program and add the Client")
	{
		$('#div_cps6_program_id').show();
		cps6_program_id.attr("required","required");
	}
	else
	{
		$('#div_cps6_program_id').hide();
		cps6_program_id.removeAttr("required");
	}
}


//	Print Selected Div
function printDiv(divName) {
     var printContents = document.getElementById(divName).innerHTML;
     var originalContents = document.body.innerHTML;

     document.body.innerHTML = printContents;

     window.print();

     document.body.innerHTML = originalContents;
}