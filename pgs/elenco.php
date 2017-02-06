<?php
	$valid=false;
	$msg=null;
	$table='pressione';
	$record=(empty($_REQUEST['id'])) ?  R::dispense($table) : R::load($table, intval($_REQUEST['id']));	
	try {
		if ($record && !empty($_REQUEST['act']) && $_REQUEST['act']=='del') { 
			$msg='Record # '.$record->id.' eliminato correttamente.';
			R::trash($record);
		}
		if (!empty($_POST['datamisurazione'])){
			foreach ($_POST as $k=>$v){
				$record[$k]=$v;
			}
			
			$valid=$record['diastolica']<$record['sistolica'];
			$valid=$valid && $record['datamisurazione']<=date('Y-m-d');
			$valid=$valid && strtotime($record['datamisurazione']);
			
			if ($valid){
				R::store($record);
				$msg='Dati inseriti correttamente';
			}else{
				$msg='Dati non validi: '.json_encode($record);
			}
		}
	} catch (RedBeanPHP\RedException\SQL $e) {
		$msg=$e->getMessage();
	}
	
	//$query='1';
	$dal=(empty($_POST['dal'])) ? date('Y-m-d') : $_POST['dal'];
	$al=(empty($_POST['al'])) ? date('Y-m-d') : $_POST['al'];
	$query="datamisurazione BETWEEN '".$dal."' AND '".$al."' ";
	
	$pa=R::find($table,$query);
	$media=R::getRow('SELECT avg(sistolica) AS sistolica, avg(diastolica) AS diastolica, avg(peso) AS peso FROM pressione WHERE '.$query);
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
				<a href="?p=elenco&act=del&id=<?=$i->id?>" title="elimina questa rilevazione">x</a>
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