<?php
use Studip\Markup;

/**
 * Storage for formatted content
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 3.4
 */
class FormattedContent
{
    protected $format;
    protected $formatted_content = null;

    public function __construct($content, StudipFormat $format = null)
    {
        $this->format = $format ?: new StudipFormat;

        $formatted = Markup::apply($this->format, $content, false);
        $wrapped   = sprintf(FORMATTED_CONTENT_WRAPPER, $formatted);

        $this->formatted_content = $wrapped;
    }

    public function getContent($include_open_graph = false)
    {
        $result = $this->formatted_content;
        if ($include_open_graph) {
            $result .= $this->getOpenGraphURLCollection()->render();
        }
        return $result;
    }

    public function getOpenGraphURLCollection()
    {
        return $this->format->getOpenGraphCollection();
    }

    public function __toString()
    {
        return $this->getContent();
    }
}