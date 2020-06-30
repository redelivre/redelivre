<?php

namespace Forminator\PayPal\Api;

use Forminator\PayPal\Common\PayPalModel;

/**
 * Class HyperSchema
 *
 * 
 *
 * @package Forminator\PayPal\Api
 *
 * @property \Forminator\PayPal\Api\Links[] links
 * @property string fragmentResolution
 * @property bool readonly
 * @property string contentEncoding
 * @property string pathStart
 * @property string mediaType
 */
class HyperSchema extends PayPalModel
{
    /**
     * Sets Links
     *
     * @param \Forminator\PayPal\Api\Links[] $links
     * 
     * @return $this
     */
    public function setLinks($links)
    {
        $this->links = $links;
        return $this;
    }

    /**
     * Gets Links
     *
     * @return \Forminator\PayPal\Api\Links[]
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * Append Links to the list.
     *
     * @param \Forminator\PayPal\Api\Links $links
     * @return $this
     */
    public function addLink($links)
    {
        if (!$this->getLinks()) {
            return $this->setLinks(array($links));
        } else {
            return $this->setLinks(
                array_merge($this->getLinks(), array($links))
            );
        }
    }

    /**
     * Remove Links from the list.
     *
     * @param \Forminator\PayPal\Api\Links $links
     * @return $this
     */
    public function removeLink($links)
    {
        return $this->setLinks(
            array_diff($this->getLinks(), array($links))
        );
    }

    /**
     * Sets FragmentResolution
     *
     * @param string $fragmentResolution
     * 
     * @return $this
     */
    public function setFragmentResolution($fragmentResolution)
    {
        $this->fragmentResolution = $fragmentResolution;
        return $this;
    }

    /**
     * Gets FragmentResolution
     *
     * @return string
     */
    public function getFragmentResolution()
    {
        return $this->fragmentResolution;
    }

    /**
     * Sets Readonly
     *
     * @param bool $readonly
     * 
     * @return $this
     */
    public function setReadonly($readonly)
    {
        $this->readonly = $readonly;
        return $this;
    }

    /**
     * Gets Readonly
     *
     * @return bool
     */
    public function getReadonly()
    {
        return $this->readonly;
    }

    /**
     * Sets ContentEncoding
     *
     * @param string $contentEncoding
     * 
     * @return $this
     */
    public function setContentEncoding($contentEncoding)
    {
        $this->contentEncoding = $contentEncoding;
        return $this;
    }

    /**
     * Gets ContentEncoding
     *
     * @return string
     */
    public function getContentEncoding()
    {
        return $this->contentEncoding;
    }

    /**
     * Sets PathStart
     *
     * @param string $pathStart
     * 
     * @return $this
     */
    public function setPathStart($pathStart)
    {
        $this->pathStart = $pathStart;
        return $this;
    }

    /**
     * Gets PathStart
     *
     * @return string
     */
    public function getPathStart()
    {
        return $this->pathStart;
    }

    /**
     * Sets MediaType
     *
     * @param string $mediaType
     * 
     * @return $this
     */
    public function setMediaType($mediaType)
    {
        $this->mediaType = $mediaType;
        return $this;
    }

    /**
     * Gets MediaType
     *
     * @return string
     */
    public function getMediaType()
    {
        return $this->mediaType;
    }

}
