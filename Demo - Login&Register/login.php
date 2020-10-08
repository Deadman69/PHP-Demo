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
	$login = htmlspecialchars(strip_tags($_GET['login']));
	$password = htmlspecialchars(strip_tags($_GET['password']));

	$query = "SELECT login, password FROM users WHERE login = '$login' LIMIT 1";
	$reponse = $bddPDO->prepare($query);
	$reponse->execute();

	while ($donnees = $reponse->fetch()) {
		if(password_verify($password, $donnees['password']))
			echo "Connexion acceptée, bienvenue ".$donnees['login']. " !";
		else
			echo "Mot de passe eronné !";
	}
}
?> 