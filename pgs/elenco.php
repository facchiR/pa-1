<?php
	/* Inizializziamo variabili che ci serviranno in seguito */
	$valid=false; //valida gli input utente
	$msg=null; //eventuali messaggi a video
	$table='pressione'; //tabella del db con la quale interagiamo
	
	/*
		La variabile $record deve contenere sempre un oggetto RedBean, che utilizzeremo nel resto della pagina. 
		Se l'utente ha fatto richiesta di un record specifico carichiamo quel record (R::load), se no creiamo un oggetto vuoto (R::dispense). 
		
		Nel caricare il record specificato dall'utente provvediamo ad accertarci che $_REQUEST['id'] corrisponda ad un numero, recuperando appunto il valore numerico della stringa. 
	*/
	
	$record=(empty($_REQUEST['id'])) ?  R::dispense($table) : R::load($table, 
	intval($_REQUEST['id']));	
	
	/* Effettuiamo eventuali operazioni di manipolazione dei record */
	
	try {
		
		/* 
			ELIMINAZIONE:
			controlliamo se è stata richiesta l'azione di eliminazione e, se sì, eliminiamo il record impostiamo la variabile $msg in modo da notificare all'utente l'esito dell'operazione 
		*/
		
		if ($record && isset($_REQUEST['act']) && $_REQUEST['act']=='del') { 
			$status=R::trash($record);
			$msg= ($status) ? 'Record # '.$record->id.' eliminato correttamente.' : 'Impossibile eliminare il record #'.$record->id;
		}
		

		/* 
			CREAZIONE/SALVATAGGIO:
			controlliamo se è stato inviato un campo obbligatorio del form (datamisurazione) e, in tal caso, provvediamo ad assegnare all'oggetto record tutti i valori impostati nel modulo. 
		*/		
		
		
		if (isset($_POST['datamisurazione'])){
			
			/* 
				Si noti l'uso di $_POST anziché di $_REQUEST, necessario per circoscrivere l'ambito ai soli dati inseriti nel form. 
			*/
			
			foreach ($_POST as $k=>$v){
				$record[$k]=$v;
			}
			
			
			/* 
				Validazioni. Per impostazione di default consideriamo l'input dell'utente sempre NON VALIDO; in questa fase operiamo i necessari controlli per considerarlo, se del caso, valido ed ammettere di conseguenza la scrittura nella tabella del DB. 
				
				La variabile $valid deve rispondere a tutte le condizioni indicate, in sequenza. L'operatore logico && garantisce, in caso contrario, che il valore $valid risulti "false", inibendo così il salvataggio dei dati.
			*/
			
			$valid=$record['diastolica']<$record['sistolica']
				&& strtotime($record['datamisurazione']) 
				&& $record['datamisurazione']<=date('Y-m-d');
			
			/*
				Se i dati sono stati validati vengono salvati nel DB con il metodo R::store. 
				Alla variabile $msg viene quindi assegnata una stringa che informa circa lo stato dell'operazione.
			*/
			
			$msg= ($valid && R::store($record)) ?
				'Dati inseriti correttamente':
				'Dati non validi: '.json_encode($record);
			
			
			/* sintassi alternativa, senza operatore ternario condizionale 
			
			if ($valid){
				R::store($record);
				$msg='Dati inseriti correttamente';
			}else{
				$msg='Dati non validi: '.json_encode($record);
			}
			
			*/
			
		}
	} catch (RedBeanPHP\RedException\SQL $e) {
		
		/* 
			Con catch intercettiamo eventuali errori sollevati da ReBean impedendo che vengano visualizzati a video o registrati nei log e ne sfruttiamo il contenuto descrittivo per mostrare un messaggio di errore all'utente 
		*/
		
		$msg=$e->getMessage();
	}
	
	/*
		Filtriamo i valori per data, se richiesto. 
		Per l'assegnazione di un valore alla variabile 'query' sono state adoperate le virgolette in luogo dei consueti apici perché la stringa SQL richiede internamente l'uso dell'apice singolo come delimitatore della data formattata.
		Si noti l'inizializzazione di Query a '1' per ottenere comunque un risultato valido (WHERE 1 è una condizione che restituisce tutti i valori selezionati). 
		
		I dati recuperati dalla tabella specificata nella variabile $table vengono così memorizzati nella variabile $pa, che conterrà così un oggetto di ReBean
	*/
	$query='1';
	$dal=(empty($_POST['dal'])) ? date('Y-m-d') : $_POST['dal'];
	$al=(empty($_POST['al'])) ? date('Y-m-d') : $_POST['al'];
	
	if (strtotime($dal) && strtotime($al))
		$query="datamisurazione BETWEEN '".$dal."' AND '".$al."' ";
	
	$pa=R::find($table,$query);
		
	
	/* 
		Sfrutto il metodo getRow di ReadBean per assegnare alla variabile $media l'output di una query che restituisce una sola riga e mediante la quale ottengo i valori medi dei dati numerici inseriti. 
	*/
	
	$media=R::getRow('SELECT avg(sistolica) sistolica, avg(diastolica) diastolica, avg(peso) peso FROM pressione WHERE '.$query);
