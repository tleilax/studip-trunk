<select name="<?= $name ?>[<?= $structure->getID() ?>]" id="<?= $name ?>_<?= $structure->getID() ?>"
        <? if ($multiple) echo 'multiple'; ?>
        <? if ($structure->getIsRequired()) echo 'required'; ?>>
<? foreach ($type_param as $pkey => $pval): ?>
    <option value="<?= $is_assoc ? (string)$pkey : $pval ?>"
            <? if ($value === ($is_assoc ? (string)$pkey : $pval)) echo 'selected'; ?>>
        <?= htmlReady($pval) ?>
    </option>
<? endforeach; ?>
</select>
