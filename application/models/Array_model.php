<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Array_model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->helper('url');
		$this->load->database();
	}

	public function state_list() {
		$arr = ['Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Puerto Rico', 'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'Washington DC', 'West Virginia', 'Wisconsin', 'Wyoming'];

		$state_list = array();
		foreach ($arr as $v) {$state_list[$v] = $v;}
		return $state_list;
	}

	public function sltb_code_id_list() {
		$arr = ["1" => "PPL loan", "2" => "Non PPL consolidation loan", "3" => "Perkins loan", "4" => "Stafford loan"];
		return $arr;
	}

	public function stlb_payment_plan_scenario() {
		$arr = [
			"1" => ["name" => "Single AGI", "group" => "SA"],
			"2" => ["name" => "Single AGI", "group" => "SH"],
			"3" => ["name" => "Single AGI", "group" => "SO"],
			"4" => ["name" => "MFJ AGI", "group" => "MA"],
			"5" => ["name" => "MFJ AGI", "group" => "MH"],
			"6" => ["name" => "MFJ AGI", "group" => "MO"],
			"7" => ["name" => "MFS AGI", "group" => "MA"],
			"8" => ["name" => "MFS AGI", "group" => "MH"],
			"9" => ["name" => "MFS AGI", "group" => "MO"],
			"10" => ["name" => "Single Monthly", "group" => "SA"],
			"11" => ["name" => "Single Monthly", "group" => "SH"],
			"12" => ["name" => "Single Monthly", "group" => "SO"],
			"13" => ["name" => "MFJ Monthly", "group" => "MA"],
			"14" => ["name" => "MFJ Monthly", "group" => "MH"],
			"15" => ["name" => "MFJ Monthly", "group" => "MO"],
			"16" => ["name" => "MFS Monthly", "group" => "MA"],
			"17" => ["name" => "MFS Monthly", "group" => "MH"],
			"18" => ["name" => "MFS Monthly", "group" => "MO"],
			"19" => ["name" => "MFJA AGI", "group" => "AA"],
			"20" => ["name" => "MFJA AGI", "group" => "AH"],
			"21" => ["name" => "MFJA AGI", "group" => "AO"],
			"22" => ["name" => "MFJA Monthly", "group" => "AA"],
			"23" => ["name" => "MFJA Monthly", "group" => "AH"],
			"24" => ["name" => "MFJA Monthly", "group" => "AO"],
			"25" => ["name" => "MFSA AGI", "group" => "AA"],
			"26" => ["name" => "MFSA AGI", "group" => "AH"],
			"27" => ["name" => "MFSA AGI", "group" => "AO"],
			"28" => ["name" => "MFSA Monthly", "group" => "AA"],
			"29" => ["name" => "MFSA Monthly", "group" => "AH"],
			"30" => ["name" => "MFSA Monthly", "group" => "AO"],
		];
		return $arr;
	}

	public function stlb_ca_payment_plan_scenario_group($marital_status = '', $file_joint_or_separate = '', $location = '') {
		if ($marital_status == 15 || $marital_status == 89) {
			// Married

			if ($file_joint_or_separate == 18 || $file_joint_or_separate == 92) {
				// File Join
				if ($location == "AK") {
					$res = array("MA" => ["0" => "MFJ AGI", "1" => "MFJ Monthly"]);
				} else if ($location == "HI") {
					$res = array("MH" => ["0" => "MFJ AGI", "1" => "MFJ Monthly"]);
				} else { $res = array("MO" => ["0" => "MFJ AGI", "1" => "MFJ Monthly"]);}
			} else {
				// File Separately
				if ($location == "AK") {
					$res = array("MA" => ["0" => "MFS AGI", "1" => "MFS Monthly"]);
				} else if ($location == "HI") {
					$res = array("MH" => ["0" => "MFS AGI", "1" => "MFS Monthly"]);
				} else { $res = array("MO" => ["0" => "MFS AGI", "1" => "MFS Monthly"]);}
			}

		} else if ($marital_status == 72 || $marital_status == 134) {
			//	Married, but separated

			if ($location == "AK") {
				$res = array("AA" => ["0" => "MFJA AGI", "1" => "MFJA Monthly"]);
			} else if ($location == "HI") {
				$res = array("AH" => ["0" => "MFJA AGI", "1" => "MFJA Monthly"]);
			} else { $res = array("AO" => ["0" => "MFJA AGI", "1" => "MFJA Monthly"]);}

		} else if ($marital_status == 73 || $marital_status == 135) {
			//	Married, but cannot reasonably access my spouse's income information

			if ($location == "AK") {
				$res = array("AA" => ["0" => "MFSA AGI", "1" => "MFSA Monthly"]);
			} else if ($location == "HI") {
				$res = array("AH" => ["0" => "MFSA AGI", "1" => "MFSA Monthly"]);
			} else { $res = array("AO" => ["0" => "MFSA AGI", "1" => "MFSA Monthly"]);}

		} else {
			//	Single

			if ($location == "AK") {
				$res = array("SA" => ["0" => "Single AGI", "1" => "Single Monthly"]);
			} else if ($location == "HI") {
				$res = array("SH" => ["0" => "Single AGI", "1" => "Single Monthly"]);
			} else { $res = array("SO" => ["0" => "Single AGI", "1" => "Single Monthly"]);}

		}

		return $res;

		$arr = [
			"SA" => [["name" => "Single AGI", "group" => "SA"], ["name" => "Single Monthly", "group" => "SA"]],
			"SH" => [["name" => "Single AGI", "group" => "SH"], ["name" => "Single Monthly", "group" => "SH"]],
			"SO" => [["name" => "Single AGI", "group" => "SO"], ["name" => "Single Monthly", "group" => "SO"]],
			"MA" => ["0" => ["name" => "MFJ AGI", "group" => "MA"], "1" => ["name" => "MFS AGI", "group" => "MA"], "2" => ["name" => "MFJ Monthly", "group" => "MA"], "3" => ["name" => "MFS Monthly", "group" => "MA"]],
			"MH" => ["0" => ["name" => "MFJ AGI", "group" => "MH"], "1" => ["name" => "MFS AGI", "group" => "MH"], "2" => ["name" => "MFJ Monthly", "group" => "MH"], "3" => ["name" => "MFS Monthly", "group" => "MH"]],
			"MO" => ["0" => ["name" => "MFJ AGI", "group" => "MO"], "1" => ["name" => "MFS AGI", "group" => "MO"], "2" => ["name" => "MFJ Monthly", "group" => "MO"], "3" => ["name" => "MFS Monthly", "group" => "MO"]],
			"AA" => ["0" => ["name" => "MFJA AGI", "group" => "AA"], "1" => ["name" => "MFJA Monthly", "group" => "AA"], "2" => ["name" => "MFSA AGI", "group" => "AA"], "3" => ["name" => "MFSA Monthly", "group" => "AA"]],
			"AH" => ["0" => ["name" => "MFJA AGI", "group" => "AH"], "1" => ["name" => "MFJA Monthly", "group" => "AH"], "2" => ["name" => "MFSA AGI", "group" => "AH"], "3" => ["name" => "MFSA Monthly", "group" => "AH"]],
			"AO" => ["0" => ["name" => "MFJA AGI", "group" => "AO"], "1" => ["name" => "MFJA Monthly", "group" => "AO"], "2" => ["name" => "MFSA AGI", "group" => "AO"], "3" => ["name" => "MFSA Monthly", "group" => "AO"]],
		];
		//return $arr;
	}

	public function stlb_payment_plan_scenario_group() {
		$arr = [
			"SA" => [["name" => "Single AGI", "group" => "SA"], ["name" => "Single Monthly", "group" => "SA"]],
			"SH" => [["name" => "Single AGI", "group" => "SH"], ["name" => "Single Monthly", "group" => "SH"]],
			"SO" => [["name" => "Single AGI", "group" => "SO"], ["name" => "Single Monthly", "group" => "SO"]],
			"MA" => ["0" => ["name" => "MFJ AGI", "group" => "MA"], "1" => ["name" => "MFS AGI", "group" => "MA"], "2" => ["name" => "MFJ Monthly", "group" => "MA"], "3" => ["name" => "MFS Monthly", "group" => "MA"]],
			"MH" => ["0" => ["name" => "MFJ AGI", "group" => "MH"], "1" => ["name" => "MFS AGI", "group" => "MH"], "2" => ["name" => "MFJ Monthly", "group" => "MH"], "3" => ["name" => "MFS Monthly", "group" => "MH"]],
			"MO" => ["0" => ["name" => "MFJ AGI", "group" => "MO"], "1" => ["name" => "MFS AGI", "group" => "MO"], "2" => ["name" => "MFJ Monthly", "group" => "MO"], "3" => ["name" => "MFS Monthly", "group" => "MO"]],
			"AA" => ["0" => ["name" => "MFJA AGI", "group" => "AA"], "1" => ["name" => "MFJA Monthly", "group" => "AA"], "2" => ["name" => "MFSA AGI", "group" => "AA"], "3" => ["name" => "MFSA Monthly", "group" => "AA"]],
			"AH" => ["0" => ["name" => "MFJA AGI", "group" => "AH"], "1" => ["name" => "MFJA Monthly", "group" => "AH"], "2" => ["name" => "MFSA AGI", "group" => "AH"], "3" => ["name" => "MFSA Monthly", "group" => "AH"]],
			"AO" => ["0" => ["name" => "MFJA AGI", "group" => "AO"], "1" => ["name" => "MFJA Monthly", "group" => "AO"], "2" => ["name" => "MFSA AGI", "group" => "AO"], "3" => ["name" => "MFSA Monthly", "group" => "AO"]],
		];
		return $arr;
	}

	public function arr_payment_plan($total_loan) {
		//$arr = ["1"=>"10-Year Standard", "2"=>"25-Year Extended", "3"=>"REPAYE", "4"=>"PAYE", "5"=>"IBR", "6"=>"New IBR", "7"=>"ICR"];
		if ($total_loan >= 60000) {
			$years = 360;
		} else if ($total_loan >= 40000) {
			$years = 300;
		} else if ($total_loan >= 20000) {
			$years = 240;
		} else if ($total_loan >= 10000) {
			$years = 180;
		} else if ($total_loan >= 7500) {
			$years = 144;
		} else { $years = 120;}

		$plan_name = "Standard Plan (" . ($years / 12) . "-Year)";
		$arr = ["1" => $plan_name, "2" => "25-Year Extended", "3" => "REPAYE", "4" => "PAYE", "5" => "IBR", "6" => "New IBR", "7" => "ICR"];

		return $arr;
	}

	public function arr_intake_program_id() {
		$arr = ["1" => "3", "23" => "2", "127" => "4", "40" => "5", "178" => "6", "193" => "7", "91" => "1"];
		return $arr;
	}

	public function loan_type_code() {
		$arr = ["DIRECT CONSOLIDATED SUBSIDIZED" => "E", "DIRECT CONSOLIDATED SUBSIDIZED (SULA ELIGIBLE)" => "E", "DIRECT CONSOLIDATED UNSUBSIDIZED" => "K", "DIRECT PLUS CONSOLIDATED" => "V", "DIRECT PLUS GRADUATE" => "I", "DIRECT PLUS PARENT" => "U", "DIRECT STAFFORD SUBSIDIZED" => "D", "DIRECT STAFFORD SUBSIDIZED (SULA ELIGIBLE)" => "D", "DIRECT STAFFORD UNSUBSIDIZED" => "L", "DIRECT UNSUBSIDIZED (TEACH)" => "L", "FEDERAL INSURED (FISL)" => "C", "FEDERAL PERKINS" => "F", "FFEL CONSOLIDATED" => "O", "FFEL PLUS GRADUATE" => "S", "FFEL PLUS PARENT" => "T", "FFEL REFINANCED" => "O", "FFEL STAFFORD NON-SUBSIDIZED" => "G", "FFEL STAFFORD SUBSIDIZED" => "A", "FFEL STAFFORD UNSUBSIDIZED" => "G", "FFEL SUPPLEMENTAL LOAN (SLS)" => "H", "GUARANTEED STUDENT LOAN (GSL)" => "B", "HEALTH EDUCATION ASSISTANCE LOAN (HEAL)" => "R", "HEALTH PROFESSIONS STUDENT LOAN (HPSL)" => "Q", "NATIONAL DEFENSE LOAN (PERKINS)" => "N", "NATIONAL DIRECT STUDENT LOAN (PERKINS)" => "M", "NURSING STUDENT LOAN (NSL)" => "Y", "PERKINS EXPANDED LENDING" => "F"];
		return $arr;
	}

}
?>