<?php
/*
 * Copyright (C) 2013 by
 * Fuad Jamour <fjamour@gmail.com>
 * All rights reserved.
 * MIT license.
*/
?>

<?php include('country_names_map.php'); ?>

<?php
	$countries_op = '<option selected="">----</option>';
	foreach($COUNTRY_TO_CODE as $country => $code) {
		$countries_op .= ('<option>' . $country . '</option>');
	}
?>

<?php
	function get_countries($select="")
	{
		global $COUNTRY_TO_CODE;
		$countries_op = "";
		if($select == "")
			$countries_op = '<option selected="">----</option>';
		else
			$countries_op = '<option>----</option>';
		
		
		foreach($COUNTRY_TO_CODE as $country => $code) {
			if($country == $select)
				$countries_op .= ('<option selected="">' . $country . '</option>');
			else
				$countries_op .= ('<option>' . $country . '</option>');
		}
		return $countries_op;
	}
?>
