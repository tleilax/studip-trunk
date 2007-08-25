<?php

// $Id$

require_once('config.inc.php');
require_once('lib/classes/cssClassSwitcher.inc.php');
include_once('lib/classes/idna_convert.class.php');
include_once('lib/classes/UserConfig.class.php');

/*****************************************************************************
get_ampel_state is a helper function for get_ampel_write and get_ampel_read.
It checks if the new parameters lead to a "lower" trafficlight. If so, the new
level and the new text are set and returned.
/*****************************************************************************/

function get_ampel_state ($cur_ampel_state, $new_level, $new_text) {
	if ($cur_ampel_state["access"] < $new_level) {
		$cur_ampel_state["access"] = $new_level;
		$cur_ampel_state["text"] = $new_text;
	}
	return $cur_ampel_state;
}

/*****************************************************************************
get_ampel_write, waehlt die geeignete Grafik in der Ampel Ansicht
(fuer Berechtigungen) aus. Benoetigt den Status in der Veranstaltung
und auf der Anmeldeliste und den read_level der Veranstaltung
/*****************************************************************************/

function get_ampel_write ($mein_status, $admission_status, $write_level, $print="TRUE", $start = -1, $ende = -1, $temporaly = 0) {
	global $perm;

	$ampel_state["access"] = 0;		// the current "lowest" access-level. If already yellow, it can't be green again, etc.
	$ampel_state["text"] = "";			// the text for the reason, why the "ampel" has the current color
	/*
	 * 0 : green
	 * 1 : yellow
	 * 2 : red
	 */

	if ($mein_status == "dozent" || $mein_status == "tutor" || $mein_status == "autor") { // in den F�llen darf ich auf jeden Fall schreiben
		$ampel_state = get_ampel_state($ampel_state,0,"");
		//echo $ampel_state["access"]."<br/>";
		//echo $ampel_state["text"]."<br/>";
	} else {
		if ($temporaly != 0) {
			$ampel_state = get_ampel_state($ampel_state,1,_("(Vorl. Eintragung)"));
		}

		if (($start != -1) && ($start > time())) {
			$ampel_state = get_ampel_state($ampel_state,1,_("(Starttermin)"));
		}

		if (($ende != -1) && ($ende < time())) {
			$ampel_state = get_ampel_state($ampel_state,2,_("(Beendet)"));
		}

		switch($write_level) {
			case 0 : //Schreiben darf jeder
				$ampel_state = get_ampel_state($ampel_state,0,"");
			break;
			case 1 : //Schreiben duerfen nur registrierte Stud.IP Teilnehmer
				if ($perm->have_perm("autor"))
					$ampel_state = get_ampel_state($ampel_state,0,"");
				else
					$ampel_state = get_ampel_state($ampel_state,2,_("(Registrierungsmail beachten!)"));
			break;
			case 2 : //Schreiben nur mit Passwort
				if ($perm->have_perm("autor"))
					$ampel_state = get_ampel_state($ampel_state,1,_("(mit Passwort)"));
				else
					$ampel_state = get_ampel_state($ampel_state,2,_("(Registrierungsmail beachten!)"));
			break;
			case 3 : //Schreiben nur nach Anmeldeverfaren
				if ($perm->have_perm("autor"))
					if ($admission_status)
						$ampel_state = get_ampel_state($ampel_state,1,_("(Anmelde-/Warteliste)"));
					else
						$ampel_state = get_ampel_state($ampel_state,1, _("(Anmeldeverfahren)"));
				else
					$ampel_state = get_ampel_state($ampel_state,2, _("(Registrierungsmail beachten!)"));
			break;
		}
	}

	switch ($ampel_state["access"]) {
		case 0 :
			$color = "gruen";
			break;
		case 1 :
			$color = "gelb";
			break;
		case 2 :
			$color = "rot";
			break;
	}

	$ampel_status="<img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/ampel_$color.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>". $ampel_state["text"] ."</font>";

	if ($print==TRUE) {
		echo $ampel_status;
	}
	return $ampel_status;
}

/*****************************************************************************
get_ampel_read, waehlt die geeignete Grafik in der Ampel Ansicht
(fuer Berechtigungen) aus. Benoetigt den Status in der Veranstaltung
und auf der Anmeldeliste und den read_level der Veranstaltung
/*****************************************************************************/

function get_ampel_read ($mein_status, $admission_status, $read_level, $print="TRUE", $start = -1, $ende = -1, $temporaly = 0) {
	global $perm;

	$ampel_state["access"] = 0;		// the current "lowest" access-level. If already yellow, it can't be green again, etc.
	$ampel_state["text"] = "";			// the text for the reason, why the "ampel" has the current color
	/*
	 * 0 : green
	 * 1 : yellow
	 * 2 : red
	 */

	if ($mein_status) { // wenn ich im Seminar schon drin bin, darf ich auf jeden Fall lesen
		$ampel_state = get_ampel_state($ampel_state,0,"");
	} else {
			if ($temporaly != 0) {
				$ampel_state = get_ampel_state($ampel_state,1,_("(Vorl. Eintragung)"));
			}

			if (($start != -1) && ($start > time())) {
				$ampel_state = get_ampel_state($ampel_state,1,_("(Starttermin)"));
			}

			if (($ende != -1) && ($ende < time())) {
				$ampel_state = get_ampel_state($ampel_state,2,_("(Beendet)"));
			}

		switch($read_level){
			case 0 :	//Lesen darf jeder
				$ampel_state = get_ampel_state($ampel_state,0,"");
			break;
			case 1 :	//Lesen duerfen registrierte nur Stud.IP Teilnehmer
				if ($perm->have_perm("autor"))
					$ampel_state = get_ampel_state($ampel_state,0,"");
				else
					$ampel_state = get_ampel_state($ampel_state,2,_("(Registrierungsmail beachten!)"));
			break;
			case 2 :	//Lesen nur mit Passwort
				if ($perm->have_perm("autor"))
					$ampel_state = get_ampel_state($ampel_state,1,_("(mit Passwort)"));
				else
					$ampel_state = get_ampel_state($ampel_state,2,_("(Registrierungsmail beachten!)"));
			break;
			case 3 :	//Lesen nur nach Anmeldeverfaren
				if ($perm->have_perm("autor"))
					if ($admission_status)
						$ampel_state = get_ampel_state($ampel_state,1,_("(Anmelde-/Warteliste)"));
					else
						$ampel_state = get_ampel_state($ampel_state,1,_("(Anmeldeverfahren)"));
				else
					$ampel_state = get_ampel_state($ampel_state,2,_("(Registrierungsmail beachten!)"));
			break;
		}
	}

	switch ($ampel_state["access"]) {
		case 0 :
			$color = "gruen";
			break;
		case 1 :
			$color = "gelb";
			break;
		case 2 :
			$color = "rot";
			break;
	}

	$ampel_status="<img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/ampel_$color.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>". $ampel_state["text"] ."</font>";

	if ($print==TRUE) {
		echo $ampel_status;
	}
	return $ampel_status;
}

