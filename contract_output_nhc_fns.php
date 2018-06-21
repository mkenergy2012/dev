<?php
include('uamc_preassessment_fns.php');
include("lib/jquery_fns.php");
ini_set('default_charset', 'UTF-8');


function contract_nhc_doc_entry($get, $post)
{
	global $_SESSION, $e_serv;
	$mytime = time();
	$db_conn = db_connect($e_serv);
	if ($_SESSION['userid'] == 'niles.rowland@lennar.com' && 1==2) {
		echo __FILE__.':'.__LINE__.' session <pre>';
		print_r($_SESSION);
		echo '</pre>';
	}
	if (!$db_conn) {
		echo '<br><h2>Unable to connect to oracle database!';
		exit;
	} else {
		if (!isset($_SESSION['division_jde_selected'])) {
			echo '<br><h3>User Session has expired! Please return to CRM Opportunity and click to return to purchase agreements!</h3>';
			exit;
		}
		$index_part_doc = $get['index_part_doc'];
		$description = $get['description'];
		$userid = '';
		if (isset($_SESSION['userid'])) {
			$userid = $_SESSION['userid'];
		}
		$division_jde = $_SESSION['division_jde_selected'];
		$community_jde = $_SESSION['community_jde_selected'];
		$customer_id = $_SESSION['customer_id'];
		$opportunity_id = '';
		if (isset($_SESSION['opportunity_id'])) {
			$opportunity_id = $_SESSION['opportunity_id'];
		}
		$homesite = $_SESSION['homesite_selected'];
		$qu_reg = "select division, region_jde from dj_division where division_jde = '$division_jde' ";

		$pos = strpos(strtolower($description), 'credit card addendum');
		if ($pos === false) {
			$cc_addendum = false;
		}
		else {
			$cc_addendum = true;
		}

		$stid = oci_parse($db_conn, $qu_reg);
		oci_execute($stid);
		while ($row2 = oci_fetch_array($stid, OCI_RETURN_NULLS + OCI_ASSOC)) {
			//here if doc is assigned...
			$division = html_entity_decode(trim($row2['DIVISION']), ENT_QUOTES);
			$region_jde = html_entity_decode(trim($row2['REGION_JDE']), ENT_QUOTES);
		}

		$document_complete = '';

		$qu_doc = "select document_complete from dj_customer_comm_home_docs where division_jde = '$division_jde' and community_jde = '$community_jde' and
																			      index_part_doc = '$index_part_doc' and customer_id = '$customer_id' and homesite = '$homesite' ";
		if ($userid == 'niles.rowland@lennar.com' && 1==2) {
			echo __FILE__.':'.__LINE__.' $qu_doc: '.$qu_doc.'<br>';
		}

		$stid = oci_parse($db_conn, $qu_doc);
		oci_execute($stid);
		while ($row2 = oci_fetch_array($stid, OCI_RETURN_NULLS + OCI_ASSOC)) {
			//here if doc is assigned...
			$document_complete = html_entity_decode(trim($row2['DOCUMENT_COMPLETE']), ENT_QUOTES);
		}
		if ($document_complete == 'Y') {
			$document_complete = 'Yes';
		}
		else {
			$document_complete = 'No';
		}

		$qu_comm = "select community from dj_community where division_jde = '$division_jde' and community_jde = '$community_jde' ";

		$stid = oci_parse($db_conn, $qu_comm);
		oci_execute($stid);
		while ($row3 = oci_fetch_array($stid, OCI_RETURN_NULLS + OCI_ASSOC)) {
			//here if doc is assigned...
			$community = html_entity_decode(trim($row3['COMMUNITY']), ENT_QUOTES);
		}


		$description_my_doc = '';
		$level_desc_first = '';

		$col = 1;
		$field_count = 0;

		//set up the buyer checkboxes
		$buyer1_firstname_oncont = 'Y';
		$buyer2_firstname_oncont = 'Y';
		$buyer3_firstname_oncont = 'Y';
		$buyer4_firstname_oncont = 'Y';

		$found1 = false;
		$found2 = false;
		$found3 = false;
		$found4 = false;

		$qu_buyers = "select field_name, value from dj_customer_comm_home where customer_id = '$customer_id' and
																	   division_jde		= '$division_jde' and
																	   community_jde	= '$community_jde' and
																	   homesite 		= '$homesite' and
																	  ( field_name		= 'BUYER1_FIRSTNAME_ONCONT' or
																	    field_name		= 'BUYER2_FIRSTNAME_ONCONT' or
																	    field_name		= 'BUYER3_FIRSTNAME_ONCONT' or
																		field_name		= 'BUYER4_FIRSTNAME_ONCONT' )
																	   ";
		if ($_SESSION['userid'] == 'niles.rowland@lennar.com' && 1==2) {
			echo __FILE__.':'.__LINE__.' $qu_buyers: '.$qu_buyers.'<br>';
		}
		$stid02 = oci_parse($db_conn, $qu_buyers);
		oci_execute($stid02);
		while ($rowvalue = oci_fetch_array($stid02, OCI_RETURN_NULLS + OCI_ASSOC)) {
			$my_field_name = html_entity_decode(trim($rowvalue['FIELD_NAME']), ENT_QUOTES);
			if ($my_field_name == 'BUYER1_FIRSTNAME_ONCONT') {
				$buyer1_firstname_oncont = html_entity_decode(trim($rowvalue['VALUE']), ENT_QUOTES);
				$found1 = true;
			} else if ($my_field_name == 'BUYER2_FIRSTNAME_ONCONT') {
				$buyer2_firstname_oncont = html_entity_decode(trim($rowvalue['VALUE']), ENT_QUOTES);
				$found2 = true;
			} else if ($my_field_name == 'BUYER3_FIRSTNAME_ONCONT') {
				$buyer3_firstname_oncont = html_entity_decode(trim($rowvalue['VALUE']), ENT_QUOTES);
				$found3 = true;
			} else if ($my_field_name == 'BUYER4_FIRSTNAME_ONCONT') {
				$buyer4_firstname_oncont = html_entity_decode(trim($rowvalue['VALUE']), ENT_QUOTES);
				$found4 = true;
			}

		}
		if (!$found1) {
			//insert the field
			$q_ins = "insert into dj_customer_comm_home (customer_Id, division_jde, community_jde, homesite, field_name, value)
													values ('$customer_id', '$division_jde', '$community_jde', '$homesite', 'BUYER1_FIRSTNAME_ONCONT', 'Y')";
			$stid02 = oci_parse($db_conn, $q_ins);
			oci_execute($stid02);
		}

		if (!$found2) {
			//insert the field
			$q_ins = "insert into dj_customer_comm_home (customer_Id, division_jde, community_jde, homesite, field_name, value)
													values ('$customer_id', '$division_jde', '$community_jde', '$homesite', 'BUYER2_FIRSTNAME_ONCONT', 'Y')";
			$stid02 = oci_parse($db_conn, $q_ins);
			oci_execute($stid02);
		}

		if (!$found3) {
			//insert the field
			$q_ins = "insert into dj_customer_comm_home (customer_Id, division_jde, community_jde, homesite, field_name, value)
													values ('$customer_id', '$division_jde', '$community_jde', '$homesite', 'BUYER3_FIRSTNAME_ONCONT', 'Y')";
			$stid02 = oci_parse($db_conn, $q_ins);
			oci_execute($stid02);
		}

		if (!$found4) {
			//insert the field
			$q_ins = "insert into dj_customer_comm_home (customer_Id, division_jde, community_jde, homesite, field_name, value)
													values ('$customer_id', '$division_jde', '$community_jde', '$homesite', 'BUYER4_FIRSTNAME_ONCONT', 'Y')";
			$stid02 = oci_parse($db_conn, $q_ins);
			oci_execute($stid02);
		}

		//end of buyer check boxes

		echo '<table class="borders_left"><tr valign="bottom">';
		if ($e_serv != 'Production' and $e_serv != 'ProductionNew')
			echo '<tr><td nowrap colspan="6">Server: ' . $_SERVER['SERVER_NAME'] . '</td></tr>';

		echo '<tr><td nowrap colspan="6">Document: ' . $description . '</td></tr>';
		echo '<tr><td nowrap colspan="6">Documents for: <b>' . $_SESSION['customer_name_selected'] . '</b></td></tr>';
		echo '<tr><td nowrap colspan="6">Community: <b>' . $division . ' - ' . $community_jde . ' - ' . $community . '</b> Homesite: <b>' . $homesite . '</b></td></tr>';
		echo '<tr><td>&nbsp;</td></tr>';
		echo '<form action="contract_nhc_doc_entry_post.php" method="post">';
		echo '<input type="hidden" name="division_jde" value="' . $division_jde . '">';
		echo '<input type="hidden" name="community_jde" value="' . $community_jde . '">';
		echo '<input type="hidden" name="customer_id" value="' . $customer_id . '">';
		echo '<input type="hidden" name="homesite" value="' . $homesite . '">';
		echo '<input type="hidden" name="index_part_doc" value="' . $index_part_doc . '">';

		$_SESSION['field_array'] = array();
		$_SESSION['field_array_type'] = array();
		$_SESSION['field_array_modify'] = array();
		$_SESSION['field_array_view_only'] = array();

		if (!$cc_addendum) {
			$qu_doc = "select dj_field.field_name, dj_field.field_label, dj_field.siebel, dj_field.siebel_field_name, dj_field.field_type, dj_field.entity_values, dj_field.big_fatty
				  from dj_field, dj_documents_dtl_field where dj_field.field_name = dj_documents_dtl_field.field_name and dj_documents_dtl_field.index_part_doc = '$index_part_doc'
				  order by dj_field.siebel desc, dj_field.field_name, dj_field.field_type";
		} else {
			$qu_doc = "select dj_field.field_name, dj_field.field_label, dj_field.siebel, dj_field.siebel_field_name, dj_field.field_type, dj_field.entity_values, dj_field.big_fatty
				  from dj_field, dj_documents_dtl_field where dj_field.field_name = dj_documents_dtl_field.field_name and dj_documents_dtl_field.index_part_doc = '$index_part_doc'
				  order by dj_field.siebel desc, dj_field.field_label, dj_field.field_name, dj_field.field_type";
		}

		$stid = oci_parse($db_conn, $qu_doc);
		oci_execute($stid);
		while ($row = oci_fetch_array($stid, OCI_RETURN_NULLS + OCI_ASSOC)) {
			$field_name = strtoupper(html_entity_decode(trim($row['FIELD_NAME']), ENT_QUOTES));
			$field_label = html_entity_decode(trim($row['FIELD_LABEL']), ENT_QUOTES);
			$siebel = html_entity_decode(trim($row['SIEBEL']), ENT_QUOTES);
			$siebel_field_name = html_entity_decode(trim($row['SIEBEL_FIELD_NAME']), ENT_QUOTES);
			$field_type = html_entity_decode(trim($row['FIELD_TYPE']), ENT_QUOTES);
			$entity_values = html_entity_decode(trim($row['ENTITY_VALUES']), ENT_QUOTES);
			$big_fatty = html_entity_decode(trim($row['BIG_FATTY']), ENT_QUOTES);
			//get the value from the dj_customer_comm_home table
			unset($myvalue);
			unset($myvalue_admin);

			$modify = false; //adding or modifying the record?
			$view_only = 'Y';
			$qu_value = "select value, view_only,blank_checked from dj_customer_comm_home where customer_id 		= '$customer_id' and
																	   division_jde		= '$division_jde' and
																	   community_jde	= '$community_jde' and
																	   homesite 		= '$homesite' and
																	   field_name		= '$field_name'
																	   ";
			$stid2 = oci_parse($db_conn, $qu_value);
			oci_execute($stid2);
			if ($_SESSION['USERID'] == 'niles.rowland@lennar.com' && 1==2) {
				if ($field_name == 'BUYER1_FIRSTNAME' and 1 == 1) {
					echo __FILE__.':'.__LINE__.' $qu_value: ' . $qu_value;
				}
			}
			while ($rowvalue = oci_fetch_array($stid2, OCI_RETURN_NULLS + OCI_ASSOC)) {
				if ($field_type == 'Text' or $field_type == 'Text Area') {
					$myvalue = html_entity_decode($rowvalue['VALUE'], ENT_QUOTES);
				}
				else {
					$myvalue = html_entity_decode(trim($rowvalue['VALUE']), ENT_QUOTES);
				}
				//$view_only = stripslashes(html_entity_decode(trim($rowvalue['VIEW_ONLY']), ENT_QUOTES));
				$modify = true;
				$blank_checkbox_val = html_entity_decode(trim($rowvalue['BLANK_CHECKED']), ENT_QUOTES);
			}

			//added 05/04/2010 for save blank value changes, mak

			if (isset($blank_checkbox_val) and $blank_checkbox_val == 'y') {
				$blank_checkbox_st = 'checked';
			} else {
				$blank_checkbox_st = '';
			}

			if (($field_name == 'BUYEREMAIL') and ($userid == 'jeff.mckenzie@lennar.com' || $_SESSION['userid'] == 'niles.rowland@lennar.com') and 1 == 1) {
				echo '<br />' . $field_name . ' - ' . $myvalue;
				echo '<br />qu: ' . $qu_value;
				echo '<br />session: ' . $_SESSION[$field_name];
			}
			//get the value from the dj_field_comm table for initial value set by div admin
			$qu_admin_value = "select value, view_only from dj_field_comm where division_jde		= '$division_jde' and
															   community_jde	= '$community_jde' and
															   field_name		= '$field_name'
																	   ";
			if ($_SESSION['userid'] == 'niles.rowland@lennar.com' && 1==2) {
				echo __FILE__.':'.__LINE__.' $qu_admin_value: '.$qu_admin_value.'<br>';
			}
			$stid3 = oci_parse($db_conn, $qu_admin_value);
			oci_execute($stid3);
			while ($rowadminvalue = oci_fetch_array($stid3, OCI_RETURN_NULLS + OCI_ASSOC)) {
				if ($field_type == 'Text' or $field_type == 'Text Area') {
					$myvalue_admin = stripslashes(html_entity_decode($rowadminvalue['VALUE'], ENT_QUOTES));
				}
				else {
					$myvalue_admin = stripslashes(html_entity_decode(trim($rowadminvalue['VALUE']), ENT_QUOTES));
				}
				$view_only = html_entity_decode(trim($rowadminvalue['VIEW_ONLY']), ENT_QUOTES);
			}
			if (($field_name == 'LOTCA' or $field_name == 'AUTHORIZEDAGENT_NAME') and $e_serv != 'ProductionNew' and 1 == 2) {
				echo '<br /> before...';
				echo '<br />' . $field_name . ' - ' . $myvalue_admin . ' - ' . $myvalue;
				echo '<br />qu: ' . $qu_admin_value;
				echo '<br />session: ' . $_SESSION[$field_name];
				echo '<br />View Only: ' . $view_only;
			}

			$set_by_divadmin = 'N';
			$itis_divadmin = 'N';
			if (isset($myvalue_admin))
				$itis_divadmin = 'Y';

			//if(!isset($myvalue) and isset($myvalue_admin))
			if (isset($myvalue_admin) and $view_only == 'N') {
				$myvalue = $myvalue_admin;
				$set_by_divadmin = 'Y';
			} //change made by mak; 04-08-2010 to enable saving blank values
			else if (isset($myvalue_admin) and (!isset($myvalue) or $myvalue == '') and (!isset($_SESSION['save_blank_value'][$field_count]) or $_SESSION['save_blank_value'][$field_count] != 'on')) {
				$myvalue = $myvalue_admin;
				$set_by_divadmin = 'Y';
			} else if (!isset($myvalue)) {
				if ($field_type == 'Percentage' or $field_type == 'Percent' or $field_type == 'Currency' or $field_type == 'Integer') {
					$myvalue = 0;
				}
				else {
					$myvalue = '';
				}
			}

			if (($field_name == 'CLOSEBYDATEEFSI') and $e_serv != 'ProductionNew' and 1 == 2) {
				echo '<br /> after...';
				echo '<br />' . $field_name . ' - ' . $myvalue_admin . ' - ' . $myvalue;
				echo '<br />qu: ' . $qu_admin_value;
				echo '<br />session: ' . $_SESSION[$field_name];
				echo '<br />View Only: ' . $view_only;
				echo '<br />Siebel: ' . $siebel;
			}

			if ($siebel == 'Y') {
				$view_only = 'N';
				if (isset($_SESSION[$field_name])) {
					$mysiebelvalue = $_SESSION[$field_name];
					$mysiebelvalue = htmlentities($mysiebelvalue, ENT_QUOTES);
				} else {
					$mysiebelvalue = ucwords($siebel_field_name);
					$mysiebelvalue = '';
				}
				if ($mysiebelvalue == '')
					$disab = 'disabled';
				else
					$disab = 'disabled';
				echo '<tr>';

				if (isset($_SESSION['nocrm']) and $_SESSION['nocrm'] == 'yes') {
					$view_only = 'Y';
					$disab = '';
					$mysiebelvalue = $myvalue;
				}

				if ($field_name == 'BUYER1_FIRSTNAME') {
					if ($buyer1_firstname_oncont == 'Y')
						$oncont = 'checked';
					else
						$oncont = 'unchecked';
					if ($mysiebelvalue == '')
						$oncont = 'unchecked';

					$oncont = "checked";

					echo '<td nowrap title="' . $field_name . ' - ' . $siebel_field_name . ' - On Contract?"><input type="checkbox" name="buyer1_firstname_oncont_dummy" disabled="disabled" ' . $oncont . '> ';
					//force the buyer 1 checkbox to = 1
					echo '<input type="hidden" name="buyer1_firstname_oncont" value="on">';
					echo ' ' . $field_label . '</td>';
					echo '<td title="On Contract?"><input type="text" name="blah' . $field_count . '" size="60" value="' . $mysiebelvalue . '" ' . $disab . '>';
				} //else if($field_name == 'BUYER2_FIRSTNAME' or $field_name == 'BUYER2_LASTNAME' or $field_name == 'BUYER2_MIDDLENAME')
				else if ($field_name == 'BUYER2_FIRSTNAME') {
					if ($buyer2_firstname_oncont == 'Y')
						$oncont = 'checked';
					else
						$oncont = 'unchecked';
					if ($mysiebelvalue == '')
						$oncont = 'unchecked';

					if ($oncont == 'unchecked') {
						$mysiebelvalue = '';
					}

					echo '<td nowrap title="' . $field_name . ' - ' . $siebel_field_name . ' - On Contract?"><input type="checkbox" name="buyer2_firstname_oncont" size="10" ' . $oncont . '> ';
					echo ' ' . $field_label . '</td>';
					echo '<td title="On Contract?"><input type="text" name="blah' . $field_count . '" size="60" value="' . $mysiebelvalue . '" ' . $disab . '>';
				} //else if($field_name == 'BUYER3_FIRSTNAME' or $field_name == 'BUYER3_LASTNAME' or $field_name == 'BUYER3_MIDDLENAME')
				else if ($field_name == 'BUYER3_FIRSTNAME') {
					if ($buyer3_firstname_oncont == 'Y')
						$oncont = 'checked';
					else
						$oncont = 'unchecked';
					if ($mysiebelvalue == '')
						$oncont = 'unchecked';

					if ($oncont == 'unchecked') {
						$mysiebelvalue = '';
					}

					echo '<td nowrap title="' . $field_name . ' - ' . $siebel_field_name . ' - On Contract?"><input type="checkbox" name="buyer3_firstname_oncont" size="10" ' . $oncont . '> ';
					echo ' ' . $field_label . '</td>';
					echo '<td title="On Contract?"><input type="text" name="blah' . $field_count . '" size="60" value="' . $mysiebelvalue . '" ' . $disab . '>';
				} //else if($field_name == 'BUYER4_FIRSTNAME' or $field_name == 'BUYER4_LASTNAME' or $field_name == 'BUYER4_MIDDLENAME')
				else if ($field_name == 'BUYER4_FIRSTNAME') {
					if ($buyer4_firstname_oncont == 'Y')
						$oncont = 'checked';
					else
						$oncont = 'unchecked';
					if ($mysiebelvalue == '')
						$oncont = 'unchecked';

					if ($oncont == 'unchecked') {
						$mysiebelvalue = '';
					}

					echo '<td nowrap title="' . $field_name . ' - ' . $siebel_field_name . ' - On Contract?"><input type="checkbox" name="buyer4_firstname_oncont" size="10" ' . $oncont . '> ';
					echo ' ' . $field_label . '</td>';
					echo '<td title="On Contract?"><input type="text" name="blah' . $field_count . '" size="60" value="' . $mysiebelvalue . '" ' . $disab . '>';
				} else if ($field_name == 'SALEDATE') {

					if ($mysiebelvalue == '') {
						$disab = '';
						$modify = true;
						echo '<td nowrap title="' . $field_name . ' - ' . $siebel_field_name . '">' . $field_label . '(mm/dd/yyyy)</td><td><input type="text" name="SALEDATE"  class="datepicker2" size="60" value="'.$myvalue.'" ' . $disab . '>';

					} else {
						$disab = '';
						$modify = true;
						$_SESSION['sale_date_input'] = $mysiebelvalue;
						// echo '<td nowrap title="'.$field_name.' - '.$siebel_field_name.'">'.$field_label.'(mm/dd/yyyy)</td><td><input type="text" name="SALEDATE'.$field_count.'" size="60" value="'.$mysiebelvalue.'" '.$disab.'>';
						// echo '<input type="hidden" name="'.$field_name.'" value="'.$mysiebelvalue.'">';
						echo '<td nowrap title="' . $field_name . ' - ' . $siebel_field_name . '">' . $field_label . '(mm/dd/yyyy)</td><td><input type="text" name="SALEDATE"  class="datepicker2" size="60" value="' . $_SESSION['sale_date_input'] . '" ' . $disab . '>';

					}
				} else {
					if (isset($_SESSION['nocrm']) and $_SESSION['nocrm'] == 'yes') {
						echo '<td nowrap title="' . $field_name . ' - ' . $siebel_field_name . '">' . $field_label . '</td><td><input type="text" name="' . $field_name . '" value="' . $mysiebelvalue . '" size="60" > ';
					} else {
						echo '<td nowrap title="' . $field_name . ' - ' . $siebel_field_name . '">' . $field_label . '</td><td><input type="text" name="blah' . $field_count . '" size="60" value="' . $mysiebelvalue . '" ' . $disab . '>';

					}
				}

				//if($field_name != 'SALEDATE')
				if ($field_name != 'SALEDATE' and (!isset($_SESSION['nocrm']) or (isset($_SESSION['nocrm']) and $_SESSION['nocrm'] != 'yes')))
					echo '<input type="hidden" name="' . $field_name . '" value="' . $mysiebelvalue . '"> ';
				echo '(Source: CRM OD)</td></tr>';
			} else if ($field_type == 'Radio Button') {
				echo '<tr>';
				echo '<td nowrap title="' . $field_name . ' - ' . $field_type . '">' . $field_label . ' (pick one)</td>';
				$pickitem = trim(strtok($entity_values, '|'));
				$my_item = 0;
				while ($pickitem != '') {
					if ($pickitem != 'None' or $field_name == 'FHAVAADDCB') {
						if ($pickitem == $myvalue)
							$checked = 'checked';
						else
							$checked = '';
						if (($field_name == 'GENDER1' or $field_name == 'MARITAL_STATUS1') and $buyer1_firstname_oncont != 'Y') {
							$checked = '';
						} else if (($field_name == 'GENDER2' or $field_name == 'MARITAL_STATUS2') and $buyer2_firstname_oncont != 'Y') {
							$checked = '';
						} else if (($field_name == 'GENDER3' or $field_name == 'MARITAL_STATUS3') and $buyer3_firstname_oncont != 'Y') {
							$checked = '';
						} else if (($field_name == 'GENDER4' or $field_name == 'MARITAL_STATUS4') and $buyer4_firstname_oncont != 'Y') {
							$checked = '';
						}
						if ($view_only != 'N' or ($view_only == 'N' and $pickitem == $myvalue)) //this forces there to be only one choice...
						{
							echo '<td nowrap title="' . $field_name . '"><input type="radio" name="' . $field_name . '" value="' . $my_item . '***-***' . $pickitem . '" ' . $checked . '> ' . $pickitem;
							echo '</td></tr><tr><td>&nbsp;</td>';
						}
					}
					$pickitem = trim(strtok('|'));
					$my_item++;
				}
				echo '</td></tr>';
			} else if ($field_type == 'Picklist') {
				echo '<tr>';
				echo '<td nowrap title="' . $field_name . ' - ' . $field_type . '">' . $field_label . '</td><td><select name="' . $field_name . '">';
				//echo '<option selected value="'.$myvalue.'">'.$myvalue.'</option>';
				$pickitem = trim(strtok($entity_values, '|'));
				$my_item = 0;
				while ($pickitem != '') {
					if (trim($pickitem) == trim($myvalue))
						echo '<option selected value="' . $my_item . '***-***' . $pickitem . '">' . $pickitem . '</option>';
					else if ($view_only != 'N') //this forces there to be only one choice...
						echo '<option value="' . $my_item . '***-***' . $pickitem . '">' . $pickitem . '</option>';
					$pickitem = trim(strtok('|'));
					$my_item++;
				}
				echo '</select></td></tr>';
			} else if ($field_type == 'Checkbox') {
				$checked = $entity_values;
				if ($myvalue == 'Y')
					$checked = 'checked';
				if ($myvalue == 'N')
					$checked = '';

				echo '<tr>';
				if ($view_only != 'N')
					echo '<td nowrap title="' . $field_name . ' - ' . $field_type . '">' . $field_label . '</td><td><input type="' . $field_type . '" name="' . $field_name . '" ' . $checked . '></td></tr>';
				else {
					echo '<td nowrap title="' . $field_name . ' - ' . $field_type . '">' . $field_label . '</td><td><input type="' . $field_type . '" name="' . $field_name . '_deselect" ' . $checked . ' disabled>';
					echo '<input type="hidden" name="' . $field_name . '" value="' . $myvalue . '"></td></tr>';
				}
			} else if ($field_type == 'Date') {
				$addtext = '(enter mm/dd/yyyy)';
				$pretext = '';
				//convert the date to mm/dd/yyyy
				echo '<tr>';
				$my_dq_value = $myvalue;
				$my_dq_value = htmlentities($myvalue, ENT_QUOTES);
				if ($view_only == 'N') {
					echo '<td nowrap title="' . $field_name . ' - ' . $field_type . '">' . $field_label . ' ' . $addtext . '</td><td><input type="text" name="' . $field_name . '" size="20" value="' . $my_dq_value . '" disabled>';
					if ($set_by_divadmin == 'Y')
						echo '(Div Admin)';
					echo '<input type="hidden" name="' . $field_name . '" size="60" value="' . $my_dq_value . '" >';
				} //else  if($blank_checkbox_val == 'y' or $set_by_divadmin == 'Y')
				else if ($itis_divadmin == 'Y') {
					echo '<td nowrap title="' . $field_name . ' - ' . $field_type . '">' . $field_label . ' ' . $addtext . '</td><td><input type="text" class="datepicker2" name="' . $field_name . '" size="20" value="' . $my_dq_value . '">';
					echo '&nbsp;&nbsp;&nbsp;&nbsp;save blank value: <input type="checkbox" name="save_blank_value' . $field_count . '" size="10" ' . $blank_checkbox_st . '>';
					if ($set_by_divadmin == 'Y')
						echo '(Div Admin)';
				} else {
					echo '<td nowrap title="' . $field_name . ' - ' . $field_type . '">' . $field_label . ' ' . $addtext . '</td><td><input type="text" class="datepicker2" name="' . $field_name . '" size="20" value="' . $my_dq_value . '">';
				}
				echo '</td></tr>';
			} //added mak 04-07-2010

			else if ($field_type == 'Text Area') {
				$my_value_ta = htmlentities($myvalue, ENT_QUOTES);

				if ($view_only == 'N') {
					//echo '<td nowrap title="'.$field_name.' - '.$field_type.'">'.$field_label.'</td><td><textarea rows="3" cols="50" name="'.$field_name.'" value="'. $my_value_ta.'" disabled>'.$my_value_ta.'</textarea>';
					echo '<td nowrap title="' . $field_name . ' - ' . $field_type . '">' . $field_label . '</td><td><textarea rows="3" cols="50" name="' . $field_name . '" value="' . $my_value_ta . '" readonly="readonly">' . $my_value_ta . '</textarea>';
					if ($set_by_divadmin == 'Y')
						echo '(Div Admin)';
				} // else if($blank_checkbox_val == 'y' or $set_by_divadmin == 'Y')
				else if ($itis_divadmin == 'Y') {
					echo '<td nowrap title="' . $field_name . ' - ' . $field_type . '">' . $field_label . '</td><td><textarea rows="3" cols="35" name="' . $field_name . '" value="' . $my_value_ta . '">' . $my_value_ta . '</textarea>';
					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;save blank value: <input type="checkbox" name="save_blank_value' . $field_count . '" size="10"' . $blank_checkbox_st . '>';
					if ($set_by_divadmin == 'Y')
						echo '(Div Admin)';
				} else {
					echo '<td nowrap title="' . $field_name . ' - ' . $field_type . '">' . $field_label . '</td><td><textarea rows="3" cols="50" name="' . $field_name . '" value="' . $my_value_ta . '">' . $my_value_ta . '</textarea>';
				}

				echo '</td></tr>';
			} else {
				$addtext = '';
				$pretext = '';
				if ($field_type == 'Currency') {
					$addtext = '(enter #\'s only. i.e. 2000)';
					if (substr_count($myvalue, '.'))
						$myvalue = number_format(return_good_number($myvalue), 2, '.', '');
					else
						$myvalue = number_format(return_good_number($myvalue), 0, '', '');
				} else if ($field_type == 'Percentage')
					$addtext = '(enter #\'s only. i.e. 15.5)';
				else if ($field_type == 'Percent')
					$addtext = '(enter #\'s only. i.e. 15.5)';
				else if ($myvalue == '' and $field_name == 'SUBDIVISION') {
					$myvalue = $_SESSION['COMMUNITY'];
				}
				echo '<tr>';
				$my_dq_value = $myvalue;
				$my_dq_value = htmlentities($myvalue, ENT_QUOTES);
				if ($view_only == 'N') {
					echo '<td nowrap title="' . $field_name . ' - ' . $field_type . '">' . $field_label . ' ' . $addtext . '</td><td><input type="text" name="123' . $field_name . '" size="60" value="' . $my_dq_value . '" disabled>';
					if ($set_by_divadmin == 'Y')
						echo '(Div Admin)';

					//next line CANNOT be commented out!!! <input above does not send field because it is disabled. Jeff McKenzie 4/23/2012
					echo '<input type="hidden" name="' . $field_name . '" size="60" value="' . $my_dq_value . '" >';
				} //	else if($blank_checkbox_val == 'y' or $set_by_divadmin == 'Y')
				else if ($itis_divadmin == 'Y') {
					echo '<td nowrap title="' . $field_name . ' - ' . $field_type . '">' . $field_label . ' ' . $addtext . '</td><td><input type="text" name="' . $field_name . '" size="40" value="' . $my_dq_value . '">';
					echo '&nbsp;&nbsp;&nbsp;&nbsp;Keep blank value: <input type="checkbox" name="save_blank_value' . $field_count . '" size="10"' . $blank_checkbox_st . '>';
					if ($set_by_divadmin == 'Y')
						echo '(Div Admin)';
				} else {
					echo '<td nowrap title="' . $field_name . ' - ' . $field_type . '">' . $field_label . ' ' . $addtext . '</td><td><input type="text" name="' . $field_name . '" size="60" value="' . $my_dq_value . '">';
					if ($set_by_divadmin == 'Y')
						echo '(Div Admin)';
				}
				echo '</td></tr>';

			}

			$_SESSION['field_array'][$field_count] = $field_name;
			$_SESSION['field_array_type'][$field_count] = $field_type;
			$_SESSION['field_array_modify'][$field_count] = $modify;
			$_SESSION['field_array_view_only'][$field_count] = $view_only;
			$_SESSION['field_array_big_fatty'][$field_count] = $big_fatty;
			$field_count++;
		}
		echo '<tr><td nowrap>Mark Document Complete?</td><td><select name="document_complete"><option selected>' . $document_complete . '</option>';
		if ($document_complete == 'Yes')
			echo '<option>No</option>';
		else
			echo '<option>Yes</option>';
		echo '</select></td></tr>';
		echo '<input type="hidden" name="field_count" value="' . $field_count . '">';
		echo '<input type="hidden" name="index_part_doc" value="' . $index_part_doc . '">';
		//need submit buttons...
		echo '<tr><td>&nbsp;</td><td colspan="2" style="text-align:left;"><input type="submit" name="subfields" value="Save Field Values"> &nbsp;&nbsp;&nbsp; ';
		echo '<button type="button" class="no_border_center"
		  onClick="javascript:location.href=\'contract_select_community_nhc.php?mytime=' . $mytime . '\';" >Cancel</button></td></tr>';

		echo '</form>';
		echo '<tr><td>&nbsp;</td></tr>';
		echo '<tr><td>&nbsp;</td></tr>';
		echo '<tr><td nowrap colspan="6">Server: ' . $_SERVER['SERVER_NAME'] . '</td></tr>';
		echo '</table>';
	}
	if (isset($stid))
		oci_free_statement($stid);
	if (isset($stid02))
		oci_free_statement($stid02);
	if (isset($stid2))
		oci_free_statement($stid2);
	if (isset($stid3))
		oci_free_statement($stid3);
	if ($db_conn)
		oci_close($db_conn);

}

