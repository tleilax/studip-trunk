<?

/**
* Retrieve a WikiPage version from current seminar's WikiWikiWeb.
*
* Returns raw text data from database if requested version is
* available. If not, an
*
* @param string WikiWiki keyword to be retrieved
* @param int    Version number. If empty, latest version is returned.
*
**/
function getWikiPage($keyword, $version, $db=NULL) {
	global $SessSemName;
	if (!$db) {
		$db=new DB_Seminar();
	}
	$q = "SELECT * FROM wiki WHERE ";
	$q .= "keyword = '$keyword' AND range_id='$SessSemName[1]' ";
	if (!$version) {
		$q .= "ORDER BY version DESC";
	} else {
		$q .= "AND version='$version'";
	}
	$q .= " LIMIT 1"; // only one version needed

	$db->query($q);
	$exists=$db->next_record();
	if (!$exists) {
		if ($keyword=="WikiWikiWeb") {
			$body=_("Dieses Wiki ist noch leer. Bearbeiten Sie es!\nNeue Seiten oder Links werden einfach durch Eingeben von WikiNamen angelegt.");
			$wikidata=array("body"=>$body, "user_id"=>"nobody",  "version"=>0);
		} else {
			return NULL;
		}
	} else {
		$wikidata = $db->Record;
	}
	return $wikidata;
}

/**
* Write a new/edited wiki page to database
* 
* @param	string	keyword	WikiPage name
* @param	string	version	WikiPage version
* @param	string	body	WikiPage text
* @param	string	user_id	Internal user id of editor
* @param	string	range_id	Internal id of seminar/einrichtung
*
**/
function submitWikiPage($keyword, $version, $body, $user_id, $range_id) {

	releasePageLocks($keyword); // kill lock that was set when starting to edit
	$db=new DB_Seminar;
	// write changes to db, show new page
	$latestVersion=getWikiPage($keyword,"");
	if ($latestVersion) {
		$date=time();
		$lastchange = $date - $latestVersion[chdate];
	}
	if ($latestVersion && ($latestVersion['body'] == $body)) {
		begin_blank_table();
		parse_msg("info�" . _("Keine �nderung vorgenommen."));
		end_blank_table();
	} else if ($latestVersion && ($version!="") && ($lastchange < 30*60) && ($user_id == $latestVersion[user_id])) {
		// if same author changes again within 30 minutes,
		// no new verison is created 
		$result=$db->query("UPDATE wiki SET body='$body', chdate='$date' WHERE keyword='$keyword' AND range_id='$range_id' AND version='$version'");
		begin_blank_table();
		parse_msg("info�" . _("Update ok, keine neue Version, da erneute �nderung innerhalb 30 Minuten."));
		end_blank_table();
	} else {
		if ($version=="") {
			$version=0;
		} else {
			$version=$latestVersion['version']+1;
		}
		$date=time();
		$result=$db->query("INSERT INTO wiki (range_id, user_id, keyword, body, chdate, version) VALUES ('$range_id', '$user_id', '$keyword','$body','$date','$version')");
		begin_blank_table();
		parse_msg("info�" . _("Update ok, neue Version angelegt."));
		end_blank_table(); 
	}

	refreshBacklinks($keyword, $body);
}

/**
* Retrieve latest version for a given keyword
*
* @param	string	keyword	WikiPage name
*
**/
function getLatestVersion($keyword, $range_id) {
	$db=new DB_Seminar;
	$q = "SELECT * FROM wiki WHERE ";
	$q .= "keyword='$keyword' AND range_id='$range_id' ";
	$q .= "ORDER BY version DESC";
	$db->query($q);
	$db->next_record();
	return $db->Record;
}

/**
* Return array containing version numbes and chdates
*
* @param string		keyword	Wiki keyword for currently selected seminar
* @param string		limit	Number of links to be retrieved (default:10)
* @param string		getfirst Should first (=most recent) version e retrieved, too?
*
**/ 
function getWikiPageVersions($keyword, $limit=10, $getfirst=0) {
	global $SessSemName;
	$db = new DB_Seminar;
	$db->query("SELECT version,chdate FROM wiki WHERE keyword = '$keyword' AND range_id='$SessSemName[1]' ORDER BY version DESC LIMIT $limit");
	if ($db->affected_rows() == 0) {
		return "";
	}
	$versions=array();
	if (!$getfirst) {
		// skip first
		$db->next_record();
	}
	while ($db->next_record()) {
		$versions[]=array("version"=>$db->f("version"),
				"chdate"=>$db->f("chdate"));
	}
	return $versions;
}


/**
* Check if given keyword exists in current WikiWikiWeb.
* 
* @param	string	WikiPage keyword
*
**/
function keywordExists($str) {
	static $keywords;
	global $SessSemName;
	if (is_null($keywords)){
		$db = new DB_Seminar;
		$db->query("SELECT DISTINCT keyword FROM wiki WHERE  range_id='$SessSemName[1]' ");
		while($db->next_record()){
			$keywords[$db->f(0)] = true;
		}
	}
	return $keywords[$str];
}


/**
* Check if keyword already exists or links to new page. 
* Returns HTML-Link-Representation.
* 
* @param	string	WikiPage keyword
*
**/
function isKeyword($str, $page){
	if (keywordExists($str) == NULL) {
		return ' <a href="wiki.php?keyword='.$str.'&view=editnew&lastpage='.$page.'">'.$str.'(?)</a>';
	} else {
		return ' <a href="wiki.php?keyword='.$str.'">'.$str.'</a>';
	}
}


