<?php
# Lifter001: DONE
# Lifter002: TODO
/**
 * Overview of all existing votes ... vote_overview.lib.php
 *
 * @author      Christian Bauer <alfredhitchcock@gmx.net>
 * @version     $Id$
 * @copyright   2003 Stud.IP-Project
 * @access      public
 * @module      vote_overview_lib
 * @package     vote
 * @modulegroup vote_modules
 */
include_once("lib/vote/view/vote_show.lib.php");

/**
 * displays the title bar
 *
 * @access  private
 */
function printSiteTitle(){
	//function deprecated, since no title_line is used at this time
}


/**
 * displays the 'Safeguard'
 *
 * @access  private
 * @param sign   string Sign to draw (must be "ok" or "ausruf")
 * @param text   string        The Text to draw
 * @param voteID string needed if you want to delete a vote (not needed)
 */
function printSafeguard($sign,$text,$mode = NULL, $voteID = NULL, $showrangeID = NULL, $referer = NULL){
	global $label;
/*	$html = "<table class=\"blank\" cellspacing=0 cellpadding=0 border=0 width=\"100%\">\n"
		  . " <tr>\n"
		  . "  <td class=\"blank\">&nbsp;\n"

		  . "	 <td width=\"34\" style=\"vertical-align:top;\">\n";
*/
	$html = "   <table align=\"center\" width=99% class=blank border=0 cellpadding=2 cellspacing=0>\n"
		  . "	<tr>\n"
	 	  . "	 <td width=\"34\" valign=\"middle\" style=\"vertical-align:middle;\">\n";//style=\"vertical-align:top;\"
	$color = "";
	if ($sign != ""){
		$html .="	  <img src=\"".VOTE_PATH_PICTURES."$sign.gif\" alt=\"$sign.gif\" =\"middle\">\n";
		if ($sign == "ausruf")
			$color = VOTE_COLOR_ERROR;
		elseif ($sign == "ok")
			$color = VOTE_COLOR_SUCCESS;
	}
	$html .="	 </td>\n";
	$html .="	 <td align=\"left\" valign=\"middle\" style=\"vertical-align:middle;\">\n";
	$html .="	  <font size=\"-1\" color=\"$color\"><br>$text</font><br><br>\n";

	if ($referer)
		$linkreferer = "&referer=".$referer;

	if (($mode == "delete_request") || ($mode == "NeverResultvisibility")){
		if ($mode == "delete_request"){
			$value1 = "delete_confirmed";
			$value2 = "delete_aborted";
		}
		else{
			$value1 = "setResultvisibility_confirmed";
			$value2 = "setResultvisibility_aborted";
		}
		$html .="<font size=\"-1\"><a href=\"".URLHelper::getLink(VOTE_FILE_ADMIN."?page=overview&voteaction=".$value1."&voteID=".$voteID."&showrangeID=".$showrangeID.($referer ? "&referer=".$referer : ""));
		$html .="\" title=\"".$label["yes"]."\">" . makeButton('ja','img',$label["yes"]) . "</a></font>\n";
		$html .="<font size=\"-1\"><a href=\"".URLHelper::getLink(VOTE_FILE_ADMIN."?page=overview&voteaction=".$value2."&voteID=".$voteID."&showrangeID=".$showrangeID.($referer ? "&referer=".$referer : ""));
		$html .="\" title=\"".$label["no"]."\">" . makeButton('nein','img',$label["no"]) . "</a></font>\n";
	}
	$html .="	 </td>\n"
		  . "	</tr>\n";
	if ($referer)
		$html .= "	 <tr><td>&nbsp;</td><td><font size=\"-1\"><a href=\"".URLHelper::getLink($referer)."\">".$label["referer"]."</a></font></td></tr>";
	$html .="   </table>\n";
		/*  . "  </td>\n"
		  . " </tr>\n"
		  . "</table>\n";*/
	return $html;
}

