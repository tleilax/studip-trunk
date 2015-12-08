<ul>
<? foreach ($urls as $link => $name) : ?>
    <li>
        <a href="<?= $link ?>">
            <?= $name ?>
        </a>
    </li>
<? endforeach; ?>
</ul>

