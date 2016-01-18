<div class="stream-container">
    <? if(sizeof($stream) > 0) : ?>
        <!-- Do we need an UI element representing every new day? -->
        <?= $this->render_partial_collection("_activity", $stream) ?>
    <? else :?>
        <?= MessageBox::info(_('Keine Aktivitäten gefunden.')) ?>
    <? endif; ?>

</div>