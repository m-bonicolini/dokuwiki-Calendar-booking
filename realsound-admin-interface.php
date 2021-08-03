<?php

require_once('realsound-db-functions.php');

/* *** PANNELLO DI AMMINISTRAZIONE *** */

function adm_adminPanel() {
	$check = checkPagePermission(ADMIN);
	if(!$check) {
		echo "<pre>Non sei autorizzato a visualizzare questa pagina.<br>Se sei l'amministratore, esegui il login e riprova.</pre>";
	}
	else {
		$siteurl = $_SERVER['PHP_SELF'];
		echo "<h4>Benvenuto!</h4><br>";
		echo "Da qui puoi gestire tutti gli aspetti relativi ad utenti, prenotazioni, lezioni.<br>
Prima di effettuare qualsiasi operazione che coinvolge un utente potresti aver bisogno di alcuni suoi dati. 
In tal caso puoi effettuare una ricerca tramite <b><a href=$siteurl?id=cercautente>questa interfaccia</a></b>.<br><br>";
		echo "Che tipo di operazione vuoi eseguire?<br>";
		echo "<ul>";
		echo "<li><a href=$siteurl?id=admincalendar>Visualizza il calendario delle attività in programma</a>";
		echo "<li><a href=$siteurl?id=adminprenotazioni>Prenota una sala per un utente</a>";
		echo "<li><a href=$siteurl?id=adminlezioni>Fissa una lezione</a>";
		echo "<li><a href=$siteurl?id=adminutenti>Gestisci utenti</a>";
		echo "</ul>";
	}
}


/* *** RICERCA UTENTI *** */

// (ausiliarie) Restituiscono tutti i dati di un utente a partire da varie chiavi di ricerca
function getUserDataByNameSurname($name, $surname) {
	try{
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	$sql = 'SELECT * 
			FROM User
			WHERE Nome = :name and Cognome = :surname';
	$stm = $conn->prepare($sql);

	$stm->bindParam(':name', $name);
	$stm->bindParam(':surname', $surname);
	
	$stm->execute();
	$row = $stm->fetchAll();
	return $row;
	}catch (Exception $e) {
	echo "<br> ERROR: " . $e->getMessage() . "<br>";
	}
}

function getUserDataByTessera($tessera){
	try{
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	$sql = 'SELECT * 
			FROM User
			WHERE NumTessera = :tessera';
	$stm = $conn->prepare($sql);

	$stm->bindParam(':tessera', $tessera);
	
	$stm->execute();
	$row = $stm->fetchAll();
	return $row;
	}catch (Exception $e) {
	echo "<br> ERROR: " . $e->getMessage() . "<br>";
	}
}

function getUserDataByUserName($username){
	try{
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	$sql = 'SELECT * 
			FROM User
			WHERE UserName = :username';
	$stm = $conn->prepare($sql);

	$stm->bindParam(':username', $username);
	
	$stm->execute();
	$row = $stm->fetchAll();
	return $row;
	}catch (Exception $e) {
	echo "<br> ERROR: " . $e->getMessage() . "<br>";
	}
}


// (ausiliaria) Restituisce un vettore con tutti i contatti di un User
function getUserContact($numTessera) {
	$userId = db_getUserAttributeByKey('UserId', 'NumTessera', $numTessera);
	
	try{
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	$sql = 'SELECT * 
			FROM User2Contatto
			WHERE Utente = :uid';
	$stm = $conn->prepare($sql);

	$stm->bindParam(':uid', $userId);
	
	$stm->execute();
	$row = $stm->fetchAll();
	return $row;
	}catch (Exception $e) {
	echo "<br> ERROR: " . $e->getMessage() . "<br>";
	}
}


// (OBSOLETE) (ausiliaria) Restituisce un vettore contenente tutti i dati dell'Utente (o DEGLI Utenti) che si chiamano "$name $surname"
function getUsersNumTessera($name, $surname) {
	try{
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	$sql = 'SELECT * 
			FROM User U JOIN User2Contatto U2C ON U.UserId = U2C.Utente
			WHERE U.Nome = :nome and U.Cognome = :cognome'; // OPZIONALE "order by Nome, Cognome" (in teoria dovrebbe essere già ordinato)
	$stm = $conn->prepare($sql);

	$stm->bindParam(':nome', $name);
	$stm->bindParam(':cognome', $surname);
	
	$stm->execute();
	$row = $stm->fetchAll();
	return $row;
	}catch (Exception $e) {
	echo "<br> ERROR: " . $e->getMessage() . "<br>";
	}
	
}


