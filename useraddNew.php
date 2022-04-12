<?
//AUTH Б24
require_once 'auth.php';

//AUTH MySQL
$servername = "localhost:3306";
$database = ""; //название бащы данных
$username = ""; //имя пользователя с доступом к данной базе данных
$password = ""; //пароль пользователя
// Создаем соединение
$conn = mysqli_connect($servername, $username, $password, $database);
// Проверяем соединение
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "Connected successfully";

mysqli_set_charset($conn, "utf8mb4");

$sql = "SELECT * FROM ssybz_users";
if ($result = $conn->query($sql)) {
    foreach ($result as $row) {
        $userid = $row["ID"];
        $username = $row["user_email"];
        $sql2 = "SELECT meta_value FROM ssybz_usermeta WHERE user_id=$userid AND meta_key='first_name'";
        if ($result = $conn->query($sql2)) {
            foreach ($result as $row2) {
                $name = $row2["meta_value"];
            }
        }
        $sql2 = "SELECT meta_value FROM ssybz_usermeta WHERE user_id=$userid AND meta_key='last_name'";
        if ($result = $conn->query($sql2)) {
            foreach ($result as $row2) {
                $last_name = $row2["meta_value"];
            }
        }
        $sql2 = "SELECT meta_value FROM ssybz_usermeta WHERE user_id=$userid AND meta_key='ssybz_capabilities'";
        if ($result = $conn->query($sql2)) {
            foreach ($result as $row2) {
                $pravo = $row2["meta_value"];
            }
        }
        if (empty($name) and empty($last_name)) {
            $name = $username;
        }

        if ($pravo == 'a:1:{s:18:"stm_lms_instructor";b:1;}') {
            $contactType = 2;
        } elseif ($pravo == 'a:1:{s:8:"customer";b:1;}') {
            $contactType = 'CLIENT';
        } else {
            $contactType = 'Underfind';
        }

        $contactinfo = executeREST(
            'crm.contact.list',
            array(
                'order' => array(
                    'DATE_CREATE' => 'DESC',
                ),
                'filter' => array(
                    'EMAIL' => $username,
                ),
                'select' => array(
                    'ID', 'UF_CRM_1640787645',
                ),
            ),
            $domain, $auth, $user);

        $findContact = $contactinfo['result'][0]['ID'];
        $yesDeal = $contactinfo['result'][0]['UF_CRM_1640787645'];
        if (!empty($findContact)) {

            $updateContact = executeREST(
                'crm.contact.update',
                array(
                    'ID' => $findContact,
                    'FIELDS' => array(
                        'TYPE_ID' => $contactType,
                    ),
                    'PARAMS' => array(
                        'REGISTER_SONET_EVENT' => "N",
                    ),
                ),
                $domain, $auth, $user);

        } else {

            $contactadd = executeREST(
                'crm.contact.add',
                array(
                    'fields' => array(
                        'NAME' => $name,
                        'LAST_NAME' => $last_name,
                        'SOURCE_ID' => 81,
                        'ASSIGNED_BY_ID' => 3732,
                        'EMAIL' => array(array("VALUE" => $username, "VALUE_TYPE" => "OTHER")),
                        'TYPE_ID' => $contactType,
                    ),
                    'filter' => array(
                        'REGISTER_SONET_EVENT' => 'Y',
                    ),
                ),
                $domain, $auth, $user);

            $findContact = $contactadd['result'];
        }
        if ($yesDeal != 1) {
            $dealadd = executeREST(
                'crm.deal.add',
                array(
                    'fields' => array(
                        'TITLE' => 'Регистрация клиента ' . $name . ' ' . $last_name,
                        'STAGE_ID' => 'C46:NEW',
                        'CONTACT_ID' => $findContact,
                        'OPENED' => 'Y',
                        'ASSIGNED_BY_ID' => 3732,
                        'CATEGORY_ID' => 46,
                        'SOURCE_ID' => 81,
                    ),
                    'filter' => array(
                        'REGISTER_SONET_EVENT' => 'Y',
                    ),
                ),
                $domain, $auth, $user);
        }
    }
}

//$result->free();
mysqli_close($conn);

function executeREST($method, array $params, $domain, $auth, $user)
{
    $queryUrl = 'https://' . $domain . '/rest/' . $user . '/' . $auth . '/' . $method . '.json';
    $queryData = http_build_query($params);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $queryUrl,
        CURLOPT_POSTFIELDS => $queryData,
    ));
    return json_decode(curl_exec($curl), true);
    curl_close($curl);
}

function writeToLog($data, $title = '')
{
    $log = "\n------------------------\n";
    $log .= date("Y.m.d G:i:s") . "\n";
    $log .= (strlen($title) > 0 ? $title : 'DEBUG') . "\n";
    $log .= print_r($data, 1);
    $log .= "\n------------------------\n";
    file_put_contents(getcwd() . '/useradd.log', $log, FILE_APPEND);
    return true;
}
