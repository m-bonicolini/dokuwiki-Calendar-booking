<?php

require_once('realsound-db-functions.php');
require_once('realsound-utils.php');


interface Calendar
{
/*
 * Gli scopi di questo metodo sono:
 * 1-Disegnare la tabella corrispondete al mese della data passata(tramite 3 interi)
 * 2-Associare ai giorni del mese preso in esame link per le opportune azioni
 * di calendarAction
 * 
 * */
	public function buildCalendar($m,$y,$id,$opzioni);
	
/*Lo scopo di questo metodo è quello di associare a un giorno noto
 * azioni specifiche */	
	public function calendarAction($d,$m,$y,$action); 
 
}


/* E' una classe astratta che implementa parzialmente Calendar.Definisce
 * una strategia comportamentale per il metodo buildCalendar();
 * 
 * METODI ASTRATTI:
 * 
 * protected printDay($d,$index,$m,$y)  --> produce disegna l'ipertesto corrispondente a una 
 * casella della tabella disegnata da buildCalendar()
 * Input :
 * $d--> Giorno preso in esame
 * $index --> Giorno della settimana espresso come numero (da 0 a 6)
 * $m-----> mese preso in esame
 * $y---->anno preso in esame
 * 
 * 
 * METODI IMPLEMENTATI:
 * 
 * public buildCalendar($d,$m,$y) --> Implementa un algoritmo di disegno 
 * del calendario utilizzando come funzione ausiliaria
 * printDay().
 * 
 * NB: Per come è implementata la build calendar il metodo printday()
 * deve provvedere a stampare l'ipertesto corrispondente all'intero
 * disegno della cella aprendo/chiudento opportunamente il tag <td> </td> 
 * e chiduere il blocco html <td> </td>
 * 
 *  */
abstract class AbsCalendar implements Calendar
{
	abstract protected function printDay($d,$m,$y,$index,$id,$action);
	abstract protected function usage($action);
	
	protected function nextMonth($m,$y)
	{
		
		$m+=1;
		if($m==13) 
		{
			$m=1;
			$y+=1;
		}
			
		$ret[0]=$m;
		$ret[1]=$y;
		return $ret;
	}
	
	protected function lastMonth($m,$y)
	{
		$m-=1;
		if(($m ==0))
		{
			$m=12;
			$y-=1;
		}
		$ret[0]=$m;
		$ret[1]=$y;
		return $ret;
	}
	
	public function buildCalendar($m,$y,$id,$action=null)
	{
		
		if((($m >12) || ($m<=0))) throw new InvalidArgumentException("Non esistono mesi minori di 0!Mese inserito=$m",1);		
		$myNext=$this->nextMonth($m,$y);
		$myLast=$this->lastMonth($m,$y);

		
		$lM=$myLast[0];
		$lY=$myLast[1];
		$nM=$myNext[0];
		$nY=$myNext[1];
		$y2=$y-1;
		$y3=$y+1;
		
		
		$this->usage($action);
		echo "<a name=\"scegligiorno\"></a>";
		/*Dichiarazione della tabella e disegno della prima riga*/
		echo "<table class=\"inline\" border=2 >";
		echo "<tr><th colspan=7> 
		Mese:&emsp;<a href=\"?id=$id&do=prev&&mes=$lM&year=$lY#scegligiorno\"><img src=\"leftArrow.png\" /></a>
		$m
		<a href=\"?id=$id&do=next&&mes=$nM&year=$nY#scegligiorno\"><img src=\"rightArrow.png\" /></a> 
		&emsp;&emsp;Anno:&emsp;
		<a href=\"?id=$id&do=prev&&mes=$m&year=$y2#scegligiorno\"><img src=\"leftArrow.png\" /></a>
		$y<a href=\"?id=$id&do=next&&mes=$m&year=$y3#scegligiorno\"><img src=\"rightArrow.png\" /></a> 
		</th></tr>";
		
		echo "<tr>
		<td class=\"col0 centeralign\" width=109>Lunedì</td>
		<td class=\"col1 centeralign\" width=109>Martedì</td>
		<td class=\"col2 centeralign\" width=109>Mercoledì</td>
		<td class=\"col3 centeralign\" width=109>Giovedì</td>
		<td class=\"col4 centeralign\" width=109>Venerdì</td>
		<td class=\"col5 centeralign\" width=109>Sabato</td>
		<td class=\"col6 centeralign\" width=109>Domenica</td>
		</tr>";
		try 
		{
			
			/*Calcola lo shift del primo giorno del mese*/
			$firstday= cal_to_jd(CAL_GREGORIAN,$m,01,$y)%7;/*0 lun,1 mar, 3 mer...6 dom*/ 
			echo "<tr >";
		
			/*Disegna i giorni nulli precedenti al $firstday*/
			for($i=0; $i<$firstday; $i++)
				echo "<td class=\"col$i centeralign\" bgcolor=\"gray\"> / </td>";
		
			/*A partire dal primo giorno del mese termina la prima settimana
			* Si tiene traccia del numero di giorni disegnati*/
			$j=1;
			for($i=$firstday; $i< 7;$i++ )
			{	
				if($i!=6)
					$this->printDay($j,$m,$y,$i,$id,$action);
				else
				{
					if(currentDay($j,$m,$y))
							$border="border:4px solid #000000;" ;
						
					echo "<td  style=\"cursor:hand;$border\" bgcolor=\"gray\" >$j</td>";
				}
				$j++;
			}
			echo "</tr>";
 
			/*Viene generata la struttura time del mese preso in esame*/ 
			if($m<10)
				$mydate = mktime(0, 0, 0, "0$m",01,$y);
			else
				$mydate = mktime(0, 0, 0, "$m",01,$y);

			/*Viene determinato il numero totale di giorni che compongono
			* il mese  */
			$nday=date("t",$mydate); 
		
			/*Finchè il numero di giorni disegnati è minore del totale cicla
			* Ogni iterazione parte da lunedì (disegna riga per riga)*/
			while($j<=$nday)
			{
				echo "<tr>";
				if($j+7 <= $nday )	/*E' possibile il disegno di un'intera settimana? */
				{
					for($i=0; $i<7;$i++ )
					{
						if($i!=6)
							$this->printDay($j,$m,$y,$i,$id,$action);
						else
							echo "<td class=\"  centeralign\"   bgcolor=\"gray\" >$j</td>" ;
						$j++;
					}
				}
				else 
				{
					$calLen=($nday-$j);
					for($i=0; $i <= $calLen;$i++ )
					{
						$this->printDay($j,$m,$y,$i,$id,$action);		
						$j++;
					}
 		
					for($i=$calLen+1; $i < 7;$i++ )
						echo "<td class=\"col$i centeralign\" bgcolor=\"gray\"> / </td>";
				}	
				echo "</tr>";
			}
		}
		catch(Exception $e)
		{
			echo "[DEV_ERROR] AbsCalendar-->buildCalendar()";
			throw new LogicException($e->getMessage());
		}
		echo "</table>";
	}

} /*end class*/