/**
* Get lock information about page
* Returns displayable string containing lock information 
* (Template: Username1 (seit x Minuten), Username2 (seit y Minuten), ...)
* or NULL if no locks set.
*
* @param	string	WikiPage keyword
*
**/
function getLock($keyword) {
	global $SessSemName, $user_id;
	$db=new DB_seminar;
	$result=$db->query("SELECT user_id, chdate FROM wiki_locks WHERE range_id='$SessSemName[1]' AND keyword='$keyword' AND user_id != '$user_id' ORDER BY chdate DESC"); 

	$lockstring="";
	$count=0;
	$num=$db->nf();
	while ($db->next_record()) {
		if ($count>0 && $count==$num-1) {
			$lockstring .= _(" und ");
		} else if ($count>0) {
			$lockstring .= ", ";
		}
		$lockstring .= get_fullname($db->f("user_id"));
		$lockstring .= sprintf(_(" (seit %d Minuten)"), ceil((time()-$db->f("chdate"))/60));
		$count++;
	}
	return $lockstring;
}

/**
* Set lock for current user and current page
*
* @param	DB_Seminar	db	DB_Seminar instance
* @param	string		user_id	Internal user id
* @param	string		range_if	Internal seminar id
* @param	string		keyword	WikiPage name
*
**/
function setWikiLock($db, $user_id, $range_id, $keyword) {
	$db->query("REPLACE INTO wiki_locks (user_id, range_id, keyword, chdate) VALUES ('$user_id', '$range_id', '$keyword', '".time()."')");
}


/**
* Release all locks for wiki page that are older than 30 minutes.
*
* @param	string	WikiPage keyword
*
**/
function releaseLocks($keyword) {
	global $SessSemName;
	$db=new DB_seminar;
	$db2=new DB_Seminar;
	$db->query("SELECT * FROM wiki_locks WHERE range_id='$SessSemName[1]' AND keyword='$keyword'");
	while ($db->next_record()) {
		if ((time() - $db->f("chdate")) > (30*60)) {
			$q="DELETE FROM wiki_locks WHERE range_id='".$db->f("range_id")."' AND keyword='".$db->f("keyword")."' AND chdate='".$db->f("chdate")."'";
			$db2->query($q);
		}
	}
}

/**
* Release locks for current wiki page and current user
*
* @param	string	keyword	WikiPage name
*
**/
function releasePageLocks($keyword) {
	global $SessSemName, $user_id;
	$db=new DB_seminar;
	$db->query("DELETE FROM wiki_locks WHERE range_id='$SessSemName[1]' AND keyword='$keyword' AND user_id='$user_id'");
}


// wiki regex pattern
// IMPORTANT: Wiki Keyword has to be in 2nd paranthesed pattern!!
// change routines below if this changes
//
$wiki_keyword_regex="(^|\s|\A|\>)([A-Z][a-z0-9]+[A-Z][a-zA-Z0-9]+)";

/**
* Replace WikiWords with appropriate links in given string
*
* @param	string 	str	
* @param 	string	page
*
**/
function wikiLinks($str, $page) { 
	global $wiki_keyword_regex;
	// regex adapted from RoboWiki
	// added > as possible start of WikiWord
	// because htmlFormat converts newlines to <br>
	return preg_replace("/$wiki_keyword_regex/e", "'\\1'.isKeyword('\\2', $page)", $str); 
}

/**
* Return list of WikiWord in given page body ($str)
*
* @param	string 	str	
*
**/
function getWikiLinks($str) {
	global $wiki_keyword_regex;
	preg_match_all("/$wiki_keyword_regex/", $str, $out, PREG_PATTERN_ORDER);
	return $out[2];
}


/**
* Return list of WikiPages containing links to given page
*
* @param	string 	str	
*
**/
function getBacklinks($keyword) {
	global $SessSemName;
	$db=new DB_seminar();
	$q="SELECT DISTINCT from_keyword FROM wiki_links WHERE range_id='$SessSemName[1]' AND to_keyword='$keyword'";
	$db->query($q);
	$backlinks=array();
	while ($db->next_record()) {
		$backlinks[]=$db->f("from_keyword");
	}

	return $backlinks;
} 

/**
* Refresh wiki_links table for backlinks from given page to
* other pages
*
* @param	string 	keyword	WikiPage-name for $str content 
* @param	string	str	Page content containing links
*
**/
function refreshBacklinks($keyword, $str) {
	global $SessSemName;
	// insert links from page to db
	// logic: all links are added, also links to nonexistant pages
	// (these will change when submitting other pages)
	$db_wiki_list=new DB_seminar();
	// first delete all links
	$q="DELETE FROM wiki_links WHERE range_id='$SessSemName[1]' AND from_keyword='$keyword'";
	$db_wiki_list->query($q);
	// then reinsert those (still) existing
	$wikiLinkList=getWikiLinks($str);
	if (!empty($wikiLinkList)) {
		foreach ($wikiLinkList as $key => $value) {
			$q="INSERT INTO wiki_links (range_id, from_keyword, to_keyword) VALUES ('$SessSemName[1]', '$keyword', '$value')";
			$db_wiki_list->query($q);
		}
	}
	$db_wiki_list->free();
}

