<?php
function randomString($length)
{
	$listeCar = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
 	$chaine = "";
	$max = mb_strlen($listeCar, '8bit') - 1;
	for ($i = 0; $i < $length; ++$i) {
		$chaine .= $listeCar[random_int(0, $max)];
	}
	return $chaine;
}
?>