<?php
/**
 * A question box is used to display a confirmation dialog for a specific
 * action, quite similar to the message boxes.
 *
 * A question box consists at least of a question and some approval parameters
 * that will be sent when the question is approved. You may pass additional
 * parameters that are sent when the question is disapproved and both urls for
 * approving and disapproving may be set.
 *
 * @author  Jan-Hendrik Willms
 * @license GPL2 or any later version
 * @since   Stud.IP 4.2
 */
class QuestionBox implements LayoutMessage
{
    /**
     * Creates a question object that should be confirmed.
     *
     * @param string $question              The question that should be confirmed
     * @param array  $approve_parameters    Parameters to send upon approval
     * @param array  $disapprove_parameters Parameters to send upon disapproval
     * @return QuestionBox instance to allow chaining
     */
    public static function create($question, array $approve_params = [], array $disapprove_params = [])
    {
        $qbox = new static($question);
        $qbox->setApproveParameters($approve_params);
        $qbox->setDisapproveParameters($disapprove_params);
        return $qbox;
    }

    protected $question;
    protected $approve_parameters = [];
    protected $disapprove_parameters = [];
    protected $approve_url = '?';
    protected $disapprove_url = '?';
    protected $include_ticket = false;

    /**
     * Constructs the object. Protected to enforce the use of our static helper
     * functions.
     *
     * @param string $question The question that should be confirmed
     */
    protected function __construct($question)
    {
        $this->question = $question;
    }

    /**
     * Set the parameters that are sent upon approval.
     *
     * @param array $parameters
     * @return QuestionBox instance to allow chaining
     */
    public function setApproveParameters(array $parameters)
    {
        $this->approve_parameters = $parameters;
        return $this;
    }

    /**
     * Set the url the approval parameters are sent to.
     *
     * @param string $url
     * @return QuestionBox instance to allow chaining
     */
    public function setApproveURL($url)
    {
        $this->approve_url = $url;
        return $this;
    }

    /**
     * Set the parameters that are sent upon disapproval.
     *
     * @param array $parameters
     * @return QuestionBox instance to allow chaining
     */
    public function setDisapproveParameters(array $parameters)
    {
        $this->disapprove_parameters = $parameters;
        return $this;
    }

    /**
     * Set the url the disapproval parameters are sent to.
     *
     * @param string $url
     * @return QuestionBox instance to allow chaining
     */
    public function setDisapproveURL($url)
    {
        $this->disapprove_url = $url;
        return $this;
    }

    /**
     * Sets boths url for approval and disapproval to the same url.
     *
     * @param string $url
     * @return QuestionBox instance to allow chaining
     */
    public function setBaseURL($url)
    {
        $this->setApproveURL($url);
        $this->setDisapproveURL($url);
        return $this;
    }

    /**
     * Defines whether a stud.ip ticket should be included in the question.
     *
     * @param bool $state
     */
    public function includeTicket($name = 'studip_ticket')
    {
        $this->include_ticket = $name;
    }

    /**
     * Renders the question box as html.
     *
     * @return string
     */
    public function __toString()
    {
        // Include fresh ticket
        if ($this->include_ticket) {
            $this->approve_parameters[$this->include_ticket] = get_ticket();
        }

        return $GLOBALS['template_factory']->render('shared/question-box', [
            'question' => $this->question,

            'approve_url'        => $this->approve_url,
            'approve_parameters' => $this->approve_parameters,

            'disapprove_url'        => $this->disapprove_url,
            'disapprove_parameters' => $this->disapprove_parameters,
        ]);
    }
}
