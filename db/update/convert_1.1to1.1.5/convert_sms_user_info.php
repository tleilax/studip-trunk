<?

// page_open
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("root");

// initialise session
include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php");
	
// -- here you have to put initialisations for the current page
require_once ("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once ("$ABSOLUTE_PATH_STUDIP/msg.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/statusgruppe.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/reiter.inc.php");

// need kontact to mothership
$db = new DB_Seminar;
$db2 = new DB_Seminar;

// Output of html head and Stud.IP head
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php");
include ("$ABSOLUTE_PATH_STUDIP/header.php");


// do we use javascript?
if ($auth->auth["jscript"]) {
	echo "<script language=\"JavaScript\">var ol_textfont = \"Arial\"</script>";
	echo "<DIV ID=\"overDiv\" STYLE=\"position:absolute; visibility:hidden; z-index:1000;\"></DIV>";
	echo "<SCRIPT LANGUAGE=\"JavaScript\" SRC=\"overlib.js\"></SCRIPT>";
}

if (($change_view) || ($delete_user) || ($view=="Messaging")) {
	change_messaging_view();
	echo "</td></tr></table>";
	page_close();
	die;
} 

?>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="blank">
			<br><br><center><font style="font-weight: bold">Convert-Script</font> die user_info anzupassen...</center><br><hr noshade size="9">
			<?
			$db=new DB_Seminar;
			$count = "0";
			$db->query("SELECT * FROM user_info WHERE  email_forward = '0'");
			while ($db->next_record()) {
				$db2->query("UPDATE user_info SET email_forward = '1' WHERE  user_id = '".$db->f("user_id")."'");
				$count = $count+1; // zaehle
			}
			echo $count." Eintr&auml;ge bearbeitet.";
			?>
		</td>
	</tr>
</table>

<?

// Save data back to database.
page_close() ?>

</body>
</html>