?>
<h2>
	<a href="index.php">
		Misurazioni
	</a>
</h2>
	<p>
		<form action="?p=elenco" method="POST" class="pure-form">
			Dal 
			<input type="date" name="dal" value="<?=$dal?>" max="<?=date('Y-m-d')?>"/>
			&nbsp; al 
			<input type="date" name="al" value="<?=$al?>" max="<?=date('Y-m-d')?>"/>
			<button type="submit">Mostra</button>
		</form>
	</p>
<h4 id="message" class="msg label error">
	<?=$msg?>
</h4>
<form action="?p=elenco" method="POST" onsubmit="validateForm()" class="pure-form">
	<caption>Nuova misurazione:</caption>
	<input 
		type="date" 
		name="datamisurazione" 
		value="<?=date('Y-m-d')?>" 
		max="<?=date('Y-m-d')?>" 
		placeholder="data" 
		required 
	/>
	<input type="number" name="sistolica" placeholder="sistolica" required onchange="limit(this)" />
	<input type="number" name="diastolica" placeholder="diastolica" required />		
	<input type="number" name="peso" placeholder="peso" step="any" />	
	<button type="submit" class="pure-button pure-button-primary">Salva</button>
</form>
<table id="tabmisura" class="pure-table pure-table-striped pure-table-bordered">
	<thead>	
		<tr>
			<th>
				Data misurazione
			</th>
			<th>
				Sistolica
			</th>		
			<th>
				Diastolica
			</th>
			<th>
				Peso
			</th>
			<th>
				&nbsp;
			</th>			
		</tr>
	</thead>
	<tbody>
	<?php foreach ($pa as $i) : ?>
		<tr>
			<td>
				<?=date_format(date_create($i->datamisurazione),'d/m/Y')?>
			</td> 
			<td>
				<?=$i->sistolica?> 
			</td> 
			<td>
				<?=$i->diastolica?>
			</td>
			<td>
				<?=$i->peso?>
			</td>
			<td>
				<a href="?p=elenco&act=del&id=<?=$i->id?>" title="elimina questa rilevazione">x</a>
			</td>			
		</tr>
	<?php endforeach; ?>
	</tbody>
	<tfoot>
		<tr>
			<td>
				Media:
			</td> 
			<td>
				<?=number_format($media['sistolica'],0)?> 
			</td> 
			<td>
				<?=number_format($media['diastolica'],0)?> 
			</td>
			<td>
				<?=number_format($media['peso'],2)?> 
			</td>
			<td>
				<!-- con questo carattere speciale (&nbsp;) indichiamo uno spazio che non può essere rimosso dal browser in fase di renderizzazione della pagina. Normalmente, infatti, gli spazi tra i tag non vengono visualizzati -->			
				&nbsp; 
			</td>			
		</tr>
	</tfoot>	
</table>
<script>
	var limit=function(e){
		var inp=document.getElementsByName('diastolica')[0]
		inp.setAttribute('max',e.value)
	}
	var validateForm=function(e){
		var valid=false
		var data={diastolica:false,sistolica:false,datamisurazione:false}
		data.diastolica=document.getElementsByName('diastolica')[0].value || false
		data.sistolica=document.getElementsByName('sistolica')[0].value || false
		data.datamisurazione=document.getElementsByName('datamisurazione')[0].value || false
		valid=parseInt(data.diastolica)<parseInt(data.sistolica)
		valid=valid && new Date(data.datamisurazione)<=new Date()
		if (!valid) {
			document.getElementById('message').innerHtml('Controlla i dati inseriti, perché non sono validi. <br />'+JSON_encode(data))
			event.preventDefault()
		}
	}
</script>
<script>
	$(function() {
		$('form').addClass('pure-form')
		$('button').addClass('pure-button')
		$('#tabmisura').DataTable()
	})
</script>