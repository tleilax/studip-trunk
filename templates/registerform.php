<script type="text/javascript" language="javascript" src="<?=$GLOBALS['ASSETS_URL']?>javascripts/md5.js"></script>
<script type="text/javascript" language="javaScript">
<!--
function checkusername(){
 var re_username = /<?=$validator->username_regular_expression?>/;
 var checked = true;
 if (document.login.username.value.length<4) {
    alert("<?=_("Der Benutzername ist zu kurz \\n- er sollte mindestens 4 Zeichen lang sein.")?>");
 		document.login.username.focus();
    checked = false;
    }
 if (re_username.test(document.login.username.value)==false) {
    alert("<?=_("Der Benutzername enth�lt unzul�ssige Zeichen \\n- er darf keine Sonderzeichen oder Leerzeichen enthalten.")?>");
 		document.login.username.focus();
    checked = false;
		}
 return checked;
}

function checkpassword(){
 var checked = true;
 if (document.login.password.value.length<4) {
    alert("<?=_("Das Passwort ist zu kurz \\n- es sollte mindestens 4 Zeichen lang sein.")?>");
 		document.login.password.focus();
    checked = false;
    }
 return checked;
}

function checkpassword2(){
 var checked = true;
if (document.login.password.value != document.login.password2.value) {
    alert("<?=_("Das Passwort stimmt nicht mit dem Best�tigungspasswort �berein!")?>");
    		document.login.password2.focus();
    checked = false;
    }
 return checked;
}

function checkVorname(){
 var re_vorname = /<?=$validator->name_regular_expression?>/;
 var checked = true;
 if (re_vorname.test(document.login.Vorname.value)==false) {
    alert("<?=_("Bitte geben Sie Ihren tats�chlichen Vornamen an.")?>");
 		document.login.Vorname.focus();
    checked = false;
		}
 return checked;
}

function checkNachname(){
 var re_nachname = /<?=$validator->name_regular_expression?>/;
 var checked = true;
 if (re_nachname.test(document.login.Nachname.value)==false) {
    alert("<?=_("Bitte geben Sie Ihren tats�chlichen Nachnamen an.")?>");
 		document.login.Nachname.focus();
    checked = false;
		}
 return checked;
}

function checkEmail(){
 var re_email = /<?=$validator->email_regular_expression?>/;
 var Email = document.login.Email.value;
 var checked = true;
 if ((re_email.test(Email))==false || Email.length==0) {
    alert("<?=_("Die E-Mail Adresse ist nicht korrekt!")?>");
 		document.login.Email.focus();
    checked = false;
    }
 return checked;
}

function checkdata(){
 // kompletter Check aller Felder vor dem Abschicken
 var checked = true;
 if (!checkusername())
  checked = false;
 if (!checkpassword())
  checked = false;
 if (!checkpassword2())
  checked = false;
 if (!checkVorname())
  checked = false;
 if (!checkNachname())
  checked = false;
 if (!checkEmail())
  checked = false;
 if (checked) {
   document.login.method = "post";
   document.login.action = "<?=$_SERVER['REQUEST_URI']?>";
   document.login.response.value = MD5(document.login.password.value);
   document.login.response2.value = MD5(document.login.password2.value);
   document.login.password.value = "";
   document.login.password2.value = "";
 }
 return checked;
}
// -->
</SCRIPT>

<table class="logintable" width="800" align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
	<td class="topic">
	<b>&nbsp;<?=_("Stud.IP - Registrierung")?></b>
	</td>
</tr>
<tr><td class="blank" align="top">
<div style="margin:20px;">
<b><?=_("Herzlich Willkommen!")?></b>
<br>
<?=_("Bitte f&uuml;llen Sie zur Anmeldung das Formular aus:")?>
<?if ( isset($username) ): ?>
	<!-- failed login code -->
	<table>
		<tr>
			<? parse_msg ("error�" . _("Bei der Registrierung ist ein Fehler aufgetreten:") . " <b>".$error_msg."</b>" . _("Bitte korrigieren Sie Ihre Eingaben und versuchen Sie es erneut") . "</b>", "�", "blank", 1, FALSE );?>
		</tr>
	</table>
