<?php

/**
 * This class extends the base class of phpsvg to provide extra functionality. 
 */
class CampanhaSVGDocument extends SVGDocument {
    /**
     * Override XmlElement::prettyXML() method which is
     * a performance killer. If you call SVGDocument::asXML()
     * there is no way to skip XmlElement::prettyXML()
     */
    protected function prettyXml($xml, $debug = false) {
        return $xml;
    }
    
    /**
     * Workaround to be able to prepend the image to the top
     * of the SVG file since SimpleXML used by phpsvg support only
     * append and not prepend.
     */
    public function prependImage($prepend)
    {
        $dom = dom_import_simplexml($this);

        $new = $dom->insertBefore(
            $dom->ownerDocument->createElement($prepend->getName()),
            $dom->firstChild
        );
        
        $new->setAttribute('xlink:href', $prepend->getAttribute('xlink:href'));
        $new->setAttribute('x', $prepend->getAttribute('x'));
        $new->setAttribute('y', $prepend->getAttribute('y'));
        $new->setAttribute('id', $prepend->getAttribute('id'));
        $new->setAttribute('width', $prepend->getWidth());
        $new->setAttribute('height', $prepend->getHeight());

        return simplexml_import_dom($new, get_class($this));
    }
}
