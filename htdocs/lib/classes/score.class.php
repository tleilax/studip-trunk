<?
/*
score.class.php - Score class
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

class Score {
	var $score;	// Score of the user
	var $publik;	// whether or not the score is published
	var $ismyscore;	// wheter or not this is my own score
	var $title;	// Title that refers to the score
	var $myscore;	// my own Score
	var $mygender;
	var $score_content_cache = null;

	
	// Konstruktor
	
	function Score ($user_id) {
		$this->ismyscore = $this->CheckOwner($user_id);
		$this->myscore = $this->GetMyScore();
		$this->mygender = $this->GetGender($user_id);
		$this->title = $this->gettitel($this->myscore, $this->mygender);
		$this->publik = $this->CheckScore($user_id);
	}

function CheckOwner ($user_id) {
	global $user;
	if ($user_id == $user->id)
		return TRUE;
	else
		return FALSE;	
}

function GetGender ($user_id) {
	$db=new DB_Seminar;
	$db->query("SELECT geschlecht AS gender FROM user_info WHERE user_id = '$user_id'");
	$db->next_record();
	return $db->f("gender");
}

function PublishScore () {
	global $user;
	$db=new DB_Seminar;
	$query = "UPDATE user_info "
		." SET score = '$this->myscore'"
		." WHERE user_id = '$user->id'";
	$db->query($query);
	$this->publik = $this->myscore;
}

function KillScore () {
	global $user;
	$db=new DB_Seminar;
	$query = "UPDATE user_info "
		." SET score = 0"
		." WHERE user_id = '$user->id'";
	$db->query($query);
	$this->publik = FALSE;
}

function IsMyScore () {
	return $this->ismyscore;	
}

function ReturnMyScore () {
	return $this->myscore;	
}

function ReturnMyTitle () {
	return $this->title;	
}

function ReturnPublik () {
	return $this->publik;	
}

function GetScore ($user_id) {
	$db=new DB_Seminar;
	$db->query("SELECT score FROM user_info WHERE user_id = '$user_id'");
	$db->next_record();
	return $db->f("score");
}

function CheckScore ($user_id) {
	$db=new DB_Seminar;
	$db->query("SELECT score FROM user_info WHERE user_id = '$user_id' AND score > 0");
	if ($db->next_record())
		return $db->f("score");
	else 
		return FALSE;
}


function doRefreshScoreContentCache(){
	$db = new DB_Seminar("SELECT a.user_id,username FROM user_info a LEFT JOIN auth_user_md5 b USING (user_id) WHERE score > 0");
	while ($db->next_record()){
		$this->score_content_cache[$db->f('user_id')]['username'] = $db->f('username');
	}
	if (is_array( ($user_ids = array_keys($this->score_content_cache)) )){
		$id_list = "('" . join("','", $user_ids) . "')";
		$db->query("SELECT count(post_id) as guestcount,u.user_id FROM user_info u  LEFT JOIN guestbook ON(range_id=u.user_id) 
					WHERE u.user_id IN $id_list AND guestbook=1 GROUP BY u.user_id");
		while ($db->next_record()){
			$this->score_content_cache[$db->f('user_id')]['guestcount'] = $db->f('guestcount');
		}
		$db->query("SELECT count(news_id) as newscount,range_id FROM news_range WHERE range_id IN $id_list GROUP BY range_id");
		while ($db->next_record()){
			$this->score_content_cache[$db->f('range_id')]['newscount'] = $db->f('newscount');
		}
		$db->query("SELECT count(event_id) eventcount,range_id FROM calendar_events WHERE range_id IN $id_list AND class = 'PUBLIC' GROUP BY range_id");
		while ($db->next_record()){
			$this->score_content_cache[$db->f('range_id')]['eventcount'] = $db->f('eventcount');
		}
		$db->query("SELECT count(list_element_id) AS litcount, range_id FROM lit_list LEFT JOIN lit_list_content USING ( list_id )
					WHERE visibility = 1 AND range_id IN $id_list GROUP BY range_id");
		while ($db->next_record()){
			$this->score_content_cache[$db->f('range_id')]['litcount'] = $db->f('litcount');
		}
		if ($GLOBALS['VOTE_ENABLE']){
			$db->query("SELECT count(vote_id) AS votecount,range_id FROM vote WHERE range_id IN $id_list GROUP BY range_id");
			while ($db->next_record()){
				$this->score_content_cache[$db->f('range_id')]['votecount'] = $db->f('votecount');
			}
		}
	}
	return true;
}

function GetScoreContent($user_id) {
	if (!is_array($this->score_content_cache)){
		$this->doRefreshScoreContentCache();
	}
	$username = $this->score_content_cache[$user_id]['username'];
	if ( ($gaeste = $this->score_content_cache[$user_id]['guestcount']) !== null ) {
		if ($gaeste == 1) 
			$tmp = _("G�stebuch aktiviert mit 1 Eintrag");
		else 
			$tmp = _("G�stebuch aktiviert mit ". $gaeste ." Eintr�gen");
		$content .= "<a href=\"about.php?username=$username&guestbook=open#guest\"><img src=\"pictures/icon-guest.gif\" border=\"0\"".tooltip("$tmp")."></a>&nbsp;";
	} else {
		$content .= "<img src=\"pictures/blank.gif\" width=\"17\">";
	}
	
	if ( ($news = $this->score_content_cache[$user_id]['newscount']) ) {
		$content .= "<a href=\"about.php?username=$username\"><img src=\"pictures/icon-news.gif\" border=\"0\"".tooltip(_("$news pers�nliche News"))."></a>&nbsp;";
	} else {
		$content .= "<img src=\"pictures/blank.gif\" width=\"17\">";
	}
	if ( ($vote = $this->score_content_cache[$user_id]['votecount']) ) {
		if ($vote == 1)
			$tmp = _("Votes");
		else
			$tmp = _("Votes");
		$content .= "<a href=\"about.php?username=$username\"><img src=\"pictures/icon-vote.gif\" border=\"0\"".tooltip("$vote $tmp")."></a>&nbsp;";
	} else {
		$content .= "<img src=\"pictures/blank.gif\" width=\"17\">";
	}
	
	if ( ($termin = $this->score_content_cache[$user_id]['eventcount']) ) {
		if ($termin == 1)
			$tmp = _("Termin");
		else 
			$tmp = _("Termine");
		$content .= "<a href=\"about.php?username=$username#a\"><img src=\"pictures/icon-uhr.gif\" border=\"0\"".tooltip("$termin $tmp")."></a>&nbsp;";
	} else {
		$content .= "<img src=\"pictures/blank.gif\" width=\"17\">";
	}
	
	if ( ($lit = $this->score_content_cache[$user_id]['litcount']) ) {
		if ($lit == 1)
			$tmp = _("Literaturangabe");
		else 
			$tmp = _("Literaturangaben");
		$content .= "<a href=\"about.php?username=$username\"><img src=\"pictures/icon-lit.gif\" border=\"0\"".tooltip("$lit $tmp")."></a>&nbsp;";
	} else {
		$content .= "<img src=\"pictures/blank.gif\" width=\"17\">";
	}
	return $content;
}

/**
* Retrieves the titel for a given studip score
*
* @param		integer	a score value
* @param		integer	gender (0: male; 1:female)
* @return		string	the titel
*
*/
function gettitel($score, $gender=0) {

	if ($score)
		$logscore = floor(log10($score) / log10(2));
	else
		$logscore = 0;
		
	if ($logscore > 20)
		$logscore = 20;
		
	$titel[0]  =	array(0 => _("Unbeschriebenes Blatt"), 1 => _("Unbeschriebenes Blatt"));
	$titel[1]  =	array(0 => _("Unbeschriebenes Blatt"), 1 => _("Unbeschriebenes Blatt"));
	$titel[2]  =	array(0 => _("Unbeschriebenes Blatt"), 1 => _("Unbeschriebenes Blatt"));
	$titel[3]  =	array(0 => _("Neuling"), 1 => _("Neuling"));
	$titel[4]  =	array(0 => _("Greenhorn"), 1 => _("Greenhorn"));
	$titel[5]  =	array(0 => _("Anf&auml;nger"), 1 => _("Anf&auml;ngerin"));
	$titel[6]  =	array(0 => _("Einsteiger"), 1 => _("Einsteigerin"));
	$titel[7]  =	array(0 => _("Beginner"), 1 => _("Beginnerin"));
	$titel[8]  =	array(0 => _("Novize"), 1 => _("Novizin"));
	$titel[9]  =	array(0 => _("Fortgeschrittener"), 1 => _("Fortgeschrittene"));
	$titel[10] =	array(0 => _("Kenner"), 1 => _("Kennerin"));
	$titel[11] =	array(0 => _("K&ouml;nner"), 1 => _("K&ouml;nnerin"));
	$titel[12] =	array(0 => _("Profi"), 1 => _("Profi"));
	$titel[13] =	array(0 => _("Experte"), 1 => _("Expertin"));
	$titel[14] =	array(0 => _("Meister"), 1 => _("Meisterin"));
	$titel[15] =	array(0 => _("Gro&szlig;meister"), 1 => _("Gro&szlig;meisterin"));
	$titel[16] =	array(0 => _("Idol"), 1 => _("Idol"));
	$titel[17] =	array(0 => _("Guru"), 1 => _("Hohepriesterin"));
	$titel[18] =	array(0 => _("Lichtgestalt"), 1 => _("Lichtgestalt"));
	$titel[19] =	array(0 => _("Halbgott"), 1 => _("Halbg&ouml;ttin"));
	$titel[20] =	array(0 => _("Gott"), 1 => _("G&ouml;ttin"));

	return $titel[$logscore][$gender];
}

