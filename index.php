<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");

define("CLIENT_ID", "");
define("CLIENT_SECRET", "");

$key = false;
if (file_exists(__DIR__ . '/auth.env')) {
    $key = json_decode(file_get_contents(__DIR__ . '/auth.env'));
}

$generate_token = false;
if ($key) {
    $ch = curl_init('https://id.twitch.tv/oauth2/validate');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $key->access_token
    ));

    $r = curl_exec($ch);
    $i = curl_getinfo($ch);
    curl_close($ch);

    if ($i['http_code'] == 200) {
        $generate_token = false;

        $data = json_decode($r);
        if (json_last_error() == JSON_ERROR_NONE) {
            if ($data->expires_in < 3600) {
                $generate_token = true;
            }
        } else {
            $generate_token = true;
        }
    }
}

if ($generate_token) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_setopt($ch, CURLOPT_URL, 'https://id.twitch.tv/oauth2/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'client_id=' . CLIENT_ID . '&client_secret=' . CLIENT_SECRET . '&grant_type=client_credentials');
    
    $headers = array();
    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $r = curl_exec($ch);
    $i = curl_getinfo($ch);

    curl_close($ch);

    if ($i['http_code'] == 200) {
        $key = json_decode($r);
        if (json_last_error() == JSON_ERROR_NONE) {
            file_put_contents(__DIR__ . '/auth.env', $r);
        }
    }
}

if($_SERVER["REQUEST_METHOD"]=="GET")
{
    
}
else if ($_SERVER["REQUEST_METHOD"]=="POST")
{
    
}
else
{
    http_response_code(405);
}

if ($_SERVER["PATH_INFO"]=="/get-user")
{
    $q = $_SERVER['QUERY_STRING'];
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_setopt($ch, CURLOPT_URL, 'https://api.twitch.tv/helix/users?id=' . $q);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    $headers = array();
    $headers[] = 'Authorization: Bearer ' . $key->access_token;
    $headers[] = 'Client-Id: ' . CLIENT_ID;
    $headers[] = 'Content-Type: application/json; charset=utf-8';
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $r = curl_exec($ch);
    $json = json_encode($r);
    echo $json;

    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
}

if ($_SERVER["PATH_INFO"]=="/get-stream")
{
    $q = $_SERVER['QUERY_STRING'];
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_setopt($ch, CURLOPT_URL, 'https://api.twitch.tv/helix/streams?user_id=' . $q);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    $headers = array();
    $headers[] = 'Authorization: Bearer ' . $key->access_token;
    $headers[] = 'Client-Id: ' . CLIENT_ID;
    $headers[] = 'Content-Type: application/json; charset=utf-8';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $r = curl_exec($ch);
    $json = json_encode($r);
    echo $json;

    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
}

if ($_SERVER["PATH_INFO"]=="/help")
{
    echo '<center>
    <h1 style="margin-top: 70px; margin-bottom: 40px">REST API for returning live Twitch data</h1>
    <h2>Querying the API, example</h2>
    <p>https://qsdlr.org/twitch-rest-api/get-user?31239503</p>

    <h3 style="margin-top: 40px">Endpoints</h3>
    <table width="300">
        <tr>
            <th align="left">Endpoint</th>
            <th align="left">Description</th>
        </tr>
        <tr>
            <td><p>/get-user?<b>user-id</b></p></td>
            <td><p>Returns user data</p></td>
        </tr>
        <tr>
            <td><p>/get-stream?<b>user-id</b></p></td>
            <td><p>Returns stream data</p></td>
        </tr>
        <tr>
            <td>/help</td>
            <td><p>Documentation</p></td>
        </tr>
    </table>

    <h3 style="margin-top: 40px">Few user IDs to query</h3>
    <table width="300">
        <tr>
            <th align="left">User ID</th>
            <th align="left">Description</th>
        </tr>
        <tr>
            <td><p>31239503</p></td>
            <td><p>ESL, CS:GO Broadcaster</p></td>
        </tr>
        <tr>
            <td>19070311</td>
            <td><p>Seagull, Overwatch 2 player</p></td>
        </tr>
        <tr>
            <td><p>37402112</p></td>
            <td><p>Shroud, Valorant player</p></td>
        </tr>
    </table>
    </center>';
}

?>