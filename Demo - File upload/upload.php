<?php
include_once("funcs.php");

$dossier = 'upload/';
$extensions = array('png', 'gif', 'jpg', 'jpeg');
$taille_maxi = 100000;

$fichier = basename($_FILES['avatar']['name']);
$taille = filesize($_FILES['avatar']['tmp_name']);
$extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);  

if(!in_array($extension, $extensions)) // Si l'extension n'est pas dans le tableau
     $erreur = 'Vous devez uploader un fichier de type png, gif, jpg, jpeg...';
if($taille>$taille_maxi)
     $erreur = 'Le fichier est trop lourd ! Taille maximum 100mo !';

if(!isset($erreur)) // S'il n'y a pas d'erreur, on upload
{
     // On va renommer le fichier pour éviter les doublons (25^62 = 7.3468e90)
     $shouldContinue = true;
     while($shouldContinue)
     {
          $fichier = randomString(25).".".$extension;
          if(!is_file($dossier."/".$fichier)) // If file does not exist
               $shouldContinue = false;
     }

     // Une fois qu'on a vérifier que le fichier était unique, on upload
     if(move_uploaded_file($_FILES['avatar']['tmp_name'], $dossier . $fichier)) //Si la fonction renvoie TRUE, c'est que ça a fonctionné...
     {
          echo "Upload effectué avec succès, cliquez <a href='upload/$fichier'>ici</a> pour avoir accès à votre fichier !";
     }
     else //Sinon (la fonction renvoie FALSE).
          echo 'Echec de l\'upload !';
}
else
     echo $erreur;
?>