<?php
require_once 'lib/classes/StudygroupAvatar.class.php';
require_once 'lib/classes/Avatar.class.php';

$infobox = array();
$infobox['picture'] = 'infobox/studygroup.jpg';
$infobox['content'] = array(
    array(
        'kategorie'=>_("Information"),
        'eintrag'=>array(
            array("text"=>_("Studiengruppen sind eine einfache M�glichkeit, mit Kommilitonen, Kollegen und anderen zusammenzuarbeiten. Jeder kann Studiengruppen gr�nden. Auf dieser Seite finden Sie eine Liste aller Studiengruppen. Klicken Sie auf die �berschriften, um die jeweiligen Spalten zu sortieren."),"icon"=>"ausruf_small2.gif")
        )
    )
);
$sort_url =$controller->url_for('studygroup/browse/1/');
$link = "dispatch.php/studygroup/browse/%s/".$sort;
?>
<?=$this->render_partial("studygroup/_overview", array('sort_url' => $sort_url, 'link' => $link))?>

