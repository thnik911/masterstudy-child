<?php
ini_set("display_errors","1");
ini_set("display_startup_errors","1");
ini_set('error_reporting', E_ALL);
writetolog($_REQUEST, 'new request');

//AUTH 
require_once('auth.php');

$deal1 = $_REQUEST['deal1'];
$cnt = $_REQUEST['cnt'];


        $deallist = executeREST(
                'crm.deal.list',
                array(
                        'order' => array (
                            'ID' => 'DESC',
                        ),
                        'filter' => array (
                            'CLOSED' => 'N',
                            'CATEGORY_ID' => 27,
                            'STAGE_ID' => 'C27:13',
                            'CONTACT_ID' => $cnt,
            
                        ),
                        'select' => array (
                            "ID", "ASSIGNED_BY_ID",
                        ),
                        'start' => $startdeal,
                    ),
                $domain, $auth, $user);
                $deal2 = $deallist['result'][0]['ID'];
                $assing = $deallist['result'][0]['ASSIGNED_BY_ID'];

        if(!empty($deal2)){
            $updatedeal = executeREST(
                'crm.deal.update',
                array(
                        'ID' => $deal2,	
                        'FIELDS' => array (
                            'STAGE_ID' => 'C27:WON',
                            ),
                        'PARAMS' => array (
                            'REGISTER_SONET_EVENT' => "N",
                            ),
                        ),
            $domain, $auth, $user);

            $merge = 'По сделке https://test.bitrix24.ru/crm/deal/details/' . $deal2 . '/ поступила оплата за курс';

            $notify = executeREST(
                'im.notify.personal.add',
                array(
                'USER_ID' => $assing,
                'MESSAGE' => $merge,
                ),
            $domain, $auth, $user);

            $merge2 = 'https://test.bitrix24.ru/crm/deal/details/' . $deal1 . '/';

            $taskadd = executeREST(
                'tasks.task.add',
                array(
                            
                        'fields' => array (
                            'TITLE' => 'Была совершена покупка на сайте XXX',
                            'DESCRIPTION' => 'Ссылка на сделку с покупкой: ' . $merge2,
                            'CREATED_BY' => $assing,
                            'RESPONSIBLE_ID' => $assing,
                            'UF_CRM_TASK' => array('D_' . $deal2),
                            ),
                        ),
            $domain, $auth, $user);
        }else{
            $merge2 = 'https://test.bitrix24.ru/crm/deal/details/' . $deal1 . '/';
            $taskadd = executeREST(
                'tasks.task.add',
                array(
                            
                        'fields' => array (
                            'TITLE' => 'Была совершена покупка на сайте XXX. Не найдена сделка в Ваши клиенты',
                            'DESCRIPTION' => 'Ссылка на сделку с покупкой: ' . $merge2,
                            'CREATED_BY' => 3732,
                            'RESPONSIBLE_ID' => 3732,
                            'GROUP_ID' => 100,
                            ),
                        ),
            $domain, $auth, $user);
        }

function executeREST ($method, array $params, $domain, $auth, $user) {
    $queryUrl = 'https://'.$domain.'/rest/'.$user.'/'.$auth.'/'.$method.'.json';
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

function writeToLog($data, $title = '') {
$log = "\n------------------------\n";
$log .= date("Y.m.d G:i:s") . "\n";
$log .= (strlen($title) > 0 ? $title : 'DEBUG') . "\n";
$log .= print_r($data, 1);
$log .= "\n------------------------\n";
file_put_contents(getcwd() . '/logs/findDeal.log', $log, FILE_APPEND);
return true;
}

?>