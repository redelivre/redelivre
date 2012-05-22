<?php

/**
 * This class single purpose is to override XmlElement::prettyXML()
 * method which is a performance killer. If you call SVGDocument::asXML()
 * there is no way to skip XmlElement::prettyXML()
 */
class CampanhaSVGDocument extends SVGDocument {
    protected function prettyXml($xml) {
        return $xml;
    }
}
