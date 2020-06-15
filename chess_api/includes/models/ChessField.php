<?php

class ChessField
{
    private $field_array;
    private $field_error;
    private $game_info;
    private $checkmate = false;

    public function makeMove($from_coordinates, $to_coordinates) {
        if (!$this->checkGameStatus()) {
            return false;
        }

        if (!$this->validateCoordinates($from_coordinates, $to_coordinates)) {
            return false;
        }

        list($from_x, $from_y) = $this->transformCoordinates($from_coordinates);
        list($to_x, $to_y) = $this->transformCoordinates($to_coordinates);

        $this->field_array = $this->toFieldArray($this->game_info->chess_field);

        if (!$this->validateColor($from_x, $from_y)) {
            return false;
        }

        if (!$this->checkEmptyCell($from_x, $from_y)) {
            return false;
        }

        if (!$this->checkMoveByRules($from_x, $from_y, $to_x, $to_y)) {
            return false;
        }

        if (!$this->checkDestinationCell($from_x, $from_y, $to_x, $to_y)) {
            return false;
        }

        if ($this->field_array[$to_x][$to_y][0] == KING) {
            $this->checkmate = true;
        }
        $this->field_array[$to_x][$to_y][0] = $this->field_array[$from_x][$from_y][0];
        $this->field_array[$to_x][$to_y][1] = $this->field_array[$from_x][$from_y][1];
        $this->field_array[$from_x][$from_y][0] = EMPTY_CELL;
        $this->field_array[$from_x][$from_y][1] = EMPTY_CELL;
        $this->game_info->next_color = OPPOSITE_COLOR[$this->game_info->next_color];

        if ($this->countFigures(WHITE_COLOR) == 0) {
            $this->game_info->game_status = BLACK_WINS;
            return true;
        }

        if ($this->countFigures(BLACK_COLOR) == 0) {
            $this->game_info->game_status = WHITE_WINS;
            return true;
        }

        if ($this->checkmate) {
            if ($this->game_info->next_color == WHITE_COLOR) {
                $this->game_info->game_status = CHECK_MATE_BLACK;
            } else {
                $this->game_info->game_status = CHECK_MATE_WHITE;
            }
        }

        $this->game_info->chess_field = $this->toFieldString($this->field_array);

        return true;

    }

    public function getErrorMessage() {
        return $this->field_error;
    }

    public function getGameInfo() {
        return $this->game_info;
    }

    public function setGameInfo($game_info)
    {
        $this->game_info = $game_info;
    }

    public function getGameStatus()
    {
        return $this->game_info->game_status;
    }

    private function countFigures($color) {
        $count = 0;
        for ($i = 0; $i < count($this->field_array); $i++) {
            for ($j = 0; $j < count($this->field_array[$i]); $j++) {
                if ($this->field_array[$i][$j][1] == $color) {
                    $count += 1;
                }
            }
        }
        return $count;
    }

    private function checkGameStatus() {
        if ($this->game_info->game_status != IN_GAME) {
            $this->field_error = 'Your game was ended. Please, start new game';
            return false;
        }

        return true;
    }

    private function toFieldString($field_array) {
        for ($i = 0; $i < count($field_array); $i++) {
            for ($j = 0; $j < count($field_array[$i]); $j++) {
                $field_array[$i][$j] = implode(SEPARATORS['cell'], $field_array[$i][$j]);
            }
        }

        for ($i = 0; $i < count($field_array); $i++) {
            $field_array[$i] = implode(SEPARATORS['line'], $field_array[$i]);
        }

        $field_string = implode(SEPARATORS['grid'], $field_array);

        return $field_string;

    }

    private function toFieldArray($field_string) {
        $field_array = explode(SEPARATORS['grid'], $field_string);

        for ($i = 0; $i < count($field_array); $i++) {
            $field_array[$i] = explode(SEPARATORS['line'], $field_array[$i]);
        }

        for ($i = 0; $i < count($field_array); $i++) {
            for ($j = 0; $j < count($field_array[$i]); $j++) {
                $field_array[$i][$j] = explode(SEPARATORS['cell'], $field_array[$i][$j]);
            }
        }

        return $field_array;
    }

    private function transformCoordinates($coordinates) {
        $coordinates = explode(' ', (strtolower($coordinates)));
        $x = $coordinates[1];
        $y = $coordinates[0];
        return array(7 - (intval($x) - 1), ord($y) - ord('a'));

    }

