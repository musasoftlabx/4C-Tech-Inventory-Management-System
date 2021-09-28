<?php
require 'DB.php';
$APPROOT   = str_replace('v1', '', APPROOT);
$UserAgent = $_SERVER['HTTP_USER_AGENT'];

require $APPROOT . '/libraries/phpJWT/autoload.php';
use \Firebase\JWT\JWT;

# Prevent unauthorized access via CSRF
function browserify($UserAgent)
{
    if (strpos($UserAgent, 'Opera') || strpos($UserAgent, 'OPR/')) {
        return 'Opera';
    } elseif (strpos($UserAgent, 'Edge')) {
        return 'Edge';
    } elseif (strpos($UserAgent, 'Chrome')) {
        return 'Chrome';
    } elseif (strpos($UserAgent, 'Safari')) {
        return 'Safari';
    } elseif (strpos($UserAgent, 'Firefox')) {
        return 'Firefox';
    } elseif (strpos($UserAgent, 'MSIE') || strpos($UserAgent, 'Trident/7')) {
        return 'Internet Explorer';
    }

    return 'Other';
}

function GetClientIP()
{
    $keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
    foreach ($keys as $k) {
        if (!empty($_SERVER[$k]) && filter_var($_SERVER[$k], FILTER_VALIDATE_IP)) {
            return $_SERVER[$k];
        }
    }
    return "UNKNOWN";
}

function GetDevice()
{
    $useragent = $_SERVER['HTTP_USER_AGENT'];
    if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4))) {
        return 'Mobile';
    } else {
        return 'Desktop';
    }
}

function GetOS()
{
    global $UserAgent;
    $OperatingSystem  = "Unknown OS Platform";
    $OperatingSystems = [
        '/windows nt 10/i'      => 'Windows 10',
        '/windows nt 6.3/i'     => 'Windows 8.1',
        '/windows nt 6.2/i'     => 'Windows 8',
        '/windows nt 6.1/i'     => 'Windows 7',
        '/windows nt 6.0/i'     => 'Windows Vista',
        '/windows nt 5.2/i'     => 'Windows Server 2003/XP x64',
        '/windows nt 5.1/i'     => 'Windows XP',
        '/windows xp/i'         => 'Windows XP',
        '/windows nt 5.0/i'     => 'Windows 2000',
        '/windows me/i'         => 'Windows ME',
        '/win98/i'              => 'Windows 98',
        '/win95/i'              => 'Windows 95',
        '/win16/i'              => 'Windows 3.11',
        '/macintosh|mac os x/i' => 'Mac OS X',
        '/mac_powerpc/i'        => 'Mac OS 9',
        '/linux/i'              => 'Linux',
        '/ubuntu/i'             => 'Ubuntu',
        '/iphone/i'             => 'iPhone',
        '/ipod/i'               => 'iPod',
        '/ipad/i'               => 'iPad',
        '/android/i'            => 'Android',
        '/blackberry/i'         => 'BlackBerry',
        '/webos/i'              => 'Mobile',
    ];

    foreach ($OperatingSystems as $regex => $value) {
        if (preg_match($regex, $UserAgent)) {
            $OperatingSystem = $value;
        }
    }
    return $OperatingSystem;
}

function GetBrowser()
{
    global $UserAgent;
    $browser  = "Unknown Browser";
    $browsers = [
        '/msie/i'      => 'Internet Explorer',
        '/firefox/i'   => 'Firefox',
        '/safari/i'    => 'Safari',
        '/chrome/i'    => 'Chrome',
        '/edge/i'      => 'Edge',
        '/opera/i'     => 'Opera',
        '/netscape/i'  => 'Netscape',
        '/maxthon/i'   => 'Maxthon',
        '/konqueror/i' => 'Konqueror',
        '/mobile/i'    => 'Handheld Browser',
    ];

    foreach ($browsers as $regex => $value) {
        if (preg_match($regex, $UserAgent)) {
            $browser = $value;
        }
    }
    return $browser;
}

function ChainPDO($sql, $binds = null)
{
    global $DB;

    if (!$binds) {
        return $DB->query($sql);
    }
    $stmt = $DB->prepare($sql);
    $stmt->execute($binds);
    return $stmt;
}

