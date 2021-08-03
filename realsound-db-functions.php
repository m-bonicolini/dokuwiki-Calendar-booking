<?php
/* *** DATABASE QUERY functions *** */

require_once('realsound-utils.php');


// controlla se l'utente ha una certa flag impostata
function db_isFlagSet($user, $flag) {
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	$stm = $conn->prepare('SELECT Flag FROM User Where UserId = :user');
	$stm->bindParam(':user', $user);
	
	$stm->execute();
	$row = $stm->fetch();
	
	$ret = checkflag($flag, $row['Flag']);
	
	return $ret;
}

function db_setFlag($userid, $flag) {
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	
	$stm = $conn->prepare('SELECT Flag FROM User WHERE UserId = :uid');
	$stm->bindParam(':uid', $userid);
	$stm->execute();
	$row = $stm->fetch();
	
	// add flag if it was unset
	if(!db_isFlagSet($userid, $flag)) {
		$myflag = $row[0];
		$myflag .= $flag;
		
		$stm = $conn->prepare('UPDATE User SET Flag = :flag WHERE UserId = :uid');
		$stm->bindParam(':flag', $myflag);
		$stm->bindParam(':uid', $userid);
		$stm->execute();
	}
	return;
}

function db_unsetFlag($userid, $flag){
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	
	$stm = $conn->prepare('SELECT Flag FROM User WHERE UserId = :uid');
	$stm->bindParam(':uid', $userid);
	$stm->execute();
	$row = $stm->fetch();
	
	$userflag = $row[0];
	$userflag = str_split($userflag); 
	$mycount = count($userflag);
	for($i=0; $i<$mycount;$i++){
		$test = $userflag[$i];
		if(!($test == $flag))
			$myflag .= $test;
	}

	$stm = $conn->prepare('UPDATE User SET Flag = :flag WHERE UserId = :uid');
	$stm->bindParam(':flag', $myflag);
	$stm->bindParam(':uid', $userid);
	$stm->execute();
	return;
}

function db_setUserType($userid, $type) {
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	$stm = $conn->prepare('UPDATE User SET Type = :type WHERE UserId = :uid');
	$stm->bindParam(':type', $type);
	$stm->bindParam(':uid', $userid);
	$stm->execute();
	
	return;
}

function db_typeOfUser($userid) {
	$ris = db_getUserAttributeByKey('Type', 'UserId', $userid);
	return $ris;
}

// ausiliaria delle varie getUser[$Attribute]By[&Key]
function db_getUserAttributeByKey($attribute, $keyName, $keyValue){
	try{
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	$sql = 'SELECT'.' '.$attribute.' '.'FROM User WHERE'.' '.$keyName.'= :valore';
	$stm = $conn->prepare($sql);

	/*$stm->bindParam(':attributo', $attribute);
	$stm->bindParam(':chiave', $keyName);*/
	$stm->bindParam(':valore', $keyValue);
	
	$stm->execute();
	$row = $stm->fetch();
	return $row[0];
	}catch (Exception $e) {
	echo "<br> ERROR: " . $e->getMessage() . "<br>";
	}
}


// Restituisce lo UserId di un utente di cui è noto lo UserName
function db_getUserIdByName($userName){
	$res = db_getUserAttributeByKey('UserId', 'UserName', $userName);
	return $res;
}

function db_getUserNameById($userId){
	$res = db_getUserAttributeByKey('UserName', 'UserId', $userId);
	return $res;
}

function db_existRoomInSessione($room, $gg, $mm, $aaaa) {
$conn = RealSoundDBConnection::getInstance()->getConnection();

$stm = $conn->prepare('SELECT EXISTS ( SELECT 1 FROM SessioneProva WHERE Room = :room and Data = :data)');
         
         $data = "$aaaa-$mm-$gg";
         $stm->bindParam(':room', $room);
         $stm->bindParam(':data', $data);

         
         $stm->execute();
         
         $row = $stm->fetch();
         
         return "$row[0]";
}

function db_edit($table, $column, $colvalue, $keyname, $keyvalue){
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	$update = "UPDATE"." ".$table." "."SET"." ".$column."= :colvalue"." "."WHERE"." ".$keyname."= :keyvalue";
	
	$stm = $conn->prepare($update);
	$stm->bindParam(':colvalue', $colvalue);
	$stm->bindParam(':keyvalue', $keyvalue);
	$ris = $stm->execute();
	
	return $ris;
}

function db_editContatto($table, $column, $colvalue, $keyname, $keyvalue, $type){
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	$update = "UPDATE"." ".$table." "."SET"." ".$column."= :colvalue"." "."WHERE"." ".$keyname."= :keyvalue"." "."AND Tipo= :type";
	
	$stm = $conn->prepare($update);
	$stm->bindParam(':colvalue', $colvalue);
	$stm->bindParam(':keyvalue', $keyvalue);
	$stm->bindParam(':type', $type);
	$ris = $stm->execute();
	
	return $ris;
}

// Restituisce 1 se c'è ALMENO UNA prenotazione in Lezione, 0 se quella sala non è mai stata prenotata in quella data
function db_existRoomInLezione($room, $gg, $mm, $aaaa) {
	$conn = RealSoundDBConnection::getInstance()->getConnection();

	$stm = $conn->prepare('SELECT EXISTS ( SELECT 1 FROM Lezione WHERE Room = :room and Data = :data)');
         
         $data = "$aaaa-$mm-$gg";
         $stm->bindParam(':room', $room);
         $stm->bindParam(':data', $data);


         $stm->execute();
         
         $row = $stm->fetch();
         
         return "$row[0]";
}

