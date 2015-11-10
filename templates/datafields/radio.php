<? foreach ($type_param as $pkey => $pval): ?>
<label>
    <input type="radio" name="<?= $name ?>[<?= $structure->getID() ?>]"
           value="<?= $is_assoc ? (string) $pkey : $pval ?>"
           <? if ($value === ($is_assoc ? (string)$pkey : $pval)) echo 'checked'; ?>
           <? if ($structure->getIsRequired()) echo 'required'; ?>>
    <?= htmlReady($pval) ?>
</label>
<? endforeach; ?>