/**
* Generate Meta-Information on Wiki-Page to display in top line
*
* @param	db-query result		all information about a wikiPage
* @return 	string	Displayable HTML
*
**/
function getZusatz($wikiData) {
	if (!$wikiData || $wikiData["version"] <= 0) {
		return "";
	}
	$s = "<font size=-1>";
	$s .=  _("Version ") . $wikiData[version];
	$s .= sprintf(_(", ge&auml;ndert von %s am %s"), "</font><a href=\"about.php?username=".get_username ($wikiData[user_id])."\"><font size=-1 color=\"#333399\">".get_fullname ($wikiData[user_id])."</font></a><font size=-1>", date("d.m.Y, H:i",$wikiData[chdate])."<font size=-1>&nbsp;"."</font>");
	return $s;
}

/**
* Display yes/no dialog to confirm WikiPage version deletion.
*
* @param	string	WikiPage name
* @param	string	WikiPage version (if empty: take latest)
*
* @return	string	Version number to delete
*
**/
function showDeleteDialog($keyword, $version) {
	global $perm, $SessSemName;
	if (!$perm->have_perm("dozent")) {
		begin_blank_table();
		parse_msg("error�" . _("Sie haben keine Berechtigung, Seiten zu l&ouml;schen."));
		end_blank_table();
		echo "</td></tr></table></body></html>";
		die;
	}
	$islatest=0; // will another version become latest version?
	$willvanish=0; // will the page be deleted entirely?
	if ($version=="latest") {
		$lv=getLatestVersion($keyword, $SessSemName[1]);
		$version=$lv["version"];
		if ($version==1) {
			$willvanish=1;
		}
		$islatest=1;
	}
	begin_blank_table();
	$msg="info�" . sprintf(_("Wollen Sie die untenstehende Version %s der Seite %s wirklich l&ouml;schen?"), "<b>".$version."</b>", "<b>".$keyword."</b>") . "<br>\n";
	if ($islatest && !$willvanish) {
		$msg .= _("Diese Version ist derzeit aktuell. Nach dem L&ouml;schen wird die n&auml;chst&auml;ltere Version aktuell.") . "<br>";
	} elseif ($islatest && $willvanish) {
		$msg .= _("Diese Version ist die derzeit einzige. Nach dem L&ouml;schen ist die Seite komplet gel�scht.") . "<br>";
	} else {
		$msg .= _("Diese Version ist nicht aktuell. Das L&ouml;schen wirkt sich daher nicht auf die aktuelle Version aus.") . "<br>";
	}    
	$msg.="<a href=\"".$PHP_SELF."?cmd=really_delete&keyword=$keyword&version=$version&dellatest=$islatest\">" . makeButton("ja2", "img") . "</a>&nbsp; \n";
	$lnk = "?keyword=$keyword"; // what to do when delete is aborted
	if (!$islatest) $lnk .= "&version=$version"; 
	$msg.="<a href=\"".$PHP_SELF."$lnk\">" . makeButton("nein", "img") . "</a>\n";
	parse_msg($msg, '�', 'blank', '1', FALSE);
	end_blank_table();
	return $version;
}

/**
* Delete WikiPage version and adjust backlinks.
*
* @param	string	WikiPage name
* @param	string	WikiPage version
* @param	string  ID of seminar/einrichtung
*
* @return	string	WikiPage name to display next
*
**/
function deleteWikiPage($keyword, $version, $range_id) {
	global $perm, $SessSemName;
	if (!$perm->have_perm("dozent")) {
		begin_blank_table();
		parse_msg("error�" . _("Sie haben keine Berechtigung, Seiten zu l&ouml;schen."));
		end_blank_table();
		echo "</td></tr></table></body></html>";
		die;
	}
	$q="DELETE FROM wiki WHERE keyword='$keyword' AND version='$version' AND range_id='$range_id'";
	$db=new DB_Seminar;
	$db->query($q);
	if (!keywordExists($keyword)) { // all versions have gone
		$addmsg = sprintf(_("<br>Damit ist die Seite mit allen Versionen gel&ouml;scht."));
		$newkeyword = "WikiWikiWeb";
	} else {
		$newkeyword = $keyword;
		$addmsg = "";
	}
	begin_blank_table();
	parse_msg("info�" . sprintf(_("Version %s der Seite <b>%s</b> gel&ouml;scht."), $version, $keyword) . $addmsg);
	end_blank_table();
	if ($dellatest) {
		$lv=getLatestVersion($keyword, $SessSemName[1]);
		if ($lv) {
			$body="";
		} else {
			$body=$lv["body"];
		}
		refreshBacklinks($keyword, $body);
	}
	return $newkeyword;
}