function printSearchResults($rangeAR,$searchString){
	global $label,$typen;

$cssSw = new cssClassSwitcher;									// Klasse f�r Zebra-Design
$cssSw->enableHover();
$html = "\n" . $cssSw->GetHoverJSFunction() . "\n";

	$html.= "<table class=\"blank\" cellspacing=0 cellpadding=0 border=0 width=\"100%\">\n"
		  . " <tr>\n"
		  . "  <td class=blank>\n"
		  . "   <table align=\"center\" width=99% class=blank border=0 cellpadding=2 cellspacing=0>\n"
		  . "	<tr>\n"
		  . "	 <td colspan=\"9\" align=\"left\" valign=\"top\" class=\"blank\">\n"
		  . "	  <br><font size=\"2\"><b>".$label["searchresults_title"]." <".$searchString.">:</b>\n"
		  . "	 </td>\n";

	if ((empty($rangeAR)) || ($searchString == NULL	)){
		$html .="	</tr>\n"
			  . "	<tr ".$cssSw->getHover().">\n";
		if ($searchString == NULL){
			$html .="	 <td class=\"steel1kante\">\n"
				  . "	  <br><font size=\"-1\">\n"
				  . $label["searchresults_no_string"]."<br><br>\n";
		}
		else{
			$html .="	 <td class=\"steel1kante\">\n"
				  . "	  <br><font size=\"-1\">\n"
				  . $label["searchresults_no_results"]."<br><br>\n";
		}
		$html .="	</font>\n"
			  . "	 </td>\n"
			  . "	</tr>\n"
			  . "   </table>\n"
			  . "  </font></td>\n"
			  . " </tr>\n"
			  . "</table>\n";
		echo $html;
		return;
	}

	foreach ($rangeAR as $k => $v) {
		while (list($typen_key,$typen_value)=each ($typen)) {
        	if ($v["type"]==$typen_key){
				//$html.= "\$type: ".$v["type"]." || ID=$k -> Name=".$v["name"]."\n";
				$ranges["$typen_key"][]=array("id" =>$k,"name"=>$v["name"]);
				$ranges2["$typen_key"][]=array($k,$v["name"]);
				}
		}
		reset($typen);
    }
	reset($typen);
	while(list($typen_key,$typen_value)=each ($typen)){
		$counter = 0;
		$html .="	 <tr><td class=\"steel\" style=\"vertical-align:bottom;\" align=\"left\" colspan=\"4\" height=\"26\"><font size=\"-1\"><b>$typen_value:</b></font></td></tr>\n";
		if ($ranges["$typen_key"]){
			foreach ($ranges["$typen_key"] as $range) {
	  			if ($counter == 0)			$displayclass = "steel1kante";
	  			elseif (($counter % 2) == 0)	$displayclass = "steel1";
	  			else							$displayclass = "steelgraulight";
				$html .="	<tr ".$cssSw->getHover().">"
					  . "<td class=\"".$cssSw->getClass()."\"><font size=\"-1\">".htmlReady($range["name"])."</td>"
					  . "<td class=\"".$cssSw->getClass()."\"><font size=\"-1\"><a href=\"".URLHelper::getLink(VOTE_FILE_ADMIN."?page=edit&rangeID=".$range["id"]."&type=vote&showrangeID=".$range["id"])."\" alt=\"Voting erstellen.\">Voting erstellen</a></font></td>"
					  . "<td class=\"".$cssSw->getClass()."\"><font size=\"-1\"><a href=\"".URLHelper::getLink(VOTE_FILE_ADMIN."?page=edit&rangeID=".$range["id"]."&type=test&showrangeID=".$range["id"])."\" alt=\"Voting erstellen.\">Test erstellen</a></font></td>"
					  . "<td class=\"".$cssSw->getClass()."\"><font size=\"-1\"><a href=\"".URLHelper::getLink(VOTE_FILE_ADMIN."?page=overview&showrangeID=".$range["id"])."\" alt=\"Diesen Bereich anzeigen.\">Bereich Anzeigen</a></font></td>"
					  . "	</tr>\n";
			$counter++;
			$cssSw->switchClass();
			}
		}
		else{
				$html .="	<tr>"
					  . "<td class=\"steel1kante\" colspan=\"4\"><font size=\"-1\">".$label["searchresults_no_results_range"]."</font></td>"
					  . "	</tr>\n";
		}
		reset($ranges);
	}

	$html .="	 </td>\n"
		  . "	</tr>\n"
		  . "   </table>\n"
		  . "  </td>\n"
		  . " </tr>\n"
		  . "</table>\n";
	echo $html;
}
/**
 * displays the options to create new votes and tests and the display filter
 *
 * @access  private
 * @param $range	array An array with alle accessable rangeIDs [0] and the titles [1]
 * @param $sarchRange	string The ID of the range to display
 */
