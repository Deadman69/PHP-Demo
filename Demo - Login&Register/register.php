<?php
include_once("funcs.php");
include_once("db.php");

/*
Input: login, password, passwordConfirmation, mail
GET by default, could be transformed in post easily
*/

$error = "";

if(!isset($_GET['login']))
	$error = "Entrez votre login !";
if(!isset($_GET['password']))
	$error = "Entrez votre mot de passe !";
if(!isset($_GET['passwordConfirmation']))
	$error = "Entrez la confirmation de votre mot de passe !";
if(!isset($_GET['mail']))
	$error = "Entrez votre mail !";

if($error != "")
	echo "$error";
else
{
	$login = htmlspecialchars(strip_tags($_GET['login']));
	$password = htmlspecialchars(strip_tags($_GET['password']));
	$passwordVerif = htmlspecialchars(strip_tags($_GET['passwordConfirmation']));
	$mail = htmlspecialchars(strip_tags($_GET['mail']));

	if(isElementExisting($login, "login"))
		$error = "Ce login est déjà utilisé";
	if(isElementExisting($mail, "mail"))
		$error = "Ce mail est déjà utilisé";
	if($password != $passwordVerif)
		$error = "La confirmation ne correspond pas !";

	if($error == "")
	{
		$password = password_hash($password, PASSWORD_DEFAULT);
		$query = 'INSERT INTO Users(login, password, mail) VALUES(?, ?, ?)';
		$result = $bddPDO->prepare($query);
		$result->execute(array($login, $password, $mail)); // They need to be in the right order (the same as above)

		echo "Enregistrement...";
	}
	else
		echo "$error";
}
?> 