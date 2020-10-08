<?php 
include_once("myFuncs.php");
include_once("db.php");

// Generating our values with cryptographic method (check myFuncs.php)
$iv = generate_iv();
$key = generate_user_key();


// Encrypting data
$prenom = encrypt(htmlspecialchars(strip_tags("John")), $iv, $key);
$nom = encrypt(htmlspecialchars(strip_tags("Doe")), $iv, $key);
$mail = encrypt(htmlspecialchars(strip_tags("contact@contact.com")), $iv, $key);
$telephone = encrypt(htmlspecialchars(strip_tags("+33648756935")), $iv, $key);
$login = htmlspecialchars(strip_tags("mySuperLogin"));
// In this example, password is not encrypted, but you should not use this method to encrypt password but using
// password_hash() instead, so password won't be decryptable
$password = htmlspecialchars(strip_tags("myS3cu4edP@ssw04d"));

$query = "INSERT INTO Users(nom, prenom, mail, telephone, login, password) VALUES('$nom', '$prenom', '$mail', '$telephone', '$login', '$password')";
$result = $bddPDO->prepare($query);
$result->execute();

// Register after Users otherwise constraint key fail

// Idealy, IV and KEY should be stored in an other database with restricted access
// It's stored to decrypt later
// Here UserID = 1, should remplace with $_SESSION['id']
$query = "INSERT INTO Crypto(uid, iv, cle) VALUES(1, '$iv', '$key')";
$result = $bddPDO->prepare($query);
$result->execute();
?> 
