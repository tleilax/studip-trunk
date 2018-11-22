<?php
abstract class ConsultationController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        // Restore request if present
        if (isset($this->flash['request'])) {
            foreach ($this->flash['request'] as $key => $value) {
                Request::set($key, $value);
            }
        }
    }

    protected function keepRequest()
    {
        $this->flash['request'] = Request::getInstance()->getIterator()->getArrayCopy();
    }

    protected function sendMessage(User $user, ConsultationSlot $slot, $subject, $reason)
    {
        // Don't send message if teacher doesn't want it
        if ($user->id === $slot->block->teacher_id && !UserConfig::get($user->id)->CONSULTATION_SEND_MESSAGES) {
            return;
        }

        setTempLanguage($user->id);

        // if ($subject === self::MAIL_REASON_BOOKED) {
        //     $subject = _('Sprechstundentermin zugesagt');
        // }

        $message = $this->get_template_factory()->open('consultation/mail.php')->render([
            'slot'   => $slot,
            'reason' => $reason ?: _('Kein Grund angegeben'),
        ]);

        $messaging = new messaging;
        $messaging->insert_message($message, $user->username, '', '', '', '', '', $subject);

        restoreLanguage();
    }
}
