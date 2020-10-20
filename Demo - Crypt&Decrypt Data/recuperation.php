<?php
include_once("myFuncs.php");
include_once("db.php");

$userID = 1;

$query = "SELECT nom, prenom, mail, telephone, login, password FROM Users WHERE id = ?";
$reponse = $bddPDO->prepare($query);
$reponse->execute(array($userID));

// Get user KEY and IV with user ID (here 1)
$key = get_key($userID);
$iv = get_iv($userID);

while ($donnees = $reponse->fetch())
{
    echo "Crypted Last Name : ".$donnees['nom']."</br>";
    echo "Crypted First Name : ".$donnees['prenom']."</br>";
    echo "Crypted Mail : ".$donnees['mail']."</br>";
    echo "Crypted Phone : ".$donnees['telephone']."</br>";
    echo "Login : ".$donnees['login']."</br>";
    echo "Password : ".$donnees['password']."</br>";
    echo "<br><br>";
    echo "Uncrypted Last Name : ".decrypt($donnees['nom'], $iv, $key)."</br>";
    echo "Uncrypted First Name : ".decrypt($donnees['prenom'], $iv, $key)."</br>";
    echo "Uncrypted Mail : ".decrypt($donnees['mail'], $iv, $key)."</br>";
    echo "Uncrypted Phone : ".decrypt($donnees['telephone'], $iv, $key)."</br>";
    echo "Login : ".$donnees['login']."</br>";
    echo "Password : ".$donnees['password']."</br>";
}
?>