function db_AllSessione($room, $gg, $mm, $aaaa) {
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	
	$stm = $conn->prepare('select OraInizio as Start, OraFine as End, Utente, SessioneProvaId from SessioneProva where Room = :room and Data = :data order by OraInizio;');
	
	$data = "$aaaa-$mm-$gg";
	
	$stm->bindParam(':room', $room);
	$stm->bindParam(':data', $data);
	
	
	
	$stm->execute();
	$row = $stm->fetchAll();
	
	return $row;
}

function db_AllLezione($room, $gg, $mm, $aaaa) {
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	
	$stm = $conn->prepare('select OraInizio as Start, OraFine as End, Insegnante, LezioneId from Lezione where Room = :room and Data = :data order by OraInizio;');
	
	$data = "$aaaa-$mm-$gg";
	
	$stm->bindParam(':room', $room);
	$stm->bindParam(':data', $data);
	
	
	
	$stm->execute();
	$row = $stm->fetchAll();
	
	return $row;
}


function db_getAllSala() {
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	
	$stm = $conn->prepare('SELECT SalaId as Id, Nome FROM Sala order by SalaId');
	
	$stm->execute();
	$row = $stm->fetchAll();
	
	return $row;
}


function db_getRuleDate($gg, $mm, $aaaa) {
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	
	$stm = $conn->prepare('Select Start, Lenght, Quantum from CalendarRules where Date = :data');
	
	$data = "$aaaa-$mm-$gg";
	
	$stm->bindParam(':data', $data);
	
	
	
	$stm->execute();
	$row = $stm->fetchAll();
	
	return $row;
}

function db_getRuleWeek($WeekDayAsInt) {
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	
	$stm = $conn->prepare('Select Start, Lenght, Quantum from CalendarRules where WeekDay = :giorno and Month is NULL');
	
	$stm->bindParam(':giorno', $WeekDayAsInt);
	
	
	
	$stm->execute();
	$row = $stm->fetchAll();
	
	return $row;
}

function db_getRuleMonth($MonthAsInt) {
    $conn = RealSoundDBConnection::getInstance()->getConnection();

    $stm = $conn->prepare('Select Start, Lenght, Quantum from CalendarRules where Month = :mese and WeekDay is null;');

    $stm->bindParam(':mese', $MonthAsInt);

        

    $stm->execute();
    $row = $stm->fetchAll();

    return $row;
}

function db_getRuleWeekAndMonth($WeekDayAsInt, $MonthAsInt) {
        $conn = RealSoundDBConnection::getInstance()->getConnection();

        $stm = $conn->prepare('Select Start, Lenght, Quantum from CalendarRules where Month = :mese and WeekDay = :giorno;');

        $stm->bindParam(':giorno', $WeekDayAsInt);
        $stm->bindParam(':mese', $MonthAsInt);

       

        $stm->execute();
        $row = $stm->fetchAll();

        return $row;
}

