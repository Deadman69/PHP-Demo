<?php
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

if($error != "")
	echo "$error";
else
{
	$login = mysqli_real_escape_string($db, $_GET['login']);
	$password = mysqli_real_escape_string($db, $_GET['password']);

	$query = "SELECT login, password FROM usersBis WHERE login = '$login'";
	$reponse = $bddPDO->prepare($query);
	$reponse->execute();

	while ($donnees = $reponse->fetch())
	{
		if(password_verify($password, $donnees['password']))
			echo "Connexion acceptée, bienvenue ".$donnees['login']. " !";
		else
			echo "Mot de passe eronner !";
	}
}
?> 