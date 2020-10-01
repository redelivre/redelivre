<?php /** @noinspection PhpUnusedPrivateMethodInspection, PhpUnused, PhpUnusedPrivateFieldInspection, PhpUnusedLocalVariableInspection, DuplicatedCode */

/**
 * Class Custom
 *
 * Responsible for processing and generating custom feed
 *
 * @since 1.0.0
 * @package Shopping
 *
 */
class Woo_Feed_Custom {
    /**
     * This variable is responsible for holding all product attributes and their values
     *
     * @since   1.0.0
     * @var     Woo_Feed_Products_v3 $products Contains all the product attributes to generate feed
     * @access  public
     */
    public $products;

    /**
     * This variable is responsible for holding feed configuration form values
     *
     * @since   1.0.0
     * @var     array $rules Contains feed configuration form values
     * @access  public
     */
    public $rules;
	
	
	/**
     * Store product information
     *
     * @since   1.0.0
     * @var     array $storeProducts
     * @access  public
     */
    private $storeProducts;

    /**
     * Define the core functionality to generate feed.
     *
     * Set the feed rules. Map products according to the rules and Check required attributes
     * and their values according to merchant specification.
     * @var Woo_Generate_Feed $feedRule Contain Feed Configuration
     * @since    1.0.0
     */
    public function __construct( $feedRule ) {
	    $this->products = new Woo_Feed_Products_v3( $feedRule );
	    // When update via cron job then set productIds.
	    if ( ! isset($feedRule['productIds']) ) {
		    $feedRule['productIds'] = $this->products->query_products();
	    }
	    $this->products->get_products($feedRule['productIds']);
	    $this->rules = $feedRule;

// $products = new Woo_Feed_Products();
// $limit =isset($feedRule['Limit'])?esc_html($feedRule['Limit']):'';
// $offset = isset($feedRule['Offset'])?esc_html($feedRule['Offset']):'';
// $categories = isset($feedRule['categories']) ? $feedRule['categories']: '';
// $storeProducts = $products->woo_feed_get_visible_product($limit, $offset,$categories,$feedRule);
// $feedRule=$products->feedRule;
// $engine = new WF_Engine($storeProducts,$feedRule);
// $this->products = $engine->mapProductsByRules();
// $this->rules = $feedRule;
// if ($feedRule['feedType'] == 'xml') {
// $this->mapAttributeForXML();
// }
    }

    /**
     * Prepare Feed For XML Output
     *
     */
    public function mapAttributeForXML() {
        if ( $this->products ) {
            foreach ( $this->products as $no => $product ) {
                foreach ( $product as $key => $value ) {
                    $this->products[ $no ][ $key ] = $this->formatXMLLine($key, $value);
                }
            }
        }
    }

    /**
     * Format and Make the XML node for the Feed
     *
     * @param $attribute
     * @param $value
     * @param string    $space
     * @return string
     */
    function formatXMLLine( $attribute, $value, $space = '' ) {
        $attribute = str_replace( ' ', '_', $attribute );
        // Make child node for XML
	    if ( ! empty( $value ) )
	        $value = trim( $value );
        if ( false === strpos($value, '<![CDATA[' ) && 'http' == substr(trim($value), 0, 4) ) {
            $value = "<![CDATA[$value]]>";
        } elseif ( false === strpos( $value, '<![CDATA[' ) && ! is_numeric( trim( $value ) ) && ! empty( $value ) ) {
            $value = "<![CDATA[$value]]>";
        }
        if ( 'myshopping.com.au' == $this->rules['provider'] && 'Category' == $attribute ) {
            return "$space<$attribute xml:space=\"preserve\">$value</$attribute>";
        }
        return "
        $space<$attribute>$value</$attribute>";
    }

    /**
     * Return Feed
     *
     * @return array|bool|string
     */
    public function returnFinalProduct() {
	    if ( ! empty($this->products) ) {
		    if ( 'xml' == $this->rules['feedType'] ) {
			    $feed = array(
				    'body'   => $this->products->feedBody,
				    'header' => $this->products->feedHeader,
				    'footer' => $this->products->feedFooter,
			    );
			    return $feed;
		    } elseif ( 'txt' == $this->rules['feedType'] ) {
			    $feed = array(
				    'body'   => $this->products->feedBody,
				    'header' => $this->products->feedHeader,
				    'footer' => '',
			    );
			    return $feed;
		    } elseif ( 'csv' == $this->rules['feedType'] ) {
			    $feed = array(
				    'body'   => $this->products->feedBody,
				    'header' => $this->products->feedHeader,
				    'footer' => '',
			    );
			    
			    return $feed;
		    }
	    }

        $feed = array(
            'body'   => '',
            'header' => '',
            'footer' => '',
        );
        return $feed;
    }

