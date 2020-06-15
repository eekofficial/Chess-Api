<?php

define('BASE_DIR', __DIR__);
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'chess');
define('DB_SERVER', 'localhost');
define('SEPARATORS', array('grid'=>'/', 'line'=>';', 'cell'=>','));
define('EMPTY_CELL', 'e');
define('BLACK_COLOR', 'b');
define('WHITE_COLOR', 'w');
define('KING', 'K');
define('QUEEN', 'Q');
define('ROOK', 'R');
define('KNIGHT', 'N');
define('BISHOP', 'B');
define('PAWN', 'p');
define('BLACK_WINS', 'black_wins');
define('WHITE_WINS', 'white_wins');
define('CHECK_MATE_WHITE', 'check_mate_white');
define('CHECK_MATE_BLACK', 'check_mate_white');
define('IN_GAME', 'in_game');
define('OPPOSITE_COLOR', array(BLACK_COLOR=>WHITE_COLOR, WHITE_COLOR=>BLACK_COLOR));
?>