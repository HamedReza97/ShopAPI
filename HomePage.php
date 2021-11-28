<?php
/**
 *
 * @param  WP_REST_Request $request Full details about the request.
 * @return array $args.
 **/
 /**
 * Validate a request argument based on details registered to the route.
 *
 * @param  mixed            $value   Value of the 'filter' argument.
 * @param  WP_REST_Request  $request The current request object.
 * @param  string           $param   Key of the parameter. In this case it is 'filter'.
 * @return WP_Error|boolean
 */
 
 //#######################دسته بندی دلخواه#################################

function wl_catagory($request) {
	
	$custom_catagorys = get_posts( array(
    'post_type' => 'product',
    'orderby'  => 'post_date',
    'order' => 'DESC',
    
    'posts_perPage' => -1,
    'post_status' => 'publish',
    'tax_query' => array(
        array(
        'taxonomy' => 'product_cat',
        'field'    => 'term_id',
        'terms'     =>  [$request['id']],
        'operator'  => 'IN'
            )
        ),
    ));
    $i=0;
    foreach($custom_catagorys as $custom_catagory) {
		$custom_catagory_product[$i]['id'] = $custom_catagory->ID;
		$custom_catagory_product[$i]['title'] = $custom_catagory->post_title;
        $custom_catagory_product[$i]['regularPrice'] = $custom_catagory->_regular_price;
        $custom_catagory_product[$i]['salePrice'] = $custom_catagory->_sale_price; 
        if($post->_sale_price){
        $custom_catagory_product[$i]['salePrecent'] = (int)round((($custom_catagory->_regular_price - $custom_catagory->_sale_price) / $custom_catagory->_regular_price) *100);
        }
        $custom_catagory_product[$i]['featuredImage']['thumbnail'] = get_the_post_thumbnail_url($custom_catagory->ID, 'thumbnail');
		$custom_catagory_product[$i]['featuredImage']['medium'] = get_the_post_thumbnail_url($custom_catagory->ID, 'medium');
		$custom_catagory_product[$i]['featuredImage']['large'] = get_the_post_thumbnail_url($custom_catagory->ID, 'large');
		$custom_catagory_product[$i]['stock'] = $custom_catagory->_stock;
		$custom_catagory_product[$i]['averageRating'] = $custom_catagory->_wc_average_rating;
		$custom_catagory_product[$i]['totalSales'] = $custom_catagory->total_sales;
		
		$i++;
	}
	$data = $custom_catagory_product;
	
    if($data == []){
	return new WP_REST_Response("No Product Found in This Catagory", 404);
    }
    else
    {
    return new WP_REST_Response($data, 200);
    }
     
}

 //#############################پیشنهاد ویژه##########################

	function wl_specialoffer($request){
    $args = array(
    'post_type' => 'product',
    'posts_perPage' => -1
    );
        $i=0;                
	    $posts = get_posts($args);
	    foreach ($posts as $post){
		$special_offer[$i]['id'] = $post->ID;
		$special_offer[$i]['title'] = $post->post_title;
        $special_offer[$i]['regularPrice'] = $post->_regular_price;
        $special_offer[$i]['salePrice'] = $post->_sale_price; 
        if($post->_sale_price){
        $special_offer[$i]['salePrecent'] = (int)round((($post->_regular_price - $post->_sale_price) / $post->_regular_price) *100);
        }
       $special_offer[$i]['featuredImage']['thumbnail'] = get_the_post_thumbnail_url($post->ID, 'thumbnail');
		$special_offer[$i]['featuredImage']['medium'] = get_the_post_thumbnail_url($post->ID, 'medium');
		$special_offer[$i]['featuredImage']['large'] = get_the_post_thumbnail_url($post->ID, 'large');
		$special_offer[$i]['stock'] = $post->_stock;
		$special_offer[$i]['averageRating'] = $post->_wc_average_rating;
		$special_offer[$i]['totalSales'] = $post->total_sales;
		$format = 'F d, Y H:i';
        $timestamp = $post->offer_to_date;
        $special_offer[$i]['offerToDate'] = $gmt = date_i18n($format, $timestamp);
        $current_time = current_datetime();
        $time_remaining = $post->offer_to_date - $current_time->getTimestamp();
        $difference = abs($time_remaining);
        $days = floor($difference / 86400);
        $hours = floor(($difference - $days * 86400) / 3600);
        $minutes = floor(($difference - $days * 86400 - $hours * 3600) / 60);
        $seconds = floor($difference - $days * 86400 - $hours * 3600 - $minutes * 60);
        $special_offer[$i]['timeRemaining'] = "$days:$hours:$minutes:$seconds";
        $special_offer[$i]['timeRemainingTimeStamp'] = "$time_remaining";
        if ($time_remaining <= 0){
        unset($special_offer[$i]);    
        }
		$i++;
	    }
	    if ($special_offer){
	    $i=0;
        foreach ($special_offer as $special){
        $data[$i] = $special;
        $i++;
        }
	    }
    if($special_offer == []){
	return new WP_REST_Response("No Prodcut was Found!", 404);
    }
    else
    {

        return new WP_REST_Response($data, 200);
    }
        return array('order' => apply_filters('woocommerce_api_order_response', $data, $posts, $fields));
	}