    private function checkEmptyCell($from_x, $from_y) {
        if ($this->field_array[$from_x][$from_y][0] == EMPTY_CELL) {
            $this->field_error = 'You are trying to make move with empty cell';
            return false;
        }

        return true;
    }

    private function checkDestinationCell($from_x, $from_y, $to_x, $to_y) {
        if ($this->game_info->next_color == $this->field_array[$to_x][$to_y][1]) {
            $this->field_error = 'You are trying to move your figure on cell that occupied by yourself';
            return false;
        }

        if (($to_y == $from_y) && $this->field_array[$from_x][$from_y][0] == PAWN && $this->field_array[$to_x][$to_y][0] != EMPTY_CELL) {
            $this->field_error = 'You are trying to eat not correctly with your pawn';
            return false;
        }

        if (abs($to_y - $from_y) == 1 && $this->field_array[$from_x][$from_y][0] == PAWN && $this->field_array[$to_x][$to_y][0] == EMPTY_CELL) {
            $this->field_error = 'You are trying to eat empty cell with your pawn';
            return false;
        }

        return true;
    }

    private function checkMoveByRules($from_x, $from_y, $to_x, $to_y) {
        switch ($this->game_info->next_color) {
            case WHITE_COLOR:
                switch ($this->field_array[$from_x][$from_y][0]) {
                    case PAWN:
                        return $this->checkRuleForWhitePawn($from_x, $from_y, $to_x, $to_y);
                        break;
                }
                break;
            case BLACK_COLOR:
                switch ($this->field_array[$from_x][$from_y][0]) {
                    case PAWN:
                        return $this->checkRuleForBlackPawn($from_x, $from_y, $to_x, $to_y);
                        break;
                }
                break;
        }

        switch ($this->field_array[$from_x][$from_y][0]) {
            case KING:
                return $this->checkRuleForKing($from_x, $from_y, $to_x, $to_y);
                break;
            case QUEEN:
                return $this->checkRuleForQueen($from_x, $from_y, $to_x, $to_y);
                break;
            case ROOK:
                return $this->checkRuleForRook($from_x, $from_y, $to_x, $to_y);
                break;
            case KNIGHT:
                return $this->checkRuleForKnight($from_x, $from_y, $to_x, $to_y);
                break;
            case BISHOP:
                return $this->checkRuleForBishop($from_x, $from_y, $to_x, $to_y);
                break;
        }
        return false;
    }

    private function checkRuleForKing($from_x, $from_y, $to_x, $to_y) {
        if ($from_y == $to_y && $from_x == $from_y) {
            $this->field_error = 'You are trying to move your king on the same place';
            return false;
        }

        if (abs($to_y - $from_y) > 1 || abs($to_x - $from_x) > 1) {
            $this->field_error = 'You are trying to move your king on more than one cell';
            return false;
        }

        return true;
    }

    private function checkRuleForKnight($from_x, $from_y, $to_x, $to_y) {
        if (abs($to_y - $from_y) == 1 && abs($to_x - $from_x) == 2) {
            return true;
        }

        if (abs($to_y - $from_y) == 2 && abs($to_x - $from_x) == 1) {
            return true;
        }

        $this->field_error = 'You are trying to move your knight not correctly';
        return false;
    }

    private function checkRuleForBishop($from_x, $from_y, $to_x, $to_y) {
        if ($from_y == $to_y && $from_x == $from_y) {
            $this->field_error = 'You are trying to move your bishop on the same place';
            return false;
        }

        if (abs($to_y - $from_y) != abs($to_x - $from_x)) {
            $this->field_error = 'You can move your bishop only on diagonals';
            return false;
        }

        $direction_x = $to_x - $from_x;
        $direction_y = $to_y - $from_y;

        while ($from_x != $to_x && $from_y != $to_y) {
            if ($direction_x > 0 && $direction_y > 0) {
                $from_x += 1;
                $from_y += 1;
            } else if ($direction_x < 0 && $direction_y < 0) {
                $from_x -= 1;
                $from_y -= 1;
            } else if ($direction_x > 0 && $direction_y < 0) {
                $from_x += 1;
                $from_y -= 1;
            } else if ($direction_x < 0 && $direction_y > 0) {
                $from_x -= 1;
                $from_y += 1;
            }

            if ($from_x == $to_x && $from_y == $to_y &&
                $this->field_array[$from_x][$from_y][1] == OPPOSITE_COLOR[$this->game_info->next_color]) {
                return true;
            }

            if ($this->field_array[$from_x][$from_y][0] != EMPTY_CELL) {
                $this->field_error = 'You faced with another figure on your path';
                return false;
            }

        }

        return true;
    }

