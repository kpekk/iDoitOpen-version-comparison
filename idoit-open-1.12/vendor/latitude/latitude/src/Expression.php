<?php

namespace Latitude\QueryBuilder;

class Expression implements Statement
{
    use Traits\CanUseDefaultIdentifier;
    use Traits\HasNoParameters;
    /**
     * Create a new expression.
     */
    public static function make($template, ...$identifiers)
    {
        return new static($template, $identifiers);
    }
    // Statement
    public function sql(Identifier $identifier = null)
    {
        $identifier = $this->getDefaultIdentifier($identifier);
        return \vsprintf($this->template, $identifier->allQualified($this->identifiers));
    }
    /**
     * @var string
     */
    protected $template;
    /**
     * @var string[]
     */
    protected $identifiers;
    /**
     * @see Expression::make()
     */
    protected function __construct($template, array $identifiers)
    {
        $this->template = $template;
        $this->identifiers = $identifiers;
    }
}