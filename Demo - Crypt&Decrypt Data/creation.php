<?php 
include_once("myFuncs.php");
include_once("db.php");

// Generating our values with cryptographic method (check myFuncs.php)
$iv = generate_iv();
$key = generate_user_key();


// Encrypting data
$prenom = encrypt("John", $iv, $key);
$nom = encrypt("Doe", $iv, $key);
$mail = encrypt("contact@contact.com", $iv, $key);
$telephone = encrypt("+33648756935", $iv, $key);
$login = "mySuperLogin";
// In this example, password is not encrypted, but you should not use this method to encrypt password but using
// password_hash() instead, so password won't be decryptable
$password = "myS3cu4edP@ssw04d";

$query = "INSERT INTO Users(nom, prenom, mail, telephone, login, password) VALUES('$nom', '$prenom', '$mail', '$telephone', '$login', '$password')";
mysqli_query($db, $query);

// Register after Users otherwise constraint key fail

// Idealy, IV and KEY should be stored in an other database with restricted access
// It's stored to decrypt later
// Here UserID = 1, should remplace with $_SESSION['id']
$query = "INSERT INTO Crypto(uid, iv, cle) VALUES(1, '$iv', '$key')";
mysqli_query($db, $query);
?> 
