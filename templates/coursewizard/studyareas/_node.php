<li class="sem-tree-<?= htmlReady($node->id) ?> keep-node" data-id="<?= $node->id ?>">
    <?php if ($node->isAssignable()) : ?>
        <img src="<?= Assets::img('icons/yellow/arr_2left.svg') ?>"/>
    <?php endif ?>
    <input type="checkbox" id="<?= htmlReady($node->sem_tree_id) ?>"/>
    <label for="<?= htmlReady($node->sem_tree_id) ?>" onclick="return STUDIP.CourseWizard.getTreeChildren('<?= htmlReady($node->sem_tree_id) ?>', true)">
        <?= htmlReady($node->name) ?>
    </label>
    <ul></ul>
</li>