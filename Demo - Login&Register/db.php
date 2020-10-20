<?php
$host = "localhost";
$user_mysql = "admin";    // nom de l'utilisateur MySQL 
$password_mysql = "admin";    // mot de passe de l'utilisateur MySQL
$database = "test";

try
{
    $bddPDO = new PDO("mysql:host=".$host.";dbname=".$database.";charset=utf8", $user_mysql, $password_mysql);
}
catch(Exception $e)
{
    die('Erreur : '.$e->getMessage());
}
?>