/**
* List all topics in this seminar's wiki
*
* @param  mode  string  Either "all" or "new", affects default sorting and page title.
* @param  sortby  string  Different sortings of entries.
**/
function listPages($mode, $sortby=NULL) {
	global $SessSemName, $user_id, $loginfilelast;

	$db=new DB_Seminar;
	$db2=new DB_Seminar;

	if ($mode=="all") {
		$selfurl = "wiki.php?view=listall";
		$sort = "ORDER by keyword"; // default sort order for "all pages"
		$nopages = _("In dieser Veranstaltung wurden noch keine WikiSeiten angelegt.");
	} else if ($mode=="new") {
		$selfurl = "wiki.php?view=listnew";
		$sort = "ORDER by lastchange"; // default sort order for "new pages"
		$nopages = _("Seit Ihrem letzten Login gab es keine �nderungen.");
	} else {
		parse_msg("info�" . _("ERROR: Falscher Anzeigemodus:") . $mode);
		return 0;
	}  

	$titlesortlink = "title";
	$versionsortlink = "version";
	$changesortlink = "lastchange";

	switch ($sortby) {
		case 'title':
			// sort by keyword, prepare link for descending sorting
			$sort = " ORDER BY keyword";
			$titlesortlink = "titledesc";
			break;
		case 'titledesc':
			// sort descending by keyword, prep link for asc. sort
			$sort = " ORDER BY keyword DESC";
			break;
		case 'version':
			$sort = " ORDER BY lastversion DESC";
			$versionsortlink = "versiondesc";
			break;
		case 'versiondesc':
			$sort = " ORDER BY lastversion";
			break;
		case 'lastchange':
			// sort by change date, default: newest first
			$sort = " ORDER BY lastchange DESC"; 
			$changesortlink = "lastchangedesc";
			break;
		case 'lastchangedesc':
			// sort by change date, oldest first
			$sort = " ORDER BY lastchange"; 
			break;
	}

	if ($mode=="all") {
		$q="SELECT keyword, MAX(chdate) AS lastchange, MAX(version) AS lastversion FROM wiki WHERE range_id='$SessSemName[1]' GROUP BY keyword " . $sort;
	} else if ($mode=="new") {
		$lastlogindate = $loginfilelast[$SessSemName[1]];
		$q="SELECT keyword, MAX(chdate) AS lastchange, MAX(version) AS lastversion FROM wiki WHERE range_id='$SessSemName[1]' AND chdate > '$lastlogindate' GROUP BY keyword " . $sort;
	}
	$result=$db->query($q);

	// quit if no pages found
	if ($db->affected_rows() == 0) {
		begin_blank_table();
		parse_msg ("info\xa7" . $nopages);
		echo "</table></td></tr></table></body></html>";
		die;
	}

	// show pages
	begin_blank_table();
	echo "<tr><td class=\"blank\" colspan=\"2\">&nbsp;</td></tr>\n";
	echo "<tr><td class=\"blank\" colspan=\"2\">";
	echo "<table width=\"99%\" border=\"0\"  cellpadding=\"2\" cellspacing=\"0\" align=\"center\">";
	echo "<tr height=28>";
	$s = "<td class=\"steel\" width=\"%d%%\" align=\"%s\"><img src=\"pictures/blank.gif\" width=\"1\" height=\"20\">%s</td>";
	printf($s, 3, "left", "&nbsp;");
	printf($s, 39,"left",  "<font size=-1><b><a href=\"$selfurl&sortby=$titlesortlink\">"._("Titel")."</a></b></font>");
	printf($s, 10,"center",  "<font size=-1><b><a href=\"$selfurl&sortby=$versionsortlink\">"._("�nderungen")."</a></b></font>");
	printf($s, 15,"left",  "<font size=-1><b><a href=\"$selfurl&sortby=$changesortlink\">"._("Letzte �nderung")."</a></b></font>");
	printf($s, 25,"left",  "<font size=-1><b>"._("von")."</b></font>");
	echo "</tr>";

	$c=1;
	while ($db->next_record()) {

		$class = ($c++ % 2) ? "steel1" : "steelgraulight";

		$keyword=$db->f("keyword");
		$lastchange=$db->f("lastchange");
		$db2->query("SELECT user_id, version FROM wiki WHERE range_id='$SessSemName[1]' AND keyword='$keyword' AND chdate='$lastchange'");
		$db2->next_record();
		$userid=$db2->f("user_id");
	    
		$tdheadleft="<td class=\"$class\" align=\"left\"><font size=\"-1\">";
		$tdheadcenter="<td class=\"$class\" align=\"center\"><font size=\"-1\">";
		$tdtail="</font></td>";
		print("<tr>".$tdheadleft."&nbsp;"."$tdtail");
		printf($tdheadleft."<a href = wiki.php?keyword=" . $keyword . ">");
		print(htmlReady($keyword) ."</a>");
		print($tdtail);
		print($tdheadcenter.$db2->f("version").$tdtail);
		print($tdheadleft.date("d.m.Y, H:i", $lastchange));
		if ($mode=="new" && $db2->f("version")>1) {
			print("&nbsp;(<a href=\"wiki.php?view=diff&keyword=$keyword&versionssince=$lastlogindate\">Diff</a>)");
		}
		print($tdtail);
		print($tdheadleft.get_fullname($userid).$tdtail."</tr>");
	}
	echo "</table><p>&nbsp;</p>";
	end_blank_table();
}


/**
* Print a wiki header (css class: topic) including seminar name.
*
**/
function wikiSeminarHeader() {
	global $SessSemName;
	echo "\n<table width=\"100%\" class=\"blank\" border=0 cellpadding=0 cellspacing=0>\n";
	echo "<tr>";
	echo "<td class=\"topic\" width=\"100%\">";
	echo "<b>&nbsp;<img src=\"pictures/icon-wiki.gif\" align=absmiddle>&nbsp; ". $SessSemName["header_line"] ." - " .  _("Wiki") . "</b></td>";
	//echo "<td class=\"topic\" width=\"5%\" align=\"right\">";
	//echo "<a href =\"wiki.php?cmd=anpassen\">";
	//echo "<img src=\"pictures/pfeillink.gif\" border=0 " . tooltip(_("Look & Feel anpassen")) . ">&nbsp;</a></td></tr>\n";
	echo "</tr>";
	echo "<tr><td class=\"blank\" colspan=2>&nbsp; </td></tr>\n";
	echo "</table>";
}

