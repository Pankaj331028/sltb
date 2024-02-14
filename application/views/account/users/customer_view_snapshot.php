<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php

error_reporting(0);
@extract($_POST);
$fmt = new NumberFormatter('en_US', NumberFormatter::CURRENCY);

$user = $this->default_model->get_arrby_tbl('users', '*', "role='Customer' and id='" . $this->uri->segment(3) . "'", '1');
$user = $user["0"];
@extract($user);
$client_id = $id;

$q = 6;

if ($this->db->query('select * from intake_client_status where client_id=' . $client_id . ' and intake_id=4')->num_rows() > 0) {
	$q = 102;
}
$ansR = $this->default_model->get_arrby_tbl_single('intake_file_result', '*', "client_id='" . $client_id . "' and intake_question_id='" . $q . "'", '1');
$intake_file_result_id = $ansR['intake_file_id'];

$q = $this->db->query("SELECT * FROM nslds_loans where intake_file_result_id='$intake_file_result_id' and client_id='$client_id' order by loan_date asc");
$nr = $q->num_rows();
if ($nr > 0) {

	$principal = $interest = $total_loan = 0;
	$q2 = $this->db->query("SELECT sum(loan_outstanding_principal_balance) as prncpl, sum(loan_outstanding_interest_balance) as intrst FROM nslds_loans where client_id='" . $client_id . "'");
	$nlnR = $q2->row_array();
	$principal = $nlnR['prncpl'];
	$interest = $nlnR['intrst'];
	$total_loan = ($principal + $interest);

	?>
<div style="padding:15px;">
<table style="text-align:center; width:100%;">
<tr>	<td colspan="2">
<p style="font-size:18px;  text-align:center;"><strong>Total Federal Student Loan Debt </strong><br /><?php echo $fmt->formatCurrency(($total_loan), "USD"); ?></p>
</td>	</tr>

<tr>	<td>
<p style="font-size:18px;  text-align:center;"><strong>Principal </strong><br /><?php echo $fmt->formatCurrency(($principal), "USD"); ?></p>
</td>

<td>
<p style="font-size:18px;  text-align:center;"><strong>Interest </strong><br /><?php echo $fmt->formatCurrency(($interest), "USD"); ?></p>
</td>

</tr>

</table>
<div>
<p><strong>Chronologic History of Loans:</strong></p>
<?php
foreach ($q->result_array() as $row) {
		?>
<div style="border:1px solid #ede7e7; padding:15px; background:#efefef42; margin-bottom:15px;">
<?php	if (isset($row['loan_date'])) {?><p style="margin-bottom:2px;"><strong>Date: </strong> <?php echo $row['loan_date']; ?></p><?php	}?>
<?php	if (isset($row['loan_attending_school'])) {?><p style="margin-bottom:2px;"><strong>School: </strong> <?php echo $row['loan_attending_school']; ?></p><?php	}?>
<?php	if (isset($row['loan_type'])) {?><p style="margin-bottom:2px;"><strong>Loan Type: </strong> <?php echo $row['loan_type']; ?></p><?php	}?>
<?php	if (isset($row['loan_dispersed_amount'])) {?><p style="margin-bottom:2px;"><strong>Origination amount: </strong> <?php echo $fmt->formatCurrency(($row['loan_dispersed_amount']), "USD"); ?></p><?php	}?>
<?php	if (isset($row['loan_outstanding_principal_balance'])) {?><p style="margin-bottom:2px;"><strong>Current Principal: </strong> <?php echo $fmt->formatCurrency(($row['loan_outstanding_principal_balance']), "USD"); ?></p><?php	}?>
<?php	if (isset($row['loan_outstanding_interest_balance'])) {?><p style="margin-bottom:2px;"><strong>Current Interest: </strong> <?php echo $fmt->formatCurrency(($row['loan_outstanding_interest_balance']), "USD"); ?></p><?php	}?>

<?php	if (isset($row['loan_interest_rate_type_description'])) {?><p style="margin-bottom:2px;"><strong>Loan Interest Rate Type Description: </strong> <?php echo $row['loan_interest_rate_type_description']; ?></p><?php	}?>

<?php	if (isset($row['loan_interest_rate'])) {?><p style="margin-bottom:2px;"><strong>Loan Interest Rate: </strong> <?php echo $row['loan_interest_rate'] . "%"; ?></p><?php	}?>

<?php	if (isset($row['loan_status'])) {?><p style="margin-bottom:0px;"><strong>Last status and date: </strong> <?php echo $row['loan_status_description']; ?> <?php	if (isset($row['loan_status_effective_date'])) {echo ", " . $row['loan_status_effective_date'];}?></p><?php	}?>


<?php
if (isset($row['loan_contact_type'])) {
			$caddress = "";

			if (trim($row['loan_contact_street_address_1']) != "") {$caddress = $row['loan_contact_street_address_1'];}
			if (trim($row['loan_contact_street_address_2']) != "") {$caddress .= " " . $row['loan_contact_street_address_2'];}
			if (trim($row['loan_contact_city']) != "") {$caddress .= ", " . $row['loan_contact_city'];}
			if (trim($row['loan_contact_state']) != "") {$caddress .= ", " . $row['loan_contact_state'];}
			if (trim($row['loan_contact_zip_code']) != "") {$caddress .= " - " . $row['loan_contact_zip_code'];}

			echo '<p style="margin-bottom:2px;"><strong>' . $row['loan_contact_type'] . ': </strong> ' . $row['loan_contact_name'] . ' &nbsp; - &nbsp; ' . $caddress . '</p>';
		}
		?>
<?php	if (isset($row['current_lender'])) {?><p style="margin-bottom:2px;"><strong>Current Lender: </strong> <?php echo $row['current_lender']; ?></p><?php	}?>
<?php	if (isset($row['current_guarantee_agency'])) {?><p style="margin-bottom:2px;"><strong>Current Guarantee Agency: </strong> <?php echo $row['current_guarantee_agency']; ?></p><?php	}?>
</div>
<?php	}?>
</div>
</div>
<?php	} else {echo '<p style="color:red;">No Data Found.</p>';}?>