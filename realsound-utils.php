<?php

// Controlla se la stringa $flag è contenuta (true) o no (false) in $flagstream
function checkflag($flag, $flagStream){
	
	$pos = strpos($flagStream, $flag);
	if($pos === false) return false;
	else return true;
}

function humanDate($d,$m,$y)
{
	$weekDay=cal_to_jd(CAL_GREGORIAN,$m,$d,$y)%7;
	switch($weekDay)
	{
		case 0:$ret[0]="Lunedì";break;
		case 1: $ret[0]="Martedì"; break;
		case 2: $ret[0]="Mercoledì"; break;
		case 3: $ret[0]="Giovedì";break;
		case 5: $ret[0]="Sabato";break;
		case 4: $ret[0]="Venerdì"; break;
		case 6: $ret[0]="Domenica";break;
		default: break;
	}
	$ret[1]=$d; /*numero giorno*/
	
	switch($m)
	{
		case 1: $ret[2]="Gennaio"; break;
		case 2: $ret[2]="Febbraio"; break;
		case 3: $ret[2]="Marzo";break;
		case 4: $ret[2]="Aprile"; break;
		case 5: $ret[2]="Maggio";break;
		case 6: $ret[2]="Giugno";break;
		case 7: $ret[2]="Luglio";break;
		case 8:$ret[2]="Agosto";break;
		case 9:$ret[2]="Settembre";break;
		case 10:$ret[2]="Ottobre";break;
		case 11:$ret[2]="Novembre";break;
		case 12:$ret[2]="Dicembre";break;
		default: break;
	}
	
	$ret[3]=$y;
	
	return $ret;
}


function currentDay($day,$mes,$year)
{
	if(($day==date('j')) && ($mes==date('n') ) && ($year==date('Y')))
		return true;
	else 
		return false;		
	
}

function isPast($d,$m,$y)
{ 
	/*the past*/
	if((date('Y')>$y))
		return true;		
	
	if((date('n')>$m)  && (date('Y')==$y) )
		return true;
	
	
	if((date('n')==$m)  && (date('Y')==$y))
	{	
		if(date('j')>$d)
			return true;
	}
	return false;
}


