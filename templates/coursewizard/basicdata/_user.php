<div class="<?= $class ?>">
    <input type="hidden" name="<?= $inputname ?>[<?= $user->id ?>]" value="1"/>
    <?= Avatar::getAvatar($user->id)->getImageTag(Avatar::SMALL) ?>
    <?= htmlReady($user->getFullname('full_rev')) ?> (<?= htmlReady($user->username) ?>)
    <a href="" onclick="return STUDIP.CourseWizard.removePerson(this)">
        <?= Assets::img('icons/blue/trash.svg') ?></a>
</div>
