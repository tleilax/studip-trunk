<div class="stream-container">
    <? if(sizeof($stream) > 0) : ?>
        <?= $this->render_partial_collection("_activity", $stream) ?>
    <? else :?>
        <?= MessageBox::info(_('Keine Aktivitäten gefunden.')) ?>
    <? endif; ?>

</div>