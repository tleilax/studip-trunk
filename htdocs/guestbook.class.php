<?
/*
guestbook.class.php - Guestbook for personal homepages
Copyright (C) 2003 Ralf Stockmann <rstockm@gwdg.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

class Guestbook {
	var $active;	// user has activated the guestbook
	var $number;	// number of entrys in the guestbook
	var $rights;	// do i have admin-rights for the guestbook
	var $user_id;	// user_id of the guestbook
	var $username;	// username
	var $msg_guest; // Output Message
	var $anchor;	// html anchor
	var $openclose; // open/close status
	var $perpage;	// count of entrys per guestbook-site
	var $guestpage;	// page of guestbook currently displayed

	// Konstruktor
	
	function Guestbook ($user_id,$rights, $guestpage) {
		$this->user_id = $user_id;
		$this->username = get_username($user_id);
		$this->checkGuestbook();
		$this->numGuestbook();
		$this->rights = $rights;
		$this->getRightsGuestbook();
		$this->msg_guest = "";
		$this->anchor = FALSE;
		$this->openclose = "close";
		$this->perpage = 10;
		$this->guestpage = $guestpage;
	}
	
	function checkGuestbook () {
		$db=new DB_Seminar;
		$db->query("SELECT * FROM user_info WHERE user_id = '$this->user_id' AND guestbook = '1'");
		if ($db->next_record())  // Guestbook is aktive
			$this->active = TRUE;
		else
			$this->active = FALSE;
	}
	
	function numGuestbook () {
		$db=new DB_Seminar;
		$db->query("SELECT count(*) as count FROM guestbook WHERE range_id = '$this->user_id'");
		if ($db->next_record())  
			$this->number = $db->f("count");
		else
			$this->number = 0;
		}
	
	function getRightsGuestbook () {
		global	$user;
		if ($this->user_id == $user->id || $this->rights == TRUE)
			$this->rights = TRUE;
		else
			$this->rights = FALSE;
	}
	
	function showGuestbook () {
		global $perm, $PHP_SELF;
		if ($this->rights == TRUE)
			if ($this->active==TRUE)
				$active = " ("._("aktiviert").")";
			else
				$active = " ("._("deaktiviert").")";
		if ($this->openclose == "close")
			$link = $PHP_SELF."?guestbook=open&username=$this->username#guest";
		else
			$link = $PHP_SELF."?guestbook=close&username=$this->username#guest";
		
		// set Anchor
		if ($this->anchor == TRUE)
			echo "<a name=\"guest\">";
			
		echo "\n<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"100%\" align=\"center\">";
		echo "\n<tr valign=\"baseline\"><td class=\"topic\"><img src=\"./pictures/guestbook.gif\" border=\"0\" align=\"texttop\"><b>&nbsp;&nbsp;";
		echo _("G�stebuch").$active;
				print("</b></td></tr>");
				
		echo "\n<tr><td class=\"blank\" colspan=$colspan>";
		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" align=\"center\"><tr><td class=\"blank\">";
		
		// Info Messages
		if ($this->msg_guest != "") {
			echo "<table width=\"100%%\" class=\"blank\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
			my_msg($this->msg_guest);
			echo "</table>";
		}
		//
		$titel = "<a href=\"$link\" class=\"tree\" >".$this->number."&nbsp;"._(" Eintr�ge")."</a>";
		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
		if ($this->active == TRUE)
			$zusatz .= $this->guest_navi();
		printhead ("100%","0",$link,$this->openclose,$new,"<img src=\"pictures/icon-guest.gif\">",$titel,$zusatz,$forumposting["chdate"],"TRUE",$index,$forum["indikator"]);	
			
		echo "</tr></table>";
		if ($this->openclose == "open") {
			echo "<table width=\"100%\" border=0 cellpadding=0 cellspacing=0 align=center><tr><td>";
			if ($this->active==TRUE) {
				$content = $this->showPostsGuestbook();
				if ($perm->have_perm("autor"))
					$content .= $this->formGuestbook();
			}
			
			printcontent ("100%",$formposting,$content,$buttons,TRUE,"");
			echo "</td></tr></table>";
			echo "<table width=\"100%\" border=0 cellpadding=3 cellspacing=0 align=center><tr><td class=\"steel2\">";
			if ($this->rights == TRUE)
				$buttons = $this->buttonsGuestbook();
			else
				$buttons = "";
			echo "$buttons</td><td class= \"steel2\" align=\"right\">$zusatz&nbsp;</td></tr></table>";
			
		}
		echo "</td></tr></table></td></tr></table>";
	}
	
	
// Berechnung und Ausgabe der Bl�tternavigation

/**
* builds the navigation element in page-view
*
* @param	array	forum contains several data of the actual board-site
*
* @return	string 	navi contains the HTML of the navigation
*
**/