    private function checkRuleForWhitePawn($from_x, $from_y, $to_x, $to_y) {
        if ($from_x == $to_x) {
            $this->field_error = 'You are trying to move your pawn on the same line';
            return false;
        }

        if (abs($to_y - $from_y) > 1) {
            $this->field_error = "You can't move your pawn left or right on more than one cell";
            return false;
        }

        if ($to_x > $from_x) {
            $this->field_error = "You can't move down";
            return false;
        }

        if ($from_x == 6) {
            if ($to_x != 5 and $to_x != 4) {
                $this->field_error = "You can't move your pawn on more than 1 or 2 cell up from that position";
                return false;
            }
            return true;
        }

        if (abs($to_x - $from_x) > 1) {
            $this->field_error = "You can't move your pawn on more than 1 cell up from that position";
            return false;
        }

        return true;
    }

    private function checkRuleForQueen($from_x, $from_y, $to_x, $to_y) {
        if ($from_y == $to_y && $from_x == $from_y) {
            $this->field_error = 'You are trying to move your queen on the same place';
            return false;
        }

        if ($to_y != $from_y && $to_x != $from_x && abs($to_y - $from_y) != abs($to_x - $from_x)) {
            $this->field_error = 'You can move your queen only on horizontal or vertical or diagonal lines';
            return false;
        }

        if ($from_y == $to_y || $from_x == $to_y) {
            $direction_x = $to_x - $from_x;
            $direction_y = $to_y - $from_y;

            while ($from_x != $to_x || $from_y != $to_y) {
                if ($direction_x < 0) {
                    $from_x -= 1;
                } else if ($direction_x > 0) {
                    $from_x += 1;
                } else if ($direction_y > 0) {
                    $from_y += 1;
                } else if ($direction_y < 0) {
                    $from_y -= 1;
                }

                if ($from_x == $to_x && $from_y == $to_y &&
                    $this->field_array[$from_x][$from_y][1] == OPPOSITE_COLOR[$this->game_info->next_color]) {
                    return true;
                }

                if ($this->field_array[$from_x][$from_y][0] != EMPTY_CELL) {
                    $this->field_error = 'You faced with another figure on your path';
                    return false;
                }

            }
        } else {
            $direction_x = $to_x - $from_x;
            $direction_y = $to_y - $from_y;

            while ($from_x != $to_x && $from_y != $to_y) {
                if ($direction_x > 0 && $direction_y > 0) {
                    $from_x += 1;
                    $from_y += 1;
                } else if ($direction_x < 0 && $direction_y < 0) {
                    $from_x -= 1;
                    $from_y -= 1;
                } else if ($direction_x > 0 && $direction_y < 0) {
                    $from_x += 1;
                    $from_y -= 1;
                } else if ($direction_x < 0 && $direction_y > 0) {
                    $from_x -= 1;
                    $from_y += 1;
                }

                if ($from_x == $to_x && $from_y == $to_y &&
                    $this->field_array[$from_x][$from_y][1] == OPPOSITE_COLOR[$this->game_info->next_color]) {
                    return true;
                }

                if ($this->field_array[$from_x][$from_y][0] != EMPTY_CELL) {
                    $this->field_error = 'You faced with another figure on your path';
                    return false;
                }

            }
        }

        return true;
    }

    private function checkRuleForRook($from_x, $from_y, $to_x, $to_y) {
        if ($from_y == $to_y && $from_x == $to_x) {
            $this->field_error = 'You are trying to move your rook on the same place';
            return false;
        }

        if ($from_y != $to_y && $from_x != $to_x) {
            $this->field_error = 'You can move your rook only on vertical or horizonral line';
            return false;
        }

        $direction_x = $to_x - $from_x;
        $direction_y = $to_y - $from_y;

        while ($from_x != $to_x || $from_y != $to_y) {
            if ($direction_x < 0) {
                $from_x -= 1;
            } else if ($direction_x > 0) {
                $from_x += 1;
            } else if ($direction_y > 0) {
                $from_y += 1;
            } else if ($direction_y < 0) {
                $from_y -= 1;
            }

            if ($from_x == $to_x && $from_y == $to_y &&
                $this->field_array[$from_x][$from_y][1] == OPPOSITE_COLOR[$this->game_info->next_color]) {
                return true;
            }

            if ($this->field_array[$from_x][$from_y][0] != EMPTY_CELL) {
                $this->field_error = 'You faced with another figure on your path';
                return false;
            }

        }

        return true;
    }

