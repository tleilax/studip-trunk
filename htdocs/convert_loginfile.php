<?php
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", user => "Seminar_User"));
$perm->check("root");
include "functions.php";
include "statusgruppe.inc.php";
set_time_limit(0); //bis zum bitteren Ende...
class FakeUser{
	var $user_variables;
	var $user_id = "nobody";
	var $name = "Seminar_User";
	var $session;

	function FakeUser(){
		$this->session = $GLOBALS['sess'];
		if (!is_object($this->session)){
			die("No Session Object found");
		}
	}

	function microwaveIt(){
		$this->user_variables = null;
		$this->session->get_lock();
		$vals = $this->session->that->ac_get_value($this->user_id, $this->name);
		$vals = str_replace("\$GLOBALS", "\$this->user_variables", $vals);
		eval(sprintf(";%s",$vals));
	}

	function &getVariable($name){
		if(!isset($this->user_variables[$name]))
			return false;
		return $this->user_variables[$name];
	}

	function killVariable($name){
		if(!isset($this->pt[$name]))
			return false;
		unset($this->pt[$name]);
		unset($this->user_variables[$name]);
		return true;
	}

	function freezeIt() {
		$str = "";
		$this->serialize("this->in",&$str);
		$this->serialize("this->pt",&$str);

		reset($this->pt);
		while ( list($thing) = each($this->pt) ) {
			$thing = trim($thing);
			if ($thing) {
				$this->serialize("this->user_variables['".$thing."']",&$str);
			}
		}
		$str = str_replace("\$this->user_variables", "\$GLOBALS", $str);
		$ret = $this->session->that->ac_store($this->user_id, $this->name, $str);
		$this->session->release_lock();

	}
	function serialize($prefix, $str) {
		static $t,$l,$k;

	## Determine the type of $$prefix
	eval("\$t = gettype(\$$prefix);");
	switch ( $t ) {

	case "array":
		## $$prefix is an array. Enumerate the elements and serialize them.
		eval("reset(\$$prefix); \$l = gettype(list(\$k)=each(\$$prefix));");
		$str .= "\$$prefix = array(); ";
		while ( "array" == $l ) {
			## Structural recursion
			$this->serialize($prefix."['".ereg_replace("([\\'])", "\\\\1", $k)."']", &$str);
			eval("\$l = gettype(list(\$k)=each(\$$prefix));");
		}

	break;
	case "object":
		## $$prefix is an object. Enumerate the slots and serialize them.
		eval("\$k = \$${prefix}->classname; \$l = reset(\$${prefix}->persistent_slots);");
		$str.="\$$prefix = new $k; ";
		while ( $l ) {
			## Structural recursion.
			$this->serialize($prefix."->".$l,&$str);
			eval("\$l = next(\$${prefix}->persistent_slots);");
		}

	break;
	default:
		## $$prefix is an atom. Extract it to $l, then generate code.
		eval("\$l = \$$prefix;");
		$str.="\$$prefix = '".ereg_replace("([\\'])", "\\\\1", $l)."'; ";


	break;
	}
	}
}

$db=new DB_Seminar;
$db2=new DB_Seminar;
$db3=new DB_Seminar;
$test = new FakeUser();
$c = 0;
$db->query("select * from active_sessions WHERE name = 'Seminar_User' AND sid != 'nobody'");
while ($db->next_record()) {
	$changed = $db->f("changed");
	$test->user_id = $db->f("sid");
	$test->microwaveIt();
	$lastvisit =& $test->getVariable("loginfilelast");
	$now =& $test->getVariable("loginfilenow");
	echo ++$i . " : " . $db->f("sid") . " ";
	$c=0;
	$e=0;
	if (is_array($now))
		foreach ($now as $key=>$val) {
			$db2->query("REPLACE INTO object_user_visits SET object_id = '$key', user_id ='".$db->f("sid")."', type='".get_object_type($key)."', visitdate='$val', last_visitdate = '".$lastvisit[$key]."'");
			$db2->query("REPLACE INTO object_user_visits SET object_id = '$key', user_id ='".$db->f("sid")."', type='forum', visitdate='$val', last_visitdate = '".$lastvisit[$key]."'");
			$db2->query("REPLACE INTO object_user_visits SET object_id = '$key', user_id ='".$db->f("sid")."', type='documents', visitdate='$val', last_visitdate = '".$lastvisit[$key]."'");
			$db2->query("REPLACE INTO object_user_visits SET object_id = '$key', user_id ='".$db->f("sid")."', type='wiki', visitdate='$val', last_visitdate = '".$lastvisit[$key]."'");
			$db2->query("REPLACE INTO object_user_visits SET object_id = '$key', user_id ='".$db->f("sid")."', type='scm', visitdate='$val', last_visitdate = '".$lastvisit[$key]."'");
			$db2->query("REPLACE INTO object_user_visits SET object_id = '$key', user_id ='".$db->f("sid")."', type='schedule', visitdate='$val', last_visitdate = '".$lastvisit[$key]."'");
			$db2->query("REPLACE INTO object_user_visits SET object_id = '$key', user_id ='".$db->f("sid")."', type='literature', visitdate='$val', last_visitdate = '".$lastvisit[$key]."'");
			$e=$e+7;
			//News
			$query3 = ("SELECT news_id FROM news_range WHERE range_id = '".$key."' ");
			$db3->query($query3);
			while ($db3->next_record()) {
				$db2->query("SELECT visitdate FROM object_user_visits WHERE object_id = '".$db3->f("news_id")."' ");
				$db2->next_record();
				if ($val > $db2->f("visitdate"))
					$db2->query("REPLACE INTO object_user_visits SET object_id = '".$db3->f("news_id")."', user_id ='".$db->f("sid")."', type='news', visitdate='$val', last_visitdate = '".$lastvisit[$key]."'");
				$e++;
			}
			//Votes
			$query3 = ("SELECT vote_id FROM vote WHERE range_id = '".$key."' ");
			$db3->query($query3);
			while ($db3->next_record()) {
				$db2->query("REPLACE INTO object_user_visits SET object_id = '".$db3->f("vote_id")."', user_id ='".$db->f("sid")."', type='vote', visitdate='$val', last_visitdate = '".$lastvisit[$key]."'");
				$e++;
			}
			//Evals
			$query3 = ("SELECT eval_id FROM eval_range WHERE range_id = '".$key."' ");
			$db3->query($query3);
			while ($db3->next_record()) {
				$db2->query("REPLACE INTO object_user_visits SET object_id = '".$db3->f("eval_id")."', user_id ='".$db->f("sid")."', type='eval', visitdate='$val', last_visitdate = '".$lastvisit[$key]."'");
				$e++;
			}
			$c++;
		}
	//kill the old session-variable
	$test->killVariable("loginfilenow");
	$test->killVariable("loginfilelast");
	$test->freezeIt();
	$db2->query("UPDATE active_sessions set changed='$changed' WHERE name='Seminar_user' AND sid='".$db->f("sid")."'");

	echo "<br/>".$c ."&nbsp;Veranstaltungen/Einrichtungen konvertiert, $e Eintr&auml;ge erzeugt...";
	echo "<hr>";
	$cc = $cc + $c;
	$ee = $ee + $e;
}
$db2->query("OPTIMIZE TABLE `active_sessions` ");
echo "<br/>".$cc ."&nbsp;Veranstaltungen/Einrichtungen konvertiert, $ee Eintr&auml;ge erzeugt... WOW!";
echo "<br />uff, geschafft!";

page_close();
?>