function contract_nhc_doc_entry_post($get, $post)
{
	global $_SESSION, $e_serv;
	$mytime = time();
	$today = date('d-M-y');
	$userid = $_SESSION['userid'];
	$db_conn = db_connect($e_serv);
	// $post = $_POST;
	if (!$db_conn) {
		echo '<br><h2>Unable to connect to oracle database!';
		exit;
	} else {
		if (($userid == 'jeff.mckenzie@lennar.comzzz') || ($userid == 'niles.rowland@lennar.com')) {
			echo '<br />count: ' . $post['field_count'];
			echo '<pre>';
			print_r($post);
			print_r($_SESSION);
			echo '</pre>';
		}
		$division_jde = trim($post['division_jde']);
		$index_part_doc = $post['index_part_doc'];

		reset($_SESSION['field_array']);
		reset($_SESSION['field_array_type']);
		reset($_SESSION['field_array_modify']);
		reset($_SESSION['field_array_view_only']);
		reset($_SESSION['field_array_big_fatty']);

		$field_count = $post['field_count'];
		$division_jde = $post['division_jde'];
		$community_jde = $post['community_jde'];
		$customer_id = $post['customer_id'];
		$homesite = $post['homesite'];
		$index_part_doc = $post['index_part_doc'];
		$document_complete = $post['document_complete'];
		$_SESSION['sale_date_input'] = '';
		if (isset($post['SALEDATE'])) {
			$_SESSION['sale_date_input'] = htmlentities(stripslashes(trim($post['SALEDATE'])), ENT_QUOTES);
		}

		//added by mak 04/08/2010, to enable saving blank values from NHC
		$bl = 0;
		$_SESSION['save_blank_value'] = array();


		while ($bl <= $field_count) {
			$save_blank_value = 'save_blank_value' . $bl;
			$_SESSION['save_blank_value'][$bl] = '';
			if (isset($post[$save_blank_value])) {
				$_SESSION['save_blank_value'][$bl] = $post[$save_blank_value];
			}
			$bl++;
		}


		if ($document_complete == 'Yes') {
			$document_complete = 'Y';
		}
		else {
			$document_complete = 'N';
		}


		$qu_doc_comp = "select index_part from dj_customer_comm_home_docs where customer_id = '$customer_id' and
																		   division_jde = '$division_jde' and
																		   community_jde = '$community_jde' and
																		   index_part_doc = '$index_part_doc' and
																		   homesite 	= '$homesite' ";
		if ($userid == 'niles.rowland@lennar.com' && 1==1) {
			echo __FILE__.':'.__LINE__.' $qu_doc_comp: '.$qu_doc_comp.'<br>';
		}
		$index_part_dcchd = 0;
		$stid200 = oci_parse($db_conn, $qu_doc_comp);
		oci_execute($stid200);
		while ($row200 = oci_fetch_array($stid200, OCI_RETURN_NULLS + OCI_ASSOC)) {
			//here if doc is assigned...
			$index_part_dcchd = html_entity_decode(trim($row200['INDEX_PART']), ENT_QUOTES);
		}
		if ($index_part_dcchd == 0) //insert
		{
			$qu_insert = "insert into dj_customer_comm_home_docs (customer_id, index_part_doc, division_jde, community_jde, homesite, document_complete, version_num)
															values ('$customer_id', '$index_part_doc', '$division_jde', '$community_jde', '$homesite', '$document_complete', 0)";
		} else //update
		{
			$qu_insert = "update dj_customer_comm_home_docs set document_complete = '$document_complete' where index_part = '$index_part_dcchd' ";
		}
		if ($userid == 'niles.rowland@lennar.com' && 1==1) {
			echo __FILE__.':'.__LINE__.' $qu_insert: '.$qu_insert.'<br>';
		}
		$stid222 = oci_parse($db_conn, $qu_insert);
		oci_execute($stid222);

		$fatty_found = false;
		$first_time_thru = true;

		for ($i = 0; $i < $field_count; $i++) {
			$checked = '';
			$field_name = $_SESSION['field_array'][$i];
			$field_type = $_SESSION['field_array_type'][$i];
			$modify = $_SESSION['field_array_modify'][$i];
			$view_only = $_SESSION['field_array_view_only'][$i];
			$big_fatty = $_SESSION['field_array_big_fatty'][$i];
			$myvalue = '';
			if (($field_type == 'Text' or $field_type == 'Text Area') and isset($post[$field_name]))
				$myvalue = htmlentities(stripslashes($post[$field_name]), ENT_QUOTES);
			else if (isset($post[$field_name]))
				$myvalue = htmlentities(stripslashes(trim($post[$field_name])), ENT_QUOTES);
			$myvalue_new = '';
			if (isset($post[$field_name]))
				$myvalue_new = stripslashes(trim($post[$field_name]));
			$double_quote = '"';

			$found_dj_comm_home = null;
			$qu_check_dj_comm_home = "select index_part from DJ_CUSTOMER_COMM_HOME where CUSTOMER_ID='$customer_id' and DIVISION_JDE='$division_jde' and community_jde='$community_jde' and homesite='$homesite' and field_name='$field_name'";
			$stidchk = oci_parse($db_conn, $qu_check_dj_comm_home);
			oci_execute($stidchk);
			while ($rowchk = oci_fetch_array($stidchk, OCI_RETURN_NULLS + OCI_ASSOC)) {
				$found_dj_comm_home = $rowchk['INDEX_PART'];
			}
			oci_free_statement($stidchk);

			if ($userid == 'niles.rowland@lennar.com' && $field_name == 'SALEDATE') {

				echo __FILE__.':'.__LINE__.' $found_dj_comm_home: ';
				var_dump($found_dj_comm_home);
				echo '<br>qu_check_dj_comm_home: '.$qu_check_dj_comm_home.'<br>';
			}


			//check for credit card #
			if ($field_name == 'CREDITCARDNUMBER') {
				$myvalue = str_replace(" ", "", $myvalue);
				$myvalue = str_replace("-", "", $myvalue);
				$myvalue = str_replace("_", "", $myvalue);
				$myvalue = str_replace(".", "", $myvalue);

				/*					$lencc = strlen($myvalue)-4;
					$myvalue = substr_replace($myvalue, 'XXXXXXXXXXXX', 0, $lencc);
					$myvalue_new = substr_replace($myvalue_new, 'XXXXXXXXXXXX', 0, $lencc);
*/
				$lencc = strlen($myvalue) - 4;
				for ($cc = 0; $cc < $lencc; $cc++) {
					$myvalue = substr_replace($myvalue, 'X', $cc, 1);
					$myvalue_new = substr_replace($myvalue_new, 'X', $cc, 1);

				}

			}

			//check if the date field is TBD and if it is then set it to a blank value since
			// the report fails due to a TBD value,added 02/01/2011,madhav kolipaka


			if ($field_type == 'Date' and strtoupper($myvalue) == 'TBD') {
				$myvalue = '';

			}


			//added below for save blank value modifications;mak;05042010

			if ($_SESSION['save_blank_value'][$i] == 'on') {
				$blank_checked = 'y';
			} else {
				$blank_checked = '';
			}

			$dq = false;
			if (strpos($myvalue_new, $double_quote) === false) {
				$dq = false;
			}
			else {
				$dq = true;
			}

			if ($field_type == "Radio Button" or $field_type == "Picklist") {
				$pick_value = strtok($myvalue, "***-***");
				$myvalue = strtok("***-***");
				if ($field_name == 'FHAVAADDCB' and trim($myvalue) == '2') {
					$myvalue = '';
					$pick_value = '';
				}
			}


			if (isset($post[$field_name])) {
				if ($field_type == 'Checkbox') {
					if ($post[$field_name] == 'on') {
						$myvalue = 'Y';
					}
					else {
						$myvalue = $myvalue;
					}

				} else {
					if ($myvalue == '') {
						$myvalue = null;
						//echo '<br />NULL: '.$field_name;
					} else if ($field_type == 'Phone') {
						$myvalue = return_good_phone($myvalue);
					} else if ($field_type == 'Currency') {
						$myvalue = $myvalue;
						//	if($myvalue < 1 and $myvalue > 0)
						//		$myvalue = '';
						if (substr_count($myvalue, '.'))
							$myvalue = number_format(return_good_number($myvalue), 2, '.', '');
						else
							$myvalue = number_format(return_good_number($myvalue), 0, '', '');
					} else if ($field_type == 'Percentage') {
						//	if($myvalue < 1 and $myvalue > 0)
						//		$myvalue = '';
						$myvalue = return_good_number($myvalue);
					} else if ($field_type == 'Date') {
						if (strpos($myvalue, '/') != false) {
							$myvalue = check_my_date($myvalue, 'G');
						}
						else if (strpos(strtoupper($myvalue), 'JAN') != false or
							strpos(strtoupper($myvalue), 'FEB') != false or
							strpos(strtoupper($myvalue), 'MAR') != false or
							strpos(strtoupper($myvalue), 'APR') != false or
							strpos(strtoupper($myvalue), 'MAY') != false or
							strpos(strtoupper($myvalue), 'JUN') != false or
							strpos(strtoupper($myvalue), 'JUL') != false or
							strpos(strtoupper($myvalue), 'AUG') != false or
							strpos(strtoupper($myvalue), 'SEP') != false or
							strpos(strtoupper($myvalue), 'OCT') != false or
							strpos(strtoupper($myvalue), 'NOV') != false or
							strpos(strtoupper($myvalue), 'DEC') != false) {
							//	if($field_name == 'ESTCLDT')
							//		echo '<br />estcldt b4 (O): '.$myvalue;
							$myvalue = check_my_date($myvalue, 'O');
							//	if($field_name == 'ESTCLDT')
							//		echo '<br />estcldt after (O): '.$myvalue;
						} else if (strpos($myvalue, '-', 4) != false)
							$myvalue = check_my_date($myvalue, 'M');

					} else if ($field_type == 'Integer') {
						//	if($myvalue < 1 and $myvalue > 0)
						//		$myvalue = '';
						$myvalue = return_good_number_orig($myvalue);
					}
				}
			} else if ($field_type == 'Checkbox') {
				$myvalue = 'N';
			}

			if ($modify and is_null($myvalue)) //found data entered...
			{
				$myvalue_saved = $myvalue;
				if ($field_type == 'Date') {
					$myvalue = return_oracle_date($myvalue);
				}
				if ($field_name != 'BUYER2_FIRSTNAME' and $field_name != 'BUYER1_FIRSTNAME' and $field_name != 'BUYER2_LASTNAME' and
					$field_name != 'BUYER3_FIRSTNAME' and $field_name != 'BUYER3_LASTNAME' and
					$field_name != 'BUYER4_FIRSTNAME' and $field_name != 'BUYER4_LASTNAME' and
					$field_name != 'BUYER2_ADDRESS' and $field_name != 'BUYER2_CITY' and
					$field_name != 'BUYER2_STATE' and $field_name != 'BUYER2_ZIP' and
					$field_name != 'BUYER2_MIDDLENAME' and $field_name != 'BUYER2WORKPHONE' and
					$field_name != 'BUYER2HOMEPHONE' and $field_name != 'BUYER2EMAIL' and
					$field_name != 'BUYER3_MIDDLENAME' and $field_name != 'BUYER3WORKPHONE' and
					$field_name != 'BUYER3HOMEPHONE' and $field_name != 'BUYER3EMAIL' and
					$field_name != 'BUYER4_MIDDLENAME' and $field_name != 'BUYER4WORKPHONE' and
					$field_name != 'BUYER4HOMEPHONE' and $field_name != 'BUYER4EMAIL' and
					$field_name != 'BUYER3_ADDRESS' and $field_name != 'BUYER3_CITY' and
					$field_name != 'BUYER3_STATE' and $field_name != 'BUYER3_ZIP' and
					$field_name != 'BUYER4_ADDRESS' and $field_name != 'BUYER4_CITY' and
					$field_name != 'BUYER4_STATE' and $field_name != 'BUYER4_ZIP'
				) {
					$qu_cust = "update dj_customer_comm_home set value 			= null,
																 view_only	 	= '$view_only',
																 modified_by 	= '$userid',
																 modified_date 	= '$today',
																 blank_checked  = '$blank_checked'
															where customer_id 	= '$customer_id' and
																  division_jde 	= '$division_jde' and
																  community_jde = '$community_jde' and
																  homesite 		= '$homesite' and
																  field_name 	= '$field_name' ";
					$stid2 = oci_parse($db_conn, $qu_cust);
					oci_execute($stid2);
				}
				$myvalue = $myvalue_saved;
				$posb2 = strpos($field_name, 'UYER2_');
				if ($posb2 !== false and 1 == 2) {
					echo '<br>Here ***1 ' . $qu_cust;
				}
			} else if ($modify && $found_dj_comm_home) //found data entered...
			{
				if ($userid == 'niles.rowland@lennar.com' and ($field_name == 'SALEDATE')) {
					echo '<br>'.__FILE__.':'.__LINE__.' found_dj_comm_home: ' . $found_dj_comm_home;
				}
				$myvalue_saved = $myvalue;
				if ($field_type == 'Date') {
					$myvalue = return_oracle_date($myvalue);
				}
				if ($field_name != 'BUYER1_FIRSTNAME' and $field_name != 'BUYER2_FIRSTNAME' and $field_name != 'BUYER2_LASTNAME' and
					$field_name != 'BUYER3_FIRSTNAME' and $field_name != 'BUYER3_LASTNAME' and
					$field_name != 'BUYER4_FIRSTNAME' and $field_name != 'BUYER4_LASTNAME' and
					$field_name != 'BUYER2_ADDRESS' and $field_name != 'BUYER2_CITY' and
					$field_name != 'BUYER2_STATE' and $field_name != 'BUYER2_ZIP' and
					$field_name != 'BUYER2_MIDDLENAME' and $field_name != 'BUYER2WORKPHONE' and
					$field_name != 'BUYER2HOMEPHONE' and $field_name != 'BUYER2EMAIL' and
					$field_name != 'BUYER3_MIDDLENAME' and $field_name != 'BUYER3WORKPHONE' and
					$field_name != 'BUYER3HOMEPHONE' and $field_name != 'BUYER3EMAIL' and
					$field_name != 'BUYER4_MIDDLENAME' and $field_name != 'BUYER4WORKPHONE' and
					$field_name != 'BUYER4HOMEPHONE' and $field_name != 'BUYER4EMAIL' and
					$field_name != 'BUYER3_ADDRESS' and $field_name != 'BUYER3_CITY' and
					$field_name != 'BUYER3_STATE' and $field_name != 'BUYER3_ZIP' and
					$field_name != 'BUYER4_ADDRESS' and $field_name != 'BUYER4_CITY' and
					$field_name != 'BUYER4_STATE' and $field_name != 'BUYER4_ZIP'
				) {
					$qu_cust = "update dj_customer_comm_home set value 			= '$myvalue',
																	view_only	 	= '$view_only',
																	modified_by 	= '$userid',
																	modified_date 	= '$today',
																	blank_checked  = '$blank_checked'
																where customer_id 	= '$customer_id' and
																	division_jde 	= '$division_jde' and
																	community_jde = '$community_jde' and
																	homesite 		= '$homesite' and
																	field_name 	= '$field_name' ";
					$stid2 = oci_parse($db_conn, $qu_cust);
					oci_execute($stid2);
					if ($userid == 'niles.rowland@lennar.com' and ($field_name == 'BLOCK' or $field_name == 'SALEDATE')) {
						echo '<br>'.__FILE__.':'.__LINE__.' qu_cust: ' . $qu_cust;
					}

				}
				$myvalue = $myvalue_saved;
				$posb2 = strpos($field_name, 'UYER2_');
				if ($posb2 !== false and 1 == 2) {
					echo '<br>Here ***2 ' . $qu_cust;
				}
			} else //need to insert
			{
				$myvalue_saved = $myvalue;
				if ($field_type == 'Date') {
					$myvalue = return_oracle_date($myvalue);
				}
				$qu_cust = "insert into dj_customer_comm_home (customer_id, division_jde, community_jde, homesite, value, field_name, created_by, created_date, modified_by, modified_date, blank_checked)
														   values ('$customer_id', '$division_jde', '$community_jde', '$homesite', '$myvalue', '$field_name', '$userid', '$today', '$userid', '$today','$blank_checked')";
				if ($userid == 'niles.rowland@lennar.com' and ($field_name == 'BLOCK' or $field_name == 'SALEDATE')) {
					echo '<br>'.__FILE__.':'.__LINE__.' qu_cust: ' . $qu_cust;
				}
				$stid2 = oci_parse($db_conn, $qu_cust);
				$myres = oci_execute($stid2);
				$myvalue = $myvalue_saved;
				if ($userid == 'niles.rowland@lennar.com' and ($field_name == 'BLOCK' or $field_name == 'SALEDATE')) {
					echo '<br>'.__FILE__.':'.__LINE__.' myres: ' . $myres;
				}
				$posb2 = strpos($field_name, 'UYER2_');
				if ($posb2 !== false and 1 == 2) {
					echo '<br>Here ***3 ' . $qu_cust;
				}
			}
			//deal with apostrophes
			$mynewvalue = html_entity_decode(stripslashes(trim($myvalue)), ENT_QUOTES);
			$mynewvalue = str_replace("'", "''", $mynewvalue);
			//insert or update the dj_big_fatty record for this customer_id, community_jde, homesite, document *****************
			//check to see if the dj_big_fatty record exists...
			if ($first_time_thru) {
				$qu_fatty = "select * from dj_big_fatty where division_jde = '$division_jde' and
																  community_jde = '$community_jde' and
																  homesite 		= '$homesite' and
																  customer_id 	= '$customer_id' ";

				$stid3 = oci_parse($db_conn, $qu_fatty);
				oci_execute($stid3);


				//$mynewvalue = str_replace('"', '\"', $mynewvalue);

				while ($row3 = oci_fetch_array($stid3, OCI_RETURN_NULLS + OCI_ASSOC)) {

					//here if doc is assigned...
					$fatty_found = true;
					//need the index part...
					$djbf_index_part = $row3['INDEX_PART'];
				}
				$first_time_thru = false;
			}


			if ($field_type == "Radio Button" or $field_type == "Picklist") //store an index for the radio button or pick list (0,1,2...)
			{
				$mynewvalue = $pick_value;
				if ($field_name == 'FHAVAADDCB' and trim($mynewvalue) == '2') {
					$mynewvalue = '';
				}
			}

			if ($fatty_found and $field_name != '') {
				if ($big_fatty == 'DJ_BIG_FATTY') {
					$qu_fatty_u = "update dj_big_fatty set $field_name = '$mynewvalue'
														where division_jde = '$division_jde' and
															  community_jde = '$community_jde' and
															  homesite 	   = '$homesite' and
															  customer_id   = '$customer_id'  ";
				} else if ($big_fatty == 'DJ_BIG_FATTY2') {
					$qu_fatty_u = "update dj_big_fatty2 set $field_name = '$mynewvalue' where index_part = '$djbf_index_part' ";
				}
				if (1 == 2 and $field_type == "Radio Button" or $field_type == "Picklist") {
					if ($userid == 'jeff.mckenzie@lennar.com' and 1 == 2)
						echo '<br />' . $qu_fatty_u;
				}
				if ($field_name != '') {
					$stid33 = oci_parse($db_conn, $qu_fatty_u);
					if ($field_name == 'SALEDATE') {
						echo '<br />'.__FILE__.':'.__LINE__.' ********bigfatt : ' . $qu_fatty_u . ' Field: ' . $field_name;
					}
					if (!oci_execute($stid33)) {
						//problem updating dj_big_fatty
						mail('jeff.mckenzie@lennar.com', 'Prob Updating dj_big_fatty or 2', $qu_fatty_u, 'From: Jeff DJ <jeff.mckenzie@lennar.com>');
					}
				}
			}

			if (!$fatty_found) {
				if ($big_fatty == 'DJ_BIG_FATTY') {
					$qu_fatty_i = "insert into dj_big_fatty  (division_jde, community_jde, homesite, customer_id, $field_name)
													values ('$division_jde', '$community_jde', '$homesite', '$customer_id', '$mynewvalue' )";
				} else if ($big_fatty == 'DJ_BIG_FATTY2') {
					$qu_fatty_i = "insert into dj_big_fatty  (division_jde, community_jde, homesite, customer_id)
													values ('$division_jde', '$community_jde', '$homesite', '$customer_id')";
				}
				$stid33 = oci_parse($db_conn, $qu_fatty_i);
				oci_execute($stid33);

				//now look up the dj_big_fatty and get the index_part...
				$qu_fatty = "select * from dj_big_fatty where division_jde = '$division_jde' and
																  community_jde = '$community_jde' and
																  homesite 		= '$homesite' and
																  customer_id 	= '$customer_id' ";

				$stid3 = oci_parse($db_conn, $qu_fatty);
				oci_execute($stid3);
				while ($row3 = oci_fetch_array($stid3, OCI_RETURN_NULLS + OCI_ASSOC)) {
					$djbf_index_part = $row3['INDEX_PART'];
				}

				if ($big_fatty == 'DJ_BIG_FATTY2') {
					$qu_fatty_i2 = "insert into dj_big_fatty2  (index_part, $field_name)
													values ('$djbf_index_part', '$mynewvalue' )";
				} else if ($big_fatty == 'DJ_BIG_FATTY') {
					$qu_fatty_i2 = "insert into dj_big_fatty2  (index_part)
													values ('$djbf_index_part')";
				}
				$stid33 = oci_parse($db_conn, $qu_fatty_i);
				oci_execute($stid33);

				//echo '<br>'.$qu_fatty_i;
			}

		} //end of for loop

//***************
//echo '<br>Here 1: '.$field_name;
		if (isset($post['buyer1_firstname_oncont'])) {
			if ($post['buyer1_firstname_oncont'] == 'on')
				$buyer1_firstname_oncont = 'Y';
			else {
				$buyer1_firstname_oncont = 'N';
				$buyer1_firstname_oncont = 'Y';
				$myvalue = '';
			}

		} else {
			$buyer1_firstname_oncont = 'N';
			$buyer1_firstname_oncont = 'Y'; //*** force buyer 1 to ALWAYS be on contract
			$myvalue = '';
		}

		$qu_up_buyer = "update dj_customer_comm_home set value = '$buyer1_firstname_oncont'
																			where customer_id = '$customer_id' and
																			   division_jde		= '$division_jde' and
																			   community_jde	= '$community_jde' and
																			   homesite 		= '$homesite' and
																			   field_name		= 'BUYER1_FIRSTNAME_ONCONT' ";
		$stid02 = oci_parse($db_conn, $qu_up_buyer);
		oci_execute($stid02);

		if (isset($post['buyer2_firstname_oncont'])) {
			if ($post['buyer2_firstname_oncont'] == 'on') {
				$buyer2_firstname_oncont = 'Y';
				$_SESSION['BUYER2_FIRSTNAME_ONCONT'] = 'Y';

				$fname = '';
				$mname = '';
				$lname = '';
				$address = '';
				$city = '';
				$state = '';
				$zip = '';
				$workph = '';
				$homeph = '';
				$email = '';

				$qu_buyers = "select field_name, value from dj_customer_comm_home where customer_id = '$customer_id' and
																				   division_jde		= '$division_jde' and
																				   community_jde	= '$community_jde' and
																				   homesite 		= '$homesite' and
																				   (field_name		= 'BUYER2_CITY' or
																					field_name 		= 'BUYER2_ADDRESS' or
																					field_name 		= 'BUYER2_FIRSTNAME' or
																					field_name 		= 'BUYER2_MIDDLENAME' or
																					field_name 		= 'BUYER2_LASTNAME' or
																					field_name 		= 'BUYER2WORKPHONE' or
																					field_name 		= 'BUYER2HOMEPHONE' or
																					field_name 		= 'BUYER2_STATE' or
																					field_name      = 'BUYER2EMAIL' or
																					field_name 		= 'BUYER2_ZIP')
																				   ";
				$stid02 = oci_parse($db_conn, $qu_buyers);
				oci_execute($stid02);
				while ($rowvalue22 = oci_fetch_array($stid02, OCI_RETURN_NULLS + OCI_ASSOC)) {
					$field_name = $rowvalue22['FIELD_NAME'];
					$value = $rowvalue22['VALUE'];
					if ($field_name == 'BUYER2_FIRSTNAME') {
						$_SESSION['BUYER2_FIRSTNAME'] = $value;
						$fname = $value;
					} else if ($field_name == 'BUYER2_MIDDLENAME') {
						$_SESSION['BUYER2_MIDDLENAME'] = $value;
						$mname = $value;
					} else if ($field_name == 'BUYER2_LASTNAME') {
						$_SESSION['BUYER2_LASTNAME'] = $value;
						$lname = $value;
					} else if ($field_name == 'BUYER2_ADDRESS') {
						$_SESSION['BUYER2_ADDRESS'] = $value;
						$address = $value;
					} else if ($field_name == 'BUYER2_CITY') {
						$_SESSION['BUYER2_CITY'] = $value;
						$city = $value;
					} else if ($field_name == 'BUYER2_STATE') {
						$_SESSION['BUYER2_STATE'] = $value;
						$state = $value;
					} else if ($field_name == 'BUYER2_ZIP') {
						$_SESSION['BUYER2_ZIP'] = $value;
						$zip = $value;
					} else if ($field_name == 'BUYER2WORKPHONE') {
						$_SESSION['BUYER2WORKPHONE'] = $value;
						$workph = $value;
					} else if ($field_name == 'BUYER2HOMEPHONE') {
						$_SESSION['BUYER2HOMEPHONE'] = $value;
						$homeph = $value;
					} else if ($field_name == 'BUYER2EMAIL') {
						$_SESSION['BUYER2EMAIL'] = $value;
						$email = $value;
					}
				}
				$qu_up_dj_bf2 = "update dj_big_fatty set BUYER2_FIRSTNAME    = '$fname',
															 BUYER2_MIDDLENAME   = '$mname',
															 BUYER2_LASTNAME     = '$lname',
															 BUYER2_ADDRESS 	 = '$address',
															 BUYER2_CITY    	 = '$city', 
															 BUYER2_STATE   	 = '$state', 
															 BUYER2_ZIP     	 = '$zip', 
															 BUYER2WORKPHONE     = '$workph', 
															 BUYER2HOMEPHONE     = '$homeph',
															 BUYER2EMAIL		 = '$email'
									 where customer_id = '$customer_id' and
										   division_jde		= '$division_jde' and
										   community_jde	= '$community_jde' and
										   homesite 		= '$homesite' ";
				$stidbf2 = oci_parse($db_conn, $qu_up_dj_bf2);
				oci_execute($stidbf2);

//echo '<br>Here 2: '.$qu_up_dj_bf2;
			} else {
				$buyer2_firstname_oncont = 'N';
				$myvalue = '';
				$_SESSION['BUYER2_FIRSTNAME_ONCONT'] = 'N';

				$qu_up_dj_bf2 = "update dj_big_fatty set BUYER2_FIRSTNAME = '', 
															 BUYER2_MIDDLENAME = '', 
															 BUYER2_LASTNAME = '', 
															 BUYER2_ADDRESS = '', 
															 BUYER2_CITY = '', 
															 BUYER2_STATE = '', 
															 BUYER2_ZIP     	 = '', 
															 BUYER2WORKPHONE     = '', 
															 BUYER2HOMEPHONE     = '', 
															 BUYER2EMAIL     = '' 
									where customer_id = '$customer_id' and 
										  division_jde = '$division_jde' and 
										  community_jde = '$community_jde' and 
										  homesite = '$homesite' ";
				$stid02 = oci_parse($db_conn, $qu_up_dj_bf2);
				oci_execute($stid02);
//echo '<br>Here 3: '.$qu_up_dj_bf2;
			}

		} else {
			$buyer2_firstname_oncont = 'N';
			$myvalue = '';
			$_SESSION['BUYER2_FIRSTNAME_ONCONT'] = 'N';
			$qu_up_dj_bf2 = "update dj_big_fatty set BUYER2_FIRSTNAME = '', 
														 BUYER2_MIDDLENAME = '', 
														 BUYER2_LASTNAME = '', 
														 BUYER2_ADDRESS = '', 
														 BUYER2_CITY = '', 
														 BUYER2_STATE = '', 
														 BUYER2_ZIP     	 = '', 
														 BUYER2WORKPHONE     = '', 
														 BUYER2HOMEPHONE     = '', 
														 BUYER2EMAIL     = '' 
									where customer_id = '$customer_id' and 
										  division_jde = '$division_jde' and 
										  community_jde = '$community_jde' and 
										  homesite = '$homesite' ";
			$stid02 = oci_parse($db_conn, $qu_up_dj_bf2);
			oci_execute($stid02);
//echo '<br>Here 4: '.$qu_up_dj_bf2;
		}

		$qu_up_buyer = "update dj_customer_comm_home set value = '$buyer2_firstname_oncont'
																	where customer_id = '$customer_id' and
																	   division_jde		= '$division_jde' and
																	   community_jde	= '$community_jde' and
																	   homesite 		= '$homesite' and
																	   field_name		= 'BUYER2_FIRSTNAME_ONCONT' ";
		$stid02 = oci_parse($db_conn, $qu_up_buyer);
		oci_execute($stid02);

		//do BUYER3
		if (isset($post['buyer3_firstname_oncont'])) {
			if ($post['buyer3_firstname_oncont'] == 'on') {
				$buyer3_firstname_oncont = 'Y';
				$_SESSION['BUYER3_FIRSTNAME_ONCONT'] = 'Y';

				$fname = '';
				$mname = '';
				$lname = '';
				$workph = '';
				$homeph = '';
				$email = '';

				$qu_buyers = "select field_name, value from dj_customer_comm_home where customer_id = '$customer_id' and
																					   division_jde		= '$division_jde' and
																					   community_jde	= '$community_jde' and
																					   homesite 		= '$homesite' and
																					   (field_name 		= 'BUYER3_FIRSTNAME' or
																						field_name 		= 'BUYER3_MIDDLENAME' or
																						field_name 		= 'BUYER3_LASTNAME' or
																						field_name 		= 'BUYER3WORKPHONE' or
																						field_name 		= 'BUYER3HOMEPHONE' or
																						field_name      = 'BUYER3EMAIL' or
																						field_name      = 'BUYER3_ADDRESS' or
																						field_name      = 'BUYER3_CITY' or
																						field_name      = 'BUYER3_STATE' or
																						field_name      = 'BUYER3_ZIP' 
																						)
																					   ";
				$stid02 = oci_parse($db_conn, $qu_buyers);
				oci_execute($stid02);
				while ($rowvalue22 = oci_fetch_array($stid02, OCI_RETURN_NULLS + OCI_ASSOC)) {
					$field_name = $rowvalue22['FIELD_NAME'];
					$value = $rowvalue22['VALUE'];
					if ($field_name == 'BUYER3_FIRSTNAME') {
						$_SESSION['BUYER3_FIRSTNAME'] = $value;
						$fname = $value;
					} else if ($field_name == 'BUYER3_MIDDLENAME') {
						$_SESSION['BUYER3_MIDDLENAME'] = $value;
						$mname = $value;
					} else if ($field_name == 'BUYER3_LASTNAME') {
						$_SESSION['BUYER3_LASTNAME'] = $value;
						$lname = $value;
					} else if ($field_name == 'BUYER3WORKPHONE') {
						$_SESSION['BUYER3WORKPHONE'] = $value;
						$workph = $value;
					} else if ($field_name == 'BUYER3HOMEPHONE') {
						$_SESSION['BUYER3HOMEPHONE'] = $value;
						$homeph = $value;
					} else if ($field_name == 'BUYER3EMAIL') {
						$_SESSION['BUYER3EMAIL'] = $value;
						$email = $value;
					} else if ($field_name == 'BUYER3_ADDRESS') {
						$_SESSION['BUYER3_ADDRESS'] = $value;
						$address = $value;
					} else if ($field_name == 'BUYER3_CITY') {
						$_SESSION['BUYER3_CITY'] = $value;
						$city = $value;
					} else if ($field_name == 'BUYER3_STATE') {
						$_SESSION['BUYER3_STATE'] = $value;
						$state = $value;
					} else if ($field_name == 'BUYER3_ZIP') {
						$_SESSION['BUYER3_ZIP'] = $value;
						$zip = $value;
					}
				}
				$qu_up_dj_bf2 = "update dj_big_fatty set BUYER3_FIRSTNAME    = '$fname',
																 BUYER3_MIDDLENAME   = '$mname',
																 BUYER3_LASTNAME     = '$lname',
																 BUYER3WORKPHONE     = '$workph', 
																 BUYER3HOMEPHONE     = '$homeph',
																 BUYER3EMAIL		 = '$email'
										 where customer_id = '$customer_id' and
											   division_jde		= '$division_jde' and
											   community_jde	= '$community_jde' and
											   homesite 		= '$homesite' ";
				$stidbf2 = oci_parse($db_conn, $qu_up_dj_bf2);
				oci_execute($stidbf2);

				$qu_up_dj_bf2 = "update dj_big_fatty2 set BUYER3_CITY    = '$city',
																  BUYER3_STATE   = '$state',
																  BUYER3_ADDRESS = '$address',
																  BUYER3_ZIP     = '$zip' 
										 where index_part = '$djbf_index_part' ";
				$stidbf2 = oci_parse($db_conn, $qu_up_dj_bf2);
				oci_execute($stidbf2);
			} else {
				$buyer3_firstname_oncont = 'N';
				$myvalue = '';
				$_SESSION['BUYER3_FIRSTNAME_ONCONT'] = 'N';
				$qu_up_dj_bf2 = "update dj_big_fatty set BUYER3_FIRSTNAME = '', 
																 BUYER3_MIDDLENAME = '', 
																 BUYER3_LASTNAME = '', 
																 BUYER3WORKPHONE     = '', 
																 BUYER3HOMEPHONE     = '', 
																 BUYER3EMAIL     = '' 
											where customer_id = '$customer_id' and 
												  division_jde = '$division_jde' and 
												  community_jde = '$community_jde' and 
												  homesite = '$homesite' ";
				$stid02 = oci_parse($db_conn, $qu_up_dj_bf2);
				oci_execute($stid02);

				$qu_up_dj_bf2 = "update dj_big_fatty2 set BUYER3_ADDRESS = '', 
																  BUYER3_CITY    = '', 
																  BUYER3_STATE   = '', 
																  BUYER3_ZIP     = '' 
											where index_part = '$djbf_index_part' ";
				$stid02 = oci_parse($db_conn, $qu_up_dj_bf2);
				oci_execute($stid02);
			}

		} else {
			$buyer3_firstname_oncont = 'N';
			$myvalue = '';
			$_SESSION['BUYER3_FIRSTNAME_ONCONT'] = 'N';
			$qu_up_dj_bf2 = "update dj_big_fatty set BUYER3_FIRSTNAME = '', 
															 BUYER3_MIDDLENAME = '', 
															 BUYER3_LASTNAME = '', 
															 BUYER3WORKPHONE     = '', 
															 BUYER3HOMEPHONE     = '', 
															 BUYER3EMAIL     = '' 
										where customer_id = '$customer_id' and 
											  division_jde = '$division_jde' and 
											  community_jde = '$community_jde' and 
											  homesite = '$homesite' ";
			$stid02 = oci_parse($db_conn, $qu_up_dj_bf2);
			oci_execute($stid02);

			$qu_up_dj_bf2 = "update dj_big_fatty2 set BUYER3_ADDRESS = '', 
															  BUYER3_CITY    = '', 
															  BUYER3_STATE   = '', 
															  BUYER3_ZIP     = '' 
										where index_part = '$djbf_index_part' ";
			$stid02 = oci_parse($db_conn, $qu_up_dj_bf2);
			oci_execute($stid02);
		}

		$qu_up_buyer = "update dj_customer_comm_home set value = '$buyer3_firstname_oncont'
																		where customer_id = '$customer_id' and
																		   division_jde		= '$division_jde' and
																		   community_jde	= '$community_jde' and
																		   homesite 		= '$homesite' and
																		   field_name		= 'BUYER3_FIRSTNAME_ONCONT' ";
		$stid02 = oci_parse($db_conn, $qu_up_buyer);
		oci_execute($stid02);


		// do BUYER4
		if (isset($post['buyer4_firstname_oncont'])) {
			if ($post['buyer4_firstname_oncont'] == 'on') {
				$buyer4_firstname_oncont = 'Y';
				$_SESSION['BUYER4_FIRSTNAME_ONCONT'] = 'Y';

				$fname = '';
				$mname = '';
				$lname = '';
				$workph = '';
				$homeph = '';
				$email = '';

				$qu_buyers = "select field_name, value from dj_customer_comm_home where customer_id = '$customer_id' and
																					   division_jde		= '$division_jde' and
																					   community_jde	= '$community_jde' and
																					   homesite 		= '$homesite' and
																					   (field_name 		= 'BUYER4_FIRSTNAME' or
																						field_name 		= 'BUYER4_MIDDLENAME' or
																						field_name 		= 'BUYER4_LASTNAME' or
																						field_name 		= 'BUYER4WORKPHONE' or
																						field_name 		= 'BUYER4HOMEPHONE' or
																						field_name      = 'BUYER4EMAIL' or
																						field_name      = 'BUYER3_ADDRESS' or
																						field_name      = 'BUYER3_CITY' or
																						field_name      = 'BUYER3_STATE' or
																						field_name      = 'BUYER3_ZIP' 
																						)
																					   ";
				$stid02 = oci_parse($db_conn, $qu_buyers);
				oci_execute($stid02);
				while ($rowvalue22 = oci_fetch_array($stid02, OCI_RETURN_NULLS + OCI_ASSOC)) {
					$field_name = $rowvalue22['FIELD_NAME'];
					$value = $rowvalue22['VALUE'];
					if ($field_name == 'BUYER4_FIRSTNAME') {
						$_SESSION['BUYER4_FIRSTNAME'] = $value;
						$fname = $value;
					} else if ($field_name == 'BUYER4_MIDDLENAME') {
						$_SESSION['BUYER4_MIDDLENAME'] = $value;
						$mname = $value;
					} else if ($field_name == 'BUYER4_LASTNAME') {
						$_SESSION['BUYER4_LASTNAME'] = $value;
						$lname = $value;
					} else if ($field_name == 'BUYER4WORKPHONE') {
						$_SESSION['BUYER4WORKPHONE'] = $value;
						$workph = $value;
					} else if ($field_name == 'BUYER4HOMEPHONE') {
						$_SESSION['BUYER4HOMEPHONE'] = $value;
						$homeph = $value;
					} else if ($field_name == 'BUYER4EMAIL') {
						$_SESSION['BUYER4EMAIL'] = $value;
						$email = $value;
					} else if ($field_name == 'BUYER4_ADDRESS') {
						$_SESSION['BUYER4_ADDRESS'] = $value;
						$address = $value;
					} else if ($field_name == 'BUYER4_CITY') {
						$_SESSION['BUYER4_CITY'] = $value;
						$city = $value;
					} else if ($field_name == 'BUYER4_STATE') {
						$_SESSION['BUYER4_STATE'] = $value;
						$state = $value;
					} else if ($field_name == 'BUYER4_ZIP') {
						$_SESSION['BUYER4_ZIP'] = $value;
						$zip = $value;
					}
				}
				$qu_up_dj_bf2 = "update dj_big_fatty set BUYER4_FIRSTNAME    = '$fname',
																 BUYER4_MIDDLENAME   = '$mname',
																 BUYER4_LASTNAME     = '$lname',
																 BUYER4WORKPHONE     = '$workph', 
																 BUYER4HOMEPHONE     = '$homeph',
																 BUYER4EMAIL		 = '$email'
										 where customer_id = '$customer_id' and
											   division_jde		= '$division_jde' and
											   community_jde	= '$community_jde' and
											   homesite 		= '$homesite' ";
				$stidbf2 = oci_parse($db_conn, $qu_up_dj_bf2);
				oci_execute($stidbf2);
				$qu_up_dj_bf2 = "update dj_big_fatty2 set BUYER4_CITY    = '$city',
																  BUYER4_STATE   = '$state',
																  BUYER4_ADDRESS = '$address',
																  BUYER4_ZIP     = '$zip' 
										 where index_part = '$djbf_index_part' ";
				$stidbf2 = oci_parse($db_conn, $qu_up_dj_bf2);
				oci_execute($stidbf2);
			} else {
				$buyer4_firstname_oncont = 'N';
				$myvalue = '';
				$_SESSION['BUYER4_FIRSTNAME_ONCONT'] = 'N';
				$qu_up_dj_bf2 = "update dj_big_fatty set BUYER4_FIRSTNAME = '', 
																 BUYER4_MIDDLENAME = '', 
																 BUYER4_LASTNAME = '', 
																 BUYER4WORKPHONE     = '', 
																 BUYER4HOMEPHONE     = '', 
																 BUYER4EMAIL     = '' 
											where customer_id = '$customer_id' and 
												  division_jde = '$division_jde' and 
												  community_jde = '$community_jde' and 
												  homesite = '$homesite' ";
				$stid02 = oci_parse($db_conn, $qu_up_dj_bf2);
				oci_execute($stid02);
				$qu_up_dj_bf2 = "update dj_big_fatty2 set BUYER4_ADDRESS = '', 
																  BUYER4_CITY    = '', 
																  BUYER4_STATE   = '', 
																  BUYER4_ZIP     = '' 
											where index_part = '$djbf_index_part' ";
				$stid02 = oci_parse($db_conn, $qu_up_dj_bf2);
				oci_execute($stid02);
			}

		} else {
			$buyer4_firstname_oncont = 'N';
			$myvalue = '';
			$_SESSION['BUYER4_FIRSTNAME_ONCONT'] = 'N';
			$qu_up_dj_bf2 = "update dj_big_fatty set BUYER4_FIRSTNAME = '', 
															 BUYER4_MIDDLENAME = '', 
															 BUYER4_LASTNAME = '', 
															 BUYER4WORKPHONE     = '', 
															 BUYER4HOMEPHONE     = '', 
															 BUYER4EMAIL     = '' 
										where customer_id = '$customer_id' and 
											  division_jde = '$division_jde' and 
											  community_jde = '$community_jde' and 
											  homesite = '$homesite' ";
			$stid02 = oci_parse($db_conn, $qu_up_dj_bf2);
			oci_execute($stid02);

			$qu_up_dj_bf2 = "update dj_big_fatty2 set BUYER4_ADDRESS = '', 
															  BUYER4_CITY    = '', 
															  BUYER4_STATE   = '', 
															  BUYER4_ZIP     = '' 
										where index_part = '$djbf_index_part' ";
			$stid02 = oci_parse($db_conn, $qu_up_dj_bf2);
			oci_execute($stid02);

		}

		$qu_up_buyer = "update dj_customer_comm_home set value = '$buyer4_firstname_oncont'
																		where customer_id = '$customer_id' and
																		   division_jde		= '$division_jde' and
																		   community_jde	= '$community_jde' and
																		   homesite 		= '$homesite' and
																		   field_name		= 'BUYER4_FIRSTNAME_ONCONT' ";
		$stid02 = oci_parse($db_conn, $qu_up_buyer);
		oci_execute($stid02);

//***************
	}

	if (isset($stid200))
		oci_free_statement($stid200);
	if (isset($stid222))
		oci_free_statement($stid222);
	if (isset($stid02))
		oci_free_statement($stid02);
	if (isset($stid2))
		oci_free_statement($stid2);
	if (isset($stid3))
		oci_free_statement($stid3);
	if (isset($stid33))
		oci_free_statement($stid33);
	if (isset($stidbf2))
		oci_free_statement($stidbf2);

	if ($db_conn)
		oci_close($db_conn);

	if (($userid == 'jeff.mckenzie@lennar.com' || $userid == 'niles.rowland@lennar.com') and 1 == 2) {
		echo '<br>POST';
		echo '<pre>';
		print_r($_POST);
		print_r($_SESSION);
		echo '</pre>';
		// exit;
	}

	$link = 'contract_select_community_nhc.php?index_part=' . $index_part_doc . '&edit_now=N';

	print ("<script language=\"JavaScript\">\n");
	print ("redirecturl = \"" . $link . "\";\n");
	print ("</script>");

//add redirect here
			if(($userid != 'jeff.mckenzie@lennar.comzzz' && $userid != 'niles.rowland@lennar.com') || 1==1) {
?>

			<script language="JavaScript">

				  window.location = redirecturl;
			</script>
<?php
			}
}


