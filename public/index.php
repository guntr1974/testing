<?php

function new_freephoneline_crypto_context($Key) {
	$Key = substr(md5($Key), 0, 16); //The first 16 characters of the hex represntation of the MD5 of the key
	$IV = 'fedcba9876543210'; //The IV they use
    if ($CryptoModule = mcrypt_module_open('rijndael-128', '', 'cbc', '')) { //Open the cipher (AES _is_ Rijindael)
        mcrypt_generic_init($CryptoModule, $Key, $IV); //Initialize
        return $CryptoModule; //Return
    } else {
        die('Initializing mcrypt failed!');
    }
}

function delete_freephoneline_crypto_context($CryptoModule) {
    mcrypt_generic_deinit($CryptoModule);
    mcrypt_module_close($CryptoModule);
}

$SIPUsername = '13438828274';
$EncryptedSIPPassword = base64_decode('PwCwHO8qjQvsihu0Nv48VQ==');

$Context = new_freephoneline_crypto_context($SIPUsername);
$DecryptedPassword = mdecrypt_generic($Context, $EncryptedSIPPassword);
delete_freephoneline_crypto_context($Context);

$Output = 'Username is: '.$SIPUsername.', and password is: '.$DecryptedPassword;

echo $Output;
?>