/**
* Print a wiki page header including printhead-bar with page name and 
* last change info. 
*
**/
function wikiSinglePageHeader($wikiData, $keyword) {
	$zusatz=getZusatz($wikiData);

	begin_blank_table();
	printhead(0, 0, FALSE, "icon-wiki", FALSE, "", "<b>$keyword</b>", $zusatz);
	end_blank_table(); 
}

/**
* Display edit form for wiki page.
* 
* @param	string	keyword	WikiPage name
* @param	array	wikiData	Array from DB with WikiPage data	
* @param	string	user_id		Internal user id
* @param	string	backpage	Page to display if editing is aborted
*
**/
function wikiEdit($keyword, $wikiData, $user_id, $backpage=NULL) {

	if (!$wikiData) {
		$body = "";
		$version = 0;
		$lastpage="&lastpage=$backpage";
	} else {
		$body = $wikiData["body"];
		$version = $wikiData["version"];
		$lastpage = "";
	}
	releaseLocks($keyword); // kill old locks 
	$locks=getLock($keyword);
	if ($locks && $lock["user_id"]!=$user_id) { 
		begin_blank_table();
		echo "<tr><td class=blank>&nbsp;</td></tr>";
		parse_msg("info�" . _("Die Seite wird eventuell bearbeitet von ") . $locks . ".<br>" . _("Wenn Sie die Seite trotzdem &auml;ndern, kann ein Versionskonflikt entstehen.") . "<br>" . _("Es werden dann beide Versionen eingetragen und m&uuml;ssen von Hand zusammengef&uuml;hrt werden.") . "<br>" . _("Klicken Sie auf Abbrechen, um zur�ckzukehren."));
		end_blank_table();
	}

	echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">";
	echo "<tr><td>";
	$cont = "<font size=-2><p>" . _("Sie k&ouml;nnen beliebigen Text einf&uuml;gen und vorhandenen Text &auml;ndern.") . " ";
	$cont .= _("Beachten Sie dabei die "). "<a href=\"help/index.php?help_page=ix_forum6.htm\" target=\"_new\">" . _("Formatierungsm&ouml;glichkeiten") . "</a>.<br>";
	$cont .= _("Links entstehen automatisch aus W&ouml;rtern, die mit Gro&szlig;buchstaben beginnen und einen Gro&szlig;buchstaben in der Wortmitte enthalten.") . "</p></font>";
    
	$cont .= "<p><form method=\"post\" action=\"?keyword=$keyword&cmd=edit\">";
	$cont .= "<textarea name=\"body\" cols=\"80\" rows=\"15\">$body</textarea>\n";
	$cont .= "<input type=\"hidden\" name=\"wiki\" value=\"$keyword\">";
	$cont .= "<input type=\"hidden\" name=\"version\" value=\"$version\">";
	$cont .= "<input type=\"hidden\" name=\"submit\" value=\"true\">";
	$cont .= "<input type=\"hidden\" name=\"cmd\" value=\"show\">";
	$cont .= "<br><br><input type=image name=\"submit\" value=\"abschicken\" " . makeButton("abschicken", "src") . " align=\"absmiddle\" border=0 >&nbsp;<a href=\"wiki.php?cmd=abortedit&keyword=$keyword$lastpage\"><img " . makeButton("abbrechen", "src") . " align=\"absmiddle\" border=0></a>";
	$cont .= "</form>\n";
	printcontent(0,0,$cont,"");
	echo "</tr></table>     ";
	echo "</td></tr></table>";
}

/**
* Display wiki page for print.
* 
* @param	string	keyword	WikiPage name
* @param	string	version	WikiPage version
*
**/
function printWikiPage($keyword, $version) {
	global $SessSemName;
	$wikiData=getWikiPage($keyword,$version);
	echo "<html><head><title>$keyword</title></head>";
	echo "<body>";
	echo "<p><em>$SessSemName[header_line]</em></p>";
	echo "<h1>$keyword</h1>";
	echo "<p><em>Version ".$wikiData['version'];
	echo ", letzte �nderung ".date("d.m.Y, h:i", $wikiData['chdate']);
	echo " von ".get_fullname($wikiData['user_id']).".</em></p>";
	echo "<hr>";
	echo wikiReady($wikiData['body']);
	echo "<hr><p><font size=-1>created by Stud.IP Wiki-Module ";
	echo date("d.m.Y, h:i", time());
	echo " </font></p>";
	echo "</body></html>";
}

/**
* Show export dialog
*
**/
function exportWiki() {
	showPageFrameStart();
	begin_blank_table();
	parse_msg("info�Alle Wiki-Seiten werden als gro�e HTML-Datei zusammengf�gt und in einem neuen Fenster angezeigt. Von dort aus k�nnen Sie die Datei abspeichern.");
	$infobox = array ();
	$infobox[] = array("kategorie" => _("Information"), "eintrag" => array(array("icon"=>"pictures/ausruf_small.gif", "text"=>_("Die Wiki-Seiten werden als eine zusammenh�ngende HTML-Datei ohne Links exportiert."))));
	print "</tr><tr align=center><td>";
	print "<a href=\"wiki.php?view=wikiprintall\" target=\"_new\"><img ".makebutton("weiter","src"). " border=0></a></td></tr>";
	end_blank_table();
	echo "</td>"; // end of content area
	showPageFrameEnd($infobox);
}

