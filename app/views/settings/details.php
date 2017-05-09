<? use Studip\Button; ?>

<? if ($locked_info): ?>
    <?= MessageBox::info(formatLinks($locked_info)) ?>
<? endif; ?>

<form id="edit_private" action="<?= $controller->url_for('settings/details/store') ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studipticket" value="<?= get_ticket() ?>">
    <fieldset>
        <legend><?= _('Lebenslauf bearbeiten') ?></legend>
        <label>
            <?= _('Telefon Festnetz (privat)') ?>:<br>
            <input type="tel" name="telefon"
                   value="<?= htmlReady($user->privatnr) ?>"
                    <? if (!$controller->shallChange('user_info.privatnr')) echo 'disabled'; ?>>
        </label>
        <label>
            <?= _('Mobiltelefon (privat)') ?>:<br>
            <input type="tel" name="cell"
                   value="<?= htmlReady($user->privatcell) ?>"
                    <? if (!$controller->shallChange('user_info.privatcell')) echo 'disabled'; ?>>
        </label>
        <label for="private_address">
            <?= _('Adresse (privat):') ?>
            <input type="text" name="anschrift" id="private_address"
                   value="<?= htmlReady($user->privadr) ?>"
                    <? if (!$controller->shallChange('user_info.privadr')) echo 'disabled'; ?>>
        </label>
        <? if (Config::get()->ENABLE_SKYPE_INFO): ?>
            <label>
                <?= _('Skype Name:') ?>
                <input type="text" name="skype_name"
                       value="<?= htmlReady($config->SKYPE_NAME) ?>">
            </label>
        <? endif; ?>
        <label>
            <?= _('Motto:') ?>
            <input type="text" name="motto" id="motto"
                   value="<?= htmlReady($user->motto) ?>"
                    <? if (!$controller->shallChange('user_info.motto')) echo 'disabled'; ?>>
        </label>
        <label>
            <?= _('Homepage:') ?>
            <input type="url" name="home" id="homepage"
                   value="<?= htmlReady($user->Home) ?>"
                    <? if (!$controller->shallChange('user_info.Home')) echo 'disabled'; ?>>
        </label>
        <label>
            <?= _('Hobbys:') ?>
            <?= I18N::textarea('hobby', $user->hobby, !$controller->shallChange('user_info.hobby')? ['disabled' => true, 'readonly' => true] : []) ?>
        </label>
        <a name="lebenslauf"></a>
        <label>
            <?= _('Lebenslauf:') ?>
            <?= I18N::textarea('lebenslauf', $user->lebenslauf, !$controller->shallChange('user_info.lebenslauf')? ['disabled' => true, 'readonly' => true] : []) ?>
        </label>
        <? if ($is_dozent): ?>
            <a name="schwerpunkte"></a>
            <label>
                <?= _('Schwerpunkte:') ?>
                <?= I18N::textarea('schwerp', $user->schwerp, !$controller->shallChange('user_info.schwerp')? ['disabled' => true, 'readonly' => true] : []) ?>
            <a name="publikationen"></a>
            <label>
                <?= _('Publikationen:') ?>
                <?= I18N::textarea('publi', $user->publi, !$controller->shallChange('user_info.publi')? ['disabled' => true, 'readonly' => true] : []) ?>
            </label>
        <? endif; ?>
    </fieldset>

    <? if (count($user_entries) > 0): ?>
        <fieldset>
            <legend> <?= _('Zus�tzliche Datenfelder') ?></legend>
            <? foreach ($user_entries as $id => $entry): ?>

                <? if (isset($invalid_entries[$id])): ?>

                    <? $entry = $invalid_entries[$id]; // Exchange entry ?>
                <? else: ?>
                <? endif; ?>
                <label for="datafields_<?= $entry->getId() ?>">
                    <?= htmlReady($entry->getName()) ?>
                    <? if ($entry->isEditable() && !LockRules::check($user->user_id, $entry->getId())): ?>
                        <?= $entry->getHTML('datafields') ?>
                    <? else: ?>
                        <div>
                            <?= formatReady($entry->getDisplayValue(false)) ?>

                            <small> <?= _('(Das Feld ist f�r die Bearbeitung gesperrt und kann '
                                       . 'nur durch einen Administrator ver�ndert werden.)') ?></small>
                        </div>
                    <? endif; ?>
                    <? if (!$entry->isVisible($user->perms)): ?>
                        <?= tooltipIcon(_('Systemfeld (f�r die Person selbst nicht sichtbar)'), true) ?>
                    <? endif; ?>
                </label>


            <? endforeach; ?>
        </fieldset>
    <? endif; ?>
    <footer>
        <?= Button::create(_('�bernehmen'), 'store', ['title' => _('�nderungen �bernehmen')]) ?>
    </footer>
</form>