/*if (browserify($_SERVER['HTTP_USER_AGENT']) == 'Other') {
die();
}*/
function LogToDB($params)
{
    global $LOG;

    $params = json_decode($params, true);

    $ResponseCode = empty($params['ResponseCode']) ? null : $params['ResponseCode'];
    $ResponseTime = empty($params['ResponseTime']) ? null : $params['ResponseTime'];
    $logtype      = $params['logtype'];
    $operation    = $params['operation'];
    $service      = $params['service'];
    $endpoint     = empty($params['endpoint']) ? null : $params['endpoint'];
    $request      = $params['request'];
    $response     = $params['response'];

    $LOGGER = $LOG->prepare("INSERT INTO logs VALUES (NULL,?,?,?,?,?,?,?,?,now())");
    $LOGGER->execute([$logtype, $operation, $ResponseCode, $ResponseTime, $service, $endpoint, $request, $response]);
}

function Minify($str)
{
    return preg_replace('/\s+/', '', $str);
}

function chain($str)
{
    return preg_replace('/\s+/', '', $str);
}

function SMS($PhoneNumber, $msg, $DailyThreshold = 10)
{
    $CountDailySMS = ChainPDO("SELECT COUNT(PhoneNumber) FROM SMS WHERE PhoneNumber = ? AND DATE(timestamp) = CURDATE()", [$PhoneNumber])->fetchColumn();

    if ($CountDailySMS <= $DailyThreshold) {
        $request =
            '{
                "CreateCommunicationVBMRequest": {
                    "CommunicationVBO": {
                        "IDs": {
                            "ID": "' . $PhoneNumber . '"
                        },
                        "Parts": {
                            "Body": {
                                "Text": "' . $msg . '"
                            },
                            "Trailer": {
                                "Text": "DARAJA"
                            }
                        }
                    }
                }
            }';

        $ch = curl_init(SMS_ENDPOINT);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type:application/json',
            'Accept-Encoding:application/json',
            'x-source-system:ussd',
            'x-correlation-conversationid:134524353525534534',
            'x-source-identity-token:VElCQ09BQ0FDSUExMjMh',
            'x-route-id:sendsms',
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(json_decode($request), true));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, CURL_TIMEOUT);
        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        LogToDB(json_encode(
            [
                'logtype'   => 'DEBUG',
                'operation' => 'DATABASE',
                'service'   => 'SendSMS',
                'endpoint'  => SMS_ENDPOINT,
                'request'   => $request,
                'response'  => null,
            ]
        ));

        if ($error) {
            http_response_code(503);
            LogToDB(json_encode(
                [
                    'logtype'   => 'ERROR',
                    'operation' => 'DATABASE',
                    'service'   => 'SendSMS',
                    'endpoint'  => SMS_ENDPOINT,
                    'request'   => $request,
                    'response'  => $error,
                ]
            ));
        } else {
            LogToDB(json_encode(
                [
                    'logtype'   => 'DEBUG',
                    'operation' => 'DATABASE',
                    'service'   => 'SendSMS',
                    'endpoint'  => SMS_ENDPOINT,
                    'request'   => $request,
                    'response'  => $response,
                ]
            ));
            ChainPDO("INSERT INTO SMS VALUES (NULL, ?, now())", [$PhoneNumber]);
        }
    }
}

function PasswordHasher($password)
{
    $hmac            = hash_hmac('sha512', $password, file_get_contents('../GenerateSecurityCredential/SandboxCertificate.cer'));
    $base64          = strtr(base64_encode(0), '+', '.');
    $salt            = substr($base64, 0, 22);
    $bcrypt          = crypt($hmac, 'daraja' . $salt);
    return $password = md5($bcrypt);
}

