<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>
<? if (isset($flash['message'])): ?>
    <?= MessageBox::success($flash['message']) ?>
<? endif ?>

<form action="<?= $controller->url_for('admin/plugin/save_default_activation', $plugin_id) ?>" method="post" class="default">
    <fieldset>
        <legend><?= _('Standard-Aktivierung in Veranstaltungen') ?>: <?= htmlReady($plugin_name) ?></legend>
        <?= CSRFProtection::tokenTag() ?>
        <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">
        <select name="selected_inst[]" multiple size="20" class="nested-select" style="width: 100%">
            <? foreach ($institutes as $id => $institute): ?>
                <option class="nested-item-header" value="<?= $id ?>" <?= in_array($id, $selected_inst) ? 'selected' : '' ?>>
                    <?= htmlReady($institute['name']) ?>
                </option>

                <? if (isset($institute['children'])): ?>
                    <? foreach ($institute['children'] as $id => $child): ?>
                        <option class="nested-item" value="<?= $id ?>" <?= in_array($id, $selected_inst) ? 'selected' : '' ?>>
                            <?= htmlReady($child['name']) ?>
                        </option>
                    <? endforeach ?>
                <? endif ?>
            <? endforeach ?>
        </select>
    </fieldset>
    
    <footer>
        <?= Button::create(_('�bernehmen'),'save', array('title' => _('Einstellungen speichern')))?>
        &nbsp;
        <?= LinkButton::create('<< ' . _("Zur�ck"), $controller->url_for('admin/plugin'), array('title' => _('Zur�ck zur Plugin-Verwaltung')))?>
    </footer>
</form>