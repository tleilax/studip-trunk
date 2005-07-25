<?php
/*
show_news.php - Anzeigefunktion fuer News
Copyright (C) 2001 Ralf Stockmann <rstockm@gwdg.de>, Cornelis Kater <ckater@gwdg.de>, Stefan Suchi <suchi@gmx.de>

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
//$Id$

require_once ("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/language.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/object.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/StudipNews.class.php");

function process_news_commands(&$cmd_data) {
	global $nopen, $nclose, $comopen, $comnew, $comsubmit, $comdel, $comdelnews;

	//Auf und Zuklappen News

	$cmd_data["comopen"]='';
	$cmd_data["comnew"]='';
	$cmd_data["comsubmit"]='';
	$cmd_data["comdel"]='';
	$cmd_data["comdelnews"]='';

	if ($nopen) $cmd_data["nopen"]=$nopen;
	if ($nclose)  $cmd_data["nopen"]='';
	if ($comopen) $cmd_data["comopen"]=$comopen;
	if ($comnew) $cmd_data["comnew"]=$comnew;
	if ($comsubmit) $cmd_data["comsubmit"]=$comsubmit;
	if ($comdel) $cmd_data["comdel"]=$comdel;
	if ($comdelnews) $cmd_data["comdelnews"]=$comdelnews;
}

function get_num_comments($object_id) {
	$query="SELECT COUNT(*) AS count FROM comments WHERE object_id='$object_id'";
	$db=new DB_Seminar();
	$db->query($query);
	$db->next_record();
	return $db->f("count");
}

function get_comments($object_id) {
	global $_fullname_sql;
	$comments=array();
	$query="SELECT comments.content, comments.mkdate, " . $_fullname_sql['full'] ." AS fullname, a.username AS username, comments.comment_id FROM comments LEFT JOIN auth_user_md5 a USING (user_id) LEFT JOIN user_info USING (user_id) WHERE object_id='$object_id' ORDER BY comments.mkdate";
	$db=new DB_Seminar();
	$db->query($query);
	while ($db->next_record()) {
		$comments[]=array($db->f("content"), $db->f("fullname"), $db->f("username"), date("d.m.Y, H:i",$db->f("mkdate")), $db->f("comment_id"));
	}
	return $comments;
}

function insert_comment($object_id, $content) {
	global $auth;
	$db=new DB_Seminar();
	$q="INSERT INTO comments SET object_id='$object_id', user_id='".$auth->auth['uid']."', comment_id='".md5(uniqid("ohohokommentarohoh",1))."', content='$content', mkdate='".time()."', chdate='".time()."'";
	$db->query($q);
}

function delete_comment($comment_id) {
	global $auth, $perm;
	$ok=0;
	$db=new DB_Seminar();
	$q="SELECT * FROM comments WHERE comment_id='$comment_id'";
	$db->query($q);
	if ($db->next_record()) {
		if ($perm->have_perm("root")) {
			$ok=1;
		} else if ($db->f("user_id")!=$auth->auth["uid"]) {
			$db2=new DB_Seminar();
			$q2="SELECT * FROM news WHERE news_id='".$db->f("object_id")."'";
			$db2->query($q2);
			if ($db2->next_record() && $db2->f("user_id")==$auth->auth["uid"]) {
				$ok=1;
			}
		} else {
			$ok=1;
		}
	}
	if ($ok) {
			$q="DELETE FROM comments WHERE comment_id='$comment_id'";
			$db->query($q);
	}
	return $ok;
}

function show_news($range_id, $show_admin=FALSE,$limit="", $open, $width="100%", $last_visited=0, $cmd_data) {
	global $_fullname_sql,$PHP_SELF,$auth , $SessSemName;

	$db2=new DB_Seminar;

	$aktuell=time();

	if (get_config('NEWS_RSS_EXPORT_ENABLE')){
		$news_author_id = StudipNews::GetRssIdFromUserId($range_id);
	}

	$news =& StudipNews::GetNewsByRange($range_id, true);

	if ($SessSemName[1] == $range_id){
		$admin_link = ($SessSemName["class"]=="sem") ? "new_sem=TRUE&view=news_sem" :  "new_inst=TRUE&view=news_inst";
	} else if ($range_id == $auth->auth['uid']){
		$admin_link = "range_id=self";
	} else if ($range_id == "studip"){
		$admin_link = "range_id=studip";
	}

	if (!count($news)) {
		if ($show_admin) {
			echo"\n<table  border=\"0\" bgcolor=\"#FFFFFF\" cellspacing=\"0\" cellpadding=\"2\" align=\"center\" width=\"$width\" >";
			echo"\n<tr><td class=\"topic\" colspan=\"2\" width=\"99%\"><img src=\"./pictures/news2.gif\" border=\"0\"". tooltip(_("Newsticker. Klicken Sie auf die Pfeile (rechts), um neue News in diesen Bereich einzustellen. Klicken Sie auf die Pfeile am linken Rand, um den ganzen Nachrichtentext zu lesen.")) . "align=\"texttop\"><b>&nbsp;" . _("News") . "</b></td>";
			echo"\n<td align = \"right\" class=\"topic\">";
			printf ("&nbsp;<a href=\"admin_news.php?%s&cmd=new_entry\"><img src=\"./pictures/pfeillink.gif\" border=\"0\"" . tooltip(_("News einstellen")) . "></a>&nbsp;", $admin_link);
			echo"\n</td></tr>";
			echo "\n<tr><td class=\"steel1\" colspan=\"3\"><blockquote><br /><font size=\"-1\">" . _("Es sind keine aktuellen News vorhanden. Um neue News zu erstellen, klicken Sie auf die Doppelpfeile.") . "<br />&nbsp; </font></blockquote>";
			echo "\n</td></tr></table>";
			return TRUE;
		} else {
			return FALSE;
		}
	} else {
		$colspan=2;

		//Ausgabe der Kopfzeile vor erster auszugebener News
		echo"\n<table  border=\"0\" bgcolor=\"#FFFFFF\" cellspacing=\"0\" cellpadding=\"2\" align=\"center\" width=\"$width\" >";
		echo"\n<tr><td class=\"topic\" colspan=\"2\" width=\"99%\"><img src=\"./pictures/news2.gif\" border=\"0\"". tooltip(_("Newsticker. Klicken Sie auf die Pfeile (rechts), um neue News in diesen Bereich einzustellen. Klicken Sie auf die Pfeile am linken Rand, um den ganzen Nachrichtentext zu lesen.")) . "align=\"texttop\"><b>&nbsp;" . _("News") . "</b></td>";
		if ($news_author_id) {
			$colspan++;
			echo "\n<td align=\"right\" width=\"36\" class=\"topic\">";
			echo "\n<a href=\"rss.php?id=$news_author_id\"><img src=\"pictures/rss.gif\" border=\"0\"" . tooltip(_("RSS-Feed")) . "></a>";
			echo "\n</td>";
		}
		if ($show_admin) {
			$colspan++;
			echo"\n<td align = \"right\" width=\"1%\" class=\"topic\" nowrap>";
			printf ("&nbsp;<a href=\"admin_news.php?%s&modus=admin&cmd=show\"><img src=\"./pictures/pfeillink.gif\" border=\"0\"" . tooltip(_("News bearbeiten")) . "></a>&nbsp;", $admin_link);
			echo"\n</td>";
		}
		echo "\n</tr>\n<tr><td colspan=$colspan>";

		// Ausgabe der Daten
		foreach ($news as $news_id => $news_detail) {
			$tmp_titel=htmlReady(mila($news_detail["topic"]));
			$titel='';
			if ($open == $news_id) {
				$link=$PHP_SELF."?nclose=true";
				$titel=$tmp_titel."<a name='anker'>";
				if ($news_detail["user_id"] != $auth->auth["uid"])
				object_add_view($news_id);  //Counter for news - not my own
				object_set_visit($news_id, "news"); //and, set a visittime
			} else {
				$link=$PHP_SELF."?nopen=".$news_id;
				$titel=$tmp_titel;
			}

			$icon="&nbsp;<img src=\"./pictures/news-icon.gif\" border=0>";

			$db2->query("SELECT username, " . $_fullname_sql['full'] ." AS fullname FROM auth_user_md5 a LEFT JOIN user_info USING (user_id) WHERE a.user_id='".$news_detail["user_id"]."'");
			$db2->next_record();
			$link .= "&username=".$db2->f("username") . "#anker";
			$zusatz="<a href=\"about.php?username=".$db2->f("username")."\"><font size=-1 color=\"#333399\">".htmlReady($db2->f("fullname"))."</font></a><font size=-1>&nbsp;".date("d.m.Y",$news_detail["date"])." | <font color=\"#005500\">".object_return_views($news_id)."<font color=\"black\"> |</font>";
			
			$unamelink = '&username='.$db2->f('username');
			$uname = $db2->f('username');

			if ($news_detail['allow_comments']==1) {
				$num_comments=get_num_comments($news_detail['news_id']);
				$zusatz.=" <font color=\"#aaaa66\">".$num_comments."</font><font color=\"black\"> |</font>";
			}

			if ($link)
			$titel = "<a href=\"$link\" class=\"tree\" >".$titel."</a>";

			$tempnew = (($news_detail['chdate'] >= object_get_visit($news_id,'news',false,false)) && ($news_detail['user_id'] != $auth->auth["uid"]));
			echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" align=\"center\"><tr>";
			if ($open == $news_id)
			printhead(0, 0, $link, "open", $tempnew, $icon, $titel, $zusatz, $news_detail["date"]);
			else
			printhead(0, 0, $link, "close", $tempnew, $icon, $titel, $zusatz, $news_detail["date"]);

			echo "</tr></table>	";

			if ($open == $news_id) {
				list ($content,$admin_msg)=explode("<admin_msg>",$news_detail['body']);
				$content = formatReady($content);
				if ($news_detail['chdate_uid']){
					$admin_msg = StudipNews::GetAdminMsg($news_detail['chdate_uid'],$news_detail['chdate']);
				}
				if ($admin_msg) {
					$content.="<br><br><i>".htmlReady($admin_msg)."</i>";
				}
				if (!$content)
				$content=_("Keine Beschreibung vorhanden.") . "\n";
				else
				$content.="<br>";

				if ($auth->auth["uid"]==$news_detail["user_id"] || $show_admin) {
					$edit="<a href=\"admin_news.php?cmd=edit&edit_news=".$news_id."&$admin_link\">" . makeButton("bearbeiten") . "</a>";
					$edit.="&nbsp;<a href=\"admin_news.php?cmd=kill&kill_news=".$news_id."&$admin_link\">" . makeButton("loeschen") . "</a>";
				}

				//
				// Kommentare
				//
				if ($news_detail['allow_comments']==1) {
					$cmdline="<p align=center><font size=-1><a href=".$PHP_SELF."?comnew=".$news_detail['news_id'].$unamelink.">"._("Kommentar schreiben")."</a></font></p>";
					$showcomments=0;
					if ($cmd_data["comsubmit"]==$news_detail['news_id']) {
						global $comment_content;
						insert_comment($news_detail['news_id'], $comment_content);
						$showcomments=1;
					} else if ($cmd_data["comdelnews"]==$news_detail['news_id']) {
						delete_comment($cmd_data["comdel"]);
						$showcomments=1;
					} else if ($cmd_data["comnew"]==$news_detail['news_id']) {
						$formular="&nbsp;<br>\n<form action=\"".$PHP_SELF."\" method=\"get\">";
						$formular.="<table cellpadding=\"2\" cellspacing=\"0\" width=\"80%\" align=\"center\" style=\"background:#ffffcc; border: 1px black solid;\">";
						$formular.="<input type=hidden name=\"comsubmit\" value=\"".$news_detail['news_id']."\">";
						$formular.="<input type=hidden name=\"username\" value=\"$uname\">";
						$formular.="<tr><td style=\"border-bottom: 1px black dotted;\"><font size=-1>"._("Bitte Kommentar eingeben:")."</font></td></tr>";
						$formular.="<tr><td align=\"center\">";
						$formular.="<textarea name=\"comment_content\" rows=10 cols=50></textarea>";
						$formular.="</td></tr>";
						$formular.="<tr><td align=\"center\">";
						$formular.="<input type=\"image\" ".makeButton("absenden","src").">";
						$formular.="</td></tr>";
						$formular.="</table></form><p>&nbsp;</p>";
						$content.=$formular;
						$showcomments=1;
						$newcomment=1;

					}
					if ($showcomments || $cmd_data["comopen"]==$news_detail['news_id']) {
						$comments="\n<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"80%\" align=\"center\">";
						$c=get_comments($news_detail['news_id']);
						if (count($c)) {
							foreach ($c as $comment) {

								$comments.="<tr><td>";
								$comments.="<table style=\"border: 1px black solid;\" cellpadding=3 cellspacing=0 width=100%>";
								$comments.="<tr style=\"background:#ffffcc\"><td align=right style=\"border-bottom: 1px black dotted\">";
								$comments.="<font size=-1><a href=\"about.php?username=$comment[2]\">$comment[1]</a>  $comment[3]</font>";
								if (get_userid($comment[2])==$auth->auth["uid"] || $news_detail['user_id']==$auth->auth["uid"]) {
									$comments.="&nbsp;&nbsp;<a href=\"$PHP_SELF?comdel=".$comment[4]."&comdelnews=".$news_detail['news_id']."\"><img src=\"pictures/trash.gif\" border=0></a>&nbsp;";
								}
								$comments.="</td></tr>";
								$comments.="<tr style=\"background:#ffffcc;\"><td><font size=-1>".formatReady($comment[0])."<br>&nbsp;</font></td></tr>";
								$comments.="</table></td></tr>";
							}
						}
						$comments.="</table>";
						if (!$newcomment) {
							$content.=$cmdline.$comments;
							$numcomments=get_num_comments($news_detail['news_id']);
							if (!$cmd_data['comdel'] || $numcomments>=1) {
								$content.=$cmdline;
							}
						} else {
							$content.=$comments;
						}
					} else {
						$numcomments=get_num_comments($news_detail['news_id']);
						if ($numcomments) {
							$cmdline="<p align=center><font size=-1><a href=".$PHP_SELF."?comopen=".$news_detail['news_id'].$unamelink.">Kommentare lesen ($numcomments)</a>&nbsp;|&nbsp;<a href=".$PHP_SELF."?comnew=".$news_detail['news_id'].$unamelink.">"._("Kommentar schreiben")."</a></font></p>";
						}
						$content.=$cmdline;
					}
				}

				echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" align=\"center\"><tr>";
				printcontent(0,0, $content, $edit);
				echo "</tr></table>	";
			}
	  	}
	}
	echo "</td></tr></table>";

	return TRUE;
}

function show_rss_news($id){
	$RssTimeFmt = '%Y-%m-%dT%H:%MZ';
	$smtp = new studip_smtp_class();
	$studip_url = $smtp->url . "about.php?username=".get_username($id);
	header("Content-type: text/xml; charset=utf-8");
	echo "<?xml version=\"1.0\"?>
	<rss version=\"2.0\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\">
	<channel>
	<title>Stud.IP - ".get_username($id). " rss feed</title>
	<link>$studip_url</link>
	<image>
	<url>http://www.studip.de/images/studip_logo.gif</url>
	<title>Stud.IP - ".get_username($id). " rss feed</title>
	<link>$studip_url</link>
	</image>
	<description>$RssChannelDesc</description>
	<lastBuildDate>".date("r")."</lastBuildDate>
	<generator>". utf8_encode('Stud.IP - ' . $GLOBALS['SOFTWARE_VERSION']) . "</generator>";
	foreach(StudipNews::GetNewsByRange($id, true) as  $news_id => $details) {
		echo "<item>
		<title>".utf8_encode($details["topic"])."</title>
		<link>".utf8_encode($studip_url . "&#38;nopen=$id&#35;anker")."</link>";
		list ($body,$admin_msg) = explode("<admin_msg>",$details["body"]);
		echo"<description>"."<![CDATA[".utf8_encode(formatready($body,1,1))."]]>"."</description>
		<dc:contributor>"."<![CDATA[".utf8_encode(htmlready($details['author']))."]]>"."</dc:contributor>
		<dc:date>".gmstrftime($RssTimeFmt,$details['date'])."</dc:date>
		</item>";
	}
    echo "</channel>\n</rss>";
	return TRUE;
}
?>