<?endif;?>
<br><br>
<form name=login action="<?=$_SERVER['REQUEST_URI']?>" onsubmit="return checkdata()">
<table border=0 bgcolor="#eeeeee" align="center" cellspacing=2 cellpadding=4>
 <tr valign=top align=left>
  <td colspan="2"><?=_("Benutzername:")?></td>
  <td><input type="text" name="username" onchange="checkusername()" value="<?php print (isset($username) ? $username : "" ) ?>" size=32 maxlength=63></td>
 </tr>

 <tr valign=top align=left>
  <td colspan="2"><?=_("Passwort:")?></td>
  <td><input type="password" name="password" onchange="checkpassword()" size=32 maxlength=31></td>
 </tr>

 <tr valign=top align=left>
  <td colspan="2"><?=_("Passwortbest&auml;tigung:")?></td>
  <td><input type="password" name="password2" onchange="checkpassword2()" size=32 maxlength=31></td>
 </tr>

 <tr valign=top align=left>
  <td><?=_("Titel:")?>&nbsp;</td>
  <td align="right">
  <select name="title_chooser_front" onChange="document.login.title_front.value=document.login.title_chooser_front.options[document.login.title_chooser_front.selectedIndex].text;">
  <?
  for($i = 0; $i < count($GLOBALS['TITLE_FRONT_TEMPLATE']); ++$i){
	  echo "\n<option";
	  if($GLOBALS['TITLE_FRONT_TEMPLATE'][$i] == $title_front)
	  	echo " selected ";
	  echo ">" . $GLOBALS['TITLE_FRONT_TEMPLATE'][$i] . "</option>";
  }
  ?>
  </select>
  </td>
  <td><input type="text" name="title_front" value="<?php print (isset($title_front) ? $title_front : "" ) ?>" size=32 maxlength=63></td>
 </tr>

  <tr valign=top align=left>
  <td><?=_("Titel nachgest.:")?>&nbsp;</td>
  <td align="right">
  <select name="title_chooser_rear" onChange="document.login.title_rear.value=document.login.title_chooser_rear.options[document.login.title_chooser_rear.selectedIndex].text;">
  <?
  for($i = 0; $i < count($GLOBALS['TITLE_REAR_TEMPLATE']); ++$i){
	  echo "\n<option";
	  if($GLOBALS['TITLE_REAR_TEMPLATE'][$i] == $title_rear)
	  	echo " selected ";
	echo ">" . $GLOBALS['TITLE_REAR_TEMPLATE'][$i] . "</option>";
  }
  ?>
  </select></td>
  <td><input type="text" name="title_rear" value="<?php print (isset($title_rear) ? $title_rear : "" ) ?>" size=32 maxlength=63></td>
 </tr>
 <tr valign=top align=left>
  <td colspan="2"><?=_("Vorname:")?></td>
  <td><input type="text" name="Vorname" onchange="checkVorname()"  value="<?php print (isset($Vorname) ? $Vorname : "" ) ?>"size=32 maxlength=63></td>
 </tr>

 <tr valign=top align=left>
  <td colspan="2"><?=_("Nachname:")?></td>
  <td><input type="text" name="Nachname" onchange="checkNachname()"  value="<?php print (isset($Nachname) ? $Nachname : "" ) ?>"size=32 maxlength=63></td>
 </tr>

<tr valign=top align=left>
  <td colspan="2"><?=_("Geschlecht:")?></td>
  <td><input type="RADIO" <? if (!$geschlecht) echo "checked" ?> name="geschlecht" value="0"><?=_("m&auml;nnlich")?>&nbsp; <input type="RADIO" name="geschlecht" <? if ($geschlecht) echo "checked" ?> value="1"><?=_("weiblich")?></td>
 </tr>

 <tr valign=top align=left>
  <td colspan="2"><?=_("E-Mail:")?></td>
  <td><input type="text" name="Email" onchange="checkEmail()"  value="<?php print (isset($Email) ? $Email : "" ) ?>"size=32 maxlength=63></td>
 </tr>

 <tr>
  <td colspan="3" align=right>
  <input type="image"  name="submitbtn" <?=makeButton("uebernehmen","src")?> align="absmiddle" border="0" >
  &nbsp;<a href="index.php?cancel_login=1"><img <?=makeButton("abbrechen","src")?> align="absmiddle" border="0"></a>
  </td>
 </tr>
</table>
<br /><br />

<input type="hidden" name="login_ticket" value="<?=Seminar_Session::get_ticket();?>">
<input type="hidden" name="response"  value="">
<input type="hidden" name="response2"  value="">
</form>

</td></tr></table>

<script language="JavaScript">
<!--
  // Activate the appropriate input form field.
  if (document.login.username.value == '') {
    document.login.username.focus();
  } else {
    document.login.password.focus();
  }
// -->
</script>
