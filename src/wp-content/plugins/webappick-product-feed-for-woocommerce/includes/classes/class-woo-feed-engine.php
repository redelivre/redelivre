<?php

/**
 * A class definition responsible for processing and mapping product according to feed rules and make the feed
 *
 * @link       https://webappick.com/
 * @since      1.0.0
 *
 * @package    Woo_Feed
 * @subpackage Woo_Feed/includes
 * @author     Ohidul Islam <wahid@webappick.com>
 */
class WF_Engine
{
    /**
     * This variable is responsible for mapping store attributes to merchant attribute
     *
     * @since   1.0.0
     * @var     array $mapping Map store attributes to merchant attribute
     * @access  private
     */
    private $mapping;

    /**
     * Store product information
     *
     * @since   1.0.0
     * @var     array $storeProducts
     * @access  public
     */
    private $storeProducts;

    /**
     * New product information
     *
     * @since   1.0.0
     * @var     array $products
     * @access  private
     */
    private $products;

    /**
     * Contain Feed Rules
     *
     * @since   1.0.0
     * @var     array $rules
     * @access  private
     */
    private $rules;

    /**
     * Contain TXT Feed First Row
     *
     * @since   1.0.0
     * @var     array $rules
     * @access  private
     */
    public $txtFeedHeader;

    /**
     * Contain CSV Feed First Row
     *
     * @since   1.0.0
     * @var     array $rules
     * @access  private
     */
    public $csvFeedHeader;
    public $productClass;

    public function __construct($Products, $rules)
    {
        $this->rules = $rules;
        $this->storeProducts = $Products;
	    $this->productClass = new Woo_Feed_Products();
    }

    public function stripInvalidXml($value) {
	    return woo_feed_stripInvalidXml( $value );
    }

