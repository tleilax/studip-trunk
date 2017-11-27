<div id="globalsearch-searchbar">
    <input type="text" name="globalsearchterm" id="globalsearch-input" size="50" class="hidden-js"
        placeholder="<?= _('Was suchen Sie?') ?>">
    <a href="#" id="globalsearch-icon">
        <?= Icon::create('search', 'info_alt')->asImg(24) ?>
    </a>
    <div id="globalsearch-list" class="hidden-js">
        <a href="#" id="globalsearch-togglehints" data-toggle-text="<?= _('Tipps ausblenden') ?>">
            <?= _('Tipps einblenden') ?>
        </a>
        <?= $GLOBALS['template_factory']->render('globalsearch/_hints') ?>
    </div>
</div>