abstract class RealSoundCalendar extends AbsCalendar
{
	   /*metodo privato ausiliario di printDay
	 *  Ritorna true se è possibile prenotare per quel giorno
	 *  Altrimenti ritorna false*/
	protected function checkSala($d,$m,$y)
	{
		/*$i = ID_SALA*/
		$sale=db_getAllSala();
		$mycount= count($sale);
		for($i=1;$i<($mycount+1);$i++)
		{	
			if(currentDay($d,$m,$y))
			{
				/*checkslot() controlla la disponibilità delle
				 * sale nelle fascie orarie disponibili*/
				if($this->checkslot($i,$d,$m,$y)) 
					return true;
			}	
			else /*non è il giorno corrente, posso fare un controllo pigro*/
			{
				if(!db_existRoomInSessione($i, $d, $m, $y))
					return true;
				else
				{
			
					if($this->checkslot($i,$d,$m,$y))
						return true;
				}
			}	
		}
		return false;
	}
	
	protected function checkSalaLezione($d,$m,$y)
	{
		/*$i = ID_SALA*/
		$sale=db_getAllSala();
		$mycount= count($sale);
		for($i=1;$i<($mycount+1);$i++)
		{	
			if(currentDay($d,$m,$y))
			{
				/*checkslot() controlla la disponibilità delle
				 * sale nelle fascie orarie disponibili*/
				if($this->checkslotLezione($i,$d,$m,$y)) 
					return true;
			}	
			else /*non è il giorno corrente, posso fare un controllo pigro*/
			{
				if(!db_existRoomInLezione($i, $d, $m, $y))
					return true;
				else
				{
					if($this->checkslotLezione($i,$d,$m,$y))
						return true;
				}
			}	
		}
		return false;
	}
	
	/*checkslot() controlla la disponibilità delle
	*   sale nelle fascie orarie disponibili
	*   Ha i seguenti comportamenti:
	*   1-ritorna true se , almeno in un ora del giorno(tra quelle concesse)
	*   è libera la sala passata come argomento
	*   2-ritorna false se non è possibile prenotare la sala nel giorno selezionato
	*   3-Se è il giorno corrente salta le ore riferite al passato
	*   
	* */
	 protected  function checkslot($room,$d,$m,$y)
	{
		if($d <10)
			$mydata=db_AllSessione($room,  "0$d", $m, $y);
		else
			$mydata=db_AllSessione($room,  $d, $m, $y);
		
		/*prima fascia oraria*/
		for($i=9;$i<13;$i++)
			if((!(currentDay($d,$m,$y) && (date("G")>=$i))) && $this->isAvaiableSlot($mydata,$i))
				return true;
		
		/*seconda*/
		for($i=20;$i<24;$i++)
			if((!(currentDay($d,$m,$y) && (date("G")>=$i)))&&($this->isAvaiableSlot($mydata,$i)))
				return true;
		
		return false;		
	}
  
	protected  function checkslotLezione($room,$d,$m,$y)
	{
		if($d <10)
			$mydata=db_AllLezione($room,  "0$d", $m, $y);
		else
			$mydata=db_AllLezione($room,  $d, $m, $y);
		
		/*prima fascia oraria*/
		for($i=STARTLEZIONI;$i<ENDLEZIONI;$i++)
			if((!(currentDay($d,$m,$y) && (date("G")>=$i)))&&($this->isAvaiableSlot($mydata,$i)))
				return true;
		
		return false;		
	}
	
	/*controlla la disponibilità di un singolo slot*/
	protected function isAvaiableSlot($v,$value)
	{  	
		for($i=0;$i<count($v);$i++)
		{
			$myvar=$v[$i];  
			$st=$myvar[0]; 
			$et=$myvar[1]; 
			list($sh,$sm,$ss)= explode(":",$st);
			list($eh,$em,$es) = explode(":",$et); 
		 
			if($value>=$sh && $value<$eh)
				return false;
		}      
		return true;
	}

	 protected function getLezioneByTime($db,$stime)
	 {
		for($i=0;$i<count($v);$i++)
		{
			$myvar=$v[$i];  
			$st=$myvar[0]; 
			$et=$myvar[1]; 
			list($sh,$sm,$ss)= explode(":",$st);
			list($eh,$em,$es) = explode(":",$et); 
			
			if($value==$stime)
				return $v[$i];
		}      
		return null;
	 }
	 	

	protected function buildGantt($d,$m,$y,$len0,$len1,$q,$start0,$start1)
	{
		if($q==0)
			$q=1;
			
		/*confguro la prima riga della tabella*/
		echo "<br><center></central><table class=\"inline\" align=\"center\" >";
		echo "<tr>";
		echo "<td class=\" centeralign\" bgcolor=\"gray\"> </td>";
		for($i=$start0;$i<$start0+$len0;$i=($i+$q))
		{
			$myvar=$i+$q;
			if($i<10)
			{
				echo "<td class=\" centeralign\" >0$i-";
				if($myvar<10)
					echo "0$myvar</td>";
				else
					echo "$myvar</td>";
			}
			else 
				echo "<td class=\"  centeralign\" >$i-$myvar </td>";
		}
		echo "<td class=\"  centeralign\"   bgcolor=\"gray\" >...</td>";
		for($i=$start1;$i<$start1+$len1;$i=($i+$q))
		{
	  
			$myvar=$i+$q;
			if($i<10)
			{
				echo "<td class=\"  centeralign\" >0$i-";
				if($myvar<10)
					echo "0$myvar</td>";
				else
					echo "$myvar</td>";
			}
			else 
				echo "<td class=\"  centeralign\" >$i-$myvar </td>";
		}
		echo "</tr>";
		/*fine prima riga*/
		$sale=db_getAllSala();
		$mycount= count($sale);
		for($j=1;$j<$mycount+1;$j++)
		{
			if($d <10)
				$mydata=db_AllSessione($j,"0$d", $m, $y);
			else
				$mydata=db_AllSessione($j,$d, $m, $y);
		    
			echo "<tr>";
			
			/*$nomeSala = db_getRoomNameById($j);*/
			$thisSala = $sale[$j-1];
			$nomeSala = $thisSala['Nome'];
			echo "<td class=\"centeralign\" >Sala $nomeSala</td>";
			for($i=$start0;$i<$start0+$len0;$i=($i+$q))
				$this->printCell($mydata,$i,$j,$d,$m,$y);
			
			echo "<td class=\"  centeralign\"   bgcolor=\"gray\" ></td>";
      
			for($i=$start1;$i<$start1+$len1;$i=($i+$q))
				$this->printCell($mydata,$i,$j,$d,$m,$y);
								
			echo" </tr>"; 		
		}
		echo "</table></center>";
	}		

	/*stampa una singola cella del diagramma di gantt*/
	protected function printCell($v,$value,$room,$d,$m,$y)
	{
		if(currentDay($d,$m,$y))
		{
			if($value <= date("G"))
			{
				echo "<td class=\"centeralign\"  > </td>";
				return;
			}
		}

		if($this->isAvaiableSlot($v,$value))
			echo "<td class=\"centeralign\"   bgcolor=\"green\">
			<input type=\"checkbox\" name=\"Sala$room-Ora$value\" value=\"1\" /> </td>";
		else
			echo "<td class=\"centeralign\"   bgcolor=\"red\"> </td>";
	}
	