//#########################بنرهای صفحه اصلی ###########################

    function wl_banner($request){
    $args = array(
	'force_no_custom_order' => true,
	'post_type' => array('attachment'),
	'post_parent' => 5776,
	'post_status' => array('inherit', 'publish'),
	'posts_perPage' => -1,
);
    $attachments = get_posts($args);
    $i = 0;
    foreach ($attachments as $attachment){
    $banner[$i]['id'] = $attachment->ID;
    $banner[$i]['postName'] = $attachment->post_name;
    $banner[$i]['postContent'] = $attachment->post_content;
    $banner[$i]['intent'] = $attachment->post_excerpt;
    $banner[$i]['link'] = $attachment->guid;
    $i++;
    }
    
    if($banner == []){
    return new WP_REST_Response("No Slider was Found!", 404);
    }
    else
    {
    return new WP_REST_Response($banner, 200);
    }
        return array('order' => apply_filters('woocommerce_api_order_response', $banner, $order, $fields));
}

 //######################محصولات پربازدید##########################

	function wl_popular($request){
    $args = array(
        'post_type' => 'product',
        'orderby'  => 'meta_value_num',
        'order' => 'DESC',
        'meta_key' => 'pageview',
        'posts_perPage' => 4,
        'paged' => $request['page'],
    );
    $posts = get_posts($args);
    $count = wp_count_posts($type = 'product');
    $i=0;                    
	 $posts = get_posts($args);
    foreach($posts as $post) {
		$popular_products[$i]['id'] = $post->ID;
		$popular_products[$i]['title'] = $post->post_title;
        $popular_products[$i]['regularPrice'] = $post->_regular_price;
        $popular_products[$i]['salePrice'] = $post->_sale_price; 
        if($post->_sale_price){
        $popular_products[$i]['salePrecent'] = (int)round((($post->_regular_price - $post->_sale_price) / $post->_regular_price) *100);
        }
        $popular_products[$i]['featuredImage']['thumbnail'] = get_the_post_thumbnail_url($post->ID, 'thumbnail');
		$popular_products[$i]['featuredImage']['medium'] = get_the_post_thumbnail_url($post->ID, 'medium');
		$popular_products[$i]['featuredImage']['large'] = get_the_post_thumbnail_url($post->ID, 'large');
		$popular_products[$i]['stock'] = $post->_stock;
		$popular_products[$i]['averageRating'] = $post->_wc_average_rating;
		$popular_products[$i]['totalSales'] = $post->total_sales;
		$popular_products[$i]['pageView'] = $post->pageview;
		$i++;
	}
    $data=new \stdClass();
    $data->total=intval($count->publish);
    $data->perPage=4;
    $data->currentPage=(int)$request['page'];
    $data->lastPage=ceil(intval($count->publish)/$data->perPage);
    $data->from=($data->currentPage-1)*$data->perPage+1;
    $data->to=$data->from+$data->perPage;
    $data->data=$popular_products;
    if($popular_products == []){
    return new WP_REST_Response("No Product was Found!", 404);
    }
    else
    {
    return new WP_REST_Response($data, 200);
    }
        return array('order' => apply_filters('woocommerce_api_order_response', $data, $order, $fields));
}

 //#################محصولات پرفروش###############################

    function wl_mostsold($request){
     $args = array(
        'post_type' => 'product',
        'orderby'  => 'meta_value_num',
        'order' => 'DESC',
        'meta_key' => 'total_sales',
        'paged' => $request['page'],
        'posts_perPage' => 4
    );
    $i=0;                    
    $count = wp_count_posts($type = 'product');
	$posts = get_posts($args);
    foreach($posts as $post) {
		$most_sold[$i]['id'] = $post->ID;
		$most_sold[$i]['title'] = $post->post_title;
        $most_sold[$i]['regularPrice'] = $post->_regular_price;
        $most_sold[$i]['salePrice'] = $post->_sale_price; 
        if($post->_sale_price){
        $most_sold[$i]['salePrecent'] = (int)round((($post->_regular_price - $post->_sale_price) / $post->_regular_price) *100);
        }
        $most_sold[$i]['featuredImage']['thumbnail'] = get_the_post_thumbnail_url($post->ID, 'thumbnail');
		$most_sold[$i]['featuredImage']['medium'] = get_the_post_thumbnail_url($post->ID, 'medium');
		$most_sold[$i]['featuredImage']['large'] = get_the_post_thumbnail_url($post->ID, 'large');
		$most_sold[$i]['stock'] = $post->_stock;
		$most_sold[$i]['averageRating'] = $post->_wc_average_rating;
		$most_sold[$i]['totalSales'] = $post->total_sales;
		$i++;
	}
	$data=new \stdClass();
    $data->total=intval($count->publish);
    $data->perPage=4;
    $data->currentPage=(int)$request['page'];
    $data->lastPage=ceil(intval($count->publish)/$data->perPage);
    $data->from=($data->currentPage-1)*$data->perPage+1;
    $data->to=$data->from+$data->perPage;
    $data->data=$most_sold;
	if($most_sold == []){
    return new WP_REST_Response("No Product was Found!", 404);
    }
    else
    {
    return new WP_REST_Response($data, 200);
    }
        return array('order' => apply_filters('woocommerce_api_order_response', $data, $order, $fields));
        
    }
    
 //#############################اسلایدر اینترو#########################

    function wl_introslider($request){
        $args = array(
        'post_type' => 'intro_slider',
        'orderby'  => 'post_date',
        'order' => 'ASC',
        'posts_perPage' => -1,
    );
    
    $intros = get_posts($args);
    $i=0;
    foreach ($intros as $intro){
        
        $intro_slider[$i]['id'] = $intro->ID;
        $intro_slider[$i]['title'] = $intro->post_title;
        $intro_slider[$i]['content'] = $intro->post_content;
        $intro_slider[$i]['link'] = $intro->guid;
        $i++;
    }
    
    if($intro_slider == []){
    return new WP_REST_Response("No Intro Slider was Found!", 404);
    }
    else
    {
    return new WP_REST_Response($intro_slider, 200);
    }
        return array('order' => apply_filters('woocommerce_api_order_response', $intro_slider, $order, $fields));
    }
    