function printSelections($range,$sarchRange = "",$safeguard = NULL){
	global $rangemode,$label,$showrangeID;
	$arraysize = count($range);

	$bgimage = "	 <td class=\"blank\" width=\"270\" rowspan=\"4\" align=\"center\" valign=\"top\" style=\"vertical-align:top;\">"
	 	 . "	  <img src=\"".VOTE_PATH_PICTURES."/voting.jpg\" alt=\"".$label["sitetitle_title"]."\" align=\"middle\" border=\"0\">\n"
		 . "	 </td>\n";

	$html = "<table border=\"0\" class=\"blank\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">\n"
		  . " <tr valign=\"top\">\n"
		  . "  <td width=\"99%\" NOWRAP class=\"blank\"><br>"
	  	  . "   <table align=\"center\" width=\"99%\" class=\"blank\" border=\"0\" cellpadding=\"0\" cellspacing=0>\n"
		  . "	  "
		  . "	<tr><td>".$safeguard."</td>".$bgimage."</tr><tr>\n";

	// create new vote/test
$html .= makeNewVoteSelectForm(VOTE_FILE_ADMIN."?page=edit");


	// background-image

	$html.="	</tr>\n"
		 . "	<tr>\n";

	if (($rangemode == "root" ) || ($rangemode == "admin") || ($rangemode == "dozent"))
		$html .= makeDisplaySelectForm(VOTE_FILE_ADMIN."?page=overview");
	else
		$html.="	<td class=\"blank\" style=\"vertical-align:middle;\" nowrap></td>\n";
	$html.="	</tr>\n"
		 . "	<tr>\n";
	if (($rangemode == "root" ) || ($rangemode == "admin"))
		$html .= makeSearchForm($searchRange);
	else $html.="	<td class=\"blank\" style=\"vertical-align:middle;\" nowrap></td>\n";
	$html.="	</tr>\n"
		 . "</table></td></tr></table>\n";
	echo $html;
}
/**
 * displays the Votes in a table
 *
 * @access private
 * @param mode                  string        could be 'new', 'active', 'stopped' or 'start_table', 'end_table'
 * @param votes                 array        an array with all the data (optional)
 * @param openID                string        display the results of this voteID (optional)
 */
