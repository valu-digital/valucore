<?php
namespace Valu\Doctrine\MongoDB\Query;

use Valu\Selector\Selector as SelectorDefinition,
    Valu\Selector\Sequence as SequenceDefinition,
    Doctrine\ODM\MongoDB\DocumentManager,
    Doctrine\ODM\MongoDB\Query\Builder,
    Doctrine\ODM\MongoDB\Query\Expr,
    Valu\Doctrine\MongoDB\Query\Selector\Template,
    Valu\Doctrine\MongoDB\Query\Selector\Delegate,
    Valu\Doctrine\MongoDB\Query\Selector\Sequence,
    Valu\Doctrine\MongoDB\Query\Selector\Sequence\Delegate as SequenceDelegate;

/**
 * CSS selector based query
 * 
 * @author juhasuni
 *
 */
class Selector
{
    
    /**
     * Selector
     * 
     * @var Valu\Selector\Selector
     */
    protected $selector;
    
    /**
     * Document manager
     * 
     * @var DocumentManager
     */
    protected $documentManager;
    
    /**
     * Document class names
     * 
     * @var array
     */
    protected $documentNames;

    /**
     * Query builder
     *
     * @var Doctrine\ODM\MongoDB\Query\Builder
     */
    protected $queryBuilder;
    
    /**
     * Selector template
     * 
     * @var Template
     */
    protected $selectorTemplate;

    /**
     * Class name for options
     *
     * @var string
     */
    protected $optionsClass = 'Valu\Doctrine\MongoDB\Query\Selector\SelectorOptions';
    
    /**
     * Options
     *
     * @var Zend\Stdlib\ParameterObject
     */
    protected $options;
    
    public function __construct(SelectorDefinition $selector, DocumentManager $documentManager, array $documentNames, $options = null){
        $this->selector = $selector;
        $this->documentManager = $documentManager;
        $this->documentNames = $documentNames;
        
        if(!is_null($options)){
            $this->setOptions($options);
        }
    }
    
    /**
     * Extend query with selector
     *
     * @param $queryBuilder Builder
     * @param $expr Expr
     */
    public function extendQuery(Builder $queryBuilder, Expr $expression = null){
        $this->queryBuilder = $queryBuilder;
        
        $definition = null;
        $combinator = null;
        $next = null;
    
        /**
         * If delegate wants to process sequences in
         * reverse order, start from the last one
         */
        if($this->getDelegate()->reversed()){
            $definition   = $this->selector->getLastSequence();
            $next         = $definition->getParentSequence();
            $combinator   = $next ? $next->getChildCombinator() : null;
        }
        else{
            $definition   = $this->selector->getFirstSequence();
            $combinator   = $definition->getChildCombinator();
            $next         = $definition->getChildSequence();
        }
        
        /**
         * Process sequence
         */
        if($definition){
            $joined = true;
            
            if($expression === null){
                $joined     = false;
                $expression = $queryBuilder->expr();
            }
            
            $sequence = $this->createSequence($definition);
            $sequence->extendQuery($queryBuilder, $expression);
            
            if (!$joined) {
                $queryBuilder->addAnd($expression);
            }
        }
        
        /**
         * If another selector exists in sequence (either
         * parent or child, depending on the processing order)
         * combine that selector
         */
        if($next){
            
            $nextExpr   = $queryBuilder->expr();
            $new        = clone $this;
            
            if($this->getDelegate()->reversed()){
                $new->getSelectorDefinition()
                        ->popSequence();
            }
            else{
                $new->getSelectorDefinition()
                        ->shiftSequence();
            }
            
            //TODO: query builder needs to be passed on one way or another
            $this->getDelegate()->combineSelector($this, $new, $combinator, $nextExpr);
            
            $queryBuilder->addAnd($nextExpr);
        }
    }
    
    /**
     * Retrieve delegate instance
     * 
     * @return Delegate
     */
    public function getDelegate(){
        return $this->getOption('delegate');
    }
    
    /**
     * Set delegate
     *
     * @param Delegate $delegate
     */
    public function setDelegate(Delegate $delegate){
        $this->setOption('delegate', $delegate);
    }
    
    /**
     * Retrieve selector definition
     *
     * @return \Valu\Selector\Selector
     */
    public function getSelectorDefinition(){
        return $this->selector;
    }

    /**
     * Create new query builder for sequences
     * 
     * @param SequenceDefinition $sequenceDefinition
     */
    public function createSequence(SequenceDefinition $sequenceDefinition){
        
        $options = $this->getOption('sequence_options');

        $sequence = new Sequence(
            $sequenceDefinition,
            $this->getDocumentManager(),
            $this->getDocumentNames(),
            $this->getSelectorTemplate(),
            $options
        );
        
        return $sequence;
    }
    
    /**
     * Retrieve selector template
     * 
     * @return \Valu\Doctrine\MongoDB\Query\Selector\Template
     */
    public function getSelectorTemplate(){
        if(!$this->selectorTemplate){
            
            $this->selectorTemplate = new Template(
                $this->getDocumentManager(),
                $this->getDocumentNames(),
                $this->getOptions()->toArray()
            );
        }
        
        return $this->selectorTemplate;
    }
    
    /**
     * Retrieve document manager instance
     * 
     * @return \Doctrine\ODM\MongoDB\DocumentManager
     */
    public function getDocumentManager(){
        return $this->documentManager;
    }
    
    /**
     * Retrieve document class names
     * 
     * @return string
     */
    public function getDocumentNames(){
        return $this->documentNames;
    }
    
    /**
     * Set service options
     *
     * @param  array|Traversable $options
     * @return Service
     */
    public function setOptions($options)
    {
        $this->options = new $this->optionsClass($options);
        return $this;
    }
    
    /**
     * Retrieve service options
     *
     * @return array
     */
    public function getOptions()
    {
        if(!$this->options){
            $this->options = new $this->optionsClass(array());
        }
    
        return $this->options;
    }
    
    /**
     * Is an option present?
     *
     * @param  string $key
     * @return bool
     */
    public function hasOption($key)
    {
        return $this->getOptions()->__isset($key);
    }
    
    /**
     * Set option
     *
     * @param string $key
     * @param mixed $value
     * @return Service
     */
    public function setOption($key, $value)
    {
        $this->getOptions()->__set($key, $value);
        return $this;
    }
    
    /**
     * Retrieve a single option
     *
     * @param  string $key
     * @return mixed
     */
    public function getOption($key)
    {
        return $this->getOptions()->__get($key);
    }
    
    public function __clone()
    {
        $this->selector = clone $this->selector;
    }
    
    public static function extend(Builder $queryBuilder, $pattern, DocumentManager $documentManager, array $documentNames, $options = null)
    {
        if(is_string($pattern)){
            $definition = \Valu\Selector\Parser\SelectorParser::parseSelector($pattern);
        }
        else{
            $definition = $pattern;
        }
        
        $selector = new Selector($definition, $documentManager, $documentNames, $options);
        $selector->extendQuery($queryBuilder);
    
        return $selector;
    }
}