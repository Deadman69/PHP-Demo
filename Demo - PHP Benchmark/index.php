<?php
/**
	/!\ DISCLAIMER /!\
	
	Ce programme n'est pas exact, il ne donne le temps qu'à titre indicatif et peut varier
	en cas d'utilisation d'un autre programme
	
	
	/!\ DISCLAIMER /!\
*/





// Début de la configuration

$funcRepetitions = 1000; // nombre de fois que la fonction va être répétée 
function benchmarkFunc() { // la fonction à benchmark
	echo "Hello !";
}


$sgbd = "mysql"; // mysql, sqlite, oci, sqlsrv     :  oci = oracle
$host = "localhost";
$user_mysql = "root";    // nom de l'utilisateur MySQL 
$password_mysql = "root";    // mot de passe de l'utilisateur MySQL
$database = "cake_tuto";

// Fin de la configuration

$results = [];
try {$bddPDO = new PDO($sgbd.":host=".$host.";dbname=".$database.";charset=utf8", $user_mysql, $password_mysql);}
catch(Exception $e) {die('Erreur : '.$e->getMessage()); }



for ($i = 1; $i <= $funcRepetitions; $i++) {
	$start_time = microtime(TRUE);
	benchmarkFunc();
	$end_time = microtime(TRUE);
	array_push($results, ($end_time - $start_time));
}

echo "<br><br><br>Temps total d'exécution : ".(array_sum($results))." secondes";
echo "<br>Temps moyen d'exécution : ".(array_sum($results)/count($results))." secondes";
echo "<br>Temps d'exécution le plus long : ".(max($results))." secondes";
echo "<br>Temps d'exécution le plus court : ".(min($results))." secondes";

?>