function printVoteTable($mode, $votes = NULL, $openID = NULL){
	global $rangemode, $label, $showrangeID;

	$fontstart 	= "<font size=\"-1\">";
	$fontend	= "</font>";
	// label variables depending on mode
	switch ($mode){
	 case VOTE_STATE_NEW:
	  $table_title = $label["table_title_new"];
	  $icon = "gelb";
	  $specific_status = $label["startdate"];
	  $status_value = "start";
	  $status_button = $label["status_button_new"];
	  $status_tooltip = $label["status_tooltip_new"];
	  $no_votes_message = $label["no_votes_message_new"];
	  break;
	 case VOTE_STATE_ACTIVE:
	  $table_title = $label["table_title_active"];
	  $icon = "gruen";
	  $specific_status = $label["enddate"];
	  $status_value = "stop";
	  $status_button = $label["status_button_active"];
	  $status_tooltip = $label["status_tooltip_active"];
	  $no_votes_message = $label["no_votes_message_active"];
	  break;
	 case VOTE_STATE_STOPPED:
	  $table_title = $label["table_title_stopped"];
	  $icon = "rot";
	  $specific_status = $label["visibility"];
	  $status_value = "continue";
	  $status_button = $label["status_button_stopped"];
	  $status_tooltip = $label["status_tooltip_stopped"];
	  $no_votes_message = $label["no_votes_message_stopped"];
	  break;
	 case "start_table":
	  print "<table class=\"blank\" cellspacing=0 cellpadding=2 border=0 width=\"100%\">\n";
	  return;
	  break;
	 case "end_table":
	  print "</div></table>";
	  return;
	 case "printTitle":
	  $html= " <tr>\n"
	  	   . "  <td class=blank>\n"
	  	   . "   <table align=\"center\" width=99% class=\"blank\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">\n"
	  	   . "	<tr>\n"
	  	   . "	 <td colspan=\"9\" align=\"left\" valign=\"top\" class=\"blank\" style=\"vertical-align:middle;\">\n"
	  	   . "	  <font size=\"2\" style=\"vertical-align:middle;\"><br><b>".$label["table_title"]." \"".htmlReady($votes)."\":</b></font>\n"
	  	   . "	 </td>\n"
	  	   . "   </tr>\n"
	  	   . "   </table>\n"
	  	   . "  </td>\n"
	  	   . " </tr>\n";
	   echo $html;
	  return;
	  break;
	 default:
	  break;
	}

// Display votes/tests
	$html = " <tr>\n"
		  . "  <td class=\"blank\">\n"
		  . "   <table align=\"center\" width=99% class=blank border=0 cellpadding=2 cellspacing=0>\n"
		  . "	<tr>\n"
		  . "	 <td colspan=\"9\" align=\"left\" valign=\"top\" ><font size=\"-7\"><br></font> \n"
		  . "	  <font size=\"-1\">".$table_title."</font>\n";
//** displays the header-cells **
// open/close all votes-Arrows
	if (($mode != VOTE_STATE_NEW)&& (!empty($votes)) &&
		($openID == ("openallactive" || "openallstopped")))
			$html .="	   <a name=\"openvote\"></a>";

	$html .="	 </td>\n"
		  . "	</tr>\n"
		  . "	<tr>\n"

		  . makeTableHeaderCell("blindgif","10")
		  . makeTableHeaderCell("blindgif","18")
		  . makeTableHeaderCell($label["title"],"","left");
		  if (($rangemode == "root" ) || ($rangemode == "admin") || ($rangemode == "dozent"))
		   	$html .= makeTableHeaderCell($label["user"],"150");
		  else
		  	$html .= makeTableHeaderCell($label["range"],"150");

	$html .=makeTableHeaderCell($specific_status,"93")
		  . makeTableHeaderCell($label["status"],"93")
		  . makeTableHeaderCell("blindgif","93");

	// insert 'edit'-caption or 'makecopy'-caption
	if($mode == VOTE_STATE_NEW or $mode == VOTE_STATE_ACTIVE)	$html.= makeTableHeaderCell($label["edit"]);
	else									$html.= makeTableHeaderCell("blindgif","93");

	// insert 'delete'-caption
	$html.=   makeTableHeaderCell($label["delete"],"93")
		 . "	</tr>\n";
//** END of displays the header-cells **

//** displays the data-cells **

	// if there are any votes ... display them
	if (!empty($votes)){
	 $counter = 0;
	 $arraysize = count($votes);

	 // print tablerows until all votedata is plotted
	 while($counter < $arraysize){
	  	if ($counter == 0)				$displayclass = "steel1kante";
	  	elseif (($counter % 2) == 0)	$displayclass = "steel1";
	  	else							$displayclass = "steelgraulight";

	  $html.="	<tr>\n";
	//	   . "	 <td class=$displayclass width=\"10\" align=\"center\">\n";
// displays arrow (a opened one)
	 if(($openID == $votes[$counter]["voteID"]) ||
	 	(($mode == "active") && ($openID == "openallactive")) ||
		(($mode == "stopped") && ($openID == "openallstopped"))
		){
	  	if($openID == $votes[$counter]["voteID"])
			$html.="	  <a name=\"openvote\"></a>";
		$html.=  makeArrow ($votes[$counter]["changedate"],"open",$displayclass,$mode);
	  }
// displays arrow (a closed one)
	  else {
		$html.=  makeArrow ($votes[$counter]["changedate"],"closed",$displayclass,$mode,$votes[$counter]["voteID"]);
	  }

// displays the vote/test-icon
	  $html.="	 "//</td>\n"
		   . "	 <td class=$displayclass width=\"18\" align=\"center\">\n"
		   . "	  <img src=\"";
	  ($votes[$counter]["type"] == INSTANCEOF_VOTE) ? $html.= VOTE_ICON_VOTE : $html.= VOTE_ICON_TEST;
	  $html.="\" align=\"middle\" width=\"18\" alt=\"".INSTANCEOF_VOTE."\">\n"//style=\"vertical-align:middle;\"
		   . "	 </td>\n";

	  $html.="	 <td class=$displayclass width=\"\" align=\"left\">\n";
// displays titel (a closed one)
	  if($openID == $votes[$counter]["voteID"])
		$html.="	  <a href=\"".URLHelper::getLink(VOTE_FILE_ADMIN."?page=overview&showrangeID=".$showrangeID)."\" alt=\"Zuklappen\" title=\"Zuklappen\" name=\"open\">";
// displays titel (a opened one)
	  else
		$html.="	  <a href=\"".URLHelper::getLink(VOTE_FILE_ADMIN."?page=overview&openID=".$votes[$counter]["voteID"]."&showrangeID=".$showrangeID."#openvote")."\" alt=\"Aufklappen\" title=\"Aufklappen\">";
	  $html.="	  ".$fontstart.$votes[$counter]["title"].$fontend."</a>\n"
		   . "	 </td>\n";

// displays rangename
	  if (($rangemode == "root" ) || ($rangemode == "admin") || ($rangemode == "dozent"))
	  	$html.= makeTableDataCellLink($votes[$counter]["username"], $votes[$counter]["rangetitle"],$displayclass,"center","150");
	  else
	    $html.= makeTableDataCell($votes[$counter]["rangetitle"],$displayclass,"center","150");

// displays the start/end-date
	  if($mode == VOTE_STATE_NEW or $mode == VOTE_STATE_ACTIVE)
		$html.= makeTableDataCell($votes[$counter]["secial_data"],$displayclass,"center","93");
// displays the visible-status
	  else{
	   if ($votes[$counter]["secial_data"] == "invisible") {
		$visibility_alt = $label["visibility_alt"]["invis"];
		$visibility_tooltip = $label["visibility_tooltip"]["invis"];}
	   else {
		$visibility_alt = $label["visibility_alt"]["vis"];
		$visibility_tooltip = $label["visibility_tooltip"]["vis"];}

	   $html.= makeTableDataCellForm($displayclass, "overview",
			$votes[$counter]["secial_data"], $visibility_tooltip,
			"voteID", $votes[$counter]["voteID"],
			"voteaction", "change_visibility",
			"showrangeID", $showrangeID);
	 }

// insert 'start/end/continue'-button
	 $html.= makeTableDataCellForm($displayclass, "overview",
			$status_button, $status_tooltip,
			"voteID", $votes[$counter]["voteID"],
			"voteaction", $status_value,
			"showrangeID", $showrangeID);
// insert 'restart'-button
	 if($mode == "active" or $mode == "stopped")
	    $html.= makeTableDataCellForm($displayclass, "overview",
			$label["restart_button"], $label["restart_tooltip"],
			"voteID", $votes[$counter]["voteID"],
			"voteaction", "restart",
			"showrangeID", $showrangeID);
	 else
	    $html.= makeTableDataCell("blindgif",$displayclass,"center","93");

// insert 'edit'-button
	 if($mode == VOTE_STATE_NEW or $mode == VOTE_STATE_ACTIVE)
	 	$html.= makeTableDataCellForm($displayclass, "edit",
			$label["edit_button"], $label["edit_tooltip"],
			"voteID", $votes[$counter]["voteID"],
			"showrangeID", $showrangeID,
			"type", $votes[$counter]["type"]);
// insert 'makecopy'-button
	 else
	 	$html.= makeTableDataCellForm($displayclass, "edit",
			$label["makecopy_button"], $label["makecopy_tooltip"],
			"voteID", $votes[$counter]["voteID"],
			"makecopy", "1",
			"showrangeID", $showrangeID,
			"type", $votes[$counter]["type"]);
// insert 'delete'-button
	 $html.= makeTableDataCellForm($displayclass, "overview",
			$label["delete_button"], $label["delete_tooltip"],
			"voteID", $votes[$counter]["voteID"],
			"voteaction", "delete_request",
			"showrangeID", $showrangeID);
	 $html.="	</tr>\n";
//** END of displays the data-cells **

//** displays the data-cells with the vote/test-result**

	 // a new row, if there is a vote/test-result to display
	 if(($openID == $votes[$counter]["voteID"]) ||
	 	(($mode == "active") && ($openID == "openallactive")) ||
		(($mode == "stopped") && ($openID == "openallstopped"))
		){
		if ($counter == 0)			$displayclass = "steel1";
		if ($votes[$counter]["type"] == INSTANCEOF_VOTE)
		    $vote = &new Vote($votes[$counter]["voteID"]);
		else
			$vote = &new TestVote($votes[$counter]["voteID"]);
	    $html.="	<tr>\n"
	       . makeTableDataCell("&nbsp;",$displayclass,"center","10")
	       . makeTableDataCell("&nbsp;",$displayclass,"center","18")
	       . makeTableDataCell(createVoteResult($vote,"YES").createVoteInfo($vote,
		   		$votes[$counter]["isAssociated"]),$displayclass,"left","","6")
	       . makeTableDataCell("blindgif",$displayclass,"center","93")
	       . "	</tr>\n";
	 }
//** END of displays the data-cells with the vote/test-result**

	 $counter++;
     } // END while($counter < $arraysize)
reset($votes);
// open/close all
	if (($mode != VOTE_STATE_NEW) && (!empty($votes))){
	    $html .="	<tr>\n";
		if (($counter % 2) == 0)	$html .="	 <td class=\"steel1kante\" colspan=\"9\">\n";
	    else						$html .="	 <td class=\"steelkante\" colspan=\"9\">\n";
	    $html .="	 <center>\n";
		if (($mode == VOTE_STATE_ACTIVE) && ($openID == ("openallactive")))
			$html .="	  <a href=\"".URLHelper::getLink("?showrangeID=$showrangeID")."\"><img src=\"".VOTE_PATH_PICTURES."forumgraurauf.gif\" alt=\"".$label["arrow_close_all"]."\" title=\"".$label["arrow_close_all"]."\" border=0></a> \n";
		elseif (($mode == VOTE_STATE_STOPPED) && ($openID == ("openallstopped")))
			$html .="	  <a href=\"".URLHelper::getLink("?showrangeID=$showrangeID")."\"><img src=\"".VOTE_PATH_PICTURES."forumgraurauf.gif\" alt=\"".$label["arrow_close_all"]."\" title=\"".$label["arrow_close_all"]."\" border=0></a> \n";
		else
			$html .="	  <a href=\"".URLHelper::getLink("?showrangeID=$showrangeID&openID=openall".$mode."#openvote")."\"><img src=\"".VOTE_PATH_PICTURES."forumgraurunt.gif\" alt=\"".$label["arrow_open_all"]."\" title=\"".$label["arrow_open_all"]."\" border=0></a> \n";
		$html .="	 </center></td>\n"
	    	  . "	</tr>\n";
	}

      $html.="   </table>\n"
	 . "  </td>\n"
	 . " </tr>\n";
   }// END if (!empty($votes))

//** displays empty data-cells **
   else {
      $html .= "	<tr>\n"
	 . makeTableDataCell("blindgif","steel1kante","center","10","1")
	 . makeTableDataCell("blindgif","steel1kante","center","18","1")
	 . makeTableDataCell($fontstart.$no_votes_message.$fontend,"steel1kante","left","","1")
	 . makeTableDataCell("blindgif","steel1kante","center","120")
	 . makeTableDataCell("blindgif","steel1kante","center","93")
	 . makeTableDataCell("blindgif","steel1kante","center","93")
	 . makeTableDataCell("blindgif","steel1kante","center","93")
	 . makeTableDataCell("blindgif","steel1kante","center","93")
	 . makeTableDataCell("blindgif","steel1kante","center","93")
	 . "	</tr>\n"
	 . "   </table>\n"
	 . "  </td>\n"
	 . " </tr>\n";
	}
//** END of displays empty data-cells **

   echo $html;
}
// END OF function printVoteTable
/* ************************************************************************** *
/*																			  *
/*  private functions														  *
/*																			  *
/* ************************************************************************* */