	protected function buildGanttLezione($d,$m,$y)
	{
		echo "<br><center></central><table class=\"inline\" align=\"center\" >";
		echo "<tr>";
		echo "<td class=\" centeralign\" bgcolor=\"gray\"> </td>";
		
		for($i=STARTLEZIONI;$i<ENDLEZIONI;$i++)
		{
			$myvar=$i+1;
			if($i<10)
			{
				echo "<td class=\" centeralign\" >0$i-";
				if($myvar<10)
					echo "0$myvar</td>";
				else
					echo "$myvar</td>";
			}
			else 
				echo "<td class=\"  centeralign\" >$i-$myvar </td>";
		}
		echo "</tr>";
        
		$sale=db_getAllSala();/*prelevo tutte le sale da db*/
		$mycount= count($sale); /*numero di sale presenti*/
		for($j=1;$j<$mycount+1;$j++)
		{
			echo "<tr>";
			$roomId=$j;
			$roomName=db_getRoomNameById($j);
			echo "<td class=\"  centeralign\" >$roomName </td>";
			$allLezione=db_AllLezione($roomId,  $d, $m, $y);
			for($i=STARTLEZIONI;$i<ENDLEZIONI;$i++)
			{
				if(!(currentDay($d,$m,$y) && (date('G')>=$i)))
				{
					if($this->isAvaiableSlot($allLezione,$i))
						echo "<td bgcolor=\"green\"><center><input type=\"checkbox\" name=\"Sala$roomId-Lezione$i\"  value=\"1\"/></center></td>";
					else 
						echo "<td align=\"center\"  bgcolor=\"red\"></td>";
				}
				else
					echo "<td align=\"center\" ></td>";
			}
			echo "</tr>";
		}
		echo "</table></center>";
	}

	/*probabilmente da rimuovere in futuro*/
	protected function tpl_buildCalInfo($day,$mes,$year)
	{
		echo "Hai selezionato il mese $mes <br>";
		echo "Hai selezionato il giorno $day <br>";
		echo "Anno: $year <br>";
	}
	
	protected function printDayPrenotazioni($d,$m,$y,$index,$id)
	{
		if(currentDay($d,$m,$y))
			$border="border:4px solid #000000;" ;
		
			
		if(isPast($d,$m,$y))
			echo "<td class=\"col$index centeralign\" style=\"cursor:hand;$border\"  > $d</td>";
		elseif($this->checkSala($d,$m,$y))
			echo "<td class=\"col$index centeralign\" style=\"cursor:hand;$border\" bgcolor=\"green\" > <a href=\"?id=$id&do=up&day=$d&mes=$m&year=$y#sceglisala\" style=\"width:100%;text-decoration:none;\" >$d</a></td>";	
		else 
			echo "<td class=\"col$index centeralign\" style=\"cursor:hand;$border\" bgcolor=\"red\">$d</td>";
	}
	
	
	
	protected function printDayLezione($d,$m,$y,$index,$id)
	{
		if(currentDay($d,$m,$y))
			$border="border:4px solid #000000;" ;
		
		if(isPast($d,$m,$y))
			echo 	"<td class=\"col$index centeralign\" style=\"cursor:hand;$border\"  > $d</td>";
		elseif($this->checkSalaLezione($d,$m,$y))
			echo 	"<td class=\"col$index centeralign\" style=\"cursor:hand;$border\" bgcolor=\"green\" > <a href=\"?id=$id&do=up&day=$d&mes=$m&year=$y#sceglisala\" style=\"width:100%;text-decoration:none;\" >$d</a></td>";	
		else 
			echo "<td class=\"col$index centeralign\" style=\"cursor:hand;$border\" bgcolor=\"red\">$d</td>";
	} 
	
	protected function printDaySommario($d,$m,$y,$index,$id)
	{  
		$sale=db_getAllSala();
		$mycount= count($sale);
		$flag=false; /*verrà impostata a true se esistono sessioni o lezioni per il giorno*/
		
		if(currentDay($d,$m,$y))
			$border="border:4px solid #000000;" ;
		
		for($i=1;($i<$mycount+1)&&($flag==false);$i++)
			if(db_existRoomInSessione($i,  $d, $m, $y) || db_existRoomInLezione($i,$d,$m,$y)) 
				$flag=true;
			
		if($flag)
		{
			$green=false;
			for($i=1;($i<$mycount+1)&&(!$green);$i++)
			{
				/*$roomId=$sale[$i]['Id'];*/
				$roomId=$i;
				$allSessione=db_AllSessione($roomId,$d,$m,$y);
				$allLezione= db_AllLezione($roomId,$d,$m,$y);
				if(currentDay($d,$m,$y))
				{
					$pivot=date('G');
					if($pivot<ENDPROVE0-1)
						for($j=$pivot;($j<ENDPROVE0)&&(!$green);$j++)
							if(!$this->isAvaiableSlot($allSessione,$j))
								$green=true;
					   
					if($pivot<ENDLEZIONI-1)
						for($j=$pivot;($j<ENDLEZIONI)&&(!$green);$j++)
							if(!$this->isAvaiableSlot($allLezione,$j))
								$green=true; 
						
					if($pivot<ENDPROVE1-1)
						for($j=$pivot;($j<ENDPROVE1)&&(!$green);$j++)
							if(!$this->isAvaiableSlot($allSessione,$j))
								$green=true;
				}
				else
				{

					for($j=STARTPROVE0;($j<ENDPROVE0)&&(!$green);$j++)
						if(!$this->isAvaiableSlot($allSessione,$j))
							$green=true;
					   
					for($j=STARTLEZIONI;($j<ENDLEZIONI)&&(!$green);$j++)
						if(!$this->isAvaiableSlot($allLezione,$j))
							$green=true; 
						
					for($j=STARTPROVE1;($j<ENDPROVE1)&&(!$green);$j++)
						if(!$this->isAvaiableSlot($allSessione,$j))
							$green=true;
				}
			}
			if($green)
				echo	"<td class=\"col$index centeralign\" style=\"cursor:hand;$border\" bgcolor=\"green\" > <a href=\"?id=$id&do=up&day=$d&mes=$m&year=$y#sceglisala\" style=\"width:100%;text-decoration:none;\" >$d</a></td>";	
			else
				echo "<td class=\"col$index centeralign\" style=\"cursor:hand;$border\" bgcolor=\"gray\">$d</td>";
		}
		else
			echo "<td class=\"col$index centeralign\" style=\"cursor:hand;$border\" bgcolor=\"gray\">$d</td>";
	}
		
}
     
class RealSoundCalendarUser extends RealSoundCalendar
{
	
	protected function usage($action)
	{
		$msgIstruzioni="Con pochi semplici passi potrai prenotare una sala per la tua sessione di prova.<br> 
	Se invece vuoi visualizzare e/o annullare una prenotazione fatta in precedenza, <a href=\"/doku.php?id=riepilogo\" class=\"wikilink1\" title=\"riepilogo\">clicka QUI</a>.<br><br>
	Per prenotare una sala, clicka sul <font color=\"green\">giorno</font> desiderato. I giorni <font color=\"red\">rossi</font> purtroppo sono già occupati.<br><br>";
		
		echo $msgIstruzioni;
	}
	
