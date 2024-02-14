<?php	defined('BASEPATH') OR exit('No direct script access allowed');	?>
<?php
error_reporting(0);
@extract($_POST);

$sg_1 = $this->uri->segment(1);
$sg_3 = $this->uri->segment(3);
$client_id = $this->uri->segment(4);

//	Get Client Full Details
$user = $client_data['client'];
@extract($client_data['client']);
$icsr = $client_data['intake_client_status'];
$cmr = $client_data['case_manager'];
$cmpR = $client_data['users_company'];

$company_id = $user['company_id'];
$intake_id = 1;

$initial_intake_status = "Pending";
if(isset($icsr['status'])) {	if($icsr['status'] == "Complete") {	$initial_intake_status = "Complete"; }	}

$fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);


#######	Federal Student Loan Debt	#####
$principal = $interest = $total_loan = 0;
$q = $this->db->query("SELECT sum(loan_outstanding_principal_balance) as prncpl, sum(loan_outstanding_interest_balance) as intrst FROM nslds_loans where client_id='".$client_id."'");
if($q->num_rows()>0)
{
	$nlnR = $q->row_array();
	
	$principal = $nlnR['prncpl'];
	$interest = $nlnR['intrst'];
	$total_loan = ($principal + $interest);
}

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Client Analysis Report prepared for <?php echo $user['name']; ?> <?php echo $user['lname']; ?></title>
</head>
<body style="font-family:Arial, Helvetica, sans-serif;">
<div>
<div style="padding:0px 0px;">
<div style="height:60px; border-bottom:1px solid #CCCCCC;">
<?php	if(file_exists($client_data['users_company']['logo'])) {	

$path= $client_data['users_company']['logo'];
$type = pathinfo($path, PATHINFO_EXTENSION);
$image = file_get_contents($path);
$image = 'data:image/' . $type . ';base64,' . base64_encode($image);

echo '<img src="'.$image.'" alt="'.$client_data['users_company']['name'].'" style="max-height:50px;" />';	} else { if(trim($client_data['company']['company_name'])!="") { echo '<h3 style="font-size:30px;"><strong>'.$client_data['company']['company_name'].'</strong></h3>'; } else { echo '<h3 style="font-size:30px;"><strong>'.$client_data['users_company']['name'].'</strong></h3>'; } }
?>
</div>

<p style="height:25px; margin-bottom:0px;"><strong>Client Analysis Report prepared for <?php echo $user['name']; ?> <?php echo $user['lname']; ?></strong></p>
<table class="table table-bordered" cellpadding="5" cellspacing="0" border="1">
<tr>	<th width="120">Total Loan Balance</th>	<th width="120">Total Principal</th>	<th>Total Interest</th>	</tr>
<tr>
	<td><?php	echo $fmt->formatCurrency(round($total_loan), "USD")	?></td>
    <td><?php	echo $fmt->formatCurrency(round($principal), "USD")	?></td>
    <td><?php	echo $fmt->formatCurrency(round($interest), "USD")	?></td>
</tr>
</table>

<?php	if(trim($car['scenario_selected'])) {	?>
<h4 style="height:25px; margin-bottom:0px;"><strong>Payment Plans:</strong></h4>
<table class="table table-bordered" cellpadding="5" cellspacing="0" border="1"><?php echo $data_print_text; ?></table>

<div style="height:30px; padding:10px 0px; font-size:12px;"><strong>Disclaimer:</strong> These numbers are estimates. Only the servicer can give exact numbers.</div>

<p style="height:90px;"><strong>Consolidation:</strong> Consolidation is another word for refinance but is specifically for Federal loans only. It is the process of paying off some or all Federal loans with a new Federal loan from the Department of Education. This may be required to gain eligibility to payment plans and/or to gain eligibility to forgiveness opportunities. It is also a method to get loans out of default. The process of consolidating takes between 30 and 90 days.</p>
<p style="height:50px;">The above summary was presented to you during your analysis. This record is for your files. Please feel free to reach out with any questions or concerns.</p>

<?php	}	else	{	?>
<div style="height:50px; margin-top:25px;">We cannot calculate the payments in this scenario because we do not have income information.</div>
<?php	}	?>
</div>
</div>
</body>
</html>