//########################دسته بندی###########################
    
    function wl_homecatagory(){
        
    $terms = get_terms(array('taxonomy'=>'product_cat','parent'=>0));
    $term_data =[];
    $i = 0;
    foreach ($terms as $term){
    $term_data[$i]['status'] = $term->display_type;
    $term_data[$i]['id'] = $term->term_id;
    $term_data[$i]['catagory'] = $term->name;
    $image_id = get_term_meta($term->term_id, 'thumbnail_id', true );
    $term_data[$i]['icon'] = $post_thumbnail_img = wp_get_attachment_image_src( $image_id, 'large' );
    $i++;
    }
    
    if($term_data == []){
    return new WP_REST_Response("No Catagory was Found!", 404);
    }
    else
    {
    return new WP_REST_Response($term_data, 200);
    }
        return array('order' => apply_filters('woocommerce_api_order_response', $term_data, $order, $fields));
    }

//########################اسلایدرهای صفحه اصلی###########################
    
    function wl_slider(){
        $args = array(
        'force_no_custom_order' => true,
	    'post_type' => array('page_slider'),
	    'post_parent' => 5776,
	    'post_status' => array('inherit', 'publish'),
	    'posts_perPage' => -1,
    );
    
    $sliders = get_posts($args);
    
    $i=0;
    foreach ($sliders as $slider){
        
        $slider_data[$i]['id'] = $slider->ID;
        $slider_data[$i]['title'] = $slider->post_title;
        $slider_data[$i]['content'] = $slider->post_content;
        $slider_data[$i]['link'] = $slider->guid;
        $i++;
    }
     
    if($slider_data == []){
    return new WP_REST_Response("No Slider was Found!", 404);
    }
    else
    {
    return new WP_REST_Response($slider_data, 200);
    }
        return array('order' => apply_filters('woocommerce_api_order_response', $slider_data, $order, $fields));
    }
    
//#########################محصولات عمده###########################
	function wl_majorSale(){
	$posts = get_posts( array(
    'post_type' => 'product',
    'orderby'  => 'post_date',
    'order' => 'DESC',
    
    'posts_perPage' => -1,
    'post_status' => 'publish',
    'tax_query' => array(
        array(
        'taxonomy' => 'product_cat',
        'field'    => 'term_id',
        'terms'     =>  3818,
        'operator'  => 'IN'
            )
        ),
    ));
    $i=0;
    foreach($posts as $post) {
		$Major[$i]['id'] = $post->ID;
		$Major[$i]['title'] = $post->post_title;
        $Major[$i]['regularPrice'] = $post->_regular_price;
        $Major[$i]['salePrice'] = $post->_sale_price; 
        if($post->_sale_price){
        $Major[$i]['salePrecent'] = (int)round((($post->_regular_price - $post->_sale_price) / $post->_regular_price) *100);
        }
        $Major[$i]['featuredImage']['thumbnail'] = get_the_post_thumbnail_url($post->ID, 'thumbnail');
		$Major[$i]['featuredImage']['medium'] = get_the_post_thumbnail_url($post->ID, 'medium');
		$Major[$i]['featuredImage']['large'] = get_the_post_thumbnail_url($post->ID, 'large');
		$Major[$i]['stock'] = $post->_stock;
		$Major[$i]['averageRating'] = $post->_wc_average_rating;
		$Major[$i]['totalSales'] = $post->total_sales;
		$i++;
	}
	if($Major == []){
    return new WP_REST_Response("No Product was Found!", 404);
    }
    else
    {
    return new WP_REST_Response($Major, 200);
    }
        return array('order' => apply_filters('woocommerce_api_order_response', $Major, $order, $fields));
	}
	
