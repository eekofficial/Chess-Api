<?php

require_once('includes/initialize.php');

header('Content-Type: application/json; charset=UTF-8');

$response = array();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($api->startNewGame()) {
        $response['message'] = 'The new game was started!';
        echo json_encode($response);
    } else {
        $response['message'] = $api->getErrorMessage();
        echo json_encode($response);
    }
}
?>