/**
* HTML-dump all wiki pages.
* Implements an iterative breadth-first traversal of WikiPage-tree. 
* 
* @param	string	ID of veranstaltung/einrichtung
* @param	string	Short title (header) of veranstaltung/einrichtung
*
**/
function printAllWikiPages($range_id, $header) {
	$visited=array(); // holds names of already visited pages
	$tovisit=array(); // holds names of pages yetto visit/expand
	$tovisit[]="WikiWikiWeb"; // start with top level page
	echo "<html><head><title>$header</title></head>";
	echo "<body>";
	echo "<p><em>$header</em></p>";
	while (! empty($tovisit)) { // while there are still pages left to visit
		$pagename=array_shift($tovisit);
		$pagedata=getLatestVersion($pagename, $range_id);
		if ($pagedata) { // consider only pages with content
			array_push($visited, $pagename);
			$linklist=getWikiLinks($pagedata["body"]);
			foreach ($linklist as $l) {
				// add pages not visited yet to queue
				if (! in_array($l, $visited)) {
					$tovisit[] = $l; // breadth-first
				}
			}
			echo "<hr><h1>$pagename</h1>";
			echo "<p><em>Version ".$pagedata['version'];
			echo ", letzte �nderung ".date("d.m.Y, h:i", $pagedata['chdate']);
			echo " von ".get_fullname($pagedata['user_id']).".</em></p>";
			// output is html without WikiLinks
			echo wikiReady($pagedata['body']);
		}
	}
	echo "<hr><p><font size=-1>created by Stud.IP Wiki-Module ";
	echo date("d.m.Y, h:i", time());
	echo " </font></p>";
	echo "</body></html>";
}

/**
* Display start of page "frame", i.e. open correct table structure.
*
**/
function showPageFrameStart() {
	print "<table width=\"100%\" class=\"blank\" cellpadding=0 cellspacing=0>";
	print "<tr class=\"blank\"><td class=\"blank\" nowrap width=\"1%\">&nbsp;</td><td class=\"blank\" valign=\"top\">";
}

/**
* Display the right and bottom part of a page "frame".
*
* Renders an infobox and closes the table. 
*
* @param	array	ready to pass to print_infoxbox()
*
**/
function showPageFrameEnd($infobox) {
	// start of infobox area
	echo "<td class=\"blank\" width=\"270\" align=\"right\" valign=\"top\">";
	print_infobox ($infobox,"pictures/details.jpg");
	echo "</td></tr><tr><td colspan=3 class=\"blank\">&nbsp;</td></tr>";
	echo "</table>"; // end infoframe (content+box)
	echo "</td></tr></table>"; // end page box
}

/**
* Returns an infobox string holding information and action links for
* current page.
* If newest version is displayed, infobox includes backlinks.
*
* @param	string	WikiPage name
* @param	bool	Is version displayed latest version?
*
**/
function getShowPageInfobox($keyword, $latest_version) {

	$versions=getWikiPageVersions($keyword);
	$versiontext="<a href=\"wiki.php?keyword=".$keyword."\">Aktuelle Version</a><br>";
	if ($version) {
		foreach ($versions as $v) {
			$versiontext .= "<a href=\"wiki.php?keyword=$keyword&version=".$v['version']."\">"._("Version")." ".$v['version']."</a> - ".date("d.m.Y, H:i",$v['chdate'])."<br>";
		}
	}
	if (!$versiontext) {
		$versiontext=_("Keine alten Versionen.");
	}

	$viewtext="<a href=\"wiki.php?keyword=".$keyword."&view=show\">Standard</a><br>";
	$viewtext .= "<a href=\"wiki.php?keyword=".$keyword."&view=wikiprint&version=$version\" target=\"_new\">Druckansicht</a><br>";
	$viewtext .= "<a href=\"wiki.php?keyword=".$keyword."&cmd=showdiff&view=diff\">�nderungen am Text verfolgen</a>";
	$views=array(array("icon" => "pictures/blank.gif", "text" => $viewtext));

	$backlinktext="";
	$first=1;
	$backlinks=getBacklinks($keyword);
	foreach($backlinks as $b) {
		if (!$first) {
			$backlinktext .= "<br>";
		} else {
			$first=0;
		}
		$backlinktext .= "<a href=\"wiki.php?keyword=$b\">$b</a>";
	}
	if (empty($backlinktext)) {
		$backlinktext = _("Keine Verweise vorhanden.");
	}
	$backlinktext= array(array("icon" => "pictures/icon-leer.gif", "text" =>
 $backlinktext));
	$infobox = array ();
	if (!$latest_version) {
		$infobox[] = array("kategorie" => _("Information"), "eintrag" => array(array("icon"=>"pictures/ausruf_small.gif", "text"=>_("Sie betrachten eine alte Version, die nicht mehr ge�ndert werden kann. Verwenden Sie dazu die <a href=\"wiki.php?keyword=$keyword\">aktuelle Version</a>."))));
	}
	$infobox[] = array("kategorie"  => _("Ansicht:"), "eintrag" => $views);
	if ($latest_version) { 
		// no backlinks for old versions!
		$infobox[] = array("kategorie" => _("Seiten, die auf diese Seite verweisen:"), "eintrag" => $backlinktext);
	}
	$infobox[] = array("kategorie" => _("Alte Versionen dieser Seite:"),
			"eintrag" => array(array("icon"=>"pictures/blank.gif","text"=>$versiontext)));
	return $infobox;
}