function htmlReady ($what, $trim = TRUE, $br = FALSE) {
	if ($trim) $what = trim(htmlentities($what,ENT_QUOTES));
	else $what = htmlentities($what,ENT_QUOTES);
	// workaround zur Darstellung von Zeichen in der Form &#x268F oder &#283;
	$what = preg_replace('/&amp;#(x[0-9a-f]+|[0-9]+);/i', '&#$1;', $what);
	if ($br) $what = preg_replace("/(\n\r|\r\n|\n|\r)/", "<br />", $what); // newline fixen
	return $what;
}

function JSReady ($what = "", $target = "overlib") {
	switch ($target) {

	case "contact" :
		$what = htmlentities($what,ENT_COMPAT);
		$what = str_replace("\n","<br />",$what);
		$what = str_replace("\r","",$what);
		return $what;
	break;

	case "alert" :
		$what = addslashes(htmlentities($what,ENT_COMPAT));
		$what = str_replace("\r","",$what);
		$what = str_replace("\n","\\n",$what); // alert boxen stellen keine html tags dar
		return $what;
	break;

	case 'forum' :
		$what = str_replace("\r",'',formatReady($what));
		if (ereg('\[quote',$what) AND ereg('\[/quote\]',$what))
			$what = quotes_decode($what);
		$what = '<p width="100%"class="printcontent">' . $what . '</p>';
		return addslashes(htmlentities($what,ENT_COMPAT));
		break;

	case "overlib" :
	default :
		$what = addslashes(htmlentities(htmlentities($what,ENT_COMPAT),ENT_COMPAT));
		$what = str_replace("\n","<br />",$what);
		$what = str_replace("\r","",$what);
		return $what;
		break;
	}
}

//////////////////////
// de- und encodieren der Quotings

function quotes_decode ($description) {
// Funktion um Quotings zu decoden
// $description: der Text der gequotet werden soll, wird zurueckgegeben

	$description = " ".$description;
	$stack = Array();
	$curr_pos = 1;
	while ($curr_pos && ($curr_pos < strlen($description))) {
		$curr_pos = strpos($description, "[", $curr_pos);
		if ($curr_pos) {
			$possible_start = substr($description, $curr_pos, 6);
			$possible_end = substr($description, $curr_pos, 8);
			if (strcasecmp("[quote", $possible_start) == 0) {
				array_push($stack, $curr_pos);
				++$curr_pos;
				}
			else if (strcasecmp("[/quote]", $possible_end) == 0) {
				if (sizeof($stack) > 0) {
					$start_index = array_pop($stack);
					$before_start_tag = substr($description, 0, $start_index);
					$between_tags = substr($description, $start_index+6, $curr_pos - $start_index-6);
//                                        echo $between_tags."<hr>";
					$after_end_tag = substr($description, $curr_pos + 8);
					IF (substr($between_tags,0,1)=="=") { //wir haben einen Namen angegeben
						$nameend_pos = strpos($between_tags,"]");
						$quote_name = substr($between_tags,1,$nameend_pos-1);
						IF (substr($between_tags,$nameend_pos,5)=="]<br>") // ja, hier wurde anstaendig gequotet
							$between_tags = substr($between_tags,$nameend_pos+6);
						ELSE // da wird gepfuscht, also mal besser Finger weg
							$between_tags = substr($between_tags,$nameend_pos+1);
						$between_tags = "<b>".sprintf(_("%s hat geschrieben:"),$quote_name)."</b><hr>".$between_tags;
						}
					ELSE { // kein Name, also nur Zitat
						$nameend_pos = strpos($between_tags,"]");
						IF (substr($between_tags,$nameend_pos,5)=="]<br>") // ja, hier wurde anstaendig gequotet
							$between_tags = "<b>"._("Zitat:")."</b><hr>".substr($between_tags,$nameend_pos+6);
						ELSE // da wird gepfuscht, also mal besser Finger weg
							$between_tags = "<b>"._("Zitat:")."</b><hr>".substr($between_tags,$nameend_pos+1);
						}
					$description = $before_start_tag . "<blockquote class=\"quote\">";
					$description .= $between_tags . "</blockquote>";
					if (substr($after_end_tag,0,6)=="<br />") {
						$after_end_tag = substr($after_end_tag,6);
					}
					$description .= $after_end_tag;
					if (sizeof($stack) > 0) {
						$curr_pos = array_pop($stack);
						array_push($stack, $curr_pos);
						++$curr_pos;
						}
					else $curr_pos = 1;
					}
				else ++$curr_pos;
				}
			else ++$curr_pos;
			}
		}
	return $description;
}

///////////

function quotes_encode ($description,$author) {
// Funktion um Quotings zu encoden
// $description: der Text der gequotet werden soll, wird zurueckgegeben
// $author: Name des urspruenglichen Autors

	if (ereg("%%\[editiert von",$description)) { // wurde schon mal editiert
		$postmp = strpos($description,"%%[editiert von");
		$description = substr_replace($description," ",$postmp);
		}
	while (ereg("\[quote",$description) AND ereg("\[/quote\]",$description)){ // da wurde schon mal zitiert...
		$pos1 =         strpos($description, "[quote");
		$pos2 =         strpos($description, "[/quote]");
		if ($pos1 < $pos2)
			$description = substr($description,0,$pos1)."[...]".substr($description,$pos2+8);
		else break; // hier hat einer von Hand rumgepfuscht...
		}
	$description = "[quote=".$author."]\n".$description."\n[/quote]";
	return $description;
}

// Hilfsfunktion f�r formatReady
function format_help($what, $trim = TRUE, $extern = FALSE, $wiki = FALSE, $show_comments="icon") {

	if (preg_match_all("'\[code\](.+)\[/code\]'isU", $what, $match_code)) {
		$what = htmlReady($what, $trim, FALSE);
		$what = preg_replace("'\[code\].+\[/code\]'isU", '�', $what);
		if ($wiki == TRUE)
			//$what = wiki_format(symbol(smile(FixLinks(format(latex($what, $extern)), FALSE, TRUE, TRUE, $extern), $extern), $extern), $show_comments);
			$what = wiki_format(symbol(smile(latex(FixLinks(format($what), FALSE, FALSE, TRUE, $extern, TRUE), $extern), $extern), $extern), $show_comments);
		else
			//$what = symbol(smile(FixLinks(format(latex($what, $extern)), FALSE, TRUE, TRUE, $extern), $extern), $extern);
			$what = symbol(smile(latex(FixLinks(format($what), FALSE, FALSE, TRUE, $extern), $extern), $extern), $extern);
		$what = explode('�', $what);
		$i = 0;
		$all = '';
		foreach ($what as $w) {
			if ($match_code[1][$i] == ''){
				$all .=  $w ;
			} else {
				$a = highlight_string( $match_code[1][$i] , TRUE);
				$all .= $w . (($wiki == TRUE)? "<nowikilink>$a</nowikilink>":$a );
			}
			$i++;
		}
		return $all;
	}
	$what = htmlReady($what, $trim, FALSE);
	if ($wiki == TRUE)
		//return symbol(smile(FixLinks(wiki_format(format(latex($what, $extern)), $show_comments), FALSE, TRUE, TRUE, $extern), $extern), $extern);
		//return wiki_format(symbol(smile(FixLinks(format(latex($what, $extern)), FALSE, TRUE, TRUE, $extern), $extern), $extern), $show_comments);
		return wiki_format(symbol(smile(latex(FixLinks(format($what), FALSE, FALSE, TRUE, $extern, TRUE), $extern), $extern), $extern), $show_comments);
	else
	//	return symbol(smile(FixLinks(format(latex($what, $extern)), FALSE, TRUE, TRUE, $extern), $extern), $extern);
		return symbol(smile(latex(FixLinks(format($what), FALSE, FALSE, TRUE, $extern), $extern), $extern), $extern);
}