	/*Implementazione del metodo dell'interfaccia Calendar.
	 *  Tramite buildGantt() disegna il diagramma di Gantt
	 * delle sale prova disponibili per la prenotazione*/
	public function calendarAction($d,$m,$y,$action=null)
	{
		$myGran=1; /*granularita (NB:per ora e' in ore)*/
		$len1=ENDPROVE0-STARTPROVE0;
		$len2=ENDPROVE1-STARTPROVE1;
		$this->buildGantt($d,$m,$y,4,4,1,9,20);	
	}	

	/*Implementa il seguente criterio di stampa:
	 * 1-Se l'utente ha la possibilità di effettuare una prenotazione 
	 * stampa una casella verde.Associa al giorno un link dove effettuare 
	 * la calendarAction()
	 * 2-Se il giorno preso in esame è riferito al passato stampa il colore di
	 * default.Non associa link
	 * 3-Se il giorno preso in esame non permette la prenotazione
	 * di sale prove stampa una casella rossa. Non associa link*/
	protected function printDay($d,$m,$y,$index,$id,$action)
	{
	   $this->printDayPrenotazioni($d,$m,$y,$index,$id);
	}                                         		
}		
	
 
 
 
class RealSoundCalendarAdmin extends RealSoundCalendar
{
	private function drawUserFormByTessera() 
	{
		$myself=$_SERVER['PHP_SELF'];
		$html = "<center><table>
				<tr>
					<td align=\"center\">Numero di Tessera</td> 
				</tr>
				<tr>
					<td align=\"center\"><input type=\"text\" class=\"formInput\" name=\"TESSERA\" /></td> 
				</tr>
	            </table></center>";
	
		echo $html;
		return;
	}
	
	private function drawUserFormByUserName() 
	{
		$myself=$_SERVER['PHP_SELF'];
		$html = "<center><table>
				<tr>
					<td align=\"center\">UserName</td> 
				</tr>
				<tr>
					<td align=\"center\"><input type=\"text\" class=\"formInput\" name=\"USERNAME\" /></td> 
				</tr>
				</table></center>";
		echo $html;
		return;
	}
	
	private function drawLezioneForm()
	{
		$myself=$_SERVER['PHP_SELF'];
		$html = "<br><center><table>
				<tr><td align=\"center\">TUTOR</td><td align=\"center\">ALLIEVO</td></tr>
				<tr>
					<td align=\"center\">UserName<br><input type=\"text\" class=\"formInput\" name=\"TUTORUSERNAME\" /></td>
					<td align=\"center\">UserName<br><input type=\"text\" class=\"formInput\" name=\"USERNAME\" /></td> 
				</tr>
				<tr>
					<td align=\"center\">Tessera<br><input type=\"text\" class=\"formInput\" name=\"TUTORTESSERA\" /></td>
					<td align=\"center\">Tessera<br><input type=\"text\" class=\"formInput\" name=\"TESSERA\" /></td> 
				</tr>
	            </table></center>";
		echo $html;
		return;
	}
	
	public function RealSoundCalendarAdmin()
	{
		if(!checkPagePermission(ADMIN))
		{
			echo "<pre>Non hai i permessi per visualizzare la seguente pagina</pre>";
			exit(1);
		}
	}
	
	private function getHumanTime($start,$end)
	{
		if($start<10)
			$ret[0]="0$start";
		else
		 {
			$ret[0]="$start";
			$ret[1]="$end";
			return $ret;
		}	
		
		if($end<10)
			$ret[1]="0$end";
		else
			$ret[1]="$end";
		
		return $ret;  
	}
	
	protected function usage($action)
	{
		switch ($action) 
		{
			case 'Summary' :
				$msgIstruzioni="Con pochi semplici passi potrai visualizzare tutte le prenotazioni (sessioni di prova e lezioni).<br> 
				I giorni <font color=\"green\">verdi</font> sono quelli dove sono presenti delle prenotazioni.<br>
				Se si desidera annulare un'attività basterà selezionare opportunamente i checkbox proposti in agenda.<br><br>";
			break;
			case 'PrenotaSessione' :
				$msgIstruzioni="Con pochi semplici passi potrai prenotare una sala per un socio dell'associazione.<br>
				Per prenotare una sala, clicka sul <font color=\"green\">giorno</font> desiderato. I giorni <font color=\"red\">rossi</font> sono già occupati.<br>
				Successivamente seleziona la sala, l'orario e inserisci username o tessera del socio.<br><br>";
			break;
			case 'PrenotaLezione' :
				$msgIstruzioni="Con pochi semplici passi potrai fissare una lezione.<br>
				Per prima cosa seleziona il <font color=\"green\">giorno</font> desiderato. I giorni <font color=\"red\">rossi</font> sono già occupati.<br>
				Successivamente seleziona la sala, l'orario e inserisci username o tessera del socio e del tutor.<br><br> ";
			break;
			default :
				$msgIstruzioni = "[DEV-ERROR] Errore: hai selezionato un'operazione non supportata da questa classe calendario. Questa è una condizione fortemente anomala, si prega di contattare un tecnico.";
		}
		echo $msgIstruzioni;
	}
	
	/*Implementazione del metodo dell'interfaccia Calendar.
	 *  Tramite buildGantt() disegna il diagramma di Gantt
	 * delle sale prova disponibili per la prenotazione*/
	public function calendarAction($d,$m,$y,$action)
	{
		switch($action)
		{
			case 'Summary':
				$this-> buildSummary($d,$m,$y);
			break;
			case 'GanttProve':
				echo "<form method=\"post\" action=\"http://dev.real-sound.org/doku.php?id=adminprenotazioni\">";
				$this->drawUserFormByTessera();
				$this->drawUserFormByUserName(); 
				$this->buildGantt($d,$m,$y,4,4,1,STARTPROVE0,STARTPROVE1);	
				echo " <br><center><input type=\"submit\"  name=\"Prenota\" value=\"Prenota\" />";
				echo "</form>";
			break;
			case 'GanttLezione':
				echo "<form method=\"post\" action=\"http://dev.real-sound.org/doku.php?id=adminlezioni\">";
				$this->drawLezioneForm(); 
				$this->buildGanttLezione($d,$m,$y);	
				echo " <br><center><input type=\"submit\"  name=\"Prenota\" value=\"Prenota\" />";
				echo "</form>";
			 break;
			default:
				echo "<pre>Azione non disponibile</pre>";
				exit(1);
			break;
		}	
	}	

	/*Implementa il seguente criterio di stampa:
	 * 1-Se l'utente ha la possibilità di effettuare una prenotazione 
	 * stampa una casella verde.Associa al giorno un link dove effettuare 
	 * la calendarAction()
	 * 2-Se il giorno preso in esame è riferito al passato stampa il colore di
	 * default.Non associa link
	 * 3-Se il giorno preso in esame non permette la prenotazione
	 * di sale prove stampa una casella rossa. Non associa link*/
	protected function printDay($d,$m,$y,$index,$id,$action)
	{  
		switch($action)
		{
			case 'Summary':
				$this->printDaySommario($d,$m,$y,$index,$id);
			break;
			case 'PrenotaSessione':
				$this->printDayPrenotazioni($d,$m,$y,$index,$id);
			break;
			case 'PrenotaLezione':
				$this->printDayLezione($d,$m,$y,$index,$id);
			break;
			default:
				echo "Errore";
			break;
		}
	}
	
