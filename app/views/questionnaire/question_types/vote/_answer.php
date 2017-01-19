<li>
    <?= Assets::img('anfasser_24.png', [ 'title' => _('Antwort verschieben'), 'class' => 'move' ]) ?>
    <input type="text"
           name="questions[<?= $vote->getId() ?>][task][answers][]"
           value="<?= htmlReady($answer['text']) ?>"
           placeholder="<?= _('Antwort ...') ?>"
           aria-label="<?= _('Geben Sie eine Antwortm�glichkeit zu der von Ihnen gestellten Frage ein.') ?>">
    <?= Icon::create('trash', ['title' => _('Antwort l�schen')])->asImg(20, ['class' => 'text-bottom delete']) ?>
    <?= Icon::create('add', ['title' => _('Antwort hinzuf�gen')])->asImg(20, ['class' => 'text-bottom add']) ?>
</li>
