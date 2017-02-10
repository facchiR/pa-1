<?php 
	//REF: http://www.redbeanphp.com/index.php
	
	/* 
		Richiamiamo obbligatoriamente la libreria di redbean, salvata nella sottocartella lib. La funzione require_once impedisce che la stessa porzione di codice venga inclusa più volte per errore. 
	*/
	require_once('lib/rb.php');
	
	/*	
		Setup di Redbean e della connessione al database (PDO). 
		I parametri sono dati da: 
		1) una stringa (obbligatoria) contenente le seguenti informazioni: 
			- tipologiadb (es: mysql), seguita da ":"
			- ip o nome DNS del database server (es.: 127.0.0.1, introdotto da "host=" e seguito da ";")
			- nome del database (introdotto da "dbname=")
		2) una seconda stringa con l'username autorizzato ad accedere al db (es.: 'utente')	
		3) una terza stringa con la password dell'username di cui sopra
	*/
	R::setup('mysql:host=127.0.0.1;dbname=a','pa', 'pressione');
	
	/*
		Abbiamo bisogno di sapere quale pagina visualizzare tra quelle disponibili (routing). Per farlo dobbiamo esaminare la richiesta effettuata dall'utente, che può essere effettuata tramite parametri dell'URL (array associativo superglobale $_GET) oppure tramite l'invio di un modulo (array associativo superglobale $_POST). Per rispondere correttamente in entrambi i casi ci riferiamo all'array associativo superglobale $_REQUEST, che li raggruppa entrambi. 
		
		Inizializziamo quindi una variabile $pg che dovrà sempre contenere un valore, dato dal valore del parametro 'p' specificato nell'URL (nella forma ad es. '?p=nomepagina') o in un modulo (ad esempio con un campo '<input name="p" type="hidden" value="nomepagina" />'). Nel caso in cui l'utente non effettui una specifica richiesta la variabile verrà inizializzata con il valore 'home'. 
		
		Ricorriamo, per questo, all'operatore ternario condizionale. 
		
	*/
	$pg=(empty($_REQUEST['p'])) ? 'home' : $_REQUEST['p'];
	
	/* 
		Aggiungiamo alla variabile pg un prefisso dato da un percorso ed un suffisso dato dall'estensione '.php' 
	*/
	$pg='pgs/'.$pg.'.php';
	
	/* 
		Controlliamo se la pagina richiesta dall'utente esiste o meno. Nel caso in cui non esista includiamo una pagina di errore specifica.

	*/	
	
	if (!file_exists($pg)) $pg='pgs/404.php';
	
	/* Qui inizia la parte di presentazione */
?>
<!doctype html>
<html lang="it">
	<head>
		<meta charset="utf8" />
		<title>Pressione Arteriosa</title>
		<link rel="stylesheet" href="https://unpkg.com/purecss@0.6.2/build/pure-min.css" integrity="sha384-UQiGfs9ICog+LwheBSRCt1o5cbyKIHbwjWscjemyBMT9YCUMZffs6UqUTd0hObXD" crossorigin="anonymous">		
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.13/css/jquery.dataTables.min.css" />
		<style>
			body{font:12px 'Tahoma','Verdana','Arial',sens-serif;padding:.75em;margin:0 auto}
			tfoot td{background:royalblue!important;color:white}
		</style>
		<script
		  src="https://code.jquery.com/jquery-3.1.1.min.js"
		  integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
		  crossorigin="anonymous">
		</script>
		<script src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
	</head>
	<body>
		<?php 
			/* 
				Controlliamo se la pagina richiesta dall'utente esiste o meno. Se esiste, la includiamo. Mentre require (e require_one) interrompono l'esecuzione dello script nel caso in cui il file da includere non esista, include (ed include_once) non bloccano l'elaborazione. 

			*/ 
		?>
		<?php if (file_exists($pg)) include($pg)?>
	</body>
</html>