function contract_select_community_nhc($get, $post)
{
	global $_SESSION, $e_serv, $crmdomain, $oracle_proxy;
	$mytime = time();

	if (isset($_SESSION['customer_id']) and $_SESSION['customer_id'] != '') {
		$db_conn = db_connect($e_serv);
		if (!$db_conn) {
			echo '<br><h2>Unable to connect to oracle database!';
			exit;
		} else {
			$corp_access = 'N';
			$index_part_dj = $_SESSION['djbf_index_part'];
			if (isset($_SESSION['corp_access']))
				$corp_access = $_SESSION['corp_access'];
			$division_jde = $_SESSION['division_jde_selected'];
			$community_jde = $_SESSION['community_jde_selected'];
			$customer_id = $_SESSION['customer_id'];
			$qu_reg = "select uamc, division, region_jde, esign_contracts from dj_division where division_jde = '$division_jde' ";
			//echo '<br />'.$qu_reg;
//get homesite from integration...
			$homesite = $_SESSION['homesite_selected'];
			if (isset($_SESSION['primary_opportunity_id']))
				$opportunity_id = $_SESSION['primary_opportunity_id'];
			else
				$opportunity_id = '';

			$contact_id = $_SESSION['primary_contact_id'];

			if (isset($_SESSION['SFORCE']) and $_SESSION['SFORCE'] == 'Y' and $_SESSION['nocrm'] != 'yes') {
				if ((!isset($instance_url) or $instance_url == '') and isset($_SESSION['instance_url']))
					$instance_url = $_SESSION['instance_url'];
				if ((!isset($access_token) or $access_token == '') and isset($_SESSION['access_token']))
					$access_token = $_SESSION['access_token'];
				if (!isset($_SESSION['instance_url']) or !isset($_SESSION['access_token'])) {
					echo '<br><br> ERROR: Session has timed out - no instance url or access token. Return to Purchase Agreements from Salesforce opportunity.';
					exit;
				}
			}

			$stid = oci_parse($db_conn, $qu_reg);
			oci_execute($stid);
			while ($row2 = oci_fetch_array($stid, OCI_RETURN_NULLS + OCI_ASSOC)) {
				//here if doc is assigned...
				$division = html_entity_decode(trim($row2['DIVISION']), ENT_QUOTES);
				$region_jde = html_entity_decode(trim($row2['REGION_JDE']), ENT_QUOTES);
				$uamc_div = html_entity_decode(trim($row2['UAMC']), ENT_QUOTES);
				$esign_contracts = trim($row2['ESIGN_CONTRACTS']);
				$_SESSION['esign_contracts'] = $esign_contracts;
			}

			$qu_comm = "select community, uamc from dj_community where division_jde = '$division_jde' and community_jde = '$community_jde' ";

			$stid = oci_parse($db_conn, $qu_comm);
			oci_execute($stid);
			while ($row3 = oci_fetch_array($stid, OCI_RETURN_NULLS + OCI_ASSOC)) {
				//here if doc is assigned...
				$community = html_entity_decode(trim($row3['COMMUNITY']), ENT_QUOTES);
				$_SESSION['community'] = $community;
				$uamc = strtoupper(trim($row3['UAMC']));
			}


			$description_my_doc = '';
			$level_desc_first = '';

			$col = 1;
			$doc_count = 0;

			if (isset($_POST['ratified']) and $_POST['ratified'] == 'on') {
				$checked = 'checked';
				$ratified = ' and DJ_CUST_COMM_HOME_DOCS_VER.is_ratified=\'Yes\' ';
			} else {
				$checked = '';
				$ratified = '';
			}

			$cusname = $_SESSION['customer_name_selected'];

			/*
		echo '<form action="contract_select_community_nhc.php" method="post" name="test">';
		echo '<table><tr><td>View Ratified Documents Only:<input type=checkbox name="ratified" onclick="this.form.submit()" '.$checked.'></td></tr></table>';
        echo '</form>';
*/


			//added below javascript function on 01/13/2011,madhav kolipaka;
			//function to submit form when the button print all blank documents is clicked

			echo '<script language="JavaScript" type="text/javascript">
	 <!--
	 function fsubmit ( selectedtype )
	 {
	   document.select_doc2.print_blank.value = selectedtype ;
	   document.select_doc2.submit() ;
	 }
	 -->
     </script>';

			$found_esign = false;
			$Oracle_Primary_Opportunity_ID__c = '';
//***************

			if (!$found_esign) {
				$debug = false;
				if ($_SESSION['userid'] == 'jeff.mckenzie@lennar.com') {
					echo '<br>JMC(only) ' . $qu_esign;
					echo '<br>comm: ' . $community_jde;
					echo '<br>home: ' . $homesite;
					echo '<br>cust: ' . $customer_id;
					echo '<br>job#: ' . $_SESSION['JOB'];
					echo '<br>SFDC: ' . $_SESSION['SFORCE'];
					echo '<br>userid: ' . $_SESSION['userid'];
					$debug = true;
				}
				$SFORCE = 'N';
				$JOB = $_SESSION['JOB'];
				if (isset($_SESSION['SFORCE']) and $_SESSION['SFORCE'] != '')
					$SFORCE = $_SESSION['SFORCE'];
				if ($SFORCE != 'N') {
					if (!isset($_SESSION['instance_url']) or !isset($_SESSION['access_token'])) {
						$q2 = "select sf_access_token access_token,sf_instance_url instance_url from sf_client_access_registry where OMCS_APP_NAME = 'DocuSign Integration' ";
						$stid = oci_parse($db_conn, $q2);
						oci_execute($stid);
						while ($row = oci_fetch_array($stid, OCI_RETURN_NULLS + OCI_ASSOC)) {
							$instance_url = trim($row['INSTANCE_URL']);
							$access_token = trim($row['ACCESS_TOKEN']);
						}
					} else {
						$instance_url = trim($_SESSION['instance_url']);
						$access_token = trim($_SESSION['access_token']);
					}
				}
			}


//****************


			if (isset($_SESSION['Oracle_Primary_Opportunity_ID__c']) and $_SESSION['Oracle_Primary_Opportunity_ID__c'] != '') {
				$Oracle_Primary_Opportunity_ID__c = $_SESSION['Oracle_Primary_Opportunity_ID__c'];
			}
			if (isset($opportunity_id) and $opportunity_id != '') {
				$qu_esign = "select index_part, opportunity_id from dj_esign where division_jde   = '$division_jde' and
													  community      = '$community_jde' and
													  opportunity_id = '$opportunity_id' and
													  customer_id    = '$customer_id' ";
				$qu_esign = "select index_part, opportunity_id from dj_esign where division_jde   = '$division_jde' and
										(opportunity_id = '$opportunity_id' or opportunity_id = '$Oracle_Primary_Opportunity_ID__c') ";
			} ELSE {
				$qu_esign = "select index_part, opportunity_id from dj_esign where division_jde   = '$division_jde' and
													  community      = '$community_jde' and
													  homesite       = '$homesite' and
													  customer_id    = '$customer_id' ";
			}
			$stidesign = oci_parse($db_conn, $qu_esign);
			oci_execute($stidesign);
			while ($rowesign = oci_fetch_array($stidesign, OCI_RETURN_NULLS + OCI_ASSOC)) {
				$index_part_esign = trim($rowesign['INDEX_PART']);
				if ($opportunity_id == '' and $rowesign['OPPORTUNITY_ID'] != '') {
					$opportunity_id = trim($rowesign['OPPORTUNITY_ID']);
					$_SESSION['opportunity_id'] = $opportunity_id;
				}
				$found_esign = true;
			}

			if ($debug) {
				echo '<br>sess: ' . $_SESSION['Oracle_Primary_Opportunity_ID__c'];
				echo '<br>var: ' . $Oracle_Primary_Opportunity_ID__c;
				echo '<br>qu_esign: ' . $qu_esign;
			}


			$found_extra_docs = false;
			$qu_extra_docs = "select index_part from dj_esign_custom where division_jde like '%$division_jde%' and
												                                  category = 'contracts' ";
			$stiqu_extra_docs = oci_parse($db_conn, $qu_extra_docs);
			oci_execute($stiqu_extra_docs);
			while ($rowesign2 = oci_fetch_array($stiqu_extra_docs, OCI_RETURN_NULLS + OCI_ASSOC)) {
				$found_extra_docs = true;
			}


			//change made on form action on 06/10 to replace contract_select_nhc_print by contract_select_nhc_community
			//also added a hidden variable archive on 06/10/2009-mak
			//change made to revert back to what it was befor,08-20-2009,mak
			echo '<form action="contract_select_nhc_print.php" method="post" name="select_doc2" onSubmit="return dp_check();">';
			echo '<table class="borders_left"><tr valign="bottom">';
			echo '<input type="hidden" name="division_jde" value="' . $division_jde . '">';
			echo '<input type="hidden" name="community_jde" value="' . $community_jde . '">';
			echo '<input type="hidden" name="customer_id" value="' . $customer_id . '">';
			echo '<input type="hidden" name="opportunity_id" value="' . $opportunity_id . '">';
			echo '<input type="hidden" name="homesite" value="' . $homesite . '">';
			echo '<input type="hidden" name="archive" value=1>';
			if(isset($_SESSION['nocrm']) and $_SESSION['nocrm'] == 'yes'){
				echo '<tr><td nowrap colspan="6"><a href = "/contracts/tools/contract_access_ndr.php"> <u>Back to Offline Contracts<u></a></td></tr>';
				echo '<tr><td nowrap colspan="3">Documents for: <b>' . $_SESSION['customer_name_selected'] . '</b></td>';
			}else{
				if ($e_serv != 'Production' and $e_serv != 'ProductionNew' and isset($_SESSION['SFORCE']) and $_SESSION['SFORCE'] != 'Y') {
					//echo '<tr><td nowrap colspan="6">Server: '.$_SERVER['SERVER_NAME'].'</td></tr>';
					if ($opportunity_id != '')
						echo '<tr><td nowrap colspan="6"><a href = "' . $crmdomain . '/OnDemand/user/OpportunityDetail?OpptyDetailForm.Id=' . $opportunity_id . '" target="_parent"> <u>Back to Opportunity<u></a></td></tr>';
				} else if (isset($_SESSION['SFORCE']) and $_SESSION['SFORCE'] != 'Y') {
					if ($opportunity_id != '')
						echo '<tr><td nowrap colspan="6"><a href = "' . $crmdomain . '/OnDemand/user/OpportunityDetail?OpptyDetailForm.Id=' . $opportunity_id . '" target="_parent"> <u>Back to Opportunity</u></a></td></tr>';
				} else if (isset($_SESSION['SFORCE']) and $_SESSION['SFORCE'] == 'Y') {
					if ($opportunity_id != '' and 1 == 2) //don't need with salesforce
						echo '<tr><td nowrap colspan="6"><a href="' . $instance_url . '/' . $opportunity_id . '" target="_top"> <u>Back to SF Opportunity</u></a></td></tr>';
				}
				echo '<tr><td nowrap colspan="3">Documents for: <b>' . $_SESSION['customer_name_selected'] . '</b></td>';

				if ($e_serv != 'Production' and $e_serv != 'ProductionNew' and 1 == 2) {
					echo '<td align=left bgcolor=#ffffff nowrap colspan="4" rowspan="1">&nbsp;<button type="button" class="no_border_center"
						onClick="javascript:location.href=\'prequalify.php?from_contracts=Y\';">UAMC Loan Pre-Assessment</button></td>';
				} else if ($uamc == 'Y' or ($uamc_div == 'Y' and $uamc != 'N') and 1 == 2) {
					echo '<td align=left bgcolor=#ffffff nowrap colspan="4" rowspan="1">&nbsp;<button type="button" class="no_border_center"
						onClick="javascript:location.href=\'prequalify.php?from_contracts=Y\';">UAMC Loan Pre-Assessment</button></td>';
				}
			}

			$esign_community = true;
			//**** remove 7042 and 15082 ********************** that's really an Orlando community but is set up with myLennar Buy Demo TPU

			$esign_checked = 'unchecked';

//echo '<br/>esign_contracts:'.$_SESSION['esign_contracts'].' - esign_community:'.$esign_community.' - found_esign:'.$found_esign;
			//if($_SESSION['esign_contracts'] == 'Y')
			if ($_SESSION['esign_contracts'] == 'Y' and $esign_community) {
				$esign_checked = 'checked';
				if ($found_esign) {
					echo '<td align=left bgcolor=#ffffff nowrap colspan="4" rowspan="1">&nbsp;<button type="button" class="no_border_center"
						  onClick="javascript:location.href=\'docusign/esign_status.php?from_contracts=Y\';">e-sign document status</button></td>';
				}
			}
			//else
			//	{
			//	echo '<td align=left bgcolor=#ffffff nowrap colspan="6" rowspan="1">&nbsp;found_esign:'.$found_esign.' - esign_contracts:'.$_SESSION['esign_contracts'];
			//	}

			echo '</tr>';
			echo '<tr><td nowrap colspan="6">Community: <b>' . $division . ' - ' . $community_jde . ' - ' . $community . '</b> Homesite: <b>' . $homesite . '</b></td></tr>';
			echo '<tr><td colspan="8" align="left" valign="bottom">
				  <center><button type="button" class="no_border_center"
					  onClick="javascript:location.href=\'contract_nhc_all_doc_entry.php?from_contracts=Y\';">Enter All Field Values</button>
					   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			if ($_SESSION['esign_contracts'] == 'Y' and $esign_community)
				echo 'e-sign?<input type="checkbox" name="esign_docs" ' . $esign_checked . '>';
			else
				echo '<input type="hidden" name="esign_docs" value="off">';

			echo '<input type="submit" name="Submit" value="Print Selected Contract Documents" class="no_border_center" />
				  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			if ($_SESSION['esign_contracts'] == 'Y' and $esign_community and $found_extra_docs) {
				echo '<button type="button" class="no_border_center"
					  onClick="javascript:location.href=\'docusignportal/docportal.php?nav=contracts&from_contracts=Y&fromcrm=Y&email=' . $_SESSION['userid'] . '&division_jde=' . $division_jde . '&community_jde=' . $community_jde . '&homesite=' . $homesite . '&customer_id=' . $customer_id . '&opportunity_id=' . $opportunity_id . '&ind=' . $index_part_dj . '&b1fn=' . addslashes($_SESSION['BUYER1_FIRSTNAME']) . '&b1ln=' . addslashes($_SESSION['BUYER1_LASTNAME']) . '\';">Print Extra Documents</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			}
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" class="no_border_center"
									  onClick="javascript:fsubmit(1);">Print Selected Blank Documents</button></center></td></tr>';

			// echo '<tr><td>&nbsp;</td></tr>';
			echo '<tr><td>&nbsp;</td>
				  <td class="border_bottom_center">Document</td>
				  <td class="border_bottom_center"> Complete </td>
				  <td class="border_bottom_center"> Print </td>';
			//	echo '<td class="border_bottom_center">Print<br /> Draft </td>';
			echo '<td nowrap class="border_bottom_center">Date Last<br />Printed</td>
				  <td nowrap class="border_bottom_center"> Document #</td>';

			echo '<td nowrap class="border_bottom_center"> Print<br/>Blank</td>';
			echo '<td nowrap class="border_bottom_center"> Mandatory?</td>';
			echo '		  <td nowrap class="border_bottom_center">Revision<br />Date</td>
				  <td nowrap class="border_bottom_center">Version #</td></tr>';
			//check for apostrophes in cust name and other variables

			$fn1 = str_replace("+", "", $_SESSION['BUYER1_FIRSTNAME']);
			$fn2 = $_SESSION['BUYER1_LASTNAME'];
			$fn = str_replace("'", "''", $fn1) . ' ' . str_replace("'", "''", $fn2);
			if ($corp_access == 'Y') {

				//options_addition_02142012. union statement added to below query.

				$qu_doc = "select * from (select distinct a.*,b.DOCUMENT_NUMBER,b.filepath from (
                    select DISTINCT dj_documents.index_part, dj_documents.source_doc, dj_documents.source_blank_doc, dj_documents.description, dj_documents.level_desc, dj_documents.doc_num, dj_documents.revision_date, dj_documents.version_num, dj_documents.test_only, dj_documents_dtl_div.sort_order_doc
				    from dj_documents, dj_documents_dtl_div, dj_docs_dtl_comm_assign
					where dj_documents.index_part = dj_documents_dtl_div.index_part_doc and dj_documents_dtl_div.division_jde = '$division_jde' and dj_documents_dtl_div.active = 'Y' and
						  dj_docs_dtl_comm_assign.index_part_doc = dj_documents_dtl_div.index_part_doc and dj_docs_dtl_comm_assign.community_jde = '$community_jde' and dj_docs_dtl_comm_assign.active = 'Y' and
						  dj_documents.index_part = dj_docs_dtl_comm_assign.index_part_doc
						  and  dj_documents.description not like 'UAMC%'
					order by dj_documents_dtl_div.sort_order_doc, dj_documents.level_desc desc, dj_documents.description) a
           LEFT JOIN (select DJ_CUST_COMM_HOME_DOCS_VER.document_number,DJ_CUST_COMM_HOME_DOCS_VER.filepath from DJ_CUST_COMM_HOME_DOCS_VER
           join( select document_number,max(archived_date) archived_date from DJ_CUST_COMM_HOME_DOCS_VER
           where homesite='$homesite' and DJ_CUST_COMM_HOME_DOCS_VER.customer_first_name||' '||DJ_CUST_COMM_HOME_DOCS_VER.customer_last_name='$fn'
           group by document_number) arch on arch.document_number=DJ_CUST_COMM_HOME_DOCS_VER.document_number
           and arch.archived_date=DJ_CUST_COMM_HOME_DOCS_VER.archived_date and DJ_CUST_COMM_HOME_DOCS_VER.homesite='$homesite' and DJ_CUST_COMM_HOME_DOCS_VER.customer_first_name||' '||DJ_CUST_COMM_HOME_DOCS_VER.customer_last_name='$fn' $ratified ) b on ltrim(rtrim(a.doc_num))=ltrim(rtrim(b.document_number))
           union ALL
           select index_part,NULL,NULL,description,NULL,NULL,date_loaded,1,NULL,10000 SORT_ORDER_DOC,NULL, NULL
           from dj_documents_options where division='$division_jde'
           and community= '$community_jde' and homesite='$homesite' and customer_first_name||' '||customer_last_name='$fn') x
           order by x.sort_order_doc";

				$qu_doc = "select * from (select distinct a.*,b.DOCUMENT_NUMBER,b.filepath from (
                    select DISTINCT dj_documents.index_part, dj_documents.source_doc, dj_documents.source_blank_doc, dj_documents.description, dj_documents.level_desc, dj_documents.doc_num, dj_documents.revision_date, dj_documents.version_num, dj_documents.test_only, dj_documents_dtl_div.sort_order_doc
				    from dj_documents, dj_documents_dtl_div, dj_docs_dtl_comm_assign
					where dj_documents.index_part = dj_documents_dtl_div.index_part_doc and dj_documents_dtl_div.division_jde = '$division_jde' and dj_documents_dtl_div.active = 'Y' and
						  dj_docs_dtl_comm_assign.index_part_doc = dj_documents_dtl_div.index_part_doc and dj_docs_dtl_comm_assign.community_jde = '$community_jde' and dj_docs_dtl_comm_assign.active = 'Y' and
						  dj_documents.index_part = dj_docs_dtl_comm_assign.index_part_doc
						  and  dj_documents.description not like 'UAMC%'
					order by dj_documents_dtl_div.sort_order_doc, dj_documents.level_desc desc, dj_documents.description) a
           LEFT JOIN (select DJ_CUST_COMM_HOME_DOCS_VER.document_number,DJ_CUST_COMM_HOME_DOCS_VER.filepath from DJ_CUST_COMM_HOME_DOCS_VER
           join( select document_number,max(archived_date) archived_date from DJ_CUST_COMM_HOME_DOCS_VER
           where homesite='$homesite' and DJ_CUST_COMM_HOME_DOCS_VER.customer_first_name||' '||DJ_CUST_COMM_HOME_DOCS_VER.customer_last_name='$fn'
           group by document_number) arch on arch.document_number=DJ_CUST_COMM_HOME_DOCS_VER.document_number
           and arch.archived_date=DJ_CUST_COMM_HOME_DOCS_VER.archived_date and DJ_CUST_COMM_HOME_DOCS_VER.homesite='$homesite' and DJ_CUST_COMM_HOME_DOCS_VER.customer_first_name||' '||DJ_CUST_COMM_HOME_DOCS_VER.customer_last_name='$fn' $ratified ) b on ltrim(rtrim(a.doc_num))=ltrim(rtrim(b.document_number))
           union ALL
           select index_part,NULL,NULL,description,NULL,NULL,date_loaded,1,NULL,10000 SORT_ORDER_DOC,NULL, NULL
           from dj_documents_options where division='$division_jde'
           and community= '$community_jde' and homesite='$homesite' and (opportunityid = '$opportunity_id' or opportunityid = '$Oracle_Primary_Opportunity_ID__c')) x
           order by x.sort_order_doc";
			} else {
				$qu_doc = "     select * from (select distinct a.*,b.DOCUMENT_NUMBER,b.filepath from (
                    select DISTINCT dj_documents.index_part, dj_documents.source_doc, dj_documents.source_blank_doc, dj_documents.description, dj_documents.level_desc, dj_documents.doc_num, dj_documents.revision_date, dj_documents.version_num, dj_documents.test_only, dj_documents_dtl_div.sort_order_doc
				    from dj_documents, dj_documents_dtl_div, dj_docs_dtl_comm_assign
					where dj_documents.index_part = dj_documents_dtl_div.index_part_doc and dj_documents_dtl_div.division_jde = '$division_jde' and dj_documents_dtl_div.active = 'Y' and
						  dj_docs_dtl_comm_assign.index_part_doc = dj_documents_dtl_div.index_part_doc and dj_docs_dtl_comm_assign.community_jde = '$community_jde' and dj_docs_dtl_comm_assign.active = 'Y' and
						  dj_documents.index_part = dj_docs_dtl_comm_assign.index_part_doc and (dj_documents.test_only is null or dj_documents.test_only = 'N')
						  and  dj_documents.description not like 'UAMC%'
					order by dj_documents_dtl_div.sort_order_doc, dj_documents.level_desc desc, dj_documents.description) a
           LEFT JOIN (select DJ_CUST_COMM_HOME_DOCS_VER.document_number,DJ_CUST_COMM_HOME_DOCS_VER.filepath from DJ_CUST_COMM_HOME_DOCS_VER
           join( select document_number,max(archived_date) archived_date from DJ_CUST_COMM_HOME_DOCS_VER
           where homesite='$homesite' and DJ_CUST_COMM_HOME_DOCS_VER.customer_first_name||' '||DJ_CUST_COMM_HOME_DOCS_VER.customer_last_name='$fn'
           group by document_number) arch on arch.document_number=DJ_CUST_COMM_HOME_DOCS_VER.document_number
           and arch.archived_date=DJ_CUST_COMM_HOME_DOCS_VER.archived_date and DJ_CUST_COMM_HOME_DOCS_VER.homesite='$homesite' and DJ_CUST_COMM_HOME_DOCS_VER.customer_first_name||' '||DJ_CUST_COMM_HOME_DOCS_VER.customer_last_name='$fn' $ratified ) b on ltrim(rtrim(a.doc_num))=ltrim(rtrim(b.document_number))
             union ALL
           select index_part,NULL,NULL,description,NULL,NULL,date_loaded,1,NULL,10000 SORT_ORDER_DOC,NULL, NULL
           from dj_documents_options where division='$division_jde'
           and community= '$community_jde' and homesite='$homesite' and customer_first_name||' '||customer_last_name='$fn') x
           order by x.sort_order_doc";

				$qu_doc = "     select * from (select distinct a.*,b.DOCUMENT_NUMBER,b.filepath from (
                    select DISTINCT dj_documents.index_part, dj_documents.source_doc, dj_documents.source_blank_doc, dj_documents.description, dj_documents.level_desc, dj_documents.doc_num, dj_documents.revision_date, dj_documents.version_num, dj_documents.test_only, dj_documents_dtl_div.sort_order_doc
				    from dj_documents, dj_documents_dtl_div, dj_docs_dtl_comm_assign
					where dj_documents.index_part = dj_documents_dtl_div.index_part_doc and dj_documents_dtl_div.division_jde = '$division_jde' and dj_documents_dtl_div.active = 'Y' and
						  dj_docs_dtl_comm_assign.index_part_doc = dj_documents_dtl_div.index_part_doc and dj_docs_dtl_comm_assign.community_jde = '$community_jde' and dj_docs_dtl_comm_assign.active = 'Y' and
						  dj_documents.index_part = dj_docs_dtl_comm_assign.index_part_doc and (dj_documents.test_only is null or dj_documents.test_only = 'N')
						  and  dj_documents.description not like 'UAMC%'
					order by dj_documents_dtl_div.sort_order_doc, dj_documents.level_desc desc, dj_documents.description) a
           LEFT JOIN (select DJ_CUST_COMM_HOME_DOCS_VER.document_number,DJ_CUST_COMM_HOME_DOCS_VER.filepath from DJ_CUST_COMM_HOME_DOCS_VER
           join( select document_number,max(archived_date) archived_date from DJ_CUST_COMM_HOME_DOCS_VER
           where homesite='$homesite' and DJ_CUST_COMM_HOME_DOCS_VER.customer_first_name||' '||DJ_CUST_COMM_HOME_DOCS_VER.customer_last_name='$fn'
           group by document_number) arch on arch.document_number=DJ_CUST_COMM_HOME_DOCS_VER.document_number
           and arch.archived_date=DJ_CUST_COMM_HOME_DOCS_VER.archived_date and DJ_CUST_COMM_HOME_DOCS_VER.homesite='$homesite' and DJ_CUST_COMM_HOME_DOCS_VER.customer_first_name||' '||DJ_CUST_COMM_HOME_DOCS_VER.customer_last_name='$fn' $ratified ) b on ltrim(rtrim(a.doc_num))=ltrim(rtrim(b.document_number))
             union ALL
           select index_part,NULL,NULL,description,NULL,NULL,date_loaded,1,NULL,10000 SORT_ORDER_DOC,NULL, NULL
           from dj_documents_options where division='$division_jde'
           and community= '$community_jde' and homesite='$homesite' and (opportunityid = '$opportunity_id' or opportunityid = '$Oracle_Primary_Opportunity_ID__c')) x
           order by x.sort_order_doc";
			}
			if ($_SESSION['userid'] == 'jeff.mckenzie@lennar.com' and 1 == 2) {
				echo '<br />ratified: ' . $ratified . '<br />';
				echo '<br />corp_access: ' . $corp_access . ' - ' . $qu_doc . '<br />';
//echo '<br />corp_access: '.$corp_access.'<br />';
			}
			$stid = oci_parse($db_conn, $qu_doc);
			oci_execute($stid);
			while ($row = oci_fetch_array($stid, OCI_RETURN_NULLS + OCI_ASSOC)) {
				$complete_checked = 'checked';
				$no_print = '';

				//$doc_count++;
				$index_part_doc = $row['INDEX_PART'];
				$source_blank_doc = trim($row['SOURCE_BLANK_DOC']);
				$source_doc = trim($row['SOURCE_DOC']);
				$description = html_entity_decode(trim($row['DESCRIPTION']), ENT_QUOTES);
				$level_desc = html_entity_decode(trim($row['LEVEL_DESC']), ENT_QUOTES);
				$version_num = html_entity_decode(trim($row['VERSION_NUM']), ENT_QUOTES);
				$revision_date = html_entity_decode(trim($row['REVISION_DATE']), ENT_QUOTES);
				$doc_num = html_entity_decode(trim($row['DOC_NUM']), ENT_QUOTES);
				$archived_doc_num = html_entity_decode(trim($row['DOCUMENT_NUMBER']), ENT_QUOTES);
				$archived_doc_path = html_entity_decode(trim($row['FILEPATH']), ENT_QUOTES);
				$sort_order_doc = html_entity_decode(trim($row['SORT_ORDER_DOC']), ENT_QUOTES);
				$test_only = trim($row['TEST_ONLY']);
				$date_last_printed = '';
				$complete_checked = '';
				$no_print_draft = 'disabled';
				$no_blanks = true;
				$qu_doc222 = "Select footer_1 from dj_documents where index_part = '$index_part_doc' ";
				$stid333 = oci_parse($db_conn, $qu_doc222);
				oci_execute($stid333);
				while ($row533 = oci_fetch_array($stid333, OCI_RETURN_NULLS + OCI_ASSOC)) {
					$footer_1 = trim($row533['FOOTER_1']);
					//echo '<br>footer: '.$footer_1;
				}

				$options_ck = 0;

				//below to get options filename and check if the file exists or not, added 04/19/2012,Madhav
				//mak_04192012

				$qu_options_ck = "select file_name from dj_documents_options where index_part = '$index_part_doc'";
				$stid_ck = oci_parse($db_conn, $qu_options_ck);
				oci_execute($stid_ck);
				while ($rowck = oci_fetch_array($stid_ck, OCI_RETURN_NULLS + OCI_ASSOC)) {
					$file_name_ck = $rowck['FILE_NAME'];

					$bad_chars = array("+", "'", " ", "-", ".", '"', ":", ";", ",", "%", "#", "&", "*", "(", ")", "$", "?", "@", "~", "|", "!", "^", "{", "}", "[", "]", "\\");
					$report_location = "contracts/" . $division_jde . "/" . $community_jde . "/" . $homesite . "/" . preg_replace("([^a-zA-Z 0-9/]+)", "_", str_replace(' ', '', str_replace('/', '', str_replace("''", "_", $fn2)))) . "_" . preg_replace("([^a-zA-Z 0-9/]+)", "_", trim(str_replace(' ', '', str_replace('/', '', str_replace("''", "_", $fn1)))));
					//$report_location = preg_replace("([^a-zA-Z 0-9/]+)","_",str_replace($bad_chars, '_', $report_location));
					if (!file_exists($report_location . '/options/' . $file_name_ck)) {
						$options_ck = 1;
					}
				} //end of inner while

				if ($description == 'Change Order') {
					$qu_doc_options = "select file_name from dj_documents_options where index_part = '$index_part_doc'";
					$stiddjo = oci_parse($db_conn, $qu_doc_options);
					oci_execute($stiddjo);
					while ($rowdjo = oci_fetch_array($stiddjo, OCI_RETURN_NULLS + OCI_ASSOC)) {
						$file_name = $rowdjo['FILE_NAME'];
					}

					$desc_add = substr($file_name, -6, 2);
					$description .= ' ' . $desc_add;
				}
				$qu_docs = "select field_name, siebel from dj_documents_dtl_field  where index_part_doc = '$index_part_doc' ";
				$stid2 = oci_parse($db_conn, $qu_docs);
				oci_execute($stid2);
				while ($row4 = oci_fetch_array($stid2, OCI_RETURN_NULLS + OCI_ASSOC)) {
					$myvalue = '';
					$myview_only = '';
					//$document_complete = '';
					//$no_print = 'disabled';
					$field_name = html_entity_decode(trim($row4['FIELD_NAME']), ENT_QUOTES);
					$siebel = html_entity_decode(trim($row4['SIEBEL']), ENT_QUOTES);
					$qu_complete = "select value, view_only from dj_customer_comm_home  where customer_id 	= '$customer_id' and
																						  community_jde	= '$community_jde' and
																						  homesite		= '$homesite' and
																						  field_name 	= '$field_name'  ";

					$stid33 = oci_parse($db_conn, $qu_complete);
					oci_execute($stid33);
					while ($row53 = oci_fetch_array($stid33, OCI_RETURN_NULLS + OCI_ASSOC)) {
						//in here so i can print draft...
						$no_print_draft = '';
						$myvalue = html_entity_decode(trim($row53['VALUE']), ENT_QUOTES);
						$myview_only = html_entity_decode(trim($row53['VIEW_ONLY']), ENT_QUOTES);
					}
					if ($myvalue == '' and $siebel != 'Y') {
						$no_blanks = false;
					}
				}
				$no_print = '';
				if ($no_blanks) {
					$no_print = '';
					$complete_checked = 'checked';
				} else {
					$complete_checked = '';
					$no_print = 'disabled';
				}
				//allow printing to occur if document is marked complete...
				$OracleContactID__c = '';
				if (isset($_SESSION['OracleContactID__c']) and $_SESSION['OracleContactID__c'] != '')
					$OracleContactID__c = $_SESSION['OracleContactID__c'];

				$qu_dj_cust = "select * from dj_customer_comm_home_docs where (customer_id = '$customer_id' or customer_id = '$OracleContactID__c') and division_jde = '$division_jde' and community_jde = '$community_jde' and
																		  homesite = '$homesite' and index_part_doc = '$index_part_doc' ";
				$stid333 = oci_parse($db_conn, $qu_dj_cust);
				oci_execute($stid333);
				while ($row533 = oci_fetch_array($stid333, OCI_RETURN_NULLS + OCI_ASSOC)) {
					$last_print_date = trim($row533['LAST_PRINT_DATE']);
					$date_last_printed = $last_print_date;
					$document_complete = trim($row533['DOCUMENT_COMPLETE']);
					if ($document_complete == 'Y') {
						$no_print = '';
						$complete_checked = 'checked';
					}
				}


				$other_pdf = '';
				//added 05/03/2010, to merge blank pdf docs without triggering oracle reports

				if (!empty($source_doc)
					//and substr($source_blank_doc, strrpos($source_blank_doc, '.') + 1)=='pdf'
				) {
					$other_pdf = 'n';
				} //below valid for blank pdf's
				else {

					$other_pdf = 'y';
				}


				//****

				//****

				//below to check if the options file exists in the folder,added 04/19/2012, mak_04192012
				if ($options_ck == 0) {
					//*************** added by Jeff McKenzie 10/10/2013
					/*

			Check for homesite specific documents. If homesite specific, don't display if it doesn't belong to this home...

			*/
					//
					$pos_pdfonly = stripos($description, 'PDF ONLY');
					$commsphs = $community_jde . ' ' . $homesite . ' PDF ONLY';
					$commpdfonly = $community_jde . ' PDF ONLY';
					$pos_comm = strpos($description, $community_jde); //need to see if the community is in the description

					$pos_comm_hs = stripos($description, $commsphs);
					$pos_commpdf = stripos($description, $commpdfonly);

					$display_doc = true;
					if ($pos_comm)
						$display_doc = false; //i found the community, but only show the document IF 1 of 2 strings exist in description

					// community_jde + "PDF ONLY"
					//or community_jde + " " + homesite + "PDF ONLY"

					if (!$display_doc and ($pos_comm_hs !== false or $pos_commpdf !== false))
						$display_doc = true;

					/*
			$hs_specific = false;
			if($pos_pdfonly !== false and $pos_comm_hs !== false)
				$hs_specific = true;

			$display_doc = true;
			if($hs_specific and $pos_comm_hs === false)
				$display_doc = false;
*/
					if ($display_doc) {
						$doc_count++;

						$shade = '#CFCFCF';
						if (fmod($doc_count, 2) == 0)
							$shade = '#FFFFFF';

						//echo '<tr><td align="right">'.$doc_count.'. |'.$display_doc.'|'.$pos_pdfonly.'|'.$pos_comm_hs.'|'.$pos_comm.'|</td>';
						echo '<tr>';
						echo '<td align="right" style="text-align:right;background-color:' . $shade . ';">' . $doc_count . '. </td>';
						if ($test_only == 'Y')
							$description .= ' - <b>*** TEST 1st ***</b>';

						$pos = strpos(strtolower($description), 'credit card addendum');
						if ($pos === false)
							$cc_addendum = false;
						else
							$cc_addendum = true;

						if ($source_doc != '')
							echo '<td nowrap style="text-align:left;background-color:' . $shade . ';"><a href="contract_nhc_doc_entry.php?index_part_doc=' . $index_part_doc . '&description=' . $description . '">' . $description . '</a></td>';
						else {
							$no_print = 'disabled';
							echo '<td nowrap style="text-align:left;background-color:' . $shade . ';" title="This is only a blank PDF. No data to input or print.">' . $description . '</td>';
						}

						//added 05/04/2010 for merge changes

						if ($other_pdf == 'y') {
							$no_print = '';
							$complete_checked = 'checked';
						}

						echo '<td style="text-align:center;background-color:' . $shade . ';"><input type="checkbox" name="check_box_complete' . $doc_count . '" ' . $complete_checked . ' disabled></td>';
						echo '<td style="text-align:center;background-color:' . $shade . ';"><input type="checkbox" name="check_box_doc_print' . $doc_count . '" ' . $no_print . '></td>';
						//	echo '<td style="text-align:center;"><input type="checkbox" name="check_box_doc_print_draft'.$doc_count.'"  '.$no_print.'></td>';

						//change made 05/04/2010 for merging blank pdf's

						if ($archived_doc_num == '' OR ($other_pdf == 'y' and !file_exists($archived_doc_path))) {
							echo '<td nowrap style="background-color:' . $shade . ';">' . $date_last_printed . '</td>';
						} else if (!$cc_addendum and file_exists($archived_doc_path)) {
							echo '<td nowrap style="background-color:' . $shade . ';" title="Click for LAST PDF document printed."><a href="' . $archived_doc_path . '" target="new"><u>' . $date_last_printed . '</u></a></td>';
						} else
							echo '<td style="background-color:' . $shade . ';">&nbsp;</td>';

						if ($source_blank_doc != '')
							echo '<td nowrap style="background-color:' . $shade . ';" title="Click for blank PDF document."><a href="contract_blank_docs/' . $source_blank_doc . '" target="new"><u>' . $doc_num . '</u></a></td>';
						else
							echo '<td nowrap style="background-color:' . $shade . ';">' . $doc_num . '</td>';
						//echo '<td nowrap><a href="'.$archived_doc_path.'" target="new"><u>'.$archived_doc_num.'</u></a></td>';

						//below is the check box to select blank documents to be printed,added 02/04/2011

						echo '<td style="text-align:center;background-color:' . $shade . ';"><input type="checkbox" name="check_box_doc_print_bl' . $doc_count . '"></td>';
						echo '<td nowrap style="background-color:' . $shade . ';">' . $footer_1 . '</td>';
						echo '<td nowrap style="background-color:' . $shade . ';">' . $revision_date . '</td>';
						echo '<td style="text-align:center;background-color:' . $shade . ';">' . $version_num . '</td>';
						//echo '<td style="text-align:center;">'.$sort_order_doc.'</td>';
						echo '<input type="hidden" name="index_part_doc' . $doc_count . '" value="' . $index_part_doc . '">';
						echo '<input type="hidden" name="arch_docno' . $doc_count . '" value="' . $archived_doc_num . '">';
						echo '<input type="hidden" name="arch_docpath' . $doc_count . '" value="' . $archived_doc_path . '">';
						//added below 05/04/2010 for merge changes
						echo '<input type="hidden" name="other_pdf' . $doc_count . '" value="' . $other_pdf . '">';
						$col++;
						if ($col > 1) {
							$col = 1;
							echo '</tr>';

						}//end of if condition for options file exists check,mak_04192012

						echo '<tr>';
					}
				}
			}


