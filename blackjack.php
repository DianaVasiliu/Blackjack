<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style type="text/css">
    body {
        background: radial-gradient(#017306, #013206);
        background-repeat: no-repeat;
        min-height: 100vh;
        margin: 0;
        padding: 0;
        text-align: center;
        color: white;
    }
    input {
        width: auto;
        padding: 1rem;
        background-color: white;
    }
    </style>
</head>

<body>
<br />
<form action="./blackjack.php" method="POST">
    <input type="submit" name="new_card" value="New card"></input>

    <input type="submit" name="stop" value="Stop"></input>

    <input type="submit" name="new_game" value="New game"></input>
</form>

</body>
</html>


<?php
    session_start();

    // if pressing the 'New card' button, then i am a player
    if(isset($_POST['new_card']) && $_SESSION['status'] == "in_game")
    {
        echo 'You drew a new card<br>';
        get_card("player");

        check_player();        
    }
    // pressed the 'stop' button
    elseif((isset($_POST['stop'])) && ($_SESSION['status'] == "in_game"))
    {
        $_SESSION['status'] = "game_over";

        show_cards('player');

        // calculating the score of the player and the dealer
        $player = countCards($_SESSION['player_cards']);
        $dealer = countCards($_SESSION['dealer_cards']);

        // if the dealer has less than 17 points, then he draws another card
        while($dealer < 17)
        {
            get_card("dealer");
            $dealer = countCards($_SESSION['dealer_cards']);
        }

        show_cards('dealer');
        
        echo 'Calculating the result...<br><br>';
        
        check_player();

        // if the dealer has 21 points, he won
        if($dealer == 21)
        {
            echo "Dealer Blackjack!! You lost...<br>";
        }
        // dealer != 21
        elseif($player == 21)
        {
            echo "Player Blackjack!! Congratulations!!<br>";
        }
        // draw
        elseif ($dealer == $player) {
            echo 'Draw!';
        }
        // the dealer has more points than the player 
        elseif($player < $dealer)
        {
            // under 21, he wins
            // player < dealer < 21
            if($dealer < 21)
            {
                echo "Dealer won<br>";
            }
            // over 21, he loses
            // player < 21 < dealer
            elseif ($dealer > 21)
            {   
                echo "Congratulations! You won!<br>";
            }
        }
        // the player has more points than the dealer
        elseif ($dealer < $player)
        {
            // dealer < player < 21
            if ($player < 21) {
                echo "Congratulations! You won!<br>";
            }
        }

        echo '<br>';
        echo 'Player score: '.$player;
        echo '<br>';
        echo 'Dealer score: '.$dealer;
        echo '<br>';
        
        session_destroy();
    }
    // pressed 'New game' button
    elseif(isset($_POST['new_game']))
    {
        echo 'You started a new game<br>';
        session_unset();
        session_destroy();
        session_start();
        makeGame();
    }
    // creating a new game if it's the first one
    else 
    {
        echo 'Starting a new game<br>';
        makeGame();
    }

function makeGame()
{
    session_unset();
    session_destroy();
    session_start();
    
    $_SESSION['status'] = "in_game";

    // creating the cards
    $_SESSION['deck'] = array(
    "2-C", "3-C", "4-C", "5-C", "6-C", "7-C", "8-C", "9-C", "10-C", "J-C", "Q-C", "K-C", "A-C", 
    "2-D", "3-D", "4-D", "5-D", "6-D", "7-D", "8-D", "9-D", "10-D", "J-D", "Q-D", "K-D", "A-D", 
    "2-H", "3-H", "4-H", "5-H", "6-H", "7-H", "8-H", "9-H", "10-H", "J-H", "Q-H", "K-H", "A-H", 
    "2-S", "3-S", "4-S", "5-S", "6-S", "7-S", "8-S", "9-S", "10-S", "J-S", "Q-S", "K-S", "A-S");

    // shuffle the card deck
    shuffle($_SESSION['deck']);	

    
    $count = 0;

    // impart cartile la playeri
    // 
    // each player gets 2 cards at the beginning
    for($x = 0; $x < 2; $x++)
    {
        // giving a single card each time to each player
        $_SESSION['player'][] = $_SESSION['deck'][$count];         
        $_SESSION['dealer'][] = $_SESSION['deck'][($count + 1)];

        // removing the 2 cards
        $count = $count + 2;
    }

    // player's cards
    for($x = 0; $x < 2; $x++)
    {
        $temp = explode('-', $_SESSION['player'][$x]);
        $_SESSION['player_cards'][] = $temp[0];
        $_SESSION['player_suits'][] = $temp[1];
    }

    // dealer's cards
    for($x = 0; $x < 2; $x++)
    {
        $temp = explode('-', $_SESSION['dealer'][$x]);
        $_SESSION['dealer_cards'][] = $temp[0];
        $_SESSION['dealer_suits'][] = $temp[1];
    }

    show_cards('player'); 
    echo '<br><br>';
    show_cards('dealer');

    $player = countCards($_SESSION['player_cards']);
    $dealer = countCards($_SESSION['dealer_cards']);
    
    // checking if the dealer has already got blackjack
    if($dealer == 21)
    {
        echo "Dealer won! Lucky him... First hand was blackjack!<br>";
        $_SESSION['status'] = "game_over";
        show_cards('dealer');
               
        echo '<br>';
        echo 'Player score: '.$player;
        echo '<br>';
        echo 'Dealer score: '.$dealer;        
        echo '<br>';
    }
    else
    {
        // the number of cards in the game
        $_SESSION['card_count'] = 4;
    }
    // checking if the player has <= 21
    check_player();
}

function check_player() {
    $player = countCards($_SESSION['player_cards']);
    $dealer = countCards($_SESSION['dealer_cards']);
    
    // checking if the player lost at the beginning of the game or when drawing a new card
    if($player > 21)
    {
        echo "You lost. Dealer wins.<br>";
        $_SESSION['status'] = "game_over";

        show_cards('dealer');

        echo '<br>';
        echo 'Player score: '.$player;
        echo '<br>';
        echo 'Dealer score: '.$dealer;
        echo '<br>';

        session_unset();
        session_destroy();
    }
    else {
        echo 'Your current score is: '.$player.'.<br>';
    }
}

function countCards($cards)
{
    // giving numerical value to the J-K cards
	for($x = 0; $x < count($cards); $x++)
	{
		switch ($cards[$x])
		{
			case "J":
				$cards[$x] = 10;
				break;
			case "Q":
				$cards[$x] = 10;
				break;
			case "K":
				$cards[$x] = 10;
				break;
        } 
	}
    
    // score without Aces
    $count = 0;
    // Aces array
    $queue = array();

    // calculating the score
	for($x = 0; $x < count($cards); $x++)
	{
        // if it's not an Ace
		if(is_numeric($cards[$x]))
		{
			$count = $count + $cards[$x];
        }
        // if it's an Ace, we are adding it into a queue - we will add its value at the end
		else
		{
			array_push($queue, $cards[$x]);
		}
    }
    
	// if we had any Ace
	if(count($queue) > 0)
	{
		// one Ace
		if(count($queue) == 1)
		{
            // the Ace is 11
			if($count <= 10)
			{
                $count = $count +  11;
            }
            // the Ace is 1
            else
			{
                $count= $count + 1;
			}
        }
        // more Aces
		else
		{
            $no_of_aces = count($queue);
			for($x = 0; $x < count($queue); $x++)
			{
                // if all the aces are 1
                // and they fit into <= 10
                // then at least one ace can be 11
				if($count + $no_of_aces <= 10)
				{
                    $count = $count + 11;
                    $no_of_aces--;
                }
                // if no 11 can fit into the sum
                // then the Ace is 1
				else
				{
                    $count = $count + 1;
                    $no_of_aces--;
				}
			}
		}
	}
	
	return $count;
}


function get_card($who)
{
	$temp = explode('-', $_SESSION['deck'][$_SESSION['card_count']]);
	$_SESSION[$who . "_cards"][count($_SESSION[$who . "_cards"])] = $temp[0];
	$_SESSION[$who . "_suits"][count($_SESSION[$who . "_suits"])] = $temp[1];
    $_SESSION['card_count']++;
    
    if ($who == 'player') {
        show_cards('player'); 
        echo '<br>';
        show_cards('dealer');
    }
}

function show_cards($whose) {
    echo ucfirst($whose).' cards:<br>';
    if ($whose == 'player') {
        for ($i = 0; $i < count($_SESSION[$whose.'_cards']); $i++) {
            echo '<img src="./cards/'.$_SESSION[$whose.'_cards'][$i].$_SESSION[$whose.'_suits'][$i].'.png" width="80px">';
        }
    }
    elseif ($whose == 'dealer' && $_SESSION['status'] == "game_over") {
        for ($i = 0; $i < count($_SESSION[$whose.'_cards']); $i++) {
            echo '<img src="./cards/'.$_SESSION[$whose.'_cards'][$i].$_SESSION[$whose.'_suits'][$i].'.png" width="80px">';
        }
    }
    else {
        for ($i = 0; $i < count($_SESSION[$whose.'_cards']); $i++) {
            echo '<img src="./cards/red_back.png" width="80px">';
        }
    }
    echo '<br><br>';
}

?>
