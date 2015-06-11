<li class="sem-tree-<?= htmlReady($node->id) ?> keep-node" data-id="<?= $node->id ?>">
    <?php if ($node->isAssignable()) : ?>
    <a href="" onclick="return STUDIP.CourseWizard.assignNode('<?= $node->id ?>')">
        <?= Assets::img('icons/yellow/arr_2left.svg') ?></a>
    <?php endif ?>
    <?php if ($node->hasChildren()) : ?>
    <input type="checkbox" id="<?= htmlReady($node->sem_tree_id) ?>"/>
    <label for="<?= htmlReady($node->sem_tree_id) ?>" onclick="return STUDIP.CourseWizard.getTreeChildren('<?= htmlReady($node->sem_tree_id) ?>', true)">
    <?php endif ?>
        <?= htmlReady($node->name) ?>
    <?php if ($node->hasChildren()) : ?>
    </label>
    <ul></ul>
    <?php endif ?>
</li>