function guest_navi () {
	global $PHP_SELF;
	$i = 1;
	$maxpages = ceil($this->number / $this->perpage);
	$ipage = ($this->guestpage / $this->perpage)+1;
	if ($ipage != 1)
		$navi .= "<a href=\"$SELF_PHP?guestpage=".($ipage-2)*$this->perpage."&guestbook=open&username=$this->username#guest\"><font size=-1>" . _("zur�ck") . "</a> | </font>";
	else
		$navi .= "<font size=\"-1\">Seite: </font>";
	while ($i <= $maxpages) {
		if ($i == 1 || $i+2 == $ipage || $i+1 == $ipage || $i == $ipage || $i-1 == $ipage || $i-2 == $ipage || $i == $maxpages) {
			if ($space == 1) {
				$navi .= "<font size=-1>... | </font>";
				$space = 0;
			}
			if ($i != $ipage)
				$navi .= "<a href=\"$SELF_PHP?guestpage=".($i-1)*$this->perpage."&guestbook=open&username=$this->username#guest\"><font size=-1>".$i."</a></font>";
			else
				$navi .= "<font size=\"-1\"><b>".$i."</b></font>";
			if ($maxpages != 1)
				$navi .= "<font size=\"-1\"> | </font>";
		} else {
			$space = 1;
		}
		$i++;	
	}
	if ($ipage != $maxpages)
		$navi .= "<a href=\"$SELF_PHP?guestpage=".($ipage)*$this->perpage."&guestbook=open&username=$this->username#guest\"><font size=-1> " . _("weiter") . "</a></font>";
	return $navi;
}
	
	
	
	
	function showPostsGuestbook () {
		global $PHP_SELF;
		$i = 0;
		$db=new DB_Seminar;
		$output = "<table class=\"blank\" width=\"98%%\" border=\"0\" cellpadding=\"5\" cellspacing=\"0\">";
		$db->query("SELECT * FROM guestbook WHERE range_id = '$this->user_id' ORDER BY mkdate DESC LIMIT $this->guestpage, $this->perpage");
		while ($db->next_record()) {  
			$position = $this->number - ($this->guestpage+$i);
			$output .= "<tr><td class=\"steel2\"><b><font size=\"-1\">#$position - <a href=\"$PHP_SELF?username=".get_username($db->f("user_id"))."\">";
			$output .= sprintf(_("%s hat am %s geschrieben:"), get_fullname($db->f("user_id"))."</a>", date("d.m.Y - H:i", $db->f("mkdate")));
			$output .= "</font></b></td></tr>"
				. "<tr><td class=\"steelgraulight\"><font size=\"-1\">".quotes_decode(formatready($db->f("content")))."</font><p align=\"right\">";
			if ($this->rights == TRUE)
				$addon = "<a href=\"".$PHP_SELF."?guestbook=delete&guestpage=$this->guestpage&deletepost=".$db->f("post_id")."&username=$this->username#guest\">" . makeButton("loeschen", "img") . "</a>";
			else
				$addon = "&nbsp;";
			
			$output .= $addon
				."</p></td></tr>"
				. "<tr><td class=\"steel1\">&nbsp;</td></tr>";
			$i++;
		}
		$output .= "</table>";
		return $output;	
	}
		
	function formGuestbook () {
		global $auth, $PHP_SELF;
		if ($auth->auth["jscript"]) {
			$max_col = round($auth->auth["xres"] / 12 );
		} else 
			$max_col =  64 ; //default f�r 640x480
		$cols = round($max_col*0.45);
		if ($cols < 28) $cols = 28;
		$text = "<p align=\"center\">"._("Geben Sie hier Ihren G�stebuchbeitrag ein!")."</p>";
	
			$form =	"<form name=\"guestbook\" method=\"post\" action=\"".$PHP_SELF."#guest\">"
			."<input type=hidden name=guestbook value='$this->user_id'>"
			."<input type=hidden name=username value='$this->username'>"
			.$text
			."<div align=center><textarea name=post style=\"width:70%\" cols=\"". $cols."\"  rows=8 wrap=virtual>"
			."</textarea>"
			."<br><br><input type=image name=create value=\"abschicken\" " . makeButton("abschicken", "src") . " align=\"absmiddle\" border=0>&nbsp;"
			."&nbsp;&nbsp;<a href=\"show_smiley.php\" target=\"new\"><font size=\"-1\">"._("Smileys")."</a>&nbsp;&nbsp;"."<a href=\"help/index.php?help_page=ix_forum6.htm\" target=\"new\"><font size=\"-1\">"._("Formatierungshilfen")."</a><br>";
		return $form;
	}
		
	function buttonsGuestbook () {
		global $PHP_SELF;
		$buttons = "";
		if ($this->active == TRUE) {
			$buttons .= "&nbsp;&nbsp;<a href=\"".$PHP_SELF."?guestbook=switch&username=$this->username&rnd=".rand()."#guest\">" . makeButton("deaktivieren", "img") . "</a>";
		} else {
			$buttons .= "<a href=\"".$PHP_SELF."?guestbook=switch&username=$this->username#guest\">" . makeButton("aktivieren", "img") . "</a>";
		}
		$buttons .= "&nbsp;&nbsp;<a href=\"".$PHP_SELF."?guestbook=erase&username=$this->username#guest\">" . makeButton("alleloeschen", "img") . "</a>";
		return $buttons;	
	}

	function actionsGuestbook ($guestbook,$post="",$deletepost="") {
		if ($this->rights == TRUE) {
			if ($guestbook=="switch")
				$this->msg_guest = $this->switchGuestbook();
			if ($guestbook=="erase")
				$this->msg_guest = $this->eraseGuestbook();
			if ($guestbook=="delete")
				$this->msg_guest = $this->deleteGuestbook($deletepost);
		}
		
		if ($post) {
			$msg = $this->addPostGuestbook($this->user_id,$post);
		}
		if ($guestbook != "close")
			$this->openclose = "open";
		$this->checkGuestbook();
		$this->numGuestbook();
		$this->anchor = TRUE;
	}
	
	function switchGuestbook () {
		$db=new DB_Seminar;
		if ($this->active == "TRUE") { // Guestbook is activated
			$db->query("UPDATE user_info SET guestbook='0' WHERE user_id='$this->user_id'");
			$tmp = _("Sie haben das G�stebuch deaktiviert. Es ist nun nicht mehr sichtbar.");
		} else { 
			$db->query("UPDATE user_info SET guestbook='1' WHERE user_id='$this->user_id'");
			$tmp = _("Sie haben das G�stebuch aktiviert: Besucher k�nnen nun schreiben!");
		}
		return $tmp;
	}
	
	function eraseGuestbook () {
		$db=new DB_Seminar;
		$db->query("DELETE FROM guestbook WHERE range_id = '$this->user_id'");	
		$tmp = _("Sie haben alle Beitr�ge des G�stebuchs gel�scht!");	
		return $tmp;
	}
	
	function deleteGuestbook ($deletepost) {
		if ($this->getRangeGuestbook($deletepost)==TRUE) {
			$db=new DB_Seminar;
			$db->query("DELETE FROM guestbook WHERE post_id = '$deletepost'");	
			$tmp = _("Sie haben einen Beitrag im G�stebuch gel�scht!");	
		} else {
			$tmp = _("Netter Versuch!");	
		}
		return $tmp;
	}
	
	function getRangeGuestbook ($post_id) {
		$db=new DB_Seminar;
		$db->query("SELECT range_id FROM guestbook WHERE post_id = '$post_id'");
		if ($db->next_record())  
			if ($db->f("range_id")==$this->user_id)
				return TRUE;
			else
				return FALSE;
	}
		
	function makeuniqueGuestbook () {
		// baut eine ID die es noch nicht gibt

		$hash_secret = "kershfshsshdfgz";
		$db=new DB_Seminar;
		$tmp_id=md5(uniqid($hash_secret));
		$db->query ("SELECT post_id FROM guestbook WHERE post_id = '$tmp_id'");	
		if ($db->next_record()) 	
			$tmp_id = $this->makeuniqueGuestbook(); //ID gibt es schon, also noch mal
		return $tmp_id;
	}
	
	function addPostGuestbook($range_id,$content) {
		global $user;
		$now = time();
		$post_id = $this->makeuniqueGuestbook();
		$user_id = $user->id;
		$db=new DB_Seminar;
		$db->query("INSERT INTO guestbook (post_id,range_id,user_id,mkdate,content) values ('$post_id', '$range_id', '$user_id', '$now', '$content')");	
		return $post_id;
	}
}

/*





















*/

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>