function db_getUserSessione($userId, $currDate, $shiftdays) {
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	
	if($shiftdays > 0) {
		$stm = $conn->prepare('SELECT SessioneProvaId as Id, Room, Data, OraInizio as Start, OraFine as End
							FROM SessioneProva
							WHERE Utente = :user and Data >= ADDDATE(:data, :shiftdays)
							order by Data, Start ');
		$stm->bindParam(':user', $userId);
		$stm->bindParam(':data', $currDate);
		$stm->bindParam(':shiftdays', $shiftdays);
	}else {
		$stm = $conn->prepare('SELECT SessioneProvaId as Id, Room, Data, OraInizio as Start, OraFine as End
							FROM SessioneProva
							WHERE Utente = :user and Data >= :data
							order by Data, Start ');
	
		$stm->bindParam(':user', $userId);
		$stm->bindParam(':data', $currDate);
	}
	
	$stm->execute();
	$row = $stm->fetchAll();
	
	return $row;
}

// restituisce il vettore degli utenti in attesa di approvazione
function db_getWaitingUsers(){
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	
	$stm = $conn->prepare('SELECT * FROM User WHERE Flag LIKE \'%S%\'');
	
	$stm->execute();
	$row = $stm->fetchAll();
	
	return $row;	
}

// Input: studente e data odierna. Output: tutte le lezioni future a cui è iscritto.
function db_getUserLezione($userId, $currDate) {
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	
	$stm = $conn->prepare('SELECT Data, OraInizio as Start, OraFine as End, Insegnante, Room as Sala
							FROM Lezione as L JOIN Iscrizione as I ON  L.LezioneId = I.Classe
							WHERE I.Studente = :user and L.Data >= :data');
	
	$stm->bindParam(':user', $userId);
	$stm->bindParam(':data', $currDate);
	
	$stm->execute();
	$row = $stm->fetchAll();
	
	return $row;
}

// Input: LezioneId. Output: vettore con UserId degli studenti iscritti.
function db_getIscritti($lezioneId) {
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	
	$stm = $conn->prepare('SELECT Studente FROM Iscrizione WHERE Classe = :lezione');
	
	$stm->bindParam(':lezione', $lezioneId);
	
	$stm->execute();
	$row = $stm->fetchAll();
	
	return $row;
}

function db_getRoomNameById($roomId) {
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	$stm = $conn->prepare('SELECT Nome FROM Sala WHERE SalaId = :id');

	$stm->bindParam(':id', $roomId);
	$stm->execute();
	$row = $stm->fetch();
	
	return $row['Nome'];
	
}

// controlla che la sessione sia ad almeno 24 di distanza e poi la cancella.
function db_secureDeleteSessione($sessionId) {
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	$stm = $conn->prepare('SELECT Data, OraInizio as Start FROM SessioneProva WHERE SessioneProvaId = :id');
	$stm->bindParam(':id', $sessionId);
	$stm->execute();
	
	$row = $stm->fetch();
	
	$len=count($row);
	$nextDay=strtotime("+1 days");
	list($y,$m,$d)= explode("-",$row['Data']);
	list($hh,$mm,$ss) = explode(":",$row['Start']);
	
	$data = strtotime("$d-$m-$y $hh:$mm");
	
	if($data >= $nextDay) {
		$res = db_deleteSessione($sessionId);
		return $res;
	}else throw new LogicException("Attenzione! Si stà tentando di cancellare una prenotazione con meno di 24 ore di anticipo. Questo è contrario al regolamento dell'Associazione.");
}

// cancella una prenotazione
function db_deleteSessione($sessionId) {
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	
	$stm = $conn->prepare('DELETE FROM SessioneProva WHERE SessioneProvaId = :id;');
	
	$stm->bindParam(':id', $sessionId);
	
	$stm->execute();
	
	return $stm;
}

// rimuove tutte le iscrizioni, infine rimuove la lezione
function db_secureDeleteLezione($lezioneId){
	db_deleteIscrizione($lezioneId);
	db_deleteLezione($lezioneId);
	
	return;
}

function db_deleteLezione($lezioneId) {
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	
	$stm = $conn->prepare('DELETE FROM Lezione WHERE LezioneId = :id;');
	
	$stm->bindParam(':id', $lezioneId);
	
	$ris = $stm->execute();
	
	return $ris;
}

// cancella TUTTE le iscrizioni a $lezioneId
function db_deleteIscrizione($lezioneId){
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	
	$stm = $conn->prepare('DELETE FROM Iscrizione WHERE Classe = :lezione;');
	
	$stm->bindParam(':lezione', $lezioneId);
	
	$ris = $stm->execute();
	
	return $ris;
}

function db_deleteUser($userId) {
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	$delete = 'DELETE FROM User WHERE UserId = :uid';
	
	$stm = $conn->prepare($delete);
	$stm->bindParam(':uid', $userId);
	$ris = $stm->execute();
	
	return $ris;
}

function db_deleteUserContact($userId){
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	$delete = 'DELETE FROM User2Contatto WHERE Utente = :uid';
	
	$stm = $conn->prepare($delete);
	$stm->bindParam(':uid', $userId);
	$ris = $stm->execute();
	
	return $ris;
}

function abs_securePrenotazione($user, $room, $data, $start, $end, $type){
	
	
	/* *** Validazione dati immessi ***
	 * indipendentemente dal database (fuori transazione) */
	
	// controllo tipi user e room
	if(!is_int($user)) throw new LogicException("User not int");
	
	if(!is_int($room)) throw new LogicException("Room not int");
	
	// controllo data ben formata
	list($yyyy, $mm, $dd) = explode("-", $data);
	if(!checkdate($mm, $dd, $yyyy)) throw new LogicException("Invalid Date format: $data. Dopo l'explode(): mm=$mm, dd=$dd, yyyy=$yyyy");
	
	// controllo time ben formati (start e end)
	list($shh, $smm, $sss) = explode(":", $start);
	if($shh<0 || $shh>23 || $smm!=0 || $sss!=0) throw new LogicException("Invalid Time format for start: $start");
	
	list($ehh, $emm, $ess) = explode(":", $end);
	if($ehh<0 or $ehh>24 or $emm!=0 or $ess!=0) throw new LogicException("Invalid Time format for end: $end");
	
	// controllo che data e ora non siano nel passato
	if(isPast($dd, $mm, $yyyy)) throw new LogicException ("La data per cui si vuole prenotare è passata!!!");
	if(currentDay($dd, $mm, $yyyy) && $start < date("G")) throw new LogicException ("L'orario per cui si vuole prenotare è già passato");
	
	// controllo che end sia successivo a start
	if($shh >= $ehh) throw new LogicException ("L'orario di fine prenotazione è antecedente o coincidente all'orario di inizio");
	
	if($type === 'Prova') {	
		// controllo che start e end siano nelle fasce orarie prestabilite per le PROVE
		if(($shh < STARTPROVE0 || ($shh >= ENDPROVE0 && $shh < STARTPROVE1))
			|| ($ehh < STARTPROVE0+1 || ($ehh > ENDPROVE0 && $ehh < STARTPROVE1+1))) throw new InvalidArgumentException ("Attenzione: Non si può effettuare questa prenotazione. L'orario di prenotazione inserito è nella fascia oraria riservata alle lezioni.");
	}
	elseif($type === 'Lezione') {
		if($shh < STARTLEZIONI or $shh > ENDLEZIONI or $ehh < STARTLEZIONI or $ehh > ENDLEZIONI) throw new InvalidArgumentException ("Attenzione: Non si può effettuare questa prenotazione. L'orario di prenotazione inserito è nella fascia oraria riservata alle sessioni di prova.");
	}
	else throw new LocigException("Il tipo di prenotazione deve essere \'Prova\' o \'Lezione\'. E' stato inserito: $type");
	
	
	/* *** Begin Transaction *** 
	 * Convalida coerenza dei dati inseriti rispetto al database,
	 * se sono corretti procede alla INSERT */
	 
	
	try {
		$conn = RealSoundDBConnection::getInstance()->getConnection();  
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$conn->beginTransaction();
		// Fai qui la roba che devi fare!!
		
		// vincoli da rispettare prima di fare la INSERT
		$QUERY_userExists = '(SELECT EXISTS (SELECT 1 FROM User WHERE UserId = :utente))';
		$QUERY_roomExists = '(SELECT EXISTS (SELECT 1 FROM Sala WHERE SalaId = :sala))';
		$QUERY_userNotBusy1 = '(SELECT EXISTS
								(SELECT 1
									FROM SessioneProva 
									WHERE Utente = :user AND Data = :data 
									AND ((CAST(:start AS time) BETWEEN OraInizio AND SUBTIME(OraFine, \'00:00:01\'))
									OR (CAST(:end AS time) BETWEEN ADDTIME(OraInizio, \'00:00:01\') AND OraFine) 
									OR (CAST(:start AS time) <= OraInizio AND CAST(:end AS time) >= OraFine)) 
								))';
		
		// Fatto così sto assumento che l'utente che prenota una SessioneProva sia sempre un common user e mai un docente
		// In realtà queste due varianti, nel caso della Prova, andrebbero eseguite entrambe.
		if($type === 'Prova') {
			$QUERY_userNotBusy2 = '(SELECT EXISTS
									(SELECT 1
										FROM Lezione as L JOIN Iscrizione as I ON L.LezioneId = I.Classe 
										WHERE I.Studente = :user AND Data = :data 
										AND ((CAST(:start AS time) BETWEEN OraInizio AND SUBTIME(OraFine, \'00:00:01\'))
										OR (CAST(:end AS time) BETWEEN ADDTIME(OraInizio, \'00:00:01\') AND OraFine) 
										OR (CAST(:start AS time) <= OraInizio AND CAST(:end AS time) >= OraFine)) 
									))';
		}
		// Qui invece sto assumento, cosa piuttosto ovvia, che un Insegnante possa essere impegnato solo in una SUA lezione.
		// Si assume cioè che un Insegnante non possa essere l'allievo di un altro Insegnante (quindi impegnato come STUDENTE in una lezione).
		elseif($type === 'Lezione') {
			$QUERY_userNotBusy2 = '(SELECT EXISTS
									(SELECT 1
										FROM Lezione 
										WHERE Insegnante = :user AND Data = :data 
										AND ((CAST(:start AS time) BETWEEN OraInizio AND SUBTIME(OraFine, \'00:00:01\'))
										OR (CAST(:end AS time) BETWEEN ADDTIME(OraInizio, \'00:00:01\') AND OraFine) 
										OR (CAST(:start AS time) <= OraInizio AND CAST(:end AS time) >= OraFine)) 
									))';
		}
		else throw new LogicalException("Il tipo di prenotazione deve essere \'Prova\' o \'Lezione\'. E' stato inserito: $type");
		
		$QUERY_isRoomBusy1 = '(SELECT EXISTS 
								(SELECT 1 
									FROM SessioneProva 
									WHERE Room = :room AND Data = :data 
									AND ((CAST(:start AS time) BETWEEN OraInizio AND SUBTIME(OraFine, \'00:00:01\')) 
									OR (CAST(:end AS time) BETWEEN ADDTIME(OraInizio, \'00:00:01\') AND OraFine)
									OR (CAST(:start AS time) <= OraInizio AND CAST(:end AS time) >= OraFine ))
								))';
		$QUERY_isRoomBusy2 = '(SELECT EXISTS 
								(SELECT 1 
									FROM Lezione 
									WHERE Room = :room AND Data = :data 
									AND ((CAST(:start AS time) BETWEEN OraInizio AND SUBTIME(OraFine, \'00:00:01\')) 
									OR (CAST(:end AS time) BETWEEN ADDTIME(OraInizio, \'00:00:01\') AND OraFine)
									OR (CAST(:start AS time) <= OraInizio AND CAST(:end AS time) >= OraFine ))
								))';
		

		// 0. userExists
		$stm = $conn->prepare($QUERY_userExists);
		$stm->bindParam(':utente', $user);
		$stm->execute();
		
		$ret = $stm->fetch();
		
		if(!$ret[0]) throw new LogicException("User don't exist in database");
		
		
		// 1. roomExists
		$stm = $conn->prepare($QUERY_roomExists);
		$stm->bindParam(':sala', $room);
		$stm->execute();
		
		$ret = $stm->fetch();
		
		if(!$ret[0]) throw new LogicException("Room don't exist in database");
		
		
		// 2. userNotBusy1 (non ha un'altra sessione prenotata nella stessa ora)
		
		$stm = $conn->prepare($QUERY_userNotBusy1);
		$stm->bindParam(':user', $user);
		$stm->bindParam(':data', $data);
		$stm->bindParam(':start', $start);
		$stm->bindParam(':end', $end);
		
		$stm->execute();
		
		$ret = $stm->fetch();
		
		if($ret[0]) {
			$ExceptionError = USER_INPUT;
			throw new InvalidArgumentException("Attenzione! Risulta che l'orario specificato si sovrapponga ad un'altra Sua prenotazione. Per favore annulli la prenotazione precedente, prima di effettuare questa prenotazione.");
		}
		
		// 3. userNotBusy2 (non è iscritto ad una lezione nella stessa ora)
		$stm = $conn->prepare($QUERY_userNotBusy2);
		$stm->bindParam(':user',$user);
		$stm->bindParam(':data',$data);
		$stm->bindParam(':start',$start);
		$stm->bindParam(':end',$end);
		
		$stm->execute();
		
		$ret = $stm->fetch();
		
		if($ret[0]) {
			$ExceptionError = FATAL;
			throw new InvalidArgumentException("Attenzione! L'utente risulta già impegnato in una lezione nell'orario specificato. Questa è una condizione fortemente anomala: contatta i tecnici.");
		}
		
		// 4. isRoomBusy1 (sala già prenotata per Sessione Prova)
		$stm = $conn->prepare($QUERY_isRoomBusy1);
		$stm->bindParam(':room',$room);
		$stm->bindParam(':data',$data);
		$stm->bindParam(':start',$start);
		$stm->bindParam(':end',$end);
		
		$stm->execute();
		
		$ret = $stm->fetch(); // o fetchAll() as needed
		
		if($ret[0]) throw new RunTimeException("Attenzione! La sala selezionata risulta già occupata negli orari specificati. Probabilmente qualcun altro ha effettuato la Sua stessa prenotazione pochi istanti fa.");
		
		
		// 4. isRoomBusy2 (sala già prenotata per Lezione)
		$stm = $conn->prepare($QUERY_isRoomBusy2);
		$stm->bindParam(':room',$room);
		$stm->bindParam(':data',$data);
		$stm->bindParam(':start',$start);
		$stm->bindParam(':end',$end);
		
		$stm->execute();
		
		$ret = $stm->fetch(); // o fetchAll() as needed
		
		if($ret[0]) {
			$ExceptionError = FATAL;
			throw new InvalidArgumentException("Attenzione! La sala prenotata risulta impegnata per una lezione nell'orario specificato. Questa è una condizione fortemente anomala: contattare i tecnici");
		}
		
		// Finiti i controlli... INSERT!!
		if($type === 'Prova') {
			$insert = 'INSERT INTO SessioneProva (Utente, Room, OraInizio, OraFine, Data) VALUES (:user, :room, :start, :end, :data);';
		}
		elseif ($type === 'Lezione') { 
			$insert = 'INSERT INTO Lezione (Insegnante, Data, OraInizio, OraFine, Room) VALUES (:user, :data, :start, :end, :room);';
		}
		$stm = $conn->prepare($insert);
		$stm->bindParam(':user',$user);
		$stm->bindParam(':room',$room);
		$stm->bindParam(':start',$start);
		$stm->bindParam(':end',$end);
		$stm->bindParam(':data',$data);
		
		$stm->execute();
		
		if(!$stm) throw new Exception("Nonostante tutti i controlli, la inserzione in Database è fallita.");
		
		// Fine transaction
		$conn->commit();
		return 1;
		
		} catch (InvalidArgumentException $e) {
		  $conn->rollBack();
		  switch ($ExceptionError) {
			  case FATAL :
				  throw new LogicException($e->getMessage());
			  break;
			  case USER_INPUT :
				  echo "<br>" . $e->getMessage() . "<br>";
				  exit(1);
			  break;
			  default :
				  throw new LogicException("Attenzione! Sollevata eccezione non prevista (e quindi non gestita) in un caso InvalidArgumentException");
			  break;
		  }
		  }
		  catch (RunTimeException $e) {
		  $conn->rollBack();
		  echo "<br>" . $e->getMessage() . "<br>";
		  }
		  catch (LogicException $e) {
		  $conn->rollBack();
		  echo "[DEV_ERROR] Transaction Failed: " . $e->getMessage();
		  exit(1);
		  }
		  catch (Exception $e) {
		  $conn->rollBack();
		  throw new Exception($e->getMessage());
		  }
}

function secureInsertLezione($prof, $room, $data, $start, $end){
	$type = "Lezione";
	abs_securePrenotazione($prof, $room, $data, $start, $end, $type);
}

function secureInsertProva($user, $room, $data, $start, $end) {
	$type = "Prova";
	abs_securePrenotazione($user, $room, $data, $start, $end, $type);
}

function secureIscrizione($lezione, $studente) {
	/* *** Validazione dati immessi ***
	 * indipendentemente dal database (fuori transazione) */
	
	// controllo tipi lezione e studente
	if(!is_int($lezione)) throw new LogicException("Lezione not int");
	
	if(!is_int($studente)) throw new LogicException("Studente not int");
	
	/* *** Begin Transaction *** 
	 * Convalida coerenza dei dati inseriti rispetto al database,
	 * se sono corretti procede alla INSERT */
	 
	
	try {
		$conn = RealSoundDBConnection::getInstance()->getConnection();  
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$conn->beginTransaction();
		// Fai qui la roba che devi fare!!
		
		$QUERY_studenteExists = '(SELECT EXISTS (SELECT 1 FROM User WHERE UserId = :studente))';
		$QUERY_lezioneExists = '(SELECT EXISTS (SELECT 1 FROM Lezione WHERE LezioneId = :lezione))';

		// Qui mancano controlli. Va verificato che lo studente che si stà iscrivendo, non sia impegnato
		// a) in una sessione prova nello stesso orario della lezione che si vuole prenotare
		// b) in un'altra lezione nello stesso orario della lezione che si vuole prenotare
		
		// per farlo va fatta una query ad hoc per prendere i dati (data, ora inizio, ora fine) della lezione
		// POI si replicano le query UserNotBusy della abs_securePrenotazione()
		
		// 0. studenteExists
		$stm = $conn->prepare($QUERY_studenteExists);
		$stm->bindParam(':studente', $studente);
		$stm->execute();
		
		$ret = $stm->fetch();
		
		if(!$ret[0]) throw new LogicException("Student don't exist in database");
		
		// 1. lezioneExists
		$stm = $conn->prepare($QUERY_lezioneExists);
		$stm->bindParam(':lezione', $lezione);
		$stm->execute();
		
		$ret = $stm->fetch();
		
		if(!$ret[0]) throw new LogicException("Lesson don't exist in database");
		
		// Finiti i controlli... INSERT!!
		$insert = 'INSERT INTO Iscrizione (Studente, Classe) VALUES (:studente, :lezione);';
		$stm = $conn->prepare($insert);
		$stm->bindParam(':studente',$studente);
		$stm->bindParam(':lezione',$lezione);
		
		$stm->execute();
		
		if(!$stm) throw new Exception("Nonostante tutti i controlli, la inserzione in Database è fallita.");
		
		// Fine transaction
		$conn->commit();
		return 1;
		
	}
	catch(LogicException $e){
		$conn->rollBack();
		echo "[DEV_ERROR] Transaction Failed: " . $e->getMessage();
		exit(1);
	}
	catch (Exception $e) {
		$conn->rollBack();
		throw new Exception($e->getMessage());
	}
	
}


// Valida i dati immessi e inserisce il nuovo utente in database.
// Possibili valori di ritorno: true (tutto bene), false (mancata accettazione della privacy), 
// vettore di errore nell'inserzione dei dati
function validateInsertUser($POSTdata) {
	$refresh = 0; // impostata a 1 appena fallisce un controllo
	$error_POST = $POSTdata; // qui ritorno gli errori per ogni campo
	
	
	/* *** Accettazione trattamento dati personali *** */
	if(!isset($_POST['privacy'])) return false; // unico caso in cui ritorna false
	
	/* *** Validazione dati immessi ***
	 * indipendentemente dal database */
	// controlla:
	// 1) che ogni campo obbligatorio non sia NULL
	// 2) che sia del tipo giusto
	// 3) che sia della giusta dimensione
	for($i=0; $i<count($POSTdata); $i++) {
		switch ($i) {
			case USERNAME:{
				if(empty($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = ISNULL;
				}
				elseif(!is_string($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = NOTSTRING;
				}
				elseif(strlen($POSTdata[$i]) > 80) {
					$refresh = 1;
					$error_POST[$i] = OVER;
				}
			}break;
			case NOME: {
				if(empty($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = ISNULL;
				}
				elseif(!is_string($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = NOTSTRING;
				}
				elseif(strlen($POSTdata[$i]) > 45) {
					$refresh = 1;
					$error_POST[$i] = OVER;
				}
			}break;
			case COGNOME: {
				if(empty($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = ISNULL;
					break;
				}
				elseif(!is_string($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = NOTSTRING;
					break;
				}
				elseif(strlen($POSTdata[$i]) > 45) {
					$refresh = 1;
					$error_POST[$i] = OVER;
				}
			}break;
			case PROFESSIONE:
				$a=$POSTdata[$i];
				if(empty($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = ISNULL;
					break;
				}
				elseif(!is_string($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = NOTSTRING;
					break;
				}
				elseif(strlen($POSTdata[$i]) > 45) {
					$refresh = 1;
					$error_POST[$i] = OVER;
				}
				break;
			case BDATE:
				list($yyyy, $mm, $dd) = explode("-", $POSTdata[$i]);
				
				if(empty($yyyy) and empty($mm) and empty($dd)) {
					$refresh = 1;
					$error_POST[$i] = ISNULL;
					break;
				}
				elseif(!checkdate($mm, $dd, $yyyy)) {
					$refresh = 1;
					$error_POST[$i] = NOTDATE;
				}
				break;
			case BCITY:
				if(empty($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = ISNULL;
					break;
				}
				elseif(!is_string($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = NOTSTRING;
					break;
				}
				elseif(strlen($POSTdata[$i]) > 45) {
					$refresh = 1;
					$error_POST[$i] = OVER;
				}
				break;
			case BPROV:
				if(empty($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = ISNULL;
					break;
				}
				elseif(!is_string($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = NOTSTRING;
					break;
				}
				elseif(strlen($POSTdata[$i]) > 45) {
					$refresh = 1;
					$error_POST[$i] = OVER;
				}
				break;
			case BSTATE:
				if(empty($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = ISNULL;
					break;
				}
				elseif(!is_string($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = NOTSTRING;
					break;
				}
				elseif(strlen($POSTdata[$i]) > 45) {
					$refresh = 1;
					$error_POST[$i] = OVER;
				}
				break;
			case RVIA:
				if(empty($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = ISNULL;
					break;
				}
				elseif(!is_string($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = NOTSTRING;
					break;
				}
				elseif(strlen($POSTdata[$i]) > 45) {
					$refresh = 1;
					$error_POST[$i] = OVER;
				}break;
			case RCIVICO:
				if(empty($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = ISNULL;
					break;
				}
				elseif(!is_string($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = NOTSTRING;
					break;
				}
				elseif(strlen($POSTdata[$i]) > 10) {
					$refresh = 1;
					$error_POST[$i] = OVER;
				}break;
			case RCAP:
				if(empty($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = ISNULL;
					break;
				}
				elseif(!is_numeric($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = NOTINT;
					break;
				}
				elseif(($POSTdata[$i] < 0) or ($POSTdata[$i] > 99999)) {
					$refresh = 1;
					$error_POST[$i] = OVER;
				}break;
			case RCITY:
				if(empty($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = ISNULL;
					break;
				}
				elseif(!is_string($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = NOTSTRING;
					break;
				}
				elseif(strlen($POSTdata[$i]) > 45) {
					$refresh = 1;
					$error_POST[$i] = OVER;
				}break;
			case RPROV:
				if(empty($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = ISNULL;
					break;
				}
				elseif(!is_string($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = NOTSTRING;
					break;
				}
				elseif(strlen($POSTdata[$i]) > 45) {
					$refresh = 1;
					$error_POST[$i] = OVER;
				}break;
			case RSTATE:
				if(empty($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = ISNULL;
					break;
				}
				elseif(!is_string($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = NOTSTRING;
					break;
				}
				elseif(strlen($POSTdata[$i]) > 45) {
					$refresh = 1;
					$error_POST[$i] = OVER;
				}break;
			case DVIA:
				if(!empty($POSTdata[$i])) {
					if(!is_string($POSTdata[$i])) {
						$refresh = 1;
						$error_POST[$i] = NOTSTRING;
						break;
					}
					elseif(strlen($POSTdata[$i]) > 45) {
						$refresh = 1;
						$error_POST[$i] = OVER;
					}
				}
				break;
			case DCIVICO:
				if(!empty($POSTdata[$i])) {
					if(!is_string($POSTdata[$i])) {
						$refresh = 1;
						$error_POST[$i] = NOTSTRING;
						break;
					}
					elseif(strlen($POSTdata[$i]) > 10) {
						$refresh = 1;
						$error_POST[$i] = OVER;
					}
				}
				break;
			case DCAP:
				if(!empty($POSTdata[$i])) {
					if(!is_numeric($POSTdata[$i])) {
						$refresh = 1;
						$error_POST[$i] = NOTINT;
					}
					elseif(($POSTdata[$i] < 0) or ($POSTdata[$i] > 16777215)) {
						$refresh = 1;
						$error_POST[$i] = OVER;
					}
				}
				break;
			case DCITY:
				if(!empty($POSTdata[$i])) {
					if(!is_string($POSTdata[$i])) {
						$refresh = 1;
						$error_POST[$i] = NOTSTRING;
						break;
					}
					elseif(strlen($POSTdata[$i]) > 45) {
						$refresh = 1;
						$error_POST[$i] = OVER;
					}
				}
				break;
			case DPROV:
				if(!empty($POSTdata[$i])) {
					if(!is_string($POSTdata[$i])) {
						$refresh = 1;
						$error_POST[$i] = NOTSTRING;
						break;
					}
					elseif(strlen($POSTdata[$i]) > 45) {
						$refresh = 1;
						$error_POST[$i] = OVER;
					}
				}
				break;
			case DSTATE:
				if(!empty($POSTdata[$i])) {
					if(!is_string($POSTdata[$i])) {
						$refresh = 1;
						$error_POST[$i] = NOTSTRING;
						break;
					}
					elseif(strlen($POSTdata[$i]) > 45) {
						$refresh = 1;
						$error_POST[$i] = OVER;
					}
				}
				break;
			case CMAIL:
				if(empty($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = ISNULL;
					break;
				}
				elseif(filter_var($POSTdata[$i], FILTER_VALIDATE_EMAIL)==false) {
					$refresh = 1;
					$error_POST[$i] = NOTMAIL;
					break;
				}
				elseif(strlen($POSTdata[$i]) > 45) {
					$refresh = 1;
					$error_POST[$i] = OVER;
				}
				break;
				
			case CPHONE:
				if(empty($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = ISNULL;
					break;
				}
				elseif(!is_string($POSTdata[$i])) {
					$refresh = 1;
					$error_POST[$i] = NOTSTRING;
					break;
				}
				elseif(strlen($POSTdata[$i]) > 45) {
					$refresh = 1;
					$error_POST[$i] = OVER;
				}
				break;
				/* qui volendo... controllo che sia una stringa composta solo da cifre numeriche tranne la prima (+) */
		}
		
	}
	
	/* *** Validazione USERNAME (non già usato nel database) *** */
	$sql = '(SELECT EXISTS (SELECT 1 FROM User WHERE UserName = :userName))';
	$uname = $POSTdata[USERNAME];
	 
	$conn = RealSoundDBConnection::getInstance()->getConnection();
	$stm = $conn->prepare($sql);
	
	$stm->bindParam(':userName', $uname);
	$stm->execute();
	$row = $stm->fetch();
	
	if($row[0]) {
		$refresh = 1;
		$error_POST[USERNAME] = USED;
	}
	
	if($refresh) return $error_POST;
	else {
		try {
			$findNumTessera = 'SELECT MAX(NumTessera) FROM User';
			$insertContatto = 'INSERT INTO User2Contatto (Utente, Tipo, Value) VALUES (:userid, :tipo, :value)';
			$insertUser = 'INSERT INTO User (UserName, 
											Type, 
											Nome, 
											Cognome, 
											Professione,
											NumTessera, 
											NascitaDate, 
											NascitaCity, 
											NascitaStato, 
											NascitaProvincia, 
											ResidenzaVia, 
											ResidenzaCivico, 
											ResidenzaCAP, 
											ResidenzaCittà, 
											ResidenzaProvincia, 
											ResidenzaStato, 
											DomicilioVia, 
											DomicilioCivico, 
											DomicilioCAP, 
											DomicilioCittà, 
											DomicilioProvincia, 
											DomicilioStato,
											Flag
											)
									VALUES (:USERNAME,
											:TYPE,
											:NOME,
											:COGNOME,
											:PROFESSIONE,
											:NUMTESSERA,
											:BDATE,
											:BCITY,
											:BSTATE,
											:BPROV,
											:RVIA,
											:RCIVICO,
											:RCAP,
											:RCITY,
											:RPROV,
											:RSTATE,
											:DVIA,
											:DCIVICO,
											:DCAP,
											:DCITY,
											:DPROV,
											:DSTATE,
											:FLAG
									)';
			$conn = RealSoundDBConnection::getInstance()->getConnection();
			$conn->beginTransaction();
			
			/* assegno il numero di tessera */
			$stm = $conn->prepare($findNumTessera);
			$stm->execute();
			$row = $stm->fetch();
			$numtessera = $row[0] + 1;
			
			/* inserisco l'utente */
			$stm = $conn->prepare($insertUser);
		  
			$stm->bindParam(":USERNAME",$POSTdata[USERNAME]);
			$v=COMMON;
			$stm->bindParam(":TYPE",$v);
			$stm->bindParam(":NOME",$POSTdata[NOME]);
			$stm->bindParam(":COGNOME",$POSTdata[COGNOME]);
			$stm->bindParam(":PROFESSIONE",$POSTdata[PROFESSIONE]);
			$stm->bindParam(":NUMTESSERA",$numtessera);
			$stm->bindParam(":BDATE",$POSTdata[BDATE]);
			$stm->bindParam(":BCITY",$POSTdata[BCITY]);
			$stm->bindParam(":BSTATE",$POSTdata[BSTATE]);
			$stm->bindParam(":BPROV",$POSTdata[BPROV]);
			$stm->bindParam(":RVIA",$POSTdata[RVIA]);
			$stm->bindParam(":RCIVICO",$POSTdata[RCIVICO]);
			$stm->bindParam(":RCAP",$POSTdata[RCAP]);
			$stm->bindParam(":RCITY",$POSTdata[RCITY]);
			$stm->bindParam(":RPROV",$POSTdata[RPROV]);
			$stm->bindParam(":RSTATE",$POSTdata[RSTATE]);
			$stm->bindParam(":DVIA",$POSTdata[DVIA]);
			$stm->bindParam(":DCIVICO",$POSTdata[DCIVICO]);
			$stm->bindParam(":DCAP",$POSTdata[DCAP]);
			$stm->bindParam(":DCITY",$POSTdata[DCITY]);
			$stm->bindParam(":DPROV",$POSTdata[DPROV]);
			$stm->bindParam(":DSTATE",$POSTdata[DSTATE]);
			
			$flag = SOSPESO;
			if(isset($_POST['marketing'])) $flag .= MARKETING;
			$stm->bindParam(":FLAG", $flag);							
			
			$ris = $stm->execute();
			$row = $stm->fetch();
			
			/* inserisco i contatti */
			// MAIL
			$userid = db_getUserIdByName($POSTdata[USERNAME]);
			$stm = $conn->prepare($insertContatto);
			$stm->bindParam(":userid",$userid);
			$t=MAIL;
			$stm->bindParam(":tipo", $t);
			$stm->bindParam(":value", $POSTdata[CMAIL]);
			$stm->execute();
			
			// PHONE
		 //   $stm = $conn->prepare($insertContatto);
			$stm->bindParam(":userid",$userid);
			$t=PHONE;
			$stm->bindParam(":tipo", $t);
			$stm->bindParam(":value", $POSTdata[CPHONE]);
			$stm->execute();
			
			$conn->commit();
			return true;
		}
		catch (Exception $e) {
			$conn->rollBack();
			throw new Exception($e->getMessage());
		}
	} // chiuso else
} // chiusa funzione