    /**
     * Configure the feed according to the rules
     * @return array
     */
    public function mapProductsByRules()
    {

        $attributes = $this->rules['attributes'];
        $prefix = $this->rules['prefix'];
        $suffix = $this->rules['suffix'];
        $outputType = $this->rules['output_type'];
        $limit = $this->rules['limit'];
        $merchantAttributes = $this->rules['mattributes'];
        $type = $this->rules['type'];
        $default = $this->rules['default'];


        $wf_attr = array();
        $wf_cattr = array();

        # Map Merchant Attributes and Woo Attributes
        $countAttr = 0;

        if (!empty($merchantAttributes)) {
            foreach ($merchantAttributes as $key => $attr) {
                if ($type[$key] == 'attribute') {
                    $this->mapping[$attr]['value'] = $attributes[$key];
                    $this->mapping[$attr]['suffix'] = $suffix[$key];
                    $this->mapping[$attr]['prefix'] = $prefix[$key];
                    $this->mapping[$attr]['type'] = $outputType[$key];
                    $this->mapping[$attr]['limit'] = $limit[$key];
                } else if ($type[$key] == 'pattern') {
                    $this->mapping[$attr]['value'] = "wf_pattern_$default[$key]";
                    $this->mapping[$attr]['suffix'] = $suffix[$key];
                    $this->mapping[$attr]['prefix'] = $prefix[$key];
                    $this->mapping[$attr]['type'] = $outputType[$key];
                    $this->mapping[$attr]['limit'] = $limit[$key];
                }
                $countAttr++;
            }
        }

        # Process Dynamic Attributes and Category Mapping
        if (!empty($this->mapping)) {
            foreach ($this->mapping as $mkey => $attr) {
                if (strpos($attr['value'], 'wf_attr_') !== false) {
                    $wf_attr[] = $attr['value'];
                }

                if (strpos($attr['value'], 'wf_cattr_') !== false) {
                    $wf_cattr[] = $attr['value'];
                }
            }

            # Init Woo Attributes, Custom Attributes and Taxonomies
            if (!empty($this->storeProducts)) {
                if (!empty($wf_attr) || !empty($wf_cattr)) {
                    foreach ($this->storeProducts as $key => $value) {

	                    $id = $value['id'];

                        # Get Woo Attributes
                        if (!empty($wf_attr)) {
                            foreach ($wf_attr as $attr_key => $attr_value) {
                                //$this->storeProducts[$key][$attr_value] = implode(',', wc_get_product_terms($id, str_replace('wf_attr_', '', $attr_value), array('fields' => 'names')));
                                $this->storeProducts[$key][$attr_value] = $this->productClass->getProductAttribute($id,$attr_value);
                            }
                        }

                        # Get Custom Attributes
                        if (!empty($wf_cattr)) {
                            foreach ($wf_cattr as $cattr_key => $cattr_value) {
                                $this->storeProducts[$key][$cattr_value] = $this->productClass->getProductMeta($id,$cattr_value);
                            }
                        }
                    }
                }
            }
        }

        # Make Product feed array according to mapping
        if (!empty($this->storeProducts)) {
            foreach ($this->storeProducts as $key => $value) {
                $i = 0;
                foreach ($this->mapping as $attr => $rules) {
                    if (array_key_exists($rules['value'], $value)) {
                        $output = $value[$rules['value']];
                        if (!empty($output)) {

                            if(!empty($rules['type'])){
                                foreach ($rules['type'] as $key22 => $value22) {
                                    # Format Output According to output type
                                    if ($value22 == 2) { # Strip Tags
                                        $output = wp_strip_all_tags(html_entity_decode($output));
                                    } elseif ($value22 == 3) { # UTF-8 Encode
                                        $output = utf8_encode($output);
                                    } elseif ($value22 == 4) { # htmlentities
                                        $output = htmlentities($output, ENT_QUOTES, 'UTF-8');
                                    } elseif ($value22 == 5) { # Integer
                                        $output = absint($output);
                                    } elseif ($value22 == 6) { # Price
                                        $output=(float) $output;
                                        $output = number_format($output, 2, '.', '');
                                    } elseif ($value22 == 7) { # Delete Space
                                        $output = trim($output);
                                    } elseif ($value22 == 8) { # CDATA
                                        $output = '<![CDATA[' . $output . ']]>';
                                    }elseif ($value22 == 9) {
                                        $output=$this->stripInvalidXml($output);
                                    }else if($value22 == 10){
	                                    $output=$this->productClass->remove_short_codes($output);
                                    }
                                }
                            }

                            # Format Output According to output limit
                            if (!empty($rules['limit']) && is_numeric($rules['limit']) && strpos($output, "<![CDATA[") !== false) {
                                $output = str_replace(array("<![CDATA[", "]]>"), array('', ''), $output);
                                $output = substr($output, 0, $rules['limit']);
                                $output = '<![CDATA[' . $output . ']]>';
                            } elseif (!empty($rules['limit']) && is_numeric($rules['limit'])) {
                                $output = substr($output, 0, $rules['limit']);
                            }

                            # Prefix and Suffix Assign
                            if (strpos($output, "<![CDATA[") !== false) {
                                $output = str_replace(array("<![CDATA[", "]]>"), array('', ''), $output);
                                if('http' === substr($output, 0, 4 )){
                                    $output = $this->make_url_with_parameter($rules['prefix'],$output,$rules['suffix']);
                                }else{
                                    if($attr=='price' || $rules['value']=='sale_price' || $rules['value']=='current_price' && $output != ''){
                                        $suffix=!empty($rules['suffix'])?trim($rules['suffix']):$rules['suffix'];
                                        $output = $rules['prefix'].$output.''.$suffix;
                                    }else{
                                        if($output != '')
                                        {
                                            $output = $rules['prefix'].$output.$rules['suffix'];
                                        }
                                    }
                                }
                                $output = '<![CDATA[' . $output . ']]>';
                            } else {
                                if('http' === substr($output, 0, 4 )){
                                    $output = $this->make_url_with_parameter($rules['prefix'],$output,$rules['suffix']);
                                }else{
                                    if($attr=='price' || $rules['value']=='sale_price' || $rules['value']=='current_price' && $output != ''){
                                        $suffix=!empty($rules['suffix'])?trim($rules['suffix']):$rules['suffix'];
                                        $output = $rules['prefix'].$output.''.$suffix;
                                    }else{
                                        if($output != '')
                                        {
                                            $output = $rules['prefix'].$output.''.$rules['suffix'];
                                        }
                                    }
                                }
                            }

                            if(!empty($output)){
                                $output=stripslashes($output);
                            }

                        } elseif ($output=="0"){
                            $output="0";
                        } else{
                            $output='';
                        }

                        //$attr = trim($attr);
                        $this->products[$key][$attr] = $output;
                    } else {

                        if (!empty($default[$i])) {
                            $output = str_replace("wf_pattern_", '', $rules['value']);
                            if (!empty($output)) {

                                # Format Output According to output type
                                if(!empty($rules['type'])){
                                    foreach ($rules['type'] as $key22 => $value22) {
                                        if ($value22 == 2) { # Strip Tags
                                            $output = wp_strip_all_tags(html_entity_decode($output));
                                        } elseif ($value22 == 3) { # UTF-8 Encode
                                            $output = utf8_encode($output);
                                        } elseif ($value22 == 4) { # htmlentities
                                            $output = htmlentities($output, ENT_QUOTES, 'UTF-8');
                                        } elseif ($value22 == 5) { # Integer
                                            $output = absint($output);
                                        } elseif ($value22 == 6) { # Price
                                            $output=(float) $output;
                                            $output = number_format($output, 2, '.', '');
                                        } elseif ($value22 == 7) { # Delete Space
                                            $output = trim($output);
                                        } elseif ($value22 == 8) { # CDATA
                                            $output = '<![CDATA[' . $output . ']]>';
                                        }elseif ($value22 == 9) {
                                            $output=$this->stripInvalidXml($output);
                                            //$output = preg_replace( '/[^[:print:]]/',' ',$output);
                                        }else if($value22 == 10){
                                            //$output=preg_replace("/\[[^]]+\]/",'',$output);
                                            $output=$this->productClass->remove_short_codes($output);
                                        }
                                    }
                                }

                                # Format Output According to output limit
                                if (!empty($output) && !empty($rules['limit']) && is_numeric($rules['limit']) && strpos($output, "<![CDATA[") !== false) {
                                    $output = str_replace(array("<![CDATA[", "]]>"), array('', ''), $output);
                                    $output = substr($output, 0, $rules['limit']);
                                    $output = '<![CDATA[' . $output . ']]>';
                                } elseif (!empty($output) && !empty($rules['limit']) && is_numeric($rules['limit'])) {
                                    $output = substr($output, 0, $rules['limit']);
                                }

                                # Prefix and Suffix Assign
                                if (strpos($output, "<![CDATA[") !== false) {
                                    $output = str_replace(array("<![CDATA[", "]]>"), array('', ''), $output);
                                    if('http' === substr($output, 0, 4 )){
                                        $output = $this->make_url_with_parameter($rules['prefix'],$output,$rules['suffix']);
                                    }else{
                                        if($attr=='price' || $rules['value']=='sale_price' || $rules['value']=='current_price' && $output != ''){
	                                        $suffix=!empty($rules['suffix'])?trim($rules['suffix']):$rules['suffix'];
                                            $output = $rules['prefix'].$output.''.$suffix;
                                        }else{
                                            if($output != '')
                                            {
                                                $output = $rules['prefix'].$output.$rules['suffix'];
                                            }
                                        }
                                    }
                                    $output = '<![CDATA[' . $output . ']]>';

                                } else {
                                    if('http' === substr($output, 0, 4 )){
                                        $output = $this->make_url_with_parameter($rules['prefix'],$output,$rules['suffix']);
                                    }else{
                                        if($attr=='price'|| $rules['value']=='sale_price' || $rules['value']=='current_price' && $output != ''){
                                            $suffix=!empty($rules['suffix'])?trim($rules['suffix']):$rules['suffix'];
                                            $output = $rules['prefix'].$output.''.$suffix;
                                        }else{
                                            if($output != '')
                                            {
                                                $output = $rules['prefix'].$output.$rules['suffix'];
                                            }
                                        }
                                    }
                                }

                                if(!empty($output)){
                                    $output=stripslashes($output);
                                }

                            } elseif ($output=="0"){
                                $output="0";
                            } else{
                                $output='';
                            }

                            $attr = trim($attr);
                            $this->products[$key][$attr] = $output;
                        } else {
                            $output = str_replace("wf_pattern_", '', $rules['value']);
                             if(!empty($output)){
                                $output=stripslashes($output);
                            }
                            if ($output=="0"){
                                $output="0";
                            } else{
                                $output='';
                            }
                            $attr = trim($attr);
                            $this->products[$key][$attr] = $output;
                        }
                    }
                    $i++;
                }
            }
        }

	    return $this->products;
    }



