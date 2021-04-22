<?php
set_time_limit(0);
$socket = fsockopen('irc.root-me.org','6667');
// Vérification de la bonne connexion :
if(!$socket) {
	// Si on n'a pas réussi, on affiche un message d'erreur et on quitte.
	echo 'Impossible de se connecter';
	exit;
}


// On renseigne l'USER
$nicknameNumber = "BotTestChallangeOne";
fputs($socket,"USER $nicknameNumber $nicknameNumber $nicknameNumber $nicknameNumber\r\n");
// On donne le NICK.
fputs($socket,"NICK $nicknameNumber\r\n");

$continuer = 1; // On initialise une variable permettant de savoir si l'on doit continuer la boucle.
while($continuer) // Boucle principale.
{

	$donnees = fgets($socket, 1024); // Le 1024 permet de limiter la quantité de caractères à recevoir du serveur.
	$retour = explode(':',$donnees); // On sépare les différentes données.
	// On regarde si c'est un PING, et, le cas échéant, on envoie notre PONG.
	if(rtrim($retour[0]) == 'PING')
	{
		fputs($socket,'PONG :'.$retour[1]);
		$continuer = 0;
	}
	if($donnees) // Si le serveur a envoyé des données, on les affiche.
		echo $donnees;
}

fputs($socket,"JOIN #root-me_challenge\r\n"); // On rejoint le canal.

// Boucle principale du programme :
while(1)
{
	$donnees = fgets($socket, 1024); // On lit les données du serveur.
	if($donnees) // Si le serveur nous a envoyé quelque chose.
	{
		$commande = explode(' ',$donnees);
		if($commande[0] == 'PING') // Si c'est un PING, on renvoie un PONG.
		{
			fputs($socket,"PONG ".$commande[1]."\r\n");
		}
		if($commande[1] == 'PRIVMSG') // Si c'est un message.
		{
			$fp = fopen('data.txt', 'a+');
			fwrite($fp, json_encode($commande)."\n\n\n");

			$pseudo = explode('!',$commande[0]); // On prend le pseudo de la personne
			$pseudo = substr($pseudo[0],1); // On enlève le double point au début du pseudo.
			if($pseudo == "Deadman")
				fputs($socket,"PRIVMSG Candy :!ep2 \r\n");

			if($pseudo == "Candy" and count($commande) < 5) {
				$encodedString = str_replace(":", "", $commande[3]);
				$encodedString = str_replace("\r\n", "", $encodedString);
				$message = "!ep2 -rep ".base64_decode($encodedString);
				fwrite($fp, $message."\n\n\n");
				fputs($socket,"PRIVMSG Candy :$message \r\n");
			}

			fclose($fp);
		}
	}
	usleep(100); // On fait « dormir » le programme afin d'économiser l'utilisation du processeur.
}
?>