/**
* universal and very usable functions to get all the special stud.ip formattings
*
*
* @access       public
* @param        string $what		what to format
* @param        boolean $trim		should the output trimmed?
* @param        boolean $extern TRUE if called from external pages ('externe Seiten')
* @param	boolean $wiki		if TRUE format for wiki
* @param	string 	$show_comments	Comment mode (none, all, icon), used for Wiki comments
* @return       string
*/
function formatReady ($what, $trim = TRUE, $extern = FALSE, $wiki = FALSE, $show_comments="icon") {

	if (preg_match_all("'\[nop\](.+)\[/nop\]'isU", $what, $matches)) {
		$what = preg_replace("'\[nop\].+\[/nop\]'isU", '{_*~*%}', $what);
		$what = str_replace("\n", '<br />', format_help($what, $trim, $extern, $wiki, $show_comments));
		$what = explode('{_*~*%}', $what);
		$i = 0; $all = '';
		foreach ($what as $w) {
			if ($matches[1][$i] == '') {
				$all .= $w;
			} else {
				$a = preg_replace("/\n?\r\n?/", '<br />', htmlReady($matches[1][$i], $trim, FALSE));
				$all .= $w . (($wiki == TRUE)? "<nowikilink>$a</nowikilink>" : $a);
			}
			$i++;
		}
		return $all;
	}
	return str_replace("\n", '<br />', format_help($what, $trim, $extern, $wiki, $show_comments));
}


/**
* the special version of formatReady for Wiki-Webs
*
*
* @access       public
* @param        string $what		what to format
* @param        string $trim		should the output trimmed?
* @param        boolean $extern TRUE if called from external pages ('externe Seiten')
* @return       string
*/
function wikiReady ($what, $trim = TRUE, $extern = FALSE, $show_comments="icon") {
	return formatReady ($what, $trim, $extern, TRUE, $show_comments);
}

/**
* a special wiki formatting routine (used for comments)
*
*
* @access       public
* @param        string $text		what to format
* @param	string	$show_comments	How to show comments
*/
function wiki_format ($text, $show_comments) {
	if ($show_comments=="icon" || $show_comments=="all") {
		$text=preg_replace("#\[comment(=.*)?\](.*)\[/comment\]#emsU","format_wiki_comment('\\2','\\1',$show_comments)",$text);
	} else {
		$text=preg_replace("#\[comment(=.*)?\](.*)\[/comment\]#msU","",$text);
	}
	return $text;
}


function format_wiki_comment($comment, $metainfo, $show_comment) {
	$metainfo=trim($metainfo,"=");
	if ($show_comment=="all") {
		$commenttmpl="<table style=\"border:thin solid;margin: 5px;\" bgcolor=\"#ffff88\"><tr><td><font size=-1><b>"._("Kommentar von")." %1\$s:</b>&nbsp;</font></td></tr><tr class=steelgrau><td class=steelgrau><font size=-1>%2\$s</font></td></tr></table>";
		return sprintf($commenttmpl, $metainfo, stripslashes($comment));
	} elseif ($show_comment=="icon") {
		$comment = decodehtml($comment);
		$comment = preg_replace("/<.*>/U","",$comment);
		$metainfo = decodeHTML($metainfo);
		return '<nowikilink><a href="javascript:void(0);" '.tooltip(sprintf("%s %s:\n%s",_("Kommentar von"),$metainfo,$comment),TRUE,TRUE) . "><img src=\"".$GLOBALS['ASSETS_URL']."images/icon-comment.gif\" border=0></a></nowikilink>";
	} else {
		echo "<p>Error: unknown show_comment value in format_wiki_comment: ".$show_comment."</p>";
		die();
	}
}


////////////////////////////////////////////////////////////////////////////////


function latex($text, $extern = FALSE) {
	global $LATEXRENDER_ENABLE,
	       $LATEX_PATH,
	       $DVIPS_PATH,
	       $CONVERT_PATH,
	       $IDENTIFY_PATH,
	       $TMP_PATH,
	       $LATEX_FORMATS;

	if ($LATEXRENDER_ENABLE && isset($LATEX_FORMATS)) {
		include_once("lib/classes/latexrender.class.php");
		$latex = new LatexRender($GLOBALS['DYNAMIC_CONTENT_PATH'].'/tex', $GLOBALS['DYNAMIC_CONTENT_URL'].'/tex');
		$latex->_latex_path = $LATEX_PATH;
		$latex->_dvips_path = $DVIPS_PATH;
		$latex->_convert_path = $CONVERT_PATH;
		$latex->_identify_path = $IDENTIFY_PATH;
		$latex->_tmp_dir = $TMP_PATH;

		// There can be many formatting tags that are
		// handled by the latex renderer
		// The tags and their LaTex templates are set in the
		// variable $LATEX_FORMATS (in local.inc)
		//
		foreach( $LATEX_FORMATS as $formatname => $format) {
			$latex->setFormat($formatname, $format["template"]);
			$to_match=sprintf("#\[%s\](.*?)\[/%s\]#si", $format["tag"], $format["tag"]);
			preg_match_all($to_match,$text,$tex_matches);

			for ($i=0; $i < count($tex_matches[0]); $i++) {
				$pos = strpos($text, $tex_matches[0][$i]);
				$latex_formula = decodeHTML($tex_matches[1][$i]);

				$url = $latex->getFormulaURL($latex_formula);

				if ($url != false) {
					$text = substr_replace($text, "<img src=\"".$url."\">",$pos,strlen($tex_matches[0][$i]));
				} else {
					if ($extern) {
						$text = '';
					} else {
						$errtxt = $latex->getErrorString();
						if (!$errtxt) $errtxt = _("Nicht interpretierbare oder m�glicherweise gef�hrliche Latex Formel");
						$text = substr_replace($text, '['.$errtxt.']',$pos,strlen($tex_matches[0][$i]));
					}
				}
			}
		}
	}
	return $text;
}

/**
* decodes html entities to normal characters
*
* @access	public
* @param	string
* @return	string
*/
function decodeHTML ($string) {
	$string = strtr($string, array_flip(get_html_translation_table(HTML_ENTITIES,ENT_QUOTES)));
	$string = preg_replace("/&#([0-9]+);/me", "chr('\\1')", $string);
	return $string;
}

/**
* uses a special syntax to formatting text
*
* @access	public
* @param	string	text to format
* @return	string
*/
function format ($text) {
  $cache = StudipCacheFactory::getCache();
  if (!($formatted_text = $cache->read($key = 'formatted_text/' . md5($text)))) {
    $cache->write($key, $formatted_text = _real_format($text));
  }
  return $formatted_text;
}

