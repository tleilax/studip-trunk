<?
require_once 'lib/classes/Messagebox.class.php';

page_open(array('sess' => 'Seminar_Session',
                'auth' => 'Seminar_Auth',
                'perm' => 'Seminar_Perm',
                'user' => 'Seminar_User'));

// Start of Output
include 'lib/seminar_open.php'; // initialise Stud.IP-Session
include 'lib/include/html_head.inc.php'; // Output of html head
include 'lib/include/header.php';   //hier wird der "Kopf" nachgeladen

?>
    <div id="layout_container" style="padding: 1em">

		<div class="topic"><b>Step000162</b></div>
		<div class="steel1"><br>Dies ist eine Beispielseite f�r die neuen Messageboxen...<br><br></div>
		<br>

		<?=Messagebox::get('INFO')->show('Info-Nachricht');?>
		<?=Messagebox::get('SUCCESS')->show('Erfolgs-Nachricht');?>
		<?=Messagebox::get('ERROR')->show('Error-Nachricht, sollte am besten nie erscheinen...');?>
		<?=Messagebox::get('WARNING')->show('Warning-Nachricht', array('Detail Fehler 1', 'Fehler 2', 'Fehler 3'));?>

		<div class="clear"></div>
	</div>
<?
include 'lib/include/html_end.inc.php';
page_close();
?>