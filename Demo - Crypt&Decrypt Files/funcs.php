<?php
function generate_iv() {
	$ciphering = "AES-128-CTR";
	$iv_length = openssl_cipher_iv_length($ciphering);
	$iv = openssl_random_pseudo_bytes($iv_length);
	return $iv;
}

function generate_key() {
	$ciphering = "AES-128-CTR";
	$cle_taille = openssl_cipher_iv_length($ciphering);
	$cle = openssl_random_pseudo_bytes($cle_taille);
	return $cle;
}

function get_key($file_id) {
	global $bddPDO;
	$query = "SELECT cle FROM crypto WHERE uid = '".$file_id."' ";
    $result = $bddPDO->prepare($query);
	$result->execute();
	return $result->fetch()['cle'];
}

function get_iv($file_id) {
	global $bddPDO;
	$query = "SELECT iv FROM crypto WHERE uid = '".$file_id."' ";
    $result = $bddPDO->prepare($query);
	$result->execute();
	return $result->fetch()['iv'];
}

function get_file_id($file_path) {
	global $bddPDO;
	$query = "SELECT id FROM files WHERE file_path = '".$file_path."' ";
    $result = $bddPDO->prepare($query);
	$result->execute();
	return $result->fetch()['id'];
}

function get_last_entry() {
	global $bddPDO;
	$query = "SELECT id FROM files ORDER BY id LIMIT 1 ";
    $result = $bddPDO->prepare($query);
	$result->execute();
	return $result->fetch()['id'];
}


/**
 * Define the number of blocks that should be read from the source file for each chunk.
 * For 'AES-128-CBC' each block consist of 16 bytes.
 * So if we read 10,000 blocks we load 160kb into memory. You may adjust this value
 * to read/write shorter or longer chunks.
 */
define('FILE_ENCRYPTION_BLOCKS', 10000);

/**
 * Encrypt the passed file and saves the result in a new file with ".enc" as suffix.
 * 
 * @param string $source Path to file that should be encrypted
 * @param string $key    The key used for the encryption
 * @param string $iv 	 The iv used for the encryption
 * @param string $dest   File name where the encryped file should be written to.
 * @return string|false  Returns the file name that has been created or FALSE if an error occured
 */
function encryptFile($source, $key, $iv, $dest) {
    // $key = substr(sha1($key, true), 0, 16);
    // $iv = openssl_random_pseudo_bytes(16);

    $error = false;
    if ($fpOut = fopen($dest, 'w')) {
        // Put the initialzation vector to the beginning of the file
        fwrite($fpOut, $iv);
        if ($fpIn = fopen($source, 'rb')) {
            while (!feof($fpIn)) {
                $plaintext = fread($fpIn, 16 * FILE_ENCRYPTION_BLOCKS);
                $ciphertext = openssl_encrypt($plaintext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
                // Use the first 16 bytes of the ciphertext as the next initialization vector
                $iv = substr($ciphertext, 0, 16);
                fwrite($fpOut, $ciphertext);
            }
            fclose($fpIn);
        } else {
            $error = true;
        }
        fclose($fpOut);
    } else {
        $error = true;
    }

    return $error ? false : $dest;
}

/**
 * Dencrypt the passed file and saves the result in a new file, removing the
 * last 4 characters from file name.
 * 
 * @param string $source Path to file that should be decrypted
 * @param string $key    The key used for the decryption (must be the same as for encryption)
 * @param string $dest   File name where the decryped file should be written to.
 * @return string|false  Returns the file name that has been created or FALSE if an error occured
 */
function decryptFile($source, $key, $dest) {
    // $key = substr(sha1($key, true), 0, 16);

    $error = false;
    if ($fpOut = fopen($dest, 'w')) {
        if ($fpIn = fopen($source, 'rb')) {
            // Get the initialzation vector from the beginning of the file
            $iv = fread($fpIn, 16);
            while (!feof($fpIn)) {
                $ciphertext = fread($fpIn, 16 * (FILE_ENCRYPTION_BLOCKS + 1)); // we have to read one block more for decrypting than for encrypting
                $plaintext = openssl_decrypt($ciphertext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
                // Use the first 16 bytes of the ciphertext as the next initialization vector
                $iv = substr($ciphertext, 0, 16);
                fwrite($fpOut, $plaintext);
            }
            fclose($fpIn);
        } else {
            $error = true;
        }
        fclose($fpOut);
    } else {
        $error = true;
    }

    return $error ? false : $dest;
}


?>