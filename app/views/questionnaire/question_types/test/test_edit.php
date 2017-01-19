<? $etask = $vote->etask; ?>

<label>
    <?= _('Frage') ?>
    <textarea name="questions[<?= $vote->getId() ?>][description]"
              class="size-l"
              required><?= isset($etask->description) ? htmlReady($etask->description) : '' ?></textarea>
</label>

<? $emptyAnswerTemplate = $this->render_partial('questionnaire/question_types/test/_answer', [ 'vote' => $vote, 'answer' => [] ]) ?>
<ol class="clean options" data-optiontemplate="<?= htmlReady($emptyAnswerTemplate) ?>">
    <? if (isset($etask->task['answers'])) {
        foreach ($etask->task['answers'] as $index => $answer) {
            echo $this->render_partial('questionnaire/question_types/test/_answer', compact('vote', 'answer', 'index'));
        }
    }
    echo $this->render_partial(
        'questionnaire/question_types/test/_answer',
        [
            'vote' => $vote,
            'answer' => [],
            'index' => $index + 1,
            'forcecorrect' => !isset($etask->task['answers']) || empty($etask->task['answers'])
        ]
    ); ?>
</ol>

<div style="padding-left: 13px; margin-bottom: 20px;">
    <?= tooltipIcon(_('W�hlen Sie �ber die Auswahlboxen aus, welche Antwortm�glichkeit korrekt ist.')) ?>
</div>

<label>
    <input type="checkbox"
           name="questions[<?= $vote->getId() ?>][task][type]"
           value="multiple"
           <?= $vote->isNew() || $etask->task['type'] === 'multiple' ? 'checked' : '' ?>>
    <?= _('Mehrere Antworten sind erlaubt.') ?>
</label>

<label>
    <input type="checkbox"
           name="questions[<?= $vote->getId() ?>][options][randomize]"
           value="1"
           <?= isset($etask->options['randomize']) && $etask->options['randomize'] ? 'checked' : '' ?>>
    <?= _('Antworten den Teilnehmenden zuf�llig pr�sentieren.') ?>
</label>

<div style="display: none" class="delete_question"><?= _('Diese Antwortm�glichkeit wirklich l�schen?') ?></div>

<script>
 jQuery(function () {
     jQuery('.options').sortable({
         'axis': 'y',
         'containment': 'parent',
         'handle': '.move',
         'update': function () {
             STUDIP.Questionnaire.Test.updateCheckboxValues();
         }
     });
 });
</script>