//email archived documents
			if (isset($_POST['email_nhc']) and $_POST['email_nhc'] == 'on') {


				$i = 0;
				$k = 0;
				$files = array();

				while ($i < $_POST['doc_count']) {

					$archived_doc_path = 'arch_docpath' . $i;
					$check_box_doc_print = 'check_box_doc_print' . $i;

					if ($_POST[$check_box_doc_print] == 'on' and $_POST[$archived_doc_path] != '') {


						$files[$k] = substr($_POST[$archived_doc_path], strripos($_POST[$archived_doc_path], "contracts/"));

						$k++;


					}


					$i++;


				}//end of while


				//emailing part

				if (!empty($files)) {
					// email fields: to, from, subject, and so on
					$to = "madhav.kolipaka@lennar.com";
					$from = "madhav.kolipaka@lennar.com";
					$subject = "NHC Archived documents";
					$message = "Attached are The archived documents from NHC";
					$headers = "From: $from";

					// boundary
					$semi_rand = md5(time());
					$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

					// headers for attachment
					$headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";

					// multipart boundary
					$message = "This is a multi-part message in MIME format.\n\n" . "--{$mime_boundary}\n" . "Content-Type: text/plain; charset=\"iso-8859-1\"\n" . "Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n";
					$message .= "--{$mime_boundary}\n";

					// preparing attachments
					for ($x = 0; $x < count($files); $x++) {
						$file = fopen($files[$x], "rb");
						$data = fread($file, filesize($files[$x]));
						fclose($file);
						$data = chunk_split(base64_encode($data));
						$message .= "Content-Type: {\"application/octet-stream\"};\n" . " name=\"$files[$x]\"\n" .
							"Content-Disposition: attachment;\n" . " filename=\"$files[$x]\"\n" .
							"Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
						$message .= "--{$mime_boundary}\n";
					}

					// send

					$ok = @mail($to, $subject, $message, $headers);
					if ($ok) {

						echo "<p>mail sent to $to!</p>";
					} else {
						echo "<p>mail could not be sent!</p>";
					}


				}//end of inner if


			}//end of if


			echo '<input type="hidden" name="doc_count" value="' . $doc_count . '">';
			//echo '<tr><td colspan="8"><font color="blue">Email Selected Documents:</font><input type=checkbox name="email_nhc" onclick="this.form.submit()"></td></tr>';


			$job = $_SESSION['JOB'];


