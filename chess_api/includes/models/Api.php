<?php

class Api {

    private $db;
    private $chess_field;
    public $error;

    public function __construct() {
        $this->db = new Database;
        $this->chess_field = new ChessField;
    }

    public function makeMove($from_coordinates, $to_coordinates) {
        $game_info = $this->getGameInfo();
        $this->chess_field->setGameInfo($game_info);

        if (!($this->chess_field->makeMove($from_coordinates, $to_coordinates))) {
            $this->error = $this->chess_field->getErrorMessage();
            return false;
        }

        $new_game_info = $this->chess_field->getGameInfo();
        $this->modifyGameInfo($new_game_info);

        return true;
    }

    public function getGameStatus() {
        $game_info = $this->getGameInfo();
        $this->chess_field->setGameInfo($game_info);
        $game_status = $this->chess_field->getGameStatus();
        return $game_status;
    }

    public function startNewGame() {
        $this->chess_field->setStartGameInfo();
        $game_info = $this->chess_field->getGameInfo();

        if (!$this->isExistGameInfo()) {
            if (!($this->addGameInfo($game_info))) {
                return false;
            }
        }

        if (!($this->modifyGameInfo($game_info))) {
            return false;
        }

        return true;
    }

    public function getErrorMessage() {
        return $this->error;
    }

    private function isExistGameInfo() {
        $game_info = $this->getGameInfo();

        if ($game_info) {
            return true;
        }
        return false;
    }

    private function getGameInfo() {
        $query = 'SELECT * FROM game';
        $this->db->query($query);
        $game_info = $this->db->single();
        return $game_info;
    }

    private function addGameInfo($game_info) {
        $query = 'INSERT INTO game(chess_field, game_status, next_color) 
                    VALUES(:chess_field, :game_status, :next_color)';
        $this->db->query($query);
        $this->db->bind(':chess_field', $game_info->chess_field);
        $this->db->bind(':game_status', $game_info->game_status);
        $this->db->bind(':next_color', $game_info->next_color);

        if (!($this->db->execute())) {
            $this->error = "Can't add game info";
            return false;
        }

        return true;
    }

    private function modifyGameInfo($game_info) {
        $query = 'UPDATE game SET chess_field = :chess_field, game_status = :game_status, next_color = :next_color';
        $this->db->query($query);
        $this->db->bind(':chess_field', $game_info->chess_field);
        $this->db->bind(':game_status', $game_info->game_status);
        $this->db->bind(':next_color', $game_info->next_color);

        if (!($this->db->execute())) {
            $this->error = "Can't modify game info";
            return false;
        }

        return true;
    }
}

$api = new Api();
?>