/**
* Display wiki page.
* 
* @param	string	keyword	WikiPage name
* @param	string	version	WikiPage version
*
**/
function showWikiPage($keyword, $version) {
	global $perm;
	$wikiData = getWikiPage($keyword, $version);
	if (!$version) {
		$latest_version=1;
	}

	showPageFrameStart();
	// show page logic
	//
	wikiSinglePageHeader($wikiData, $keyword);

	if ($perm->have_perm("autor")) { 
		if (!$latest_version) {
			if ($perm->have_perm("dozent")) {
				$edit="<a href=\"?keyword=$keyword&cmd=delete&version=$version\"><img ".makeButton("loeschen","src")." border=\"0\"></a>";
			} else {
				$edit="�ltere Version, nicht bearbeitbar!";
			}
		} else {
			$edit="";
			if ($perm->have_perm("autor")) {
				$edit.="<a href=\"?keyword=$keyword&view=edit\"><img ".makeButton("bearbeiten","src")." border=\"0\"></a>";
			}
			if ($perm->have_perm("dozent")) {
				$edit.="&nbsp;<a href=\"?keyword=$keyword&cmd=delete&version=latest\"><img ".makeButton("loeschen","src")." border=\"0\"></a>";
			}
		}
		$edit .= "<br>&nbsp;";
	} else {
		$edit="";
	}

	begin_blank_table();
	echo "<tr class=\"printcontent\">";
	echo "<td class=\"printcontent\" width=\"22\">&nbsp;&nbsp;</td>";
	echo "<td class=\"printcontent\" align=\"center\">&nbsp;<br>\n";
	echo $edit;
	end_blank_table(); 

	begin_blank_table();
	echo "<tr>\n";
	$cont = wikiLinks(wikiReady($wikiData["body"]), $keyword);
	$num_body_lines=substr_count($wikiData['body'], "\n");
	if ($num_body_lines<15) {
		$cont .= "<p>";
		$cont .= str_repeat("&nbsp<br>", 15-$num_body_lines);
	}
	printcontent(0,0, $cont, $edit);
	end_blank_table(); 

	echo "</td>"; // end content area
	//
	// end showpage logic

	$infobox=getShowPageInfobox($keyword, $latest_version);
	showPageFrameEnd($infobox);
}

/**
* Helper function that prints header for a "blank" table
*
**/
function begin_blank_table() {
	echo "<table width=\"100%\" class=\"blank\" border=0 cellpadding=0 cellspacing=0>\n";
}

/**
* Helper function that prints footer for a "blank" table
*
**/
function end_blank_table() {
	echo "</tr></table>";
}

/**
* Display Page diffs, restrictable to recent versions
*
* @param	string	WikiPage name
* @param	string	Only show versions never than this timestamp
*
**/
function showDiffs($keyword, $versions_since) {
	global $SessSemName;
	$db = new DB_Seminar;
	$q = "SELECT * FROM wiki WHERE ";
	$q .= "keyword = '$keyword' AND range_id='$SessSemName[1]' ";
	$q .= "ORDER BY version DESC";
	$result = $db->query($q);
	if ($db->affected_rows() == 0) {
		begin_blank_table();
		parse_msg ("info\xa7" . _("Es gibt keine zu vergleichenden Versionen."));
		end_blank_table();
		echo "</td></tr></table></body></html>";
		die;
	}

	showPageFrameStart();
	wikiSinglePageHeader($wikiData, $keyword);

	echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">";
	$db->next_record();
	$last = $db->f("body");
	$lastversion = $db->f("version");
	$zusatz=getZusatz($db->Record);
	while ($db->next_record()) {
		echo "<tr>";
		$current = $db->f("body");
		$currentversion = $db->f("version");
		$diffarray = "<b><font size=-1>�nderungen zu </font> $zusatz</b><p>";
		$diffarray .= "<table cellpadding=0 cellspacing=0 border=0 width=\"100%\">\n";
		$diffarray .= do_diff($current, $last);
		$diffarray .= "</table>\n";
		printcontent(0,0, $diffarray, "");
		echo "</tr>";
		$last = $current;
		$lastversion = $currentversion;
		$zusatz=getZusatz($db->Record);
		if ($versions_since && $db->f("chdate") < $versions_since) {
			break;
		}
	}
	echo "</table>     ";
	showPageFrameEnd($keyword, 1);
}
   
/////////////////////////////////////////////////
// DIFF funcitons adapted from:
// PukiWiki - Yet another WikiWikiWeb clone.
// http://www.pukiwiki.org (GPL'd)
//
//
// 
function do_diff($strlines1,$strlines2)
{
	$plus="<td width=\"3\" bgcolor=\"green\">&nbsp;</td>";
	$minus="<td width=\"3\" bgcolor=\"red\">&nbsp;</td>";
	$equal="<td width=\"3\" bgcolor=\"grey\">&nbsp;</td>";
	$obj = new line_diff($plus, $minus, $equal);
	$str = $obj->str_compare($strlines1,$strlines2);
	return $str;
}

/*
line_diff

S. Wu, <A HREF="http://www.cs.arizona.edu/people/gene/vita.html">
E. Myers,</A> U. Manber, and W. Miller,
<A HREF="http://www.cs.arizona.edu/people/gene/PAPERS/np_diff.ps">
"An O(NP) Sequence Comparison Algorithm,"</A>
Information Processing Letters 35, 6 (1990), 317-323.
*/

class line_diff
{
	var $arr1,$arr2,$m,$n,$pos,$key,$plus,$minus,$equal,$reverse;
	