/**
 * This function does all the grunt work for #format
 */
function _real_format($text) {
	$text = preg_replace("'\n?\r\n?'", "\n", $text);
	$pattern = array(
					"'^--+(\d?)$'me",               // Trennlinie
//					"'\[pre\](.+?)\[/pre\]'is",    // praeformatierter Text
					"'(^|\s)%(?!%)(\S+%)+(?=(\s|$))'e",     // SL-kursiv
					"'(^|\s)\*(?!\*)(\S+\*)+(?=(\s|$))'e",  // SL-fett
					"'(^|\s)_(?!_)(\S+_)+(?=(\s|$))'e",     // SL-unterstrichen
					"'(^|\s)#(?!#)(\S+#)+(?=(\s|$))'e",     // SL-diktengleich
					"'(^|\s)\+(?!\+)(\S+\+)+(?=(\s|$))'e",  // SL-groesser
					"'(^|\s)-(?!-)(\S+-)+(?=(\s|$))'e",     // SL-kleiner
					"'(^|\s)&gt;(?!&gt;)(\S+&gt;)+(?=(\s|$))'ie",  // SL-hochgestellt
					"'(^|\s)&lt;(?!&lt;)(\S+&lt;)+(?=(\s|$))'ie",  // SL-tiefgestellt
					"'(^|\n)\!([^!].*)'m",              // Ueberschrift 4. Ordnung
					"'(^|\n)\!{2}([^!].*)'m",           // Ueberschrift 3. Ordnung
					"'(^|\n)\!{3}([^!].*)'m",           // Ueberschrift 2. Ordnung
					"'(^|\n)\!{4}([^!].*)'m",           // Ueberschrift 1. Ordnung
					"'(\n|\A)(([-=]+ .+(\n|\Z))+)'e",    // Listen
                                        "'(\n|\A)((\\|.+(\n|\Z))+)'e",    // Tabellen
					"'%%(\S|\S.*?\S)%%'s",               // ML-kursiv
					"'\*\*(\S|\S.*?\S)\*\*'s",           // ML-fett
					"'__(\S|\S.*?\S)__'s",                     // ML-unterstrichen
					"'##(\S|\S.*?\S)##'s",                     // ML-diktengleich
					"'((--)+|(\+\+)+)(\S|\S.*?\S)\\1'se",        // ML-kleiner / ML-groesser
					"'&gt;&gt;(\S|\S.*?\S)&gt;&gt;'is",     // ML-hochgestellt
					"'&lt;&lt;(\S|\S.*?\S)&lt;&lt;'is",     // ML-tiefgestellt
					"'{-(\S|\S.*?\S)-}'s",                  // ML-strike-through
					"'\n\n  (((\n\n)  )*(.+?))(\Z|\n\n(?! ))'se",   // Absatz eingerueckt
					"'\n?(</?h[1-4r]>)\n?'"                        // removes newline delimiters
					);
	$replace = array(
					"'<hr noshade=\"noshade\" width=\"98%\" size=\"'.('\\1' ? '\\1' : '1').'\" align=\"center\" />'",
//					"<pre>\\1</pre>",
					"'\\1<i>'.substr(str_replace('%', ' ', '\\2'), 0, -1).'</i>'",
					"'\\1<b>'.substr(str_replace('*', ' ', '\\2'), 0, -1).'</b>'",
					"'\\1<u>'.substr(str_replace('_', ' ', '\\2'), 0, -1).'</u>'",
					"'\\1<tt>'.substr(str_replace('#', ' ', '\\2'), 0, -1).'</tt>'",
					"'\\1<big>'.substr(str_replace('+', ' ', '\\2'), 0, -1).'</big>'",
					"'\\1<small>'.substr(str_replace('-', ' ', '\\2'), 0, -1).'</small>'",
					"'\\1<sup>'.substr(str_replace('&gt;', ' ', '\\2'), 0, -1).'</sup>'",
					"'\\1<sub>'.substr(str_replace('&lt;', ' ', '\\2'), 0, -1).'</sub>'",
					"\n<h4>\\2</h4>",
					"\n<h3>\\2</h3>",
					"\n<h2>\\2</h2>",
					"\n<h1>\\2</h1>",
					"preg_call_format_list('\\2')",
					"preg_call_format_table('\\2')",
					"<i>\\1</i>",
					"<b>\\1</b>",
					"<u>\\1</u>",
					"<tt>\\1</tt>",
					"preg_call_format_text('\\1', format('\\4'))",
					"<sup>\\1</sup>",
					"<sub>\\1</sub>",
					"<strike>\\1</strike>",
					"'<blockquote>'.format(stripslashes('\\1')).'</blockquote>'",
					"\\1"
					);
	$text = preg_replace('#\[pre\](.+?)\[/pre\]#is', '<pre>\\1</pre>', $text); // praeformatierter Text
	$regtxt = '!(((\[[^\]\[\n\f]+\])?(https?|ftp)://[^\s\]<]+)|(\[tex\].*?\[/tex\]))!is';
	if (preg_match_all($regtxt, $text, $match)) {
		$text = preg_replace($regtxt, '�', $text);
		$text = preg_replace($pattern, $replace, $text);
		$i = 0;
		while (($pos = strpos($text, '�'))  !== false){
			$text = substr($text,0, $pos) . $match[1][$i++] . substr($text, $pos +1);
		}
	} else {
		$text = preg_replace($pattern, $replace, $text);
	}
	return $text;
}

/**
* callback function used by format() to generate big and small font
*
* @access	private
* @param	string  string containing a string with quick-format char (++ or --)
* @param	string  string containing text between quick-format char
* @return	string
*/
function preg_call_format_text($ctxt, $content){
	$c = strlen($ctxt) / 2;
	if ($c > 5) $c = 5;
	$tag = ($ctxt{1} == '+')? 'big':'small';
	return str_repeat('<'.$tag.'>', $c) . $content. str_repeat('</'.$tag.'>', $c);
}

/**
* callback function used by format() to generate html-lists
*
* @access	private
* @param	string	string containing a list in quick-format-syntax
* @return	string
*/
function preg_call_format_list ($content) {
	$items = array();
	$lines = explode("\n", $content);
	$level = 0;
	$current_level = 0;
	for ($i = 0; $i < sizeof($lines); $i++) {
		$line = $lines[$i];
		if (preg_match("'^([-=]+) (.*)$'", $line, $matches)) {

			$matched_level = strlen($matches[1]);
			if ($matched_level > $current_level)
				$level++;
			else if ($matched_level < $current_level)
				$level = $matched_level;

			if ($matches[1]{0} == "-")
				$list_tags[] = "ul";
			else
				$list_tags[] = "ol";

			$items[$i]["level"] = $level;
			$items[$i]["content"] = $matches[2];

			$current_level = $level;
		}
	}

	for ($i = 0;$i < sizeof($items); $i++) {
		$level_diff = $items[$i]["level"] - $items[$i + 1]["level"];

		if ($i == 0) {
			$ret .= "<{$list_tags[$i]}>";
			$stack[] = $list_tags[$i];
		}

		if ($level_diff > 0) {
			$ret .= "<li>{$items[$i]['content']}</li></" . array_pop($stack) . ">";
			for ($j = $items[$i]["level"] - 1; $j > $items[$i + 1]["level"]; $j--)
				$ret .= "</li></" . array_pop($stack) . ">";
		}
		else if ($level_diff < 0) {
			$ret .= "<li>{$items[$i]['content']}<" . $list_tags[$i + 1] . ">";
			$stack[] = $list_tags[$i + 1];
		}
		else
			$ret .= "<li>{$items[$i]['content']}";

		if ($level_diff >= 0 && $i < sizeof($items) - 1)
			$ret .= "</li>";
		$level = $items[$i]["level"];
	}

	return $ret;
}

