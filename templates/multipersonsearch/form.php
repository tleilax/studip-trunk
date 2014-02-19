<p><?= $description; ?></p>
<form method="POST" action="<?= URLHelper::getLink('dispatch.php/' . $executeURL); ?>&js=true" id="<?= $name; ?>">
    <input id="<?= $name . '_searchinput'; ?>" type="text" placeholder="<?= _("Suchen"); ?>" value="" name="<?= $name . '_searchinput'; ?>" style="width: 210px;" aria-label="<?= _("Suchen"); ?>"></input>
    <img title="Suche starten" src="<?= Assets::image_path("icons/16/blue/search.png"); ?>" onclick="STUDIP.MultiPersonSearch.search()"><br>
    <strong id="<?= $name . '_search_message_box'; ?>" style="display: none;"><?= _("Es wurden keine neuen Ergebnisse gefunden."); ?><br></strong>
    
    <? foreach($quickfilter as $title => $users) : ?>
        <a href="javascript:STUDIP.MultiPersonSearch.loadQuickfilter('<?= $title; ?>');"><?= $title; ?> (<?= count($users); ?>)</a> 
        <select multiple="multiple" id="<?= $name . '_quickfilter_' . $title; ?>" style="display: none;">
        <? foreach($users as $user) : ?>
            <option value="<?= $user->id ?>"><?= Avatar::getAvatar($user->id)->getURL(Avatar::SMALL); ?> -- <?= htmlReady($user->getFullName('full_rev')) ?> -- <?= htmlReady($user->perms) ?> (<?= htmlReady($user->username)?>)</option>
        <? endforeach; ?>
         </select>
    <? endforeach; ?>
    <br>
    <strong id="<?= $name . '_quickfilter_message_box'; ?>" style="display: none;"><?= _("Es wurden bereits alle Personen dieses Filters ausgewählt."); ?><br></strong>
    
    <select multiple="multiple" id="<?= $name . '_selectbox'; ?>" name="<?= $name . '_selectbox'; ?>[]" onchange="STUDIP.MultiPersonSearch.count()">
    </select>
    <select multiple="multiple" id="<?= $name . '_selectbox_default'; ?>" style="display: none;">
        <? foreach ($defaultSelectableUsers as $person): ?>
            <option value="<?= $person->id ?>"><?= Avatar::getAvatar($person->id)->getURL(Avatar::SMALL); ?> -- <?= htmlReady($person->getFullName('full_rev')) ?> -- <?= htmlReady($person->perms) ?> (<?= htmlReady($person->username)?>)</option>
        <? endforeach; ?>
        <? foreach ($defaultSelectedUsers as $person): ?>
            <option value="<?= $person->id ?>" selected><?= Avatar::getAvatar($person->id)->getURL(Avatar::SMALL); ?> -- <?= htmlReady($person->getFullName('full_rev')) ?> -- <?= htmlReady($person->perms) ?> (<?= htmlReady($person->username)?>)</option>
        <? endforeach; ?>
    </select>
    
    <?= \Studip\Button::create(_('Speichern'), 'confirm') ?>
    <?= \Studip\Button::create(_('Abbrechen'), $name . '_button_abort') ?>
    
    <?= CSRFProtection::tokenTag() ?>
    
</form>
