<?php
namespace Adminz\Admin;
use Adminz\Admin\Adminz as Adminz;
use DOMDocument;
use DOMXpath;
use WC_Product;
/**
 *
 */
class ADMINZ_Tools extends Adminz {
    public $options_group = "adminz_tools";
    public $title = 'Tools';
    public $slug = 'adminz_tools';
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
    function run_import_single() {
        $link = $_POST['link'];
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
        
        if(!empty($post_thumbnails) and is_array($post_thumbnails)){
            foreach ($post_thumbnails as $key => $url) {
                $res = $this->save_images($url,$post_args['post_title']."-".$key);
                
                // set first image for thumbnail
                if($key ==0){
                    set_post_thumbnail( $post_id, $res['attach_id'] );  
                }
                $data['post_content'] = str_replace($url, $res['url'], $data['post_content']);
            }
        }        
        
        $content_replaced = array(
            'ID'           => $post_id,
            'post_content' => $this->fix_content($data['post_content'])
        );
        wp_update_post( $content_replaced );

        wp_send_json_success('<a target="blank" href="' . get_permalink($post_id) . '">View post</a>');
        wp_die();
    }
    function run_import_single_product() {
        $link = $_POST['link'];        
        $data = $this->get_product($link);
        $post_args = array(
            'post_title'    => $data['post_title'],
            'post_type'     => 'product',
            'post_status'   => 'publish',
            'post_author'   => get_current_user_id()
        );

        $product  = new WC_Product();
        $product->set_name($data['post_title']);                      
        $product->set_status('publish');
        $product->set_regular_price($data['_price']);
        $product->set_sale_price($data['_sale_price']);
        

        $product_id = $product->save();
        if(!$product_id){
            wp_send_json_success('<code>Cannot import!</code>');
            wp_die();
        }

        // query all image and save
        $post_thumbnails = $data['post_thumbnail'];
        $gallery = [];
        if(!empty($post_thumbnails) and is_array($post_thumbnails)){
            foreach ($post_thumbnails as $key => $url) {
                $res = $this->save_images($url,$post_args['post_title']."-".$key);
                
                // set first image for thumbnail
                if($key ==0){
                    set_post_thumbnail( $product_id, $res['attach_id'] );  
                }
                $data['post_content'] = str_replace($url, $res['url'], $data['post_content']);
                $gallery[] = $res['attach_id'];
            }
        }        
        
        $content_replaced = array(
            'ID'           => $product_id,
            'post_content' => $this->fix_content($data['post_content'])
        );
        
        wp_update_post( $content_replaced );        
        update_post_meta($product_id,'_product_image_gallery',implode(",", $gallery));

        wp_send_json_success('<a target="blank" href="' . get_permalink($product_id) . '">View post</a>');
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
    function get_single($link){
        $return = [];

        $html = wp_remote_get($link) ['body'];
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
        //start check
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXpath($doc);
        

        $title_class = get_option('adminz_tools_post_title', 'entry-title');
        $header_class = get_option('adminz_tools_post_header_title', 'entry-header');

        $title = $xpath->query("//*[contains(@class, '" . $header_class . "')]//*[contains(@class, '" . $title_class . "')]");
        if (!is_null($title)) {
            foreach ($title as $element) {
                $nodes = $element->childNodes;
                foreach ($nodes as $node) {
                    $return['post_title'] .= $this->fix_content($node->nodeValue, $link);
                }
            }
        }

        // get entry image as first array
        $image_class = get_option('adminz_tools_post_thumbnail', 'entry-image');
        $imgs = $xpath->query("//*[contains(@class, '" . $image_class . "')]//img");
        if (!is_null($imgs)) {
            foreach ($imgs as $element) {
                if ($element->getAttribute('src')) {
                    $return['post_thumbnail'][] = $this->fix_url($element->getAttribute('src'),$link);
                    break;
                }
            }
        }

        // get all image in entry-content
        $contentclass = get_option('adminz_tools_post_content', 'entry-content');
        $imgs = $xpath->query("//*[contains(@class, '" . $contentclass . "')]//img");
        if (!is_null($imgs)) {
            foreach ($imgs as $element) {
                if ($element->getAttribute('src')) {
                    $return['post_thumbnail'][] = $this->fix_url($element->getAttribute('src'),$link);
                }
            }
        }

        $content = $xpath->query("//*[contains(@class, '" . $contentclass . "')]");
        $remove_end = get_option('adminz_tools_content_remove_end', 0);
        $remove_first = get_option('adminz_tools_content_remove_first', 0);
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
        return $return;
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
    function get_product($link){
        $return = [];

        $html = wp_remote_get($link) ['body'];
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
        //start check
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXpath($doc);

        // product type
        $type = $xpath->query("//*[contains(@class, 'type-product')]/div");
        $return['product_type'] = "simple";
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
        
        $header_class = get_option('adminz_tools_product_header_title', 'product-info');
        $title_class = get_option('adminz_tools_product_title', 'product-title');        

        $title = $xpath->query("//*[contains(@class, '" . $header_class . "')]//*[contains(@class, '" . $title_class . "')]");
        if (!is_null($title)){
            foreach ($title as $element){
                $nodes = $element->childNodes;
                foreach ($nodes as $node){
                    $return['post_title'] .= $this->fix_content($node->nodeValue, $link);
                }
            }
        }

        // get price product
        $header_class = get_option('adminz_tools_product_header_title', 'product-info');
        $price_class = get_option('adminz_tools_product_price','price-wrapper');
        $product_prices = get_option('adminz_tools_product_prices', 'woocommerce-Price-amount');

        $prices_dom = $xpath->query("//*[contains(@class, '".$header_class."')]//*[contains(@class, '".$price_class."')]//*[contains(@class, '".$product_prices."')]");
        $price_arr = [];
        if (!is_null($prices_dom)) {
            foreach ($prices_dom as $element) {
                preg_match_all('/[0-9]/', $element->textContent, $matches);
                $price_arr[] = implode("", $matches[0]);
            }
        }
        if(isset($price_arr[0])){
            $return['_price'] = $price_arr[0];
        }
        if($return['product_type'] == 'simple'){            
            if(isset($price_arr[1])){
                $return['_sale_price'] = $price_arr[1];
            }
        }

        // get entry image as first array
        $image_class = get_option('adminz_tools_product_thumbnail', 'woocommerce-product-gallery__image');
        $imgs = $xpath->query("//*[contains(@class, '" . $image_class . "')]//img");
        if (!is_null($imgs)){
            foreach ($imgs as $element){
                if ($element->getAttribute('src')){                    
                    $return['post_thumbnail'][] = $this->fix_url($element->getAttribute('src'),$link);
                    break;
                }
            }
        }

        // get all image in entry-content
        $contentclass = get_option('adminz_tools_product_content', 'entry-content');
        $imgs = $xpath->query("//*[contains(@class, '" . $contentclass . "')]//img");
        if (!is_null($imgs)){
            foreach ($imgs as $element){
                if ($element->getAttribute('src')){                    
                    $return['post_thumbnail'][] = $this->fix_url($element->getAttribute('src'),$link);
                }
            }
        }

        $content = $xpath->query("//*[contains(@class, '" . $contentclass . "')]");
        $remove_end = get_option('adminz_tools_content_remove_end', 0);
        $remove_first = get_option('adminz_tools_content_remove_first', 0);
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
        return $return;
    }
    function test_category() {
        $data = json_encode($this->get_category($_POST['link']));
        wp_send_json_success($data);
        wp_die();
    }
    function get_category($link) {
        $return = [];
        $html = wp_remote_get($link) ['body'];
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
        //start check
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXpath($doc);
        $blog_wrapper_class = get_option('adminz_tools_category_wrapper', 'blog-wrapper');
        $post_item_class = get_option('adminz_tools_category_post_item', 'post-item');;

        $posts = $xpath->query("//*[contains(@class, '" . $blog_wrapper_class . "')]//*[contains(@class, '" . $post_item_class . "')]");
        
        if (!is_null($posts)) {
            foreach ($posts as $key => $n) {
                //preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $n->textContent)
                $return[$key]['post_title'] = $n->textContent;

                $url = $n->getElementsByTagName('a')->item(0);                
                if (!is_null($url)) {
                    $return[$key]['post_url'] = $this->fix_url($url->getAttribute('href'),$link);
                }

            }
        }

        return $return;
    }
    function test_category_product() {
        $data = json_encode($this->get_category_product($_POST['link']));
        wp_send_json_success($data);
        wp_die();
    }    
    function get_category_product($link) {
        $return = [];
        $html = wp_remote_get($link) ['body'];
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
        //start check
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXpath($doc);
        $products_wrapper = get_option('adminz_tools_category_product_wrapper', 'products');
        $product_item_wrapper = get_option('adminz_tools_category_product_item', 'product-small col');;

        $posts = $xpath->query("//*[contains(@class, '" . $products_wrapper . "')]//*[contains(@class, '" . $product_item_wrapper . "')]");
        
        if (!is_null($posts)) {
            foreach ($posts as $key => $n) {
                //preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $n->textContent)
                $return[$key]['post_title'] = $n->textContent;

                $url = $n->getElementsByTagName('a')->item(0);                
                if (!is_null($url)) {
                    $return[$key]['post_url'] = $this->fix_url($url->getAttribute('href'),$link);
                }

            }
        }

        return $return;
    }
    function save_images($image_url, $posttitle) {
        $file = file_get_contents($image_url);
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
    function fix_url($url, $link) {
        preg_match('/(http(|s)):\/\/(.*?)\//si', $link, $output);
        $domain = $output[0];

        if ($url[0] == "/"){
            $url = $domain . substr($url,1);
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

        // remove all link
        $content = preg_replace('#<a.*?>(.*?)</a>#i', '\1', $content);

        // remove all script tag
        $content = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $content);

        // fix if missing domain url
        $content = preg_replace('/src="\/(.*)"/', 'src="' . $domain . '\1"', $content);

        // replce strings
        $replace_from = get_option('adminz_tools_content_replace_from', "");
        $replace_to = get_option('adminz_tools_content_replace_to', "");
        if ($replace_from){
            $content = str_replace(explode("|", str_replace(["\n", "\r"], ["|", ""], $replace_from)) , explode("|", str_replace(["\n", "\r"], ["|", ""], $replace_to)) , $content);
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
                            test_single(link,$(this).closest("td").find(".data_test"));
                        }else{
                            alert("Input link");
                        }                           
                        return false;
                    })
                    $('.run_import_single').click(function(){
                        var link = $(this).closest("td").find("input").val();
                        if(link){
                            run_import_single(link,$(this).closest("td").find(".data_test"));
                        }else{
                            alert("Input link");
                        }                           
                        return false;
                    })                  
                    $('.test_category').click(function(){
                        var link = $(this).closest("td").find("input").val();
                        if(link){
                            test_category(link,$(this).closest("td").find(".data_test"));
                        }else{
                            alert("Input link");
                        }
                        return false;
                    })
                    $('.run_import_category').click(function(){
                        var link = $(this).closest("td").find("input").val();
                        if(link){
                            run_import_category(link,$(this).closest("td").find(".data_test"));
                        }else{
                            alert("Input link");
                        }
                        return false;
                    });
                    $('.test_product').click(function(){
                        var link = $(this).closest("td").find("input").val();
                        if(link){
                            test_product(link,$(this).closest("td").find(".data_test"));
                        }else{
                            alert("Input link");
                        }                           
                        return false;
                    })
                    $('.test_category_product').click(function(){
                        var link = $(this).closest("td").find("input").val();
                        if(link){
                            test_category_product(link,$(this).closest("td").find(".data_test"));
                        }else{
                            alert("Input link");
                        }
                        return false;
                    })
                    $('.run_import_category_product').click(function(){
                        var link = $(this).closest("td").find("input").val();
                        if(link){
                            run_import_category_product(link,$(this).closest("td").find(".data_test"));
                        }else{
                            alert("Input link");
                        }
                        return false;
                    })
                    $('.run_import_single_product').click(function(){
                        var link = $(this).closest("td").find("input").val();
                        if(link){
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
                                var html_run = "";
                                html_run += "<div style='padding: 10px; background-color: white;'>";
                                html_run += "Checking...";
                                html_run +="</div>";
                                output.html(html_run);
                            },
                            success: function(response) {                                       
                                //Làm gì đó khi dữ liệu đã được xử lý
                                if(response.success) {                                          
                                    var data_test = JSON.parse(response.data);                                  
                                    var html_test = "";
                                    html_test += "<div style='padding: 10px; background-color: white;'>"; 

                                    html_test +="<code>Thumbnail</code>";
                                    html_test +="<div>";
                                    if(data_test.post_thumbnail){
                                        for (var i = 0; i < data_test.post_thumbnail.length; i++) {
                                            if(i==0){
                                                html_test +="<div><img src='"+data_test.post_thumbnail[i]+"'/></div>";
                                            }else{
                                                html_test +='<img style="margin-right: 10px; height: 70px; border: 5px solid silver;" src="'+data_test.post_thumbnail[i]+'"/>';
                                            }
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
                                var html_run = "";
                                html_run += "<div style='padding: 10px; background-color: white;'>";
                                html_run += "Importing...";
                                html_run +="</div>";
                                output.html(html_run);
                            },
                            success: function(response) {
                                //Làm gì đó khi dữ liệu đã được xử lý
                                if(response.success) {
                                    var html_run = "";
                                    html_run += "<div style='padding: 10px; background-color: white;'>";
                                    html_run += response.data;
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
                    function run_import_single_product(link,output){
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
                                var html_run = "";
                                html_run += "<div style='padding: 10px; background-color: white;'>";
                                html_run += "Importing...";
                                html_run +="</div>";
                                output.html(html_run);
                            },
                            success: function(response) {
                                //Làm gì đó khi dữ liệu đã được xử lý
                                if(response.success) {
                                    var html_run = "";
                                    html_run += "<div style='padding: 10px; background-color: white;'>";
                                    html_run += response.data;
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
                                var html_run = "";
                                html_run += "<div style='padding: 10px; background-color: white;'>";
                                html_run += "Checking...";
                                html_run +="</div>";
                                output.html(html_run);
                            },
                            success: function(response) {
                                //Làm gì đó khi dữ liệu đã được xử lý
                                if(response.success) {
                                    var data_test = JSON.parse(response.data);
                                    var html_test = "";
                                    html_test += "<div style='padding: 10px; background-color: white;'>";
                                    html_test +='<table>';
                                    for (var i = 0; i < data_test.length; i++) {                                            
                                        html_test +='<tr data-link="'+data_test[i]['post_url']+'"> <td class="status">Status</td><td><p>'+data_test[i]['post_title']+'</p><a target="blank" href="'+data_test[i]['post_url']+'">Link</a></td></tr>';
                                    }                                       
                                    html_test +='</table>';
                                    html_test +="</div>";
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
                                var html_run = "";
                                html_run += "<div style='padding: 10px; background-color: white;'>";
                                html_run += "Checking...";
                                html_run +="</div>";
                                output.html(html_run);
                            },
                            success: function(response) {
                                //Làm gì đó khi dữ liệu đã được xử lý
                                if(response.success) {
                                    var data_test = JSON.parse(response.data);
                                    var html_test = "";
                                    html_test += "<div style='padding: 10px; background-color: white;'>";
                                    html_test +='<table>';
                                    for (var i = 0; i < data_test.length; i++) {                                            
                                        html_test +='<tr data-link="'+data_test[i]['post_url']+'"> <td class="status">Status</td><td><p>'+data_test[i]['post_title']+'</p><a target="blank" href="'+data_test[i]['post_url']+'">Link</a></td></tr>';
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
                                var html_run = "";
                                html_run += "<div style='padding: 10px; background-color: white;'>";
                                html_run += "Checking...";
                                html_run +="</div>";
                                output.html(html_run);
                            },
                            success: function(response) {                                       
                                //Làm gì đó khi dữ liệu đã được xử lý
                                if(response.success) {                                          
                                    var data_test = JSON.parse(response.data);
                                    var html_test = "";
                                    html_test += "<div style='padding: 10px; background-color: white;'>"; 

                                    html_test +="<code>Thumbnail</code>";
                                    html_test +="<div>";
                                    if(data_test.post_thumbnail){
                                        for (var i = 0; i < data_test.post_thumbnail.length; i++) {
                                            if(i==0){
                                                html_test +="<div><img src='"+data_test.post_thumbnail[i]+"'/></div>";
                                            }else{
                                                html_test +='<img style="margin-right: 10px; height: 70px; border: 5px solid silver;" src="'+data_test.post_thumbnail[i]+'"/>';
                                            }
                                        }
                                    }else{
                                        html_test +="<b style='color: white; background-color: red;'>Not found</b></br>";
                                    }
                                    html_test +="</div>";

                                    html_test +="<div>";
                                    html_test +="<code>Product type:</code>";
                                    html_test +=data_test['product_type'];
                                    html_test += ' | All product type excertp: simple is <b>Incomplete!</b>';
                                    html_test +="</div>";                                    

                                    html_test +="<code>Title:</code>";
                                    if(data_test.post_title){
                                        html_test +="<h1>"+data_test.post_title+"</h1>";
                                    } else{
                                        html_test +="<b style='color: white; background-color: red;'>Not found</b></br>";
                                    }

                                    html_test +="<code>Price:</code></br>";
                                    if(data_test._price){
                                        html_test +=" <span>Regular: "+data_test._price+"</span>";
                                    } else{
                                        html_test +="<b style='color: white; background-color: red;'>Sale price not found</b></br>";
                                    }
                                    html_test +="</br>";
                                    if(data_test._sale_price){
                                        html_test +=" <span>Sale: "+data_test._sale_price+"</span>";
                                    } else{
                                        html_test +="<b style='color: white; background-color: red;'>Price not found</b></br>";
                                    }
                                    
                                    html_test +="</br>";
                                    html_test += "<code>Content:</code>";
                                    if(data_test.post_content){
                                        html_test +="<div>"+data_test.post_content+"</div>";
                                    }else{
                                        html_test +="<b style='color: white; background-color: red;'>Not found</b></br>";
                                    }
                                    html_test +="</div>";
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
                                var html_run = "";
                                html_run += "<div style='padding: 10px; background-color: white;'>";
                                html_run += "Checking...";
                                html_run +="</div>";
                                output.html(html_run);
                            },
                            success: function(response) {
                                //Làm gì đó khi dữ liệu đã được xử lý
                                if(response.success) {
                                    var data_test = JSON.parse(response.data);
                                    var html_test = "";
                                    html_test += "<div style='padding: 10px; background-color: white;'>";
                                    html_test +='<table>';
                                    for (var i = 0; i < data_test.length; i++) {                                            
                                        html_test +='<tr data-link="'+data_test[i]['post_url']+'"> <td class="status">Status</td><td><p>'+data_test[i]['post_title']+'</p><a target="blank" href="'+data_test[i]['post_url']+'">Link</a></td></tr>';
                                    }                                       
                                    html_test +='</table>';
                                    html_test +="</div>";
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
                                var html_run = "";
                                html_run += "<div style='padding: 10px; background-color: white;'>";
                                html_run += "Checking...";
                                html_run +="</div>";
                                output.html(html_run);
                            },
                            success: function(response) {
                                //Làm gì đó khi dữ liệu đã được xử lý
                                if(response.success) {
                                    var data_test = JSON.parse(response.data);
                                    var html_test = "";
                                    html_test += "<div style='padding: 10px; background-color: white;'>";
                                    html_test +='<table>';
                                    for (var i = 0; i < data_test.length; i++) {                                            
                                        html_test +='<tr data-link="'+data_test[i]['post_url']+'"> <td class="status">Status</td><td><p>'+data_test[i]['post_title']+'</p><a target="blank" href="'+data_test[i]['post_url']+'">Link</a></td></tr>';
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
                            <input type="url" name="adminz_tools_from_post" placeholder="Post url here" value="<?php echo get_option('adminz_tools_from_post', ''); ?>"> 
                        </label>
                        <button class="button test_single">Test post</button>
                        <button class="button button-primary run_import_single">Run import</button>
                        
                        <br>
                        <p class="data_test"></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">From category</th>
                    <td>
                        <label>
                            <input type="url" name="adminz_tools_from_category" placeholder="Post url here" value="<?php echo get_option('adminz_tools_from_category', ''); ?>"> 
                        </label>
                        <button class="button test_category">Test category</button>
                        <button class="button button-primary run_import_category">Run import</button>
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
                            <input type="url" name="adminz_tools_from_product" placeholder="Post url here" value="<?php echo get_option('adminz_tools_from_product', ''); ?>"> 
                        </label>
                        <button class="button test_product">Test product</button>
                        <button class="button button-primary run_import_single_product">Run import</button>
                        
                        <br>
                        <p class="data_test"></p> 
                    </td>
                </tr>  
                <tr valign="top">
                    <th scope="row">From Product category</th>
                    <td>
                        <label>
                            <input type="url" name="adminz_tools_from_product_category" placeholder="Post url here" value="<?php echo get_option('adminz_tools_from_product_category', ''); ?>"> 
                        </label>
                        <button class="button test_category_product">Test product category</button>
                        <button class="button button-primary run_import_category_product">Run import</button>
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
                    <th><h3>Customize class check</h3></th>
                    <td></td>
                </tr>
                <tr valign="top">                   
                    <th>
                        Post single
                    </th>
                    <td>
                        <p>
                            <input type="text" name="adminz_tools_post_header_title" placeholder='entry-header' value="<?php echo get_option('adminz_tools_post_header_title', 'entry-header'); ?>" />
                            <code>Header title wrapper class</code>
                        </p>
                        <p>
                            <input type="text" name="adminz_tools_post_title" placeholder='entry-title' value="<?php echo get_option('adminz_tools_post_title', 'entry-title'); ?>" />
                            <code>Title wrapper class</code>
                        </p>
                        <p>
                            <input type="text" name="adminz_tools_post_thumbnail" placeholder='entry-image' value="<?php echo get_option('adminz_tools_post_thumbnail', 'entry-image'); ?>" />
                            <code>Thumbnail wrapper class</code>
                        </p>
                        <p>
                            <input type="text" name="adminz_tools_post_content" placeholder='entry-content' value="<?php echo get_option('adminz_tools_post_content', 'entry-content'); ?>" />
                            <code>Content wrapper class</code>
                        </p>
                    </td>
                </tr>
                <tr valign="top">                   
                    <th>
                        Category/ blog
                    </th>
                    <td>
                        <p>                         
                            <input type="text" name="adminz_tools_category_wrapper" placeholder = 'blog-wrapper' value="<?php echo get_option('adminz_tools_category_wrapper', 'blog-wrapper'); ?>" />
                            <code>Category wrapper class</code> <em>| distinguish it from widget/ sidebar wrapper</em>
                        </p>
                        <p>
                            <input type="text" name="adminz_tools_category_post_item" placeholder='post-item' value="<?php echo get_option('adminz_tools_category_post_item', 'post-item'); ?>" />
                            <code>Post item wrapper class</code>
                        </p>                        
                    </td>
                </tr>
                <?php if(class_exists( 'WooCommerce' ) ){ ?>
                <tr valign="top">                   
                    <th>
                        Product single
                    </th>
                    <td>
                        <p>
                            <input type="text" name="adminz_tools_product_header_title" placeholder='product-info' value="<?php echo get_option('adminz_tools_product_header_title', 'product-info'); ?>" />
                            <code>Product header title wrapper class</code>
                        </p>
                        <p>
                            <input type="text" name="adminz_tools_product_title" placeholder='product-title' value="<?php echo get_option('adminz_tools_product_title', 'product-title'); ?>" />
                            <code>Product title wrapper class</code>
                        </p>
                        <p>
                            <input type="text" name="adminz_tools_product_price" placeholder='price-wrapper' value="<?php echo get_option('adminz_tools_product_price', 'price-wrapper'); ?>" />
                            <code>Product price wrapper class</code>
                        </p>
                        <p>
                            <input type="text" name="adminz_tools_product_prices" placeholder='woocommerce-Price-amount' value="<?php echo get_option('adminz_tools_product_prices', 'woocommerce-Price-amount'); ?>" />
                            <code>Product prices class</code>
                        </p>
                        <p>
                            <input type="text" name="adminz_tools_product_thumbnail" placeholder='woocommerce-product-gallery__image' value="<?php echo get_option('adminz_tools_product_thumbnail', 'woocommerce-product-gallery__image'); ?>" />
                            <code>Product thumbnail wrapper class</code>
                        </p>
                        <p>
                            <input type="text" name="adminz_tools_product_content" placeholder='entry-content' value="<?php echo get_option('adminz_tools_product_content', 'entry-content'); ?>" />
                            <code>Product content wrapper class</code>
                        </p>
                    </td>
                </tr>
                <tr valign="top">                   
                    <th>
                        Product list
                    </th>
                    <td>
                        <p>                         
                            <input type="text" name="adminz_tools_category_product_wrapper" placeholder = 'products' value="<?php echo get_option('adminz_tools_category_product_wrapper', 'products'); ?>" />
                            <code>Product list wrapper class</code> <em>| distinguish it from widget/ sidebar wrapper</em>
                        </p>
                        <p>
                            <input type="text" name="adminz_tools_category_product_item" placeholder='product-small col' value="<?php echo get_option('adminz_tools_category_product_item', 'product-small col'); ?>" />
                            <code>Product item wrapper class</code>
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
                        Remove line
                    </th>
                    <td>
                        <p>                         
                            <input type="number" name="adminz_tools_content_remove_first" placeholder = '0' value="<?php echo get_option('adminz_tools_content_remove_first', 0); ?>" />
                            <code>Removes the number of elements from the <strong>First</strong></code> <em>Useful when removing post meta: date, author, viewcount </em>
                        </p>
                        <p>                         
                            <input type="number" name="adminz_tools_content_remove_end" placeholder = '0' value="<?php echo get_option('adminz_tools_content_remove_end', 0); ?>" />
                            <code>Removes the number of elements from the <strong>END</strong></code> <em>Useful when removing signatures or socials share buttons</em>
                        </p>
                    </td>
                </tr>
                <tr valign="top">                   
                    <th>
                        Replace string
                    </th>
                    <td>
                        <p>                         
                            <textarea rows="3" cols="100%" class="input-text wide-input " type="text" name="adminz_tools_content_replace_from" ><?php echo get_option('adminz_tools_content_replace_from', ""); ?></textarea><br>
                        </p>
                        <p>             
                            <textarea rows="3" cols="100%" class="input-text wide-input " type="text" name="adminz_tools_content_replace_to" ><?php echo get_option('adminz_tools_content_replace_to', ""); ?></textarea><br>
                        </p>
                        <code>Each character is one line</code>
                    </td>
                </tr>
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
        register_setting($this->options_group, 'adminz_tools_from_post');        
        register_setting($this->options_group, 'adminz_tools_from_category');        
        register_setting($this->options_group, 'adminz_tools_from_product');        
        register_setting($this->options_group, 'adminz_tools_from_product_category');        

        register_setting($this->options_group, 'adminz_tools_post_header_title');
        register_setting($this->options_group, 'adminz_tools_post_title');
        register_setting($this->options_group, 'adminz_tools_post_thumbnail');
        register_setting($this->options_group, 'adminz_tools_post_content');

        register_setting($this->options_group, 'adminz_tools_category_wrapper');
        register_setting($this->options_group, 'adminz_tools_category_post_item');

        register_setting($this->options_group, 'adminz_tools_product_header_title');
        register_setting($this->options_group, 'adminz_tools_product_title');
        register_setting($this->options_group, 'adminz_tools_product_price');
        register_setting($this->options_group, 'adminz_tools_product_thumbnail');
        register_setting($this->options_group, 'adminz_tools_product_content');

        register_setting($this->options_group, 'adminz_tools_category_product_wrapper');
        register_setting($this->options_group, 'adminz_tools_category_product_item');

        register_setting($this->options_group, 'adminz_tools_content_remove_first');
        register_setting($this->options_group, 'adminz_tools_content_remove_end');
        register_setting($this->options_group, 'adminz_tools_content_replace_from');
        register_setting($this->options_group, 'adminz_tools_content_replace_to');
    }
}