//#########################محصولات ذخیره شده###########################	

    function wl_wishlist($request){
        
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $wishlist_id = $current_user->niki_wishlist;

    if ($user_id != null){
        
    $posts = get_posts( array(
    'post__in' => $wishlist_id,
    'post_type' => 'product',
    'orderby'  => 'post_date',
    'order' => 'DESC',
    'posts_perPage' => -1,
    ));
    $i=0;
    foreach($posts as $post) {
		$wishlist_data[$i]['id'] = $post->ID;
		$wishlist_data[$i]['title'] = $post->post_title;
        $wishlist_data[$i]['regularPrice'] = $post->_regular_price;
        $wishlist_data[$i]['salePrice'] = $post->_sale_price; 
        if($post->_sale_price){
        $wishlist_data[$i]['salePrecent'] = (int)round((($post->_regular_price - $post->_sale_price) / $post->_regular_price) *100);
        }
        $wishlist_data[$i]['featuredImage']['thumbnail'] = get_the_post_thumbnail_url($post->ID, 'thumbnail');
		$wishlist_data[$i]['featuredImage']['medium'] = get_the_post_thumbnail_url($post->ID, 'medium');
		$wishlist_data[$i]['featuredImage']['large'] = get_the_post_thumbnail_url($post->ID, 'large');
		$wishlist_data[$i]['stock'] = $post->_stock;
		$wishlist_data[$i]['averageRating'] = $post->_wc_average_rating;
		$wishlist_data[$i]['totalSales'] = $post->total_sales;
		$i++;
	}
    }
    if($user_id == 0){
    return new WP_REST_Response("User not loged in or not Found!", 404);
    }
    else
    {
	if($wishlist_data == []){
    return new WP_REST_Response("No Product was Found!", 404);
    }
    else
    {
    return new WP_REST_Response($wishlist_data, 200);
    }
    }
        return array('order' => apply_filters('woocommerce_api_order_response', $wishlist_data, $order, $fields));
	}
	
	//Define custom endpoint
add_action('rest_api_init', function() {
    
	register_rest_route('wl/v1', '/catagory', [
	    'methods'  => WP_REST_Server::READABLE,
	    'callback' =>'wl_catagory',
	    ]);    
	register_rest_route('wl/v1', '/popular', [
	    'methods'  => WP_REST_Server::READABLE,
	    'callback' =>'wl_popular',
	    'page' => array (
        'required' => true
        ),
	    ]);  
	register_rest_route('wl/v1', '/mostsold', [
	    'methods'  => WP_REST_Server::READABLE,
	    'callback' =>'wl_mostsold',
	    'page' => array (
        'required' => true
        ),
	    ]);
	register_rest_route('wl/v1', '/specialoffer', [
	    'methods'  => WP_REST_Server::READABLE,
	    'callback' =>'wl_specialoffer',
	    ]);   
	register_rest_route('wl/v1', '/banner', [
	    'methods'  => WP_REST_Server::READABLE,
	    'callback' =>'wl_banner',
	    ]); 
	register_rest_route('wl/v1', '/slider', [
	    'methods'  => WP_REST_Server::READABLE,
	    'callback' =>'wl_slider',
	    ]); 
	register_rest_route('wl/v1', '/introslider', [
	    'methods'  => WP_REST_Server::READABLE,
	    'callback' =>'wl_introslider',
	    ]);    
	register_rest_route('wl/v1', '/homecatagory', [
	    'methods'  => WP_REST_Server::READABLE,
	    'callback' =>'wl_homecatagory',
	    ]);     
	register_rest_route('wl/v1', '/majorsale', [
	    'methods'  => WP_REST_Server::READABLE,
	    'callback' =>'wl_majorsale',
	    ]);     
	register_rest_route('wl/v1', '/wishlist', [
	    'methods'  => WP_REST_Server::READABLE,
	    'callback' =>'wl_wishlist',
	    ]);     
});
    