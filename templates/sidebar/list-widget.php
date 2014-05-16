<ul class="<?= implode(' ', $css_classes) ?>">
<? foreach ($elements as $element): ?>
    <li<?= $element->icon ? ' style="list-style-image: url(' . $element->icon .');"' : "" ?><?= $element->active ? ' class="active"' : '' ?>>
        <?= $element->render() ?>
    </li>
<? endforeach; ?>
</ul>