/**
* callback function used by format() to generate tables
*
* @access	private
* @param	string	string containing a table in quick-format-syntax
* @return	string
*/
function preg_call_format_table($content) {
	$lines = explode("\n", $content);
	$tcode=array();
	$tcode[]="<table style=\"border-collapse: collapse\" cellpadding=3>";
	foreach ($lines as $l) {
		$tcode[]=preg_replace("'\|(.+?)(\|\s*\n(?=\|)|\|\Z)'se","'<tr><td style=\"border: thin solid #666666\"><font size=-1>'.preg_replace('/\|/','</font></td><td style=\"border: thin solid #666666\"><font size=-1>','\\1').'</font></td></tr>'", $l);
	}
	$tcode[]="</table>";
	return implode("",$tcode)."\n";
}


/**
* removes all characters used by quick-format-syntax
*
* @access	public
* @param	string
* @return	string
*/
function kill_format ($text) {
	$text = preg_replace("'\n?\r\n?'", "\n", $text);
	// wir wandeln [code] einfach in [pre][nop] um und sind ein Problem los ... :-)
	$text = preg_replace_callback ( "|(\[/?code\])|isU", create_function('$a', 'return ($a[0] == "[code]")? "[pre][nop]":"[/nop][/pre]";'), $text);

	$pattern = array(
					"'(^|\n)\!{1,4}(.+)$'m",      // Ueberschriften
					"'(\n|\A)(-|=)+ (.+)$'m",     // Aufzaehlungslisten
					"'(^|\s)%(?!%)(\S+%)+'e",     // SL-kursiv
					"'(^|\s)\*(?!\*)(\S+\*)+'e",  // SL-fett
					"'(^|\s)_(?!_)(\S+_)+'e",     // SL-unterstrichen
					"'(^|\s)#(?!#)(\S+#)+'e",     // SL-diktengleich
					"'(^|\s)\+(?!\+)(\S+\+)+'e",  // SL-groesser
					"'(^|\s)-(?!-)(\S+-)+'e",     // SL-kleiner
					"'(^|\s)>(?!>)(\S+>)+'e",     // SL-hochgestellt
					"'(^|\s)<(?!<)(\S+<)+'e",     // SL-tiefgestellt
					"'%%(\S|\S.*?\S)%%'s",        // ML-kursiv
					"'\*\*(\S|\S.*?\S)\*\*'s",    // ML-fett
					"'__(\S|\S.*?\S)__'s",        // ML-unterstrichen
					"'##(\S|\S.*?\S)##'s",        // ML-diktengleich
					"'\+\+(((\+\+)*)(\S|\S.*?\S)?\\2)\+\+'s",  // ML-groesser
					"'--(((--)*)(\S|\S.*?\S)?\\2)--'s",        // ML-kleiner
					"'>>(\S|\S.*?\S)>>'is",  // ML-hochgestellt
					"'<<(\S|\S.*?\S)<<'is",  // ML-tiefgestellt
					"'\n\n  (((\n\n)  )*(.+?))(\Z|\n\n(?! ))'s",  // Absatz eingerueckt
					"'(?<=\n|^)--+(\d?)(\n|$|(?=<))'m", // Trennlinie
					"'\[pre\](.+?)\[/pre\]'is" ,        // praeformatierter Text
					"'\[nop\].+\[/nop\]'isU",
					//"'\[.+?\](((http\://|https\://|ftp\://)?([^/\s]+)(.[^/\s]+){2,})|([-a-z0-9_]+(\.[_a-z0-9-]+)*@([a-z0-9-]+(\.[a-z0-9-]+)+)))'i",
					"'\[(.+?)\](((http\://|https\://|ftp\://)?([^/\s]+)(\.[^/\s]+){2,}(/[^\s]*)?)|([-a-z0-9_]+(\.[_a-z0-9-]+)*@([a-z0-9-]+(\.[a-z0-9-]+)+)))'i",
			//		"'\[quote=.+?quote\]'is",    // quoting
					"'(\s):[^\s]+?:(\s)'s"              // smileys

					);
	$replace = array(
					"\\1\\2", "\\1\\3",
					"'\\1'.substr(str_replace('%', ' ', '\\2'), 0, -1)",
					"'\\1'.substr(str_replace('*', ' ', '\\2'), 0, -1)",
					"'\\1'.substr(str_replace('_', ' ', '\\2'), 0, -1)",
					"'\\1'.substr(str_replace('#', ' ', '\\2'), 0, -1)",
					"'\\1'.substr(str_replace('+', ' ', '\\2'), 0, -1)",
					"'\\1'.substr(str_replace('-', ' ', '\\2'), 0, -1)",
					"'\\1'.substr(str_replace('>', ' ', '\\2'), 0, -1)",
					"'\\1'.substr(str_replace('<', ' ', '\\2'), 0, -1)",
					"\\1", "\\1", "\\1", "\\1", "\\1", "\\1",
					"\\1", "\\1", "\n\\1\n", "", "\\1",'[nop] [/nop]',
					//"\\2",
					'$1 ($2)',
					 //"",
					  '$1$2');

	if (preg_match_all("'\[nop\](.+)\[/nop\]'isU", $text, $matches)) {
		$text = preg_replace($pattern, $replace, $text);
		$text = explode("[nop] [/nop]", $text);
		$i = 0;
		$all = '';
		foreach ($text as $w)
			$all .= $w . $matches[1][$i++];

		return $all;
	}

	return preg_replace($pattern, $replace, $text);
}