// (ausiliarie) Disegno dei form di input per la ricerca *** */
function drawUserSearchFormByNameSurname() {
	$myself=$_SERVER['PHP_SELF'];
	$html = "<form method=\"post\" action=\"$myself?id=cercautente\"><table>
				<tr>
					<td>Nome</td> <td>Cognome</td> <td></td>
				</tr>
				<tr>
					<td><input type=\"text\" class=\"formInput\" name=\"NOME\" /></td> 
					<td><input type=\"text\" class=\"formInput\" name=\"COGNOME\" /></td> 
					<td><input type=\"submit\"  name=\"CERCANS\" value=\"Cerca\" /></td>
				</tr>
	
			</table></form>";
	
	echo $html;
	return;
}

function drawUserSearchFormByTessera() {
	$myself=$_SERVER['PHP_SELF'];
	$html = "<form method=\"post\" action=\"$myself?id=cercautente\"><table>
				<tr>
					<td>Numero di Tessera</td> <td></td> <td></td>
				</tr>
				<tr>
					<td><input type=\"text\" class=\"formInput\" name=\"TESSERA\" /></td> 
					<td></td> 
					<td><input type=\"submit\"  name=\"CERCAT\" value=\"Cerca\" /></td>
				</tr>
	
			</table></form>";
	
	echo $html;
	return;
}

function drawUserSearchFormByUserName() {
	$myself=$_SERVER['PHP_SELF'];
	$html = "<form method=\"post\" action=\"$myself?id=cercautente\"><table>
				<tr>
					<td>Username</td> <td></td> <td></td>
				</tr>
				<tr>
					<td><input type=\"text\" class=\"formInput\" name=\"USERNAME\" /></td> 
					<td></td> 
					<td><input type=\"submit\"  name=\"CERCAUN\" value=\"Cerca\" /></td>
				</tr>
	
			</table></form>";
	
	echo $html;
	return;
}


// Disegna il risultato di un getUserData* (passato in input) e di getUserContact (invocato a partire dal numero di tessera), */
function drawUserData($userArray){
	$mycount = count($userArray);
	for($i=0;$i<$mycount;$i++){
		$row = $userArray[$i];
		list($aaaa, $mm, $gg) = explode("-",$row['NascitaDate']);
		$numTessera = $row['NumTessera'];
		$username = $row['UserName'];
		$contatti = getUserContact($numTessera);
		
		// maniera grezza di estrapolare i contatti. va fatta funzione.
		$mailrow = $contatti[0];
		$phonerow = $contatti[1];
		$mail = $mailrow['Value'];
		$phone = $phonerow['Value'];
		
		echo $row['Nome']." ".$row['Cognome'].", nato il $gg/$mm/$aaaa. Telefono: $phone. E-mail: $mail<br>";
		echo "<b>Numero di tessera: $numTessera. Username: $username</b><br><br>"; 
		
	}
}

