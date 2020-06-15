<?php

require_once('includes/initialize.php');

header('Content-Type: application/json; charset=UTF-8');

$response = array();
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $game_status = $api->getGameStatus();

    switch($game_status) {
        case IN_GAME:
            $response['game_status'] = 'Game in process';
            break;
        case BLACK_WINS:
            $response['game_status'] = 'Black wins';
            break;
        case WHITE_WINS:
            $response['game_status'] = 'White wins';
            break;
        case CHECK_MATE_WHITE:
            $response['game_status'] = 'White wins with checkmate';
            break;
        case CHECK_MATE_BLACK:
            $response['game_status'] = 'Black wins with checkmate';
            break;
        default:
            $response['game_status'] = "Can't get game status!";
            break;
    }

    echo json_encode($response);
}

?>