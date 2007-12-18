<?

// $Id$

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
* Fill in username in comments
*
* @param	string	body	WikiPage text
*
**/
function completeWikiComments($body) {
	global $auth;
	return preg_replace("/\[comment\]/","\[comment=".addslashes(get_fullname($auth->auth['uid'],'full',false))."\]",$body);
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
	$message="";
	$db=new DB_Seminar;
	// write changes to db, show new page
	$latestVersion=getWikiPage($keyword,"");
	if ($latestVersion) {
		$date=time();
		$lastchange = $date - $latestVersion[chdate];
	}

	// complete username in comments
	$body=completeWikiComments($body);

	if ($latestVersion && ($latestVersion['body'] == $body)) {
		$message="info�" . _("Keine �nderung vorgenommen.");
	} else if ($latestVersion && ($version!="") && ($lastchange < 30*60) && ($user_id == $latestVersion[user_id])) {
		// if same author changes again within 30 minutes,
		// no new verison is created
		$result=$db->query("UPDATE wiki SET body='$body', chdate='$date' WHERE keyword='$keyword' AND range_id='$range_id' AND version='$version'");
		begin_blank_table();
		$message="info�" . _("Update ok, keine neue Version, da erneute �nderung innerhalb 30 Minuten.");
	} else {
		if ($version=="") {
			$version=0;
		} else {
			$version=$latestVersion['version']+1;
		}
		$date=time();
		$result=$db->query("INSERT INTO wiki (range_id, user_id, keyword, body, chdate, version) VALUES ('$range_id', '$user_id', '$keyword','$body','$date','$version')");
		$message="info�" . _("Update ok, neue Version angelegt.");
	}

	refreshBacklinks($keyword, $body);
	return $message;
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
	$q .= "keyword='".decodeHTML($keyword)."' AND range_id='$range_id' ";
	$q .= "ORDER BY version DESC LIMIT 1";
	$db->query($q);
	$db->next_record();
	return $db->Record;
}