function drawUserEditForm($userdata, $contatti) {
	$siteurl=$_SERVER['PHP_SELF'];
	
	// grep di tutte le info utili
	$user = $userdata[0];
	$mailrow = $contatti[0];
	$mail = $mailrow['Value'];
	$phonerow = $contatti[1];
	$phone = $phonerow['Value'];
	$username = $user['UserName'];
	$numtessera = $user['NumTessera'];
	$nome = $user['Nome'];
	$cognome = $user['Cognome'];
	$professione = $user['Professione'];
	$luogonascita = $user['NascitaCity'];
	$provnascita = $user['NascitaProvincia'];
	list($aaaa, $mm, $gg) = explode("-", $user['NascitaDate']);
	$datanascita = "$gg/$mm/$aaaa";
	$rvia = $user['ResidenzaVia'];
	$rcivico = $user['ResidenzaCivico'];
	$rcap = $user['ResidenzaCAP'];
	$rcity = $user['ResidenzaCittà'];
	$rprov = $user['ResidenzaProvincia'];
	$rstato = $user['ResidenzaStato'];
	// in teoria se è settato uno sono settati tutti e viceversa
	if(!empty($user['DomicilioVia'])) {
		$dvia = $user['DomicilioVia'];
		$dcivico = $user['DomicilioCivico'];
		$dcap = $user['DomicilioCAP'];
		$dcity = $user['DomicilioCittà'];
		$dprov = $user['DomicilioProvincia'];
		$dstato = $user['DomicilioStato'];
		
	}
	// continuare con tutti i campi da visualizzare e/o editare
	
	/* Format per il draw:
	 * <td>$Campo_riepilogativo</td> <td></td> <td></td>
	 * <td>$Campo_editabile</td> <td>--></td> <td><input type=\"text\" class=\"formInput\" name=\"NOMECAMPO\" /></td>*/
	
	$html = "<form method=\"post\" action=\"$myself?id=edituser\"><table>
				<tr>
					<td><b>Utente: $username, Tessera: $numtessera</b></td> <td></td> <td></td>
				</tr>
				<tr>
					<td>$nome $cognome</td> <td></td> <td></td>
				</tr>
				<tr>
					<td>Nato a $luogonascita ($provnascita) il $datanascita</td> <td></td> <td></td>
				</tr>
				<tr>
					<td>Professione: $professione</td> <td>--></td> <td><input type=\"text\" class=\"formInput\" name=\"PROFESSIONE\" /></td>
				</tr>
				<tr></tr><tr></tr>
				<tr>
					<td></td> <td align=\"center\"><b>Dati di Residenza</b></td> <td></td>
				</tr>
				<tr></tr>
				<tr>
					<td>Indirizzo: $rvia</td> <td>--></td> <td><input type=\"text\" class=\"formInput\" name=\"RVIA\" /></td>
				</tr>
				<tr>
					<td>Civico: $rcivico</td> <td>--></td> <td><input type=\"text\" class=\"formInput\" name=\"RCIVICO\" /></td>
				</tr>
				<tr>
					<td>CAP: $rcap</td> <td>--></td> <td><input type=\"text\" class=\"formInput\" name=\"RCAP\" /></td>
				</tr>
				<tr>
					<td>Città: $rcity</td> <td>--></td> <td><input type=\"text\" class=\"formInput\" name=\"RCITY\" /></td>
				</tr>
				<tr>
					<td>Provincia: $rprov</td> <td>--></td> <td><input type=\"text\" class=\"formInput\" name=\"RPROV\" /></td>
				</tr>
				<tr>
					<td>Stato: $rstato</td> <td>--></td> <td><input type=\"text\" class=\"formInput\" name=\"RSTATE\" /></td>
				</tr>";
	// se è settato il primo, sono settati tutti (qui è sicuro)
	if(isset($dvia)){
		$html .= "<tr></tr><tr></tr>
				<tr>
					<td></td> <td align=\"center\"><b>Dati di Domicilio</b></td> <td></td>
				</tr>
				<tr></tr>
				<tr>
					<td>Indirizzo: $dvia</td> <td>--></td> <td><input type=\"text\" class=\"formInput\" name=\"DVIA\" /></td>
				</tr>
				<tr>
					<td>Civico: $dcivico</td> <td>--></td> <td><input type=\"text\" class=\"formInput\" name=\"DCIVICO\" /></td>
				</tr>
				<tr>
					<td>CAP: $dcap</td> <td>--></td> <td><input type=\"text\" class=\"formInput\" name=\"DCAP\" /></td>
				</tr>
				<tr>
					<td>Città: $dcity</td> <td>--></td> <td><input type=\"text\" class=\"formInput\" name=\"DCITY\" /></td>
				</tr>
				<tr>
					<td>Provincia: $dprov</td> <td>--></td> <td><input type=\"text\" class=\"formInput\" name=\"DPROV\" /></td>
				</tr>
				<tr>
					<td>Stato: $dstato</td> <td>--></td> <td><input type=\"text\" class=\"formInput\" name=\"DSTATE\" /></td>
				</tr>
				";
	}
	
	$html .= "<tr></tr><tr></tr>
			<tr>
				<td></td> <td align=\"center\"><b>Contatti</b></td> <td></td>
			</tr>
			<tr></tr>
			<tr>
				<td>E-mail: $mail</td> <td>--></td> <td><input type=\"text\" class=\"formInput\" name=\"CMAIL\" /></td>
			</tr>
			<tr>
				<td>Telefono: $phone</td> <td>--></td> <td><input type=\"text\" class=\"formInput\" name=\"CPHONE\" /></td>
			</tr>";
	echo $html;			
	echo "</table><br>
		<div align=\"center\"><input type=\"submit\"  name=\"EDIT\" value=\"Modifica\" /></div>
		</form>";
	return;
}

