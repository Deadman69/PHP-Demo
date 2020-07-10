<?php

function isElementExisting($value, $type)
{
	$query = "SELECT COUNT(id) as total FROM usersBis WHERE $type = '$value' ";
    $result = mysqli_query($db, $query);
   	$data = mysqli_fetch_assoc($result);
	if( $data['total'] == 1)
		return true;
	else
		return false;
}

?>