/*
 * makes a <th>...</th> line
 *
 * @access private
 * @param text          string        The Text to display or 'blindgif' (optional)
 * @param width         string        witdth (optional)
 * @param align         string        align (optional)
 * @param $colspan      string        colspan (optional)
 * @return string       a string with a table-head
 */

function makeTableHeaderCell($text = "&nbsp;", $width = "5%", $align = "center", $colspan = "1"){
   if ($text == "blindgif") $text = "<img width=\"$width\" align=\"middle\" height=\"1\" src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" alt=\"\">";
   $html = "	 <td class=\"steel\" style=\"vertical-align:bottom;\" colspan=\"$colspan\" align=\"$align\" width=\"$width\" height=\"26\">\n"
      . "	  <font size=-1><b>$text</b></font>\n"
      . "	 </td>\n";
   return $html;
}
/**
 * makes a <td>...</t> line
 * @access private
 * @param text          string        The Text to display or 'blindgif' (optional)
 * @param class         string        class (optional)
 * @param align         string        align (optional)
 * @param width         string        width (optional)
 * @return string a string with a table-head
*/
function makeTableDataCell($text = "&nbsp;", $class = "steel1", $align = "center", $width = "5%", $colspan = "1"){
	if ($text == "blindgif") $text = "<img width=\"$width\" height=\"1\" src=\"".VOTE_PATH_PICTURES."blank.gif\" alt=\"\">";
	$html = "	 <td class=\"$class\" align=\"$align\" width=\"$width\" colspan=\"$colspan\">\n"
		  . "	  <font size=\"-1\">$text</font>\n"
		  . "	 </td>\n";
	return $html;
}