    /**
     * Make proper URL using parameters
     * @param $prefix
     * @param $output
     * @param $suffix
     * @return string
     */

    public function make_url_with_parameter($prefix='',$output='',$suffix='')
    {
        $getParam=explode('?',$output);
        $URLParam=array();
        if(isset($getParam[1])){
            $URLParam=$this->proper_parse_str($getParam[1]);
        }

        $EXTRAParam=array();
        if(!empty($suffix)){
	        $suffix=str_replace("?",'',$suffix);
            $EXTRAParam=$this->proper_parse_str($suffix);
        }

        $params=array_merge($URLParam,$EXTRAParam);
        if(!empty($params) && $output != ''){
            $params=http_build_query($params);
            $baseURL=isset($getParam)?$getParam[0]:$output;
            $output = $prefix.$baseURL."?".$params;
        }else{
            if($output != '')
            {
                $output = $prefix.$output.$suffix;
            }
        }

        return $output;
    }

    /**
     * Parse URL parameter
     * @param $str
     * @return array
     */
    public function proper_parse_str($str='') {

        # result array
        $arr = array();

        if(empty($str)){
            return $arr;
        }

        # split on outer delimiter
        $pairs = explode('&', $str);

        if(!empty($pairs)){

            # loop through each pair
            foreach ($pairs as $i) {
                # split into name and value
                list($name,$value) = explode('=', $i, 2);

                # if name already exists
                if( isset($arr[$name]) ) {
                    # stick multiple values into an array
                    if( is_array($arr[$name]) ) {
                        $arr[$name][] = $value;
                    }else {
                        $arr[$name] = array($arr[$name], $value);
                    }
                }
                # otherwise, simply stick it in a scalar
                else {
                    $arr[$name] = $value;
                }
            }
        }elseif (!empty($str)){
            list($name,$value) = explode('=', $str, 2);
            $arr[$name] = $value;
        }

        # return result array
        return $arr;
    }