    private function checkRuleForBlackPawn($from_x, $from_y, $to_x, $to_y) {
        if ($from_x == $to_x) {
            $this->field_error = 'You are trying to move your pawn on the same line';
            return false;
        }

        if (abs($to_y - $from_y) > 1) {
            $this->field_error = "You can't move your pawn left or right on more than one cell";
            return false;
        }

        if ($to_x < $from_x) {
            $this->field_error = "You can't move down";
            return false;
        }

        if ($from_x == 1) {
            if ($to_x != 2 and $to_x != 3) {
                $this->field_error = "You can't move your pawn on more than 1 or 2 cell up from that position";
                return false;
            }
            return true;
        }

        if (abs($to_x - $from_x) > 1) {
            $this->field_error = "You can't move your pawn on more than 1 cell up from that position";
            return false;
        }

        return true;
    }

    private function validateColor($from_x, $from_y) {
        if ($this->field_array[$from_x][$from_y][1] != $this->game_info->next_color) {
            $this->field_error = 'You tried to make move with not your figure';
            return false;
        }

        return true;
    }

    private function validateCoordinates($from_coordinates, $to_coordinates) {
        list($from_x, $from_y) = explode(' ', strtolower($from_coordinates));
        list($to_x, $to_y) = explode(' ', strtolower($to_coordinates));

        if (!($from_x >= 'a' && $from_x <= 'h')) {
            $this->field_error = 'You entered not valid from x coordinate';
            return false;
        }

        if (!($from_y >= 1 && $from_y <= 8)) {
            $this->field_error = 'You entered not valid from y coordinate';
            return false;
        }

        if (!($to_x >= 'a' && $to_x <= 'h')) {
            $this->field_error = 'You entered not valid to x coordinate';
            return false;
        }

        if (!($to_y >= '1' && $to_y <= '8')) {
            $this->field_error = 'You entered not valid to y coordinate';
            return false;
        }

        return true;
    }

    public function setStartGameInfo() {
        $f = fopen(BASE_DIR . '/start_field.txt', 'r');
        $chess_field = fgets($f);
        fclose($f);
        $next_color = WHITE_COLOR;
        $game_status = IN_GAME;
        $game_info = new GameInfo;
        $game_info->chess_field = $chess_field;
        $game_info->game_status = $game_status;
        $game_info->next_color = $next_color;
        $this->game_info = $game_info;
        $this->checkmate = false;
    }

    public function getChessField() {
        $grid = '';
        for ($i = 0; $i < count($this->field_array); $i++) {
            $line = '';
            for ($j = 0; $j < count($this->field_array); $j++) {
                switch ($this->field_array[$i][$j][0]) {
                    case KING:
                        if ($this->field_array[$i][$j][1] == WHITE_COLOR) {
                            $figure = '♔';
                        } else {
                            $figure = '♚';
                        }
                        break;
                    case QUEEN:
                        if ($this->field_array[$i][$j][1] == WHITE_COLOR) {
                            $figure = '♕';
                        } else {
                            $figure = '♛';
                        }
                        break;
                    case ROOK:
                        if ($this->field_array[$i][$j][1] == WHITE_COLOR) {
                            $figure = '♖';
                        } else {
                            $figure = '♜';
                        }
                        break;
                    case KNIGHT:
                        if ($this->field_array[$i][$j][1] == WHITE_COLOR) {
                            $figure = '♘';
                        } else {
                            $figure = '♞';
                        }
                        break;
                    case BISHOP:
                        if ($this->field_array[$i][$j][1] == WHITE_COLOR) {
                            $figure = '♗';
                        } else {
                            $figure = '♝';
                        }
                        break;
                    case PAWN:
                        if ($this->field_array[$i][$j][1] == WHITE_COLOR) {
                            $figure = '♙';
                        } else {
                            $figure = '♟';
                        }
                        break;
                    default:
                        $figure = '☖';

                }
                $line .= $figure . ' ';
            }
            $grid .= $line . PHP_EOL;
        }
        return $grid . PHP_EOL;
    }
}

?>