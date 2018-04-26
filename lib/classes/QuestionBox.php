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

    /**
     * Creates a question object that should be confirmed as a POST request.
     *
     * @param string $question              The question that should be confirmed
     * @param array  $approve_parameters    Parameters to send upon approval
     * @param array  $disapprove_parameters Parameters to send upon disapproval
     * @return QuestionBox instance to allow chaining
     */
    public static function createForm($question, array $approve_params = [], array $disapprove_params = [])
    {
        $qbox = self::create($question, $approve_params, $disapprove_params);
        $qbox->setMethod('POST');
        return $qbox;
    }

    protected $question;
    protected $method = 'GET';
    protected $approve_parameters = [];
    protected $disapprove_parameters = [];
    protected $approve_url = '?';
    protected $disapprove_url = '?';

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
     * Set the request method for the approval request.
     *
     * @param string $method
     * @return QuestionBox instance to allow chaining
     * @throws Exception when method is neither GET nor POST
     */
    public function setMethod($method)
    {
        $method = strtoupper($method);
        if (!in_array($method, ['GET', 'POST'])) {
            throw new Exception('Only GET and POST are allowed as methods.');
        }
        $this->method = $method;

        return $this;
    }

    /**
     * Renders the question box as html.
     *
     * @return string
     */
    public function __toString()
    {
        return $GLOBALS['template_factory']->render('shared/question-box', [
            'question' => $this->question,
            'method'   => $this->method,

            'approve_url'        => $this->approve_url,
            'approve_parameters' => $this->approve_parameters,

            'disapprove_url'        => $this->disapprove_url,
            'disapprove_parameters' => $this->disapprove_parameters,
        ]);
    }
}
