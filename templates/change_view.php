<?php
if ($_SESSION['seminar_change_view']['cid'] && $_SESSION['seminar_change_view']['cid'] == $GLOBALS['SessSemName'][1]) {
?>
<div class="messagebox messagebox_warning">
    <?= sprintf(_('Die Veranstaltung wird in der Ansicht f�r %s angezeigt. '.
        'Sie k�nnen die Ansicht %shier zur�cksetzen%s.'), 
        get_title_for_status($_SESSION['seminar_change_view']['perm'], 2), 
        '<a href="'.URLHelper::getLink('dispatch.php/course/change_view', 
        array('cid' => Request::option('cid'))).'">', '</a>'); ?>
</div>
<?php
}
?>
<!--<div class="clear"></div>-->