/* *** Interfaccia principale di ricerca *** */
function adm_searchUserInterface() {
	$check = checkPagePermission(ADMIN);
	if(!$check) {
		echo "<pre>Non sei autorizzato a visualizzare questa pagina.<br>Se sei l'amministratore, esegui il login e riprova.</pre>";
	}
	else {
		// qui faccio tutte le ricerche
		
		// prima volta...
		if(empty($_POST['CERCANS']) and empty($_POST['CERCAT']) and empty($_POST['CERCAUN'])) {
			echo "Compila i campi qui sotto e clicka il pulsante \"Cerca\" corrispondente, per ottenere informazioni sugli utenti.<br><br>";
			echo "<b>Ricerca per:</b><br>";
			searchUserByNameSurname();
			searchUserByTessera();
			searchUserByUserName();
			//$_SESSION['Ricerca'] = true;
		}
		// ...già fatta una ricerca: continuo con quella giusta
		else {
			if(!empty($_POST['CERCANS'])){
				searchUserByNameSurname();
			}
			if(!empty($_POST['CERCAT'])){
				searchUserByTessera();
			}
			if(!empty($_POST['CERCAUN'])){
				searchUserByUserName();
			}
		}
	}
}

/* *** Funzioni di ricerca (invocate dall'interfaccia adm_searchUserInterface):
 * disegnano il form, cercano, disegnano i risultati *** */
function searchUserByNameSurname() {
		// se l'utente ha già effettuato QUESTA ricerca
		if(!empty($_POST['CERCANS'])) {
			// se non ha compilato entrambi i campi ... si ricomincia
			if(empty($_POST['NOME']) or empty($_POST['COGNOME'])) {
				echo "<font color=\"red\">Devi compilare <b>entrambi</b> i campi: Nome e Cognome.</font><br>";
				drawUserSearchFormByNameSurname();
			}
			// eseguo la ricerca ...
			else {
				$name = $_POST['NOME'];
				$surname = $_POST['COGNOME'];
				$ricerca = getUserDataByNameSurname($name, $surname);
				if(count($ricerca) == 0) {
					echo "Nessun utente trovato.";
					//$_SESSION['SearchByNameSurname'] = false;
					//$_SESSION['Ricerca'] = false;
					return;
				}
				// ... e la disegno
				drawUserData($ricerca);
				
				//$_SESSION['SearchByNameSurname'] = false;
				$_SESSION['Ricerca'] = false;
				return;
			}
		}
		else {
			//prima volta per QUESTA ricerca.
			drawUserSearchFormByNameSurname();
			//$_SESSION['SearchByNameSurname'] = true;
			return;
		}
}

function searchUserByTessera(){
		// se l'utente ha già effettuato QUESTA ricerca
		if(!empty($_POST['CERCAT'])) {
			// se non ha compilato entrambi i campi ... si ricomincia
			if(empty($_POST['TESSERA']) or !is_numeric($_POST['TESSERA'])) {
				echo "<font color=\"red\">Non hai compilato il campo, oppure hai inserito un valore non numerico.</font><br>";
				drawUserSearchFormByTessera();
			}
			// eseguo la ricerca ...
			else {
				$tessera = $_POST['TESSERA'];
				$ricerca = getUserDataByTessera($tessera);
				if(count($ricerca) == 0) {
					echo "Nessun utente trovato.";
					//$_SESSION['SearchByTessera'] = false;
					//$_SESSION['Ricerca'] = false;
					return;
				}
				// ... e la disegno
				drawUserData($ricerca);
				
				//$_SESSION['SearchByTessera'] = false;
				//$_SESSION['Ricerca'] = false;
				return;
			}
		}
		else {
			//prima volta per QUESTA ricerca.
			drawUserSearchFormByTessera();
			//$_SESSION['SearchByTessera'] = true;
			return;
		}
}

