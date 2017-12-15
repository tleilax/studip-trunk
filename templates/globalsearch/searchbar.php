<div id="globalsearch-searchbar">
    <input type="text" name="globalsearchterm" id="globalsearch-input" size="30"
        placeholder="<?= _('Was suchen Sie?') ?>">
    <?= Icon::create('decline', 'inactive',
        ['id' => 'globalsearch-clear', 'class' => 'hidden-js'])->asImg(16) ?>
    <a href="#" id="globalsearch-icon">
        <?= Icon::create('search', 'info_alt')->asImg(24) ?>
    </a>
    <div id="globalsearch-list" class="hidden-js">
        <a href="#" id="globalsearch-togglehints" data-toggle-text="<?= _('Tipps ausblenden') ?>">
            <?= _('Tipps einblenden') ?>
        </a>
        <?= $GLOBALS['template_factory']->render('globalsearch/_hints') ?>
        <div id="globalsearch-results" class="hidden-js" data-loading-text="<?= _('Suche...') ?>"
             data-no-result="<?= _('Keine Ergebnisse gefunden.') ?>" data-more-results="<?= _('alle anzeigen') ?>"
             data-results-per-type="<?= Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE ?>">
        </div>
    </div>
</div>
