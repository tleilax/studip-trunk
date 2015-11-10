<input type="radio" name="<?= $name ?>[<?= $structure->getID() ?>][combo]"
       value="select" id="combo_<?= $structure->getID() ?>_select"
       <? if (in_array($value, $values)) echo 'checked'; ?>>
<select name="<?= $name ?>[<?= $structure->getID() ?>][select]" onfocus="$('#combo_<?= $structure->getID() ?>_select').prop('checked', true);">
<? foreach ($values as $v): ?>
    <option value="<?= htmlReady($v) ?>" <? if ($v === $value) echo 'selected'; ?>>
        <?= htmlReady($v) ?>
    </option>
<? endforeach; ?>
</select>

<input type="radio" name="<?= $name ?>[<?= $structure->getID() ?>][combo]"
       value="text" id="combo_<?= $structure->getID() ?>_text"
       <? if (!in_array($value, $values)) echo 'checked'; ?>>
<input name="<?= $name ?>[<?= $structure->getID() ?>][text]"
       value="<? if (!in_array($value, $values)) echo htmlReady($value); ?>"
       onfocus="$('#combo_<?= $structure->getID() ?>_text').prop('checked', true);">