function searchUserByUserName(){
		// se l'utente ha già effettuato QUESTA ricerca
		if(!empty($_POST['CERCAUN'])) {
			// se non ha compilato entrambi i campi ... si ricomincia
			if(empty($_POST['USERNAME'])) {
				echo "<font color=\"red\">Non hai compilato il campo.</font><br>";
				drawUserSearchFormByUserName();
			}
			// eseguo la ricerca ...
			else {
				$username = $_POST['USERNAME'];
				$ricerca = getUserDataByUserName($username);
				if(count($ricerca) == 0) {
					echo "Nessun utente trovato.";
					//$_SESSION['SearchByTessera'] = false;
					//$_SESSION['Ricerca'] = false;
					return;
				}
				// ... e la disegno
				drawUserData($ricerca);
				
				//$_SESSION['SearchByTessera'] = false;
				//$_SESSION['Ricerca'] = false;
				return;
			}
		}
		else {
			//prima volta per QUESTA ricerca.
			drawUserSearchFormByUserName();
			//$_SESSION['SearchByTessera'] = true;
			return;
		}
}



/* *** GESTIONE UTENTI *** */

// Interfaccia di scelta dell'operazione
function adm_manipulateUsers(){
	$siteurl=$_SERVER['PHP_SELF'];
	$check = checkPagePermission(ADMIN);
	if(!$check) {
		echo "<pre>Non sei autorizzato a visualizzare questa pagina.<br>Se sei l'amministratore, esegui il login e riprova.</pre>";
	}
	else {
		echo "Prima di effettuare una delle seguenti operazioni, ricordati che gli utenti sono identificati dal sistema mediante il numero della tessera associativa oppure il loro username.<br>
		Se non possiedi tali informazioni, usa <b><a href=$siteurl?id=cercautente>questa interfaccia</a></b> per reperirle.<br><br>";
		
		echo "Scegli un'operazione:<br><ul>";
		echo "<li><a href=$siteurl?id=iscrizione>Iscrivi un utente</a>";
		echo "<li><a href=$siteurl?id=waitingusers>Visualizza e approva le richieste di iscrizione</a>";
		echo "<li><a href=$siteurl?id=edituser>Modifica i dati di un utente</a>";
		echo "<li><a href=$siteurl?id=edituserpermission>Modifica i permessi di un utente o assegnalo ad un altro gruppo.</a>";
		echo "<li><a href=$siteurl?id=deleteuser>Cancella definitivamente un utente</a>";
		echo "</ul>";
	}
}

// Interfaccia di approvazione richieste di iscrizione pendenti.
function adm_waitingUsers(){
	$siteurl=$_SERVER['PHP_SELF'];
	$check = checkPagePermission(ADMIN);
	if(!$check) {
		echo "<pre>Non sei autorizzato a visualizzare questa pagina.<br>Se sei l'amministratore, esegui il login e riprova.</pre>";
	}
	else {
		// prima volta sulla pagina
		if(empty($_POST)) {
			$users = db_getWaitingUsers();
			$mycount = count($users);
			
			$Uid2Checkbox = array(); // verrà assegnato a $_SESSION['User2Checkbox'] alla fine del ciclo
			
			if($mycount <= 0) {
				echo "Non ci sono utenti in attesa di approvazione.";
			}
			else {
				echo "I seguenti utenti hanno richiesto l'iscrizione:<br><br>";
				echo "<form method=\"post\" action=\"$siteurl?id=waitingusers\">";
				echo "<table>";
				for($i=0;$i<$mycount;$i++) {
					$row = $users[$i];
					$uid = $row['UserId'];
					
					// aggiungo l'id in modo da averli ordinati nell'array da 0 a N
					array_push($Uid2Checkbox, $uid);
					
					$nome = $row['Nome'];
					$cognome = $row['Cognome'];
					list($aaaa, $mm, $gg) = explode("-", $row['NascitaDate']);
					$bdate = "$gg/$mm/$aaaa";
					$bcity = $row['NascitaCity'];
					$rvia = $row['ResidenzaVia'];
					$rcap = $row['ResidenzaCAP'];
					$rcity = $row['ResidenzaCittà'];
					$rcivico = $row['ResidenzaCivico'];
					$professione = $row['Professione'];
					$username = $row['UserName'];
					$numtessera = $row['NumTessera'];
					
					$contatti = getUserContact($numtessera);
				
					// maniera grezza di estrapolare i contatti. va fatta funzione.
					$mailrow = $contatti[0];
					$phonerow = $contatti[1];
					$tel = $phonerow['Value'];
					$mail = $mailrow['Value'];
					echo "<tr>";
					echo 	"<td><input type=\"checkbox\" class=\"formInput\" name=\"CHECK$uid\" /></td> <td><b>$nome $cognome</b></td>";
					echo "</tr>";
					echo "<tr>";
					echo "<tr>";
					echo 	"<td></td> <td><b>Username: $username</b> &emsp; <b>Numero di Tessera (assegnato dal sistema): $numtessera</b></td>";
					echo "</tr>";
					echo "<tr>";
					echo 	"<td></td> <td><b>Telefono: $tel &emsp; E-mail: $mail</b></td>";
					echo "</tr>";
					echo 	"<td></td> <td>Nato a $bcity il: $bdate</td>";
					echo "</tr>";
					echo "<tr>";
					echo 	"<td></td> <td>Residente in $rvia, $rcivico</td>";
					echo "</tr>";
					echo "<tr>";
					echo 	"<td></td> <td>$rcap $rcity</td>";
					echo "</tr>";
					echo "<tr>";
					echo 	"<td></td> <td>Professione: $professione</td>";
					echo "</tr>";
					echo "<tr></tr><tr></tr><tr></tr><tr></tr>";
				}
				echo "</table><br>";
				echo "<div align=\"center\"><input type=\"submit\"  name=\"APPROVE\" value=\"Approva\" /></div>";
				echo "</form>";
				
				$_SESSION['User2Checkbox'] = $Uid2Checkbox;
			}
		}
		// l'admin ha selezionato utenti da abilitare
		else {
			$uids = $_SESSION['User2Checkbox'];
			$mycount = count($_POST);
			for($i=0;$i<$mycount;$i++) {
				$thisuid = $uids[$i];
				$checkbox = $_POST["CHECK$thisuid"];
				if(!empty($checkbox)) {
					// l'utente viene cambiato da sospeso ad attivo
					db_unsetFlag($thisuid, SOSPESO);
					db_setFlag($thisuid, ATTIVO);
				}
			}
			echo "Gli utenti selezionati sono stati abilitati!";
		}
	}
}

