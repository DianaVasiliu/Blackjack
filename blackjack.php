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
    <input type="submit" name="new_card" value="Cere carte"></input>

    <input type="submit" name="stop" value="Stop"></input>

    <input type="submit" name="new_game" value="Joc Nou"></input>
</form>

</body>
</html>


<?php
    session_start();

    // daca apas pe butonul "Cere carte", inseamna ca sunt player
    if(isset($_POST['new_card']) && $_SESSION['status'] == "in_game")
    {
        echo 'Ai cerut o carte noua<br>';
        get_card("player");

        check_player();        
    }
    // daca am apasat pe butonul "Stop"
    elseif((isset($_POST['stop'])) && ($_SESSION['status'] == "in_game"))
    {
        $_SESSION['status'] = "game_over";

        show_cards('player');

        // calculez numarul de puncte ale fiecaruia dintre player si dealer
        $player = countCards($_SESSION['player_cards']);
        $dealer = countCards($_SESSION['dealer_cards']);    //aici are doar 2 carti

        //daca dealer-ul are sub 17p, mai cere o carte
        while($dealer < 17)
        {
            get_card("dealer");
            $dealer = countCards($_SESSION['dealer_cards']);
        }

        show_cards('dealer');

        
        echo 'Calculam rezultatul....<br><br>';
        
        check_player();

        // daca dealer-ul a luat 21p, a castigat
        if($dealer == 21)
        {
            echo "Dealer Blackjack!! Ai pierdut...<br>";
        }
        // dealer != 21
        elseif($player == 21)
        {
            echo "Player Blackjack!! Felicitari!!<br>";
        }
        // daca e egalitate
        elseif ($dealer == $player) {
            echo 'Remiza!';
        }
        // daca dealer-ul are mai multe puncte decat player-ul
        elseif($player < $dealer)
        {
            // daca are sub 21, castiga
            // player < dealer < 21
            if($dealer < 21)
            {
                echo "Dealer-ul castiga<br>";
            }
            // daca are sub 21, piede
            // player < 21 < dealer
            elseif ($dealer > 21)
            {   
                echo "Felicitari! Ai castigat!<br>";
            }
        }
        // daca player-ul are mai multe puncte decat dealer-ul
        elseif ($dealer < $player)
        {
            // dealer < player < 21
            if ($player < 21) {
                echo "Felicitari! Ai castigat!<br>";
            }
        }

        echo '<br>';
        echo 'Scorul Player-ului: '.$player;
        echo '<br>';
        echo 'Scorul Dealer-ului: '.$dealer;
        echo '<br>';
        
        session_destroy();
    }
    // daca apas pe butonul "Joc nou", creez un joc nou
    elseif(isset($_POST['new_game']))
    {
        echo 'Ai ales un joc nou<br>';
        session_unset();
        session_destroy();
        session_start();
        makeGame();
    }
    // daca nu este in joc, atunci creez un joc nou
    else 
    {
        echo 'Inceput de joc.. creem un joc<br>';
        makeGame();
    }

function makeGame()
{
    session_unset();
    session_destroy();
    session_start();
    
    $_SESSION['status'] = "in_game";

    // creez pachetul de carti
    $_SESSION['deck'] = array(
    "2-C", "3-C", "4-C", "5-C", "6-C", "7-C", "8-C", "9-C", "10-C", "J-C", "Q-C", "K-C", "A-C", 
    "2-D", "3-D", "4-D", "5-D", "6-D", "7-D", "8-D", "9-D", "10-D", "J-D", "Q-D", "K-D", "A-D", 
    "2-H", "3-H", "4-H", "5-H", "6-H", "7-H", "8-H", "9-H", "10-H", "J-H", "Q-H", "K-H", "A-H", 
    "2-S", "3-S", "4-S", "5-S", "6-S", "7-S", "8-S", "9-S", "10-S", "J-S", "Q-S", "K-S", "A-S");

    // amestec pachetul de carti
    shuffle($_SESSION['deck']);	

    
    $count = 0;

    // impart cartile la playeri
    // cate 2 carti la fiecare la inceput de joc
    for($x = 0; $x < 2; $x++)
    {
        // dau pe rand o carte fiecaruia
        $_SESSION['player'][] = $_SESSION['deck'][$count];         
        $_SESSION['dealer'][] = $_SESSION['deck'][($count + 1)];

        // elimin cele 2 carti date
        $count = $count + 2;
    }

    // cartile player-ului
    for($x = 0; $x < 2; $x++)
    {
        $temp = explode('-', $_SESSION['player'][$x]);
        $_SESSION['player_cards'][] = $temp[0];
        $_SESSION['player_suits'][] = $temp[1];
    }

    // cartile dealer-ului
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
    $dealer = countCards($_SESSION['dealer_cards']);    //aici are doar 2 carti
    
    // verific daca dealer-ul a primit carti care dau blackjack
    if($dealer == 21)
    {
        echo "Dealer-ul castiga! A primit Blackjack din prima.. Ce noroc!<br>";
        $_SESSION['status'] = "game_over";
        show_cards('dealer');
               
        echo '<br>';
        echo 'Scorul Player-ului: '.$player;
        echo '<br>';
        echo 'Scorul Dealer-ului: '.$dealer;        
        echo '<br>';
    }
    else
    {
        // numarul de carti in joc
        $_SESSION['card_count'] = 4;
    }
    // verific sa fie player-ul cu <= 21
    check_player();
}

function check_player() {
    $player = countCards($_SESSION['player_cards']);
    $dealer = countCards($_SESSION['dealer_cards']);
    
    // verific daca player-ul a pierdut la inceput de joc sau la tras de carte noua
    if($player > 21)
    {
        echo "Ai pierdut, Dealer-ul castiga.<br>";
        $_SESSION['status'] = "game_over";

        show_cards('dealer');

        echo '<br>';
        echo 'Scorul Player-ului: '.$player;
        echo '<br>';
        echo 'Scorul Dealer-ului: '.$dealer;
        echo '<br>';

        session_unset();
        session_destroy();
    }
    else {
        echo 'Scorul tau curent este: '.$player.'.<br>';
    }
}

function countCards($cards)
{
	// dau valoare numerica cartilor J-K
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
    
    // suma fara asi
    $count = 0;
    // vector de asi
    $queue = array();

	// numar punctele
	for($x = 0; $x < count($cards); $x++)
	{
		// daca nu este As
		if(is_numeric($cards[$x]))
		{
			$count = $count + $cards[$x];
        }
        // daca este As, il bag intr-o coada - se va calcula la final
		else
		{
			array_push($queue, $cards[$x]);
		}
    }
    
	// daca am avut vreun As
	if(count($queue) > 0)
	{
		// un singur As
		if(count($queue) == 1)
		{
			// As-ul este 11
			if($count <= 10)
			{
                $count = $count +  11;
            }
            // As-ul este 1
            else
			{
                $count= $count + 1;
			}
        }
        // mai multi Asi
		else
		{
            $no_of_aces = count($queue);
			for($x = 0; $x < count($queue); $x++)
			{
                // daca tot asii ramasi ar fi = 1
                // si totusi incap toti in <= 10
                // atunci sigur cel putin un as poate fi = 11
				if($count + $no_of_aces <= 10)
				{
                    $count = $count + 11;
                    $no_of_aces--;
                }
                // daca nu mai incape un as = 11 in suma
                // atunci as = 1
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
    echo 'Cartile '.ucfirst($whose).'-ului:<br>';
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