function OTP($operation, $EmailAddress, $OTP = null)
{
    global $DB;

    if ($operation === 'GENERATE') {
        $EmailExists = ChainPDO("SELECT COUNT(EmailAddress) FROM users WHERE EmailAddress = ?", [$EmailAddress])->fetchColumn();

        if ($EmailExists === 1) {
            $PhoneNumber = ChainPDO("SELECT PhoneNumber FROM users WHERE EmailAddress = ?", [$EmailAddress])->fetchColumn();
            $PhoneNumber = str_replace(['+', ' '], '', $PhoneNumber);

            $OTP   = mt_rand(1000, 9999);
            $token = [
                "iss"  => "https://safaricom.co.ke",
                "aud"  => "https://safaricom.co.ke",
                "iat"  => time(),
                "nbf"  => time(),
                "exp"  => time() + (60 * 1),
                "data" => [
                    "PhoneNumber" => $PhoneNumber,
                    "OTP"         => $OTP,
                ],
            ];
            ChainPDO("DELETE FROM OTP WHERE PhoneNumber = ?", [$PhoneNumber]);
            ChainPDO("DELETE FROM OTP WHERE UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(timestamp) > 120");
            ChainPDO("INSERT INTO OTP VALUES (NULL, ?, ?, ?, now())", [JWT::encode($token, JWT_KEY), $PhoneNumber, $OTP]);
            SMS($PhoneNumber, 'Your One Time PIN (OTP) is ' . $OTP);
        } else {
            http_response_code(401);
            echo 'That email address does not exist. Kindly enter the email address you used during account creation.';
        }
    } else if ($operation === 'VALIDATE') {
        $OTPexists = ChainPDO("SELECT COUNT(OTP) FROM OTP WHERE OTP = ?", [$OTP])->fetchColumn();

        if ($OTPexists === 1) {
            $PhoneNumber = ChainPDO("SELECT PhoneNumber FROM OTP WHERE OTP = ?", [$OTP])->fetchColumn();
            $token       = ChainPDO("SELECT token FROM OTP WHERE PhoneNumber = ? AND OTP = ?", [$PhoneNumber, $OTP])->fetchColumn();
            try {
                JWT::decode($token, JWT_KEY, ['HS256']);
            } catch (Exception $e) {
                json_encode(["message" => "Access denied.", "error" => $e->getMessage()]);
                http_response_code(401);
                echo 'The OTP you entered is invalid or has expired. Please try again.';
            }
        } else {
            http_response_code(401);
            echo 'The OTP you entered is invalid or has expired. Please try again.';
        }
    }
}

function JWT($params)
{
    return JWT::encode([
        "iss"  => "https://safaricom.co.ke",
        "aud"  => "https://safaricom.co.ke",
        "iat"  => time(), // issued at
        "nbf"  => time(), // not before in seconds
        "exp"  => time() + (60 * 60), // expire time in seconds
        "data" => json_decode($params, true),
    ], JWT_KEY);
}

function Tokenify($token, $reissue)
{
    if ($token) {
        try {
            $decoded = JWT::decode($token, JWT_KEY, ['HS256']);
            if ($reissue) {
                $payload = explode('.', $token)[1];
                $decoded = json_decode(base64_decode($payload), true);
                echo JWT(json_encode($decoded['data']));
            }
        } catch (Exception $e) {
            http_response_code(401);
            json_encode([
                "message" => "Access denied.",
                "error"   => $e->getMessage(),
            ]);
            die();
        }
    } else {
        http_response_code(401);
        echo json_encode(["message" => "Access denied."]);
        die();
    }
}

function TokenDeconstruct($token, $item)
{
    $payload = explode('.', $token)[1];
    $decoded = json_decode(base64_decode($payload), true);
    return $decoded['data'][$item];
}

function GenerateAccessToken($authorization, $echo = false)
{
    $endpoint = AUTHORIZATION_ENDPOINT;

    $headers = [
        'Authorization: Basic ' . $authorization,
    ];

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, CURL_TIMEOUT);
    $response     = curl_exec($ch);
    $error        = curl_error($ch);
    $ResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $ResponseTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    http_response_code($ResponseCode);

    if ($error) {
        LogToDB(json_encode(
            [
                'logtype'      => 'EMERGENCY',
                'operation'    => 'API',
                'ResponseCode' => $ResponseCode,
                'ResponseTime' => $ResponseTime,
                'service'      => 'Authorization',
                'endpoint'     => $endpoint,
                'request'      => json_encode($headers),
                'response'     => $error,
            ]
        ));

        echo json_encode([
            'title'   => 'Service error!',
            'content' => CURL_ERROR,
        ]);
    } else {
        switch ($ResponseCode) {
            case 200:
                if ($echo) {
                    $languages = ChainPDO("SELECT language, code FROM APIlanguages WHERE method = ?", ['GET'])->fetchAll(PDO::FETCH_KEY_PAIR);
                    $languages = json_encode($languages);
                    $languages = str_replace("REPLACE_WITH_ENDPOINT", $endpoint, $languages);
                    $languages = str_replace("REPLACE_WITH_AUTHORIZATION", $authorization, $languages);
                    $languages = json_decode($languages, true);

                    echo json_encode([
                        'dataset'   => $response,
                        'languages' => $languages,
                    ], JSON_PARTIAL_OUTPUT_ON_ERROR);
                } else {
                    return json_decode($response, true);
                }
                break;
            default:
                LogToDB(json_encode(
                    [
                        'logtype'      => 'ERROR',
                        'operation'    => 'API',
                        'ResponseCode' => $ResponseCode,
                        'ResponseTime' => $ResponseTime,
                        'service'      => 'Authorization',
                        'endpoint'     => $endpoint,
                        'request'      => json_encode($headers),
                        'response'     => $response,
                    ]
                ));

                echo json_encode([
                    'title'   => 'Unexpected Error!',
                    'content' => 'Make sure the information you entered is correct and retry.',
                ]);
                break;
        }
    }

    curl_close($ch);
}

