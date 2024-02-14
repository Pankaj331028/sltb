<?php	defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php
$form_data = json_decode($car['form_data'], true);
if (is_array($form_data)) {@extract($form_data);}

if (!isset($car['multiple_loans'])) {
	$multiple_loans = [
		'loan_name' => [],
		'loan_type' => [],
		'loan_amount' => [],
		'monthly_payment' => [],
		'date_of_payoff' => [],
		'date_of_default' => [],
		'school_attended' => [],
		'degree_pursued' => [],
		'specialization' => [],
		'date_school_completed' => [],
		'type_of_degree' => [],
		'date_studies_ceased' => [],
	];} else { $multiple_loans = $car['multiple_loans'];}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Data Confirmation for Attestation</title>
	<style>
			@font-face {
			font-family:times-new-roman;
			src: url(/assets/font/times-new-roman.ttf);
			}
			.container {
				max-width: 1200px;
				margin: auto;
			}
			body{
				font-family: times-new-roman;
				font-size: 15px;
/*				line-height: 24px;*/
				counter-reset: page <?=$total?>;
			}
			table {
				width: 100%;
			}
			table thead {
				padding: 20px 0px;
/*				display: block;*/
			}
			table tr,div {
				page-break-inside: auto;
				break-inside: auto;
				line-height: 18px;
			}
			table td {
/*				padding: 10px 0px;*/
				display: table-cell;
				font-family: times-new-roman;
				font-size: 15px;
				line-height: 24px;
				page-break-inside: auto;
				break-inside: auto;
			}
			.text-center {
				text-align: center;
			}
			.w-100 {
				width: 100%;
				float: left;
			}
			.w-50 {
				width: 50%;
				float: left;
			}
			.w-33 {
				width: 33.33%;
				float: left;
			}
			input {
				border: 0px;
				border-bottom: 1px solid #000;
				font-family: times-new-roman;
				font-size: 15px;
				line-height: 15px;
			}
			label{
				/*   display: grid;*/
				margin: 10px 0px;
				font-family: times-new-roman;
				font-size: 15px;
				line-height: 24px;
			}
			li {
				margin: 0px 0 30px;
				font-family: times-new-roman;
				font-size: 15px;
				line-height: 15px;
			}
			p{
				font-family: times-new-roman;
				font-size: 15px;
				line-height: 24px;
			}

			h3 {
				font-weight: normal;
/*				text-decoration: underline;*/
				font-family: times-new-roman;
				font-size: 15px;
				line-height: 24px;
			}
			.underline{
				text-decoration: underline;
			}

			.multiple_loans{
				width: 100%;
				max-width: 1100px;
			}

			.multiple_loans input{
				width: 100px;
			}

			span{
				font-weight: bold;
				min-width: 50px !important;
				border-bottom: 1px solid;
			}
			@page {
				margin: 1in;
				position: relative;
			}
			#header {
				position: fixed; left: 0px; top: -50px; right: 0px;
			}

			.footer .page:after {
				content: counter(page);
			}
			li,div,tr,td{
				clear: both;
			}
			table.nostyle{
				border-collapse: collapse;
			}
			table.nostyle td, table.nostyle th {
				border: 1px solid #000;
				padding: 10px 1px;
			}
			.floatLeft {
				float: left;
			}

			.floatRight {
				float: right;
			}

			.floatClear {
				clear: both;
			}

			.w-50 {
				width: 50% !important;
			}
			em{
				font-style: inherit;
				line-height: 24px;
			}
			tr{
				page-break-after: auto;
			}
			table{
				page-break-inside: auto;
			}
			.footer {
			  	position: fixed;
			  	left: 0;
			    right: 0;
			    font-size: 0.9em;
			  	bottom: 0;
			  	background-color: white;
			  	margin: 0;
			  	padding: 0;
			}
			#content{
				padding-bottom: 100px !important;
			}
		</style>
</head>
<body style="margin:0">
  	<div id="header">
  		[Updated January 2023]
  	</div>
   	<div id="footer2" class="footer">
		<p class="text-center" style="margin:0;padding: 0;">- <span class="page"><?php echo $PAGE_NUM ?></span> -</p>
	</div>
	<div id="content">
		<table class="table table-bordered table-striped nostyle" style="border-collapse: collapse;margin-top: 20px;">
			<thead>
				<tr>
					<th style="padding:0 3px">Loan Name/Identifier</th>
					<th style="padding:0 3px">Loan Type</th>
					<th style="padding:0 3px">#5 Loan Amount</th>
					<th style="padding:0 3px">#6 Monthly Payment</th>
					<th style="padding:0 3px;width:55px;">#6 Date Of Payoff</th>
					<th style="padding:0 3px;width:55px;">#6 Date Of Default</th>
					<th style="padding:0 3px">#7 School Attended</th>
					<th style="padding:0 3px">#7 Degree Pursued</th>
					<th style="padding:0 3px">#7 Specialization</th>
					<th style="padding:0 3px;width:55px;">#8 Date School Completed</th>
					<th style="padding:0 3px">#8 Type Of Degree</th>
					<th style="padding:0 3px;width:55px;">#8 Date Left School w/o Degree</th>
				</tr>
			</thead>
			<tbody style="page-break-inside:auto;">
				<?php
foreach ($multiple_loans['loan_name'] as $key => $value) {
	?>
					<tr>
						<td style="padding:0 3px;">
							<?=$value?>
						</td>
						<td style="padding:0 3px;">
							<?=$multiple_loans['loan_type'][$key]?>
						</td>
						<td style="padding:0 3px;">
							<?=$multiple_loans['loan_amount'][$key]?>
						</td>
						<td style="padding:0 3px;">
							<?=$multiple_loans['monthly_payment'][$key]?>
						</td>
						<td style="padding:0 3px;">
							<?=$multiple_loans['date_of_payoff'][$key]?>
						</td>
						<td style="padding:0 3px;">
							<?=$multiple_loans['date_of_default'][$key]?>
						</td>
						<td style="padding:0 3px;">
							<?=$multiple_loans['school_attended'][$key]?>
						</td>
						<td style="padding:0 3px;">
							<?=$multiple_loans['degree_pursued'][$key]?>
						</td>
						<td style="padding:0 3px;">
							<?=$multiple_loans['specialization'][$key]?>
						</td>
						<td style="padding:0 3px;">
							<?=$multiple_loans['date_school_completed'][$key]?>
						</td>
						<td style="padding:0 3px;">
							<?=$multiple_loans['type_of_degree'][$key]?>
						</td>
						<td style="padding:0 3px;">
							<?=$multiple_loans['date_studies_ceased'][$key]?>
						</td>
					</tr>
					<?php
}
?>
			</tbody>
		</table>
	</div>
</body>
</html>