/**
 * makes a <td>...</t> line mit a link
 * @access private
 * @param text          string        The Text to display or 'blindgif' (optional)
 * @param class         string        class (optional)
 * @param align         string        align (optional)
 * @param width         string        width (optional)
 * @return string a string with a table-head
*/
function makeTableDataCellLink ($username, $text = "&nbsp;",
				$class = "steel1", $align = "center",
				$width = "5%", $colspan = "1") {
   $link = "{$CANONICAL_RELATIVE_PATH_STUDIP}about.php?username=".$username;
   $html = "	 <td class=\"$class\" align=\"$align\" width=\"$width\" colspan=\"$colspan\">\n"
      . "	  <font size=\"-1\"><a href=\"".URLHelper::getLink($link)."\" title=\"".$text."\">$text</a></font>\n"
      . "	 </td>\n";
   return $html;
}

/**
 *
 * makes a <td>...</td>  with a form
 *
 * @access private
 *
 * @param class                         string        class (optional)
 * @param align                         string        align (optional)
 * @param style                         string        style (optional)
 * @param action                        string        action of the form (optional)
 * @param button_name name      string        of the button (optional)
 * @param button_tooltip        string        tooptip for the button (optional)
 * @param hidden1_name          string        name of hidden button1 (optinal)
 * @param hidden1_value         string        value of hidden button1 (optinal)
 * @param hidden2_name          string        name of hidden button2 (optinal)
 * @param hidden2_value         string        value of hidden button2 (optinal)
 * @param hidden3_name          string        name of hidden button3 (optinal)
 * @param hidden3_value         string        value of hidden button3 (optinal)
 * @return string a string with a table-data-cell and a form
 */