/**
* detects links in a given string and convert it into html-links
*
* @access	public
* @param	string	text to convert
* @param	string	TRUE if all forms of newlines have to be converted in single \n
* @param	boolean	TRUE if newlines have to be converted into <br>
* @param	boolean	TRUE if pictures should be displayed
* @param	boolean TRUE if called from external pages ('externe Seiten')
* @return	string
*/
function FixLinks ($data = "", $fix_nl = TRUE, $nl_to_br = TRUE, $img = FALSE, $extern = FALSE, $wiki = FALSE) {
	global $STUDIP_DOMAINS;
	$chars= '&;_a-z0-9-';
	if (empty($data)) {
		return $data;
	}
	if ($fix_nl)
		$data = preg_replace("/\n?\r\n?/", "\n", $data); // newline fixen
	if (!$wiki) $wiki = 0;  // schei.. php
	$img = $img ? 'TRUE' : 'FALSE';
	$extern = $extern ? 'TRUE' : 'FALSE';
	// add protocol type
	$pattern = array("/([ \t\]\n=]|^)www\./i", "/([ \t\]\n]|^)ftp\./i");
	$replace = array("\\1http://www.", "\\1ftp://ftp.");
	$fixed_text = preg_replace($pattern, $replace, $data);

	//transform the domain names of links within Stud.IP
	$fixed_text = TransformInternalLinks($fixed_text);

	$pattern = array(
					'#((\[(img)(\=([^\n\f:]+?))?(:(\d{1,3}%?))?(:(center|right))?(:([^\]]+))?\]|\[(?!img)([^\n\f\[]+)\])?(((https?://|ftp://)(['.$chars.':]+@)?)['.$chars.']+(\.['.$chars.':]+)*/?([^<\s]*[^\.\s\]<])*))#ie',
					'#(?<=\s|^|\>)(\[([^\n\f]+?)\])?(['.$chars.']+(\.['.$chars.']+)*@(['.$chars.']+(\.['.$chars.']+)+))#ie'
					);
	$replace = array(
			"preg_call_link(array('\\1', '\\5', '\\7', '\\12', '\\13', '\\3', '\\9', '\\11'), 'LINK', $img, $extern, $wiki)",
			"preg_call_link(array('\\2', '\\3'), 'MAIL', false, $extern, $wiki)");
	$fixed_text = preg_replace($pattern, $replace, $fixed_text);

	if ($nl_to_br)
		$fixed_text = str_replace("\n", "<br />", $fixed_text);

	return $fixed_text;
}

