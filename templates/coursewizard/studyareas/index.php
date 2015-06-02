<h1><?= _('Studienbereiche') ?></h1>
<div id="assigned" style="width: 45%; float:left; padding-right: 10px;">
    <h2><?= _('Bereits zugewiesen') ?></h2>
    <ul class="css-tree">
        <li class="sem-tree-assigned-root keep-node">
            <?= $GLOBALS['UNI_NAME'] ?>
            <ul>
            <?php foreach ($values['studyareas'] as $element) : ?>
            <?= $this->render_partial('coursewizard/studyareas/_assigned_node', array('element' => $element)) ?>
            <?php endforeach ?>
            </ul>
        </li>
    </ul>
</div>
<div id="studyareas"  style="width: 45%; float: left; border-left: 1px solid #666666; padding-left: 10px;" data-ajax-url="<?= URLHelper::getLink('dispatch.php/course/wizard/ajax') ?>"">
    <h2><?= _('Alle Studienbereiche') ?></h2>
    <div>
        <input type="text" size="40" maxlength="255" name="search" id="sem-tree-search"/>
        <a href="" onclick="return STUDIP.CourseWizard.searchTree()" id="sem-tree-search-start">
            <?= Assets::img('icons/blue/search.svg') ?></a>
    </div>
    <ul class="collapsable css-tree">
        <li class="sem-tree-root tree-loaded keep-node">
            <input type="checkbox" id="root" checked="checked"/>
            <label for="root">
                <?= $GLOBALS['UNI_NAME'] ?>
            </label>
            <ul>
            <?php foreach ($tree as $node) : ?>
                <li class="sem-tree-<?= htmlReady($node->sem_tree_id) ?> keep-node">
                    <?php if ($node->isAssignable()) : ?>
                    <img src="<?= Assets::img('images/icons/yellow/arr_2left.svg') ?>"/>
                    <?php endif ?>
                    <input type="checkbox" id="<?= htmlReady($node->sem_tree_id) ?>"/>
                    <label for="<?= htmlReady($node->sem_tree_id) ?>" onclick="return STUDIP.CourseWizard.getTreeChildren('<?= htmlReady($node->sem_tree_id) ?>', true)">
                        <?= htmlReady($node->name) ?>
                    </label>
                    <ul></ul>
                </li>
            <?php endforeach ?>
            </ul>
        </li>
    </ul>
</div>