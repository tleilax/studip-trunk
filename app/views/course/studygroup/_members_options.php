<? if (in_array($m, $moderators) && $GLOBALS['perm']->have_studip_perm('admin', $sem_id)) : ?>
	&nbsp;<a href="<?= $controller->url_for('course/studygroup/edit_members/'.$sem_id.'/'.$m['username'].'/promote/tutor') ?>" alt="NutzerIn runterstufen">
		<?= makebutton('runterstufen') ?>
	</a>
<? elseif (in_array($m, $tutors)) : ?>
	<? if ($GLOBALS['perm']->have_studip_perm('admin', $sem_id)) : ?>
	&nbsp;<a href="<?= $controller->url_for('course/studygroup/edit_members/'.$sem_id.'/'.$m['username'].'/promote/dozent') ?>" alt="NutzerIn bef�rdern">
		<?= makebutton('hochstufen') ?>
	</a><br>
	<br>
	<? endif; ?>

	&nbsp;<a href="<?= $controller->url_for('course/studygroup/edit_members/'.$sem_id.'/'.$m['username'].'/promote/autor') ?>" alt="NutzerIn runterstufen">
		<?= makebutton('runterstufen') ?>
	</a>
<? else : ?>
	&nbsp;<a href="<?= $controller->url_for('course/studygroup/edit_members/'.$sem_id.'/'.$m['username'].'/promote/tutor') ?>" alt="NutzerIn bef�rdern">
		<?= makebutton('hochstufen') ?>
	</a><br>
	<br>
	&nbsp;<a href="<?= $controller->url_for('course/studygroup/edit_members/'.$sem_id.'/'.$m['username'].'/remove') ?>" alt="NutzerIn runterstufen">
		<?= makebutton('rauswerfen') ?>
	</a>
<? endif ?>
