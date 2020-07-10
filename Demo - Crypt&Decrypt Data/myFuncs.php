<?php
function generate_iv()
{
	$ciphering = "AES-128-CTR";
	$iv_length = openssl_cipher_iv_length($ciphering);
	$iv = openssl_random_pseudo_bytes($iv_length);
	return $iv;
}

function generate_user_key()
{
	$ciphering = "AES-128-CTR";
	$cle_taille = openssl_cipher_iv_length($ciphering);
	$cle = openssl_random_pseudo_bytes($cle_taille);
	return $cle;
}

function get_key($db, $user_id)
{
	$query = "SELECT cle FROM crypto WHERE uid = '".$user_id."' ";
    $result = mysqli_query($db, $query);
   	$data = mysqli_fetch_assoc($result);
	return $data['cle'];
}

function get_iv($db, $user_id)
{
	$query = "SELECT iv FROM crypto WHERE uid = '".$user_id."' ";
    $result = mysqli_query($db, $query);
   	$data = mysqli_fetch_assoc($result);
	return $data['iv'];
}

function encrypt($string, $iv, $key)
{
	$ciphering = "AES-128-CTR"; 
	$options = 0;
	$encryption_iv = generate_iv();
	$encryption_key = generate_user_key(); 

	$encryption = openssl_encrypt($string, $ciphering, $key, $options, $iv); 
	return $encryption;
}

function decrypt($crypted_string, $iv, $key)
{
	$ciphering = "AES-128-CTR"; 
	$options = 0;

	$decryption = openssl_decrypt ($crypted_string, $ciphering, $key, $options, $iv);
	return $decryption;
}
?>