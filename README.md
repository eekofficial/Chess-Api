# Chess-Api
API for playing chess without calculating stalemate situations. Good for experiments with random games.

Available methods:

1) Start new game

URL: <host>/chess_api/startNewGame.php
  
Request-Type: POST

Parameters: -

Response example: 
  {
    "message": "The new game was started!"
  }

2) Get game status

URL: <host>/chess_api/getGameStatus.php
  
Request-Type: GET

Parameters: -

Response example: 
  {
    "game_status": "Game in process"
  }
  
3) Make move

URL: <host>/chess_api/makeMove.php
  
Request-Type: POST

Content-Type: application/json

Parameters: 
  - from_coordinates (eg "a 1")
  - to_coordinates (eg "h 8")
  
Response example: 
  {
    "message": "The move was made successfully!"
  }
