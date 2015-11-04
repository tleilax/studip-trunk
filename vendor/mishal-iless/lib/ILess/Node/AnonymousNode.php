<?php

/*
 * This file is part of the ILess
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ILess\Node;

use ILess\Context;
use ILess\FileInfo;
use ILess\Node;
use ILess\Output\OutputInterface;

/**
 * Anonymous node
 *
 * @package ILess\Node
 */
class AnonymousNode extends Node implements ComparableInterface,
    MarkableAsReferencedInterface, ReferencedInterface
{
    /**
     * Node type
     *
     * @var string
     */
    protected $type = 'Anonymous';

    /**
     * Current index
     *
     * @var integer
     */
    public $index = 0;

    /**
     * Map lines flag
     *
     * @var boolean
     */
    public $mapLines;

    /**
     * @var bool
     */
    private $rulesetLike = false;

    /**
     * Is referenced?
     *
     * @var boolean
     */
    public $isReferenced = false;

    /**
     * Constructor
     *
     * @param string|ValueNode|null $value
     * @param integer $index
     * @param FileInfo $currentFileInfo
     * @param boolean $mapLines
     * @param boolean $rulesetLike
     * @param boolean $referenced
     */
    public function __construct(
        $value,
        $index = 0,
        FileInfo $currentFileInfo = null,
        $mapLines = false,
        $rulesetLike = false,
        $referenced = false
    ) {
        parent::__construct($value);

        $this->index = $index;
        $this->currentFileInfo = $currentFileInfo;
        $this->mapLines = $mapLines;
        $this->rulesetLike = (boolean)$rulesetLike;
        $this->isReferenced = $referenced;
    }

    /**
     * @inheritdoc
     */
    public function generateCSS(Context $context, OutputInterface $output)
    {
        $output->add($this->value, $this->currentFileInfo, $this->index, $this->mapLines);
    }

    /**
     * Converts to CSS
     *
     * @param Context $context
     * @return string
     */
    public function toCSS(Context $context)
    {
        return $this->value;
    }

    /**
     * Compiles the node
     *
     * @param Context $context The context
     * @param array|null $arguments Array of arguments
     * @param boolean|null $important Important flag
     * @return AnonymousNode
     */
    public function compile(Context $context, $arguments = null, $important = null)
    {
        return new AnonymousNode($this->value, $this->index, $this->currentFileInfo, $this->mapLines,
            $this->rulesetLike, $this->isReferenced);
    }

    /**
     * @inheritdoc
     */
    public function compare(Node $other)
    {
        // we need to provide The context for those
        $context = new Context();
        if ($this->toCSS($context) === $other->toCSS($context)) {
            return 0;
        }
    }

    /**
     * @return bool
     */
    public function isRulesetLike()
    {
        return $this->rulesetLike;
    }

    /**
     * @return bool
     */
    public function getIsReferenced()
    {
        return !$this->currentFileInfo || !$this->currentFileInfo->reference || $this->isReferenced;
    }

    /**
     * Marks as referenced
     *
     * @return void
     */
    public function markReferenced()
    {
        $this->isReferenced = true;
    }

}