function makeTableDataCellForm( $displayclass = "steel1",
								$action = "overview",
								$button_name = "ok",
								$button_tooltip = "Tooltip",
								$hidden1_name = NULL,
								$hidden1_value= NULL,
								$hidden2_name = NULL,
								$hidden2_value= NULL,
								$hidden3_name = NULL,
								$hidden3_value= NULL,
								$hidden4_name = NULL,
								$hidden4_value= NULL){
	$link =	VOTE_FILE_ADMIN."?page=".$action;
	if (!empty($hidden1_name)) $link .="&".$hidden1_name."=".$hidden1_value;
	if (!empty($hidden2_name)) $link .="&".$hidden2_name."=".$hidden2_value;
	if (!empty($hidden3_name)) $link .="&".$hidden3_name."=".$hidden3_value;
	if (!empty($hidden4_name)) $link .="&".$hidden4_name."=".$hidden4_value;

	if ($hidden2_value != "change_visibility"){
		$button = makeButton($button_name, 'img', decodeHTML($button_tooltip));
	}
	else{
		$button = "<img src=\"{$GLOBALS['ASSETS_URL']}images/vote-icon-$button_name.gif\" width=\"18\" alt=\"".$button_name."\" title=\"".$button_tooltip."\" border=\"0\" align=\"middle\">";
	}

	$html.="	 <td class=$displayclass width=\"93\" align=\"center\" style=\"vertical-align:middle;\">\n"
		 . "	  <font size=\"-1\"><a href=\"".URLHelper::getLink($link)."\">$button</a></font>\n"
		 . "	 </td>\n";
	return $html;
}





/**
 * makes a makeSelectForm
 * @access private
 * @param
 * @param
 * @param
 * @param
 * @return
*/
function makeNewVoteSelectForm($action){
	global $rangemode, $label,$range, $showrangeID;
	$arraysize = count($range);
	$html = "	 <td class=\"steel1\" style=\"vertical-align:middle;\" nowrap>\n"
		  . "	  <form action=\"".URLHelper::getLink($action)."\" method=post><br>&nbsp;\n"

		  // vote/test selection
		  . "	  <select name=\"type\" style=\"vertical-align:middle;\">"
		  . "	   <option value=\"".INSTANCEOF_VOTE."\" selected>".$label["selections_text_vote"]."</option>\n"
		  . "	   <option value=\"".INSTANCEOF_TEST."\">".$label["selections_text_test"]."</option>\n"
		  . "	  </select>";
	 // Auswahlliste erstellen

	if ($rangemode != "autor"){
		$html .="<font size=\"-1\"> ".$label["selections_text_middle"]." </font>";
		$html .="	   <select name=\"rangeID\" style=\"vertical-align:middle;\">\n";
		if($hidden1_name == "all_ranges")
			$html .="	   <option value=\"$hidden1_name\" selected>$hidden1_value</option>\n";
		// create select entries
		$counter = 0;
		while($counter < $arraysize){
			$html .="	   <option value=\"".$range[$counter][0]."\" ";
			// select current range
			if($showrangeID == $range[$counter][0])
				$html .= " selected";
			$html .=	   ">".htmlReady(my_substr ($range[$counter][1], 0, 40))."</option>\n";
			$counter++;
		}
		$html .="	   </select>\n";
	}
	else{
		$html .= "<font size=\"-1\">".$range[0][1]."</font>\n"
			  . "	   <input type=\"hidden\" name=\"rangeID\" value=\"".$range[0][0]."\">\n";
	}

	$html .="	   <input type=image name=new style=\"vertical-align:middle;\""
		  . 	    makeButton($label["selections_button"],"src") ." alt=\"".$label["selections_tooltip"]."\" title=\"".$label["selections_tooltip"]."\" border=0>\n"
		  . "	  <br>&nbsp;</form>\n"
		  . "	</td>\n";
	reset($range);
	return $html;
}