	private  function posNotAvaiableSlotSessione($v,$value,&$head)
	{  	
		for($i=0;$i<count($v);$i++)
		{
			$myvar=$v[$i];  
			$st=$myvar[0]; 
			$et=$myvar[1]; 
			list($sh,$sm,$ss)= explode(":",$st);
			list($eh,$em,$es) = explode(":",$et); 
		 
			if($value==$sh)
				$head=true;
			else
				$head=false;
				
			if($value>=$sh && $value<$eh)
				return $i;
		}      
		return -1;
	 }
     
	private function posNotAvaiableSlotLezione($db,$seektime)
	{
		for($i=0;$i<count($db);$i++)
		{
			$myvar=$db[$i];  
			$st=$myvar[0]; 
			$et=$myvar[1]; 
			list($sh,$sm,$ss)= explode(":",$st);
			list($eh,$em,$es) = explode(":",$et); 
		 
			if($seektime==$sh)
				return $i;
		}      
		return -1;	
	}   
	
	private function checkAndAdd($vector,$value)
	{
		for($i=0;$i<count($vector);$i++)
			if($vector[$i]==$value)
				return true;
		
		array_push($vector,$value);
		return false;
	}
	
	private function buildSummary($d,$m,$y)
	{
		$lastIndex=-1;
		$sale=db_getAllSala();/*prelevo tutte le sale da db*/
		$mycount= count($sale); /*numero di sale presenti*/
		$firstTime=true;
		
		echo "<form method=\"post\" action=\"$myself?id=admincalendar\">";
		echo "<br><center></central><table class=\"inline\" align=\"center\"  width=\"100%\" value=\"1\">";
		/*Il programma si appresta a disegnare la prima riga della tabella */
		echo "<tr>";
		/*colonna orario*/
		echo "<td align=\"center\"> Orario</td>";
		/*Nomi delle sale*/
		for($i=0;$i<$mycount;$i++)
		{
			$room=$sale[$i];
			$roomName=$room['Nome'];
			echo "<td align=\"center\"> $roomName</td>";	
		}
		echo "</tr>";
		
		/*disegna la prima fascia oraria 9-13*/
		for($i=STARTPROVE0;$i<ENDPROVE0;$i++)
		{
			echo "<tr>";
			$mytime=$this->getHumanTime($i,$i+1);
			$stime= $mytime[0];
			$etime=$mytime[1];
			
			echo "<td align=\"center\">$stime"."- "."$etime</td>";
			
			for($j=1;$j<$mycount+1;$j++)
			{
				$lastIndex=-1;
				/*$roomId=$sale[$j]['SalaId'];*/
				$roomId=$j;
				$allSessione=db_AllSessione($roomId,  $d, $m, $y);
				if(currentDay($d,$m,$y) && (date('G')>=$i))
					echo "<td  align=\"center\" bgcolor=\"gray\">\</td>";
				else
				{
					if(!$this->isAvaiableSlot($allSessione,$i))
					{
						$checkHead;
						$index=$this->posNotAvaiableSlotSessione($allSessione,$i,$checkHead);
						$userId=$allSessione[$index]['Utente'];
						$name=db_getUserNameById($userId);
						$inizioSessione=$allSessione[$index][0];
						list($sh,$sm,$ss) = explode(":",$inizioSessione);
						if($checkHead)
							echo "<td bgcolor=\"green\">Sala prenotata da $name<br><center>" ;
						else
							echo "<td bgcolor=\"green\" border=\"0\">";
						
						if(!isPast($d,$m,$y))	
							echo "<center><input type=\"checkbox\" name=\"Sala$roomId-Sessione$index-Tempo$i\"  value=\"1\" /></center> </td>";
					}
					else
						echo "<td align=\"center\"  bgcolor=\"gray\">\</td>";
				}
			}
			echo "</tr>";
		}
        $firstTime=true;
		for($i=STARTLEZIONI;$i<ENDLEZIONI;$i++)
		{                                                     
			echo "<tr>";
			$mytime=$this->getHumanTime($i,$i+1);
			$stime= $mytime[0];
			$etime=$mytime[1];
			echo "<td align=\"center\">$stime"."- "."$etime</td>";
			for($j=1;$j<$mycount+1;$j++)
			{
				$roomId=$j;
				$allLezione=db_AllLezione($roomId,  $d, $m, $y);
				$lastIndex=-1;
				if(currentDay($d,$m,$y) && (date('G')>=$i))
					echo "<td align=\"center\"  bgcolor=\"gray\">\</td>";
				else
				{
					if(!$this->isAvaiableSlot($allLezione,$i))
					{
						$checkHead;
						$index=$this->posNotAvaiableSlotSessione($allLezione,$i,$checkHead);
						$tutorId=$allLezione[$index]['Insegnante'];
						$lezioneId= $allLezione[$index]['LezioneId'];
						$tutorName=db_getUserNameById($tutorId);
						if($checkHead)
						{
							echo "<td bgcolor=\"green\">Tutor: $tutorName";
							$studenti=db_getIscritti($lezioneId);
							for($k=0;($k<count($studenti))&&$checkHead;$k++)
							{
								$studentName=db_getUserNameById(intval($studenti[$k][0]));
								echo "<br>Studente: $studentName";
							}	
						}
						else 
							echo "<td bgcolor=\"green\">" ;
						

						if(!isPast($d,$m,$y))	
							echo "<br><center><input type=\"checkbox\" name=\"Sala$roomId-Lezione$index-Tempo$i\"  value=\"1\"/></center>";
						
						echo "</td>";
					}
					else 
						echo "<td align=\"center\"  bgcolor=\"gray\">\</td>";
				}
			}
			echo "</tr>";
		}
		$firstTime=true;
		for($i=STARTPROVE1;$i<ENDPROVE1;$i++)
		{
			echo "<tr>";
			$mytime=$this->getHumanTime($i,$i+1);
			$stime= $mytime[0];
			$etime=$mytime[1];
			echo "<td align=\"center\">$stime"."- "."$etime</td>";
			for($j=1;$j<$mycount+1;$j++)
			{
				$lastIndex=-1;
				$roomId=$j;
				$allSessione=db_AllSessione($roomId,  $d, $m, $y);
				if(currentDay($d,$m,$y) && (date('G')>=$i))
					echo "<td align=\"center\"  bgcolor=\"gray\">\</td>";
				else
				{
					if(!$this->isAvaiableSlot($allSessione,$i))
					{
						$checkHead;
						$index=$this->posNotAvaiableSlotSessione($allSessione,$i,$checkHead);
						$userId=$allSessione[$index]['Utente'];
						$name=db_getUserNameById($userId);
						$inizioSessione=$allSessione[$index][0];
						list($sh,$sm,$ss) = explode(":",$inizioSessione);
						if($checkHead)
							echo "<td bgcolor=\"green\">Sala prenotata da $name<br>" ;
						else
							echo "<td bgcolor=\"green\" border=\"0\">";
						
						if(!isPast($d,$m,$y))	
							echo "<center><input type=\"checkbox\" name=\"Sala$roomId-Sessione$index-Tempo$i\"  value=\"1\" /></center> </td>";
						
					}
					else
						echo "<td align=\"center\"  bgcolor=\"gray\">\</td>";
				}
			}
			echo "</tr>";
		}
		echo "</table></center>";
		echo "<br>";
		echo "<center>
				<input type=\"submit\"  name=\"Rimuovi\" value=\"Rimuovi\" />
				</center>";
		echo "</form>";
		$_SESSION['adminDate']="$d:$m:$y";
	}                                         
		
		
	 private function adminSalaProve($roomId,$db,$d,$m,$y)
	 {
		 $toMail=array();
		 for($dbIndex=0;$dbIndex<count($db);$dbIndex++)
		 {
			$first=0;/*Orario del primo checkbox */
			$last=0;/*Ultima ora consecutiva */
			list($sh,$sm,$ss)=explode(":",$db[$dbIndex][0]);
			list($eh,$em,$es)=explode(":",$db[$dbIndex][1]);
			$sh=intval($sh);
			$eh=intval($eh);	
			for($i=$sh;$i<$eh;$i++)
			{
				if(!empty($_POST["Sala$roomId-Sessione$dbIndex-Tempo$i"]))  /*sala,riga iesima nel database,tempo*/
				{
					if($first==0)
					{
						$first=$i;
						$last=$i;
					}
					else	
					{
						if($i!= $last+1 )
							throw new Exception("Errore devi selezionare ore consecutive per la solita Sessione");				
						
						$last=$i;
					}
				}
			}	
					
			if($last !=0)  /*se l'utente ha selezionato almeno un checkbox*/
			{ 
				/*verifico errori semantici/maliziosi
				 * Non dovrebbe MAI finire su questi 2 if,neanche se l'utente altera la gui*/
				if(($first<$sh) || ($first>$eh) || ($last>$eh) ||($last<$sh))
					throw new RunTimeException("L'inizio dell'intervallo selezionato per la rimozione non comprende checkbox selezionabili ");
				
				if($first>$last) 
					throw new RunTimeException("Non è possibile eliminare l'intervallo selezionato");
				                                                                                 
				/*metodo selezionato con l'opzione delete attiva
				 * Cancella le sale prova selezionate e reiserisce gli slot 
				 * opportunamente*/	
				$id=$db[$dbIndex]['SessioneProvaId'];
				$userId=$db[$dbIndex]['Utente'];	
				$userId=intval($userId);
				
				/*rimuove la sessione*/
				db_deleteSessione($id);
				$last+=1;
		
				/*iserisce opportunamente nel db*/
				if($sh<$first)
					secureInsertProva($userId,$roomId,"$y-$m-$d","$sh:00:00","$first:00:00");
				
				if($last<$eh)
					secureInsertProva($userId,$roomId,"$y-$m-$d","$last:00:00","$eh:00:00");
				
				/*informazioni necesserie per l''invio della mail*/
				$tmp['userId']=$userId;
				$tmp['inizioSessione']=$inizioSessione;
				array_push($toMail,$tmp);
			}
		}
		return $toMail; 
	} 
	