/**
* Retrieve oldest version for a given keyword
*
* @param	string	WikiPage name
*
**/
function getFirstVersion($keyword, $range_id) {
	$db=new DB_Seminar;
	$q = "SELECT * FROM wiki WHERE ";
	$q .= "keyword='$keyword' AND range_id='$range_id' ";
	$q .= "ORDER BY version ASC LIMIT 1";
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
		return array();
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
function keywordExists($str, $sem_id=NULL) {
	static $keywords;
	global $SessSemName;
	if (!$sem_id) $sem_id=$SessSemName[1];
	if (is_null($keywords)){
		$db = new DB_Seminar;
		$db->query("SELECT DISTINCT keyword FROM wiki WHERE range_id='$sem_id' ");
		while($db->next_record()){
			$keywords[$db->f(0)] = true;
		}
	}
	// retranscode html entities to ascii values (as stored in db)
	// (nessecary for umlauts)
	// BUG: other special chars like accented vowels don't work yet!
	//
	$trans_tbl = array_flip(get_html_translation_table (HTML_ENTITIES));
	$nonhtmlstr = strtr($str, $trans_tbl);

	return $keywords[$nonhtmlstr];
}


/**
* Check if keyword already exists or links to new page.
* Returns HTML-Link-Representation.
*
* @param	string	WikiPage keyword
* @param	string	current Page (for edit abort backlink)
* @param	string	out format: "wiki"=link to wiki.php, "inline"=link on same page
*
**/
function isKeyword($str, $page, $format="wiki", $sem_id=NULL){
	global $PHP_SELF;
	$trans_tbl = array_flip(get_html_translation_table (HTML_ENTITIES));
	$nonhtmlstr = strtr($str, $trans_tbl);
	if (keywordExists($str, $sem_id) == NULL) {
		if ($format=="wiki") {
			return " <a href=\"".$PHP_SELF."?keyword=" . urlencode($nonhtmlstr) . "&view=editnew&lastpage=".urlencode($page)."\">" . $str . "(?)</a>";
		} else if ($format=="inline") {
			return $str;
		}
	} else {
		if ($format=="wiki") {
			return " <a href=\"".$PHP_SELF."?keyword=".urlencode($nonhtmlstr)."\">".$str."</a>";
		} else if ($format=="inline") {
			return " <a href=\"#".urlencode($nonhtmlstr)."\">".$str."</a>";
		}
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
		$lockstring .= get_fullname($db->f("user_id"),'full',TRUE);
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
// Make sure to change routines below if this changes
//
$wiki_keyword_regex="(^|\s|\A|\>)(([A-Z]|&[AOU]uml;)([a-z0-9]|&[aou]uml;|&szlig;)+([A-Z]|&[AOU]uml;)([a-zA-Z0-9]|&[aouAOU]uml;|&szlig;)+)";

/**
* Register Wiki directive markup
*
* @param	string 	pattern
* @param	string 	replace
*
**/
function wikiMarkup($pattern, $replace, $needed_perm='autor') {
	global $wiki_directives;
	$wiki_directives[]=array($pattern, $replace, $needed_perm);
}

/**
* Process registered wiki-directives
*
* @param	string 	text, all other markup conversions applied
*
**/
function wikiDirectives($str) {
	global $wiki_directives; // array of pattern-replace-arrays
	$failure_message = _("Sie haben keine Berechtigung diese Wiki Direktive auszuf�hren.");
	if (is_array($wiki_directives)){
		foreach ($wiki_directives as $direct) {
			if ($GLOBALS['perm']->have_studip_perm($direct[2], $GLOBALS['SessSemName'][1])){
				$str = preg_replace($direct[0],$direct[1],$str);
			} else {
				$str = preg_replace($direct[0], "sprintf('<i>%s' . \$failure_message . '</i>', '\$0');", $str);
			}
		}
	}
	return $str;
}

/**
* Replace WikiWords with appropriate links in given string
* and process registered wiki-directives
*
* @param	string 	str
* @param 	string	page
* @param	string 	"wiki"=link to wiki, "inline"=link to same page
*
**/
function wikiLinks($str, $page, $format="wiki", $sem_id=NULL) {
	global $wiki_keyword_regex;
	// regex adapted from RoboWiki
	// added > as possible start of WikiWord
	// because htmlFormat converts newlines to <br>

	// in [nop] und [code] Bereichen werden keine wikiLinks gesetzt
	if (preg_match_all("'\<nowikilink\>(.+)\</nowikilink\>'isU", $str, $matches)) {
		$str = preg_replace("'\<nowikilink\>.+\</nowikilink\>'isU", '�', $str);
		$str = preg_replace("/$wiki_keyword_regex/e", "'\\1'.isKeyword('\\2', '$page', '$format','$sem_id')", $str);
		$str=wikiDirectives($str);
		$str = explode('�', $str);
		$i = 0; $all = '';
		foreach ($str as $w) $all .= $w .  $matches[1][$i++];
		return $all;
	}
	return wikiDirectives(preg_replace("/$wiki_keyword_regex/e", "'\\1'.isKeyword('\\2', '$page', '$format','$sem_id')", $str));
}

/**
* Return list of WikiWord in given page body ($str)
*
* @param	string 	str
*
**/
function getWikiLinks($str) {
	global $wiki_keyword_regex;
	$str = wikiReady($str,TRUE,FALSE,"none"); // ohne Kommentare
	// [nop] und [code] Bereiche ausblenden ...
	$str = preg_replace("'\<nowikilink\>.+\</nowikilink\>'isU", ' ', $str);
	preg_match_all("/$wiki_keyword_regex/", $str, $out, PREG_PATTERN_ORDER);
	return array_unique($out[2]);
}

/**
* Return list of WikiPages containing links to given page
*
* @param	string 	Wiki keyword
*
**/
function getBacklinks($keyword) {
	global $SessSemName;
	$db=new DB_seminar();
	$q="SELECT DISTINCT from_keyword FROM wiki_links WHERE range_id='$SessSemName[1]' AND to_keyword='$keyword'";
	$db->query($q);
	$backlinks=array();
	while ($db->next_record()) {
		if ($db->f("from_keyword")!='toc') { // don't show references from Table of contents
			$backlinks[]=$db->f("from_keyword");
		}
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
			$q="INSERT INTO wiki_links (range_id, from_keyword, to_keyword) VALUES ('$SessSemName[1]', '$keyword', '".decodeHTML($value)."')";
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
	$s .= sprintf(_(", ge&auml;ndert von %s am %s"), "</font><a href=\"about.php?username=".get_username ($wikiData[user_id])."\"><font size=-1 color=\"#333399\">".get_fullname($wikiData[user_id],'full',1)."</font></a><font size=-1>", date("d.m.Y, H:i",$wikiData[chdate])."<font size=-1>&nbsp;"."</font>");
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
	global $perm, $SessSemName, $PHP_SELF;
	if (!$perm->have_studip_perm("tutor", $SessSemName[1])) {
		begin_blank_table();
		parse_msg("error�" . _("Sie haben keine Berechtigung, Seiten zu l&ouml;schen."));
		end_blank_table();
		echo '</td></tr></table>';
		include ('lib/include/html_end.inc.php');
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

	if (!$islatest) {
		begin_blank_table();
		parse_msg("error�" . _("Die Version, die Sie l&ouml;schen wollen, ist nicht die aktuellste. &Uuml;berpr&uuml;fen Sie, ob inzwischen eine aktuellere Version erstellt wurde."));
		end_blank_table();
		echo '</td></tr></table>';
		include ('lib/include/html_end.inc.php');
		die;
	}
	begin_blank_table();
	$msg="info�" . sprintf(_("Wollen Sie die untenstehende Version %s der Seite %s wirklich l&ouml;schen?"), "<b>".$version."</b>", "<b>".$keyword."</b>") . "<br>\n";
	if (!$willvanish) {
		$msg .= _("Diese Version ist derzeit aktuell. Nach dem L&ouml;schen wird die n&auml;chst&auml;ltere Version aktuell.") . "<br>";
	} else {
		$msg .= _("Diese Version ist die derzeit einzige. Nach dem L&ouml;schen ist die Seite komplet gel�scht.") . "<br>";
	}
	$msg.="<a href=\"".$PHP_SELF."?cmd=really_delete&keyword=".urlencode($keyword)."&version=$version&dellatest=$islatest\">" . makeButton("ja2", "img") . "</a>&nbsp; \n";
	$lnk = "?keyword=".urlencode($keyword); // what to do when delete is aborted
	if (!$islatest) $lnk .= "&version=$version";
	$msg.="<a href=\"".$PHP_SELF."$lnk\">" . makeButton("nein", "img") . "</a>\n";
	$msg.='<p>'. sprintf(_("Um alle Versionen einer Seite auf einmal zu l�schen, klicken Sie %shier%s."),'<a href="'.$PHP_SELF.'?cmd=delete_all&keyword='.urlencode($keyword).'">','</a>');
	parse_msg($msg, '�', 'blank', '1', FALSE);
	end_blank_table();
	return $version;
}

/**
* Display yes/no dialog to confirm complete WikiPage deletion.
*
* @param	string	WikiPage name
*
**/
function showDeleteAllDialog($keyword) {
	global $perm, $SessSemName, $PHP_SELF;
	if (!$perm->have_studip_perm("tutor", $SessSemName[1])) {
		begin_blank_table();
		parse_msg("error�" . _("Sie haben keine Berechtigung, Seiten zu l&ouml;schen."));
		end_blank_table();
		echo '</td></tr></table>';
		include ('lib/include/html_end.inc.php');
		die;
	}
	begin_blank_table();
	$msg="info�" . sprintf(_("Wollen Sie die Seite %s wirklich vollst�ndig - mit allen Versionen - l&ouml;schen?"), "<b>".$keyword."</b>") . "<br>\n";
	if ($keyword=="WikiWikiWeb") {
		$msg .= "<p>" . _("Sie sind im Begriff die Startseite zu l�schen, die dann durch einen leeren Text ersetzt wird. Damit w�ren auch alle anderen Seiten nicht mehr direkt erreichbar.") . "</p>";
	} else {
		$numbacklinks=count(getBacklinks($keyword));
		if ($numbacklinks == 0) {
			$msg .= "<p>"._("Auf diese Seite verweist keine andere Seite.")."</p>";
		} else if ($numbacklinks == 1) {
			$msg .= "<p>"._("Auf diese Seite verweist 1 andere Seite.")."</p>";
		} else {
			$msg .= "<p>" . sprintf(_("Auf diese Seite verweisen %s andere Seiten."), count(getBacklinks($keyword))) . "</p>";
		}
	}
	$msg.="<a href=\"".$PHP_SELF."?cmd=really_delete_all&keyword=".urlencode($keyword)."\">" . makeButton("ja2", "img") . "</a>&nbsp; \n";
	$lnk = "?keyword=".urlencode($keyword); // what to do when delete is aborted
	if (!$islatest) $lnk .= "&version=$version";
	$msg.="<a href=\"".$PHP_SELF."$lnk\">" . makeButton("nein", "img") . "</a>\n";
	parse_msg($msg, '�', 'blank', '1', FALSE);
	end_blank_table();
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
	global $perm, $SessSemName, $dellatest;
	if (!$perm->have_studip_perm("tutor", $SessSemName[1])) {
		begin_blank_table();
		parse_msg("error�" . _("Sie haben keine Berechtigung, Seiten zu l&ouml;schen."));
		end_blank_table();
		echo '</td></tr></table>';
		include ('lib/include/html_end.inc.php');
		die;
	}
	$lv=getLatestVersion($keyword, $SessSemName[1]);
	if ($lv["version"] != $version) {
		begin_blank_table();
		parse_msg("error�" . _("Die Version, die Sie l&ouml;schen wollen, ist nicht die aktuellste. &Uuml;berpr&uuml;fen Sie, ob inzwischen eine aktuellere Version erstellt wurde."));
		end_blank_table();
		echo '</td></tr></table>';
		include ('lib/include/html_end.inc.php');
		die;
	}
	$q="DELETE FROM wiki WHERE keyword='$keyword' AND version='$version' AND range_id='$range_id'";
	$db=new DB_Seminar;
	$db->query($q);
	if (!keywordExists($keyword)) { // all versions have gone
		$addmsg = '<br>' . sprintf(_("Damit ist die Seite %s mit allen Versionen gel&ouml;scht."),'<b>'.$keyword.'</b>');
		$newkeyword = "WikiWikiWeb";
	} else {
		$newkeyword = $keyword;
		$addmsg = "";
	}
	begin_blank_table();
	parse_msg("info�" . sprintf(_("Version %s der Seite %s gel&ouml;scht."), $version, '<b>'.$keyword.'</b>') . $addmsg);
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
* Delete complete WikiPage with all versions and adjust backlinks.
*
* @param	string	WikiPage name
* @param	string  ID of seminar/einrichtung
*
**/
function deleteAllWikiPage($keyword, $range_id) {
	global $perm, $SessSemName;
	if (!$perm->have_studip_perm("tutor", $SessSemName[1])) {
		begin_blank_table();
		parse_msg("error�" . _("Sie haben keine Berechtigung, Seiten zu l&ouml;schen."));
		end_blank_table();
		echo '</td></tr></table>';
		include ('lib/include/html_end.inc.php');
		die;
	}
	$q="DELETE FROM wiki WHERE keyword='$keyword' AND range_id='$range_id'";
	$db=new DB_Seminar;
	$db->query($q);
	begin_blank_table();
	parse_msg("info�" . sprintf(_("Die Seite %s wurde mit allen Versionen gel&ouml;scht."), '<b>'.$keyword.'</b>'));
	end_blank_table();
	refreshBacklinks($keyword, "");
	return "WikiWikiWeb";
}



/**
* List all topics in this seminar's wiki
*
* @param  mode  string  Either "all" or "new", affects default sorting and page title.
* @param  sortby  string  Different sortings of entries.
**/
function listPages($mode, $sortby=NULL) {
	global $SessSemName, $user_id, $PHP_SELF;

	$db=new DB_Seminar;
	$db2=new DB_Seminar;

	if ($mode=="all") {
		$selfurl = $PHP_SELF."?view=listall";
		$sort = "ORDER by lastchange DESC"; // default sort order for "all pages"
		$nopages = _("In dieser Veranstaltung wurden noch keine WikiSeiten angelegt.");
	} else if ($mode=="new") {
		$lastlogindate = object_get_visit($SessSemName[1], "wiki");
		$selfurl = $PHP_SELF."?view=listnew";
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
		$q="SELECT keyword, MAX(chdate) AS lastchange, MAX(version) AS lastversion FROM wiki WHERE range_id='$SessSemName[1]' AND chdate > '$lastlogindate' GROUP BY keyword " . $sort;
	}
	$result=$db->query($q);

	// quit if no pages found
	if ($db->affected_rows() == 0) {
		begin_blank_table();
		parse_msg ("info\xa7" . $nopages);
		echo '</table></td></tr></table>';
		include ('lib/include/html_end.inc.php');
		die;
	}

	// show pages
	begin_blank_table();
	echo "<tr><td class=\"blank\" colspan=\"2\">&nbsp;</td></tr>\n";
	echo "<tr><td class=\"blank\" colspan=\"2\">";
	echo "<table width=\"99%\" border=\"0\"  cellpadding=\"2\" cellspacing=\"0\" align=\"center\">";
	echo "<tr height=28>";
	$s = "<td class=\"steel\" width=\"%d%%\" align=\"%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=\"1\" height=\"20\">%s</td>";
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
		print($tdheadleft."<a href=\"".$PHP_SELF."?keyword=" . urlencode($keyword) . "\">");
		print(htmlReady($keyword) ."</a>");
		print($tdtail);
		print($tdheadcenter.$db2->f("version").$tdtail);
		print($tdheadleft.date("d.m.Y, H:i", $lastchange));
		if ($mode=="new" && $db2->f("version")>1) {
			print("&nbsp;(<a href=\"".$PHP_SELF."?view=diff&keyword=".urlencode($keyword)."&versionssince=$lastlogindate\">"._("�nderungen")."</a>)");
		}
		print($tdtail);
		print($tdheadleft.get_fullname($userid,'full',TRUE).$tdtail."</tr>");
	}
	echo "</table><p>&nbsp;</p>";
	end_blank_table();
}

/**
* Search Wiki
*
* @param  searchfor  string  String to search for.
* @param  searchcurrentversions  bool  it true, only consider most recent versions or pages
* @param  keyword  string  last shown page or keyword for local (one page) search
* @param keyword bool if localsearch is set, only one page (all versions) is searched
**/
function searchWiki($searchfor, $searchcurrentversions, $keyword, $localsearch) {
	global $SessSemName, $user_id, $PHP_SELF;
	$range_id=$SessSemName[1];

	$db=new DB_Seminar;
	$result=NULL;

	// check for invalid search string
	if (strlen($searchfor)<3) {
		$invalid_searchstring=1;
	} else if ($localsearch && !$keyword) {
		$invalid_searchstring=1;
	} else {
		// make search string 
		$searchfori=addslashes($searchfor);
		if ($localsearch) {
			$q="SELECT * FROM wiki WHERE range_id='$range_id' AND body LIKE '%$searchfori%' AND keyword='$keyword' ORDER BY version DESC";
		} else if (!$searchcurrentversions) {
			// search in all versions of all pages
			$q="SELECT * FROM wiki WHERE range_id='$range_id' AND body LIKE '%$searchfori%' ORDER BY keyword ASC, version DESC";
		} else {
			// search only latest versions of all pages
			$q="SELECT * FROM wiki AS w1 WHERE range_id='$range_id' AND version=(SELECT MAX(version) FROM wiki AS w2 WHERE w2.range_id='$range_id' AND w2.keyword=w1.keyword) AND w1.body LIKE '%$searchfori%' ORDER BY w1.keyword ASC";
		}
		$result=$db->query($q);
	}

	showPageFrameStart();
	begin_blank_table();
	echo "<tr><td>";
	begin_blank_table();
	
	// quit if no pages found / search string was invalid
	if ($invalid_searchstring || $db->affected_rows() == 0) {
		if ($invalid_searchstring) {
			$msg="error\xa7" . _("Suchbegriff zu kurz. Geben Sie mindestens drei Zeichen ein.");
		} else {
			$tmplt=_("Die Suche nach &raquo;%s&laquo; lieferte keine Treffer.");
			$msg="error\xa7" . sprintf($tmplt, htmlReady($searchfor));
		}
		if ($keyword) {
			return showWikiPage($keyword, NULL, $msg);
		} else {
			parse_msg($msg);
			end_blank_table();
			$infobox = array ();
			$infobox[] = getSearchBox($searchfor, $keyword);
			end_blank_table();
			echo "</td>"; // end of content area
			showPageFrameEnd($infobox);
			return;
		}
	}

	// show hits
	echo "<tr><td class=\"blank\" colspan=\"2\">&nbsp;</td></tr>\n";
	echo "<tr><td class=\"blank\" colspan=\"2\">";
	echo "<table width=\"99%\" border=\"0\"  cellpadding=\"2\" cellspacing=\"0\" align=\"center\">";
	echo "<tr><td colspan=3><font size=+1>"._("Treffer f�r Suche nach")."&nbsp;&raquo;".htmlReady($searchfor)."&laquo;";
	if ($localsearch) {
		echo "&nbsp;".sprintf(_("in allen Versionen der Seite &raquo;%s&laquo;"),$keyword);
	} else if ($searchcurrentversions) {
		echo "&nbsp;"._("in aktuellen Versionen");
	} else {
		echo "&nbsp;"._("in allen Versionen");
	}
	echo "</font></td></tr>";
	echo "<tr height=28>";
	$s = "<td class=\"steel\" width=\"%d%%\" align=\"%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=\"1\" height=\"20\"><font size=-1><b>%s</b></font></td>";
	printf($s, 1, "left", "&nbsp;");
	printf($s, 10,"left",  _("Seite"));
	printf($s, 64,"left",  _("Treffer"));
	printf($s, 25,"left",  _("Version"));
	echo "</tr>";

	$c=1;
	$last_keyword="";
	$last_keyword_count=0;
	while ($db->next_record()) {

		if (!$localsearch) {
			// don't display more than one hit in a page's versions
			// offer link instead
			if ($db->f("keyword")==$last_keyword) {
				$last_keyword_count++;
				continue;
			} else if ($last_keyword_count>0) {
				print("<tr>".$tdheadleft."&nbsp;".$tdtail);
				print($tdheadleft."&nbsp;".$tdtail);
				if ($last_keyword_count==1) {
					$hitstring=_("Weitere Treffer in %s �lteren Version. Klicken Sie %shier%s, um diese Treffer anzuzeigen.");
				} else {
					$hitstring=_("Weitere Treffer in %s �lteren Versionen. Klicken Sie %shier%s, um diese Treffer anzuzeigen.");
				}
				print($tdheadleft."<em>".sprintf($hitstring,$last_keyword_count,"<b><a href=\"$PHP_SELF?view=search&searchfor=$searchfor&keyword=".urlencode($last_keyword)."&localsearch=1\">","</a></b>")."</em>".$tdtail);
				print($tdheadleft."&nbsp;".$tdtail);
				print("</tr>");
			}
			$last_keyword=$db->f("keyword");
			$last_keyword_count=0;
		}

		$class = ($c++ % 2) ? "steel1" : "steelgraulight";
		$tdheadleft="<td class=\"$class\" align=\"left\" valign=\"top\"><font size=\"-1\">";
		$tdheadcenter="<td class=\"$class\" align=\"center\"><font size=\"-1\">";
		$tdtail="</font></td>";

		print("<tr>".$tdheadleft."&nbsp;"."$tdtail");
		// Pagename
		print($tdheadleft);
		print("<a href=\"$PHP_SELF?keyword=".$db->f("keyword")."&version=".$db->f("version")."&hilight=".htmlReady($searchfor)."&searchfor=".htmlReady($searchfor)."\">");
		print($db->f("keyword")."</a>");
		print($tdtail);
		// display hit previews
		$offset=0; // step through text
		$ignore_next_hits=0; // don't show hits more than once
		$first_line=1; // don't print <br/> before first hit	
		print($tdheadleft);
		// find all occurences
		while ($offset < strlen($db->f("body"))) {
			$pos=stripos($db->f("body"), $searchfor,$offset);
			if ($pos===FALSE) break;
			$offset=$pos+1;
			if (($ignore_next_hits--)>0) {
				// if more than one occurence is found 
				// in a fragment to be displayed, 
				// the fragment is only shown once
				continue; 
			}
			// show max 80 chars
			$fragment=substr($db->f("body"),max(0, $pos-40), 80);
			#$fragment=formatReady($fragment);
			$found_in_fragment=0; // number of hits in fragment
			$fragment=preg_replace("/(".preg_quote($searchfor,"/").")/i","<span style='background-color:#FFFF88'>\\1</span>",$fragment,-1,$found_in_fragment);
			$ignore_next_hits= ($found_in_fragment>1) ? $found_in_fragment-1 : 0;
			print("...".$fragment."...");
			print "<br/>";
		}
		print($tdtail);
		// version info
		print($tdheadleft);
		print(date("d.m.Y, H:i", $db->f("chdate"))." ("._("Version")." ".$db->f("version").")");
		print($tdtail);
		print "</tr>";

	}

	if (!$localsearch && $last_keyword_count>0) {
		print("<tr>".$tdheadleft."&nbsp;".$tdtail);
		print($tdheadleft."&nbsp;".$tdtail);
		if ($last_keyword_count==1) {
			$hitstring=_("Weitere Treffer in %s �lteren Version. Klicken Sie %shier%s, um diese Treffer anzuzeigen.");
		} else {
			$hitstring=_("Weitere Treffer in %s �lteren Versionen. Klicken Sie %shier%s, um diese Treffer anzuzeigen.");
		}
		print($tdheadleft."<em>".sprintf($hitstring,$last_keyword_count,"<b><a href=\"$PHP_SELF?view=search&searchfor=$searchfor&keyword=".urlencode($last_keyword)."&localsearch=1\">","</a></b>")."</em>".$tdtail);
		print($tdheadleft."&nbsp;".$tdtail);
		print("</tr>");
	}

	echo "</table><p>&nbsp;</p>";
	end_blank_table();
	$infobox = array ();
	$infobox[] = getSearchBox($searchfor, $keyword);
	end_blank_table();
	echo "</td>"; // end of content area
	showPageFrameEnd($infobox);
}


/**
* Print a wiki header (css class: topic) including seminar name.
*
**/
function wikiSeminarHeader() {

	echo "\n<table width=\"100%\" class=\"blank\" border=0 cellpadding=0 cellspacing=0>\n";
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
	global $PHP_SELF;

	showPageFrameStart();
	wikiSinglePageHeader($wikiData, $keyword);
	begin_blank_table();
	if (!$wikiData) {
		$body = "";
		$version = 0;
		$lastpage="&lastpage=".urlencode($backpage);
	} else {
		$body = $wikiData["body"];
		$version = $wikiData["version"];
		$lastpage = "";
	}
	releaseLocks($keyword); // kill old locks
	$locks=getLock($keyword);
	$cont="";
	if ($locks && $lock["user_id"]!=$user_id) {
		parse_msg("info�" . "<p>&nbsp;</p>". sprintf(_("Die Seite wird eventuell von %s bearbeitet."), $locks) . "<br>" . _("Wenn Sie die Seite trotzdem &auml;ndern, kann ein Versionskonflikt entstehen.") . "<br>" . _("Es werden dann beide Versionen eingetragen und m&uuml;ssen von Hand zusammengef&uuml;hrt werden.") . "<br>" . _("Klicken Sie auf Abbrechen, um zur�ckzukehren."), "�", "printcontent");
	}
	if ($keyword=='toc') {
		parse_msg("info�" . "<p>&nbsp;</p>". _("Sie bearbeiten das Inhaltsverzeichnis.") . "<br>" . _("Verwenden Sie Aufz�hlungszeichen (-, --, ---), um Verweise auf Seiten hinzuzuf�gen.") , "�", "printcontent");
		if (!$body) { $body=_("- WikiWikiWeb\n- BeispielSeite\n-- UnterSeite1\n-- UnterSeite2"); }
	}

	$cont .= "<p><form method=\"post\" action=\"".$PHP_SELF."?keyword=".urlencode($keyword)."&cmd=edit\">";
	$cont .= "<textarea name=\"body\" cols=\"80\" rows=\"15\">".htmlready($body)."</textarea>\n";
	$cont .= "<input type=\"hidden\" name=\"wiki\" value=\"".urlencode($keyword)."\">";
	$cont .= "<input type=\"hidden\" name=\"version\" value=\"$version\">";
	$cont .= "<input type=\"hidden\" name=\"submit\" value=\"true\">";
	$cont .= "<input type=\"hidden\" name=\"cmd\" value=\"show\">";
	$cont .= "<br><br><input type=image name=\"submit\" value=\"abschicken\" " . makeButton("abschicken", "src") . " align=\"absmiddle\" border=0 >&nbsp;<a href=\"".$PHP_SELF."?cmd=abortedit&keyword=".urlencode($keyword).$lastpage."\"><img " . makeButton("abbrechen", "src") . " align=\"absmiddle\" border=0></a>";
	$cont .= "</form>\n";
	printcontent(0,0,$cont,"");
	$infobox = array ();
	if (get_config("EXTERNAL_HELP")) {
		$help_url=format_help_url("Basis.VerschiedenesFormat");
	} else {
		$help_url="help/index.php?help_page=ix_forum6.htm";
	}
	$infobox[] = array("kategorie" => _("Information"), "eintrag" => array(array('icon' => "ausruf_small.gif", "text"=> sprintf(_("Sie k&ouml;nnen beliebigen Text einf&uuml;gen und vorhandenen Text &auml;ndern. Beachten Sie dabei die %sFormatierungsm&ouml;glichkeiten%s. Links entstehen automatisch aus W&ouml;rtern, die mit Gro&szlig;buchstaben beginnen und einen Gro&szlig;buchstaben in der Wortmitte enthalten."),'<a href="'.$help_url.'" target="_new">','</a>'))));
	end_blank_table();
	echo "</td>"; // end of content area
	showPageFrameEnd($infobox);
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
	echo "<p><em>";
	echo sprintf(_("Version %s, letzte �nderung %s von %s."), $wikiData['version'], date("d.m.Y, H:i", $wikiData['chdate']), get_fullname($wikiData['user_id'],'full',1));
	echo "</em></p>";
	echo "<hr>";
	echo wikiDirectives(wikiReady($wikiData['body'],TRUE,FALSE,"none"));
	echo "<hr><p><font size=-1>created by Stud.IP Wiki-Module ";
	echo date("d.m.Y, H:i", time());
	echo " </font></p>";
	include ('lib/include/html_end.inc.php');
}

/**
* Show export all dialog
*
**/
function exportWiki() {
	global $PHP_SELF;
	showPageFrameStart();
	begin_blank_table();
	parse_msg("info�"._("Alle Wiki-Seiten werden als gro�e HTML-Datei zusammengef�gt und in einem neuen Fenster angezeigt. Von dort aus k�nnen Sie die Datei abspeichern."));
	$infobox = array ();
	$infobox[] = array("kategorie" => _("Information"), "eintrag" => array(array('icon' => "ausruf_small.gif", "text"=>_("Die Wiki-Seiten werden als eine zusammenh�ngende HTML-Datei ohne Links exportiert."))));
	print "</tr><tr align=center><td>";
	print "<a href=\"".$PHP_SELF."?view=wikiprintall\" target=\"_new\"><img ".makebutton("weiter","src"). " border=0></a></td></tr>";
	end_blank_table();
	echo "</td>"; // end of content area
	showPageFrameEnd($infobox);
}

/**
* Print HTML-dump of all wiki pages.
*
* @param	string	ID of veranstaltung/einrichtung
* @param	string	Short title (header) of veranstaltung/einrichtung
*
**/
function printAllWikiPages($range_id, $header) {
	echo getAllWikiPages($range_id, $header, TRUE);
}

/**
* Return HTML-dump of all wiki pages.
* Implements an iterative breadth-first traversal of WikiPage-tree.
*
* @param	string	ID of veranstaltung/einrichtung
* @param	string	Short title (header) of veranstaltung/einrichtung
* @param	bool	include html/head/body tags?
*
**/
function getAllWikiPages($range_id, $header, $fullhtml=TRUE) {
	global $SessSemName;
	$db = new DB_Seminar();
	$q = 'SELECT DISTINCT keyword FROM wiki WHERE range_id="' . $SessSemName[1] . '"  ORDER BY keyword DESC';
	$db->query($q);
	while($db->next_record()){
		$allpages[] = htmlReady($db->f('keyword'));
	}
	$out=array();
	$visited=array(); // holds names of already visited pages
	$tovisit=array(); // holds names of pages yetto visit/expand
	$tovisit[]="WikiWikiWeb"; // start with top level page
	if ($fullhtml) $out[]="<html><head><title>$header</title></head>";
	if ($fullhtml) $out[]="<body>";
	$out[]="<p><a name=\"top\"></a><em>$header</em></p>";
	while (! empty($tovisit)) { // while there are still pages left to visit
		$pagename=array_shift($tovisit);
		if (!in_array($pagename,$visited)){
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
				$out[]="<hr><a name=\"$pagename\"></a><h1>$pagename</h1>";
				$out[]="<font size=-1><p><em>";
				$out[] = sprintf(_("Version %s, letzte �nderung %s von %s."), $pagedata['version'], date("d.m.Y, H:i", $pagedata['chdate']), get_fullname($pagedata['user_id'], 'full', 1));
				$out[] = "</em></p></font>";
				// output is html without comments
				$out[]=wikiLinks(wikiReady($pagedata['body'],TRUE,FALSE,"none"),$pagename,"inline",$range_id);
				$out[] = '<p><font size=-1>(<a href="#top">' . _("nach oben") . '</a>)</font></p>';
			}
		}
		if (empty($tovisit)){
			while(! empty($allpages)){
				$l = array_pop($allpages);
				if (! in_array($l, $visited)) {
					$tovisit[] = $l;
					break;
				}
			}
		}
	}
	$out[]= '<hr><p><font size=-1>' . _("exportiert vom Stud.IP Wiki-Modul").' , ';
	$out[]=date("d.m.Y, H:i", time());
	$out[]=" </font></p>";
	if ($fullhtml) $out[]="</body></html>";
	return implode("\n",$out);
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
	print_infobox ($infobox, "wiki.jpg");
	echo "</td></tr><tr><td colspan=3 class=\"blank\">&nbsp;</td></tr>";
	echo "</table>"; // end infoframe (content+box)
	echo "</td></tr></table>"; // end page box
}

/**
* Returns an infobox category string for a searchbox
* 
* @param	string	preselection - put in searchbox
*
**/
function getSearchbox($preselection, $keyword) {
	global $PHP_SELF;
	// search
	$search_text="<form method='get' action='$PHP_SELF'>";
	$search_text.="<input type='hidden' name='view' value='search'>";
	$search_text.="<input type='hidden' name='keyword' value='".htmlReady($keyword)."'>";
	$search_text.="<input type='text' size='10' name='searchfor' value='".htmlReady($preselection)."'>";
	$search_text.="&nbsp;<input type='image' ".makeButton("suchen","src")." align='top' border='0'>";
	$search_text.="<br/>";
	$search_text.="<input type='checkbox' name='searchcurrentversions' checked>&nbsp;"._("Nur in aktuellen Versionen");
	$search_text.="</form>";
	return array("kategorie"=> _("Suche:"),
		"eintrag" => array(array(
			"icon" => "suchen.gif",
			"text"=>$search_text)));
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
	global $PHP_SELF, $show_wiki_comments;

	$versions=getWikiPageVersions($keyword);
	$versiontext = '<a href="'.$PHP_SELF.'?keyword='.urlencode($keyword).'">' . _("Aktuelle Version"). '</a><br>';
	if ($versions) {
		foreach ($versions as $v) {
			$versiontext .= "<a href=\"".$PHP_SELF."?keyword=".urlencode($keyword)."&version=".$v['version']."\">"._("Version")." ".$v['version']."</a> - ".date("d.m.Y, H:i",$v['chdate'])."<br>";
		}
	}
	if (!$versiontext) {
		$versiontext=_("Keine alten Versionen.");
	}

	$viewtext="<a href=\"".$PHP_SELF."?keyword=".urlencode($keyword)."&view=show\">"._("Standard")."</a><br>";
	$viewtext .= "<a href=\"".$PHP_SELF."?keyword=".urlencode($keyword)."&view=wikiprint&version=$version\" target=\"_new\">"._("Druckansicht")."</a>";
	if (count($versions)>=1) {
		$viewtext .= "<br><a href=\"".$PHP_SELF."?keyword=".urlencode($keyword)."&cmd=showdiff&view=diff\">"._("Text�nderungen anzeigen")."</a>";
		$viewtext .= "<br><a href=\"".$PHP_SELF."?keyword=".urlencode($keyword)."&cmd=showcombo&view=combodiff\">"._("Text mit AutorInnenzuordnung anzeigen")."</a>";
	}
	$views=array(array('icon' => "blank.gif", "text" => $viewtext));

	$backlinktext="";
	$first=1;
	$backlinks=getBacklinks($keyword);
	foreach($backlinks as $b) {
		if (!$first) {
			$backlinktext .= "<br>";
		} else {
			$first=0;
		}
		$backlinktext .= "<a href=\"".$PHP_SELF."?keyword=".urlencode($b)."\">$b</a>";
	}
	if (empty($backlinktext)) {
		$backlinktext = _("Keine Verweise vorhanden.");
	}
	$backlinktext= array(array('icon' => "icon-leer.gif", "text" =>
 $backlinktext));

 	// assemble infobox
	$infobox = array ();

	// toc
	$toccont=get_toc_content();
	if ($toccont) {
		$infobox[] = array("kategorie"=> _("Inhaltsverzeichnis")."&nbsp;".get_toc_toggler(),
			"eintrag" => array(array('icon' => "blank.gif",
			"text"=>$toccont)));
	}

	if (!$latest_version) {
		$infobox[] = array("kategorie" => _("Information"), "eintrag" => array(array('icon' => "ausruf_small.gif", "text"=> sprintf(_("Sie betrachten eine alte Version, die nicht mehr ge�ndert werden kann. Verwenden Sie dazu die %saktuelle Version%s."), '<a href="'.$PHP_SELF.'?keyword='.urlencode($keyword).'">','</a>'))));
	}
	$infobox[] = array("kategorie"  => _("Ansicht:"), "eintrag" => $views);

	// search
	$infobox[]= getSearchBox(stripslashes($_REQUEST["searchfor"]), $keyword);

	if ($latest_version) {
		// no backlinks for old versions!
		$infobox[] = array("kategorie" => _("Seiten, die auf diese Seite verweisen:"), "eintrag" => $backlinktext);
	}
	$infobox[] = array("kategorie" => _("Alte Versionen dieser Seite:"),
			"eintrag" => array(array('icon' => "blank.gif","text"=>$versiontext)));

	// comments
	$comment_all="<a href=\"$PHP_SELF?keyword=".urlencode($keyword)."&wiki_comments=all\">"._("einblenden")."</a>";
	$comment_icon="<a href=\"$PHP_SELF?keyword=".urlencode($keyword)."&wiki_comments=icon\">"._("als Icons einblenden")."</a>";
	$comment_none="<a href=\"$PHP_SELF?keyword=".urlencode($keyword)."&wiki_comments=none\">"._("ausblenden")."</a>";
	if ($show_wiki_comments=="none") {
			$comment_addon=" ("._("ausgeblendet").") ";
			$comment_text=$comment_all."<br>".$comment_icon;
	} elseif ($show_wiki_comments=="icon") {
			$comment_text=$comment_all."<br>".$comment_none;
	} elseif ($show_wiki_comments=="all") {
			$comment_text=$comment_icon."<br>".$comment_none;
	}
	$infobox[] = array("kategorie"=> _("Kommentare").$comment_addon.":",
			"eintrag" => array(array('icon' => "blank.gif",
					"text"=>$comment_text)));


// export
//	$infobox[] = array("kategorie"=> _("Export ab dieser Seite:"),
//			"eintrag" => array(array('icon' => "blank.gif","text"=>"<a href=\"wiki.php?view=exportparts&keyword=".urlencode($keyword)."\">exportieren</a>")));
	return $infobox;
}

/**
* Returns an infobox string holding information and action links for
* diff view of current page.
*
* @param	string	WikiPage name
*
**/
function getDiffPageInfobox($keyword) {
	global $PHP_SELF;

	$versions=getWikiPageVersions($keyword);
	$versiontext = '<a href="'.$PHP_SELF.'?keyword='.urlencode($keyword).'">'. _("Aktuelle Version").'</a><br>';
	if ($versions) {
		foreach ($versions as $v) {
			$versiontext .= "<a href=\"".$PHP_SELF."?keyword=".urlencode($keyword)."&version=".$v['version']."\">"._("Version")." ".$v['version']."</a> - ".date("d.m.Y, H:i",$v['chdate'])."<br>";
		}
	}
	if (!$versiontext) {
		$versiontext=_("Keine alten Versionen.");
	}

	$viewtext="<a href=\"".$PHP_SELF."?keyword=".urlencode($keyword)."&view=show\">"._("Aktuelle Version")."</a><br>";
	$views=array(array('icon' => "blank.gif", "text" => $viewtext));

	$infobox = array ();
	$infobox[] = array("kategorie" => _("Information"), "eintrag" => array(array('icon' => "ausruf_small.gif", "text"=>_("Sie betrachten die �nderungsgeschichte eines Dokumentes. Falls einzelne Versionen gel�scht wurden, kann es zu falschen AutorInnenzuordnungen kommen."))));
	$infobox[] = array("kategorie"  => _("Ansicht:"), "eintrag" => $views);
	$infobox[] = array("kategorie" => _("Alte Versionen dieser Seite:"),
			"eintrag" => array(array('icon' => "blank.gif","text"=>$versiontext)));
	return $infobox;
}

function get_toc_toggler() {
	$toc=getWikiPage("toc",0);
	if (!$toc) return '';
	$cont="";
	$ToggleText=array(_("verstecken"),_("anzeigen"));
	$cont.="<script type=\"text/javascript\">
		function toggle(obj) {
		    var elstyle = document.getElementById(obj).style;
		    var text    = document.getElementById(obj + \"tog\");
		    if (elstyle.display == 'none') {
			elstyle.display = 'block';
			text.innerHTML = \"{$ToggleText[0]}\";
		    } else {
			elstyle.display = 'none';
			text.innerHTML = \"{$ToggleText[1]}\";
		    }
		}
		</script>";
	$cont.="<span class='wikitoc_toggler'> (<a id=\"00toctog\" href=\"javascript:toggle('00toc');\">{$ToggleText[0]}</a>)</span>";
	return $cont;
}
function get_toc_content() {
	global $perm, $SessSemName, $PHP_SELF;
	// Table of Contents / Wiki navigation
	$toc=getWikiPage("toc",0);
	if ($toc) {
		$toccont.="<div class='wikitoc'>";
		$toccont.="<div id='00toc'>";
		$toccont.= wikiLinks(wikiReady($toc["body"],TRUE,FALSE,$show_comments), "toc", "wiki");
		$toccont.="</div>";
		$toccont.="</div>\n";
	} 
	if ($GLOBALS['perm']->have_studip_perm('autor', $GLOBALS['SessSemName'][1])){
		$toccont.="<div class='wikitoc_editlink'>";
		if ($toc) {
			$toccont.="<a href=\"$PHP_SELF?keyword=toc&view=edit\">"._("bearbeiten")."</a>";
		} else {
			$toccont.="<a href=\"$PHP_SELF?keyword=toc&view=edit\">"._("erstellen")."</a>";
		}
		$toccont.="</div>";
	}
	return $toccont;
}

/**
* Display wiki page.
*
* @param	string	WikiPage name
* @param	string	WikiPage version
* @param	string	ID of special dialog to be printed (delete, delete_all) or message string for parse_msg to display
* @param	string  Comment show mode (all, none, icon)
*
**/
function showWikiPage($keyword, $version, $special="", $show_comments="icon", $hilight=NULL) {
	global $perm, $SessSemName, $PHP_SELF;

	showPageFrameStart();

	// show dialogs if any..
	//
	if ($special == "delete") {
		$version=showDeleteDialog($keyword, $version);
	} else if ($special == "delete_all") {
		showDeleteAllDialog($keyword);
	} else if ($special) {
		begin_blank_table();
		parse_msg($special);
		end_blank_table();
	}

	$wikiData = getWikiPage($keyword, $version);
	if (!$version) {
		$latest_version=1;
	} else {
		$wikiLatest= getLatestVersion($keyword, $SessSemName[1]);
		if ($version==$wikiLatest["version"]) {
			$latest_version=1;
		} else {
			$latest_version=0;
		}
	}

	// show page logic
	//
	wikiSinglePageHeader($wikiData, $keyword);

	if ($perm->have_studip_perm("autor", $SessSemName[1])) {
		if (!$latest_version) {
			$edit='<img src="'.$GLOBALS['ASSETS_URL'].'images/ausruf_small2.gif">'. _("�ltere Version, nicht bearbeitbar!");
		} else {
			$edit="";
			if ($perm->have_studip_perm("autor", $SessSemName[1])) {
				$edit.="<a href=\"".$PHP_SELF."?keyword=".urlencode($keyword)."&view=edit\"><img ".makeButton("bearbeiten","src")." border=\"0\"></a>";
			}
			if ($perm->have_studip_perm("tutor", $SessSemName[1])) {
				$edit.="&nbsp;<a href=\"".$PHP_SELF."?keyword=".urlencode($keyword)."&cmd=delete&version=latest\"><img ".makeButton("loeschen","src")." border=\"0\"></a>";
			}
		}
		$edit .= "<br>&nbsp;";
	} else {
		$edit="";
	}

	begin_blank_table();
	echo "<tr>";
	echo "<td class=\"printcontent\"><div align=\"center\">&nbsp;<br>";
	echo $edit;
	echo "</div></td></tr>";
	end_blank_table();

	begin_blank_table();
	echo "<tr>\n";
	$cont="";

	$cont .= wikiLinks(wikiReady($wikiData["body"],TRUE,FALSE,$show_comments), $keyword, "wiki");
	if ($hilight) {
		// Highlighting must only take place outside HTML tags, so
		// 1. save all html tags in array $founds[0]
		// 2. replace all html tags with  \007\007
		// 3. highlight
		// 4. replace all \007\007 with corresponding saved tags
		$founds=array();
		preg_match_all("/<[^>].*>/U",$cont,$founds);
		$cont=preg_replace("/<[^>].*>/U","\007\007",$cont);
		$cont=preg_replace("/(".preg_quote(htmlReady($hilight),"/").")/i","<span style='background-color:#FFFF88'>\\1</span>",$cont,-1);
		foreach($founds[0] as $f) {
			$cont=preg_replace("/\007\007/",$f,$cont,1);
		}
	}
	$num_body_lines=substr_count($wikiData['body'], "\n");
	if ($num_body_lines<15) {
		$cont .= "<p>";
		$cont .= str_repeat("&nbsp;<br>", 15-$num_body_lines);
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
* @param	string	Only show versions newer than this timestamp
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
		parse_msg ("info�" . _("Es gibt keine zu vergleichenden Versionen."));
		end_blank_table();
		echo '</td></tr></table>';
		include ('lib/include/html_end.inc.php');
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
		$diffarray = '<b><font size=-1>'. _("�nderungen zu") . " </font> $zusatz</b><p>";
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
	$infobox=getDiffPageInfobox($keyword);
	showPageFrameEnd($infobox);
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

function toDiffLineArray($lines, $who) {
	$dla = array();
	$lines = explode("\n",preg_replace("/\r/",'',$lines));
	foreach ($lines as $l) {
		$dla[] = new DiffLine($l, $who);
	}
	return $dla;
}

function showComboDiff($keyword, $db=NULL) {

	global $SessSemName;

	$version2=getLatestVersion($keyword, $SessSemName[1]);
	$version1=getFirstVersion($keyword, $SessSemName[1]);
	$version2=$version2["version"];
	$version1=$version1["version"];

	showPageFrameStart();
	wikiSinglePageHeader($wikiData, $keyword);

	echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">";

	// create combodiff
	if (!$db) {
		$db=new DB_Seminar();
	}
	$wd1 = getWikiPage($keyword, $version1, $db);
	$diffarray1 = toDiffLineArray($wd1['body'], $wd1['user_id']);
	$current_version = $version1 + 1;
	$differ = new line_diff();
	while ($current_version <= $version2) {
		$wd2 = getWikiPage($keyword, $current_version, $db);
		if ($wd2) {
			$diffarray2 = toDiffLineArray($wd2['body'], $wd2['user_id']);
			$newarray = $differ->arr_compare("diff", $diffarray1, $diffarray2);
			$diffarray1=array();
			foreach ($newarray as $i) {
				if ($i->status["diff"] != "-") {
					$diffarray1[]=$i;
				}
			}
		}
		$current_version++;
	}
	$content="<table>";
	$count=0;
	$authors=array();
	foreach ($diffarray1 as $i) {
		if ($i && !in_array($i->who, $authors)) {
			$authors[]=$i->who;
			if ($count % 4 == 0) {
				$content.= "<tr width=\"100%\">";
			}
			$content.= "<td bgcolor=".create_color($count)." width=14><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=12 height=12></td><td><font size=-1>".get_fullname($i->who,'full',1)."</font></td><td><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=12 height=12></td>";
			if ($count % 4 == 3) {
				$content .= "</tr>";
			}
			$count++;
		}
	}
	echo "<tr><td class=\"steel1\" colspan=2>";
	echo "<p><font size=-1>&nbsp;<br>";
	echo _("Legende der AutorInnenfarben:");
	echo "<table cellpadding=6 cellspacing=6>$content</table>\n";
	echo "</p>";
	echo "<table cellpadding=0 cellspacing=0 width=\"100%\">";
	$last_author=None;
	$collect="";
	$diffarray1[]=NULL;
	foreach ($diffarray1 as $i) {
		if (!$i || $last_author != $i->who) {
			if (trim($collect)!="") {
				$idx=array_search($last_author, $authors);
				$col=create_color($idx);
				echo "<tr bgcolor=$col>";
				echo "<td width=30 align=center valign=top>";
				echo "<img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" height=3 width=3><br>";
				echo "<img src=\"".$GLOBALS['ASSETS_URL']."images/info.gif\" ". tooltip(_("�nderung von").' ' . get_fullname($last_author), TRUE, TRUE). ">";
				echo "</td>";
				echo "<td><font size=-1>";
				echo wikiReady($collect);
				echo "</font></td>";
				echo "</tr>";
			}
			$collect="";
		}
		if ($i) {
			$last_author = $i->who;
			$collect .= $i->text;
		}
	}
	echo "</table></td></tr>";
	echo "</table>     ";
	$infobox=getDiffPageInfobox($keyword);
	showPageFrameEnd($infobox);
}

function create_color($index) {
	$shades=array("e","b","d","a","c","9","8","7","6","5");
	if ($index>70) {
		$index=$index%70;
	}
	$shade=$shades[$index/7]."0";
	switch ($index % 7) {
		case 0: return "#".$shade.$shade.$shade;
		case 1: return "#ff".$shade.$shade;
		case 2: return "#".$shade."ff".$shade;
		case 3: return "#".$shade.$shade."ff";
		case 4: return "#ffff".$shade;
		case 5: return "#ff".$shade."ff";
		case 6: return "#".$shade."ffff";
	}
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
			$this->arr1[] = new DiffLine($line, 'nobody');
		}
		foreach (explode("\n",$str2) as $line)
		{
			$this->arr2[] = new DiffLine($line, 'nobody');
		}
	}
	function str_compare($str1, $str2, $show_equal=FALSE)
	{
		$this->set_str('diff',$str1,$str2);
		$this->compare();

		$str = '';
		$lastdiff = "";
		$textaccu = "";
		$template = "<tr>%s<td width=\"10\">&nbsp;</td><td><font size=-1>%s</font>&nbsp;</td></tr>";
		foreach ($this->toArray() as $obj)
		{
			if ($show_equal || $obj->get('diff') != $this->equal) {
				if ($lastdiff && $obj->get("diff") != $lastdiff) {
					$str .= sprintf($template, $lastdiff, wikiReady($textaccu));
					$textaccu="";
				}
				$textaccu .= $obj->text();
				$lastdiff = $obj->get("diff");
			}
		}
		if ($textaccu) {
			$str .= sprintf($template, $lastdiff, wikiReady($textaccu));
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
				$arr[] = $arr1[$x];
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
	var $who; // who originally wrote this line?

	function DiffLine($text, $who=NULL)
	{
		$this->text = "$text\n";
		$this->status = array();
		$this->who = $who;
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
