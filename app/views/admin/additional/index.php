<? if ($list): ?>
    <form class='default' method='post'>
        <fieldset>
            <legend><?= _('Zusatzangaben') ?></legend>
            <label>
                <?= _('Set') ?>
                <select name='aux_data'>
                    <option value='0'>
                        <?= '-- '._('Keine Zusatzdaten')." --" ?>
                    </option>
                    <? foreach ($list as $aux): ?>
                        <option value='<?= $aux->id ?>' <?= $course->aux_lock_rule && $course->aux->id == $aux->id ? "selected" : "" ?>>
                            <?= htmlReady($aux->name) ?>
                        </option>
                    <? endforeach ?>
                </select>
            </label>
            <label>
                <?= _('Eingaben erzwingen') ?>
                <input type='checkbox' name='forced' value='1' <?= $course->aux_lock_rule_forced ? "checked" : "" ?>>
            </label>
            <? if($count): ?>
           <?= $count." "._('Datens�tze vorhanden') ?>
                        <label><?= _('L�schen?') ?>
                <input type='checkbox' name='delete' value='1'>
            </label>
            <? endif; ?>
        </fieldset>
        <footer>
            <?= Studip\Button::create(_('�bernehmen'), 'save') ?>
        </footer>
    </form>
<? else: ?>
    <? _('Keine Zusatzangaben vorhanden') ?>
<? endif; ?>