// Interfaccia per modificare i dati di un utente
function adm_editUser(){
	$siteurl=$_SERVER['PHP_SELF'];
	$check = checkPagePermission(ADMIN);
	if(!$check) {
		echo "<pre>Non sei autorizzato a visualizzare questa pagina.<br>Se sei l'amministratore, esegui il login e riprova.</pre>";
	}
	else {
		
		// l'admin ha chiesto di modificare alcuni dati
		if(!empty($_POST['EDIT'])){
			$thisuser = $_SESSION['TESSERA'];
			secureEditUserData($_POST, $thisuser);
			
		}
		// l'admin ha cercato l'utente
		elseif(!empty($_POST['SRCTESSERA']) and !empty($_POST['CERCAT'])){
			$tessera = $_POST['SRCTESSERA'];
			$_SESSION['TESSERA']=$tessera;
			$userdata = getUserDataByTessera($tessera);
			$contatti = getUserContact($tessera);
			drawUserEditForm($userdata, $contatti);
		}
		// prima volta: disegno il form per la ricerca
		else{
			echo "Inserisci il numero di tessera dell'utente di cui vuoi modificare i dati<br>";
			$html = "<form method=\"post\" action=\"$siteurl?id=edituser\"><table>
						<tr>
							<td>Numero di Tessera</td> <td></td> <td></td>
						</tr>
						<tr>
							<td><input type=\"text\" class=\"formInput\" name=\"SRCTESSERA\" /></td> 
							<td></td> 
							<td><input type=\"submit\"  name=\"CERCAT\" value=\"Cerca\" /></td>
						</tr>
			
					</table></form>";
			echo $html;
		}
	}
}