	function line_diff($plus='+',$minus='-',$equal='=')
	{
		$this->plus = $plus;
		$this->minus = $minus;
		$this->equal = $equal;
	}
	function arr_compare($key,$arr1,$arr2)
	{
		$this->key = $key;
		$this->arr1 = $arr1;
		$this->arr2 = $arr2;
		$this->compare();
		$arr = $this->toArray();
		return $arr;
	}
	function set_str($key,$str1,$str2)
	{
		$this->key = $key;
		$this->arr1 = array();
		$this->arr2 = array();
		$str1 = preg_replace("/\r/",'',$str1);
		$str2 = preg_replace("/\r/",'',$str2);
		foreach (explode("\n",$str1) as $line)
		{
			$this->arr1[] = new DiffLine($line);
		}
		foreach (explode("\n",$str2) as $line)
		{
			$this->arr2[] = new DiffLine($line);
		}
	}
	function str_compare($str1,$str2)
	{
		$this->set_str('diff',$str1,$str2);
		$this->compare();
		
		$str = '';
		foreach ($this->toArray() as $obj)
		{
			$str .= "<tr>".$obj->get('diff')."<td width=\"10\">&nbsp;</td><td>".$obj->text()."</td></tr>";
		}
		return $str;
	}
	function compare()
	{
		$this->m = count($this->arr1);
		$this->n = count($this->arr2);
		
		if ($this->m == 0 or $this->n == 0) // no need compare.
		{
			$this->result = array(array('x'=>0,'y'=>0));
			return;
		}
		
		// sentinel
		array_unshift($this->arr1,new DiffLine(''));
		$this->m++;
		array_unshift($this->arr2,new DiffLine(''));
		$this->n++;
		
		$this->reverse = ($this->n < $this->m);
		if ($this->reverse) // swap
		{
			$tmp = $this->m; $this->m = $this->n; $this->n = $tmp;
			$tmp = $this->arr1; $this->arr1 = $this->arr2; $this->arr2 = $tmp;
			unset($tmp);
		}
		
		$delta = $this->n - $this->m; // must be >=0;
		
		$fp = array();
		$this->path = array();
		
		for ($p = -($this->m + 1); $p <= ($this->n + 1); $p++)
		{
			$fp[$p] = -1;
			$this->path[$p] = array();
		}
		
		for ($p = 0;; $p++)
		{
			for ($k = -$p; $k <= $delta - 1; $k++)
			{
				$fp[$k] = $this->snake($k, $fp[$k - 1], $fp[$k + 1]);
			}
			for ($k = $delta + $p; $k >= $delta + 1; $k--)
			{
				$fp[$k] = $this->snake($k, $fp[$k - 1], $fp[$k + 1]);
			}
			$fp[$delta] = $this->snake($delta, $fp[$delta - 1], $fp[$delta + 1]);
			if ($fp[$delta] >= $this->n)
			{
				$this->pos = $this->path[$delta]; // 
				return;
			}
		}
	}
	function snake($k, $y1, $y2)
	{
		if ($y1 >= $y2)
		{
			$_k = $k - 1;
			$y = $y1 + 1;
		}
		else
		{
			$_k = $k + 1;
			$y = $y2;
		}
		$this->path[$k] = $this->path[$_k];// 
		$x = $y - $k;
		while ((($x + 1) < $this->m) and (($y + 1) < $this->n)
			and $this->arr1[$x + 1]->compare($this->arr2[$y + 1]))
		{
			$x++; $y++;
			$this->path[$k][] = array('x'=>$x,'y'=>$y); // 
		}
		return $y;
	}
	function toArray()
	{
		$arr = array();
		if ($this->reverse) //
		{
			$_x = 'y'; $_y = 'x'; $_m = $this->n; $arr1 =& $this->arr2; $arr2 =& $this->arr1;
		}
		else
		{
			$_x = 'x'; $_y = 'y'; $_m = $this->m; $arr1 =& $this->arr1; $arr2 =& $this->arr2;
		}
		
		$x = $y = 1;
		$this->add_count = $this->delete_count = 0;
		$this->pos[] = array('x'=>$this->m,'y'=>$this->n); // sentinel
		foreach ($this->pos as $pos)
		{
			$this->delete_count += ($pos[$_x] - $x);
			$this->add_count += ($pos[$_y] - $y);
			
			while ($pos[$_x] > $x)
			{
				$arr1[$x]->set($this->key,$this->minus);
				$arr[] = $arr1[$x++];
			}
			
			while ($pos[$_y] > $y)
			{
				$arr2[$y]->set($this->key,$this->plus);
				$arr[] =  $arr2[$y++];
			}
			
			if ($x < $_m)
			{
				$arr1[$x]->merge($arr2[$y]);
				$arr1[$x]->set($this->key,$this->equal);
				// $arr[] = $arr1[$x];
			}
			$x++; $y++;
		}
		return $arr;
	}
}

class DiffLine
{
	var $text;
	var $status;
	
	function DiffLine($text)
	{
		$this->text = "$text\n";
		$this->status = array();
	}
	function compare($obj)
	{
		return $this->text == $obj->text;
	}
	function set($key,$status)
	{
		$this->status[$key] = $status;
	}
	function get($key)
	{
		return array_key_exists($key,$this->status) ? $this->status[$key] : '';
	}
	function merge($obj)
	{
		$this->status += $obj->status;
	}
	function text()
	{
		return $this->text;
	}
}

?>

