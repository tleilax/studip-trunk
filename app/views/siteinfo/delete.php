<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>
<div class="white" style="padding: 1ex;">
    <? if (!$execute): ?>
        <div style="text-align: center;padding: 10px;">
        <? if ($detail) :?>
            <p><?= _("Wollen Sie die Seite wirklich l�schen?") ?></p>
        <? else : ?>
            <p><?= _("Wollen Sie die Rubrik mit allen Seiten wirklich l�schen?") ?></p>
        <? endif  ?>
        <? $delete_url = 'siteinfo/delete/'.$currentrubric.'/';
           $delete_url .= $detail ? $currentdetail : "all";
           $delete_url .= "/execute";
           $abort_url = 'siteinfo/show/'.$currentrubric;
           $abort_url .= $detail ? "/".$currentdetail : '';
        ?>
        <?= LinkButton::create(_('L�schen'), $controller->url_for($delete_url)) ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for($abort_url)) ?>
    </div>
    <div>
        <hr>
    </div>
    <? endif ?>
    <?= $output ?>
</div>