//added 06/16/2010 to preserve the sale date from contracts. The below retrieves the contract sale date
			$qu_dj = "select saledate from dj_big_fatty where community_jde='$community_jde' and homesite='$homesite' and opportunity_id='$opportunity_id' ";

			$sale_date_contracts = '';

			$stid = oci_parse($db_conn, $qu_dj);
			oci_execute($stid);
			while ($row = oci_fetch_array($stid, OCI_RETURN_NULLS + OCI_ASSOC)) {
				$sale_date_contracts = html_entity_decode(trim($row['SALEDATE']), ENT_QUOTES);
			}


			if ($sale_date_contracts == '' or IS_NULL($sale_date_contracts)) {

				$sale_date_update = date("m/d/Y");

			} else {
				$sale_date_update = $sale_date_contracts;
			}


			echo '<input type="hidden" name="sale_date_contracts" value="' . $sale_date_contracts . '">';


			$db_conn_wh = db_connect_wh($e_serv);
			if (!$db_conn_wh) {
				echo '<br><h2>Unable to connect to oracle database!';
			} else {
				$qu_hs = "select HOMESITESBLSTATUS,estimatedcoedate from homesites where homesiteid='$job' ";

				$estimated_coe_date = '';
				$disabled = '';
				$homesite_status = 'AVAILABLE';
				$stid = oci_parse($db_conn_wh, $qu_hs);
				oci_execute($stid);
				while ($row = oci_fetch_array($stid, OCI_RETURN_NULLS + OCI_ASSOC)) {
					$homesite_status = html_entity_decode(trim($row['HOMESITESBLSTATUS']), ENT_QUOTES);
					$estimated_coe_date = trim($row['ESTIMATEDCOEDATE']);

					if (!is_null($estimated_coe_date) and strlen($estimated_coe_date) >= 9) {
						$disabled = 'disabled';
					}
				}
			}
			if ($disabled == '') {
				if (isset($_SESSION['estimated_coe_date']))
					$estimated_coe_date = $_SESSION['estimated_coe_date'];
			}

			//date check using javascript for COE date

			echo '<script language="javascript">
		                function dp_check()

		                {
		                if(document.select_doc2.homesite_sold.checked==1 &&  !document.select_doc2.date_picker.value)

		                {
		                alert("Please enter a value for Estimated COE");
		                return false;

		                }


		               else if(document.select_doc2.homesite_sold.checked==1 && document.select_doc2.bContingent.checked==1 && !document.select_doc2.plContingency_Type.value)

		               {
		               alert("Contingency type must be selected when Contingent is checked");

				       return false;

				       }


				         else if(document.select_doc2.homesite_sold.checked==1 && document.select_doc2.bContingent.checked==0 && document.select_doc2.plContingency_Type.value)

					   {
					  alert("Contingent must be checked for Contingency type to be selected");

				      return false;

				       }


                       else if(document.select_doc2.homesite_sold.checked==1 && !document.select_doc2.solar_type.value && document.select_doc2.solar_type.disabled==false)

		               {
		               alert("Solar type must be selected");

				       return false;

				       }												   		



		                else
		                return true;
		                  }
		              </script>';


			echo '<script type="text/javascript">

                     function con_display()

                          {

                    var checkbox = document.select_doc2.bContingent;
                    var con_type = document.select_doc2.plContingency_Type;

                        if(checkbox.checked == 1)

                          {

                        con_type.disabled=false;
                        con_type.style.display = "inline";

                          }

                          else if(checkbox.checked == 0)
                         {

                           con_type.options[0].selected = true;
                           con_type.disabled=true;



                         }
                         } </script>';


			//end of links and script definition for estimated coe datepicker

			//if contingency on opportunity is checked below to make the checkbox selected
			$con_checked = '';
			$con_disabled = 'disabled';
			if (isset($_SESSION['contingency']) and $_SESSION['contingency'] == 'Y') {
				$con_checked = 'checked';
				$con_disabled = '';
			}


			$solar_disabled = 'disabled';

			$qu_solar = "select sunstreet from dj_division where division_jde='$division_jde'";
			$ex_solar = oci_parse($db_conn, $qu_solar);
			oci_execute($ex_solar);
			while ($rowso = oci_fetch_array($ex_solar, OCI_RETURN_NULLS + OCI_ASSOC)) {
				$sunstreet = $rowso['SUNSTREET'];

			}

			if ($sunstreet == 'Y') {
				$solar_disabled = '';

			}


			//below is the array to hold contingency type values

			$contingency_arr = array('Home to Sell - Listed', 'Home to Sell -  NOT Listed', 'Home sold, Not Closed', 'Financing', 'Legal', 'Relocation', 'Home In Escrow',
				'Cashout Refi', 'Divorce Final', 'Swing Loan', 'Co-Signer', 'Legal Settlement', 'New Plan / No Model');


			echo "<tr><td colspan=\"8\"></td></tr>";

			if ($homesite_status == 'AVAILABLE' OR $homesite_status == 'HOLD' and 1 == 2) {
				echo '<tr><td colspan="8" align="left" valign="bottom">
		       Estimated COE Date <input type="text" class="datepicker2" id="date_picker" name="estimated_coe" ' . $disabled . '  value="' . $estimated_coe_date . '"><br/>
		       Contingent <input type="checkbox" id="bContingent" name="bContingent"' . $con_checked . ' onClick="con_display();"><br/>
		       Contingency Type <select id="plContingency_Type" name="plContingency_Type"' . $con_disabled . '><option value=""></option>';


				for ($i = 0; $i < count($contingency_arr); $i++) {
					$val = $contingency_arr[$i];

					if (isset($_SESSION['contingency_type']) and $val == $_SESSION['contingency_type']) {
						echo '<option selected value="' . $val . '">' . $val . '</option>';
					} else {
						echo '<option value="' . $val . '">' . $val . '</option>';
					}
				}


				$solar_list_array = array('Solar20/20', 'Purchased', 'Included');


				echo '</select><br/><br/> Solar Type <select id="solar_type" name="solar_type"' . $solar_disabled . '>
						<option value=""></option><option value="None">None</option>';


				for ($i = 0; $i < count($solar_list_array); $i++) {

					$solar_val = $solar_list_array[$i];

					if (isset($_SESSION['SOLARTYPE']) and $_SESSION['SOLARTYPE'] == $solar_val) {


						echo '<option selected value="' . $solar_val . '">' . $solar_val . '</option>';

					} else {

						echo '<option value="' . $solar_val . '">' . $solar_val . '</option>';

					}


				}


				echo '</select><br/>';

				echo '<b>Set Homesite as SOLD with a sale date of: ' . $sale_date_update . '</b> &nbsp;
		      <input type="checkbox" id="homesite_sold" name="homesite_sold" size="8">
			  <br/>
		      <center><button type="button" class="no_border_center"
					  onClick="javascript:location.href=\'contract_nhc_all_doc_entry.php?from_contracts=Y\';">Enter All Field Values</button>
					   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				if ($_SESSION['esign_contracts'] == 'Y' and $esign_community)
					echo 'e-sign?<input type="checkbox" name="esign_docs" ' . $esign_checked . '>';
				else
					echo '<input type="hidden" name="esign_docs" value="off">';
				echo '  <input type="submit" name="Submit" value="Print Selected Contract Documents" class="no_border_center" />
					  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					  <button type="button" class="no_border_center"
					  onClick="javascript:fsubmit(1);">Print Selected Blank Documents</button></center>';
			} ELSE {
				echo '<tr><td colspan="8" align="left" valign="bottom"><b>Homesite status is ' . $homesite_status . ' </b>
				<center><button type="button" class="no_border_center"
					  onClick="javascript:location.href=\'contract_nhc_all_doc_entry.php?from_contracts=Y\';">Enter All Field Values</button>
					   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				if ($_SESSION['esign_contracts'] == 'Y' and $esign_community)
					echo 'e-sign?<input type="checkbox" name="esign_docs" ' . $esign_checked . '>';
				else
					echo '<input type="hidden" name="esign_docs" value="off">';
				echo '<input type="submit" name="Submit" value="Print Selected Contract Documents" class="no_border_center" />
					  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					  <button type="button" class="no_border_center"
					  onClick="javascript:fsubmit(1);">Print Selected Blank Documents</button></center>';

			}

			//echo '<button type="button" class="no_border_center"
			//  onClick="javascript:location.href=\'contract_select_nhc_print.php?printall=YES\';" >Print ALL<br />Documents</button>';

			echo '</td>';
			echo '</tr>';
			echo '<tr><td nowrap colspan="6">Server: ' . $_SERVER['SERVER_NAME'] . '</td></tr>';

			echo '<input type="hidden" name="print_blank">';
			echo '</table></form>';


		}
	} else {
		echo '<br />Please select a customer to create the contracts for...';
	}
	if (isset($stid))
		oci_free_statement($stid);
	if (isset($stidesign))
		oci_free_statement($stidesign);
	if (isset($stid_ck))
		oci_free_statement($stid_ck);
	if (isset($stiddjo))
		oci_free_statement($stiddjo);
	if (isset($stid2))
		oci_free_statement($stid2);
	if (isset($stid33))
		oci_free_statement($stid33);
	if (isset($stid333))
		oci_free_statement($stid333);

	if ($db_conn)
		oci_close($db_conn);
}


