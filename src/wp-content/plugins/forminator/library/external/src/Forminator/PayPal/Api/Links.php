<?php

namespace Forminator\PayPal\Api;

use Forminator\PayPal\Common\PayPalModel;

/**
 * Class Links
 *
 * 
 *
 * @package Forminator\PayPal\Api
 *
 * @property string href
 * @property string rel
 * @property \Forminator\PayPal\Api\HyperSchema targetSchema
 * @property string method
 * @property string enctype
 * @property \Forminator\PayPal\Api\HyperSchema schema
 */
class Links extends PayPalModel
{
    /**
     * Sets Href
     *
     * @param string $href
     * 
     * @return $this
     */
    public function setHref($href)
    {
        $this->href = $href;
        return $this;
    }

    /**
     * Gets Href
     *
     * @return string
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * Sets Rel
     *
     * @param string $rel
     * 
     * @return $this
     */
    public function setRel($rel)
    {
        $this->rel = $rel;
        return $this;
    }

    /**
     * Gets Rel
     *
     * @return string
     */
    public function getRel()
    {
        return $this->rel;
    }

    /**
     * Sets TargetSchema
     *
     * @param \Forminator\PayPal\Api\HyperSchema $targetSchema
     * 
     * @return $this
     */
    public function setTargetSchema($targetSchema)
    {
        $this->targetSchema = $targetSchema;
        return $this;
    }

    /**
     * Gets TargetSchema
     *
     * @return \Forminator\PayPal\Api\HyperSchema
     */
    public function getTargetSchema()
    {
        return $this->targetSchema;
    }

    /**
     * Sets Method
     *
     * @param string $method
     * 
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Gets Method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Sets Enctype
     *
     * @param string $enctype
     * 
     * @return $this
     */
    public function setEnctype($enctype)
    {
        $this->enctype = $enctype;
        return $this;
    }

    /**
     * Gets Enctype
     *
     * @return string
     */
    public function getEnctype()
    {
        return $this->enctype;
    }

    /**
     * Sets Schema
     *
     * @param \Forminator\PayPal\Api\HyperSchema $schema
     * 
     * @return $this
     */
    public function setSchema($schema)
    {
        $this->schema = $schema;
        return $this;
    }

    /**
     * Gets Schema
     *
     * @return \Forminator\PayPal\Api\HyperSchema
     */
    public function getSchema()
    {
        return $this->schema;
    }

}
