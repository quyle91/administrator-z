<?php
namespace Adminz\Admin;
use Adminz\Admin\Adminz as Adminz;
use DOMDocument;
use DOMXpath;
use WC_Product;
use WC_Product_Variable;
use WC_Product_Attribute;
use WC_Product_Variation;
use WC_Product_Grouped;
use WC_Product_External;
use WC_Product_Simple;
use WP_Query;
/**
 *
 */
class ADMINZ_Import extends Adminz {
    public $options_group = "adminz_import";
    public $title = 'Import';
    public $slug = 'adminz_import';
    function __construct() {        
        add_action('admin_init', [$this, 'register_option_setting']);
        add_filter('adminz_setting_tab', [$this, 'register_tab']);        
        add_action('wp_ajax_test_single', [$this, 'test_single']);
        add_action('wp_ajax_test_category', [$this, 'test_category']);
        add_action('wp_ajax_test_product', [$this, 'test_product']);
        add_action('wp_ajax_test_category_product', [$this, 'test_category_product']);
        add_action('wp_ajax_run_import_single', [$this, 'run_import_single']);
        add_action('wp_ajax_run_import_single_product', [$this, 'run_import_single_product']);
        add_action('wp_ajax_run_import_category', [$this, 'run_import_category']);
    }
    function do_import_single($link = false){
        if(!$link){ $link = $_POST['link']; }
        $data = $this->get_single($link);        

        $post_args = array(
            'post_title'    => $data['post_title'],
            'post_status'   => 'publish',
            'post_author'   => get_current_user_id()
        );

        $post_id = wp_insert_post( $post_args, $wp_error );
        if(!$post_id){
            wp_send_json_success('<code>Cannot import!</code>');
            wp_die();
        }
        // query all image and save
        $post_thumbnails = $data['post_thumbnail'];
        $images_imported = [];
        if(!empty($post_thumbnails) and is_array($post_thumbnails)){
            foreach ($post_thumbnails as $key => $url) {
                $res = $this->save_images($url,$post_args['post_title']."-".$key);
                // set first image for thumbnail
                if($key ==0){
                    set_post_thumbnail( $post_id, $res['attach_id'] );  
                }
                $images_imported[$url] = $res['attach_id'];                
            }
        }           
        $data['post_content'] = $this->replace_img_content($link,$images_imported,$data['post_content']);
        $content_replaced = array(
            'ID'           => $post_id,
            'post_content' => $this->fix_content($data['post_content'])
        );        
        wp_update_post( $content_replaced );
        return $post_id;
    }
    function do_import_single_product($link = false) {
        if(!$link){ $link = $_POST['link']; }
        $data = $this->get_product($link);

        // first import all images and save to array temp
        $image_all = $data['images_all'];
        $images_imported = [];
        if(!empty($image_all) and is_array($image_all)){
            foreach ($image_all as $key => $url) {
                $res = $this->save_images($url,$data['post_title']."-".$key);
                if($res['attach_id']){
                    $images_imported[$url] = $res['attach_id'];
                    if(in_array($url, $data['images_gallery'])){
                        $gallery[] = $res['attach_id'];
                    }                    
                    if($url == $data['image_thumbnail']){
                        $product_thumbnail_id = $res['attach_id'];
                    }
                }
            }
        }
        // check produduct type and set product type data
        switch ($data['product_type']) {
            case 'external':
                $product  = new WC_Product_External();
                
                if($data['product_type_data']){
                    $product->set_product_url($data['product_type_data']);
                }
                if($data['_price']){
                    $product->set_regular_price($data['_price']);
                }
                if($data['_sale_price']){
                    $product->set_sale_price($data['_sale_price']);
                }
                break;
            case 'variable':
                $product  = new WC_Product_Variable();
                $variations_list = $data['variations_list'];
                $variations_data = $data['product_type_data'];
                $default_attribute = $data['default_attribute'];

                // attribute
                $attr_array = [];
                if (!empty($variations_list) and is_array($variations_list)){
                    
                    foreach ($variations_list as $key => $value) {
                        $attribute = new WC_Product_Attribute();                
                        $attribute->set_name( $value['attr_name'] );
                        if(!empty($value['attr_options']) and is_array($value['attr_options'])){
                            $option_arr = [];
                            foreach ($value['attr_options'] as $key => $value) {
                                $option_arr[] = $value;
                            }
                            $attribute->set_options($option_arr ); 
                        }
                                           
                        $attribute->set_visible( 1 );
                        $attribute->set_variation( 1 );
                        $attr_array[] = $attribute;
                    }
                }
                $product->set_attributes($attr_array);
                $product->set_default_attributes($default_attribute);

                // variations
                $product_id = $product->save();
                if(!empty($variations_data) and is_array($variations_data)){
                    foreach ($variations_data as $key => $value) {                        
                        $set_attrs = (array)$value->attributes;
                        $temp_set_attrs = [];
                        if(is_array($set_attrs) and !empty($set_attrs)){
                            foreach ($set_attrs as $key => $attr) {        
                                $key = str_replace("attribute_", "", $key);
                                $temp_set_attrs[$key] = $attr;
                            } 
                        }                                              
                        $variation = new WC_Product_Variation();
                        $variation->set_regular_price($value->display_regular_price);
                        $variation->set_sale_price($value->display_price);
                        $variation->set_parent_id($product_id);
                        $variation->set_attributes($temp_set_attrs);                        
                        if(in_array($value->image->url,array_keys($images_imported))){
                            $variation->set_image_id($images_imported[$value->image->url]);
                        }                   
                        $variation->save();
                    }
                }               

                break;
            case 'grouped':
                $product  = new WC_Product_Grouped();
                $data_type = $data['product_type_data'];
                $children = [];
                if(!empty($data_type)){
                    foreach ($data_type as $key => $child) {
                        if(!$child['exits']){
                            $child_id = $this->do_import_single_product($child['url']);
                        }else{
                            $child_id = $child['exits_id'];
                        }
                        $children[] = $child_id;
                    }
                    $product->set_children($children);
                    $product->sync($product);
                }                
                break;
            default:
                $product  = new WC_Product_Simple();

                if($data['_price']){
                    $product->set_regular_price($data['_price']);
                }
                if($data['_sale_price']){
                    $product->set_sale_price($data['_sale_price']);
                }
                break;
        }

        $product->set_name($data['post_title']);
        $product->set_status('publish');
        if($data['short_description']){
            $product->set_short_description($data['short_description']);
        }
        $product_id = $product->save();

        // content thumbnails
        if(isset($product_thumbnail_id)){
            $product->set_image_id($product_thumbnail_id);
            // unset if like product thumbnail
            if(!empty($gallery) and is_array($gallery)){
                foreach ($gallery as $key => $value) {
                    if($value == $product_thumbnail_id){
                        unset($gallery[$key]);
                    }
                }
            }        
        } 

        if(isset($gallery)){
            $product->set_gallery_image_ids($gallery);
        }

        // fix content image url
        $data['post_content'] = $this->replace_img_content($link,$images_imported,$data['post_content']);
        $content_replaced = array(
            'ID'           => $product_id,
            'post_content' => $this->fix_content($data['post_content'])
        ); 
        wp_update_post( $content_replaced );
        
        $product_id = $product->save();
        if(!$product_id){
            wp_send_json_success('<code>Cannot import!</code>');
            wp_die();
        }
        return $product_id;
    }
    function get_single($link){
        $html = $this->get_remote($link);
        $return = [];
        //start check
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXpath($doc);        

        $title_class = get_option('adminz_import_post_title', 'entry-title');
        $header_class = get_option('adminz_import_post_header_title', 'entry-header');
        if($title_class and $header_class){
            $title = $xpath->query("//*[contains(@class, '" . $header_class . "')]//*[contains(@class, '" . $title_class . "')]");
            if (!is_null($title)) {
                foreach ($title as $element) {
                    $nodes = $element->childNodes;
                    foreach ($nodes as $node) {
                        $return['post_title'] .= $this->fix_content($node->nodeValue, $link);
                    }
                }
            }
        }        

        // get entry image as first array
        $image_class = get_option('adminz_import_post_thumbnail', 'entry-image');
        if($image_class){
            $imgs = $xpath->query("//*[contains(@class, '" . $image_class . "')]//img");
            if (!is_null($imgs)) {
                foreach ($imgs as $element) {
                    if ($element->getAttribute('src')) {
                        $return['post_thumbnail'][] = $this->fix_url($element->getAttribute('src'),$link);
                        break;
                    }
                }
            }
        }        

        // get all image in entry-content
        $contentclass = get_option('adminz_import_post_content', 'entry-content');
        if($contentclass){
            $imgs = $xpath->query("//*[contains(@class, '" . $contentclass . "')]//img");
            if (!is_null($imgs)) {
                foreach ($imgs as $element) {
                    if ($element->getAttribute('src')) {                    
                        $return['post_thumbnail'][] = $this->fix_url($element->getAttribute('src'),$link);                    
                    }
                }
            }
        }

        $return['post_thumbnail'] = array_values(array_unique($return['post_thumbnail']));

        //post content
        if($contentclass){
            $content = $xpath->query("//*[contains(@class, '" . $contentclass . "')]");
            $remove_end = get_option('adminz_import_content_remove_end', 0);
            $remove_first = get_option('adminz_import_content_remove_first', 0);
            if (!is_null($content)) {
                foreach ($content as $element) {
                    $nodes = $element->childNodes;
                    foreach ($nodes as $key => $node) {
                        if ($key <= (count($nodes) - $remove_end - 1) and $key >= ($remove_first)) {
                            $return['post_content'] .= $this->fix_content($doc->saveHTML($node) , $link);
                        }
                    }
                }
            }
        }
             
        return $return;
    } 
    function get_product($link){
        $html = $this->get_remote($link);
        $return = [];
        //start check
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXpath($doc);


        $header_class = get_option('adminz_import_product_header_title', 'product-info');
        // product type
        $type = $xpath->query("//*[contains(@class, 'type-product')]/div");
        $return['product_type'] = "simple";
        $return['images_gallery'] = [];
        $return['image_thumbnail'] = "";
        $return['images_gallery'] = [];
        $return['images_variations'] = [];
        $return['images_content'] = [];
        if (!is_null($type)) {
            foreach ($type as $element) {               
                if($element->parentNode->getAttribute('id')){
                    $class = $element->parentNode->getAttribute('class');
                    $class = explode(" ", $class);
                    if(in_array("product-type-grouped", $class)){
                        $return['product_type']=  'grouped';
                    }
                    if(in_array("product-type-external", $class)){
                        $return['product_type']=  'external';
                    }
                    if(in_array("product-type-variable", $class)){
                        $return['product_type']=  'variable';
                    }
                }                
            }
        }
        switch ($return['product_type']) {
            case 'external':
                $single_add_to_cart_button = get_option( 'adminz_import_product_single_add_to_cart_button', 'single_add_to_cart_button' );
                if($single_add_to_cart_button and $header_class){
                    $external_url = $xpath->query("//*[contains(@class, '".$header_class."')]//*[contains(@class, '".$single_add_to_cart_button."')]");
                    if (!is_null($external_url)) {
                        foreach ($external_url as $external) {
                            $return['product_type_data'] = $external->parentNode->getAttribute('action');
                        }
                    }
                }                
                break;
            case 'variable':
                $variable_form_class = get_option( 'adminz_import_product_variations_json', 'variations_form' );
                if($variable_form_class and $header_class){
                    $variable_form = $xpath->query("//*[contains(@class, '".$header_class."')]//*[contains(@class, '".$variable_form_class."')]");
                    if (!is_null($variable_form)) {
                        foreach ($variable_form as $element) {
                            $data_variable = $element->getAttribute('data-product_variations');
                            $data_variable = json_decode( $data_variable );
                            $return['product_type_data'] = $data_variable;
                            if(!empty($data_variable) and is_array($data_variable)){
                                foreach ($data_variable as $key => $variable) {
                                    $return['images_variations'][] = $this->fix_url($variable->image->url,$link); 
                                }
                            }
                        }
                    }
                }                

                // list variations 
                $return['variations_list'] = [];
                $return['default_attribute'] = [];
                $variations_form_class = get_option('adminz_import_product_variations_form_select', 'variations');
                if($header_class and $variations_form_class){
                    $variations = $xpath->query("//*[contains(@class, '".$header_class."')]//*[contains(@class, '".$variations_form_class."')]//tr//*[contains(@class, 'label')]//label");
                    if (!is_null($variations)) {
                        foreach ($variations as $element) {
                            $attr_array =  [];
                            $attr_array['attr_name']= $element->textContent;
                            $trnode = $element->parentNode->parentNode;
                            $attrs = $xpath->query("./*[contains(@class, 'value')]//select//option",$trnode);  
                            $attr_options = [];      
                            foreach ($attrs as $key => $value) {
                                if($value->getAttribute('selected') == 'selected'){
                                    $return['default_attribute'][$element->getAttribute("for")] = $value->getAttribute("value");
                                }
                                if($value->getAttribute('value')){
                                    $attr_options[]= $value->getAttribute('value');
                                }
                            }
                            $attr_array['attr_options'] = $attr_options;
                            $return['variations_list'][] = $attr_array;
                        }
                    }
                }                                
                break;
            case 'grouped':               

                $grouped_form_class = get_option( 'adminz_import_product_grouped_form', 'grouped_form' );
                if($grouped_form_class and $header_class){
                    $grouped_form = $xpath->query("//*[contains(@class, '".$header_class."')]//*[contains(@class, '".$grouped_form_class."')]//tr//a");
                    if (!is_null($grouped_form)) {
                        foreach ($grouped_form as $element) {
                            if(!$element->getAttribute('aria-label')){
                                $temp= array(
                                    'title'=>$element->textContent,
                                    'url'=>$element->getAttribute('href'),
                                    'exits'=>false,
                                    'exits_url'=>false,
                                    'exits_id' =>false,
                                );
                                $exit_product_id = $this->search_product($element->textContent);
                                if($exit_product_id){
                                    $temp['exits'] = true;
                                    $temp['exits_id'] = $exit_product_id;
                                    $temp['exits_url'] = '<a target="_blank" href="'.get_permalink( $exit_product_id ).'">'.get_the_title($exit_product_id).'</a>';                            
                                }
                                $return['product_type_data'][] = $temp;
                            }                            
                        }
                    }
                }                
                break;
            default: 
                $return['product_type_data'] = [];
                break;
        }        
        
        $title_class = get_option('adminz_import_product_title', 'product-title');
        $title_tag = get_option('adminz_import_product_title_tag', '');
        
        if($header_class){
            if($title_tag){
                $title = $xpath->query("//*[contains(@class, '" . $header_class . "')]//".$title_tag);
            }else{
                $title = $xpath->query("//*[contains(@class, '" . $header_class . "')]//*[contains(@class, '" . $title_class . "')]");
            }
            
            if (!is_null($title)){
                foreach ($title as $element){
                    $nodes = $element->childNodes;
                    foreach ($nodes as $node){
                        $return['post_title'] .= $this->fix_content($node->nodeValue, $link);
                    }
                }
            }
        }        

        // get price product
        $price_class = get_option('adminz_import_product_price','price-wrapper');
        $product_prices = get_option('adminz_import_product_prices', 'woocommerce-Price-amount');
        if($header_class and $price_class and $product_prices){
            $prices_dom = $xpath->query("//*[contains(@class, '".$header_class."')]//*[contains(@class, '".$price_class."')]//*[contains(@class, '".$product_prices."')]");
            $price_arr = [];
            if (!is_null($prices_dom)) {
                foreach ($prices_dom as $element) {
                    preg_match_all('/[0-9]/', $element->textContent, $matches);
                    $price_arr[] = $this->fix_product_price(implode("", $matches[0]));
                }
            }
            
            $return['_sale_price'] = min($price_arr);
            $return['_price'] = max($price_arr);
            if(count($price_arr)==1){
                unset($return['_sale_price']);
            }
        }        

        // images on content
        $contentclass = get_option('adminz_import_product_content', 'woocommerce-Tabs-panel--description');
        if($contentclass){
            $imgs = $xpath->query("//*[contains(@class, '" . $contentclass . "')]//img");
            if (!is_null($imgs)){
                foreach ($imgs as $element){                             
                    $return['images_content'][] = $this->fix_url($element->getAttribute('src'),$link);
                }
            }
        }

        // images on gallery
        $image_class = get_option('adminz_import_product_thumbnail', 'woocommerce-product-gallery__image');
        $image_gallery_tag = get_option('adminz_import_product_thumbnail_tag', "img");
        $image_gallery_data_attr = get_option('adminz_import_product_thumbnail_data_attr', "data-src");
        if($image_class and $image_gallery_tag){
            $imgs = $xpath->query("//*[contains(@class, '" . $image_class . "')]//".$image_gallery_tag);
            if (!is_null($imgs)){
                foreach ($imgs as $element){  
                    $return['images_gallery'][] = $this->fix_url($element->getAttribute($image_gallery_data_attr),$link);                    
                }
            }
        }
        // include variation to gallery
        $include_variations_to_gallery = get_option('adminz_import_product_include_image_variations_to_gallery', 'on');
        if($include_variations_to_gallery =="on"){            
            $return['images_gallery'] = array_merge($return['images_gallery'],$return['images_variations']);
        }
        // include content to gallery
        $include_content_to_gallery = get_option('adminz_import_product_include_image_content_to_gallery', 'on');
        if($include_content_to_gallery =="on"){            
            $return['images_gallery'] = array_merge($return['images_gallery'],$return['images_content']);
        }

        // set all image array and thumbnail
        $return['images_all'] = array_values(array_unique(array_merge($return['images_gallery'],$return['images_variations'],$return['images_content'])));

        if(is_array($return['images_all']) and !empty($return['images_all'])){
            $return['image_thumbnail'] = $return['images_all'][0];
        }

        // get product short_description        
        $excerpt_class = get_option('adminz_import_product_short_description', 'product-short-description');
        if($excerpt_class and $header_class){
            $excerpt = $xpath->query("//*[contains(@class, '".$header_class."')]//*[contains(@class, '".$excerpt_class."')]");
            if (!is_null($excerpt)) {

                foreach ($excerpt as $element) {                
                    $nodes = $element->childNodes;
                    foreach ($nodes as $node) {
                        $return['short_description'] .= $this->fix_content($doc->saveHTML($node) , $link);
                    }
                }
            }
        }        

        // get product content 
        $contentclass = get_option('adminz_import_product_content', 'woocommerce-Tabs-panel--description');
        if($contentclass){
            $content = $xpath->query("//*[contains(@class, '" . $contentclass . "')]");
            $remove_end = get_option('adminz_import_content_remove_end', 0);
            $remove_first = get_option('adminz_import_content_remove_first', 0);
            if (!is_null($content)){
                foreach ($content as $element){
                    $nodes = $element->childNodes;
                    foreach ($nodes as $key => $node){
                        if ($key <= (count($nodes) - $remove_end - 1) and $key >= ($remove_first))
                        {
                            $return['post_content'] .= $this->fix_content($doc->saveHTML($node) , $link);
                        }
                    }
                }
            }
        }         
        return $return;
    }
    function get_category($link) {
        $html = $this->get_remote($link);
        $return = [];
        //start check
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXpath($doc);
        $blog_wrapper_class = get_option('adminz_import_category_wrapper', 'blog-wrapper');
        $post_item_class = get_option('adminz_import_category_post_item', 'post-item');
        $post_item_title_class = get_option('adminz_import_category_post_item_title', 'post-title');

        if($blog_wrapper_class and $post_item_class){
            $titles = $xpath->query("//*[contains(@class, '" . $blog_wrapper_class . "')]//*[contains(@class, '" . $post_item_class . "')]//*[contains(@class, '" . $post_item_title_class . "')]");
            if (!is_null($titles)) {
                foreach ($titles as $key => $n) {
                   $return[$key]['post_title'] = $n->textContent;
                }
            }
            $links = $xpath->query("//*[contains(@class, '" . $blog_wrapper_class . "')]//*[contains(@class, '" . $post_item_class . "')]"); 
            if (!is_null($links)) {
                foreach ($links as $key => $n) {
                    $url = $n->getElementsByTagName('a')->item(0);
                    if (!is_null($url)) {                    
                        $return[$key]['post_url'] = $this->fix_url($url->getAttribute('href'),$link);
                    }
                }
            }
        }        
        

        return $return;
    }
    function get_category_product($link) {
        $html = $this->get_remote($link);
        $return = [];
        //start check
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        libxml_clear_errors();
        $xpath = new DOMXpath($doc);

        $products_wrapper = get_option('adminz_import_category_product_wrapper', 'products');
        $product_item_wrapper = get_option('adminz_import_category_product_item', 'product-small col');
        $product_item_title = get_option('adminz_import_category_product_item_title', 'product-title');

        if($products_wrapper and $product_item_wrapper){
            $titles = $xpath->query("//*[contains(@class, '" . $products_wrapper . "')]//*[contains(@class, '" . $product_item_wrapper . "')]//*[contains(@class, '" . $product_item_title . "')]");
            if (!is_null($titles)) {
                foreach ($titles as $key => $n) {
                    $return[$key]['post_title'] = $n->textContent;

                }
            }
            
            $links = $xpath->query("//*[contains(@class, '" . $products_wrapper . "')]//*[contains(@class, '" . $product_item_wrapper . "')]");
            if (!is_null($links)) {
                foreach ($links as $key => $n) {
                    $url = $n->getElementsByTagName('a')->item(0);                
                    if (!is_null($url)) {
                        $return[$key]['post_url'] = $this->fix_url($url->getAttribute('href'),$link);
                    }
                }
            }
        }        

        return $return;
    }
    function run_import_single($link = false) {
        if(!$link){ $link = $_POST['link']; }
        $post_id = $this->do_import_single($link);
        wp_send_json_success("<a target='_blank' href='".get_permalink( $post_id )."'>Complete</a>");
        wp_die();
    }
    function run_import_single_product($link = false){
        if(!$link){ $link = $_POST['link']; }
        $post_id = $this->do_import_single_product($link);
        wp_send_json_success("<a target='_blank' href='".get_permalink( $post_id )."'>Complete</a>");
        wp_die();
    }
    function test_single() {
        $data = json_encode($this->get_single($_POST['link']));
        //endcheck
        $return = "";
        if (!empty($data) and is_array($data)){
            foreach ($data as $key => $value){
                $return .= '<div>' . $key . ": " . $value . '</div>';
            }
        }
        wp_send_json_success($data);
        wp_die();
    } 
    function test_product() {
        $data = json_encode($this->get_product($_POST['link']));
        //endcheck
        $return = "";
        if (!empty($data) and is_array($data)){
            foreach ($data as $key => $value){
                $return .= '<div>' . $key . ": " . $value . '</div>';
            }
        }
        wp_send_json_success($data);
        wp_die();
    }
    function test_category() {
        $data = json_encode($this->get_category($_POST['link']));
        wp_send_json_success($data);
        wp_die();
    }
    function test_category_product() {
        $data = json_encode($this->get_category_product($_POST['link']));
        wp_send_json_success($data);
        wp_die();
    }    
    function get_remote($link,$format=true){
        $request  = wp_remote_get( $link );        
        $html = wp_remote_retrieve_body( $request );
        if (!('OK' !== wp_remote_retrieve_response_message( $html ) OR 200 !== wp_remote_retrieve_response_code( $html ) )){
                return false;
            }
        if($format){ $html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8"); }        
        return $html;
    }
    function search_product($title){

        $return = false;
        $exit_products = get_posts([
            'post_type'  => 'product',
            'title' => $title,
            'post_status' =>'publish'
        ]);
        if(is_array($exit_products) and !empty($exit_products)){
            $return = $exit_products[0]->ID;
        }        
        return $return;
    }
    function replace_img_content($link,$images_imported,$content){
        if(empty($images_imported) or !is_array($images_imported)) return;
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $content_encode = mb_convert_encoding($content, 'HTML-ENTITIES', "UTF-8");
        $doc->loadHTML($content_encode);
        
        libxml_clear_errors();
        $xpath = new DOMXpath($doc);
        $imgs = $xpath->query("//img");

        $old_html = [];
        $new_html = [];
        if (!is_null($imgs)) {
            foreach ($imgs as $img) {
                $imgurl = $this->fix_url($img->getAttribute('src'),$link);
                if (array_key_exists($imgurl, $images_imported)){
                    $width = $img->getAttribute('width')? $img->getAttribute('width') : "";
                    $height = $img->getAttribute('height')? $img->getAttribute('height') : "";
                    $class = $img->getAttribute('class')? $img->getAttribute('class') : "";
                    $size = 'full';
                    if($width and $height){
                        $size = [$width, $height];
                    }
                    $class = null;
                    if($class){
                        $class = ['class'=>$class];
                    }
                    $old_html[] = $doc->saveHTML($img);
                    $new_html[] = wp_get_attachment_image($images_imported[$imgurl],$size,"",$class);
                }
            }
        }
        $content = str_replace($old_html, $new_html, $content);
        return $content;
    }
    function save_images($image_url, $posttitle) {
        $file = $this->get_remote($image_url,false);
        $postname = sanitize_title($posttitle);
        $im_name = "$postname.jpg";
        $res = wp_upload_bits($im_name, '', $file);
        //$attach_id = $this->insert_attachment($res['file']);

        $dirs = wp_upload_dir();
        $filetype = wp_check_filetype($res['file']);
        $attachment = array(
            'guid' => $dirs['baseurl'] . '/' . _wp_relative_upload_path($res['file']) ,
            'post_mime_type' => $filetype['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($res['file'])) ,
            'post_content' => '',
            'post_status' => 'inherit'
        );
        $attach_id = wp_insert_attachment($attachment, $res['file']);
        $attach_data = wp_generate_attachment_metadata($attach_id, $res['file']);
        wp_update_attachment_metadata($attach_id, $attach_data);


        $res['attach_id'] = $attach_id;        
        return $res;
    }
    function fix_product_price($price){
        // fix for decima
        return $price/pow(10,get_option('adminz_import_content_product_decimal_seprator', 2));        
    }
    function fix_url($url, $link) {        
        preg_match('/(http(|s)):\/\/(.*?)\//si', $link, $output);
        $domain = $output[0];

        // fist not http/https
        if ($url[0] !=="/" and $url[0] != "h" and $url[0] != "t" and $url[0] != "t" and $url[0] != "p"){
            $url = "/". $url;
        }

        // first : //
        if ($url[0] == "/" and $url[1] == "/"){
            $url = "https:". $url;
        }
        // first : /
        if ($url[0] == "/"){
            $url = $domain . substr($url,1);
        }  
        
        $remove_string = get_option('adminz_import_thumbnail_url_remove_string',"-280x280\n-400x400\n-800x800");
        if ($remove_string){
            $url = str_replace(
                explode("|", str_replace(["\n", "\r"], ["|", ""], $remove_string)) , 
                '',
                $url
            );
        }

        return $url;
    }
    function fix_content($content, $link = false) {
        preg_match('/(http(|s)):\/\/(.*?)\//si', $link, $output);
        $domain = $output[0];

        // first decode
        $content = htmlentities($content, null, 'utf-8');
        $content = str_replace("&nbsp;", " ", $content);
        $content = html_entity_decode($content);


        // fix missing domain in url/ href
        $preg_arr_from = [
            "/(src|href)=(\'|\")\/\//",
            "/(src|href)=(\'|\")\//",
            "/(src|href)=(\'|\")(?!http|tel|mailto)/",
        ];
        $preg_arr_to = [
            "$1=$2http://",
            "$1=$2".$domain,
            "$1=$2".$domain."$3",
        ];

        // remove all link
        if (get_option('adminz_import_content_remove_link', "on") == "on"){
            $preg_arr_from[] = '#<a.*?>(.*?)</a>#i';
            $preg_arr_to[] = '\1';
        }
        
        // remove all script tag
        if (get_option('adminz_import_content_remove_script', "on") == "on"){            
            $preg_arr_from[] = '#<script(.*?)>(.*?)</script>#is';
            $preg_arr_to[] = '';
        }        

        $content = preg_replace(
            $preg_arr_from, 
            $preg_arr_to,
            $content
        );

        // replce strings
        $replace_from = get_option('adminz_import_content_replace_from', "Administrator Z\nFoo\nBar");
        $replace_to = get_option('adminz_import_content_replace_to', "Your strings\nFoo 2\nBar 2");
        if ($replace_from){
            $content = str_replace(
                explode("|", str_replace(["\n", "\r"], ["|", ""], $replace_from)) , 
                explode("|", str_replace(["\n", "\r"], ["|", ""], $replace_to)) , 
                $content
            );
        }
         

        return $content;
    }
    function tool_script() {
        ob_start();?>
        <script type="text/javascript">
            (function($){
                $(document).ready(function(){
                    $('.test_single').click(function(){
                        var link = $(this).closest("td").find("input").val();
                        if(link){
                            $(".data_test").html("");
                            test_single(link,$(this).closest("td").find(".data_test"));
                        }else{
                            alert("Input link");
                        }                           
                        return false;
                    })
                    $('.test_category').click(function(){
                        var link = $(this).closest("td").find("input").val();
                        if(link){
                            $(".data_test").html("");
                            test_category(link,$(this).closest("td").find(".data_test"));
                        }else{
                            alert("Input link");
                        }
                        return false;
                    })
                    $('.test_product').click(function(){
                        var link = $(this).closest("td").find("input").val();
                        if(link){
                            $(".data_test").html("");
                            test_product(link,$(this).closest("td").find(".data_test"));
                        }else{
                            alert("Input link");
                        }                           
                        return false;
                    })
                    $('.test_category_product').click(function(){
                        var link = $(this).closest("td").find("input").val();
                        if(link){
                            $(".data_test").html("");
                            test_category_product(link,$(this).closest("td").find(".data_test"));
                        }else{
                            alert("Input link");
                        }
                        return false;
                    })
                    $('.run_import_single').click(function(){                        
                        var link = $(this).closest("td").find("input").val();
                        if(link){
                            $(".data_test").html("");
                            run_import_single(link,$(this).closest("td").find(".data_test"));
                        }else{
                            alert("Input link");
                        }                           
                        return false;
                    })
                    $('.run_import_category').click(function(){
                        var link = $(this).closest("td").find("input").val();
                        if(link){
                            $(".data_test").html("");
                            run_import_category(link,$(this).closest("td").find(".data_test"));
                        }else{
                            alert("Input link");
                        }
                        return false;
                    })
                    $('.run_import_category_product').click(function(){
                        var link = $(this).closest("td").find("input").val();
                        if(link){
                            $(".data_test").html("");
                            run_import_category_product(link,$(this).closest("td").find(".data_test"));
                        }else{
                            alert("Input link");
                        }
                        return false;
                    })
                    $('.run_import_single_product').click(function(){
                        var link = $(this).closest("td").find("input").val();
                        if(link){
                            $(".data_test").html("");
                            run_import_single_product(link,$(this).closest("td").find(".data_test"));
                        }else{
                            alert("Input link");
                        }                           
                        return false;
                    })                     
                    function test_single(link,output){
                        $.ajax({
                            type : "post",
                            dataType : "json",
                            url : '<?php echo admin_url('admin-ajax.php'); ?>',
                            data : {
                                action: "test_single",
                                link : link
                            },
                            context: this,
                            beforeSend: function(){                                 
                                var html_run = '<div class="notice notice-alt notice-warning updating-message"><p aria-label="Checking...">Checking...</p></div>';
                                output.html(html_run);
                            },
                            success: function(response) {                                       
                                
                                if(response.success) {                                          
                                    var data_test = JSON.parse(response.data);    
                                    console.log(data_test);                                    
                                    var html_test = "";

                                    if(!data_test.post_title){
                                        html_test = '<div class="notice notice-alt notice-warning upload-error-message"><p aria-label="Checking...">Title not found! Please check url or CSS classes check</p></div>';
                                    }else{
                                        html_test += "<div style='padding: 10px; background-color: white;'>"; 

                                        html_test +="<code>Thumbnail</code>";
                                        html_test +="<div>";
                                        if(data_test.post_thumbnail){
                                            for (var i = 0; i < data_test.post_thumbnail.length; i++) {
                                                if(i==0){
                                                    html_test +="<div><img src='"+data_test.post_thumbnail[i]+"'/>"; html_test +="</div>";
                                                }else{
                                                    html_test +='<img style="margin-right: 10px; height: 70px; border: 5px solid silver;" src="'+data_test.post_thumbnail[i]+'"/>';
                                                }
                                            }
                                            for (var i = 0; i < data_test.post_thumbnail.length; i++) {
                                                html_test +="<p><small>"+data_test.post_thumbnail[i]+"</small></p>";
                                            }
                                        }else{
                                            html_test +="<b style='color: white; background-color: red;'>Not found</b></br>";
                                        }
                                        html_test +="</div>";

                                        html_test +="<code>Title:</code>";
                                        if(data_test.post_title){
                                            html_test +="<h1>"+data_test.post_title+"</h1>";
                                        } else{
                                            html_test +="<b style='color: white; background-color: red;'>Not found</b></br>";
                                        }

                                        html_test += "<code>Content:</code>";
                                        if(data_test.post_content){
                                            html_test +="<div>"+data_test.post_content+"</div>";
                                        }else{
                                            html_test +="<b style='color: white; background-color: red;'>Not found</b></br>";
                                        }
                                        html_test +="</div>";
                                    }

                                    
                                    output.html(html_test);
                                }
                                else {
                                    alert('There is an error');
                                }
                            },
                            error: function( jqXHR, textStatus, errorThrown ){
                                
                                console.log( 'Administrator Z: The following error occured: ' + textStatus, errorThrown );
                            }
                        })
                    }
                    function test_category(link,output){
                        $.ajax({
                            type : "post",
                            dataType : "json",
                            url : '<?php echo admin_url('admin-ajax.php'); ?>',
                            data : {
                                action: "test_category",
                                link : link
                            },
                            context: this,
                            beforeSend: function(){
                                var html_run = '<div class="notice notice-alt notice-warning updating-message"><p aria-label="Checking...">Checking...</p></div>';
                                output.html(html_run);
                            },
                            success: function(response) {
                                
                                if(response.success) {
                                    var data_test = JSON.parse(response.data);                                   
                                    console.log(data_test);
                                    var html_test = "";
                                    if(!data_test.length){
                                        html_test = '<div class="notice notice-alt notice-warning upload-error-message"><p aria-label="Checking...">Title not found! Please check url or CSS classes check</p></div>';
                                    }else{
                                        html_test += "<div style='padding: 10px; background-color: white;'>";
                                        html_test +='<table>';
                                        for (var i = 0; i < data_test.length; i++) {                                            
                                            html_test +='<tr data-link="'+data_test[i]['post_url']+'"> <td class="status">Status</td><td><p>'+data_test[i]['post_title']+'</p><a target="_blank" href="'+data_test[i]['post_url']+'">Link</a></td></tr>';
                                        }                                       
                                        html_test +='</table>';
                                        html_test +="</div>";
                                    }
                                    
                                    output.html(html_test);
                                }
                                else {
                                    alert('There is an error');
                                }
                            },
                            error: function( jqXHR, textStatus, errorThrown ){
                                
                                console.log( 'Administrator Z: The following error occured: ' + textStatus, errorThrown );
                            }
                        })
                    }
                    function test_product(link,output){
                        $.ajax({
                            type : "post",
                            dataType : "json",
                            url : '<?php echo admin_url('admin-ajax.php'); ?>',
                            data : {
                                action: "test_product",
                                link : link
                            },
                            context: this,
                            beforeSend: function(){                                 
                                var html_run = '<div class="notice notice-alt notice-warning updating-message"><p aria-label="Checking...">Checking...</p></div>';
                                output.html(html_run);
                            },
                            success: function(response) {
                                if(response.success) {
                                    var data_test = JSON.parse(response.data);
                                    console.log(data_test);
                                    var html_test = "";

                                    if(!data_test.post_title){
                                        html_test = '<div class="notice notice-alt notice-warning upload-error-message"><p aria-label="Checking...">Title not found! Please check url or CSS classes check</p></div>';
                                    }else{
                                        html_test += "<div style='padding: 10px; background-color: white;'>"; 

                                        // thumbnail
                                        html_test +="<code>Thumbnail</code>";
                                        html_test +="<div>";
                                        
                                        if(data_test.image_thumbnail){
                                            html_test +="<div><img style='width: 500px;' src='"+data_test.image_thumbnail+"'/></div>";                                            
                                        }else{
                                            html_test +="<b style='color: white; background-color: red;'>Not found</b></br>";
                                        }
                                        html_test +='<p><small>'+data_test.image_thumbnail+'</small></p>';
                                        html_test +="</div>";

                                        // gallery 
                                        html_test +="<code>Gallery</code>";
                                        html_test +="<div>";
                                        for (var i = 0; i < data_test.images_gallery.length; i++) {
                                            if(i!==0){
                                                html_test +='<img style="margin-right: 10px; height: 100px; border: 5px solid silver;" src="'+data_test.images_gallery[i]+'"/>';
                                            }
                                        }
                                        for (var i = 0; i < data_test.images_gallery.length; i++) {
                                            if(i!==0){
                                                html_test +='<p><small>'+data_test.images_gallery[i]+'</small></p>';
                                            }
                                        }
                                        html_test +="</div>";


                                        // product type
                                        html_test +="<div>";
                                        html_test +="<code>Product type:</code> ";
                                        html_test +=data_test.product_type;                                    
                                        html_test +="</div>";  

                                        // title
                                        html_test +="<code>Title:</code>";
                                        if(data_test.post_title){
                                            html_test +="<h1>"+data_test.post_title+"</h1>";
                                        } else{
                                            html_test +="<b style='color: white; background-color: red;'>Not found</b></br>";
                                        }
                                        html_test +="</br>";    

                                        // price 
                                        html_test +="<code>Price:</code>";
                                        if(data_test.product_type == 'simple'){                                        
                                            if(data_test._price){
                                                html_test +=" <span>Regular: "+data_test._price+"</span>";
                                            } else{
                                                html_test +="<b style='color: white; background-color: red;'>Sale price not found</b>";
                                            }                                        
                                            if(data_test._sale_price){
                                                html_test +=" <span>Sale: "+data_test._sale_price+"</span>";
                                                
                                            }
                                        }
                                        if(data_test.product_type == 'external'){
                                            html_test +=data_test.product_type_data;
                                        }
                                        if(data_test.product_type == 'variable'){
                                            if(data_test.product_type_data.length){
                                                if(data_test.default_attribute){
                                                    html_test+= '</br><code>Default Attribute</code>';
                                                    html_test+= JSON.stringify(data_test.default_attribute);                                              
                                                }
                                                
                                                html_test+= '<table><tr>';
                                                for (var i = 0; i < data_test.product_type_data.length; i++) {
                                                    var attr_image = '<img width="50px" src="'+data_test.product_type_data[i].image.url+'"/>';
                                                    var attr_name = Object.values(data_test.product_type_data[i].attributes);
                                                    var attr_price = data_test.product_type_data[i].display_price;
                                                    var attr_price_regular_sale = data_test.product_type_data[i].display_regular_price;
                                                    html_test+='<td><p>'+attr_image+'</p> <p>'+attr_name+'</p> <p>'+attr_price+'</p><p><del>'+attr_price_regular_sale+'</del></p></td>';
                                                }
                                                html_test +='</tr></table>';
                                            }
                                        }
                                        if(data_test.product_type == 'grouped'){
                                            if(data_test.product_type_data.length){
                                                html_test+= '<table>';
                                                for (var i = 0; i < data_test.product_type_data.length; i++) {
                                                    var exit_product_status = (data_test.product_type_data[i].exits)? "Already exists on the system" : "--"
                                                    var exit_product_url = (data_test.product_type_data[i].exits_url)? data_test.product_type_data[i].exits_url : "will be import at same at";
                                                    html_test += '<tr><td><a target="_blank" href="'+data_test.product_type_data[i].url+'">'+data_test.product_type_data[i].title+'</a></td><td>'+exit_product_status+'</td><td>'+exit_product_url+'</td></tr>';
                                                }
                                                html_test +='</table>';
                                            }
                                        }
                                        html_test +="</br>";

                                        // short description          
                                        html_test +="<code>Short description:</code>";                          
                                        if(data_test.short_description){                                        
                                            html_test +="<div>"+data_test.short_description+"</div>";
                                        }else{
                                            html_test +="<b style='color: white; background-color: red;'>Not found</b></br>";
                                        }
                                        // content
                                        html_test += "<code>Description:</code>";
                                        if(data_test.post_content){
                                            html_test +="<div>"+data_test.post_content+"</div>";
                                        }else{
                                            html_test +="<b style='color: white; background-color: red;'>Not found</b></br>";
                                        }
                                        html_test +="</div>";
                                    }

                                    
                                    output.html(html_test);
                                }
                                else {
                                    alert('There is an error');
                                }
                            },
                            error: function( jqXHR, textStatus, errorThrown ){
                                
                                console.log( 'Administrator Z: The following error occured: ' + textStatus, errorThrown );
                            }
                        })
                    }
                    function test_category_product(link,output){
                        $.ajax({
                            type : "post",
                            dataType : "json",
                            url : '<?php echo admin_url('admin-ajax.php'); ?>',
                            data : {
                                action: "test_category_product",
                                link : link
                            },
                            context: this,
                            beforeSend: function(){
                                var html_run = '<div class="notice notice-alt notice-warning updating-message"><p aria-label="Checking...">Checking...</p></div>';
                                output.html(html_run);
                            },
                            success: function(response) {
                                
                                if(response.success) {
                                    var data_test = JSON.parse(response.data);
                                    console.log(data_test);
                                    var html_test = "";
                                    if(!data_test.length){
                                        html_test = '<div class="notice notice-alt notice-warning upload-error-message"><p aria-label="Checking...">Title not found! Please check url or CSS classes check</p></div>';
                                    }else{
                                        html_test += "<div style='padding: 10px; background-color: white;'>";
                                        html_test +='<table>';
                                        for (var i = 0; i < data_test.length; i++) {                                            
                                            html_test +='<tr data-link="'+data_test[i]['post_url']+'"> <td class="status">Status</td><td><p>'+data_test[i]['post_title']+'</p><a target="_blank" href="'+data_test[i]['post_url']+'">Link</a></td></tr>';
                                        }                                       
                                        html_test +='</table>';
                                        html_test +="</div>";
                                    }

                                    output.html(html_test);
                                }
                                else {
                                    alert('There is an error');
                                }
                            },
                            error: function( jqXHR, textStatus, errorThrown ){
                                
                                console.log( 'Administrator Z: The following error occured: ' + textStatus, errorThrown );
                            }
                        })
                    }
                    function run_import_single(link,output){
                        var start = new Date().getTime();
                        $.ajax({
                            type : "post",
                            dataType : "json",
                            url : '<?php echo admin_url('admin-ajax.php'); ?>',
                            data : {
                                action: "run_import_single",
                                link : link
                            },
                            context: this,
                            beforeSend: function(){
                                var html_run = '<div class="notice notice-alt updating-message"><p aria-label="Importing...">Importing...</p></div>';
                                output.html(html_run);
                            },
                            success: function(response) {                                
                                if(response.success) {
                                    var end = new Date().getTime();
                                    var html_run = "";
                                    html_run += "<div class='notice notice-alt notice-success updated-message'>";
                                    html_run += '<p aria-label="done">';
                                    html_run += response.data;
                                    html_run +="<code>"+ (end - start)/1000+" seconds</code>";
                                    html_run += '</p>';
                                    html_run +="</div>";
                                    output.html(html_run);                                    
                                }
                                else {
                                    alert('There is an error');
                                }
                            },
                            error: function( jqXHR, textStatus, errorThrown ){
                                
                                console.log( 'Administrator Z: The following error occured: ' + textStatus, errorThrown );
                            }
                        })                        
                    }
                    function run_import_category(link,output){
                        $.ajax({
                            type : "post",
                            dataType : "json",
                            url : '<?php echo admin_url('admin-ajax.php'); ?>',
                            data : {
                                action: "test_category",
                                link : link
                            },
                            context: this,
                            beforeSend: function(){
                                var html_run = '<div class="notice notice-alt notice-warning updating-message"><p aria-label="Checking...">Checking...</p></div>';
                                output.html(html_run);
                            },
                            success: function(response) {
                                
                                if(response.success) {
                                    var data_test = JSON.parse(response.data);
                                    var html_test = "";
                                    html_test += "<div style='padding: 10px; background-color: white;'>";
                                    html_test +='<table>';
                                    for (var i = 0; i < data_test.length; i++) {                                            
                                        html_test +='<tr data-link="'+data_test[i]['post_url']+'"> <td class="status">Status</td><td><p>'+data_test[i]['post_title']+'</p><a target="_blank" href="'+data_test[i]['post_url']+'">Link</a></td></tr>';
                                    }                                       
                                    html_test +='</table>';
                                    html_test +="</div>";                                       
                                    output.html(html_test);

                                    // foreach all item listed to run single import
                                    var tr = output.find('table').find('tr');
                                    tr.each(function(){
                                        var link = ($(this).attr('data-link'));
                                        var output = $(this).find("td.status");
                                        run_import_single(link,output);
                                    });
                                }
                                else {
                                    alert('There is an error');
                                }
                            },
                            error: function( jqXHR, textStatus, errorThrown ){
                                
                                console.log( 'Administrator Z: The following error occured: ' + textStatus, errorThrown );
                            }
                        })
                    }
                    function run_import_single_product(link,output){
                        var start = new Date().getTime();
                        $.ajax({
                            type : "post",
                            dataType : "json",
                            url : '<?php echo admin_url('admin-ajax.php'); ?>',
                            data : {
                                action: "run_import_single_product",
                                link : link
                            },
                            context: this,
                            beforeSend: function(){                                
                                var html_run = '<div class="notice notice-alt updating-message"><p aria-label="Importing...">Importing...</p></div>';
                                output.html(html_run);
                            },
                            success: function(response) {                                
                                if(response.success) {
                                    var end = new Date().getTime();
                                    var html_run = "";
                                    html_run += "<div class='notice notice-alt notice-success updated-message'>";
                                    html_run += '<p aria-label="done">';
                                    html_run += response.data;
                                    html_run +="<code>"+ (end - start)/1000+" seconds</code>";
                                    html_run += '</p>';
                                    html_run +="</div>";                                    
                                    output.html(html_run);
                                }
                                else {
                                    alert('There is an error');
                                }
                            },
                            error: function( jqXHR, textStatus, errorThrown ){
                                
                                console.log( 'Administrator Z: The following error occured: ' + textStatus, errorThrown );
                            }
                        })
                    }
                    function run_import_category_product(link,output){
                        $.ajax({
                            type : "post",
                            dataType : "json",
                            url : '<?php echo admin_url('admin-ajax.php'); ?>',
                            data : {
                                action: "test_category_product",
                                link : link
                            },
                            context: this,
                            beforeSend: function(){
                               var html_run = '<div class="notice notice-alt notice-warning updating-message"><p aria-label="Checking...">Checking...</p></div>';
                                output.html(html_run);
                            },
                            success: function(response) {
                                
                                if(response.success) {
                                    var data_test = JSON.parse(response.data);
                                    var html_test = "";
                                    html_test += "<div style='padding: 10px; background-color: white;'>";
                                    html_test +='<table>';
                                    for (var i = 0; i < data_test.length; i++) {                                            
                                        html_test +='<tr data-link="'+data_test[i]['post_url']+'"> <td class="status">Status</td><td><p>'+data_test[i]['post_title']+'</p><a target="_blank" href="'+data_test[i]['post_url']+'">Link</a></td></tr>';
                                    }                                       
                                    html_test +='</table>';
                                    html_test +="</div>";
                                    output.html(html_test);

                                    // foreach all item listed to run single import
                                    var tr = output.find('table').find('tr');
                                    tr.each(function(){
                                        var link = ($(this).attr('data-link'));
                                        var output = $(this).find("td.status");
                                        run_import_single_product(link,output);
                                    });
                                }
                                else {
                                    alert('There is an error');
                                }
                            },
                            error: function( jqXHR, textStatus, errorThrown ){
                                
                                console.log( 'Administrator Z: The following error occured: ' + textStatus, errorThrown );
                            }
                        })
                    }
                })
            })(jQuery)
        </script>
        <?php
        return ob_get_clean();
    }
    function tab_html() {
        global $adminz;
        ob_start();
        ?>
        <div class="import_data">
            <form method="post" action="options.php">
                <?php
        settings_fields($this->options_group);
        do_settings_sections($this->options_group);
            ?>
            <table class="form-table table_imports">
                <tr valign="top">
                    <th scope="row">
                        <h3>Import data</h3>
                    </th>
                </tr>
                <tr valign="top">
                    <th scope="row">From post</th>
                    <td>
                        <label>
                            <input type="url" name="adminz_import_from_post" placeholder="https://test.minhkhang.net/?p=1794" value="<?php echo get_option('adminz_import_from_post', 'https://test.minhkhang.net/?p=1794'); ?>"> 
                        </label>
                        <button class="button test_single">Test</button>
                        <button class="button button-primary run_import_single">Import</button>
                        
                        <br>
                        <p class="data_test"></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">From category</th>
                    <td>
                        <label>
                            <input type="url" name="adminz_import_from_category" placeholder="https://test.minhkhang.net/?page_id=92" value="<?php echo get_option('adminz_import_from_category', 'https://test.minhkhang.net/?page_id=92'); ?>"> 
                        </label>
                        <button class="button test_category">Test</button>
                        <button class="button button-primary run_import_category">Import</button>
                        <code>Check single import before run category import is recommended.</code>
                        <br>
                        <p class="data_test"></p>       
                    </td>
                </tr>
                <?php if(class_exists( 'WooCommerce' ) ){ ?>
                <tr valign="top">
                    <th scope="row">From Product</th>
                    <td>
                        <label>
                            <input type="url" name="adminz_import_from_product" placeholder="https://test.minhkhang.net/?product=all-star-canvas-hi-converse" value="<?php echo get_option('adminz_import_from_product', 'https://test.minhkhang.net/?product=all-star-canvas-hi-converse'); ?>"> 
                        </label>
                        <button class="button test_product">Test</button>
                        <button class="button button-primary run_import_single_product">Import</button>
                        
                        <br>
                        <p class="data_test"></p> 
                    </td>
                </tr>  
                <tr valign="top">
                    <th scope="row">From Product category</th>
                    <td>
                        <label>
                            <input type="url" name="adminz_import_from_product_category" placeholder="https://test.minhkhang.net/?post_type=product" value="<?php echo get_option('adminz_import_from_product_category', 'https://test.minhkhang.net/?post_type=product'); ?>"> 
                        </label>
                        <button class="button test_category_product">Test</button>
                        <button class="button button-primary run_import_category_product">Import</button>
                        <code>Check single import before run category import is recommended.</code>
                        <br>
                        <p class="data_test"></p>       
                    </td>
                </tr>   
                <?php } ?>         
            </table>
        </div>
        <?php echo $this->tool_script(); ?>
            <table class="form-table">
                <tr valign="top">
                    <th><h3>CSS classses check</h3></th>
                    <td></td>
                </tr>
                <tr valign="top">                   
                    <th>
                        <a target="_blank" href="https://quyle91.github.io/img/adminz/post-single.svg">Post single</a>
                    </th>
                    <td>
                        <p>
                            <input type="text" name="adminz_import_post_header_title" placeholder='entry-header' value="<?php echo get_option('adminz_import_post_header_title', 'entry-header'); ?>" />
                            <code>Header wrapper class</code>
                        </p>
                        <p>
                            <input type="text" name="adminz_import_post_title" placeholder='entry-title' value="<?php echo get_option('adminz_import_post_title', 'entry-title'); ?>" />
                            <code>Title wrapper class</code>
                        </p>
                        <p>
                            <input type="text" name="adminz_import_post_thumbnail" placeholder='entry-image' value="<?php echo get_option('adminz_import_post_thumbnail', 'entry-image'); ?>" />
                            <code>Thumbnails wrapper class</code>
                        </p>
                        <p>
                            <input type="text" name="adminz_import_post_content" placeholder='entry-content' value="<?php echo get_option('adminz_import_post_content', 'entry-content'); ?>" />
                            <code>Content wrapper class</code>
                        </p>
                    </td>
                </tr>
                <tr valign="top">                   
                    <th>
                        <a target="_blank" href="https://quyle91.github.io/img/adminz/category-blog.svg">Category/ blog</a>
                    </th>
                    <td>
                        <p>                         
                            <input type="text" name="adminz_import_category_wrapper" placeholder = 'blog-wrapper' value="<?php echo get_option('adminz_import_category_wrapper', 'blog-wrapper'); ?>" />
                            <code>Category wrapper class</code> <em>| distinguish it from widget/ sidebar wrapper</em>
                        </p>
                        <p>
                            <input type="text" name="adminz_import_category_post_item" placeholder='post-item' value="<?php echo get_option('adminz_import_category_post_item', 'post-item'); ?>" />
                            <code>Post item wrapper class</code>
                        </p> 
                        <p>
                            <input type="text" name="adminz_import_category_post_item_title" placeholder='post-title' value="<?php echo get_option('adminz_import_category_post_item_title', 'post-title'); ?>" />
                            <code>Post item title class</code>
                        </p>                       
                    </td>
                </tr>
                <?php if(class_exists( 'WooCommerce' ) ){ ?>
                <tr valign="top">                   
                    <th>
                        <a target="_blank" href="https://quyle91.github.io/img/adminz/product-single.svg">Product single</a>
                    </th>
                    <td>
                        <p>
                            <input type="text" name="adminz_import_product_header_title" placeholder='product-info' value="<?php echo get_option('adminz_import_product_header_title', 'product-info'); ?>" />
                            <code>Header information wrapper class</code>
                        </p>
                        <p>
                            &rdsh;<input type="text" name="adminz_import_product_title" placeholder='product-title' value="<?php echo get_option('adminz_import_product_title', 'product-title'); ?>" />
                            <code>Title wrapper class</code>
                        </p>
                        <p>
                            &rdsh;&rdsh;<input type="text" name="adminz_import_product_title_tag" placeholder='' value="<?php echo get_option('adminz_import_product_title_tag', ''); ?>" />
                            <code>Or Title tag</code><em> Leave empty if not using.</em>
                        </p>
                        <p>
                            &rdsh;<input type="text" name="adminz_import_product_price" placeholder='price-wrapper' value="<?php echo get_option('adminz_import_product_price', 'price-wrapper'); ?>" />
                            <code>Price wrapper class</code>
                        </p>
                        <p>
                            &rdsh;&rdsh;<input type="text" name="adminz_import_product_prices" placeholder='woocommerce-Price-amount' value="<?php echo get_option('adminz_import_product_prices', 'woocommerce-Price-amount'); ?>" />
                            <code>Prices class</code>
                        </p>
                        <p>
                            &rdsh;<input type="text" name="adminz_import_product_single_add_to_cart_button" placeholder='single_add_to_cart_button' value="<?php echo get_option('adminz_import_product_single_add_to_cart_button', 'single_add_to_cart_button'); ?>" />
                            <code>Single add to cart button</code>
                        </p>
                        <p>
                            &rdsh;<input type="text" name="adminz_import_product_variations_json" placeholder='variations_form' value="<?php echo get_option('adminz_import_product_variations_json', 'variations_form'); ?>" />
                            <code>Variations json data</code>
                        </p>
                        <p>
                            &rdsh;<input type="text" name="adminz_import_product_variations_form_select" placeholder='variations' value="<?php echo get_option('adminz_import_product_variations_form_select', 'variations'); ?>" />
                            <code>Variations form select</code>
                        </p>
                        <p>
                            &rdsh;<input type="text" name="adminz_import_product_grouped_form" placeholder='grouped_form' value="<?php echo get_option('adminz_import_product_grouped_form', 'grouped_form'); ?>" />
                            <code>Grouped form</code>
                        </p>
                        <p>
                            &rdsh;<input type="text" name="adminz_import_product_short_description" placeholder='product-short-description' value="<?php echo get_option('adminz_import_product_short_description', 'product-short-description'); ?>" />
                            <code>Excerpt wrapper class</code>
                        </p>
                        <p>
                            <input type="text" name="adminz_import_product_thumbnail" placeholder='woocommerce-product-gallery__image' value="<?php echo get_option('adminz_import_product_thumbnail', 'woocommerce-product-gallery__image'); ?>" />
                            <code>Gallery wrapper class</code>
                        </p>
                        <p>
                            &rdsh;<input type="text" name="adminz_import_product_thumbnail_tag" placeholder='img' value="<?php echo get_option('adminz_import_product_thumbnail_tag', 'img'); ?>" />
                            <code>Gallery item tag</code>
                        </p>
                        <p>
                            &rdsh;<input type="text" name="adminz_import_product_thumbnail_data_attr" placeholder='data-src' value="<?php echo get_option('adminz_import_product_thumbnail_data_attr', 'data-src'); ?>" />
                            <code>Gallery item data attribute</code>
                        </p>                        
                        <p>
                            <input type="text" name="adminz_import_product_content" placeholder='woocommerce-Tabs-panel--description' value="<?php echo get_option('adminz_import_product_content', 'woocommerce-Tabs-panel--description'); ?>" />
                            <code>Content wrapper class | Warning: Select <b>Inside</b> a description tab content for reason: default hide tab css</code>
                        </p>                        
                    </td>
                </tr>
                <tr valign="top">                   
                    <th>
                        <a target="_blank" href="https://quyle91.github.io/img/adminz/product-list.svg">Product list</a>
                    </th>
                    <td>
                        <p>                         
                            <input type="text" name="adminz_import_category_product_wrapper" placeholder = 'products' value="<?php echo get_option('adminz_import_category_product_wrapper', 'products'); ?>" />
                            <code>List wrapper class</code> <em>| distinguish it from widget/ sidebar wrapper</em>
                        </p>
                        <p>
                            <input type="text" name="adminz_import_category_product_item" placeholder='product-small col' value="<?php echo get_option('adminz_import_category_product_item', 'product-small col'); ?>" />
                            <code>Item wrapper class</code>
                        </p>  
                        <p>
                            <input type="text" name="adminz_import_category_product_item_title" placeholder='product-title' value="<?php echo get_option('adminz_import_category_product_item_title', 'product-title'); ?>" />
                            <code>Title class</code>
                        </p>                      
                    </td>
                </tr>
                <?php } ?>
                <tr valign="top">
                    <th><h3>Content fix</h3></th>
                    <td></td>
                </tr>                
                <tr valign="top">                   
                    <th>
                        Content Fix
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" disabled checked  name=""/>
                            Save images to library
                        </label>
                        <br>
                        <label>
                            <input type="checkbox" <?php echo get_option('adminz_import_content_remove_link','on') == 'on' ? 'checked' : ''; ?>  name="adminz_import_content_remove_link"/>
                            Remove all link
                        </label>
                        <br>
                        <label>
                            <input type="checkbox" <?php echo get_option('adminz_import_content_remove_script','on') == 'on' ? 'checked' : ''; ?>  name="adminz_import_content_remove_script"/>
                            Remove all script tag
                        </label>
                        <br>
                    </td>
                </tr>
                <tr valign="top">                   
                    <th>
                        Remove line
                    </th>
                    <td>
                        <p>                         
                            <input type="number" name="adminz_import_content_remove_first" placeholder = '0' value="<?php echo get_option('adminz_import_content_remove_first', 0); ?>" />
                            <code>Removes the number of elements from the <strong>First</strong></code> <em>Useful when removing post meta: date, author, viewcount </em>
                        </p>
                        <p>                         
                            <input type="number" name="adminz_import_content_remove_end" placeholder = '0' value="<?php echo get_option('adminz_import_content_remove_end', 0); ?>" />
                            <code>Removes the number of elements from the <strong>END</strong></code> <em>Useful when removing signatures or socials share buttons</em>
                        </p>
                    </td>
                </tr>                
                <tr valign="top">                   
                    <th>
                        Content replace string
                    </th>
                    <td>
                        <p>                         
                            <textarea rows="7" cols="100%" class="input-text wide-input " type="text" name="adminz_import_content_replace_from" placeholder="Administrator Z&#10;Foo&#10;Bar" ><?php echo get_option('adminz_import_content_replace_from', "Administrator Z\nFoo\nBar"); ?></textarea><br>
                        </p>
                        <p>             
                            <textarea rows="7" cols="100%" class="input-text wide-input " type="text" name="adminz_import_content_replace_to" placeholder="Your strings&#10;Foo 2&#10;Bar 2"><?php echo get_option('adminz_import_content_replace_to', "Your strings\nFoo 2\nBar 2"); ?></textarea><br>
                        </p>
                        <code>Each character is one line</code>
                    </td>
                </tr>                
                <?php if(class_exists( 'WooCommerce' ) ){ ?>
                <tr valign="top">                   
                    <th>
                        <h3>Product</h3>
                    </th>
                    <td>
                        
                    </td>
                </tr>                
                <tr valign="top">                   
                    <th>
                        Gallery
                    </th>
                    <td>
                        <p>
                            <label>
                                <input type="checkbox" name="adminz_import_product_include_image_content_to_gallery" <?php echo get_option('adminz_import_product_include_image_content_to_gallery','on') == 'on' ? 'checked' : ''; ?> />
                                <code>Include entry content images to gallery</code>
                            </label>
                        </p>
                        <p>
                            <label>
                                <input type="checkbox" name="adminz_import_product_include_image_variations_to_gallery" <?php echo get_option('adminz_import_product_include_image_variations_to_gallery','on') == 'on' ? 'checked' : ''; ?> />
                                <code>Include variations images to gallery</code>
                            </label>
                        </p>
                    </td>
                </tr>
                <tr valign="top">                   
                    <th>
                        Small thumbnail url fix
                    </th>
                    <td>
                        <p>                         
                            <textarea rows="3" cols="40%" class="input-text wide-input " placeholder="-280x280&#10;-400x400" type="text" name="adminz_import_thumbnail_url_remove_string" ><?php echo get_option('adminz_import_thumbnail_url_remove_string', "-280x280&#10;-400x400&#10;-800x800"); ?></textarea><br>
                        </p>                        
                        <span>https://domain.com/image_folder/image_name<code>-280x280</code>.jpg</span><br>
                        <span>https://domain.com/image_folder/image_name<code>-400x400</code>.jpg</span><br>
                    </td>
                </tr>
                <tr valign="top">                   
                    <th>
                        Price
                    </th>
                    <td>
                        <p>
                            <input type="number" name="adminz_import_content_product_decimal_seprator" placeholder='2' value="<?php echo get_option('adminz_import_content_product_decimal_seprator', 2); ?>" />
                            <code>Product price remove decimal separator from <b>END</b></code>
                        </p>
                    </td>
                </tr>
                <?php } ?>
            </table>
            <?php submit_button(); ?>
        </form>     
        <?php
        return ob_get_clean();
    }
    function register_tab($tabs) {
        $tabs[] = array(
            'title' => $this->title,
            'slug' => $this->slug,
            'html' => $this->tab_html()
        );
        return $tabs;
    }
    function register_option_setting() {
        // input field save
        register_setting($this->options_group, 'adminz_import_from_post');        
        register_setting($this->options_group, 'adminz_import_from_category');        
        register_setting($this->options_group, 'adminz_import_from_product');        
        register_setting($this->options_group, 'adminz_import_from_product_category');        
        //single post
        register_setting($this->options_group, 'adminz_import_post_header_title');
        register_setting($this->options_group, 'adminz_import_post_title');
        register_setting($this->options_group, 'adminz_import_post_thumbnail');
        register_setting($this->options_group, 'adminz_import_post_content');
        // category
        register_setting($this->options_group, 'adminz_import_category_wrapper');
        register_setting($this->options_group, 'adminz_import_category_post_item');
        register_setting($this->options_group, 'adminz_import_category_post_item_title');
        // single product
        register_setting($this->options_group, 'adminz_import_product_header_title');
        register_setting($this->options_group, 'adminz_import_product_title');
        register_setting($this->options_group, 'adminz_import_product_title_tag');
        register_setting($this->options_group, 'adminz_import_product_price');
        register_setting($this->options_group, 'adminz_import_product_prices');
        register_setting($this->options_group, 'adminz_import_product_thumbnail');
        register_setting($this->options_group, 'adminz_import_product_thumbnail_tag');
        register_setting($this->options_group, 'adminz_import_product_thumbnail_data_attr');        
        //register_setting($this->options_group, 'adminz_import_product_gallery');
        register_setting($this->options_group, 'adminz_import_product_short_description');
        register_setting($this->options_group, 'adminz_import_product_content');
        register_setting($this->options_group, 'adminz_import_product_single_add_to_cart_button');
        register_setting($this->options_group, 'adminz_import_product_variations_json');
        register_setting($this->options_group, 'adminz_import_product_variations_form_select');
        register_setting($this->options_group, 'adminz_import_product_grouped_form');
        // category product
        register_setting($this->options_group, 'adminz_import_category_product_wrapper');
        register_setting($this->options_group, 'adminz_import_category_product_item');
        register_setting($this->options_group, 'adminz_import_category_product_item_title');
        // content fix
        //register_setting($this->options_group, 'adminz_import_content_auto_save_image');
        
        register_setting($this->options_group, 'adminz_import_thumbnail_url_remove_string');
        register_setting($this->options_group, 'adminz_import_content_remove_link');
        register_setting($this->options_group, 'adminz_import_content_remove_script');
        register_setting($this->options_group, 'adminz_import_content_remove_first');
        register_setting($this->options_group, 'adminz_import_content_remove_end');
        register_setting($this->options_group, 'adminz_import_content_replace_from');
        register_setting($this->options_group, 'adminz_import_content_replace_to');        
        register_setting($this->options_group, 'adminz_import_product_include_image_content_to_gallery');
        register_setting($this->options_group, 'adminz_import_product_include_image_variations_to_gallery');        
        register_setting($this->options_group, 'adminz_import_content_product_decimal_seprator');
    }
}

