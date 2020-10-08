<?php
include_once("db.php");

function isElementExisting($value, $type)
{
	global $bddPDO;
	$query = "SELECT COUNT(id) as total FROM usersBis WHERE $type = '$value' ";

	$result = $bddPDO->prepare($query);
	$result->execute();

	if( $result->rowCount() == 1)
		return true;
	else
		return false;
}

?>


