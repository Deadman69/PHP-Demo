<?php
include_once("funcs.php");
include_once("db.php");

/*
Input: login, password
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
	$login = mysqli_real_escape_string($db, $_GET['login']);
	$password = mysqli_real_escape_string($db, $_GET['password']);
	$passwordVerif = mysqli_real_escape_string($db, $_GET['passwordConfirmation']);
	$mail = mysqli_real_escape_string($db, $_GET['mail']);

	if(isElementExisting($login, "login"))
		$error = "Ce login est déjà utilisé";
	if(isElementExisting($mail, "mail"))
		$error = "Ce mail est déjà utilisé";
	if($password != $passwordConfirmation)
		$error = "La confirmation ne correspond pas !";

	if($error == "")
	{
		$password = password_hash($password, PASSWORD_DEFAULT);
		$query = "INSERT INTO Users(login, password, mail) VALUES('$login', '$password', '$mail')";
		mysqli_query($db, $query);
	}
	else
		echo "$error";
}
?> 