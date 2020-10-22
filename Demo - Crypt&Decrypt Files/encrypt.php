<?php 
include("funcs.php");
include("db.php");

$filePath = "temp/test.txt"; // Where the file to encrypt is stored
$newFilePath = "encrypted/".basename($filePath); // Where the encrypted file will be stored
$key = generate_key();
$iv = generate_iv();

encryptFile($filePath, $key, $iv, $newFilePath);

$query = "INSERT INTO Files(file_path) VALUES(?)";
$result = $bddPDO->prepare($query);
$result->execute(array($newFilePath));

// Register after Files otherwise constraint key fail

// Idealy, IV and KEY should be stored in an other database with restricted access
// It's stored to decrypt later
$fileID = get_last_entry(); // Getting the new fileID

$query = "INSERT INTO Crypto(uid, iv, cle) VALUES(?, ?, ?)";
$result = $bddPDO->prepare($query);
$result->execute(array($fileID, $iv, $key));

echo "File is now encrypted in ".$newFilePath;
?>
