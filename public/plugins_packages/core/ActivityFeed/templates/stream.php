<? $dateformat = '%d.%m.%Y';?>
<div class="stream-container">
    <? if(sizeof($stream) > 0) : ?>
        <? foreach($stream as $activity) : ?>
            <? if($x != strftime($dateformat, $activity->getMkdate())) :?>
                <span class="activity-day"><?=strftime($dateformat, $activity->getMkdate())?></span>
                <? $x = strftime($dateformat, $activity->getMkdate()); ?>
            <?endif;?>
            <?= $this->render_partial("_activity", array('_activity' => $activity)) ?>
        <? endforeach; ?>
    <? else :?>
        <?= MessageBox::info(_('Keine Aktivitäten gefunden.')) ?>
    <? endif; ?>
</div>