function TimeElapsed($datetime, $full = false)
{
    $now  = new DateTime;
    $ago  = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) {
        $string = array_slice($string, 0, 1);
    }

    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function HSV_TO_RGB($H, $S, $V) // HSV Values:Number 0-1

{
    // RGB Results:Number 0-255
    $RGB = array();

    if ($S == 0) {
        $R = $G = $B = $V * 255;
    } else {
        $var_H = $H * 6;
        $var_i = floor($var_H);
        $var_1 = $V * (1 - $S);
        $var_2 = $V * (1 - $S * ($var_H - $var_i));
        $var_3 = $V * (1 - $S * (1 - ($var_H - $var_i)));

        if ($var_i == 0) {
            $var_R = $V;
            $var_G = $var_3;
            $var_B = $var_1;
        } else if ($var_i == 1) {
            $var_R = $var_2;
            $var_G = $V;
            $var_B = $var_1;
        } else if ($var_i == 2) {
            $var_R = $var_1;
            $var_G = $V;
            $var_B = $var_3;
        } else if ($var_i == 3) {
            $var_R = $var_1;
            $var_G = $var_2;
            $var_B = $V;
        } else if ($var_i == 4) {
            $var_R = $var_3;
            $var_G = $var_1;
            $var_B = $V;
        } else {
            $var_R = $V;
            $var_G = $var_1;
            $var_B = $var_2;
        }

        $R = $var_R * 255;
        $G = $var_G * 255;
        $B = $var_B * 255;
    }

    $RGB['R'] = $R;
    $RGB['G'] = $G;
    $RGB['B'] = $B;

    return $RGB;
}

function GetColorFromLetter($word)
{
    // get the percent of the first letter ranging from 0-1
    $first_letter_code = (ord(strtolower($word[0])) - 97) / 25.0;

    // add a phase depending on where you want to start on the color spectrum
    // red is 0, green is 0.25, cyan is 0.5, blue is ~0.75, and 1 is back to red
    $hue = $first_letter_code + 0.25;

    // you may also want to divide by how much of the spectrum you want to cover
    // (making the colors range only from green to blue, for instance)
    // but i'll leave that as an exercise

    // constrain it to 0-1
    if ($hue > 1.0) {
        $hue -= 1.0;
    }

    // the second value is the saturation ("colorfulness", ranging from gray to fully-colored)
    // the third is the value (brightness)
    $rgb = HSV_TO_RGB($hue, 1, 0.75);

    $hexstring = "#";

    foreach ($rgb as $c) {
        $hexstring .= str_pad(dechex($c), 2, "0", STR_PAD_LEFT);
    }

    return $hexstring;
}

set_error_handler(
    function ($ErrorSeverity, $ErrorMessage, $ErrorFile, $ErrorLine) {
        $logtype = 'ERROR';
        switch ($ErrorSeverity) {
            case E_ERROR:
            case E_WARNING:
                $logtype = 'WARNING';
            case E_PARSE:
                $logtype = 'CRITICAL';
            case E_NOTICE:
                $logtype = 'NOTICE';
            case E_CORE_ERROR:
            case E_CORE_WARNING:
            case E_COMPILE_ERROR:
            case E_COMPILE_WARNING:
            case E_USER_ERROR:
            case E_USER_WARNING:
            case E_USER_NOTICE:
            case E_STRICT:
            case E_RECOVERABLE_ERROR:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
        }
        http_response_code(500);
        LogToDB(json_encode(
            [
                'logtype'   => $logtype,
                'operation' => 'SYSTEM ERROR',
                'service'   => 'Caught Exceptions',
                'endpoint'  => $ErrorFile,
                'request'   => null,
                'response'  => '{ "Line": "' . $ErrorLine . '", "Message": "' . $ErrorMessage . '" }',
            ]
        ));
        echo $ErrorSeverity . ' : ' . $ErrorMessage . ' : ' . $ErrorFile . ' : ' . $ErrorLine;
//echo "Whoops. Something went wrong. Please try again. If this persists, don't hesitate to reach us.";
        die();
    }
);
