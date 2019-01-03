<?php
// don't load directly 
if ( !defined('ABSPATH') ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if (!class_exists('WPeMatico_XML_Importer')) :
class WPeMatico_XML_Importer {
	
	public static $xmlnodes = array();
	public static $xmlreturn = array();

    public static function hooks() {
        add_action('wp_ajax_wpematico_xml_check_data', array( __CLASS__, 'ajax_xml_check_data'));
        add_filter('Wpematico_process_fetching', array(__CLASS__, 'process_fetching'), 10, 1);

        add_filter('wpematico_get_item_images', array(__CLASS__, 'featured_image'), 10, 5);

        $options = get_option( WPeMatico::OPTION_KEY );
        if ( ! empty( $options['enable_xml_upload'] ) ) {

            add_filter( 'post_mime_types', array(__CLASS__, 'modify_post_mime_types') );
            add_filter( 'mime_types', array(__CLASS__, 'add_mime_types') );

        }
        
        
    }
    public static function featured_image($current_item, $campaign, $item, $options_images) {
        if ($campaign['campaign_type'] == 'xml') {
            if ( ! empty( $item->get_post_meta('image') ) ) {
                $current_item['images'][] = $item->get_post_meta('image');
            }
        }
        return $current_item;
    }
    public static function modify_post_mime_types($post_mime_types) {
        $post_mime_types['application/xml'] = array( __( 'XMLs' ), __( 'Manage XMLs' ), _n_noop( 'XML <span class="count">(%s)</span>', 'XMLs <span class="count">(%s)</span>' ) );
        return $post_mime_types;
    }
    public static function add_mime_types($mime_types) {
        $mime_types['xml'] = 'application/xml';
        return $mime_types;
    }
    public static function process_fetching($campaign) {
        
        if (is_array($campaign) && $campaign['campaign_type'] == 'xml') {
            
            if ( ! class_exists('Blank_SimplePie')) {
                require_once(WPEMATICO_PLUGIN_DIR. 'app/lib/blank-simplepie.php');
            }
            
            $simplepie = new Blank_SimplePie( $campaign['campaign_xml_feed_url'], 'WPeMatico XML Campaign Type', 'WPeMatico XML Campaign Type');
            
            $data_xml = WPeMatico::wpematico_get_contents( $campaign['campaign_xml_feed_url'], true );
            if ( ! empty( $data_xml ) ) {
                $xml = @simplexml_load_string( $data_xml, 'SimpleXMLElement', LIBXML_NOCDATA );

                $campaign_xml_node  = $campaign['campaign_xml_node'];
                $campaign_xml_node_parent  = $campaign['campaign_xml_node_parent'];


                $xpath_title                = ( !empty( $campaign_xml_node['post_title'] ) ? $campaign_xml_node['post_title'] : '' );
                $xpath_content              = ( !empty( $campaign_xml_node['post_content'] ) ? $campaign_xml_node['post_content'] : '' );
                $xpath_permalink            = ( !empty( $campaign_xml_node['post_permalink'] ) ? $campaign_xml_node['post_permalink'] : '' );
                $xpath_date                 = ( !empty( $campaign_xml_node['post_date'] ) ? $campaign_xml_node['post_date'] : '' );
                $xpath_author               = ( !empty( $campaign_xml_node['post_author'] ) ? $campaign_xml_node['post_author'] : '' );
                $xpath_image                = ( !empty( $campaign_xml_node['post_image'] ) ? $campaign_xml_node['post_image'] : '' );

                $xpath_parent_title         = ( !empty( $campaign_xml_node_parent['post_title'] ) ? $campaign_xml_node_parent['post_title'] : '' );
                $xpath_parent_content       = ( !empty( $campaign_xml_node_parent['post_content'] ) ? $campaign_xml_node_parent['post_content'] : '' );
                $xpath_parent_permalink     = ( !empty( $campaign_xml_node_parent['post_permalink'] ) ? $campaign_xml_node_parent['post_permalink'] : '' );
                $xpath_parent_date          = ( !empty( $campaign_xml_node_parent['post_date'] ) ? $campaign_xml_node_parent['post_date'] : '' );
                $xpath_parent_author        = ( !empty( $campaign_xml_node_parent['post_author'] ) ? $campaign_xml_node_parent['post_author'] : '' );
                $xpath_parent_image         = ( !empty( $campaign_xml_node_parent['post_image'] ) ? $campaign_xml_node_parent['post_image'] : '' );


                if ( ! empty( $xpath_title ) ) {


                    $nodes_title            =  ( ! empty($xpath_parent_title) ? $xml->xpath( $xpath_parent_title ) : $xml->xpath( $xpath_title ) );
                    $nodes_content          =  ( ! empty($xpath_parent_content) ? $xml->xpath( $xpath_parent_content ) : ( ! empty( $xpath_content ) ? $xml->xpath( $xpath_content ) : array() )  );  
                    $nodes_permalink        =  ( ! empty($xpath_parent_permalink) ? $xml->xpath( $xpath_parent_permalink ) : ( ! empty( $xpath_permalink ) ? $xml->xpath( $xpath_permalink ) : array() )  );  
                    $nodes_date             =  ( ! empty($xpath_parent_date) ? $xml->xpath( $xpath_parent_date ) : ( ! empty( $xpath_date ) ? $xml->xpath( $xpath_date ) : array() )  );  
                    $nodes_author           =  ( ! empty($xpath_parent_author) ? $xml->xpath( $xpath_parent_author ) : ( ! empty( $xpath_author ) ? $xml->xpath( $xpath_author ) : array() )  );  
                    $nodes_image            =  ( ! empty($xpath_parent_image) ? $xml->xpath( $xpath_parent_image ) : ( ! empty( $xpath_image ) ? $xml->xpath( $xpath_image ) : array() )  );  

                   

                    foreach ($nodes_title as $key_node_title => $node_title) {
                        
                        $new_title = '';
                        if ( ! empty($xpath_parent_title) ) {
                            $child_xpath_title      = str_replace($xpath_parent_title.'/', '', $xpath_title);
                            $child_nodes_title      = $node_title->xpath($child_xpath_title);
                            $new_title              = (string)array_shift($child_nodes_title);
                            if ( empty($new_title) ) {
                                $new_title          = '';
                            }
                        } else {
                            $new_title              = (string)$node_title;
                        }

                        $new_content = '';
                        if ( ! empty($xpath_parent_content) ) {
                            $child_xpath_content    = str_replace($xpath_parent_content.'/', '', $xpath_content);
                            $child_nodes_content    = $nodes_content[$key_node_title]->xpath($child_xpath_content);
                            $new_content            = (string)array_shift($child_nodes_content);
                            if ( empty($new_content) ) {
                                $new_content        = '';
                            }
                        } else {
                            $new_content            = (string)( ! empty( $nodes_content[$key_node_title] ) ? $nodes_content[$key_node_title] : '' );
                        }

                        $new_permalink = sanitize_title($new_title);
                        if ( ! empty($xpath_parent_permalink) ) {
                            $child_xpath_permalink  = str_replace($xpath_parent_permalink.'/', '', $xpath_permalink);
                            $child_nodes_permalink  = ( ! empty($nodes_permalink[$key_node_title]) ? $nodes_permalink[$key_node_title]->xpath($child_xpath_permalink) : array() );
                            $new_permalink          = (string)array_shift($child_nodes_permalink);
                            if ( empty($new_permalink) ) {
                                $new_permalink      = sanitize_title($new_title);
                            }
                        } else {
                            $new_permalink          = (string)( ! empty( $nodes_permalink[$key_node_title] ) ? $nodes_permalink[$key_node_title] : sanitize_title($new_title) );
                        }

                          
                        $new_date = '';
                        if ( ! empty($xpath_parent_date) ) {
                            $child_xpath_date       = str_replace($xpath_parent_date.'/', '', $xpath_date);
                            $child_nodes_date       = ( ! empty($nodes_date[$key_node_title]) ? $nodes_date[$key_node_title]->xpath($child_xpath_date) : array() ) ;
                            $new_date               = (string)array_shift($child_nodes_date);
                            if ( empty($new_date) ) {
                                $new_date           = '';
                            }
                        } else {
                            $new_date               = (string)( ! empty( $nodes_date[$key_node_title] ) ? $nodes_date[$key_node_title] : '' );
                        }  


                        $new_author = '';
                        if ( ! empty($xpath_parent_author) ) {
                            $child_xpath_author     = str_replace($xpath_parent_author.'/', '', $xpath_author);
                            $child_nodes_author     = ( ! empty($nodes_author[$key_node_title]) ? $nodes_author[$key_node_title]->xpath($child_xpath_author) : array() );
                            $new_author             = (string)array_shift($child_nodes_author);
                            if ( ! empty($new_author) ) {
                                $new_author         = new Blank_SimplePie_Item_Author($new_author);
                            } else {
                                $new_author         = '';
                            }
                        } else {
                            $new_author             = (string)( ! empty( $nodes_author[$key_node_title] ) ? new Blank_SimplePie_Item_Author($nodes_author[$key_node_title])  : '' );
                        }

                        $new_image = '';
                        if ( ! empty($xpath_parent_image) ) {
                            $child_xpath_image      = str_replace($xpath_parent_image.'/', '', $xpath_image);
                            $child_nodes_image      = $nodes_image[$key_node_title]->xpath($child_xpath_image);
                            $new_image              = (string)array_shift($child_nodes_image);
                            if ( empty($new_image) ) {
                                $new_image          = '';
                            }
                        } else {
                            $new_image              = (string)( ! empty( $nodes_image[$key_node_title] ) ? $nodes_image[$key_node_title] : '' );
                        }

                        
                        
                        $new_simplepie_item = new Blank_SimplePie_Item( $new_title, $new_content, $new_permalink, $new_date, $new_author );
                        $new_simplepie_item->set_post_meta('image', $new_image);
                        $new_simplepie_item->set_feed($simplepie);
                        $simplepie->addItem( $new_simplepie_item );
                   
                    }
                    

                }


            }
            error_log(print_r($simplepie, true));
            return $simplepie;
        }
        return $campaign;
    }

	public static function metabox( $post ) {
		global $post, $campaign_data, $helptip;
		$campaign_xml_feed_url = $campaign_data['campaign_xml_feed_url'];
        $campaign_xml_node = $campaign_data['campaign_xml_node'];
        
		?>
		<label for="campaign_xml_feed_url"><?php _e('URL of XML', 'wpematico' ); ?></label>
        <input type="text" class="regular-text" id="campaign_xml_feed_url" value="<?php echo $campaign_xml_feed_url; ?>" name="campaign_xml_feed_url">
        
		<div class="xml-campaign-check-data-container">
			<br>
            <button class="button" type="button" id="xml-campaign-check-data-btn"><?php _e('Check data', 'wpematico' ); ?></button>
        </div>

        <div id="xml-campaign-input-nodes-container" <?php echo ( empty($campaign_xml_node) ? 'style="display:none;"' : ''); ?>>
            <?php if ( ! empty( $campaign_xml_node ) ) {
                self::get_xml_input_nodes($campaign_data);
            }
            ?>


        </div>   
        <?php
	}

    public static function ajax_xml_check_data() {
        $nonce = '';
        if (isset($_REQUEST['nonce'])) {
            $nonce = $_REQUEST['nonce'];
        }
        
        if (!wp_verify_nonce($nonce, 'wpematico-xml-check-data-nonce')) {
            wp_die('Security check'); 
        }

        $xml_url = ( !empty( $_REQUEST['xml_feed'] ) ? $_REQUEST['xml_feed'] : '' ); 

        if ( empty( $xml_url ) ) {
            wp_die('Error: Empty feed URL');
        }
        $campaign_data = array(
            'campaign_xml_feed_url'     => $xml_url,
            'campaign_xml_node'         => array(),
            'campaign_xml_node_parent'  => array(),
        );
        self::get_xml_input_nodes( $campaign_data );
        die();
    }

    public static function get_xml_input_nodes($campaign_data) {
        global $helptip;
        if ( empty($helptip) ) {
            if ( ! defined('WPEMATICO_AJAX') ) {
                define('WPEMATICO_AJAX', true);
            }
            require( WPEMATICO_PLUGIN_DIR . '/app/campaign_help.php' );
        }
        $campaign_xml_feed_url = $campaign_data['campaign_xml_feed_url'];
        $campaign_xml_node = $campaign_data['campaign_xml_node'];
        $campaign_xml_node_parent = $campaign_data['campaign_xml_node_parent'];
        
        $data_xml = WPeMatico::wpematico_get_contents( $campaign_xml_feed_url, true );


        if ( stripos($data_xml, '<atom:link') !== false ||  stripos($data_xml, 'http://www.w3.org/2005/Atom') !== false || stripos($data_xml, 'application/rss+xml') !== false  ) {
             wp_die(__('The file is a RSS feed that must use <strong>Feed Fetcher</strong> campaign type instead of XML.', 'wpematico')); 
        }


        if ( ! empty( $data_xml ) ) {
            $xml = @simplexml_load_string( $data_xml, 'SimpleXMLElement', LIBXML_NOCDATA  );
            self::recurse_xml($xml);
        }
        
        ?>
        <br>
        <table class="table_check_data_xml">
            <tr>
                <td><strong><?php _e('Properties', 'wpematico' ); ?></strong></td>
                <td><strong><?php _e('Elements of XML', 'wpematico' ); echo '<span class="dashicons dashicons-warning help_tip" title-heltip="'.$helptip['Elements of XML'].'"  title="'. $helptip['Elements of XML'].'"></span>'; ?></strong></td>
                <td><strong><?php _e('Parent Element', 'wpematico' ); echo '<span class="dashicons dashicons-warning help_tip" title-heltip="'.$helptip['Parent Element'].'"  title="'. $helptip['Parent Element'].'"></span>'; ?></strong></td>
                
            </tr>
            <tr>
                <td><?php _e('Post title', 'wpematico' ); ?></td>
                <td><?php self::get_select_node_html('post_title', ( !empty( $campaign_xml_node['post_title'] ) ? $campaign_xml_node['post_title'] : '' )  ); ?></td>
                <td><?php self::get_select_node_html('post_title', ( !empty( $campaign_xml_node_parent['post_title'] ) ? $campaign_xml_node_parent['post_title'] : '' ),  'campaign_xml_node_parent', false  ); ?></td>
            </tr>
            <tr>
                <td><?php _e('Post content', 'wpematico' ); ?></td>
                <td><?php self::get_select_node_html('post_content', ( !empty( $campaign_xml_node['post_content'] ) ? $campaign_xml_node['post_content'] : '' )  ); ?></td>
                <td><?php self::get_select_node_html('post_content', ( !empty( $campaign_xml_node_parent['post_content'] ) ? $campaign_xml_node_parent['post_content'] : '' ),  'campaign_xml_node_parent', false  ); ?></td>
               
            </tr>

            <tr>
                <td><?php _e('Post permalink', 'wpematico' ); ?></td>
                <td><?php self::get_select_node_html('post_permalink', ( !empty( $campaign_xml_node['post_permalink'] ) ? $campaign_xml_node['post_permalink'] : '' )  ); ?></td>
                <td><?php self::get_select_node_html('post_permalink', ( !empty( $campaign_xml_node_parent['post_permalink'] ) ? $campaign_xml_node_parent['post_permalink'] : '' ),  'campaign_xml_node_parent', false  ); ?></td>
            </tr>

            <tr>
                <td><?php _e('Post date', 'wpematico' ); ?></td>
                <td><?php self::get_select_node_html('post_date', ( !empty( $campaign_xml_node['post_date'] ) ? $campaign_xml_node['post_date'] : '' )  ); ?></td>
                <td><?php self::get_select_node_html('post_date', ( !empty( $campaign_xml_node_parent['post_date'] ) ? $campaign_xml_node_parent['post_date'] : '' ),  'campaign_xml_node_parent', false  ); ?></td>
            </tr>

            <tr>
                <td><?php _e('Post author', 'wpematico' ); ?></td>
                <td><?php self::get_select_node_html('post_author', ( !empty( $campaign_xml_node['post_author'] ) ? $campaign_xml_node['post_author'] : '' )  ); ?></td>
                <td><?php self::get_select_node_html('post_author', ( !empty( $campaign_xml_node_parent['post_author'] ) ? $campaign_xml_node_parent['post_author'] : '' ),  'campaign_xml_node_parent', false  ); ?></td>
            </tr>
            <tr>
                <td><?php _e('Post image', 'wpematico' ); ?></td>
                <td><?php self::get_select_node_html('post_image', ( !empty( $campaign_xml_node['post_image'] ) ? $campaign_xml_node['post_image'] : '' )  ); ?></td>
                <td><?php self::get_select_node_html('post_image', ( !empty( $campaign_xml_node_parent['post_image'] ) ? $campaign_xml_node_parent['post_image'] : '' ),  'campaign_xml_node_parent', false  ); ?></td>

            
            </tr>

            
        </table>
        <?php
    }

    public static function get_select_node_html($input, $value, $input_name = 'campaign_xml_node', $use_atributes = true) {
        ?>
        <select name="<?php echo $input_name; ?>[<?php echo $input; ?>]" id="<?php echo $input_name; ?><?php echo $input; ?>" class="<?php echo $input_name; ?>">
            <option value=""><?php _e('Select a XML node please.', 'wpematico' ); ?></option>
            <?php
            $first_node_select = "";
            foreach ( self::$xmlnodes as $nodekey => $nodecount ) : ?>
                <?php if ( $first_node_select == '' ) : $first_node_select = $nodecount['key']; endif; ?>
                <option value="<?php echo $nodecount['key']; ?>" <?php selected($nodecount['key'], $value, true); ?> >
                    <?php echo $nodecount['name'].' ('.$nodecount['count'].') '.$nodecount['key'].''; ?>
                </option>

            <?php
                if ( $use_atributes ) :   
                    foreach ( self::$xmlnodes[$nodekey]['attributes'] as $atr_key => $attr ) : 
                ?>
                        <option value="<?php echo $nodecount['key']. '/@'. $atr_key; ?>" <?php selected($nodecount['key'] . '/@'. $atr_key, $value, true); ?> >
                            <?php echo '- ' . $nodecount['name'].' ('.$nodecount['count'].') '.$nodecount['key']. '/@'. $atr_key; ?>
                        </option>
            <?php
                    endforeach;
                endif;
            endforeach;
            ?>
        </select>
        <?php
    }

	private static function recurse_xml( $xml , $parent = "" ) {
        $child_count = 0;
        if ( ! empty($xml->children()) ) {

            foreach( $xml->children() as $key => $value ) :
                $child_count++;

                    $name = $value->getName();
                    $current_key = ( empty($parent) ?  (string)$key : $parent . "/" . (string)$key );
                    $count = ( isset( self::$xmlnodes[$current_key]['count'] )  ? self::$xmlnodes[$current_key]['count'] + 1  : 1); 
                    
                    self::$xmlnodes[$current_key] = array(
                        'count'         =>  $count,
                        'name'          =>  $name,
                        'attributes'    =>  $value->attributes(),
                        'key'           =>  $current_key,
                    );
                    
                
                // No childern, aka "leaf node".
                if( self::recurse_xml( $value , $current_key ) == 0 ) {
                    self::$xmlreturn[] = array(
                        'key'           =>  $parent . "/" . (string)$key,
                        'attributes'    =>  $value->attributes(),
                        'value'         =>  maybe_unserialize( htmlspecialchars( $value ) )
                    );
                }
            endforeach;

        }
        
       return $child_count;
    }


}
endif;
WPeMatico_XML_Importer::hooks();

?>