// (ausiliaria) Controlla la correttezza dei dati ed effettua le modifiche sul database
function secureEditUserData($postdata, $user){
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	
	try {
		$conn->beginTransaction();
		
		// per ogni campo eseguiamo la modifica, se è stata richiesta
		// dopo aver controllato la correttezza di ciò che stiamo inserendo
		if(!empty($postdata['PROFESSIONE'])){
			echo "<br>Sono in Professione <br>";
			//controlli specifici
			$newvalue = $postdata['PROFESSIONE'];
			//tutto bene, modifico
			$ris = db_edit('User', 'Professione', $newvalue, 'NumTessera', $user);
			echo "<br>Update ha dato esito: $ris<br>";
		}
		if(!empty($postdata['RVIA'])){
			//controlli specifici (da fare)
			$newvalue = $postdata['RVIA'];
			//tutto bene, modifico
			db_edit('User', 'ResidenzaVia', $newvalue, 'NumTessera', $user);
		}
		if(!empty($postdata['RCIVICO'])){
			//controlli specifici
			$newvalue = $postdata['RCIVICO'];
			//tutto bene, modifico
			db_edit('User', 'ResidenzaCivico', $newvalue, 'NumTessera', $user);
		}
		if(!empty($postdata['RCAP'])){
			//controlli specifici
			$newvalue = $postdata['RCAP'];
			//tutto bene, modifico
			db_edit('User', 'ResidenzaCAP', $newvalue, 'NumTessera', $user);
		}
		if(!empty($postdata['RCITY'])){
			//controlli specifici
			$newvalue = $postdata['RCITY'];
			//tutto bene, modifico
			db_edit('User', 'ResidenzaCittà', $newvalue, 'NumTessera', $user);
		}
		if(!empty($postdata['RPROV'])){
			//controlli specifici
			$newvalue = $postdata['RPROV'];
			//tutto bene, modifico
			db_edit('User', 'ResidenzaProvincia', $newvalue, 'NumTessera', $user);
		}
		if(!empty($postdata['RSTATE'])){
			//controlli specifici
			$newvalue = $postdata['RSTATE'];
			//tutto bene, modifico
			db_edit('User', 'ResidenzaStato', $newvalue, 'NumTessera', $user);
		}
		if(!empty($postdata['DVIA'])){
			//controlli specifici
			$newvalue = $postdata['DVIA'];
			//tutto bene, modifico
			db_edit('User', 'DomicilioVia', $newvalue, 'NumTessera', $user);
		}
		if(!empty($postdata['DCIVICO'])){
			//controlli specifici
			$newvalue = $postdata['DCIVICO'];
			//tutto bene, modifico
			db_edit('User', 'DomicilioCivico', $newvalue, 'NumTessera', $user);
		}
		if(!empty($postdata['DCAP'])){
			//controlli specifici
			$newvalue = $postdata['DCAP'];
			//tutto bene, modifico
			db_edit('User', 'DomicilioCAP', $newvalue, 'NumTessera', $user);
		}
		if(!empty($postdata['DCITY'])){
			//controlli specifici
			$newvalue = $postdata['DCITY'];
			//tutto bene, modifico
			db_edit('User', 'DomicilioCittà', $newvalue, 'NumTessera', $user);
		}
		if(!empty($postdata['DPROV'])){
			//controlli specifici
			$newvalue = $postdata['DPROV'];
			//tutto bene, modifico
			db_edit('User', 'DomicilioProvincia', $newvalue, 'NumTessera', $user);
		}
		if(!empty($postdata['DSTATE'])){
			//controlli specifici
			$newvalue = $postdata['DSTATE'];
			//tutto bene, modifico
			db_edit('User', 'DomicilioStato', $newvalue, 'NumTessera', $user);
		}
		
		// aggiorno anche i contatti, se richiesto
		if(!empty($postdata['CPHONE']) or !empty($postdata['CMAIL'])){
			$userid = db_getUserAttributeByKey('UserId', 'NumTessera', $user);
		}
		if(!empty($postdata['CPHONE'])){
			//controlli specifici
			$newvalue = $postdata['CPHONE'];
			//tutto bene, modifico
			db_editContatto('User2Contatto', 'Value', $newvalue, 'Utente', $userid, PHONE);
		}
		if(!empty($postdata['CMAIL'])){
			//controlli specifici
			$newvalue = $postdata['CMAIL'];
			//tutto bene, modifico
			db_editContatto('User2Contatto', 'Value', $newvalue, 'Utente', $userid, MAIL);
		}
		
		$_SESSION['TESSERA']=null;
		//tutto bene
		$conn->commit();
		echo "I dati dell'utente sono stati modificati come richiesto!";
	}catch(Exception $e){
		echo "Sono stati inseriti valori non validi. Si prega di riprovare.<br>Se il malfunzionamento persiste nonostante i dati siano corretti, contattare un tecnico.";
		$conn->rollBack();
	}
}

// Interfaccia di modifica dei permessi e del gruppo utente
function adm_editUserPermission(){
}

// Interfaccia per eliminare un utente
function adm_deleteUser(){
}