/**
 * makes a makeSelectForm
 * @access private
 * @param
 * @param
 * @param
 * @param
 * @return
*/
function makeDisplaySelectForm($action){
	global $rangemode, $label,  $range, $showrangeID;
	$arraysize = count($range);
	$html .="	  <td class=\"steelkante\" style=\"vertical-align:middle;\" nowrap>\n"
		  . "		<form action=\"".URLHelper::getLink($action)."\" method=post><font size=\"-1\"><br>&nbsp;\n"
		  . "	   ".$label["selections_selectrange_text"]."\n"
	 // Auswahlliste erstellen
		  . "	   <select name=\"showrangeID\" style=\"vertical-align:middle;\">\n";
//	if($hidden1_name == "all_ranges")
//		$html .="	   <option value=\"$hidden1_name\" selected>$hidden1_value</option>\n";
		// create select entries
	$counter = 0;
	while($counter < $arraysize){
		$html .="	   <option value=\"".$range[$counter][0]."\" ";
		// select current range
		if($showrangeID == $range[$counter][0]){
			$html .= " selected";
		}
		$html .=	   ">".htmlReady(my_substr ($range[$counter][1],0, 40))."</option>\n";

		$counter++;
	}
	$html .="	   </select>\n";

	$html .="	   <input type=image name=new style=\"vertical-align:middle;\" border=\"0\" "
		  . 	    makeButton($label["selections_selectrange_button"],"src") . " title=\"".$label["selections_selectrange_tooltip"]."\" alt=\"".$label["selections_selectrange_tooltip"]."\">\n"
		  . "	   <br></font></form>\n"
		  . "	  </td>\n";
	reset($range);
	return $html;
}

/**
 * makes a makeSelectForm
 * @access private
 * @return
*/
function makeSearchForm(){
	global $label, $searchRange;
	$html .="	  <td class=\"steelgraulight\" style=\"vertical-align:middle;\" nowrap>\n"
		  . "		<form action=\"".URLHelper::getLink($action)."\" method=post><font size=\"-1\" style=\"vertical-align:middle;\"><br>&nbsp;\n"
		  . "	     ".$label["search_text"]."\n"
		  . "	     <input type=\"text\" name=\"searchRange\"  value=\"$searchRange\" size=\"30\" style=\"vertical-align:middle;\">"
		  . "	     <input type=\"hidden\" name=\"voteaction\" value=\"search\">"
		  . "	     <input type=\"image\" style=\"vertical-align:middle;\" border=\"0\" "
		  .			makeButton($label["search_button"],"src") . " title=\"".$label["search_tooltip"]."\" alt=\"".$label["search_tooltip"]."\">\n"
		  . "	  <br>&nbsp;</font></form>\n"
		  . "	  </td>\n";
	return $html;
}

/**
 * makes a makeSelectForm
 * @access private
 * @return
*/
function makeArrow($timestmp,$open,$displayclass,$mode,$voteID = NULL){
	global $label, $showrangeID;

	switch ($mode){
	 case "new":
			$icon = "gelb";
		break;
	 case "active":
			$icon = "gruen";
		break;
	 case "stopped":
			$icon = "rot";
		break;
	}
	if ($open == "open")
		$icon .= "runt";

	$html = "	 <td class=\"".$displayclass."\" nowrap width=\"10\">\n";

	$oclink = VOTE_FILE_ADMIN."?page=overview&showrangeID=$showrangeID";
	if ($open == "closed")
		$html.= "         <a href=\"".URLHelper::getLink($oclink."&openID=".$voteID."#openvote")
			 .  "\" title=\"".$label["arrow_openthis"]."\">\n";
	else
		$html.= "         <a href=\"".URLHelper::getLink($oclink)."\" title=\"".$label["arrow_closethis"]."\">\n";


	$html.= "	  <img src=\"".$GLOBALS['ASSETS_URL']."images/forum".$icon.".gif\" border=\"0\" align=\"middle\" alt=\"";//style=\"vertical-align:middle;\"
	if ($open == "closed")
		$html.= $label["arrow_openthis"];
	else
		$html.= $label["arrow_closethis"];
	$html.= "\"></a>\n"
		 .  "	 </td>\n";
	return $html;
}
?>