function drawForm($flag,$flag1=null,$v=null)
{
	try{
		
	if( ($flag1!=READY) && (!isset($v)) )
			throw Exception("Non puoi usare FLAG!=READY senza passare il vettore
			degli errori");
	if($flag1==READY){
		$tablewidth='500 pt';
		$columnspace='50%';
	}
	else {
		$tablewidth='600 pt';
		$columnspace='40%';
	}
	   
	switch($flag)
	{
		case DRAWBEGIN:  /*22*/
		{
			echo "<fieldset>";
			echo "<table width=\"$tablewidth\">";
    
			echo    " <tr> ";
			 $myself=$_SERVER['PHP_SELF'];
			echo "<td class=\"normalfont\"><span style=\"font-family: arial,verdana; font-size: 10pt;\"><br>
				<br>
           
				<div>
				<form method=\"post\" action=\"$myself?id=iscrizione\">
                <input type=\"hidden\" name=\"expid\" value=\"%% EXPID %%\" />
                <table cellspacing=\"5\" cellpadding=\"3\" border=\"0\">
                <tbody>";
		}break;
		case USERNAME:	  /*0*/
		{
			$msg="UserName";
			$value="";
			$myerror="";
			
			if($flag1==OK)
				$value=$_POST['USERNAME'];

			if($flag1==NOTOK)
			{
				 $msg="UserName(*)";
				 $myerror=getErrorMsg($v[USERNAME]);
			}
			
			echo "<tr>
                      <td width=\"$columnspace\" class=\"normalfont\"><b>$msg</b></td>
                      <td align=\"left\">
                        <input type=\"text\" class=\"formInput\" name=\"USERNAME\" value=\"$value\" /></td>
                        <td>$myerror</td>
                    </tr>";
		}break;
        case NOME:        /*1*/
        {    
			$msg="Nome";
			$value="";
			$myerror="";
			if($flag1==OK)
				$value=$_POST['NOME'];

			if($flag1==NOTOK)
			{
				 $msg="Nome(*)";
				 $myerror=getErrorMsg($v[NOME]);
			}               
            echo "<tr>
						<td class=\"normalfont\"><b>$msg</b></td>
						<td align=\"left\">
                        <input type=\"text\" class=\"formInput\" name=\"NOME\" value=\"$value\" /></td>
                    <td>$myerror</td>
                    </tr>";
		}break;
        case COGNOME:      /*2*/
        {  
			$msg="Cognome";
			$value="";
			$myerror="";
			if($flag1==OK)
				$value=$_POST['COGNOME'];

			if($flag1==NOTOK)
			{
				 $msg="Cognome(*)";
				 $myerror=getErrorMsg($v[COGNOME]);
			}
			
			
			echo"<tr>
						<td class=\"normalfont\"><b>$msg</b></td>
						<td align=\"left\">
							<input type=\"text\" class=\"formInput\" name=\"COGNOME\" value=\"$value\"/></td>
							<td>$myerror</td>
						</tr>";
         }break;
         case PROFESSIONE:         /*3*/
         {
			 
			$msg="Professione";
			$value="";
			$myerror="";
			if($flag1==OK)
				$value=$_POST['PROFESSIONE'];

			if($flag1==NOTOK)
			{
				 $msg="Professione(*)";
				 $myerror=getErrorMsg($v[PROFESSIONE]);
			}            
             echo "<tr>
                      <td class=\"normalfont\"><b> $msg</b></b></td>
                      <td align=\"left\">
                        <input type=\"text\" class=\"formInput\" name=\"PROFESSIONE\" value=\"$value\"/></td>
                        <td>$myerror</td>
                  </tr>";
		 }break;
          case DRAWBORN:                     /*23*/
          {
			  echo "<tr>
					<td class=\"normalfont\"><b>Data Nascita</b></td>
					<td align=\"left\">";
			}break;
		  case BDATE:             /*4*/
		   {
				$myerror="";
			   	if($flag1==NOTOK)
					$myerror=getErrorMsg($v[BDATE]);
				echo "<select name=\"MESE\" onChange=\"changeDate(this.options[selectedIndex].value);\">";
				echo "
							<option value=\"0\">mm</option>
							<option value=\"1\">01</option>
							<option value=\"2\">02</option>
							<option value=\"3\">03</option>
							<option value=\"4\">04</option>
							<option value=\"5\">05</option>
							<option value=\"6\">06</option>
							<option value=\"7\">07</option>
							<option value=\"8\">08</option>
							<option value=\"9\">09</option>
							<option value=\"10\">10</option>
							<option value=\"11\">11</option>
							<option value=\"12\">12</option>
							</select>
							<select name=\"GIORNO\" id=\"giorno\">
							<option value=\"0\">gg</option>
							</select>
							<select name=\"ANNO\" id=\"anno\">
							<option value=\"0\">aaaa</option>
							</select>
							</td>
							<td>$myerror</td></tr>
							";
			}break;
			case BCITY:                        /*5*/
			{
				
					$msg="Citta";
					$value="";
					$myerror="";
					if($flag1==OK)
						$value=$_POST['BCITY'];

					if($flag1==NOTOK)
					{
						$msg="Citta(*)";
						$myerror=getErrorMsg($v[BCITY]);
					} 
                        
							echo "<tr>
									<td class=\"normalfont\"><b></b></td>
									<td>
									<b><br>Luogo di Nascita</b>
									</td>
									</tr>    ";
                    
							echo " <tr>
									<td class=\"normalfont\"><b>$msg</b></td>
									<td align=\"left\">
									<input type=\"text\" class=\"formInput\" name=\"BCITY\" value=\"$value\" /></td>
									<td>$myerror</td>
									</tr>";
			}break;
            case BPROV:            /*6*/
            {  
					$msg="Provincia";
					$value="";
					$myerror="";
					if($flag1==OK)
						$value=$_POST['BPROV'];

					if($flag1==NOTOK)
					{
						$msg.="(*)";
						$myerror=getErrorMsg($v[BPROV]);
					}      
					echo "<tr>
					<td class=\"normalfont\"><b>$msg</b></td>
					<td align=\"left\">
					<input type=\"text\" class=\"formInput\" name=\"BPROV\" value=\"$value\"/>
					</td>
					<td>$myerror</td>
                    </tr> ";
			}break;
			case BSTATE:                    /*7*/
			{  
					$msg="Stato";
					$value="";
					$myerror="";
					if($flag1==OK)
						$value=$_POST['BSTATE'];

					if($flag1==NOTOK)
					{
						$msg="Stato(*)";
						$myerror=getErrorMsg($v[BSTATE]);
					}    
                  echo "  
                    <tr>
                      <td class=\"normalfont\"><b>$msg</b></td>
                      <td align=\"left\">
                        <input type=\"text\"  class=\"formInput\" name=\"BSTATE\" value=\"$value\" /></td>
                        <td>$myerror</td>
                    </tr>" ;
			}break;
			case DRAWRESIDENCE:          /*24*/
			{  
					echo "
                    <tr>
                     <td class=\"normalfont\"><b></b></td>
                     <td>
					 <b><br>Residenza</b>
                     </td>
                     </tr> ";
			}break;
            case RVIA:        /*8*/
            {     
					$msg="Indirizzo";
					$value="";
					$myerror="";
					if($flag1==OK)
						$value=$_POST['RVIA'];

					if($flag1==NOTOK)
					{
						$msg="Indirizzo(*)";
						$myerror=getErrorMsg($v[RVIA]);
					}   
                    echo"
                    <tr>
                      <td class=\"normalfont\"><b>$msg</b></td>
                      <td align=\"left\">
                        <input type=\"text\" class=\"formInput\" name=\"RVIA\" value=\"$value\" /></td>
                   <td>$myerror</td>
                    </tr>";
			}break;
			case RCIVICO:         /*9*/
			{
				
					$msg="Civico";
					$value="";
					$myerror="";
					if($flag1==OK)
						$value=$_POST['RCIVICO'];

					if($flag1==NOTOK)
					{
						$msg="Civico(*)";
						$myerror=getErrorMsg($v[RCIVICO]);
					}
                 echo "   
                      <tr>
                          <td class=\"normalfont\"><b>$msg</b></td>
                      <td align=\"left\">
                         <input type=\"text\" class=\"formInput\" name=\"RCIVICO\" value=\"$value\" /></td>
                      <td>$myerror</td>
                      </tr>";
			}break;
            case RCAP:    /*10*/
            {
					$msg="CAP";
					$value="";
					$myerror="";
					if($flag1==OK)
						$value=$_POST['RCAP'];

					if($flag1==NOTOK)
					{
						$msg="CAP(*)";
						$myerror=getErrorMsg($v[RCAP]);
					}
					echo "<tr>
                          <td class=\"normalfont\"><b>$msg</b></td>
                      <td align=\"left\">
                         <input type=\"text\" class=\"formInput\" name=\"RCAP\" maxlength=\"5\" size=\"5\"  value=\"$value\" /></td>
                      <td>$myerror</td>
                      </tr>";
			}break;
            case RCITY:     /*11*/
			{
					$msg="Città";
					$value="";
					$myerror="";
					if($flag1==OK)
						$value=$_POST['RCITY'];

					if($flag1==NOTOK)
					{
						$msg="Città(*)";
						$myerror=getErrorMsg($v[RCITY]);
					}
                    echo"<tr>
                      <td class=\"normalfont\"><b>$msg</b></td>
                      <td align=\"left\">
                        <input type=\"text\" class=\"formInput\" name=\"RCITY\"  /value=\"$value\"></td>
                        <td>$myerror</td>
                    </tr>" ;
             }break;
             case RPROV:    /*12*/
             {
				 	$msg="Provincia";
					$value="";
					$myerror="";
					if($flag1==OK)
						$value=$_POST['RPROV'];

					if($flag1==NOTOK)
					{
						$msg="Provincia(*)";
						$myerror=getErrorMsg($v[RPROV]);
					}      
                    echo "<tr>
                      <td class=\"normalfont\"><b>$msg</b></td>
                      <td align=\"left\">
                        <input type=\"text\"  class=\"formInput\" name=\"RPROV\" value=\"$value\"/></td>
                    <td>$myerror</td>
                    </tr>";
			 }break;        
             case RSTATE:       /*13*/
             {     
					$msg="Stato";
					$value="";
					$myerror="";
					if($flag1==OK)
						$value=$_POST['RSTATE'];

					if($flag1==NOTOK)
					{
						$msg="Stato(*)";
						$myerror=getErrorMsg($v[RSTATE]);
					}  
                     echo" <tr>
                      <td class=\"normalfont\"><b>$msg</b></td>
                      <td align=\"left\">
                        <input type=\"text\"  class=\"formInput\" name=\"RSTATE\"  value=\"$value\" /></td>
                    <td>$myerror</td>
                    </tr>";
			}break;
			case DRAWDOMICILIO:     /*25*/
			{       
				echo"
                    <tr>
                      <td class=\"normalfont\"><b></b></td>
                      <td>
					<b><br>Domicilio <br>(se diverso da residenza)</b>
                        </td>
                    </tr>";
			}break;
            case DVIA:  /*14*/
			{     
					$msg="Indirizzo";
					$value="";
					$myerror="";
					if($flag1==OK)
						$value=$_POST['DVIA'];

					if($flag1==NOTOK)
					{
						$msg="Indirizzo(*)";
						$myerror=getErrorMsg($v[DVIA]);
					}   
                    echo"<tr>
                      <td class=\"normalfont\"><b>$msg</b></td>
                      <td align=\"left\">
                        <input type=\"text\" class=\"formInput\" name=\"DVIA\"  value=\"$value\"/></td>
                    <td>$myerror</td>
                    </tr>";
			}break;
			case DCIVICO:        /*15*/
			{
				$msg="Civico";
					$value="";
					$myerror="";
					if($flag1==OK)
						$value=$_POST['DCIVICO'];

					if($flag1==NOTOK)
					{
						$msg="Civico(*)";
						$myerror=getErrorMsg($v[DCIVICO]);
					}
                    echo"
                      <tr>
                          <td class=\"normalfont\"><b>$msg</b></td>
                      <td align=\"left\">
                         <input type=\"text\" class=\"formInput\" name=\"DCIVICO\"  value=\"$value\"/></td>
                      <td>$myerror</td>
                      </tr>";
		   }break;
            case DCAP:                          /*16*/
            {
					$msg="CAP";
					$value="";
					$myerror="";
					if($flag1==OK)
						$value=$_POST['DCAP'];

					if($flag1==NOTOK)
					{
						$msg="CAP(*)";
						$myerror=getErrorMsg($v[DCAP]);
					}
				echo"<tr>
                          <td class=\"normalfont\"><b>$msg</b></td>
                      <td align=\"left\">
                         <input type=\"text\" class=\"formInput\" name=\"DCAP\" maxlength=\"5\" size=\"5\"  value=\"$value\"/></td>
                         <td>$myerror</td>
                      </tr>" ;
			}break;
            case DCITY:               /*17*/
			{
					$msg="Città";
					$value="";
					$myerror="";
					if($flag1==OK)
						$value=$_POST['DCITY'];

					if($flag1==NOTOK)
					{
						$msg.="(*)";
						$myerror=getErrorMsg($v[DCITY]);
					}
                   echo"<tr>
                      <td class=\"normalfont\"><b>$msg</b></td>
                      <td align=\"left\">
                        <input type=\"text\" class=\"formInput\" name=\"DCITY\" value=\"$value\"  /></td>
                    <td>$myerror</td>
                    </tr>";
			}break;
            case DPROV:        /*18*/
            {     
					$msg="Provincia";
					$value="";
					$myerror="";
					if($flag1==OK)
						$value=$_POST['DPROV'];

					if($flag1==NOTOK)
					{
						$msg.="(*)";
						$myerror=getErrorMsg($v[DPROV]);
					}   
                  echo"  <tr>
                      <td class=\"normalfont\"><b>$msg</b></td>
                      <td align=\"left\">
                        <input type=\"text\"  class=\"formInput\" name=\"DPROV\" value=\"$value\"/></td>
                    <td>$myerror</td>
                    </tr> ";
			}break;       
            case DSTATE:     /*19*/
            {       
					$msg="Stato";
					$value="";
					$myerror="";
					if($flag1==OK)
						$value=$_POST['DSTATE'];

					if($flag1==NOTOK)
					{
						$msg.="(*)";
						$myerror=getErrorMsg($v[DSTATE]);
					} 
                   echo" <tr>
                      <td class=\"normalfont\"><b>$msg</b></td>
                      <td align=\"left\">
                        <input type=\"text\"  class=\"formInput\" name=\"DSTATE\" value=\"$value\"  /></td>
                        <td>$myerror</td>
                    </tr>";
			}break;  
			case DRAWCONTATTO:   /*26*/
			{      
                
                     echo"
                                     <tr>
                      <td class=\"normalfont\"><b></b></td>
                      <td>
						<b><br>Contatti</b>
                        </td>
                    </tr> ";
			}break;
			 case CMAIL:           /*20*/
             {
				 
				 	$msg="E-Mail";
					$value="";
					$myerror="";
					if($flag1==OK)
						$value=$_POST['CMAIL'];

					if($flag1==NOTOK)
					{
						$msg.="(*)";
						$myerror=getErrorMsg($v[CMAIL]);
					}
                     echo "
                    <tr>
                      <td class=\"normalfont\"><b>$msg</b></td>
                      <td align=\"left\">
                        <input type=\"text\" class=\"formInput\" name=\"CMAIL\" value=\"$value\" /></td>
                        <td>$myerror</td>
                    </tr>";
			}break;
			case CPHONE:            /*21*/
			{
					$msg="Telefono";
					$value="";
					$myerror="";
					if($flag1==OK)
						$value=$_POST['CPHONE'];

					if($flag1==NOTOK)
					{
						$msg.="(*)";
						$myerror=getErrorMsg($v[CPHONE]);
					}
					echo"      
                    <tr>
                      <td class=\"normalfont\"><b>$msg</b></td>
                      <td align=\"left\">
                        <input type=\"text\" class=\"formInput\" name=\"CPHONE\"  value=\"$value\" /></td>
                        <td>$myerror</td>
                    </tr>";
			}break;
			case DRAWEND:       /*27*/
			{      
					echo"             
					</tbody>
					
					 </tr>
					
					 
					</table>
					<br>
					Consenso al trattamento dei dati sensibili (art. 26, D. lgs. N. 196/2003).
					 <input type=\"checkbox\" name=\"privacy\"/>
					<br><br>
					Consenso al compimento di ricerche di mercato, all'invio di materiale pubblicitario, all'invio di materiale promozionale di iniziative.
					 <input type=\"checkbox\" name=\"marketing\"/>
					 			<br><br>
					Procedendo con la richiesta di iscrizione, l'utente conferma di aver visionato e approvato lo <a href=\"$myself?id=statuto\">Statuto dell'Associazione Realsound</a>.
					<br><br>
					<central>
					<input type=\"submit\"  name=\"conferma\" value=\"Conferma\" />
					</central>
					</form></div></span></td>
					</tr>
					</table>
					</fieldset>";
			}break;
		}/*end switch*/
	}catch(Exception $e)
	{
		echo "ATTENZIONE ERRORE DI PROGRAMMAZIONE";
		echo"<pre>[DevError]:".$e->getMessage()."</pre>";
		exit(1);
	}
}



function drawTitle($flag)
{
	$v;
	if($flag>=4 && $flag <=7)
    {
		drawForm(DRAWBORN,READY);
		return;
	}
	if($flag >=8 && $flag<=13)
	{
		drawForm(DRAWRESIDENCE,READY);
		 return;
	}
	if($flag >=14 && $flag<=19)
	{
		drawForm(DRAWDOMICILIO,READY);
		 return;
	}
	if($flag >=20 && $flag<=21)
	{
		drawForm(DRAWCONTATTO,READY);
		return;
	}
	return;
}

function drawLogin()
{
		echo "Compila il seguente form per la richiesta di associazione.<br>
		Con il versamento della quota sociale e l'approvazione della tua iscrizione, potrai accedere a tutti i servizi riservati ai soci.<br><br>";
		drawForm(DRAWBEGIN,READY);
		for($i=0;$i<22;$i++)
		{                
			if($i==4 || $i==8 || $i==14 || $i==20)
			drawTitle($i);
			
			drawForm($i,READY);
		}
		drawForm(DRAWEND,READY);
}


/* Input: tipo di utente (vedi realsound-costanti.php). 
Output: TRUE --> SE nel momento in cui viene invocata la funzione:
* 		a) c'è un utente loggato
* 		b) l'utente passa i controlli previsti per $userType
* 		ELSE FALSE.
* 		*/

function checkPagePermission($typeToCheck, $genericPage=null){
	$check = false;
	if(isset($_SERVER['REMOTE_USER'])) {
			
		$whoami = $_SERVER['REMOTE_USER'];
		$userid = db_getUserIdByName($whoami);
			
		// acquisisco dati su flag e tipo dell'utente corrente
		$authorized = db_isFlagSet($userid, ATTIVO);
		
		if(empty($genericPage)){
			$forbiddenPrenotazioni = db_isFlagSet($userid, NO_PRENOTAZIONI);
		}
		else $forbiddenPrenotazioni = false;
		
		$mytype = db_typeOfUser($userid);
		
		// controllo che l'utente sia nel gruppo giusto per questa pagina
		switch($typeToCheck) {
			case SUPERUSER :
				$groupCheckExpression = ($mytype == $typeToCheck);
			break;
			case ADMIN :
				$groupCheckExpression = ($mytype == $typeToCheck or $mytype == SUPERUSER);
				break;
			case PROF :
				$groupCheckExpression = ($mytype == $typeToCheck or $mytype == SUPERUSER);
				break;
			// essendo Common il gruppo con il potere più basso,
			// salvo pagine particolari, la pagina la possono vedere tutti.
			case COMMON :
				$groupCheckExpression = true; 
				break;
			default : $groupCheckExpression = false;
		}
		
		// tutte le flag necessarie in AND... se passo tutti i controlli allora OK
		if($authorized and $groupCheckExpression and !$forbiddenPrenotazioni) {
			$check = true;
		}
	}
	return $check;
}

function usr_editUser() {
	$siteurl=$_SERVER['PHP_SELF'];
	$check = checkPagePermission(COMMON);
	if(!$check) {
		echo "<pre>Non sei autorizzato a visualizzare questa pagina.<br>Se sei l'amministratore, esegui il login e riprova.</pre>";
	}
	else {
		$thisusername = $_SERVER['REMOTE_USER'];
		$thisusertessera = db_getUserAttributeByKey('NumTessera', 'UserName', $thisusername);
		// richiesta modifica dei dati
		if(!empty($_POST['EDIT'])){
			secureEditUserData($_POST, $thisusertessera);
		}
		// prima volta sulla pagina
		else {
			$userdata = getUserDataByTessera($thisusertessera);
			$contact = getUserContact($thisusertessera);
			drawUserEditForm($userdata, $contact, 'editaccount');
		}
	}
}
