<?php 
include("funcs.php");
include("db.php");

$encryptedFilePath = "encrypted/test.txt"; // Where the encrypted file is stored
$decryptedFilePath = "decrypted/".basename($encryptedFilePath); // Where the decrypted file will be stored
$fileID = get_file_id($encryptedFilePath); // Encrypted file ID
$fileKey = get_key($fileID); // Getting file key in database

decryptFile($encryptedFilePath, $fileKey, $decryptedFilePath);

echo "File is decrypted in ".$decryptedFilePath;

?>