/**
* Retrieves the score for the current user
* 
* @return		integer	the score
*
*/
function GetMyScore() {
	global $user,$auth, $GLOBALS;

	$user_id=$user->id; //damit keiner schummelt...

	///// Werte holen...

	$db=new DB_Seminar;
	$db->query("SELECT count(*) as postings FROM px_topics WHERE user_id = '$user_id' ");
	$db->next_record();
	$postings=$db->f("postings");

	$db->query("SELECT count(*) as dokumente FROM dokumente WHERE user_id = '$user_id' ");
	$db->next_record();
	$dokumente=$db->f("dokumente");

	$db->query("SELECT count(*) as seminare FROM seminar_user WHERE user_id = '$user_id' ");
	$db->next_record();
	$seminare=$db->f("seminare");

	$db->query("SELECT count(*) as archiv FROM archiv_user WHERE user_id = '$user_id' ");
	$db->next_record();
	$archiv=$db->f("archiv");

	$db->query("SELECT count(*) as institut FROM user_inst WHERE user_id = '$user_id' ");
	$db->next_record();
	$institut=$db->f("institut");

	$db->query("SELECT count(*) as news FROM news WHERE user_id = '$user_id' ");
	$db->next_record();
	$news=$db->f("news");
	
	$db->query("SELECT count(post_id) as guestcount FROM guestbook WHERE range_id = '$user_id' ");
	$db->next_record();
	$gaeste = $db->f("guestcount");
	
	$db->query("SELECT count(contact_id) as contactcount FROM contact WHERE user_id = '$user_id' ");
	$db->next_record();
	$contact = $db->f("contactcount");
	
	$db->query("SELECT count(kategorie_id) as katcount FROM kategorien WHERE range_id = '$user_id' ");
	$db->next_record();
	$katcount = $db->f("katcount");

	$db->query("SELECT mkdate FROM user_info WHERE user_id = '$user_id' ");
	$db->next_record();
	$age = $db->f("mkdate");
	if ($age == 0) $age = 1011275740;
	$age = (time()-$age)/31536000;
	$age = 2 + log($age);
	if ($age <1 ) $age = 1;
		
	if ($GLOBALS['VOTE_ENABLE']) {
		$db->query("SELECT count(*) FROM vote WHERE range_id = '$user_id'");
		$db->next_record();
		$vote = $db->f(0)*2;
		
		$db->query("SELECT count(*) FROM vote_user WHERE user_id = '$user_id'");
		$db->next_record();
		$vote += $db->f(0);
		
		$db->query("SELECT count( DISTINCT (vote_id) )
					FROM voteanswers_user
					LEFT JOIN voteanswers USING ( answer_id )
					WHERE user_id = '$user_id'
					GROUP BY user_id");
		$db->next_record();
		$vote += $db->f(0);
						
		$db->query("SELECT count(*) FROM eval WHERE author_id = '$user_id'");
		$db->next_record();
		$vote += 2*$db->f(0);
	}
	
	if ($GLOBALS['WIKI_ENABLE']) {
		$db->query("SELECT count(*) FROM wiki WHERE user_id = '$user_id'");
		$db->next_record();
		$wiki = $db->f(0);	
	}
	
	$visits = object_return_views($user_id);
		
		


///////////////////////// Die HOCHGEHEIME Formel:

	$score = (5*$postings) + (5*$news) + (20*$dokumente) + (2*$institut) + (10*$archiv*$age) + (10*$contact) + (20*$katcount) + (5*$seminare) + (1*$gaeste) + (5*$vote) + (5*$wiki) + (3*$visits);
	$score = round($score/$age);
	if(file_exists("./user/".$user_id.".jpg"))
		$score *=10;
		
/// Schreiben des neuen Wertes

	$query = "UPDATE user_info "
		." SET score = '$score'"
		." WHERE user_id = '$user_id' AND score > 0";
	$db->query($query);
	return $score;
}

}


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