    public function get_header( $engine ) {
        $datetime_now = date('Y-m-d H:i:s', strtotime( current_time( 'mysql' ) ) );
        if ( 'fruugo.au' == $this->rules['provider'] ) {
            $fruugo_au = "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo('name') . '</title>
                <link>' . get_bloginfo('url') . '</link>
                <description>' . get_bloginfo('description') . '</description>';
            return $fruugo_au;
        } elseif ( 'zap.co.il' == $this->rules['provider'] ) {
            $zap = "<STORE>
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo('name') . '</title>
                <link>' . get_bloginfo('url') . '</link>
                <description>' . get_bloginfo('description') . '</description>
                <agency>' . get_bloginfo('name') . '</agency>
                <email>' . get_bloginfo('admin_email') . '</email>';
            return $zap;
        } elseif ( 'myshopping.com.au' == $this->rules['provider'] ) {
            return '<productset>';
        } elseif ( 'stylight.com' == $this->rules['provider'] ) {
            return "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo('name') . '</title>
                <link>' . get_bloginfo('url') . '</link>
                <description>' . get_bloginfo('description') . '</description>';
        } elseif ( 'nextad' == $this->rules['provider'] ) {
            return "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo('name') . '</title>
                <link>' . get_bloginfo('url') . '</link>
                <description>' . get_bloginfo('description') . '</description>';
        } elseif ( 'skinflint.co.uk' == $this->rules['provider'] ) {
            return "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo('name') . '</title>
                <link>' . get_bloginfo('url') . '</link>
                <description>' . get_bloginfo('description') . '</description>';
        } elseif ( 'comparer.be' == $this->rules['provider'] ) {
            return "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo('name') . '</title>
                <link>' . get_bloginfo('url') . '</link>
                <description>' . get_bloginfo('description') . '</description>';
        } elseif ( 'dooyoo' == $this->rules['provider'] ) {
            return "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo('name') . '</title>
                <link>' . get_bloginfo('url') . '</link>
                <description>' . get_bloginfo('description') . '</description>';
        } elseif ( 'hintaseuranta.fi' == $this->rules['provider'] ) {
            return "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo('name') . '</title>
                <link>' . get_bloginfo('url') . '</link>
                <description>' . get_bloginfo('description') . '</description>';
        } elseif ( 'incurvy' == $this->rules['provider'] ) {
            return "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo('name') . '</title>
                <link>' . get_bloginfo('url') . '</link>
                <description>' . get_bloginfo('description') . '</description>';
        } elseif ( 'kijiji.ca' == $this->rules['provider'] ) {
            return "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo('name') . '</title>
                <link>' . get_bloginfo('url') . '</link>
                <description>' . get_bloginfo('description') . '</description>';
        } elseif ( 'marktplaats.nl' == $this->rules['provider'] ) {
            return "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo('name') . '</title>
                <link>' . get_bloginfo('url') . '</link>
                <description>' . get_bloginfo('description') . '</description>';
        } elseif ( 'rakuten.de' == $this->rules['provider'] ) {
            return "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo('name') . '</title>
                <link>' . get_bloginfo('url') . '</link>
                <description>' . get_bloginfo('description') . '</description>';
        } elseif ( 'shopalike.fr' == $this->rules['provider'] ) {
            return "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo('name') . '</title>
                <link>' . get_bloginfo('url') . '</link>
                <description>' . get_bloginfo('description') . '</description>';
        } elseif ( 'spartoo.fi' == $this->rules['provider'] ) {
            return "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo('name') . '</title>
                <link>' . get_bloginfo('url') . '</link>
                <description>' . get_bloginfo('description') . '</description>';
        } elseif ( 'webmarchand' == $this->rules['provider'] ) {
            return "<products version=\"1.0\" standalone=\"yes\">
                <datetime>$datetime_now</datetime>
                <title>" . get_bloginfo('name') . '</title>
                <link>' . get_bloginfo('url') . '</link>
                <description>' . get_bloginfo('description') . '</description>';
        } else {
            return $engine->get_xml_feed_header();
        }
    }

    public function get_footer( $engine ) {
        if ( in_array($this->rules['provider'], [ 'fruugo.au', 'stylight.com', 'nextad', 'skinflint.co.uk', 'comparer.be', 'dooyoo', 'hintaseuranta.fi', 'incurvy', 'kijiji.ca', 'marktplaats.nl', 'rakuten.de', 'shopalike.fr', 'spartoo.fi', 'webmarchand' ]) ) {
            return '</products>';
        } elseif ( 'zap.co.il' == $this->rules['provider'] ) {
            return '</STORE>';
        } elseif ( 'myshopping.com.au' == $this->rules['provider'] ) {
            return '</productset>';
        } else {
            return $engine->get_xml_feed_footer();
        }
    }
}