	private function adminCheckSalaProve($roomId,$db)
	{
		$last=0;/*Ultima ora consecutiva */
		$ret=false;
		
		for($dbIndex=0;$dbIndex<count($db);$dbIndex++)
		{
			$last=0;
			list($sh,$sm,$ss)=explode(":",$db[$dbIndex][0]);
			list($eh,$em,$es)=explode(":",$db[$dbIndex][1]);
			$oraInizio=$sh;
			$oraFine=$eh;
			for($i=$oraInizio;$i<$oraFine;$i++)
				if(!empty($_POST["Sala$roomId-Sessione$dbIndex-Tempo$i"]))  /*sala,riga iesima nel database,tempo*/
				{
					if($last==0)
					{
						$last=$i;
						$ret=true;
					}
					elseif($i!= $last+1 )
						throw new RunTimeException("Devi selezionare elementi consecutivi");
					else
						$last=$i;
				}	
		}			
		return $ret;
	}
	
	private function adminDelSalaProve($roomId,$db,$d,$m,$y)
	{
		$noMail=array();
		
		$ret=$this->adminSalaProve($roomId,$db,$d,$m,$y);
		for($i=0;$i<count($ret);$i++)
		{
			$title="Sessione di Prova modificata/annullata";
			$oraSessione=$ret[$i]['inizioSessione'];
			list($hh,$mm,$ss)= explode(":",$oraSessione);
$body="Ciao!
Ti informiamo che la tua prenotazione del $d-$m-$y, ore $hh:$mm è stata modificata o annullata.

Puoi controllare le modifiche effettuate al seguente indirizzo: http://dev.real-sound.org/doku.php?id=riepilogo

Per qualsiasi problema non esistare a contattarci.

Cordiali saluti,

Associazione Culturale Realsound
via Polese 1\a
40122 Bologna (BO)
telefono: 0514076611
e-mail: associazionerealsound@gmail.com
www.real-sound.org";
			$contacts = getUserContactById($ret[$i]['userId']);
			$mailrow = $contacts[0];
			$mail = $mailrow['Value'];
			$name=db_getUserNameById($ret[$i]['userId']);
			$checkmail=sendUserMail($mail,$title,$body);
			if(!$checkmail)
				array_push($noMail,$name);
		}
		if(!empty($noMail))
		{
			echo "<pre>I seguenti utenti non hanno ricevuto la mail informativa:";
			for($i=0;$i<count($noMail);$i++)
				echo"<br>".$noMail[$i];
			echo"</pre>";
		}
			
	}
	
	private function adminCheckLezione($roomId,$db,$d,$m,$y)
	{
		$last=0;/*Ultima ora consecutiva */
		$ret=false;
		
		for($dbIndex=0;$dbIndex<count($db);$dbIndex++)
		{
			$last=0;
			for($i=STARTLEZIONI;$i<ENDLEZIONI;$i++)
				if(!empty($_POST["Sala$roomId-Lezione$dbIndex-Tempo$i"]))  /*sala,riga iesima nel database,tempo*/
				{
					if($last==0)
					{
						$last=$i;
						$ret=true;
					}
					elseif($i!= $last+1 )
						throw new RunTimeException("Devi selezionare elementi consecutivi");
					else
						$last=$i;
				}	
		}			
		return $ret;
	}
	
