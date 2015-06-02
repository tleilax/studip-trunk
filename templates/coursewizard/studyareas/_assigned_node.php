<li class="sem-tree-assigned-<?= $element['id'] ?>">
    <?= htmlReady($element['name']) ?>
    <?php if ($element['assignable']) : ?>
    <a href="" onclick="return STUDIP.CourseWizard.unassignNode('<?= $element['id'] ?>')">
        <?= Assets::img('icons/16/blue/trash.svg', array('width' => 16, 'height' => 16)) ?></a>
    <input type="hidden" name="studyareas[]" value="<?= $element['id'] ?>"/>
    <?php endif ?>
    <ul>
        <?php foreach ($element['children'] as $c) : ?>
        <?= $this->render_partial('coursewizard/studyareas/_assigned_node', array('element' => $c)) ?>
        <?php endforeach ?>
    </ul>
</li>