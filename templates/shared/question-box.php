<div class="modaloverlay">
    <div class="create-question-dialog ui-widget-content ui-dialog studip-confirmation">
        <div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">
            <span><?= _('Bitte bestätigen Sie die Aktion') ?></span>
            <a href="<?= URLHelper::getLink($disapprove_url, $disapprove_parameters) ?>" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close">
                <span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span>
                <span class="ui-button-text"><?= _('Schliessen') ?></span>
            </a>
        </div>
        <div class="content ui-widget-content ui-dialog-content studip-confirmation">
            <?= formatReady($question) ?>
        </div>
        <div class="buttons ui-widget-content ui-dialog-buttonpane">
            <div class="ui-dialog-buttonset">
            <? if ($method === 'GET'): ?>
                <?= Studip\LinkButton::createAccept(
                    _('Ja'),
                    URLHelper::getURL($approve_url, $approve_parameters)
                ) ?>
            <? else: ?>
                <form action="<?= URLHelper::getLink($approve_url) ?>" method="post">
                    <?= CSRFProtection::tokenTag() ?>
                <? foreach ($approve_parameters as $key => $value): ?>
                    <?= addHiddenFields($key, $value) ?>
                <? endforeach; ?>
                    <?= Studip\Button::createAccept(_('Ja'), 'yes') ?>
                </form>
            <? endif; ?>

                <?= Studip\LinkButton::createCancel(
                    _('Nein'),
                    URLHelper::getURL($disapprove_url, $disapprove_parameters)
                ) ?>
            </div>
        </div>
    </div>
</div>