	private function adminLezione($roomId,$db,$d,$m,$y)
	{
		 $toMail=array();
		 for($dbIndex=0;$dbIndex<count($db);$dbIndex++)
		 {
			$first=0;/*Orario del primo checkbox */
			$last=0;/*Ultima ora consecutiva */ 	
			for($i=STARTLEZIONI;$i<ENDLEZIONI;$i++)
			{
				if(!empty($_POST["Sala$roomId-Lezione$dbIndex-Tempo$i"]))  /*sala,riga iesima nel database,tempo*/
				{
					if($first==0)
					{
						$first=$i;
						$last=$i;
					}
					else	
					{
						if($i!= $last+1 )
							throw new Exception("Errore devi selezionare ore consecutive per la solita Sessione");				
						$last=$i;
					}
				}
			}	
					
			if($last !=0)  /*se l'utente ha selezionato almeno un checkbox*/
			{
				$inizioLezione=$db[$dbIndex][0];
				$fineLezione=$db[$dbIndex][1];
				list($sh,$sm,$ss) = explode(":",$inizioLezione);
				list($eh,$em,$es)=explode(":",$fineLezione);
				
				$sh=intval($sh);
				$eh=intval($eh); 
				/*verifico errori semantici/maliziosi*/
				
				if(($first<$sh) || ($first>$eh) || ($last>$eh) ||($last<$sh))
					throw new RunTimeException("L'inizio dell'intervallo selezionato per la rimozione non comprende checkbox selezionabili ");
				
				if($first>$last) 
					throw new RunTimeException("Non è possibile eliminare l'intervallo selezionato");
				                                                                                 
				/*metodo selezionato con l'opzione delete attiva
				 * Cancella le sale prova selezionate e reiserisce gli slot 
				 * opportunamente*/	
				$id=$db[$dbIndex]['LezioneId'];
				$id=intval($id);
				$tutorId=$db[$dbIndex]['Insegnante'];
				$tutorId=intval($tutorId);
				$studenti=db_getIscritti($id);
				/*rimuove la Lezione*/
				db_deleteLezione($id);
				$last+=1;
		
				/*iserisce opportunamente nel db*/
				if($sh<$first)
				{
					$id=secureInsertLezione($tutorId, $roomId,"$y-$m-$d", "$sh:00:00", "$first:00:00");
					$id=intval($id);
					for($k=0;$k<count($studenti);$k++)
					{
						$student=intval($studenti[$k][0]);
						secureIscrizione($id,$student);
					}
				}
				
				if($last<$eh)
				{
					$id=secureInsertLezione($tutorId, $roomId,"$y-$m-$d", "$last:00:00", "$eh:00:00");
					$id=intval($id);
					for($k=0;$k<count($studenti);$k++)
					{
						$student=intval($studenti[$k][0]);
						secureIscrizione($id,$student);
					}
				}
				
				
				/*valori da storare per la Mail*/
				$tmp['tutorId']=$tutorId;
				$tmp['inizioLezione']="$sh:$sm";
				$tmp['fineLezione']="$eh:$em";
				$tmp['data']="$d-$m-$y";
				$tmp['sala']=db_getRoomNameById($roomId);
				$tmp['studenti']=$studenti;
				
				
				
			
				/* stora il vettore $tmp nel vettore $toMail*/
				array_push($toMail,$tmp);
			}
		}
		return $toMail;
	} 
	
	private function  adminDelLezione($roomId,$db,$d,$m,$y)
	{
		$noMail=array();
		
		$ret=$this->adminLezione($roomId,$db,$d,$m,$y);
		for($i=0;$i<count($ret);$i++)
		{
			$nomeTutor=db_getUserAttributeByKey("Nome","UserId",$ret[$i]['tutorId']);
			$cognomeTutor=db_getUserAttributeByKey("Nome","UserId",$ret[$i]['tutorId']);
			$title="Lezione modificata/annullata";
			$oraInizio=$ret[$i]['inizioLezione'];
			$oraFine=$ret[$i]['fineLezione'];
			$sala=$ret[$i]['sala'];
$body="Ciao!
Ti informiamo che una tua lezione è stata modificata o annullata.

Data:   $d-$m-$y
Tutor: $nomeTutor $cognomeTutor
Sala: $sala 
Ora inizio: $oraInizio
Ora fine: $oraFine

Controlla le modifiche effettuate al seguente indirizzo: http://dev.real-sound.org/doku.php?id=riepilogo

Per qualsiasi problema non esistare a contattarci.

Cordiali saluti,

Associazione Culturale Realsound
via Polese 1\a
40122 Bologna (BO)
telefono: 0514076611
e-mail: associazionerealsound@gmail.com
www.real-sound.org";
			$contacts = getUserContactById(intval($ret[$i]['tutorId']));
			$mailrow = $contacts[0];
			$mail = $mailrow['Value'];
			$name=db_getUserNameById(intval($ret[$i]['tutorId']));
			/*prova a mandare la mail al prof*/
			$checkmail=sendUserMail($mail,$title,$body);
			if(!$checkmail)
				array_push($noMail,$name);
				
			/*prova a mandare la mail agli studenti coinvolti*/
			$studenti=$ret[$i]['studenti'];
	        for($k=0;$k<count($v);$k++)
			{
				$contacts = getUserContactById(intval($studenti[$k][0]));
				$mailrow = $contacts[0];
				$mail = $mailrow['Value'];
				$name=db_getUserNameById(intval($studenti[$k][0]));
				/*prova a mandare la mail al prof*/
				$checkmail=sendUserMail($mail,$title,$body);
				if(!$checkmail)
					array_push($noMail,$name);	
			}
		}
		if(!empty($noMail))
		{
			echo "<pre>I seguenti utenti non hanno ricevuto la mail informativa:";
			for($i=0;$i<count($noMail);$i++)
				echo"<br>".$noMail[$i];
			echo"</pre>";
		}
			
	}
		
	
	private function adminCheckAllLezione($d,$m,$y)
	{
		$sale=db_getAllSala();/*prelevo tutte le sale da db*/
		$mycount= count($sale); /*numero di sale presenti*/
		$ret=false;
		
		for($k=1;$k<$mycount+1;$k++)
		{
			$allLezione=db_AllLezione($k,$d,$m,$y);
			$ret|=$this->adminCheckLezione($k,$allLezione,$d,$m,$y);
		}
		return $ret;
	}
	
	private function adminDelAllLezione($d,$m,$y)
	{
		$sale=db_getAllSala();/*prelevo tutte le sale da db*/
		$mycount= count($sale); /*numero di sale presenti*/
		$ret=false;
		
		if(isPast($d,$m,$y))
			throw new RunTimeException("E' stata selezionata una prenotazione riferita al passato");
			
		for($k=1;$k<$mycount+1;$k++)
		{
			$allLezione=db_AllLezione($k,  $d, $m, $y);
			$this->adminDelLezione($k,$allLezione,$d,$m,$y);
		}
	} 

	private function adminCheckAllSalaProve($d,$m,$y)
	{
		$sale=db_getAllSala();/*prelevo tutte le sale da db*/
		$mycount= count($sale); /*numero di sale presenti*/
		$ret=false;
		
		for($k=1;$k<$mycount+1;$k++)
		{
			$allSessione=db_AllSessione($k,  $d, $m, $y);
			$ret|=$this->adminCheckSalaProve($k,$allSessione,$d,$m,$y);
		}
		return $ret;
	}
	 
	private function adminDelAllMarkedSalaProve($d,$m,$y)
	{
		$sale=db_getAllSala();/*prelevo tutte le sale da db*/
		$mycount= count($sale); /*numero di sale presenti*/
		$ret=false;
		
		if(isPast($d,$m,$y))
			throw new RunTimeException("E' stata selezionata una prenotazione riferita al passato");
			
		for($k=1;$k<$mycount+1;$k++)
		{
			$allSessione=db_AllSessione($k,  $d, $m, $y);
			$ret|=$this->adminDelSalaProve($k,$allSessione,$d,$m,$y);
		}		
		return $ret;
	}
	 
