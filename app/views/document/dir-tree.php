<? if (!empty($children)): ?>
<ul>
<? foreach ($children as $id => $child): ?>
    <li class="file-directory <? if (empty($child['children'])) echo 'empty-directory'; ?>">
    <? if (!empty($child['children'])): ?>
        <input type="checkbox" id="folder-toggle-<?= $id ?>" <? if (in_array($id, $active_folders)) echo 'checked'; ?>>
        <label for="folder-toggle-<?= $id ?>">toggle</label>
    <? endif; ?>
        <input type="radio" name="folder_id" id="folder-<?= $id ?>"
               value="<?= htmlReady($id) ?>" <? if ($id === $parent_file_id) echo 'checked'; ?>>
        <label for="folder-<?= $id ?>"><?= htmlReady($child['filename']) ?></label>
    <? if (!empty($child['children'])): ?>
        <?= $this->render_partial('document/dir-tree', $child) ?>
    <? endif; ?>
    </li>
<? endforeach; ?>
</ul>
<? endif; ?>