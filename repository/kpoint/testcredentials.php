<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('classes/kpointapi.php');

$clientId = $secret = $domain = $accountNo = $userAccountnoField =  $isAuthViaAccountno = $enableUserID = '';

if(isset($_REQUEST['client_id'])) {
    $clientId = $_REQUEST['client_id'];
}
if(isset($_REQUEST['secret'])) {
    $secret = $_REQUEST['secret'];
}
if(isset($_REQUEST['domain'])) {
    $domain = $_REQUEST['domain'];
}
if(isset($_REQUEST['email'])) {
    $email = $_REQUEST['email'];
}
if(isset($_REQUEST['display_name'])) {
    $displayName = $_REQUEST['display_name'];
}
if(isset($_REQUEST['auth_via_accountno']) && $_REQUEST['auth_via_accountno'] == '1' && isset($_REQUEST['account_no'])) {
    $accountNo = $_REQUEST['account_no'];
}
if(isset($_REQUEST['auth_via_accountno'])) {
    $isAuthViaAccountno = $_REQUEST['auth_via_accountno'];
}
if(isset($_REQUEST['enable_userid'])) {
    $enableUserID = $_REQUEST['enable_userid'];
}

$objKp   = new \repository_kpoint\kpointapi($clientId, $secret, $domain, $isAuthViaAccountno, $accountNo,$enableUserID);
$arrData = $objKp->get_data($email, $displayName, 'video', 0, 1);

$responseData        = json_decode($arrData['response']);
$response            = NULL;
$response['isValid'] = false;
$response['isError'] = false;
$response['error_msg'] = '';

if($arrData['error'] === true) {
    $response['isError'] = true;
    $response['error_msg'] = $arrData['error_message'];
}

else if($arrData['error'] === false && $responseData->totalcount) {
    $response['isValid'] = true;
}

echo json_encode($response);