	public function secureAdminDelete($d,$m,$y)
	{
		try
		{
			$retSessione=$this->adminCheckAllSalaProve($d,$m,$y);
			$retLezione =$this->adminCheckAllLezione($d,$m,$y);
			
			if($retSessione)
				$this->adminDelAllMarkedSalaProve($d,$m,$y);
			
			if($retLezione)
				$this->adminDelAllLezione($d,$m,$y);
			
			if(!($retSessione || $retLezione))
				echo "<pre>Non hai selezionato elementi da rimuovere<br>Clicka qui per tornare alla pagina iniziale</pre>";
			else
				echo"<pre>Rimozione avvenuta con successo!</pre> ";
		}
		catch(RunTimeException $e)
		{
			echo "<pre>".$e->getmessage()."</pre>";
		}
		catch(Exception $e)
		{
			echo "<pre> [DEVERROR]Eccezione non prevista.<br>Contattare i tecnici</pre>";
			echo "<pre>".$e->getmessage()."</pre>";
		}	
	}
}


class RealSoundCalendarProf extends RealSoundCalendar
{
	
	private function getHumanTime($start,$end)
	{
		if($start<10)
			$ret[0]="0$start";
		else
		 {
			$ret[0]="$start";
			$ret[1]="$end";
			return $ret;
		}	
		
		if($end<10)
			$ret[1]="0$end";
		else
			$ret[1]="$end";
		
		return $ret;  
	}
		
	private  function posNotAvaiableSlotLezione($v,$value)
	{  	
		for($i=0;$i<count($v);$i++)
		{
			$myvar=$v[$i];  
			$st=$myvar[0]; 
			$et=$myvar[1]; 
			list($sh,$sm,$ss)= explode(":",$st);
			list($eh,$em,$es) = explode(":",$et); 
				
			if($value>=$sh && $value<$eh)
				return $i;
		}      
		return -1;
	 }
	protected function usage($action)
	{
		$msgIstruzioni="Con pochi semplici passi potrai visualizzare le lezioni passate/future.<br> 
	Per visualizzare gli impegni in agenda clicka sul <font color=\"green\">giorno</font> desiderato. I giorni <font color=\"gray\">grigi</font> sono privi di impegni.<br><br>";
		echo $msgIstruzioni;
	}
	
	/*Implementazione del metodo dell'interfaccia Calendar.
	 *  Tramite buildGantt() disegna il diagramma di Gantt
	 * delle sale prova disponibili per la prenotazione*/
	public function calendarAction($d,$m,$y,$action=null)
	{
		$this->buildSummaryLezioneProf($d,$m,$y);
	}	

	/*Implementa il seguente criterio di stampa:
	 * 1-Se l'utente ha la possibilità di effettuare una prenotazione 
	 * stampa una casella verde.Associa al giorno un link dove effettuare 
	 * la calendarAction()
	 * 2-Se il giorno preso in esame è riferito al passato stampa il colore di
	 * default.Non associa link
	 * 3-Se il giorno preso in esame non permette la prenotazione
	 * di sale prove stampa una casella rossa. Non associa link*/
	protected function printDay($d,$m,$y,$index,$id,$action)
	{
		$this->printDaySommarioLezioneProf($d,$m,$y,$index,$id);
	}    
	
	private function buildSummaryLezioneProf($d,$m,$y)
	{
		$lastIndex=-1;
		$sale=db_getAllSala();/*prelevo tutte le sale da db*/
		$mycount= count($sale); /*numero di sale presenti*/
		
		echo "<br><center></central><table class=\"inline\" align=\"center\"  width=\"100%\" value=\"1\">";
		/*Il programma si appresta a disegnare la prima riga della tabella */
		echo "<tr>";
		/*colonna orario*/
		echo "<td align=\"center\"> Orario</td>";
		/*Nomi delle sale*/
		for($i=0;$i<$mycount;$i++)
		{
			$room=$sale[$i];
			$roomName=$room['Nome'];
			echo "<td align=\"center\"> $roomName</td>";	
		}
		echo "</tr>";
		

		for($i=STARTLEZIONI;$i<ENDLEZIONI;$i++)
		{                                                     
			echo "<tr>";
			$mytime=$this->getHumanTime($i,$i+1);
			$stime= $mytime[0];
			$etime=$mytime[1];
			echo "<td align=\"center\">$stime"."- "."$etime</td>";
			for($j=1;$j<$mycount+1;$j++)
			{
				$roomId=$j;
				$allLezione=db_AllLezione($roomId,  $d, $m, $y);
				$lastIndex=-1;
	
				if(!$this->isAvaiableSlot($allLezione,$i))
				{
					$index=$this->posNotAvaiableSlotLezione($allLezione,$i);
					$tutorId=$allLezione[$index]['Insegnante'];
					$lezioneId= $allLezione[$index]['LezioneId'];
					$tutorName=db_getUserNameById($tutorId);
					if($tutorName===$_SERVER['REMOTE_USER'])
					{
						echo "<td bgcolor=\"green\">";	
						$studenti=db_getIscritti($lezioneId);
						for($k=0;$k<count($studenti);$k++)
						{
							$studentName=db_getUserNameById($studenti[$k][0]);
							echo "Studente: $studentName<br>";
						}
						echo "</td>";
					}
					else
						echo "<td align=\"center\"  bgcolor=\"gray\">\</td>";
				}
				else 
					echo "<td align=\"center\"  bgcolor=\"gray\">\</td>";
			}
			echo "</tr>";
		}
		echo "</table></center>";
	}
	         
	private function printDaySommarioLezioneProf($d,$m,$y,$index,$id)
	{  
		$sale=db_getAllSala();
		$mycount= count($sale);
		$green=false; 
			
		for($i=1;($i<$mycount+1)&&(!$green);$i++)
		{
			if(db_existRoomInLezione($i,$d,$m,$y)) 
			{
				$roomId=$i;
				$allLezione= db_AllLezione($roomId,$d,$m,$y);
	   
				for($j=STARTLEZIONI;($j<ENDLEZIONI)&&(!$green);$j++)
				{
					if(!$this->isAvaiableSlot($allLezione,$j))
					{
						$index=$this->posNotAvaiableSlotLezione($allLezione,$j);
						$tutorId=$allLezione[$index]['Insegnante'];
						$tutorName=db_getUserNameById($tutorId);
						if($tutorName===$_SERVER['REMOTE_USER'])
							$green=true; 
					}
				} 
			}
		}
			
		if($green)
			echo	"<td class=\"col$index centeralign\" style=\"cursor:hand\" bgcolor=\"green\" > <a href=\"?id=$id&do=up&day=$d&mes=$m&year=$y#sceglisala\" style=\"width:100%;text-decoration:none;\" >$d</a></td>";	
		else
			echo "<td class=\"col$index centeralign\" style=\"cursor:hand\" bgcolor=\"gray\">$d</td>";
	}
	                                                                 		
}		
                                                               		
	
	