    /**
     * Responsible to make XML feed header
     * @return string
     */
    public function get_xml_feed_header()
    {
        $itemsWrapper = $this->rules['itemsWrapper'];
        $extraHeader = $this->rules['extraHeader'];
        $output = '<?xml version="1.0" encoding="UTF-8" ?>
<' . $itemsWrapper . '>';
        $output .= "\n";
        if (!empty($extraHeader)) {
            $output .= $extraHeader;
            $output .= "\n";
        }

        return $output;
    }

    /**
     * Responsible to make XML feed body
     * @var array $items Product array
     * @return string
     */
    public function get_xml_feed_body()
    {
        $feed = '';
        $itemWrapper = $this->rules['itemWrapper'];
        //$feed .= $this->get_feed_header($itemsWrapper, $extraheader);
        if (!empty($this->storeProducts)) {
            foreach ($this->storeProducts as $each => $products) {
                $feed .= "      <" . $itemWrapper . ">";
                foreach ($products as $key => $value) {
                    if (!empty($value)) {
                        $feed .= $value;
                    }
                }
                $feed .= "\n      </" . $itemWrapper . ">\n";
            }
            //$feed .= $this->get_feed_footer($itemsWrapper);

            return $feed;
        }

        return false;
    }

    /**
     * Responsible to make XML feed footer
     * @var $wrapper
     * @return string
     */
    public function get_xml_feed_footer()
    {
        $wrapper = $this->rules['itemsWrapper'];
        $footer = "  </$wrapper>";

        return $footer;
    }