function contract_select_nhc_print($get, $post)
{
	global $_SESSION, $e_serv, $pwd_crm, $crmdomain, $logoff, $report_file_path, $document_root, $report_link, $contract_link, $oracle_proxy, $java_link;
	if (($_SESSION['userid'] == 'niles.rowland@lennar.com') && 1 == 2) {
		echo __FILE__.':'.__LINE__.' post<pre>';
		print_r($post);
		print_r($_SESSION);
		echo '</pre>';
		// sleep(30);
	}
	if ($_SESSION['userid'] == 'niles.rowland@lennar.com' && 1==2) {
		mail('niles.rowland@lennar.com', 'post', implode("\n",$post));
		mail('niles.rowland@lennar.com', 'session', htmlentities(implode("\n",$_SESSION)));
	}
	$_SESSION['multi_file'] = false;
	if (isset($_SESSION['SFORCE']) and $_SESSION['SFORCE'] == 'Y' and $_SESSION['nocrm'] != 'yes') {
		if ((!isset($instance_url) or $instance_url == '') and isset($_SESSION['instance_url']))
			$instance_url = $_SESSION['instance_url'];
		if ((!isset($access_token) or $access_token == '') and isset($_SESSION['access_token']))
			$access_token = $_SESSION['access_token'];
		if (!isset($_SESSION['instance_url']) or !isset($_SESSION['access_token'])) {
			echo '<br><br> ERROR: Session has timed out due to no instance_url or access_token 2. Return to Purchase Agreements from Salesforce opportunity.';
			exit;
		}
	}	
	// die('here');
	$mytime = time();
	$today = date('d-M-y');
	$userid = '';
	if (isset($_SESSION['userid'])) {
		$userid = strtolower($_SESSION['userid']);
	}
	$iname = 0;
	$merged_files = '';
	$invalid_count = 0;
	$invalid_files = '';
	$invalid_files_alert = '';

	echo '<br>custid:' . $_SESSION['customer_id'];

	if (isset($_SESSION['customer_id']) and $_SESSION['customer_id'] != '') {
		$db_conn = db_connect($e_serv);
		if (!$db_conn) {
			echo '<br><h2>Unable to connect to oracle database!';
			exit;
		} else {
			$solar_type = '';

			$_SESSION['esign_docs'] = 'N';
			if (isset($_POST['esign_docs']) and $_POST['esign_docs'] == 'on') {
				$_SESSION['esign_docs'] = 'Y';
			}
			else {
				$_SESSION['esign_docs'] = 'N';
			}

			$job = $_SESSION['JOB'];
			$division_jde = $_SESSION['division_jde'];
			$opportunity_id = $_SESSION['primary_opportunity_id'];
			if ($opportunity_id == '') {
				$opportunity_id = $_SESSION['opportunity_id'];
			}
			$contact_id = $_SESSION['primary_contact_id'];
			$community_jde = $post['community_jde'];
			$homesite = $post['homesite'];
			$sale_date_contracts = $post['sale_date_contracts'];
			$estimated_coe_date = '';
			if (isset($post['estimated_coe'])) {
				$estimated_coe_date = $post['estimated_coe'];
			}
			$bContingent = '';
			if (isset($post['bContingent'])) {
				$bContingent = $post['bContingent'];
			}
			if ($bContingent == 'on') {
				$bContingent = 'Y';
			}
			else {
				$bContingent = 'N';
			}
			$plContingency_Type = '';
			if (isset($post['plContingency_Type'])) {
				$plContingency_Type = $post['plContingency_Type'];
			}
			if (isset($post['solar_type'])) {
				$solar_type = $post['solar_type'];
			}
			$division = $_SESSION['division_jde'];
			$customer_id = $_SESSION['customer_id'];
			$phase = '';
			$plan = '';

			//added 01/11/2011 for print all blank pdfs
			$print_blank = $_POST['print_blank'];
			$print_blank_arr = array();

			if ($_SESSION['esign_docs'] == 'Y') {
				$qu_dj = "update dj_big_fatty set authorizedagent_name = '' where division_jde = '$division_jde' and
																				  community_jde = '$community_jde' and
																				  homesite = '$homesite' and
																				  customer_id = '$customer_id' ";
				$stid = oci_parse($db_conn, $qu_dj);
				oci_execute($stid);
			}


			//added 05/25/2010, Mak
			//update homesite status to SOLD in OTO and siebel when the checkbox is selected
			//below is functionality to mark homesites as sold
			if ($_SESSION['userid'] == 'niles.rowland@lennar.com' && 1==2) {
				echo __FILE__.':'.__LINE__.' session<pre>';
				print_r($_SESSION);
				echo '</pre>';
				die('here');
			}

			//if ((isset($post['homesite_sold']) and $post['homesite_sold'] == 'on' and $print_blank != 1) || (isset($_SESSION['SALEDATE']) && $_SESSION['SALEDATE'] != '')) {
				if ((isset($post['homesite_sold']) and $post['homesite_sold'] == 'on' and $print_blank != 1)) {
					if ($_SESSION['userid'] == 'niles.rowland@lennar.com' && 1==2) {
					echo __FILE__.':'.__LINE__.' session<pre>';
					print_r($_SESSION);
					echo '</pre>';
					sleep(30);
				}

//check if the sale date entered by NHC is blank

				if ($sale_date_contracts == '' or IS_NULL($sale_date_contracts)) {

					$sale_date_update = date("m/d/Y");


					$qu_dj = "update dj_big_fatty set saledate='$sale_date_update' where division_jde = '$division_jde' and community_jde = '$community_jde' and homesite = '$homesite' and customer_id = '$customer_id' ";


					$stid = oci_parse($db_conn, $qu_dj);
					oci_execute($stid);


				} else {
					$sale_date_update = $sale_date_contracts;
				}
				//update OTO
				if ($_SESSION['userid'] == 'niles.rowland@lennar.com' && 1==2) {
					mail('niles.rowland@lennar.com','qu_hs', 'hello');
				}

				$db_conn_wh = db_connect_wh($e_serv);
				if (!$db_conn_wh) {
					echo '<br><h2>Unable to connect to oracle database!';
				} else {
// this is problematic of the sale is CONTINGENT... should be LOTSTATUS='W' ******************************
					$qu_hs = "update homesites set HOMESITESBLSTATUS='SOLD',LOTSTATUS='B',opportunityid='$opportunity_id' where jobnumber='$job'";
					//if($bContingent == 'Y')
					//	$qu_hs = "update homesites set HOMESITESBLSTATUS='SOLD',LOTSTATUS='W',opportunityid='$opportunity_id' where jobnumber='$job'";
					if ($_SESSION['userid'] == 'niles.rowland@lennar.com' && 1==1) {
						mail('niles.rowland@lennar.com','qu_hs', $qu_hs);
					}

					$stid = oci_parse($db_conn_wh, $qu_hs);
					oci_execute($stid);

					//query homesites

					$qu_hs1 = "select phase,substr( plan,-4,4) plan,estimatedcoedate from homesites where jobnumber='$job'";

					$stid1 = oci_parse($db_conn_wh, $qu_hs1);
					oci_execute($stid1);

					while ($row = oci_fetch_array($stid1, OCI_RETURN_NULLS + OCI_ASSOC)) {
						$phase = trim($row['PHASE']);
						$plan = trim($row['PLAN']);
						$estimated_coe_date_jde = trim($row['ESTIMATEDCOEDATE']);
					}

					//added 07/30/2012,madhav kolipaka, if not picked in PA page, and date exists in jde, then it needs to write OD with jde date

					if (!isset($post['estimated_coe']) and $estimated_coe_date_jde != '' AND !is_null($estimated_coe_date_jde)) {
						$estimated_coe_date = $estimated_coe_date_jde;
					}

//add the code to update the opportunity options to homesite options
					$phase50 = $phase + 50;
					if (isset($_SESSION['SFORCE']) and $_SESSION['SFORCE'] == 'Y') {
						// need to get the legacy contact ID to test for options
						//
						// 1. look up option JJ_OPTION_HOMESITE with SFDC Contact ID
						// 2. If found, use that ID to update options, else
						// 3. Get legacy CRM Contact ID from SFDC.
						// 4. look up option with this id.
						// 5. if found, update jjoh and jjohs records to sfdc contact id and proceed with update below

						$OracleContactID__c = '';
						if (isset($_SESSION['OracleContactID__c']) and $_SESSION['OracleContactID__c'] != '')
							$OracleContactID__c = $_SESSION['OracleContactID__c'];

						$qu_jjohsfdc = "select customer_id from jj_options_homesite 
			    					WHERE (customer_id = '$customer_id' or customer_id = '$OracleContactID__c') AND division_jde = '$division' AND 
			    					community_jde ='$community_jde' AND homesite ='$homesite'
									AND phase IN ($phase ,$phase50) AND plan = '$plan' ";
						$foundit = false;
						//echo '<br>options qu: '.$qu_jjohsfdc;

						$stid1 = oci_parse($db_conn, $qu_jjohsfdc);
						oci_execute($stid1);
						while ($rowsf = oci_fetch_array($stid1, OCI_RETURN_NULLS + OCI_ASSOC)) {
							$foundit = true;
						}
						if (!$foundit) //get from SFDC the legacy CRM contact id
						{
							$object = "Contact";
							$iib = "";
							$query = "SELECT OracleContactID__c FROM Contact where id = '$customer_id'";
							//echo "<br>query: ".$query;
							$response = show_query($instance_url, $access_token, $query, $oracle_proxy, $iib, $object);
							//echo "<pre>";
							//print_r($response);
							//echo "</pre>";
							//echo '<br>rsp: '.$response['records'][0]['OracleContactID__c'];

							if (isset($response['records'][0]['OracleContactID__c']) and $response['records'][0]['OracleContactID__c'] != '') {
								//got the legacy crm contact id... just update the records
								$OracleContactID__c = $response['records'][0]['OracleContactID__c'];
								$qu_up_sf = "update XXPHP.jj_options_homesite set customer_id = '$customer_id' 
							             where customer_id = '$OracleContactID__c' and division_jde = '$division' and 
							             community_jde = '$community_jde' and homesite = '$homesite' 
							             AND phase IN ($phase ,$phase50) AND plan ='$plan' ";
								$stid1 = oci_parse($db_conn, $qu_up_sf);
								oci_execute($stid1);

								echo '<br>inside1: ' . $qu_up_sf;

								$qu_up_sf = "update XXPHP.jj_options_homesite_selected set customer_id = '$customer_id' 
							             where customer_id = '$OracleContactID__c' and division_jde = '$division' and 
							             community_jde = '$community_jde' and homesite = '$homesite' 
							             AND phase IN ($phase ,$phase50) AND option_plan ='$plan' ";
								$stid1 = oci_parse($db_conn, $qu_up_sf);
								oci_execute($stid1);
								//echo '<br>inside2: '.$qu_up_sf;
							}

						}
					}
					$qu_options1 = "UPDATE XXPHP.jj_options_homesite SET change_order_no = (SELECT NVL(MAX(change_order_no),0)  + 1
FROM XXPHP.jj_options_homesite_selected WHERE customer_id IS NULL AND division_jde = '$division'
AND community_jde ='$community_jde' AND homesite = '$homesite'  AND phase IN ($phase ,$phase50) AND option_plan ='$plan')
WHERE customer_id = '$customer_id' AND division_jde = '$division' AND community_jde ='$community_jde' AND homesite ='$homesite'
AND phase IN ($phase ,$phase50) AND plan ='$plan'";
					$stid1 = oci_parse($db_conn, $qu_options1);
					oci_execute($stid1);

					$qu_options2 = "UPDATE XXPHP.jj_options_homesite_selected SET change_order_no = (SELECT NVL(MAX(change_order_no),0)  + 1
FROM XXPHP.jj_options_homesite_selected WHERE customer_id IS NULL AND division_jde = '$division'
			AND community_jde ='$community_jde' AND homesite ='$homesite' AND phase IN ($phase ,$phase50) AND option_plan ='$plan'),
            change_order_no_del = CASE WHEN change_order_no_del IS NOT NULL THEN
            (SELECT NVL(MAX(change_order_no),0)  + 1 FROM XXPHP.jj_options_homesite_selected
            WHERE customer_id IS NULL AND division_jde = '$division' AND community_jde ='$community_jde' AND homesite ='$homesite'
            AND phase IN ($phase ,$phase50) AND option_plan ='$plan') END WHERE customer_id = '$customer_id' AND division_jde = '$division'
            AND community_jde ='$community_jde' AND homesite ='$homesite' AND phase IN ($phase ,$phase50) AND option_plan ='$plan'";
					$stid2 = oci_parse($db_conn, $qu_options2);
					oci_execute($stid2);

					$qu_options3 = "UPDATE jj_options_homesite_selected SET added_by_admin = 'Y',customer_id = NULL
			WHERE customer_id = '$customer_id' AND division_jde = '$division' AND community_jde ='$community_jde' AND homesite ='$homesite' AND
			phase IN ($phase ,$phase50) AND option_plan ='$plan' AND (SELECT COUNT(*) FROM XXPHP.jj_options_homesite
			WHERE customer_id IS NULL AND added_by_admin = 'Y' AND division_jde = '$division'
			AND community_jde ='$community_jde' AND homesite ='$homesite' AND phase IN ($phase ,$phase50) AND plan ='$plan') = 0";
					$stid3 = oci_parse($db_conn, $qu_options3);
					oci_execute($stid3);

					$qu_options4 = "UPDATE XXPHP.jj_options_homesite SET added_by_admin = 'Y', customer_id = NULL
			WHERE customer_id = '$customer_id' AND division_jde = '$division' AND community_jde ='$community_jde' AND homesite ='$homesite' AND
			phase IN ($phase ,$phase50) AND plan ='$plan' AND (SELECT COUNT(*) FROM XXPHP.jj_options_homesite
			WHERE customer_id IS NULL AND added_by_admin = 'Y' AND division_jde = '$division' AND
			community_jde ='$community_jde' AND homesite ='$homesite' AND phase IN ($phase ,$phase50) AND plan ='$plan') = 0";
					$stid4 = oci_parse($db_conn, $qu_options4);
					oci_execute($stid4);

					$qu_options5 = "UPDATE jj_options_homesite_selected SET added_by_admin = 'Y',  customer_id = NULL,
	        index_part_options_homesite =  (SELECT index_part FROM XXPHP.jj_options_homesite WHERE
	        customer_id IS NULL  AND added_by_admin = 'Y' AND division_jde = '$division' AND
	    	community_jde ='$community_jde' AND homesite ='$homesite' AND phase IN ($phase ,$phase50) AND plan ='$plan'
	    	AND rownum = 1) WHERE customer_id = '$customer_id' AND division_jde = '$division'
	    	AND community_jde ='$community_jde' AND homesite ='$homesite' AND phase IN ($phase ,$phase50) AND
	    	option_plan ='$plan' AND (SELECT COUNT(*) FROM XXPHP.jj_options_homesite  WHERE customer_id IS NULL
	    	AND added_by_admin = 'Y' AND division_jde = '$division' AND community_jde ='$community_jde' AND homesite ='$homesite'
	    	AND phase IN ($phase ,$phase50) AND plan ='$plan') > 0";
					$stid5 = oci_parse($db_conn, $qu_options5);
					oci_execute($stid5);


					$qu_options6 = "UPDATE XXPHP.jj_options_homesite SET options_price_total =  (SELECT SUM(quantity * price)
				        FROM  XXPHP.jj_options_homesite_selected  WHERE customer_id is NULL  AND added_by_admin = 'Y'
				        AND division_jde = '$division' AND community_jde ='$community_jde' AND homesite ='$homesite'  AND phase IN ($phase ,$phase50)
				        AND option_plan ='$plan'), change_order_no = (SELECT change_order_no  FROM XXPHP.jj_options_homesite
				        WHERE customer_id = '$customer_id' AND division_jde = '$division' AND community_jde ='$community_jde' AND homesite ='$homesite'
				        AND phase IN ($phase ,$phase50) AND plan ='$plan') WHERE customer_id IS NULL AND added_by_admin = 'Y' AND division_jde = '$division'
				        AND community_jde ='$community_jde' AND homesite ='$homesite' AND phase IN ($phase ,$phase50) and plan ='$plan' AND
				        (SELECT COUNT(*) FROM XXPHP.jj_options_homesite WHERE customer_id IS NULL AND added_by_admin = 'Y' AND
				        division_jde = '$division' AND community_jde ='$community_jde' AND homesite ='$homesite' AND phase IN ($phase ,$phase50)
	         AND plan ='$plan') > 0";
					$stid6 = oci_parse($db_conn, $qu_options6);
					oci_execute($stid6);


					$qu_options7 = "DELETE from  XXPHP.jj_options_homesite WHERE (customer_id = '$customer_id' or customer_id = '$OracleContactID__c') AND division_jde = '$division' AND
			        		community_jde ='$community_jde' AND homesite ='$homesite' AND phase IN ($phase ,$phase50) and plan ='$plan' AND
			 			(SELECT COUNT(*) FROM XXPHP.jj_options_homesite WHERE customer_id IS NULL AND added_by_admin = 'Y'  AND
			 	        division_jde = '$division' AND community_jde ='$community_jde' AND homesite ='$homesite' AND phase IN ($phase ,$phase50)
	                    AND plan ='$plan') > 0";
					$stid7 = oci_parse($db_conn, $qu_options7);
					oci_execute($stid7);


					$qu_options8 = "UPDATE XXPHP.jj_options_homesite SET change_order_no = (SELECT MAX(change_order_no)  FROM XXPHP.jj_options_homesite_selected
			 	        WHERE customer_id IS NULL AND division_jde = '$division' AND community_jde ='$community_jde' AND homesite ='$homesite'
			 	        AND phase IN ($phase ,$phase50) AND option_plan ='$plan'),
			 	        draft_ind = (SELECT MAX(draft_ind)  FROM XXPHP.jj_options_homesite_selected
			 	        WHERE customer_id IS NULL AND division_jde = '$division' AND community_jde ='$community_jde' AND homesite ='$homesite'
			 	        AND phase IN ($phase ,$phase50) AND option_plan ='$plan')
			 	        WHERE customer_id IS NULL AND added_by_admin = 'Y' AND division_jde = '$division'
	        AND community_jde ='$community_jde' AND homesite ='$homesite' AND phase IN ($phase ,$phase50) and plan ='$plan'";
					$stid8 = oci_parse($db_conn, $qu_options8);
					oci_execute($stid8);


					$qu_options9 = "UPDATE XXPHP.jj_options_homesite_selected SET change_order_no = (SELECT MAX(NVL(change_order_no,0)) + 1  FROM XXPHP.jj_options_homesite_selected
	        WHERE customer_id IS NULL AND division_jde = '$division' AND community_jde ='$community_jde' AND homesite ='$homesite'
	        AND phase IN ($phase ,$phase50) AND option_plan ='$plan')
	        WHERE customer_id IS NULL AND added_by_admin = 'Y' AND division_jde = '$division'
	        AND community_jde ='$community_jde' AND homesite ='$homesite' AND phase IN ($phase ,$phase50) and option_plan ='$plan' and draft_ind = 'Y'";
					$stid9 = oci_parse($db_conn, $qu_options9);
					oci_execute($stid9);


					$qu_options10 = "UPDATE XXPHP.jj_options_homesite SET change_order_no = (SELECT MAX(change_order_no) FROM XXPHP.jj_options_homesite_selected
	        WHERE customer_id IS NULL AND division_jde = '$division' AND community_jde ='$community_jde' AND homesite ='$homesite'
	        AND phase IN ($phase ,$phase50) AND option_plan ='$plan')
	        WHERE customer_id IS NULL AND added_by_admin = 'Y' AND division_jde = '$division'
	        AND community_jde ='$community_jde' AND homesite ='$homesite' AND phase IN ($phase ,$phase50) and plan ='$plan'";
					$stid10 = oci_parse($db_conn, $qu_options10);
					oci_execute($stid10);


					ini_set('max_execution_time', '7200');
					require_once('lib/nusoap.php');
				}

				//update SIEBEL CRM onDemand
				if (isset($_SESSION['SFORCE']) and $_SESSION['SFORCE'] == 'Y') {
					$sale_date_updatesf = date('Y-m-d', strtotime($sale_date_update));
					$estimated_coe_datesf = date('Y-m-d', strtotime($estimated_coe_date));
					//set the Buyer PA Signed Date...
					// echo "<br>Opportunity ID:  ".$opportunity_id;
					// echo "<br>Sold date : ".$sale_date_updatesf;
					// echo "<br>ECOE date : ".$estimated_coe_datesf;
					$object = 'Opportunity';
					$id = $opportunity_id;
					$json = json_encode(array("Buyer_PA_Signed_Date__c" => $sale_date_updatesf,
						"Solar_Type__c" => $solar_type,
						"Estimated_CoE__c" => $estimated_coe_datesf));
					if ($plContingency_Type != '') {
						$json = json_encode(array("Buyer_PA_Signed_Date__c" => $sale_date_updatesf,
							"Contingent__c" => true,
							"Contingency_Type__c" => $plContingency_Type,
							"Solar_Type__c" => $solar_type,
							"Estimated_CoE__c" => $estimated_coe_datesf));
					}
					echo "<br>json: " . $json;
					$body = "Opp: " . $opportunity_id;
					$body = $body . "\r\n Server: " . $e_serv;
					$body = $body . "\r\n Division: " . $division_jde;
					$body = $body . "\r\n Community: " . $community_jde;
					$body = $body . "\r\n Homesite: " . $homesite;
					$body = $body . "\r\n Job: " . $job;
					$body = $body . "\r\n Sold Date: " . $sale_date_updatesf;
					$body = $body . "\r\n ECOE date: " . $estimated_coe_datesf;
					$body = $body . "\r\n instance_url: " . $instance_url;
					$body = $body . "\r\n access_token: " . $access_token;
					$body = $body . "\r\n\r\n JSON: " . $json;

					$from = "From: Jeff McKenzie<jeff.mckenzie@lennar.com>";
					$json = str_replace('\\', '', $json);

					$iib = '';
					$jsond = json_decode($json, true);

					echo '<pre>';
					print_r($jsond);
					echo '</pre>';
					echo '<br>Object   : ' . $object;
					echo '<br>id   : ' . $id;
					echo '<br>json       : ' . $json;
					echo '<br>instance_url   : ' . $instance_url;
					echo '<br>access_token   : ' . $access_token;
					echo '<br>oracle_proxy   : ' . $oracle_proxy;

					$returned = "blah";
					$returned = update_object($object, $id, $json, $instance_url, $access_token, $oracle_proxy, $iib);
					echo "<br>back from update: " . $returned;
					//alert_msg($returned);
					$body = $body . "\r\nReturned: " . $returned;
					mail('jeff.mckenzie@lennar.com', "Mark Home Sold from PAs - " . $e_serv . ' Opp: ' . $opportunity_id, $body, $from);
					if ($returned != 204 and $returned != 200)
						mail('roseline.seweje@lennar.com', "Error Marking Sold from PAs - " . $e_serv . ' Opp: ' . $opportunity_id, $body, $from);
				} else {
					if (!isset($pwd_crm) or $pwd_crm == '')
						$pwd_crm = 'Lennar2011b*';
					//login to siebel
					/*
					if($_SERVER['SERVER_NAME'] == 'otolennprod.oracleoutsourcing.com')
					{
					$crmdomain = 'https://secure-ausomxffa.crmondemand.com';
					}
					else
					{
					$crmdomain = 'https://secure-ausomxfga.crmondemand.com';
					   }
					*/
					$url = $crmdomain . "/Services/Integration?command=login";
					$page = "/Services/Integration?command=login";
					$headers = array("GET " . $page . " HTTP/1.0", "UserName: LENNAR/PROD.SYSADMIN", "Password: $pwd_crm",);
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
					//	curl_setopt($ch, CURLOPT_PROXY, 'http://www-proxy-adc.us.oracle.com');
					curl_setopt($ch, CURLOPT_PROXY, $oracle_proxy);
					curl_setopt($ch, CURLOPT_PROXYPORT, 80);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
					curl_setopt($ch, CURLOPT_HEADER, true);
					$data = curl_exec($ch);
					$sessionid = substr($data, (strpos($data, "Set-Cookie:") + 23), (strpos($data, ";") - strpos($data, "Set-Cookie:") - 23));
					curl_close($ch);

					//below is for querying and updating siebel ondemand objects

					//update opportunity
					$serverpath = $crmdomain . "/Services/Integration;jsessionid=$sessionid";
					$namespace = "urn:crmondemand/ws/opportunity/10/2004";
					$soapaction = "document/urn:crmondemand/ws/opportunity/10/2004:OpportunityUpdate";
					$param = "<ListOfOpportunity>
					<Opportunity>
					<OpportunityId>$opportunity_id</OpportunityId>
					<SalesStage>Purchase Agreement Pending</SalesStage>
					<dSale_Date>$sale_date_update</dSale_Date>
					<bContingent>$bContingent</bContingent>
					<plContingency_Type>$plContingency_Type</plContingency_Type>
					<plSolar_Type>$solar_type</plSolar_Type>";
					//if($estimated_coe_date_jde=='' OR is_null($estimated_coe_date_jde))
					//{
					$param = $param . "<dWalkIn_Date>$estimated_coe_date</dWalkIn_Date>";
					//}

					$param = $param . "</Opportunity>
					</ListOfOpportunity>";
					$method = "OpportunityWS_OpportunityUpdate_Input";
					$client = new nusoap_client($serverpath);
					//$client->setHTTPProxy('http://www-proxy-adc.us.oracle.com' , 80 , '' , '');
					$client->setHTTPProxy($oracle_proxy, 80, '', '');
					$debug = '';
					$client->setHeaders("$debug");
					$response = $client->call($method, $param, $namespace, $soapaction);
					$soapdata = htmlspecialchars($client->responseData, ENT_QUOTES);
					$check = substr($soapdata, strpos($soapdata, "ns:LastPage") + 15, 5);

					//Query oportunity
					$serverpath = $crmdomain . "/Services/Integration;jsessionid=$sessionid";
					$namespace = "urn:crmondemand/ws/opportunity/10/2004";
					$soapaction = "document/urn:crmondemand/ws/opportunity/10/2004:OpportunityQueryPage";
					$param = "<PageSize>1</PageSize>
					<ListOfOpportunity>
					<Opportunity>
					<OpportunityId>='$opportunity_id'</OpportunityId>
					<OpportunityName></OpportunityName>
					<cBase_Price></cBase_Price>
					<cHomesite_Premium></cHomesite_Premium>
					<cTotal_Options></cTotal_Options>
					<cHomesite_Incentive></cHomesite_Incentive>
					<cCustomer_Incentive></cCustomer_Incentive>
					<Revenue></Revenue>
					<Owner></Owner>
					</Opportunity>
					</ListOfOpportunity>
					<StartRowNum>0</StartRowNum>";
					$method = "OpportunityWS_OpportunityQueryPage_Input";
					$client = new nusoap_client($serverpath);
					//	$client->setHTTPProxy('http://www-proxy-adc.us.oracle.com' , 80 , '' , '');
					$client->setHTTPProxy($oracle_proxy, 80, '', '');
					$debug = '';
					$client->setHeaders("$debug");
					$response = $client->call($method, $param, $namespace, $soapaction);
					$soapdata = htmlspecialchars($client->responseData, ENT_QUOTES);
					$check = substr($soapdata, strpos($soapdata, "ns:LastPage") + 15, 5);

					if (($_SESSION['userid'] == 'jeff.mckenzie@lennar.com' || $_SESSION['userid'] == 'niles.rowland@lennar.com') && 1 == 1) {
						mail('niles.rowland@lennar.com', 'response', $response);
						echo __FILE__.':'.__LINE__.' response<pre>';
						print_r($response);
						echo '</pre>';
						sleep(30);
					}


					if (isset($response['ListOfOpportunity']['Opportunity'])) {
						$base_price = $response['ListOfOpportunity']['Opportunity']['cBase_Price'];
						$hs_premium = $response['ListOfOpportunity']['Opportunity']['cHomesite_Premium'];
						$total_options = $response['ListOfOpportunity']['Opportunity']['cTotal_Options'];
						$hs_incentive = $response['ListOfOpportunity']['Opportunity']['cHomesite_Incentive'];
						$cus_incentive = $response['ListOfOpportunity']['Opportunity']['cCustomer_Incentive'];
						$total_price = $response['ListOfOpportunity']['Opportunity']['Revenue'];
						$owner = $response['ListOfOpportunity']['Opportunity']['Owner'];
						$oppty_name = $response['ListOfOpportunity']['Opportunity']['OpportunityName'];
					}

					/*$body.= 'Total Options: '.$total_options.'
	';
	$body.= 'Total Price: '.$total_price.'
	';
	$body.= 'Job #: '.$job.'
	';
	$body.= 'Opportunity #: '.$opportunity_id.'
	';
	$body.= 'UserId #: '.$userid.'
	';
	mail('jeff.mckenzie@lennar.com', 'Help with WS '.time(), $body);
	*/

					//query homesite
					$serverpath = $crmdomain . "/Services/Integration;jsessionid=$sessionid";
					$namespace = "urn:crmondemand/ws/customobject1/10/2004";
					$soapaction = "document/urn:crmondemand/ws/customobject1/10/2004:CustomObject1QueryPage";
					$param = "<PageSize>1</PageSize>
					 <ListOfCustomObject1>
					 <CustomObject1>
					 <ExternalSystemId>='$job'</ExternalSystemId>
					 <Name></Name>
					  </CustomObject1>
					 </ListOfCustomObject1>
					 <StartRowNum>0</StartRowNum>";
					$method = "CustomObject1WS_CustomObject1QueryPage_Input";
					$client = new nusoap_client($serverpath);
					// $client->setHTTPProxy('http://www-proxy-adc.us.oracle.com' , 80 , '' , '');
					$client->setHTTPProxy($oracle_proxy, 80, '', '');
					$debug = '';
					$client->setHeaders("$debug");
					$response = $client->call($method, $param, $namespace, $soapaction);
					$soapdata = htmlspecialchars($client->responseData, ENT_QUOTES);
					$check = substr($soapdata, strpos($soapdata, "ns:LastPage") + 15, 5);

					if ($_SESSION['userid'] == 'jeff.mckenzie@lennar.com' and 1 == 2) {
						echo '<pre>';
						print_r($response);
						echo '</pre>';
					}

					if (isset($response['ListOfCustomObject1']['CustomObject1'])) {
						$hs_address = $response['ListOfCustomObject1']['CustomObject1']['Name'];
					}


					//update contact
					$serverpath = $crmdomain . "/Services/Integration;jsessionid=$sessionid";
					$namespace = "urn:crmondemand/ws/contact/10/2004";
					$soapaction = "document/urn:crmondemand/ws/contact/10/2004:ContactUpdate";
					$param = "<ListOfContact>
					<Contact>
					<ContactId>$contact_id</ContactId>
					<IndexedPick0>Buyer</IndexedPick0>
					<CustomObject1Name>$hs_address</CustomObject1Name>
					</Contact>
					</ListOfContact>";
					$method = "ContactWS_ContactUpdate_Input";
					$client = new nusoap_client($serverpath);
					//$client->setHTTPProxy('http://www-proxy-adc.us.oracle.com' , 80 , '' , '');
					$client->setHTTPProxy($oracle_proxy, 80, '', '');
					$debug = '';
					$client->setHeaders("$debug");
					$response = $client->call($method, $param, $namespace, $soapaction);
					$soapdata = htmlspecialchars($client->responseData, ENT_QUOTES);
					$check = substr($soapdata, strpos($soapdata, "ns:LastPage") + 15, 5);


					//Update homesite schema for total options
					$serverpath = $crmdomain . "/Services/Integration;jsessionid=$sessionid";
					$namespace = "urn:crmondemand/ws/customobject1/10/2004";
					$soapaction = "document/urn:crmondemand/ws/customobject1/10/2004:CustomObject1Update";
					$param = "<ListOfCustomObject1>
					<CustomObject1>
					<ExternalSystemId>$job</ExternalSystemId>
					<IndexedPick2>SOLD</IndexedPick2>
					<CustomText62>$opportunity_id</CustomText62>
					<CustomText5>$oppty_name</CustomText5>
					<ContactId>$contact_id</ContactId>
					<IndexedCurrency0>$base_price</IndexedCurrency0>
					<CustomCurrency6>$hs_premium</CustomCurrency6>
					<CustomCurrency11>$total_options</CustomCurrency11>
					<CustomCurrency13>$hs_incentive</CustomCurrency13>
					<CustomCurrency3>$cus_incentive</CustomCurrency3>
					<CustomCurrency12>$total_price</CustomCurrency12>
					<CustomDate47>$sale_date_update</CustomDate47>";

					//below to update the COE date in OD only when jde has no value, this also means the
					//date picker field in purchase agreements is enabled to pick a date.

					//if($estimated_coe_date_jde=='' OR is_null($estimated_coe_date_jde))
					//{

					$param = $param . "<dEstimated_COE>$estimated_coe_date</dEstimated_COE>";

					//}

					$param = $param . "</CustomObject1>
					</ListOfCustomObject1>";
					$method = "CustomObject1WS_CustomObject1Update_Input";
					$client = new nusoap_client($serverpath);
					//$client->setHTTPProxy('http://www-proxy-adc.us.oracle.com' , 80 , '' , '');
					$client->setHTTPProxy($oracle_proxy, 80, '', '');
					$debug = '';
					$client->setHeaders("$debug");
					$response = $client->call($method, $param, $namespace, $soapaction);
					$soapdata = htmlspecialchars($client->responseData, ENT_QUOTES);
					$check = substr($soapdata, strpos($soapdata, "ns:LastPage") + 15, 5);


					//insert a completed activity of type milestone

					$description = 'A Homesite has been Sold. Affected Homesite Job Number is - ' . $job;

					$serverpath = $crmdomain . "/Services/Integration;jsessionid=$sessionid";
					$namespace = "urn:crmondemand/ws/activity/10/2004";
					$soapaction = "document/urn:crmondemand/ws/activity/10/2004:Activity_Insert";
					$param = "<ListOfActivity>
						<Activity>
						<Activity>Task</Activity>
						<Subject>Homesite Sold</Subject>
						<IndexedPick1>Buyer</IndexedPick1>
						<Type>Milestone</Type>
						<Priority>2-Medium</Priority>
						<DueDate>$sale_date_update</DueDate>
						<StartTime>$sale_date_update</StartTime>
						<EndTime>$sale_date_update</EndTime>
						<Status>Completed</Status>
						<Description>$description</Description>
						<OpportunityId>$opportunity_id</OpportunityId>
						<CustomObject1ExternalSystemId>$job</CustomObject1ExternalSystemId>
						<Alias>$owner</Alias>
						</Activity>
						</ListOfActivity>";
					$method = "ActivityNWS_Activity_Insert_Input";
					$client = new nusoap_client($serverpath);
					//$client->setHTTPProxy('http://www-proxy-adc.us.oracle.com' , 80 , '' , '');
					$client->setHTTPProxy($oracle_proxy, 80, '', '');
					$debug = '';
					$client->setHeaders("$debug");
					$response = $client->call($method, $param, $namespace, $soapaction);
					$soapdata = htmlspecialchars($client->responseData, ENT_QUOTES);
					$check = substr($soapdata, strpos($soapdata, "ns:LastPage") + 15, 5);


					//Script to Logoff from Siebel Server
					$logoff = $logoff . $sessionid . "?command=logoff";
					$ch1 = curl_init();
					//curl_setopt($ch1, CURLOPT_PROXY, 'http://www-proxy-adc.us.oracle.com');
					curl_setopt($ch1, CURLOPT_PROXY, $oracle_proxy);
					curl_setopt($ch1, CURLOPT_PROXYPORT, 80);
					curl_setopt($ch1, CURLOPT_URL, $logoff);
					curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, FALSE);
					$data1 = curl_exec($ch1);
					$st = curl_getinfo($ch1, CURLINFO_HTTP_CODE);
					curl_close($ch1);
				}
			}


