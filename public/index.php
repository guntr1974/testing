<?php

//This file is called index.php

$Output = 'Nothing interesting to show here Capt\'n';

function new_freephoneline_crypto_context($Key) {
    $Key = md5($Key);
	echo $Key;
//    $Key = substr($Key, 0, 16);
    $IV = 'fedcba9876543210';
    if ($CryptoModule = mcrypt_module_open('rijndael-128', '', 'cbc', '')) {
        mcrypt_generic_init($CryptoModule, $Key, $IV);
        return $CryptoModule;
    } else {
        die('Initializing mcrypt failed!');
    }
}

function delete_freephoneline_crypto_context($CryptoModule) {
    mcrypt_generic_deinit($CryptoModule);
    mcrypt_module_close($CryptoModule);
}

if (isset($_REQUEST['u']) && isset($_REQUEST['p'])) {

    $WebUsername = $_REQUEST['u']; $WebPassword = $_REQUEST['p'];

    $Context = new_freephoneline_crypto_context($WebUsername);
    $EncryptedWebPassword = mcrypt_generic($Context, $WebPassword);
    delete_freephoneline_crypto_context($Context);

    if ($CURLHandle = curl_init('http://www.freephoneline.ca/services/init')) {
        curl_setopt($CURLHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($CURLHandle, CURLOPT_HEADER, false);
        curl_setopt($CURLHandle, CURLOPT_POST, true);
        curl_setopt($CURLHandle, CURLOPT_POSTFIELDS, ''
            .'web_username='.urlencode($WebUsername).'&'
            .'web_password='.urlencode(base64_encode($EncryptedWebPassword)).'&'
            .'key='
        );

        $Data = array();
        foreach(explode("\r\n", curl_exec($CURLHandle)) as $Line)
            if (false !== $Target = strpos($Line, '='))
                $Data[trim(substr($Line,0,$Target))] = trim(substr($Line, $Target+1));
        curl_close($CURLHandle);

        if (!isset($Data['sip_username']) || !isset($Data['sip_password'])) {
            $Output = 'Error getting credentials; perhaps wrong username or password?)';
        } else {
            $SIPUsername = $Data['sip_username'];
            $EncryptedSIPPassword = base64_decode($Data['sip_password']);

            $Context = new_freephoneline_crypto_context($SIPUsername);
            $DecryptedPassword = mdecrypt_generic($Context, $EncryptedSIPPassword);
            delete_freephoneline_crypto_context($Context);

            $Output = 'Username is: '.$SIPUsername.', and password is: '.$DecryptedPassword;
        }
    } else {
        die('Initializing CURL failed!');
    }
}
?><html>
    <head>
        <title>Freephoneline SIP Credentials Decryptor</title>
    </head>
    <body>
        <form method="post" action="index.php">
            <fieldset>
                <legend>FreePhoneLine Login Information</legend>
                <div>
                    <label>Username:</label>
                    <input style="width: 200px;" type="text" name="u" placeholder="username" value="<?php echo isset($_REQUEST['u']) ? $_REQUEST['u'] : '' ; ?>"/>
                </div>
                <div>
                    <label>Password:</label>
                    <input style="width: 200px;" type="password" name="p" placeholder="password" value="<?php echo isset($_REQUEST['p']) ? $_REQUEST['p'] : ''; ?>"/>
                </div>
            </div>
            <div><input type="submit" value="Get My SIP Info"/></div>
        </form>
        <hr/> <?php echo $Output; ?>
    </body>
</html>