    /**
     * Process string for CSV
     * @param $string
     * @return mixed|string
     */
    public function processStringForCSV($string){
        if(!empty($string)){
            $string = str_replace( "\n", ' ', $string );
            $string = str_replace( "\r", ' ', $string );
            $string =trim($string);
            $string = stripslashes($string);
            return $string;
        }else if($string=="0"){
            return "0";
        }else{
         return '';
        }
    }

    /**
     * Process string for TXT
     * @param $string
     * @return mixed|string
     */
    public function processStringForTXT($string){
        if(!empty($string)){
            $string = html_entity_decode( $string, ENT_HTML401 | ENT_QUOTES ); // Convert any HTML entities

            if (stristr( $string,'"' ) ) {
                $string = str_replace( '"', '""', $string );
            }
            $string = str_replace( "\n", ' ', $string );
            $string = str_replace( "\r", ' ', $string );
            $string = str_replace( "\t", ' ', $string );
            $string = trim($string);
            $string = stripslashes($string);
            return $string;
        }else if($string=="0"){
            return "0";
        }else{
            return '';
        }
    }


    /**
     * Responsible to make TXT feed
     * @return string
     */
    public function get_txt_feed()
    {
        if (!empty($this->storeProducts)) {

            if ($this->rules['delimiter'] == 'tab') {
                $delimiter = "\t";
            } else {
                $delimiter = $this->rules['delimiter'];
            }

            if (!empty($this->rules['enclosure'])) {
                $enclosure = $this->rules['enclosure'];
                if ($enclosure == 'double') {
                    $enclosure = '"';
                } elseif ($enclosure == 'single') {
                    $enclosure = "'";
                } else {
                    $enclosure = '';
                }
            } else {
                $enclosure = '';
            }


            if (!empty($this->storeProducts)) {
                if(!empty($this->rules['extraHeader'])){
                    $headers=explode("\n",$this->rules['extraHeader']);
                    foreach($headers as $header){
                        $header =trim(preg_replace('/\s+/', ' ', $header));
                        $feed[]=explode(',',$header);
                    }
                }
                $headers = array_keys($this->storeProducts[key($this->storeProducts)]);
                $feed[] = $headers;
                foreach ($this->storeProducts as $no => $product) {
                    $row = array();
                    foreach ($headers as $key => $header) {
                        $row[] = isset($product[$header]) ? $this->processStringForTXT($product[$header]):'';
                    }
                    $feed[] = $row;
                }
                $str = '';
                $i=1;
                foreach ($feed as $fields) {
                    if($i==1){
                        $this->txtFeedHeader= $enclosure . implode("$enclosure$delimiter$enclosure", $fields) . $enclosure . "\n";
                    }else{
                        $str .= $enclosure . implode("$enclosure$delimiter$enclosure", $fields) . $enclosure . "\n";
                    }
                    $i++;
                }
                return $str;
            }
        }
        return false;
    }

    /**
     * Responsible to make CSV feed
     * @return string
     */
    public function get_csv_feed()
    {
        if (!empty($this->storeProducts)) {
            $feed=array();
            if(!empty($this->rules['extraHeader'])){
                $headers=explode("\n",$this->rules['extraHeader']);
                foreach($headers as $header){
                    $header =trim(preg_replace('/\s+/', ' ', $header));
                    if(!empty($header))
                    $feed[]=explode(',',$header);
                }
            }
            $headers = array_keys($this->storeProducts[key($this->storeProducts)]);
            $this->csvFeedHeader = $headers;
            foreach ($this->storeProducts as $no => $product) {
                $row = array();
                foreach ($headers as $key => $header) {
                    if($this->rules['provider'] == 'spartoo.fi' && $header == "Parent / Child") {
                        $_value = isset($product[$header])?$this->processStringForCSV($product[$header]): '';
                        if($_value == 'variation') {
                            $row[] = 'child';
                        } else {
                            $row[] = 'parent';
                        }
                    } else {
                        $row[] = isset($product[$header])?$this->processStringForCSV($product[$header]): "";
                    }
                }
                $feed[] = $row;
            }
            return $feed;
        }
        return false;
    }
}