/**
* callback function used by FixLinks()
*
* @access	private
* @param	array $params	parameters extracted by the regular expression
* @param	string	$mod	type of lin ('LINK' or 'MAIL')
* @param	boolean	$img	TRUE to handle image-links
* @param	boolean	$extern	TRUE if called from external pages ('externe Seiten')
* @return	string
*/
function preg_call_link ($params, $mod, $img, $extern = FALSE, $wiki = FALSE) {
	global $auth, $STUDIP_DOMAINS;
	$chars= '&;_a-z0-9-';

	$pu = @parse_url($params[4]);
	if (($pu['scheme'] == 'http' || $pu['scheme'] == 'https')
	&& ($pu['host'] == $_SERVER['HTTP_HOST'] || $pu['host'].':'.$pu['port'] == $_SERVER['HTTP_HOST'])
	&& strpos($pu['path'], $GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']) === 0){
		$intern = true;
	}
	if ($extern)
		$link_pic = '';
	elseif ($intern)
		$link_pic = "<img src=\"".$GLOBALS['ASSETS_URL']."images/link_intern.gif\" border=\"0\" hspace=\"2\" />";
	else
		$link_pic = "<img src=\"".$GLOBALS['ASSETS_URL']."images/link_extern.gif\" border=\"0\" />";

	if ($mod == 'LINK') {
		if ($params[5] != 'img') {
			if ($params[3] == '')
				$params[3] = $params[4];
			else $params[3] = format($params[3]);
			$params[4] = str_replace('&amp;', '&', $params[4]);
			$tbr = '<a href="'.idna_link($params[4]).'"'.($intern ? '' : ' target="_blank"').">$link_pic{$params[3]}</a>";
		}
		elseif ($img) {
			// Don't execute scripts
			if ((basename($pu['path']) != 'sendfile.php') && $intern && $pu['query'])
				return $params[0];
			else if (!preg_match(':.+(\.jpg|\.jpeg|\.png|\.gif)$:i', $params[0]))
				$tbr = $params[0];
			else {
				if ($params[2]) {
					$width = '';
					// width in percent
					if (substr($params[2], -1) == '%') {
						$width = (int) substr($params[2], 0, -1) < 100 ? " width=\"{$params[2]}\"" : ' width="100%"';
					}
					else {
						// width of image in pixels
						if (is_object($auth) && $auth->auth['xres'])
							// 50% of x-resolution maximal
							$max_width = floor($auth->auth['xres'] / 2);
						else
							$max_width = 400;
						$width = ($params[2] < $max_width) ? " width=\"{$params[2]}\"" : " width=\"$max_width\"";
					}
				}
				$tbr = '<img src="'.idna_link($params[4])."\" $width border=\"0\" alt=\"{$params[1]}\" title=\"{$params[1]}\">";
				if (preg_match('#(((https?://|ftp://)(['.$chars.':]+@)?)['.$chars.']+(\.['.$chars.':]+)*/?([^<\s]*[^\.\s\]<])*)#i', $params[7])) {
					$pum = @parse_url($params[7]);
					if (($pum['scheme'] == 'http' || $pum['scheme'] == 'https')
					&& ($pum['host'] == $_SERVER['HTTP_HOST'] || $pum['host'].':'.$pum['port'] == $_SERVER['HTTP_HOST'])
					&& strpos($pum['path'], $GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']) === 0){
						$imgintern = true;
					}
					$tbr = '<a href="'.idna_link($params[7]).'"'.($imgintern ? '' : ' target="_blank"').'>'.$tbr.'</a>';
				}
				if ($params[6])
					$tbr = "<div align=\"{$params[6]}\">$tbr</div>";
			}
		}
		else
			return $params[0];

	}
	elseif ($mod == 'MAIL') {
		if ($params[0] != '')
			$tbr = '<a href="mailto:'.idna_link($params[1], true). "\">$link_pic{$params[0]}</a>";
		else
			$tbr = '<a href="mailto:'.idna_link($params[1], true)."\">$link_pic{$params[1]}</a>";
	}
	if ($wiki) $tbr = '<nowikilink>'.$tbr.'</nowikilink>';
	return $tbr;
}

/**
* convert links with 'umlauten' to punycode
*
* @access	public
* @param	string	link to convert
* @param	boolean	 for mailadr = true and for other link = false
* @return	string  link in punycode
*/
function idna_link($link, $mail = false){
	if (!$GLOBALS['CONVERT_IDNA_URL']) return $link;
	if (preg_match('/&\w+;/i',$link)) { //umlaute?  (html-coded)
		$IDN = new idna_convert();
		$out = false;
		if ($mail){
			if (preg_match('#^([^@]*)@(.*)$#i',$link, $matches)) {
				$out = $IDN->encode(utf8_encode(decodeHTML($matches[2]))); // false by error
				$out = ($out)? $matches[1].'@'.$out : link;
			}
		}elseif (preg_match('#^([^/]*)//([^/]*)((/.*$)|$)#i',$link, $matches)) {
			$out = $IDN->encode(utf8_encode(decodeHTML($matches[2]))); // false by error
			$out = ($out)? $matches[1].'//'.$out.$matches[3] : $link;
		}
		return ($out)? $out:$link;
	}
	return $link;
}


/**
* create smileys
*
* This functions converts the smileys codes (":name:") notation an the shorts,
* located in the config.inc into the assigned pictures.
* On every smiley a link to show_smiley.php overview is given (only if $extern
* is FALSE). A tooltip which shows the smiley code is given, too.
*
* @access	public
* @param		string	the text to convert
* @param		boolean	TRUE if function is called from external pages
* @return		string	convertet text
*/
function smile ($text = "", $extern = FALSE) {
	global $SMILE_SHORT, $CANONICAL_RELATIVE_PATH_STUDIP;

	if(empty($text))
		return $text;

	//smileys in the ":name:" notation
	$pattern = "'(\>|^|\s):([_a-zA-Z][_a-z0-9A-Z-]*):(?=$|\<|\s)'m";
	$replace = "\\1";
	if (!$extern) {
		$path = $GLOBALS['DYNAMIC_CONTENT_URL'] . '/smile';
		$replace .= "<a href=\"{$CANONICAL_RELATIVE_PATH_STUDIP}show_smiley.php\" target=\"_blank\">";
		$replace .= "<img alt=\"\\2\" title=\"\\2\" border=\"0\" src=\"";
		$replace .= "$path/\\2.gif\"></a>\\3";
	} else {
		$path = $GLOBALS['DYNAMIC_CONTENT_URL'] . '/smile';
		$replace .= "<img alt=\"\\2\" title=\"\\2\" border=\"0\" src=\"";
		$replace .= "$path/\\2.gif\">\\3";
	}
	$text = preg_replace($pattern, $replace, $text);

	//smileys in short notation
	$patterns = array();
	$replaces = array();
	reset($SMILE_SHORT);
	while (list($key,$value) = each($SMILE_SHORT)) {
		$patterns[] = "'(\>|^|\s)" . preg_quote($key) . "(?=$|\<|\s)'m";
		if (!$extern) {
			$replaces[] = "\\1<a href=\"{$CANONICAL_RELATIVE_PATH_STUDIP}show_smiley.php\" target=\"_blank\">"
					. "<img alt=\"$value\" title=\"$value\" border=\"0\" src=\""
					. "$path/$value.gif\"></a>\\2";
		} else {
			$replaces[] = "\\1<img alt=\"$value\" title=\"$value\" border=\"0\" src=\""
					. "$path/$value.gif\">\\2";
		}
	}
	return preg_replace($patterns, $replaces, $text);
}


/**
* create symbols from the shorts
*
* This functions converts the short, locatet in the config.inc
* into the assigned pictures. It uses a different directory
* as the smile-function, becauso symbols should not be shown in
* the smiley and so, no link is given onto the picture. A tooltip which
* shows the symbol code is given, too.
*
* @access	public
* @param		string	the text to convert
* @param		boolean	TRUE if function is called from external pages
* @return		string	convertet text
*/
function symbol ($text = "", $extern = FALSE) {
	global $SYMBOL_SHORT, $CANONICAL_RELATIVE_PATH_STUDIP;

	if(empty($text))
		return $text;

		$path = $GLOBALS['DYNAMIC_CONTENT_URL'] . '/symbol';

	$patterns = array();
	$replaces = array();
	//symbols in short notation
	reset($SYMBOL_SHORT);
	while (list($key, $value) = each($SYMBOL_SHORT)) {
		$patterns[] = "'" . preg_quote($key) . "'m";
		$replaces[] = "<img " . tooltip($key)
				. " border=\"0\" src=\"$path/$value.gif\">";
	}

	return preg_replace($patterns, $replaces, $text);
}

//Beschneidungsfunktion fuer alle printhead Ausgaben
function mila ($titel, $size = 60) {
	global $auth;

	if ($auth->auth["jscript"] AND $size == 60) {
		//hier wird die maximale Laenge berechnet, nach der Abgeschnitten wird (JS dynamisch)
		if (strlen ($titel) >$auth->auth["xres"] / 13)
			$titel=substr($titel, 0, $auth->auth["xres"] / 13)."... ";
	}
	else {
		if (strlen ($titel) >$size)
			$titel=substr($titel, 0, $size)."... ";
	}
	return $titel;
}

//Ausgabe der Aufklapp-Kopfzeile
function printhead ($breite, $left, $link, $open, $new, $icon,
		$titel, $zusatz, $timestmp = 0, $printout = TRUE, $index = "", $indikator = "age") {
		// Verzweigung was der PFeil anzeigen soll

	if ($indikator == "viewcount") {
		if ($index == "0") {
			$timecolor = "#BBBBBB";
		} else {
			$tmp = $index;
			if ($tmp > 68)
				$tmp = 68;
			$tmp = 68-$tmp;
			$green = dechex(255 - $tmp);
			$other = dechex(119 + ($tmp/1.5));
			$timecolor= "#" . $other . $green . $other;
		}
	} elseif ($indikator == "rating") {
		if ($index == "?") {
			$timecolor = "#BBBBBB";
		} else {
			$tmp = (ABS(1-$index))*10*3;
			$green = dechex(255 - $tmp);
			$other = dechex(0);
			$red = dechex(255);
			$timecolor= "#" . $red . $green . $other;
		}
	} elseif ($indikator == "score") {
		if ($index == "0") {
			$timecolor = "#BBBBBB";
		} else {
			if ($index > 68)
				$tmp = 68;
			else
				$tmp = $index;
			$tmpb = 68-$tmp;
			$blue = dechex(255 - $tmpb);
			$other = dechex(119 + ($tmpb/1.5));
			$timecolor= "#" . $other . $other . $blue;
		}
	} else {
		if ($timestmp == 0)
			$timecolor = "#BBBBBB";
		else {
			if ($new == TRUE)
				$timecolor = "#FF0000";
			else {
				$timediff = (int) log((time() - $timestmp) / 86400 + 1) * 15;
				if ($timediff >= 68)
					$timediff = 68;
				$red = dechex(255 - $timediff);
				$other = dechex(119 + $timediff);
				$timecolor= "#" . $red . $other . $other;
			}
		}
	}

	if ($open == "close") {
		$print = "<td bgcolor=\"".$timecolor."\" class=\"printhead2\" nowrap width=\"1%\"";
		$print .= "align=left valign=\"top\" nowrap>";
	}
	else {
		$print = "<td bgcolor=\"".$timecolor."\" class=\"printhead3\" nowrap width=\"1%\"";
		$print .= " align=left valign=\"top\" nowrap>";
	}

	if ($link)
		$print .= "<a href=\"".$link."\">";

	$print .= "&nbsp;<img src=\"";
	if ($open == "open")
		$titel = "<b>" . $titel . "</b>";

	if ($link) {
		$addon = '';
		if ($index) $addon =  " ($indikator: $index)";
		if ($open == "close")
			$print .= $GLOBALS['ASSETS_URL']."images/forumgrau2.gif\"" . tooltip(_("Objekt aufklappen") . $addon);

		if ($open == "open")
			$print .= $GLOBALS['ASSETS_URL']."images/forumgraurunt2.gif\"" . tooltip(_("Objekt zuklappen") . $addon);
	}
	else {
		if ($open == "close") {
			$print .= $GLOBALS['ASSETS_URL']."images/forumgrau2.gif\"";
		}
		else {
			$print .= $GLOBALS['ASSETS_URL']."images/forumgraurunt2.gif\"";
		}
	}

	$print .= " border=0>";
	if ($link) {
		$print .= "</a>";
	}
	$print .= "</td><td class=\"printhead\" nowrap width=\"1%\" valign=\"middle\">$icon</td>";
	$print .= "<td class=\"printhead\" align=\"left\" width=\"20%\" nowrap valign=\"bottom\">&nbsp;";
	$print .= $titel."</td>"."<td align=\"right\" nowrap class=\"printhead\" width=\"99%\" valign='bottom'>";
	$print .= $zusatz."&nbsp;</td>";

	if ($printout)
		echo $print;
	else
		return $print;
}

//Ausgabe des Contents einer aufgeklappten Kopfzeile
function printcontent ($breite, $write = FALSE, $inhalt, $edit, $printout = TRUE, $addon="") {

	$print = "<td class=\"printcontent\" width=\"22\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	$print .= "</td><td class=\"printcontent\" width=\"$breite\" valign=\"bottom\"><br>";
	$print .= $inhalt;

	if ($edit) {
		$print .= "<br><br><div align=\"center\">$edit</div><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" height=\"6\" border=\"0\">";
		if ($addon!="")
			if (substr($addon,0,5)=="open:") // es wird der �ffnen-Pfeil mit Link ausgegeben
				$print .= "</td><td valign=\"middle\" class=\"steel1\" nowrap><a href=\"".substr($addon,5)."\"><img src=\"".$GLOBALS['ASSETS_URL']."images/forumgrau4.gif\" align=\"middle\" border=\"0\"".tooltip(_("Bewertungsbereich �ffnen"))."></a>&nbsp;";
			else { 				// es wird erweiterter Inhalt ausgegeben
				$print .= "</td><td class=\"steelblau_schatten\" nowrap>";
				$print .= "<font size=\"-2\" color=\"#444444\">$addon";
	}		}
	else
		$print .= "<br>";

	$print .= "</td>";

	if ($printout)
		echo $print;
	else
		return $print;
}


/*****************************************************************************
print_infobox, baut einen Info-Kasten aus folgenden Elementen zusammen: Bild (separat uebergeben), Ueberschriften, Icons, Inhalt (in Array).
Der Aufruf des Bildes ist optional.
Beispielaufbau f&uuml;r das Array:

$infobox = array	(
array  ("kategorie"  => "Information:",
		"eintrag" => array	(
						array	 (	"icon" => "suchen.gif",
								"text"  => "Um weitere Veranstaltungen bitte Blabla"
								),
						array	 (	"icon" => "admin.gif",
								"text"  => "um Verwaltung  Veranstaltungen bitte Blabla"
								)
		)
	),
array  ("kategorie" => "Aktionen:",
		   "eintrag" => array	(
						array (	"icon" => "ausruf_small.gif",
								"text"  => "es sind noch 19 Veranstaltungen vorhanden."
								)
		)
	)
);
/*****************************************************************************/

function print_infobox($content, $picture = '', $dont_display_immediatly = FALSE) {

    // get template
    $template =& $GLOBALS['template_factory']->open('infobox/infobox_generic_content');

    // fill attributes
    $template->set_attribute('picture', $picture);
    $template->set_attribute('content', $content);

    // render template
    if ($dont_display_immediatly)
       return $template->render();
    else
       echo $template->render();
}


/**
* Returns a given text as html tooltip
*
* title and alt attribute is default, with_popup means a JS alert box activated on click
* @access        public
* @param        string $text
* @param        boolean        $with_alt        return text with alt attribute
* @param        boolean $with_popup        return text with JS alert box on click
* @return        string
*/
function tooltip ($text, $with_alt = TRUE, $with_popup = FALSE) {
	$ret = "";
	if ($with_popup)
		$ret = " onClick=\"alert('".JSReady($text,"alert")."');\"";
	$text = preg_replace("/(\n\r|\r\n|\n|\r)/", " ", $text);
	$text = htmlReady($text);
	if ($with_alt)
		$ret .= " alt=\"$text\"";
	$ret .= " title=\"$text\" ";
	return $ret;
}


/**
* Returns a an entry in the top navigation bar
*
*
* @access       public
* @param        string $icon       	Path to the icon
* @param        string $URL		URL on button
* @param        string $text		Hovertext under the Button
* @param        string $tooltip		for Tooltip Window
* @param        integer $size		Width of the Element
* @param        string $target		same or new window...
* @param        string $align
* @param        string $toolwindow	For a special Toolwindow
* @accesskey	string			key used for the shortcut
* @return       string
*/
function MakeToolbar ($icon,$URL,$text,$tooltip,$size,$target,$align="center",$toolwindow="FALSE", $accesskey=FALSE) {
	global $user;
	if (is_object($user) && !$user->cfg->getValue(null,"ACCESSKEY_ENABLE"))
		$accesskey = FALSE;
	if ($accesskey !== FALSE)
		$accesskey_tooltip = "  [ALT] + ".strtoupper($accesskey);
	if ($toolwindow == "FALSE") {
		$tool = tooltip($tooltip.$accesskey_tooltip);
	} else {
		$tool = tooltip($tooltip,TRUE,TRUE);
	}

	if ($target != '')
		$target = ' target="'.$target.'"';
	if ($accesskey !== FALSE)
		$accesskey = ' accesskey="'.$accesskey.'"';

	$toolbar = '<td class="toolbar" align="'.$align.'">';

	$toolbar .= '<img border="0" src="'. $GLOBALS['ASSETS_URL'] . 'images/blank.gif" height="1" width="45"><br>'
			  .'<a class="toolbar" href="'.$URL.'"'.$target.$accesskey.'><img border="0" src="'.$icon.'" '.$tool.'><br>'
			  .'<img border="0" src="'. $GLOBALS['ASSETS_URL'] .'images/blank.gif" height="6" width="'.$size.'"><br>'
			  .'<b><font size="2">'.$text.'</font></b></a><br>'
			  .'<img border="0" src="'. $GLOBALS['ASSETS_URL'] . 'images/blank.gif" height="4" width="30">';
	$toolbar .= "</td>\n";
	return $toolbar;
}

/**
* detects internal links in a given string and convert used domain to the domain
* actually used (only necessary if more than one domain exists)
*
* @param	string	text to convert
* @return	string  text with convertes internal links
*/
function TransformInternalLinks($str){
	static $domain_data = null;
	if (is_array($GLOBALS['STUDIP_DOMAINS']) && count($GLOBALS['STUDIP_DOMAINS']) > 1) {
		if (is_null($domain_data)){
			$domain_data['domains'] = '';
			foreach ($GLOBALS['STUDIP_DOMAINS'] as $studip_domain) $domain_data['domains'] .= '|' . preg_quote($studip_domain);
			$domain_data['domains'] = preg_replace("'(\|.+?)((/.*?)|\|)'", "\\1[^/]*?\\2", $domain_data['domains']);
			$domain_data['domains'] = substr($domain_data['domains'], 1);
			$domain_data['user_domain'] = preg_replace("'^({$domain_data['domains']})(.*)$'i", "\\1", $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
			$domain_data['user_domain_scheme'] = 'http' . (($_SERVER['HTTPS'] || $_SERVER['SERVER_PORT'] == 443) ? 's' : '') . '://';
		}
		return preg_replace("'https?\://({$domain_data['domains']})((/[^<\s]*[^\.\s<])*)'i", "{$domain_data['user_domain_scheme']}{$domain_data['user_domain']}\\2", $str);
	} else {
		return $str;
	}
}
?>
