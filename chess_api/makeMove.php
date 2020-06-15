<?php

require_once('includes/initialize.php');

header('Content-Type: application/json; charset=UTF-8');

$response = array();

if (isset($_POST['from_coordinates']) && isset($_POST['to_coordinates'])) {
    $from_coordinates = $_POST['from_coordinates'];
    $to_coordinates = $_POST['to_coordinates'];

    $result = $api->makeMove($from_coordinates, $to_coordinates);

    if ($result) {
        $response['message'] = 'The move was made successfully!';
    } else {
        $response['message'] = $api->getErrorMessage();
     }

    echo json_encode($response);
} else {
    $response['message'] = 'Required parameters are missing!';

    echo json_encode($response);
}
?>