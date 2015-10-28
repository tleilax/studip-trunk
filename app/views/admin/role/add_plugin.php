<select multiple>
<? foreach ($plugins as $plugin): ?>
    <option value="<?= $plugin['id'] ?>" <? if (in_array($plugin['id'], $assigned)) echo 'selected'; ?>>
        <?= htmlReady($plugin['name']) ?>
    </option>
<? endforeach; ?>
</select>
