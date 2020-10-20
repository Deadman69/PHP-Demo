<?php
include_once("db.php");

function isElementExisting($value, $type)
{
	global $bddPDO;
	$query = 'SELECT id as total FROM users WHERE '.$type.' = ? ';

	$result = $bddPDO->prepare($query);
	$result->execute(array($value));

	if( $result->rowCount() >= 1) {
		return true;
	}
	else {
		return false;
	}
}

?>


