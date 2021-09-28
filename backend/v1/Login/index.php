<?php
require '../functions.php';

$_JSON    = json_decode(file_get_contents('php://input'), true);
$username = strip_tags($_JSON['username']);
$password = base64_decode(strip_tags($_JSON['password']));
//$password        = PasswordHasher($password);

$usernameExists = ChainPDO("SELECT COUNT(*) FROM users WHERE username = ?", [$username])->fetchColumn();

if ($usernameExists === 0) {
    http_response_code(401);
    echo $error = "Email or username doesn't exist. Sign up to create an account.";
/*    LogToDB(json_encode(
[
'logtype'   => 'WARNING',
'operation' => 'DATABASE',
'service'   => basename(dirname(__FILE__)),
'request'   => "{ Email_Username: " . $username . "}",
'response'  => $error,
]
));*/
} else {
    $DoesExist = ChainPDO("SELECT COUNT(*) FROM users WHERE username = ? AND password = ?", [$username, $password])->fetchColumn();

    if ($DoesExist === 0) {
        http_response_code(401);
        echo $error = 'Invalid credentials. Please try again.';
        /* LogToDB(json_encode(
    [
    'logtype'   => 'WARNING',
    'operation' => 'DATABASE',
    'service'   => basename(dirname(__FILE__)),
    'request'   => "{ Email_Username: " . $username . "}",
    'response'  => $error,
    ]
    ));*/
    } else {
        $selection = ChainPDO("SELECT FirstName, LastName FROM users WHERE username = ? AND password = ?", [$username, $password])->fetch();

        echo JWT(json_encode([
            'FirstName' => $selection['FirstName'],
            'LastName'  => $selection['LastName'],
            'username'  => $username,
        ]));
    }

}