//now exit to stop the redirect
			if ($_SESSION['userid'] == 'jeff.mckenzie@lennar.com' or 1 == 1) {
				echo '<br>Userid:' . $_SESSION['userid'];
			}
			//below is for merging the selected documents and the rest of the process

			$division_jde = $_SESSION['division_jde_selected'];
			$community_jde = $_SESSION['community_jde_selected'];
			$customer_id = $_SESSION['customer_id'];
			$qu_reg = "select division, region_jde, extra_jsp from dj_division where division_jde = '$division_jde' ";
			//get homesite from integration...
			$homesite = $_SESSION['homesite_selected'];

			$stid = oci_parse($db_conn, $qu_reg);
			oci_execute($stid);
			while ($row2 = oci_fetch_array($stid, OCI_RETURN_NULLS + OCI_ASSOC)) {
				//here if doc is assigned...
				$division = html_entity_decode(trim($row2['DIVISION']), ENT_QUOTES);
				$region_jde = html_entity_decode(trim($row2['REGION_JDE']), ENT_QUOTES);
				$extra_jsp = trim($row2['EXTRA_JSP']);
				$_SESSION['extra_jsp'] = $extra_jsp;
			}

			$qu_comm = "select community from dj_community where division_jde = '$division_jde' and community_jde = '$community_jde' ";

			$stid = oci_parse($db_conn, $qu_comm);
			oci_execute($stid);
			while ($row3 = oci_fetch_array($stid, OCI_RETURN_NULLS + OCI_ASSOC)) {
				//here if doc is assigned...
				$community = html_entity_decode(trim($row3['COMMUNITY']), ENT_QUOTES);
			}

			$description_my_doc = '';
			$level_desc_first = '';

			$first_print = true;
			$first_draft = true;
			$counterrs = 0;

			$col = 1;
			$doc_print_count = 0;
			$doc_count = $post['doc_count'];

			for ($doc_loop = 0; $doc_loop <= $doc_count; $doc_loop++) {
				$body = '';
				if ((isset($post['check_box_doc_print' . $doc_loop]) and $print_blank != 1) OR ($print_blank == 1 and isset($post['check_box_doc_print_bl' . $doc_loop]))) {
					$doc_print_count++;
				}
			}

			//added the below condition to filter the main loop when print blank docs button is clicked
			//02-04-2011
			if ($print_blank != 1) {
				$description = '';
				$level_desc = '';
				$revision_date = '';
				$header_1 = '';
				$footer_1 = '';
				$lines_per_page = '';
				$chars_per_line = '';
				$doc_num = '';
				$source_doc = '';
				$report_html = 'R';
				$source_blank_doc = '';
				$version_num = '';
				$filepath = '';

				$_SESSION['options_print'] = '';
				unset($_SESSION['docs']);

				for ($doc_loop = 0; $doc_loop <= $doc_count; $doc_loop++) {
					$body = '';
					$report_html = 'R';
					if (isset($post['check_box_doc_print' . $doc_loop])) {
						$index_part_doc = $post['index_part_doc' . $doc_loop];
						$qu_doc = "select * from dj_documents where index_part = '$index_part_doc'";
						$stid3 = oci_parse($db_conn, $qu_doc);
						oci_execute($stid3);
						while ($row5 = oci_fetch_array($stid3, OCI_RETURN_NULLS + OCI_ASSOC)) {
							$description = html_entity_decode(trim($row5['DESCRIPTION']), ENT_QUOTES);

							$pos = strpos(strtolower($description), 'credit card addendum');
							if ($pos === false)
								$cc_addendum = false;
							else
								$cc_addendum = true;

							$level_desc = html_entity_decode(trim($row5['LEVEL_DESC']), ENT_QUOTES);
							$version_num = html_entity_decode(trim($row5['VERSION_NUM']), ENT_QUOTES);
							$revision_date = html_entity_decode(trim($row5['REVISION_DATE']), ENT_QUOTES);
							$header_1 = html_entity_decode(trim($row5['HEADER_1']), ENT_QUOTES);
							$footer_1 = html_entity_decode(trim($row5['FOOTER_1']), ENT_QUOTES);
							$lines_per_page = html_entity_decode(trim($row5['LINES_PER_PAGE']), ENT_QUOTES);
							$chars_per_line = html_entity_decode(trim($row5['CHARS_PER_LINE']), ENT_QUOTES);
							$doc_num = html_entity_decode(trim($row5['DOC_NUM']), ENT_QUOTES);
							$doc_num = str_replace(';', '', str_replace('&', '', str_replace('-', '_', str_replace('.', '', str_replace(' ', '_', $doc_num)))));
							$source_doc = trim($row5['SOURCE_DOC']);
							$report_html = trim($row5['REPORT_HTML']);
							$source_blank_doc = trim($row5['SOURCE_BLANK_DOC']);
							$_SESSION['docs'][$source_doc] = $source_doc;
						}


						//options_addition_02142012

						//query dj_documents_options table
						$options_doc = 0;
						$qu_op = "select * from dj_documents_options where index_part = '$index_part_doc' and opportunityid = '$opportunity_id'";
						$stidop = oci_parse($db_conn, $qu_op);
						/*
echo '<pre>';
echo '<br/>q: '.$qu_op;
echo '</pre>';
exit;
*/

						oci_execute($stidop);
						while ($row3op = oci_fetch_array($stidop, OCI_RETURN_NULLS + OCI_ASSOC)) {
							$_SESSION['options_print'] = 'Y';

							$op_filename = trim($row3op['FILE_NAME']);
							$op_filename1 = 'options/' . $op_filename;
							$jsp = str_replace("options_summary.pdf", "Options_Summary.jsp", $op_filename);
							$jsp = str_replace("change_order.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_01.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_02.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_03.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_04.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_05.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_06.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_07.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_08.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_09.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_10.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_11.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_12.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_13.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_14.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_15.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_16.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_17.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_18.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_19.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_20.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_21.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_22.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_23.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_24.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_25.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_26.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_27.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_28.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_29.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_30.pdf", "Change_Order.jsp", $jsp);
							$jsp = str_replace("change_order_99.pdf", "Change_Order.jsp", $jsp);
							$_SESSION['docs'][$jsp] = $jsp;

							//below is the filter that would be used later in this script
							//while creating the merged files list
							$options_doc = 1;

							if (trim($merged_files) == '') {
								$merged_files = $op_filename1;
							} else {
								$merged_files = $merged_files . ', ' . $op_filename1;

							}


						}

						if ($report_html != 'R') {
							echo '<br/>shouldnt get here';
						}//end html
						else //oracle report
						{
							if (!isset($OracleContactID__c))
								$OracleContactID__c = '';

//if($userid == 'jeff.mckenzie@lennar.com')
//	exit;
							// $link = 'http://10.0.0.30:7778/reports/rwservlet?wrldclas&CONBAH02.rdf&P_IDX=7&P_DOCID=1725723V.4&P_RDATE=8/25/08&P_VNUM=3';
							$last_print_date = date('d-M-y');

							$qu_check = "select index_part from dj_customer_comm_home_docs where (customer_id = '$customer_id' or customer_id = '$OracleContactID__c') and
							division_jde = '$division_jde' and   community_jde = '$community_jde' and index_part_doc = '$index_part_doc'
							and  homesite 	= '$homesite' ";

							$index_part_check = 0;

							$stid_check = oci_parse($db_conn, $qu_check);

							oci_execute($stid_check);

							while ($row_check = oci_fetch_array($stid_check, OCI_RETURN_NULLS + OCI_ASSOC)) {
								//here if doc is assigned...
								$index_part_check = html_entity_decode(trim($row_check['INDEX_PART']), ENT_QUOTES);
							}
							if ($index_part_check == 0) //insert
							{
								$qu_print_date = "insert into dj_customer_comm_home_docs (customer_id, index_part_doc, division_jde, community_jde, homesite,last_print_date, document_complete, version_num)
								values ('$customer_id', '$index_part_doc', '$division_jde', '$community_jde', '$homesite','$last_print_date', 'N', 0)";
							} else {
								$qu_print_date = "update dj_customer_comm_home_docs  set last_print_date 	= '$last_print_date'
																								where index_part_doc 	= '$index_part_doc' and
																							            (customer_id = '$customer_id' or customer_id 	= '$OracleContactID__c') and
																										  division_jde	= '$division_jde' and
																										  community_jde	= '$community_jde' and
																										  homesite		= '$homesite' ";
							}
							$stid188 = oci_parse($db_conn, $qu_print_date);
							oci_execute($stid188);

							$qu_fatty = "select index_part from dj_big_fatty where division_jde  = '$division_jde' and
																				   community_jde = '$community_jde' and
																				   customer_id   = '$customer_id' and
																				   homesite		 = '$homesite' ";
							$stidf = oci_parse($db_conn, $qu_fatty);
							oci_execute($stidf);
							while ($rowfatty = oci_fetch_array($stidf, OCI_RETURN_NULLS + OCI_ASSOC)) {
								$p_indx = trim($rowfatty['INDEX_PART']);
							}
							$my_host = $_SERVER['HTTP_HOST'];

							//exec($cmd . " > /dev/null &");

							$made_dir = 999;

							$fname = $_SESSION['BUYER1_FIRSTNAME'];
							$lname = $_SESSION['BUYER1_LASTNAME'];

							$bad_chars = array("+", "'", " ", "-", ".", '"', ":", ";", ",", "%", "#", "&", "*", "(", ")", "$", "?", "@", "~", "|", "!", "^", "{", "}", "[", "]", "\\");
							$no_quotes = array("'", '"');

							//$vowels = array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U");
							//$onlyconsonants = str_replace($vowels, "", "Hello World of PHP");


							$nhc = $_SESSION['NEWHOMECONSULTANT_NAME'];

							if (strpos($fname, "'") != false and strpos($fname, "''") === false)
								$fname = str_replace("'", "''", $fname);
							if (strpos($lname, "'") != false and strpos($lname, "''") === false) {
								$lname = str_replace("'", "''", $lname);
							}
							/* else
                            	{
                            	echo '<br />didnt go inside<br />';
                            	}
*/
							if (strpos($doc_num, "'") != false and strpos($doc_num, "''") === false)
								$doc_num = str_replace("'", "''", $doc_num);
							if (strpos($description, "'") != false and strpos($description, "''") === false)
								$description = str_replace("'", "''", $description);
							if (strpos($version_num, "'") != false and strpos($version_num, "''") === false)
								$version_num = str_replace("'", "''", $version_num);
							if (strpos($nhc, "'") != false and strpos($nhc, "''") === false)
								$nhc = str_replace("'", "''", $nhc);


							$report_location = "contracts/" . $division_jde;
							if (!file_exists($report_location))
								$made_dir = @mkdir($report_location, 0775);
							@chmod($report_location, 0775);

							$report_location = "contracts/" . $division_jde . "/" . $community_jde;
							if (!file_exists($report_location))
								$made_dir = @mkdir($report_location, 0775);
							@chmod($report_location, 0775);

							//homesite can have funky chars
							$homesite_xxx = str_replace($bad_chars, '_', $homesite);

							$report_location = "contracts/" . $division_jde . "/" . $community_jde . "/" . $homesite_xxx;
							if (!file_exists($report_location))
								$made_dir = @mkdir($report_location, 0775);
							@chmod($report_location, 0775);

							$report_location = "contracts/" . $division_jde . "/" . $community_jde . "/" . $homesite . "/" . preg_replace("([^a-zA-Z 0-9/]+)", "_", str_replace(' ', '', str_replace('/', '', str_replace("''", "_", $lname)))) . "_" . preg_replace("([^a-zA-Z 0-9/]+)", "_", trim(str_replace(' ', '', str_replace('/', '', str_replace("''", "_", $fname)))));
							//$report_location = preg_replace("([^a-zA-Z 0-9/]+)","_",str_replace($bad_chars, '_', $report_location));
							if (!file_exists($report_location))
								$made_dir = @mkdir($report_location, 0775);
							@chmod($report_location, 0775);

							$report_location1 = $report_location;
							$report_location = "/u03/www/html/" . $report_location;

							$date_time_append = date("Y_m_d_H_i_s");

							//added the if 05/06/2010,mak


							$doc_num1 = '';

							if ($post['other_pdf' . $doc_loop] != 'y') {
								$doc_num1 = trim(str_replace('"', '_', (str_replace("'", '_', (str_replace('-', '_', str_replace('.', '', str_replace(' ', '_', $doc_num)))))))) . '.pdf';

								$doc_num1 = trim(str_replace('"', '_', (str_replace("'", '_', (str_replace('-', '_', str_replace('.', '', str_replace(' ', '_', $doc_num)))))))) . '_' . $date_time_append . '.pdf';
								$filepath = $report_location1 . '/' . $doc_num1;


								if ($_POST['archive'] == 1) {
									$fp = fopen($filepath, 'w');
									chmod($filepath, 0775);
									fclose($fp);
								}

								$filename = $doc_num . '_' . $date_time_append;


								$qu_insert = "insert into XXPHP.DJ_CUST_COMM_HOME_DOCS_VER(division_jde, community_jde, customer_id, index_part_doc, customer_first_name, customer_last_name, filename, filepath, document_number, document_desc, created_date, homesite, archived_date, version_num, entered_by)
														 values('$division_jde', '$community_jde', '$customer_id', '$index_part_doc', '$fname','$lname','$filename','$filepath','$doc_num','$description','$revision_date','$homesite', sysdate, '$version_num', '$nhc') ";
								//echo $qu_insert;

								$stid = oci_parse($db_conn, $qu_insert);
								$ok = oci_execute($stid);


							} //end of if

							if ($p_indx != $_SESSION['djbf_index_part']) {
								$_SESSION['djbf_index_part'] = $p_indx;
							}

//if($_SERVER['SERVER_NAME'] == 'otolenntest.oracleoutsourcing.com')

							$filepath = $document_root . $filepath;
							$java_path = $document_root . $report_location1;
							$waitfile = $filepath;
							$report_location_old = $report_location1;
							if ($post['other_pdf' . $doc_loop] != 'y') {
								if ($e_serv == 'Staging') {
									$link = $report_link . 'rwservlet?destype=file&desformat=PDF&desname=' . $filepath . '&userid=xxphp/con1rac1s@vmohslenn001.oracleoutsourcing.com:13510/TLEN3O.ORACLEOUTSOURCING.COM&authid=testuser/Welcome1&report=' . $source_doc . '&P_IDX=' . $p_indx . '&P_DOCID=' . $doc_num . '&P_RDATE=' . $revision_date . '&P_VNUM=' . $version_num;
									$link2 = $report_link . 'rwservlet?destype=cache&desformat=PDF&userid=xxphp/con1rac1s@vmohslenn001.oracleoutsourcing.com:13510/TLEN3O.ORACLEOUTSOURCING.COM&authid=testuser/Welcome1&report=' . $source_doc . '&P_IDX=' . $p_indx . '&P_DOCID=' . $doc_num . '&P_RDATE=' . $revision_date . '&P_VNUM=' . $version_num;
								} ELSE {
									$link = $report_link . 'rwservlet?wrldclas2&report=' . $source_doc . '&desname=' . $filepath . '&P_IDX=' . $p_indx . '&P_DOCID=' . $doc_num . '&P_RDATE=' . $revision_date . '&P_VNUM=' . $version_num;
									$link2 = $report_link . 'rwservlet?wrldclas&report=' . $source_doc . '&P_IDX=' . $p_indx . '&P_DOCID=' . $doc_num . '&P_RDATE=' . $revision_date . '&P_VNUM=' . $version_num;
								}

								$returnvar = 0;

								$ch = curl_init();

								$fp2 = fopen($filepath . '_log.html', 'a');
								chmod($filepath . '_log.html', 0775);

								// set URL and other appropriate options
								//if($userid == 'jeff.mckenzie@lennar.com')
								curl_setopt($ch, CURLOPT_FILE, $fp2);

								curl_setopt($ch, CURLOPT_URL, $link);
								curl_setopt($ch, CURLOPT_HEADER, 0);
								curl_setopt($ch, CURLOPT_TIMEOUT, 240);
								//curl_setopt($ch, CURLOPT_PROXY, 'http://www-proxy-adc.us.oracle.com');
								curl_setopt($ch, CURLOPT_PROXY, $oracle_proxy);
								curl_setopt($ch, CURLOPT_PROXYPORT, 80);
								curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

								// grab URL and pass it to the browser
								curl_exec($ch);

								// close cURL resource, and free up system resources
								curl_close($ch);
								fclose($fp2);

								//now check to see if the oracle reports finished successfully
								//there will be the text "Successfully run" in the log file
								$log_results = file_get_contents($filepath . '_log.html');
								$pos = strpos($log_results, "success");
								if ($pos === false)  // ********************* get rid of
								{
									//https://otolennprod.oracleoutsourcing.com/contracts/contract_select_community_nhc.php?from_java=yes&java_customer_id=AFFA-9Z9I51&java_homesite_id=1213&java_community_jde=6333
									$url_link = $contract_link . 'contract_select_community_nhc.php?from_java=yes';
									$url_link = $url_link . '&java_customer_id=' . $customer_id . '&java_homesite_id=' . $homesite . '&java_community_jde=' . $community_jde;
									//look for REP-
									$report_error1 = strpos($log_results, '<pre>') + 5;
									$report_error2 = strpos($log_results, '</pre>', $report_error1) - 1;
									$len = $report_error2 - $report_error1;
									$report_error = substr($log_results, $report_error1, $len);
									$counterrs++;
									if ($counterrs < 2) //*******************************                   change back to < 2)
									{
										$sendcrash = strpos($log_results, 'rwEng-0 crashed');
										$sendcrash2 = strpos($log_results, 'rwEng-1 crashed');
										if ($sendcrash === false and $sendcrash2 === false and strlen($log_results) > 10) {
											$tototo = 'jeff.mckenzie@lennar.com, roseline.seweje@lennar.com, jens.penske@lennar.com, 
					           								tekeshia.mcminns@lennar.com, allen.fazel@lennar.com, victoria.schmid@lennar.com, 
					           								joyce.garcia@lennar.com';
											if ($e_serv == 'StagingNew' or $e_serv == 'Development')
												$tototo = 'jeff.mckenzie@lennar.com, jens.penske@lennar.com, victoria.schmid@lennar.com, joyce.garcia@lennar.com';
											mail($tototo, '*** ' . $e_serv . ': Oracle Reports Error from Contracts', '
												Division:     ' . $division_jde . '
												Community:    ' . $community_jde . '
												Homesite:     ' . $homesite . '
												NHC:          ' . $userid . '
												Contract URL: ' . $url_link . '
												Report URL:   ' . $link . '
												
												Return from Oracle Reports: ' . html_entity_decode($report_error) . '
												
												______________________________
												XML: ' . $log_results, 'From: Jeff McKenzie <jeff.mckenzie@lennar.com>');
											if ($userid == '') {
												$userid = $_SESSION['NEWHOMECONSULTANT_NAME'];
												$userid = str_replace(" ", ".", $userid) . "@lennar.com";
											}
											if (1 == 2 and $userid != 'jeff.mckenzie@lennar.com' and $userid != '' and $e_serv == 'ProductionNew') //don't send in stage
											{
												//mail('jeff.mckenzie@lennar.com, roseline.seweje@lennar.com, jens.penske@lennar.com, kerry.hedman@lennar.com, tekesia.mcminns@lennar.com','*** Oracle Reports Error from Contracts: '.$userid,'NHC:'.$_SESSION['nhc'], 'From: Jeff McKenzie <jeff.mckenzie@lennar.com>');
												mail($userid, '*** Oracle Reports Error from Contracts', '
													Division:   ' . $division_jde . '
													Community:  ' . $community_jde . '
													Homesite:   ' . $homesite . '
													NHC:        ' . $userid . '
													Report URL: ' . $link . '
													
													Return from Oracle Reports: ' . html_entity_decode($report_error), 'From: Jeff McKenzie <jeff.mckenzie@lennar.com>');
											}
										}
									}
								}
								unlink($filepath . '_log.html');

								//if($doc_print_count == 10000 or $cc_addendum)
								if ($doc_print_count == 10000)    //*** prevent going into this loop...
								{
									?>
                                    <script language="JavaScript">
                                        // var newWindow = window.open(redirecturl, "new_window_<?php echo $doc_loop ?>");
                                        var newWindow2 = window.open(redirecturl2, "new_window1_<?php echo $doc_loop ?>");
                                    </script>
									<?php

									/*			//delete the dj_customer_comm_home credit card field records
									$q_credit = "delete from dj_customer_comm_home where division_jde = '$division_jde' and
																						 community_jde 		= '$community_jde' and
																						 homesite			= '$homesite' and
																						 customer_id 		= '$customer_id' and
																						 (field_name = 'CREDITCARDNUMBER' or
																						  field_name = 'CREDITCARDEXPDATE' or
																						  field_name = 'CREDITCARDCODE' ) ";
									$stidcr = oci_parse($db_conn, $q_credit);
									$ok = oci_execute($stidcr);
						*/
								}
							}

							//add redirect here

						} //end if oracle report


//added 05/07/2010,mak, to check and eliminate files with 0 bytes from the merge files list
//if(!$cc_addendum or 1==1)
						if (1 == 1) {
//options_addition_02142012, added the $options_doc filter
							echo '<br/>***Optionsdoc= ' . $options_doc . '<br/>';
							if ($options_doc == 0) {

								if (trim($merged_files) != '') {
									//*************** added by Jeff McKenzie *************************
									// 3/6/13 Don't insert into merged document if the jsp is slated to go to a separate workflow
									//
									//****************************************************************
									$merge_this_doc = true;
									$qu_dj_esign_custom = "select source, workflow from dj_esign_custom where division_jde = '$division_jde' and source = '$source_doc' ";
									$stid_djec = oci_parse($db_conn, $qu_dj_esign_custom);
									oci_execute($stid_djec);
// ******************************* this only works for 1 file / workflow... need to change to allow for multiple

									while ($rowdjec = oci_fetch_array($stid_djec, OCI_RETURN_NULLS + OCI_ASSOC)) {
										$merge_this_doc = false;
										echo '<br>Merge this doc = FALSE!';
										$_SESSION['doc_num1'] = $doc_num1;
										$_SESSION['doc_path1'] = $report_location . '/';
										$_SESSION['source_doc'] = $source_doc;
										$_SESSION['workflow'] = $rowdjec['WORKFLOW'];
										$_SESSION['multi_file'] = true;
									}
									if ($_SESSION['userid'] == 'jeff.mckenzie@lennar.com' and 1 == 2) {
										echo '<br>' . $qu_dj_esign_custom;
										exit;
									}

									if ($post['other_pdf' . $doc_loop] == 'y' and
										file_exists('contract_blank_docs/' . $source_blank_doc) and
										filesize('contract_blank_docs/' . $source_blank_doc) > 0 and trim($source_blank_doc) != '') {
										$merged_files = $merged_files . ', ' . $source_blank_doc;
										copy('contract_blank_docs/' . $source_blank_doc, $report_location1 . '/' . $source_blank_doc);
									} else if (filesize($report_location1 . '/' . $doc_num1) > 0 and trim($doc_num1) != '') {
										$merged_files = $merged_files . ', ' . $doc_num1;
									}//end of elseif
								} //end of outer if


								else {
									if ($post['other_pdf' . $doc_loop] == 'y' and
										file_exists('contract_blank_docs/' . $source_blank_doc) and
										filesize('contract_blank_docs/' . $source_blank_doc) > 0) {
										$merged_files = $source_blank_doc;
										copy('contract_blank_docs/' . $source_blank_doc, $report_location1 . '/' . $source_blank_doc);
									} else if (filesize($report_location1 . '/' . $doc_num1) > 0) {
										$merged_files = $doc_num1;
									}//end of elseif

								}//end of else

							}//end of if for options_addition_02142012


//list of invalid files to email
							if ($post['other_pdf' . $doc_loop] != 'y' and
								file_exists($report_location1 . '/' . $doc_num1) and
								filesize($report_location1 . '/' . $doc_num1) == 0) {
								$invalid_count++;

								$invalid_files_alert .= $invalid_count . '. ' . $description . ', Doc# ' . $doc_num . "\n";

								if ($invalid_files != '') {
									$invalid_files = $invalid_files . ',' . $doc_num1;
								} else {
									$invalid_files = $doc_num1;
									$index_part_doc_err = $index_part_doc;
									//oracle report link for the first invalid doc
									$link_err = $report_link . 'rwservlet?wrldclas&report=' . $source_doc . '&P_IDX=' . $p_indx . '&P_DOCID=' . $doc_num . '&P_RDATE=' . $revision_date . '&P_VNUM=' . $version_num;
								}
							} else if ($post['other_pdf' . $doc_loop] == 'y' and
								file_exists('contract_blank_docs/' . $source_blank_doc) and
								filesize('contract_blank_docs/' . $source_blank_doc) === 0 and 1 == 2) {
								$invalid_count++;

								$invalid_files_alert .= $invalid_count . '. ' . $description . ', Doc# ' . $source_blank_doc . "\n";

								if ($invalid_files != '') {
									$invalid_files = $invalid_files . ',' . $source_blank_doc;
								} else
									$invalid_files = $source_blank_doc;
							}


						} //end of if $cc_addendum for merging filess


					}
					//}
//now do the draft docs
					if (isset($post['check_box_doc_print_draft' . $doc_loop])) {
						// use this loop to check if we want to email the document to a customer
						echo '';
					}


				} //end of main document loop

			} //end of if condition that checks if blank documents button is clicked,added 02/04/2011


			if ($doc_print_count >= 1) {


//invalid file alert

				function alert($msg)
				{
					$msg = addslashes($msg);
					$msg = str_replace("\n", "\\n", $msg);
					echo "<script language='javascript'><!--\n";
					echo 'alert("' . $msg . '")';
					echo "//--></script>\n\n";
				}

// if print blank documents button was clicked then avoid temailing invalid documents

				if ($print_blank != 1) {


					if ($invalid_count > 0) {


//field values for the first invalid document;added 07/12/2010

						$qry_field_name_err = '';
						$field_name_err = array();
						$k = 0;


						$qu_err = "select d1.field_name,d1.label,d2.big_fatty from dj_documents_dtl_field d1 left join dj_field d2 on
trim(d1.field_name)=trim(d2.field_name) where d1.index_part_doc = '$index_part_doc_err' order by d1.field_name";

						$stid_err = oci_parse($db_conn, $qu_err);
						oci_execute($stid_err);
						while ($row_err = oci_fetch_array($stid_err, OCI_RETURN_NULLS + OCI_ASSOC)) {


							$field_name_err['field_name'][$k] = trim($row_err['FIELD_NAME']);
							$field_name_err['label'][$k] = trim($row_err['LABEL']);

							$field_name_err['big_fatty'][$k] = trim($row_err['BIG_FATTY']);

							$k++;


						}


						$qry_field_name_err = '';
						$qry_field_name_err2 = '';


						$count_arr = $k;


						for ($i = 0; $i < $count_arr; $i++) {


							IF ($field_name_err['big_fatty'][$i] == 'DJ_BIG_FATTY') {

								IF ($qry_field_name_err != '') {

									$qry_field_name_err = $qry_field_name_err . ', ' . $field_name_err['field_name'][$i];
								} ELSE {
									$qry_field_name_err = $field_name_err['field_name'][$i];

								}

							} //end of outer IF


							IF ($field_name_err['big_fatty'][$i] == 'DJ_BIG_FATTY2') {

								IF ($qry_field_name_err2 != '') {

									$qry_field_name_err2 = $qry_field_name_err2 . ', ' . $field_name_err['field_name'][$i];
								} ELSE {
									$qry_field_name_err2 = $field_name_err['field_name'][$i];

								}

							} //end of outer IF


						}//end of for loop


//build the query to retrieve the values from dj_big_fatty for the first invalid document
//added by mak-07/12/2010


						$result_err = '';
						$result_err2 = '';

						$qry_field_err = "select index_part,$qry_field_name_err from dj_big_fatty where division_jde='$division_jde' and community_jde='$community_jde' and homesite='$homesite'
and customer_id='$customer_id' ";

						$stid_err1 = oci_parse($db_conn, $qry_field_err);
						oci_execute($stid_err1);


						while ($row_err1 = oci_fetch_array($stid_err1, OCI_RETURN_NULLS + OCI_ASSOC)) {

							$index_part_dj = trim($row_err1[INDEX_PART]);

							for ($j = 0; $j < $count_arr; $j++) {

								IF ($field_name_err['big_fatty'][$j] == 'DJ_BIG_FATTY') {

									$field_qry = $field_name_err['field_name'][$j];
									$label_qry = $field_name_err['label'][$j];
									$result_err = $result_err . "\n" . $label_qry . ': ' . trim($row_err1[$field_qry]);

								}

							}

						}

						$qry_field_err2 = "select $qry_field_name_err2 from dj_big_fatty2 where index_part='$index_part_dj' ";

						$stid_err2 = oci_parse($db_conn, $qry_field_err2);
						oci_execute($stid_err2);


						while ($row_err2 = oci_fetch_array($stid_err2, OCI_RETURN_NULLS + OCI_ASSOC)) {

							for ($j = 0; $j < $count_arr; $j++) {

								IF ($field_name_err['big_fatty'][$j] == 'DJ_BIG_FATTY2') {

									$field_qry = $field_name_err['field_name'][$j];
									$label_qry = $field_name_err['label'][$j];
									$result_err2 = $result_err2 . "\n" . $label_qry . ': ' . trim($row_err2[$field_qry]);

								}

							}

						}


						$almsg .= "Warning: Not all selected documents were printed. Below files were invalid \n\n";
						$almsg .= "$invalid_files_alert";

						alert($almsg);


						//email invalid documents list

						$to = "madhav.kolipaka@lennar.com, jeff.mckenzie@lennar.com,jens.penske@lennar.com,angelica.henry@lennar.com";
						//$to = "madhav.kolipaka@lennar.com";
						$from = "madhav.kolipaka@lennar.com";
						$subject = "NHC Invalid documents printed for: " . $community_jde;
						$message .= "Below is the list of invalid documents selected for merging in NHC \n\n";
						$message .= " $invalid_files \n\n";
						$message .= "Below is Link to contract \n\n";


						$message .= $contract_link . "contract_select_community_nhc.php?from_java=yes&java_customer_id=$customer_id&java_homesite_id=$homesite&java_community_jde=$community_jde";

						$message .= "\n\n Below are the field values for the first invalid document with index_part_doc equal to  $index_part_doc_err and index part from dj_big_fatty equal to $index_part_dj \n";
						$message .= "$result_err \n\n";
						$message .= "\n\n";
						$message .= "Below is the link to the Oracle Report that failed\n\n";
						$message .= "$link_err";
						$headers = "From: $from";

//$ok = @mail($to, $subject, $message, $headers);

					} //end of invalid if

				}//end of if condition that checks if blank documents button is clicked,added 02/04/2011


//Below is the loop when print selected blank documents button is clicked

//below to create the list of files to be merged only when the print blank documents button is clicked

				if ($print_blank == 1) {

					$merged_files = '';
					$print_blank_unlink = array();

					for ($doc_loop = 0; $doc_loop <= $doc_count; $doc_loop++) {

						if (isset($post['check_box_doc_print_bl' . $doc_loop])) {


							$index_part_doc = $post['index_part_doc' . $doc_loop];


							$qu_doc = "select * from dj_documents where index_part = '$index_part_doc'";
							$stid3 = oci_parse($db_conn, $qu_doc);
							oci_execute($stid3);
							while ($row5 = oci_fetch_array($stid3, OCI_RETURN_NULLS + OCI_ASSOC)) {

								$source_blank_doc = trim($row5['SOURCE_BLANK_DOC']);
							}


//added 05/07/2010,mak, to check and eliminate files with 0 bytes from the merge files list

							if (trim($merged_files) != '') {
								if (filesize('contract_blank_docs/' . $source_blank_doc) > 0 and trim($source_blank_doc) != '') {
									$merged_files = $merged_files . ', ' . $source_blank_doc;
									copy('contract_blank_docs/' . $source_blank_doc, 'userfiles/' . $source_blank_doc);

								}

							}//end of outer if


							else {
								if (filesize('contract_blank_docs/' . $source_blank_doc) > 0) {
									$merged_files = $source_blank_doc;
									copy('contract_blank_docs/' . $source_blank_doc, 'userfiles/' . $source_blank_doc);

								}

							}//end of else


							$print_blank_unlink[] = "userfiles/" . $source_blank_doc;


						}//end of if that checks if the checkbox is checked


					}//end of the for loop that loops over the selected blank documents to be merged


					$java_path = $document_root . "userfiles";


				}//end of if condition that checks if blank documents button is clicked,added 02/04/2011


//insert merged files list to database to be sent to the pdf tool

				IF (!IS_NULL($merged_files) and trim($merged_files) != '') {
					if ($java_path == '') {
						//only the Options Addendum from options have been selected... need to recalculate the file PATH

						$made_dir = 999;

						$fname = $_SESSION['BUYER1_FIRSTNAME'];
						$lname = $_SESSION['BUYER1_LASTNAME'];

						$bad_chars = array("+", "'", " ", "-", ".", '"', ":", ";", ",", "%", "#", "&", "*", "(", ")", "$", "?", "@", "~", "|", "!", "^", "{", "}", "[", "]");
						$no_quotes = array("'", '"');

						$nhc = $_SESSION['NEWHOMECONSULTANT_NAME'];

						if (strpos($fname, "'") != false and strpos($fname, "''") === false)
							$fname = str_replace("'", "''", $fname);
						if (strpos($lname, "'") != false and strpos($lname, "''") === false) {
							$lname = str_replace("'", "''", $lname);
						}
						/* else
				{
				echo '<br />didnt go inside<br />';
				}
*/
						if (strpos($doc_num, "'") != false and strpos($doc_num, "''") === false)
							$doc_num = str_replace("'", "''", $doc_num);
						if (strpos($description, "'") != false and strpos($description, "''") === false)
							$description = str_replace("'", "''", $description);
						if (strpos($version_num, "'") != false and strpos($version_num, "''") === false)
							$version_num = str_replace("'", "''", $version_num);
						if (strpos($nhc, "'") != false and strpos($nhc, "''") === false)
							$nhc = str_replace("'", "''", $nhc);


						$report_location = "contracts/" . $division_jde;
						if (!file_exists($report_location))
							$made_dir = @mkdir($report_location, 0775);
						@chmod($report_location, 0775);

						$report_location = "contracts/" . $division_jde . "/" . $community_jde;
						if (!file_exists($report_location))
							$made_dir = @mkdir($report_location, 0775);
						@chmod($report_location, 0775);

						$report_location = "contracts/" . $division_jde . "/" . $community_jde . "/" . $homesite;
						if (!file_exists($report_location))
							$made_dir = @mkdir($report_location, 0775);
						@chmod($report_location, 0775);

						$report_location = "contracts/" . $division_jde . "/" . $community_jde . "/" . $homesite . "/" . preg_replace("([^a-zA-Z 0-9/]+)", "_", str_replace(' ', '', str_replace('/', '', str_replace("''", "_", $lname)))) . "_" . preg_replace("([^a-zA-Z 0-9/]+)", "_", trim(str_replace(' ', '', str_replace('/', '', str_replace("''", "_", $fname)))));
						//$report_location = preg_replace("([^a-zA-Z 0-9/]+)","_",str_replace($bad_chars, '_', $report_location));
						if (!file_exists($report_location))
							$made_dir = @mkdir($report_location, 0775);
						@chmod($report_location, 0775);

						$report_location1 = $report_location;

						$java_path = $document_root . "contracts/" . $report_location1;

					}

					//now add the extra jsp document...
					if ($extra_jsp != '') {
						$filepath = $java_path . '/extra_jsp.pdf';
						if (!isset($doc_num))
							$doc_num = '';
						if (!isset($version_num))
							$version_num = '';
						if (!isset($revision_date))
							$revision_date = '';
						/*
		if($e_serv=='Staging' or $e_serv=='StagingNew')
			{
			$link = $report_link.'rwservlet?destype=file&desformat=PDF&desname='.$filepath.'&userid=xxphp/con1rac1s@vmohslenn001.oracleoutsourcing.com:13510/TLEN3O.ORACLEOUTSOURCING.COM&authid=testuser/Welcome1&report='.$extra_jsp.'&P_IDX='.$p_indx.'&P_DOCID='.$doc_num.'&P_RDATE='.$revision_date.'&P_VNUM='.$version_num;
			}
		else if($e_serv=='Production' or $e_serv=='ProductionNew')
			{
			$link = $report_link.'rwservlet?wrldclas2&report='.$extra_jsp.'&desname='.$filepath.'&P_IDX='.$p_indx.'&P_DOCID='.$doc_num.'&P_RDATE='.$revision_date.'&P_VNUM='.$version_num;
			}
*/
						$link = $report_link . 'rwservlet?wrldclas2&report=' . $extra_jsp . '&desname=' . $filepath . '&P_IDX=' . $p_indx . '&P_DOCID=' . $doc_num . '&P_RDATE=' . $revision_date . '&P_VNUM=' . $version_num;

						$ch = curl_init();

						$fp2 = fopen($filepath . '_log.html', 'a');
						chmod($filepath . '_log.html', 0775);

						// set URL and other appropriate options
						//if($userid == 'jeff.mckenzie@lennar.com')
						curl_setopt($ch, CURLOPT_FILE, $fp2);

						curl_setopt($ch, CURLOPT_URL, $link);
						curl_setopt($ch, CURLOPT_HEADER, 0);
						curl_setopt($ch, CURLOPT_TIMEOUT, 240);
						//curl_setopt($ch, CURLOPT_PROXY, 'http://www-proxy-adc.us.oracle.com');
						curl_setopt($ch, CURLOPT_PROXY, $oracle_proxy);
						curl_setopt($ch, CURLOPT_PROXYPORT, 80);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

						// grab URL and pass it to the browser
						curl_exec($ch);

						// close cURL resource, and free up system resources
						curl_close($ch);
						fclose($fp2);

						$log_results = file_get_contents($filepath . '_log.html');
						$pos = strpos($log_results, "success");
						if ($pos === false and strlen($log_results) > 10) {
							$report_error1 = strpos($log_results, '<pre>') + 5;
							$report_error2 = strpos($log_results, '</pre>', $report_error1) - 1;
							$len = $report_error2 - $report_error1;
							$report_error = substr($log_results, $report_error1, $len);
							$counterrs++;
							if ($counterrs < 2) {
								$tototo = 'jeff.mckenzie@lennar.com, roseline.seweje@lennar.com, jens.penske@lennar.com, 
					           tekeshia.mcminns@lennar.com, allen.fazel@lennar.com, victoria.schmid@lennar.com, 
					           joyce.garcia@lennar.com';
								if ($e_serv == 'StagingNew' or $e_serv == 'Development')
									$tototo = 'jeff.mckenzie@lennar.com, jens.penske@lennar.com, joyce.garcia@lennar.com, victoria.schmid@lennar.com';
								mail($tototo, '*** Oracle Reports Error Extra JSP from Contracts - ' . $e_serv, '
Division:     ' . $division_jde . '
Community:    ' . $community_jde . '
Homesite:     ' . $homesite . '
NHC:          ' . $userid . '
Filepath:     ' . $filepath . '
Report URL:   ' . $link . '

Return from Oracle Reports: ' . html_entity_decode($report_error) . '

______________________________
XML: ' . $log_results, 'From: Jeff McKenzie <jeff.mckenzie@lennar.com>');
								if ($userid == '') {
									$userid = $_SESSION['NEWHOMECONSULTANT_NAME'];
									$userid = str_replace(" ", ".", $userid) . "@lennar.com";
								}
								if (1 == 2 and $userid != 'jeff.mckenzie@lennar.com' and $userid != '') {

									//	mail('jeff.mckenzie@lennar.com','*** Oracle Reports Error from Contracts: '.$userid,'NHC:'.$_SESSION['nhc'], 'From: Jeff McKenzie <jeff.mckenzie@lennar.com>');
									mail($userid, '*** Oracle Reports Error from Contracts', '
Division:   ' . $division_jde . '
Community:  ' . $community_jde . '
Homesite:   ' . $homesite . '
NHC:        ' . $userid . '
Report URL: ' . $link . '

Return from Oracle Reports: ' . html_entity_decode($report_error), 'From: Jeff McKenzie <jeff.mckenzie@lennar.com>');
								}
							}
							unlink($filepath . '_log.html');


						} ELSE {
							$_SESSION['docs']['extra_jsp.pdf'] = $extra_jsp;
							$merged_files = $merged_files . ', extra_jsp.pdf';
						}
					}


					$qry_merged = "insert into dj_cust_merged_docs (filepath, filename, merged_file_name, date_created, division_jde, community_jde, homesite)
				values ('$java_path','$merged_files',NULL,sysdate, '$division_jde', '$community_jde', '$homesite')";
					$stid_merged = oci_parse($db_conn, $qry_merged);
					oci_execute($stid_merged);
				}


				$qry = "select index_part from dj_cust_merged_docs where community_jde = '$community_jde' and
    													   division_jde = '$division_jde' and
    													   homesite = '$homesite' and 
    													   filename = '$merged_files'
    												order by index_part desc";
				$stid = oci_parse($db_conn, $qry);
				oci_execute($stid);

				$merge_index_part = 0;
				$first = true;
				while ($row3 = oci_fetch_array($stid, OCI_RETURN_NULLS + OCI_ASSOC)) {
					if ($first) {
						$first = false;
						$merge_index_part = trim($row3['INDEX_PART']);
					}
				}
				if ($merge_index_part == 0) {
					$qry = "select index_part from dj_cust_merged_docs where community_jde = '$community_jde' order by index_part desc";
					$stid = oci_parse($db_conn, $qry);
					oci_execute($stid);

					$merge_index_part = 0;
					$first = true;
					while ($row3 = oci_fetch_array($stid, OCI_RETURN_NULLS + OCI_ASSOC)) {
						if ($first) {
							$first = false;
							$merge_index_part = trim($row3['INDEX_PART']);
						}
					}
				}

				$link_java = $java_link . 'pdftool?id=' . $merge_index_part;


			} //end of check for doc_loop

// **************** now set up the multipath

			$_SESSION['called_once'] = 1;

//*******************
			$doc_list = 'contract_select_community_nhc.php?call_pdftool=NO';

			if (!IS_NULL($merged_files) and trim($merged_files) != '' and isset($merge_index_part)) {
				$doc_list = 'contract_select_community_nhc.php?merge_index_part=' . $merge_index_part . '&call_pdftool=YES&waitfile=' . $waitfile;
			} else if (isset($merge_index_part)) {
				$doc_list = 'contract_select_community_nhc.php?merge_index_part=' . $merge_index_part . '&call_pdftool=NO';
			}


			print ("<script language=\"JavaScript\">\n");
			//print ("redirect_pdftool = \"".$link_java."\";\n");
			print ("redirect_doclist = \"" . $doc_list . "\";\n");
			print ("</script>");

			?>
            <script language="JavaScript">
                window.location = redirect_doclist;
            </script>
			<?php


			/*

//unlink archived blank documents



$k=0;
while($k<count($print_blank_unlink))
{

$filename=$print_blank_unlink[$k];
unlink($filename);

$k++;
}

*/

		} //end of checking for dbconn


	} else {
		echo '<br />Please select a customer to thee create contracts for...';
	}
	if (isset($stid))
		oci_free_statement($stid);
	if (isset($stid1))
		oci_free_statement($stid1);
	if (isset($stid2))
		oci_free_statement($stid2);
	if (isset($stid3))
		oci_free_statement($stid3);
	if (isset($stid4))
		oci_free_statement($stid4);
	if (isset($stid5))
		oci_free_statement($stid5);
	if (isset($stid6))
		oci_free_statement($stid6);
	if (isset($stid7))
		oci_free_statement($stid7);
	if (isset($stid8))
		oci_free_statement($stid8);
	if (isset($stid9))
		oci_free_statement($stid9);
	if (isset($stid10))
		oci_free_statement($stid10);
	if (isset($stidop))
		oci_free_statement($stidop);
	if (isset($stid_check))
		oci_free_statement($stid_check);
	if (isset($stid188))
		oci_free_statement($stid188);
	if (isset($stidf))
		oci_free_statement($stidf);
	if (isset($stid_djec))
		oci_free_statement($stid_djec);
	if (isset($stid_err))
		oci_free_statement($stid_err);
	if (isset($stid_err1))
		oci_free_statement($stid_err1);
	if (isset($stid_err2))
		oci_free_statement($stid_err2);
	if (isset($stid_merged))
		oci_free_statement($stid_merged);

	if ($db_conn)
		oci_close($db_conn);
}


function get_esign_data($get, $post)
{
	global $_SESSION, $e_serv;
	$mytime = time();
	$db_conn = db_connect($e_serv);
	if (!$db_conn) {
		echo '<br><h2>Unable to connect to oracle database!';
		exit;
	} else {

		//need to delete any unset records due to 10.0.0.33 issue

		//http://10.0.0.33/docusign_test/esign.php

		//https://otolenntest.oracleoutsourcing.com/contracts/contract_select_community_nhc.php?from_java=yes&java_customer_id=AFFA-76RQ8Y&java_homesite_id=K054&java_community_jde=16351

		//$del_djesign = "delete from dj_esign where date_processed is null";
		//$stid2 = oci_parse($db_conn, $del_djesign);
		//oci_execute($stid2);

		$merge_index_part = $get['merge_index_part'];
		$community_jde = $_SESSION['community_jde'];
		$customer_id = $_SESSION['customer_id'];
		$homesite = $_SESSION['homesite'];
		echo '
		<form action="docusign/get_esign_data_post.php" method="POST">
		<input type="hidden" name="merge_index_part" value="' . $merge_index_part . '">
		  <br/>
		  <strong>Recipient Information</strong>
		  <br/>
		  <hr/>
		  <table cellpadding="5">
			<tr>
			  <td style="text-align:center"><strong>Role</strong></td>
			  <td style="text-align:center"><strong>Full Name</strong></td>
			  <td style="text-align:center"><strong>Email</strong></td>
			  <td style="text-align:center"><strong>Location</strong></td>
			</tr>';
		$buyer1 = $_SESSION['BUYER1_FIRSTNAME'];
		if ($_SESSION['BUYER1_MIDDLENAME'] != '')
			$buyer1 .= ' ' . $_SESSION['BUYER1_MIDDLENAME'];
		$buyer1 .= ' ' . $_SESSION['BUYER1_LASTNAME'];

		echo '
			<tr>
			  <td>Buyer 1 (req\'d)</td>
			  <td><INPUT type="text" id="Buyer1_UserName" name="Buyer1_UserName" size="40"
			  			 value="' . $buyer1 . '"/></td>
			  <td><INPUT type="text" id="Buyer1_Email" name="Buyer1_Email" size="40"
			  			 value="' . $_SESSION['BUYEREMAIL'] . '" /></td>
			  <td>
				<select id="Buyer1_Location" name="Buyer1_Location">
				  <option value="Embedded">In Office</option>
				  <option value="Remote">Remote</option>
				</select>
			  </td>
			</tr>';
		if ($_SESSION['BUYER2_LASTNAME'] != '' and $_SESSION['BUYER2_FIRSTNAME_ONCONT']) {
			$buyer2 = $_SESSION['BUYER2_FIRSTNAME'];
			if ($_SESSION['BUYER2_MIDDLENAME'] != '')
				$buyer2 .= ' ' . $_SESSION['BUYER2_MIDDLENAME'] . ' ';
			$buyer2 .= $_SESSION['BUYER2_LASTNAME'];
			echo '
				<tr>
				  <td>Buyer 2</td>
				  <td><INPUT type="text" id="Buyer2_UserName" name="Buyer2_UserName" size="40"
			  			 value="' . $buyer2 . '"/></td>
				  <td><INPUT type="text" id="Buyer2_Email" name="Buyer2_Email" size="40"
			  			 value="' . $_SESSION['BUYER2EMAIL'] . '" /></td>
				  <td>
					<select id="Buyer2_Location" name="Buyer2_Location">
					  <option value="Remote">Remote</option>
					  <!--<option value="Embedded">In Office</option>-->
					</select>
				  </td>
				</tr>';
		}
		if ($_SESSION['BUYER3_LASTNAME'] != '' and $_SESSION['BUYER3_FIRSTNAME_ONCONT']) {
			$buyer3 = $_SESSION['BUYER3_FIRSTNAME'];
			if ($_SESSION['BUYER3_MIDDLENAME'] != '')
				$buyer3 .= ' ' . $_SESSION['BUYER3_MIDDLENAME'] . ' ';
			$buyer3 .= $_SESSION['BUYER3_LASTNAME'];
			echo '
				<tr>
				  <td>Buyer 3</td>
				  <td><INPUT type="text" id="Buyer3_UserName" name="Buyer3_UserName" size="40"
			  			 value="' . $buyer3 . '"/></td>
				  <td><INPUT type="text" id="Buyer3_Email" name="Buyer3_Email" size="40"
			  			 value="' . $_SESSION['BUYER3EMAIL'] . '" /></td>
				  <td>
					<select id="Buyer3_Location" name="Buyer3_Location">
					  <option value="Remote">Remote</option>
					  <!--<option value="Embedded">In Office</option>-->
					</select>
				  </td>
				</tr>';
		}
		if ($_SESSION['BUYER4_LASTNAME'] != '' and $_SESSION['BUYER4_FIRSTNAME_ONCONT']) {
			$buyer4 = $_SESSION['BUYER4_FIRSTNAME'];
			if ($_SESSION['BUYER4_MIDDLENAME'] != '')
				$buyer4 .= ' ' . $_SESSION['BUYER4_MIDDLENAME'] . ' ';
			$buyer4 .= $_SESSION['BUYER4_LASTNAME'];
			echo '
				<tr>
				  <td>Buyer 4</td>
				  <td><INPUT type="text" id="Buyer4_UserName" name="Buyer4_UserName" size="40"
			  			 value="' . $buyer4 . '"/></td>
				  <td><INPUT type="text" id="Buyer4_Email" name="Buyer4_Email" size="40"
			  			 value="' . $_SESSION['BUYER4EMAIL'] . '" /></td>
				  <td>
					<select id="Buyer4_Location" name="Buyer4_Location">
					  <option value="Remote">Remote</option>
					  <!--<option value="Embedded">In Office</option>-->
					</select>
				  </td>
				</tr>';
		}
		echo '
			<tr>
			  <td>NHC</td>
			  <td><INPUT type="text" id="NHC_UserName" name="NHC_UserName" size="40"
			  			 value="' . $_SESSION['NEWHOMECONSULTANT_NAME'] . '" /></td>
			  <td><INPUT type="text" id="NHC_Email" name="NHC_Email" size="40"
			  			 value="' . $_SESSION['NHCEMAIL'] . '" /></td>
			  <td>
				<select id="NHC_Location" name="NHC_Location">
				  <option value="Remote">Remote</option>
				  <!--<option value="Embedded">In Office</option>-->
				</select>
			  </td>
			</tr>';
		echo '
			<tr>
			  <td>Authorized Agent (req\'d)</td>
			  <td><INPUT type="text" id="AuthorizedAgent_UserName" name="AuthorizedAgent_UserName" size="40"
			  			 value="' . $_SESSION['AUTHORIZEDAGENT_NAME'] . '" /></td>
			  <td><INPUT type="text" id="AuthorizedAgent_Email" name="AuthorizedAgent_Email" size="40"/></td>
			  <td>
				<select id="Buyer1_Location" name="AuthorizedAgent_Location">
				  <option value="Remote">Remote</option>
				  <!--<option value="Embedded">In Office</option>-->
				</select>
			  </td>
			</tr>';
		echo '
		  </table>
		  <br/><br/>
		  <INPUT type="submit" value="Send for Electronic Signature Now" />';

		echo ' <br/><br/>   ';
		echo '<button type="button"
		  onClick="javascript:location.href=\'contract_select_community_nhc.php?from_java=yes&java_customer_id=' . $customer_id . '&java_homesite_id=' . $homesite . '&java_community_jde=' . $community_jde . '\';" >Return to Contract Documents</button></td></tr>';

	}
}

function get_esign_data_post($get, $post)
{
	global $_SESSION, $e_serv;
	$mytime = time();
	$db_conn = db_connect($e_serv);
	if (!$db_conn) {
		echo '<br><h2>Unable to connect to oracle database!';
		exit;
	} else {
		$merge_index_part = $post['merge_index_part'];
		$division_jde = $_SESSION['division_jde'];
		$community_jde = $_SESSION['community_jde'];
		$homesite = $_SESSION['homesite'];
		if (!isset($_SESSION['homesite_selected']))
			$_SESSION['homesite_selected'] = $_SESSION['community_jde'];
		$customer_id = $_SESSION['customer_id'];
		$opportunity_id = $_SESSION['opportunity_id'];
//echo '<br/>Merge index part:'.$merge_index_part;
		//log into webservices

		/*
		$url = $crmdomain . "/Services/Integration?command=login";
		$page = "/Services/Integration?command=login";
		$headers = array( "GET ".$page." HTTP/1.0", "UserName: LENNAR/PROD.SYSADMIN", "Password: $pwd_crm", );
		//$headers = array( "GET ".$page." HTTP/1.0", "UserName: Lennar/LEAD.INTEGRATION", "Password: $pwd_crm", );
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_HEADER, true);
		$data = curl_exec($ch);
		$sessionid = substr($data,(strpos($data,"Set-Cookie:")+23),(strpos($data,";")-strpos($data,"Set-Cookie:")-23));
		curl_close($ch);

		$due_date = date('m/d/Y');
		$complete_date = date('m/d/Y h:s a');
		//create a completed activity...
		$serverpath = $crmdomain . "/Services/Integration;jsessionid=$sessionid";
		$namespace= "urn:crmondemand/ws/activity/10/2004";
		$soapaction = "document/urn:crmondemand/ws/activity/10/2004:Activity_Insert";
		$param ="<ListOfActivity>
		<Activity>
        <Activity>Task</Activity>
        <Subject>Electronic Signature Document</Subject>
        <Type>Purchase Agreement</Type>
		<Status>Completed</Status>
		<IndexedPick3>Auto Response</IndexedPick3>
        <DueDate>$due_date</DueDate>
        <Description>Electronic Signature Document with Docusign</Description>
        <OpportunityId>$opportunity_id</OpportunityId>
		</Activity>
		</ListOfActivity>";
		$method = "ActivityNWS_Activity_Insert_Input";
		$client = new nusoap_client($serverpath);
		$debug='';
		$client->setHeaders("$debug");
		$response = $client->call($method,$param,$namespace,$soapaction);
		$soapdata=htmlspecialchars($client->responseData, ENT_QUOTES);
		$check= substr($soapdata,strpos($soapdata,"ns:LastPage")+15,5);
*/

		/*
		$ch1 = curl_init();
		curl_setopt($ch1, CURLOPT_URL,$logoff);
		curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, FALSE);
		$data1 = curl_exec($ch1);
		$st=curl_getinfo($ch1,CURLINFO_HTTP_CODE);
		curl_close($ch1);
*/

		$qu_docs = "select * from dj_cust_merged_docs  where index_part = '$merge_index_part' ";
		$stid2 = oci_parse($db_conn, $qu_docs);
		oci_execute($stid2);
		while ($row4 = oci_fetch_array($stid2, OCI_RETURN_NULLS + OCI_ASSOC)) {
			$merged_file_name = trim($row4['MERGED_FILE_NAME']);
			$file_path = trim($row4['FILEPATH']);
		}
		//copy the file to the ftp server
		$filecopyfrom = $file_path . '/' . $merged_file_name;
		$filecopytodir2 = 'docusign/media/';

		$filecopytodir = 'media/';
		$fn = str_replace(" ", "", str_replace(",", "", $_SESSION['BUYER1_FIRSTNAME']));
		$ln = str_replace(" ", "", str_replace(",", "", $_SESSION['BUYER1_LASTNAME']));
		$filecopyto2 = $filecopytodir2 . $division_jde . '_' . $community_jde . '_' . $homesite . '_' . $ln . '_' . $fn . '_' . time() . '.pdf';
		$filecopyto = $filecopytodir . $division_jde . '_' . $community_jde . '_' . $homesite . '_' . $ln . '_' . $fn . '_' . time() . '.pdf';
		//$filecopyto2 = $filecopytodir2.$division_jde.'_'.$community_jde.'_'.$homesite.'_'.$ln.'_'.$fn.'.pdf';
		//$filecopyto = $filecopytodir.$division_jde.'_'.$community_jde.'_'.$homesite.'_'.$ln.'_'.$fn.'.pdf';
		//$filecopyto = $filecopytodir.'TLC360.pdf';

		if (file_exists($filecopyfrom) and file_exists($filecopytodir2))
			$copyfile = copy($filecopyfrom, $filecopyto2);

		$Buyer1_UserName = $_POST['Buyer1_UserName'];
		$Buyer1_Email = $_POST['Buyer1_Email'];
		$Buyer1_Location = $_POST['Buyer1_Location'];
		$Buyer2_UserName = $_POST['Buyer2_UserName'];
		$Buyer2_Email = $_POST['Buyer2_Email'];
		$Buyer2_Location = $_POST['Buyer2_Location'];
		$Buyer3_UserName = $_POST['Buyer3_UserName'];
		$Buyer3_Email = $_POST['Buyer3_Email'];
		$Buyer3_Location = $_POST['Buyer3_Location'];
		$Buyer4_UserName = $_POST['Buyer4_UserName'];
		$Buyer4_Email = $_POST['Buyer4_Email'];
		$Buyer4_Location = $_POST['Buyer4_Location'];
		$NHC_UserName = $_POST['NHC_UserName'];
		$NHC_Email = $_POST['NHC_Email'];
		$NHC_Location = $_POST['NHC_Location'];
		$AuthorizedAgent_UserName = $_POST['AuthorizedAgent_UserName'];
		$AuthorizedAgent_Email = $_POST['AuthorizedAgent_Email'];
		$AuthorizedAgent_Location = $_POST['AuthorizedAgent_Location'];
		$date_added = date('d-M-y');
		$time_added = date('h:i:s a');
		$Buyer3_UserName = '';
		$Buyer3_Email = '';
		$Buyer3_Location = '';
		$Buyer4_UserName = '';
		$Buyer4_Email = '';
		$Buyer4_Location = '';

		$link = $linkhead . 'contract_select_community_nhc.php?from_java=yes&java_customer_id=' . $_SESSION['customer_id'] . '&java_homesite_id=' . $_SESSION['homesite_selected'] . '&java_community_jde=' . $_SESSION['community_jde'];

		$qu_ins = "insert into dj_esign (division_jde, community, homesite, customer_id, opportunity_id, date_added, time_added,
										 document_path, buyer1_email, buyer2_email, buyer3_email, buyer4_email, location,
										 buyer1_name, buyer2_name, nhc_email, nhc_name, authorized_agent_email, authorized_agent_name,
										 return_url)
				   values ('$division_jde','$community_jde','$homesite','$customer_id','$opportunity_id','$date_added','$time_added',
				   		   '$filecopyto','$Buyer1_Email','$Buyer2_Email','$Buyer3_Email','$Buyer4_Email','$Buyer1_Location',
				   		   '$Buyer1_UserName','$Buyer2_UserName','$NHC_Email','$NHC_UserName','$AuthorizedAgent_Email','$AuthorizedAgent_UserName',
				   		   '$link')";

		$stid3 = oci_parse($db_conn, $qu_ins);
		oci_execute($stid3);

		//now we need the logic to sit and wait for the web services to finish...
		//but only wait for 2 minutes at the most...
		if ($Buyer1_Location != 'Remote') {
			if (!ini_get('safe_mode')) {
				set_time_limit(180); //wait for 3 minutes max
				//echo '<br />Not safe mode...';
			}
			echo '<br />Waiting for electronic signature documents to process...';


			$qu_esign = "select * from dj_esign  where division_jde = '$division_jde' and community = '$community_jde' and homesite = '$homesite' and
													  customer_id = '$customer_id' and date_added = '$date_added' and time_added = '$time_added' and
													  document_path = '$filecopyto' ";
			$stid4 = oci_parse($db_conn, $qu_esign);
			//echo '<br/>'.$qu_esign;
			if (oci_execute($stid4)) {
				while ($row4 = oci_fetch_array($stid4, OCI_RETURN_NULLS + OCI_ASSOC)) {
					$index_part = trim($row4['INDEX_PART']);

				}


				$url_to_sign = $docusign_folder_path . '/esign.php?index=' . $index_part;

				echo '<br/>' . $url_to_sign;
				//mail('jeff.mckenzie@lennar.com', $url_to_sign, $qu_esign, '');

				if ($url_to_sign != '') {
					print ("<script language=\"JavaScript\">\n");
					print ("redirecturl = \"" . $url_to_sign . "\";\n");
					print ("</script>");
					//add redirect here
					?>
                    <script language="JavaScript">
                        window.location = redirecturl;
                    </script>
					<?php
				}
			} else {
				echo '<br />PROBLEM could not get index part... . <a href="' . $link . '">click to continue </a>';
			}
		} //not remote
		else {
			echo '<br />The documents have been submitted for electronic signature... all are remote. <a href="' . $link . '">click to continue </a>';
		}
		echo '<br />after while...';

		$_SESSION['esign_docs'] = 'N';


	}
	if (isset($stid2))
		oci_free_statement($stid2);
	if (isset($stid3))
		oci_free_statement($stid3);
	if (isset($stid4))
		oci_free_statement($stid4);

	if ($db_conn)
		oci_close($db_conn);
}

//style sheet added 02/15/2011,madhav kolipaka. for class no_border_center used in buttons

echo '<style type="text/css">
.no_border_center
		{
			font-family: Arial,Helvetica,Geneva,Swiss,SunSans-Regular;
			color: black;
			font-size: 9pt;
			font-weight: normal;
			text-align: center;
		}
</style>';


?>
