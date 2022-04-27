<?php
    function wl_SortintItem(){
        $response = array();
        $response[0]['Title'] = "پیشفرض";
        $response[0]['Tag'] = "default";
        
        $response[1]['Title'] = "بیشترین فروش";
        $response[1]['Tag'] = "sell";
        
        $response[2]['Title'] = " قیمت صعودی";
        $response[2]['Tag'] = "priceTop";
        
        $response[3]['Title'] = "قیمت نزولی";
        $response[3]['Tag'] = "priceDown";
        
        $response[4]['Title'] = "جدیدترین";
        $response[4]['Tag'] = "new";
        
        $response[5]['Title'] = "قدیمی ترین";
        $response[5]['Tag'] = "old";
        
        return new WP_REST_Response($response, 200);
    }
    
    function wl_Colors(){
        $response = array();
        $response[0]['Title'] = "قرمز شگفت انگیز";
        $response[0]['Colors'] = ['#F25F70','#FF5959','#EF394E','#EF394E','#EF394E'];
        
        $response[1]['Title'] = "آبی";
        $response[1]['Colors'] = ['#0FB3D1','#2742FF'];
        
        
        
        return new WP_REST_Response($response, 200);
    }
    
    function wl_boxes(){
        $args = array(
        'force_no_custom_order' => true,
	    'post_type' => array('PiAppHomeProductBox'),
	    'post_status' => array('publish'),
	    'posts_perPage' => -1,
	    'orderby'  => 'post_parent',
        'order' => 'ASC',
    );
    
    $sliders = get_posts($args);
    
    $i=0;
    foreach ($sliders as $slider){
        
        $slider_data[$i]['ID'] = $slider->ID;
        $slider_data[$i]['Title'] = $slider->post_title;
        $slider_data[$i]['Position'] = $slider->post_parent;
        $slider_data[$i]['More'] = $slider->comment_count;
        $slider_data[$i]['Time'] = $slider->post_content;
        $slider_data[$i]['ColorJson'] = $slider->post_mime_type;
        $slider_data[$i]['API'] = $slider->post_content_filtered;
        $slider_data[$i]['Type'] = explode("-",$slider->post_name)[0];
        $slider_data[$i]['CatId'] = $slider->menu_order;
        $i++;
    }
        $arr = array();
        $data = $slider_data;
        $arr['code'] =$data == [] ? 404:200;
        $arr['message'] = $data == [] ? "No Box Found":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $data == [] ? null:$data; 
        return new WP_REST_Response($response, $data == [] ? 404:200);
    }




function wl_catagoryHomeOrderByDate($request) {
	
	$custom_catagorys = get_posts( array(
    'post_type' => 'product',
     'orderby'  => 'post_date',
    'order' => 'DESC',
    'posts_per_page' => -1,
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
		
					$format = 'F d, Y H:i';
        $timestamp = $post->offer_to_date;
        $custom_catagory_product[$i]['offerToDate'] = $gmt = date_i18n($format, $timestamp);
        $current_time = current_datetime();
        $time_remaining = $post->offer_to_date - $current_time->getTimestamp();
        $difference = abs($time_remaining);
        $days = floor($difference / 86400);
        $hours = floor(($difference - $days * 86400) / 3600);
        $minutes = floor(($difference - $days * 86400 - $hours * 3600) / 60);
        $seconds = floor($difference - $days * 86400 - $hours * 3600 - $minutes * 60);

// 		$custom_catagory_product[$i]['timeRemaining'] = "$days:$hours:$minutes:$seconds";
//         $custom_catagory_product[$i]['timeRemainingTimeStamp'] = "$time_remaining";
		$custom_catagory_product[$i]['timeRemaining'] = "0";
        $custom_catagory_product[$i]['timeRemainingTimeStamp'] = "0";
		
		$i++;
	}
	$data = $custom_catagory_product;
	
        $arr = array();
        $arr['code'] =$data == [] ? 404:200;
        $arr['message'] = $data == [] ? "No Custom CatProduct Found":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $data == [] ? null:$data; 
        return new WP_REST_Response($response, $data == [] ? 404:200);
     
}



function wl_categoryHomeProduct($request) {
	
	$custom_catagorys = get_posts( array(
    'post_type' => 'product',

    'posts_per_page' => -1,
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
			$format = 'F d, Y H:i';
        $timestamp = $post->offer_to_date;
        $custom_catagory_product[$i]['offerToDate'] = $gmt = date_i18n($format, $timestamp);
        $current_time = current_datetime();
        $time_remaining = $post->offer_to_date - $current_time->getTimestamp();
        $difference = abs($time_remaining);
        $days = floor($difference / 86400);
        $hours = floor(($difference - $days * 86400) / 3600);
        $minutes = floor(($difference - $days * 86400 - $hours * 3600) / 60);
        $seconds = floor($difference - $days * 86400 - $hours * 3600 - $minutes * 60);

// 		$custom_catagory_product[$i]['timeRemaining'] = "$days:$hours:$minutes:$seconds";
//         $custom_catagory_product[$i]['timeRemainingTimeStamp'] = "$time_remaining";
		$custom_catagory_product[$i]['timeRemaining'] = "0";
        $custom_catagory_product[$i]['timeRemainingTimeStamp'] = "0";
		$custom_catagory_product[$i]['stock'] = $custom_catagory->_stock;
		$custom_catagory_product[$i]['averageRating'] = $custom_catagory->_wc_average_rating;
		$custom_catagory_product[$i]['totalSales'] = $custom_catagory->total_sales;
		
		$i++;
	}
	$data = $custom_catagory_product;
	
        $arr = array();
        $arr['code'] =$data == [] ? 404:200;
        $arr['message'] = $data == [] ? "No Custom CatProduct Found":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $data == [] ? null:$data; 
        return new WP_REST_Response($response, $data == [] ? 404:200);
     
}

function wl_DefaultProduct($request) {
	
	$custom_catagorys = get_posts( array(
    'post_type' => 'product',

    'posts_per_page' => -1,
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
			$format = 'F d, Y H:i';
        $timestamp = $post->offer_to_date;
        $custom_catagory_product[$i]['offerToDate'] = $gmt = date_i18n($format, $timestamp);
        $current_time = current_datetime();
        $time_remaining = $post->offer_to_date - $current_time->getTimestamp();
        $difference = abs($time_remaining);
        $days = floor($difference / 86400);
        $hours = floor(($difference - $days * 86400) / 3600);
        $minutes = floor(($difference - $days * 86400 - $hours * 3600) / 60);
        $seconds = floor($difference - $days * 86400 - $hours * 3600 - $minutes * 60);

// 		$custom_catagory_product[$i]['timeRemaining'] = "$days:$hours:$minutes:$seconds";
//         $custom_catagory_product[$i]['timeRemainingTimeStamp'] = "$time_remaining";
		$custom_catagory_product[$i]['timeRemaining'] = "0";
        $custom_catagory_product[$i]['timeRemainingTimeStamp'] = "0";
		$custom_catagory_product[$i]['stock'] = $custom_catagory->_stock;
		$custom_catagory_product[$i]['averageRating'] = $custom_catagory->_wc_average_rating;
		$custom_catagory_product[$i]['totalSales'] = $custom_catagory->total_sales;
		
		$i++;
	}
	$data = $custom_catagory_product;
	
        $arr = array();
        $arr['code'] =$data == [] ? 404:200;
        $arr['message'] = $data == [] ? "No Custom CatProduct Found":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $data == [] ? null:$data; 
        return new WP_REST_Response($response, $data == [] ? 404:200);
     
}
        

	function wlReviewCount($request){	
	    if ($request['page'] == ""){
	        $request['page'] = 1;
	    }
	     $comment_args = [
	    'post_id' => $request['id'],
	    'type' => 'review',
	    'status' => 'approve'
	    ];

	    $comments = get_comments($comment_args);
		$y = 0;
		foreach($comments as $comment){
		    if ($comment->comment_post_ID==$request['id']){
		if ($comment->comment_parent == 0){
		$outputs[$y]['commentId'] = $comment->comment_ID;
		$outputs[$y]['commentPostId'] = $comment->comment_post_ID;
		 $reply_args = [
	    'post_id' => $request['id'],
        'parent' => $comment->comment_ID,
	    ];
	    $replyes2 = get_comments($reply_args);
	    $outputs[$y]['replies'] = count($replyes2);
		$outputs[$y]['parent'] = $comment->comment_parent;
		$outputs[$y]['commentAuthor'] = $comment->comment_author == "" ? null:$comment->comment_author;
        $outputs[$y]['commentDate'] = $comment->comment_date;
        $outputs[$y]['commentContent'] = $comment->comment_content;
        $outputs[$y]['commentType'] = $comment->comment_type;
        $outputs[$y]['commentRate'] = get_comment_meta($comment->comment_ID, 'rating', true) == "" ? null:get_comment_meta($comment->comment_ID, 'rating', true);
        $outputs[$y]['commentTitle'] = get_comment_meta($comment->comment_ID, 'comment_title', true) == "" ? null:get_comment_meta($comment->comment_ID, 'comment_title', true);
        $outputs[$y]['commentAdvantages'] = get_comment_meta($comment->comment_ID, 'comment_advantages', true) == "" ? null: get_comment_meta($comment->comment_ID, 'comment_advantages', true);
        $outputs[$y]['commentDisdvantages'] = get_comment_meta($comment->comment_ID, 'comment_disadvantages', true) == "" ? null: get_comment_meta($comment->comment_ID, 'comment_disadvantages', true);
        $y ++;
		}
		}
        }
        $y=0;
        
        $reply_args = [
	    'post_id' => $request['id'],
        'parent' => $request['parent'],
	    ];
	    $replyes = get_comments($reply_args);
	    
        foreach($replyes as $reply){
		$replies[$y]['commentId'] = $reply->comment_ID;

	    $replies[$y]['commentPostId'] = $reply->comment_post_ID;
		$replies[$y]['parent'] = $reply->comment_parent;
		$replies[$y]['commentAuthor'] = $reply->comment_author == "" ? null:$reply->comment_author;
        $replies[$y]['commentDate'] = $reply->comment_date;
        $replies[$y]['commentContent'] = $reply->comment_content;
        $replies[$y]['commentType'] = $reply->comment_type;
        $replies[$y]['commentRate'] = get_comment_meta($reply->comment_ID, 'rating', true) == "" ? null:get_comment_meta($reply->comment_ID, 'rating', true);
        $replies[$y]['commentTitle'] = get_comment_meta($reply->comment_ID, 'comment_title', true) == "" ? null:get_comment_meta($reply->comment_ID, 'comment_title', true);
        $replies[$y]['commentAdvantages'] = get_comment_meta($reply->comment_ID, 'comment_advantages', true) == "" ? null: get_comment_meta($reply->comment_ID, 'comment_advantages', true);
        $replies[$y]['commentDisdvantages'] = get_comment_meta($reply->comment_ID, 'comment_disadvantages', true) == "" ? null: get_comment_meta($reply->comment_ID, 'comment_disadvantages', true);
		$y ++;
        }
        
        if ($request['parent']){
        $output = $replies;
        $count = count($replies);
        }
        else 
        {
        $output = $outputs;
        $count = count($outputs);
        }
        $pData = array_chunk($output, 4);
	    if ($request['page'] ==  []){
	    $output = $pData[0];
	    }
	    else
    	{
	    $output = $pData[$request['page']-1];
	    }
        $data=new \stdClass();
        $data->total=($count);
        $data->perPage=4;
        $data->currentPage=(int)$request['page'];
        $data->lastPage=ceil(($count)/$data->perPage);
        $data->from=($data->currentPage-1)*$data->perPage+1;
        $data->to=$data->from+$data->perPage;
        $data->data=$output;
        
        $arr = array();
        $arr['code'] =$output == [] ? 404:200;
        $arr['message'] = $output == [] ? "Product Not Found or no review Available for This Product":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $output == [] ? null:$data; 
        return new WP_REST_Response($response, $output == [] ? 404:200);
	}
	

	function wlReviewCount2($request){	
	    if ($request['page'] == ""){
	        $request['page'] = 1;
	    }
	    $comment_args = [
	    'post_id' => $request['id'],
		'number' => 4,
        'paged' => $request['page'],
	    ];
	    $count =  get_comments(array('post_id' => $request['id']
	    ));
	    
	    $comments = get_comments($comment_args);
		$y = 0;
		foreach($comments as $comment){
		    if ($comment->comment_post_ID==$request['id']){
		if ($comment->comment_parent == 0){
		    $reply_args = [
	    'post_id' => $request['id'],
		'number' => -1,
        'parent' => $comment->comment_ID,
	    ];
	    $replyes = get_comments($reply_args);
	    $outputs[$y]['replies'] = count($replyes);
		$outputs[$y]['commentId'] = $comment->comment_ID;
		$outputs[$y]['commentPostId'] = $comment->comment_post_ID;
		$outputs[$y]['parent'] = $comment->comment_parent;
		$outputs[$y]['commentAuthor'] = $comment->comment_author == "" ? null:$comment->comment_author;
        $outputs[$y]['commentDate'] = $comment->comment_date;
        $outputs[$y]['commentContent'] = $comment->comment_content;
        $outputs[$y]['commentType'] = $comment->comment_type;
        $outputs[$y]['commentRate'] = get_comment_meta($comment->comment_ID, 'rating', true) == "" ? null:get_comment_meta($comment->comment_ID, 'rating', true);
        $outputs[$y]['commentTitle'] = get_comment_meta($comment->comment_ID, 'comment_title', true) == "" ? null:get_comment_meta($comment->comment_ID, 'comment_title', true);
        $outputs[$y]['commentAdvantages'] = get_comment_meta($comment->comment_ID, 'comment_advantages', true) == "" ? null: get_comment_meta($comment->comment_ID, 'comment_advantages', true);
        $outputs[$y]['commentDisdvantages'] = get_comment_meta($comment->comment_ID, 'comment_disadvantages', true) == "" ? null: get_comment_meta($comment->comment_ID, 'comment_disadvantages', true);
        $y ++;
		}
		}
        }
        $y=0;
        
        $reply_args = [
	    'post_id' => $request['id'],
		'number' => -1,
        'parent' => $request['parent'],
	    ];
	    $replyes = get_comments($reply_args);
	    
        foreach($replyes as $reply){
		$replies[$y]['commentId'] = $reply->comment_ID;
		$reply_args = [
	    'post_id' => $request['id'],
		'number' => -1,
        'parent' => $reply->comment_ID,
	    ];
	    $replyes = get_comments($reply_args);
	    $replies[$y]['replies'] = count($replyes);
		$replies[$y]['commentPostId'] = $reply->comment_post_ID;
		$replies[$y]['parent'] = $reply->comment_parent;
		$replies[$y]['commentAuthor'] = $reply->comment_author == "" ? null:$reply->comment_author;
        $replies[$y]['commentDate'] = $reply->comment_date;
        $replies[$y]['commentContent'] = $reply->comment_content;
        $replies[$y]['commentType'] = $reply->comment_type;
        $replies[$y]['commentRate'] = get_comment_meta($reply->comment_ID, 'rating', true) == "" ? null:get_comment_meta($reply->comment_ID, 'rating', true);
        $replies[$y]['commentTitle'] = get_comment_meta($reply->comment_ID, 'comment_title', true) == "" ? null:get_comment_meta($reply->comment_ID, 'comment_title', true);
        $replies[$y]['commentAdvantages'] = get_comment_meta($reply->comment_ID, 'comment_advantages', true) == "" ? null: get_comment_meta($reply->comment_ID, 'comment_advantages', true);
        $replies[$y]['commentDisdvantages'] = get_comment_meta($reply->comment_ID, 'comment_disadvantages', true) == "" ? null: get_comment_meta($reply->comment_ID, 'comment_disadvantages', true);
		$y ++;
        }
        if ($request['parent']){
        $output = $replies;
        }
        else 
        {
        $output = $ouitputs;
        }
        $data=new \stdClass();
        $data->total=count($count);
        $data->perPage=4;
        $data->currentPage=(int)$request['page'];
        $data->lastPage=ceil(count($count)/$data->perPage);
        $data->from=($data->currentPage-1)*$data->perPage+1;
        $data->to=$data->from+$data->perPage;
        $data->data=$output;
        
        $arr = array();
        $arr['code'] =$output == [] ? 404:200;
        $arr['message'] = $output == [] ? "Product Not Found or no review Available for This Product":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $output == [] ? null:$data; 
        return new WP_REST_Response($response, $output == [] ? 404:200);
	}
	
	// Banner Gettttttting
	
	   function wlBannerOfParentPage2($request){
    $args = array(
	'force_no_custom_order' => true,
	'post_type' => array('PiAppAttachment'),
	'post_parent' => 0,
	'post_status' => array('publish'),
	'posts_per_page' => -1,
);
    $attachments = get_posts($args);
    $i = 0;
    foreach ($attachments as $attachment){
    $banner[$i]['id'] = $attachment->ID;
    $banner[$i]['postName'] = $attachment->post_name;
    $banner[$i]['call'] = $attachment->post_content_filtered;
    $banner[$i]['postContent'] = $attachment->post_content;
    $intent = $attachment->post_name;
    if(empty($intent)){
        $banner[$i]['intent'] = "none";
    }else{
        $intent = $attachment->post_name; 
    }
    $banner[$i]['intent'] = $intent;
    $banner[$i]['type'] = $attachment->post_mime_type;
    $banner[$i]['position'] = $attachment->post_excerpt;
    $banner[$i]['link'] = $attachment->guid;
    $i++;
    }
    
    $data = $banner;
            $arr = array();
        $arr['code'] =$data == [] ? 404:200;
        $arr['message'] = $data == [] ? "No Banner Found":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $data == [] ? null:$data; 
        return new WP_REST_Response($response, $data == [] ? 404:200);
        
        return array('order' => apply_filters('woocommerce_api_order_response', $banner, $order, $fields));
}
	   function wlBannerOfParentPage($request){
    $args = array(
	'force_no_custom_order' => true,
	'post_type' => array('PiAppAttachment'),
	'post_parent' => $request['parent'],
	'post_status' => array('publish'),
	'posts_per_page' => -1,
);
    $attachments = get_posts($args);
    $i = 0;
    foreach ($attachments as $attachment){
    $banner[$i]['id'] = $attachment->ID;
    $banner[$i]['postName'] = $attachment->post_name;
    $banner[$i]['call'] = $attachment->post_content_filtered;
    $brand = $attachment->post_content;
    if(empty($attachment->post_content) || $attachment->post_content == null){
        $brand = "null";
    }
    $banner[$i]['postContent'] = $brand."/".$attachment->menu_order;
    $intent = $attachment->post_name;
    if(empty($intent)){
        $intent = "none";
    }else{
        $intent = $attachment->post_name; 
    }
    $banner[$i]['intent'] = $attachment->post_mime_type."&".$attachment->post_excerpt . "\n" . $intent;
    
    $banner[$i]['link'] = $attachment->guid;
    $i++;
    }
    
    $data = $banner;
            $arr = array();
        $arr['code'] =$data == [] ? 404:200;
        $arr['message'] = $data == [] ? "No Banner Found":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $data == [] ? null:$data; 
        return new WP_REST_Response($response, $data == [] ? 404:200);
        
        return array('order' => apply_filters('woocommerce_api_order_response', $banner, $order, $fields));
}

	
	// This is mostSpeacial Of categories
	
	
	   function wl_cat_specails($request){
     
     $args = array(
        'post_type' => 'product',
        'orderby'  => 'meta_value_num',
        'order' => 'DESC',
        'meta_key' => 'total_sales',
        'paged' => $request['page'],
        'numberposts' => -1,
        'tax_query' => array(
        array(
        'taxonomy' => 'product_cat',
        'field'    => 'term_id',
        'terms'     =>  [$request['id']],
        'operator'  => 'IN'
            )
        ),
        '_stock' => '0'
    );
    $i=0;                    
    $count = wp_count_posts($type = 'product');
	$posts = get_posts($args);
    foreach($posts as $post) {
        if($post->_stock <= 0 )
            continue;
        if($i>=$request['number'])
            break;
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
    $data->perPage=3;
    $data->currentPage=(int)$request['page'];
    $data->lastPage=ceil(intval($count->publish)/$data->perPage);
    $data->from=($data->currentPage-1)*$data->perPage+1;
    $data->to=$data->from+$data->perPage;
    $data->data=$most_sold;

        $arr = array();
        $arr['code'] =$data == [] ? 404:200;
        $arr['message'] = $data == [] ? "No MostSold Found":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $data == [] ? null:$data; 
        return new WP_REST_Response($response, $data == [] ? 404:200);
        

        return array('order' => apply_filters('woocommerce_api_order_response', $data, $order, $fields));
        
    }
	
	
	//Brand Special
	
	
	
	   function wl_brand_specails($request){
     
     $args = array(
        'post_type' => 'product',
        'orderby'  => 'meta_value_num',
        'order' => 'DESC',
        'meta_key' => 'total_sales',
        'paged' => $request['page'],
        'numberposts' => -1,
        'tax_query' => array(
        array(
        'taxonomy' => 'pa_برند',
        'field'    => 'term_id',
        'terms'     =>  [$request['brand']],
        'operator'  => 'IN'
            )
        ),
        '_stock' => '0'
    );
    $i=0;                    
    $count = wp_count_posts($type = 'product');
	$posts = get_posts($args);
    foreach($posts as $post) {
        if($post->_stock <= 0 )
            continue;
        if($i>=$request['number'])
            break;
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
		$most_sold[$i]['urlFirst'] = "https://www.onlineshoo.com/wp-content/plugins/woocommerce-offer-nikan/assets/images/slider-banner.png";
		$most_sold[$i]['stock'] = $post->_stock;
		$most_sold[$i]['averageRating'] = $post->_wc_average_rating;
		$most_sold[$i]['totalSales'] = $post->total_sales;
		$i++;
	}
	$data=new \stdClass();
    $data->total=intval($count->publish);
    $data->perPage=3;
    $data->currentPage=(int)$request['page'];
    $data->lastPage=ceil(intval($count->publish)/$data->perPage);
    $data->from=($data->currentPage-1)*$data->perPage+1;
    $data->to=$data->from+$data->perPage;
    $data->data=$most_sold;

        $arr = array();
        $arr['code'] =$data == [] ? 404:200;
        $arr['message'] = $data == [] ? "No MostSold Found":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $data == [] ? null:$data; 
        return new WP_REST_Response($response, $data == [] ? 404:200);
        

        return array('order' => apply_filters('woocommerce_api_order_response', $data, $order, $fields));
        
    }
	
	
	function wlGiveBrand($req){
    $terms = get_terms(
            array(
                    'taxonomy'=>'pa_برند',        'orderby'  => 'count',
    'order' => 'DESC',
                )
        );
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
        $data = $term_data;
        $arr = array();
        $arr['code'] =$data == [] ? 404:200;
        $arr['message'] = $data == [] ? "No IntroSlider Found":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $data == [] ? null:$data; 
        return new WP_REST_Response($response, $data == [] ? 404:200);
        

}

	
	//Define custom endpoint
add_action('rest_api_init', function() {

	register_rest_route('wl/v1', '/layout', [
	    'methods'  => WP_REST_Server::READABLE,
	    'callback' =>'wl_boxes',
	    ]);  
	    	register_rest_route('wl/v1', '/catagoryHome', [
	    'methods'  => WP_REST_Server::READABLE,
	    'callback' =>'wl_categoryHomeProduct',
	    ]);  	
	    	register_rest_route('wl/v1', '/catagoryHomeProduct', [
	    'methods'  => WP_REST_Server::READABLE,
	    'callback' =>'wl_categoryHomeProduct',
	    ]);  	   
	    	register_rest_route('wl/v1', '/defaultProduct', [
	    'methods'  => WP_REST_Server::READABLE,
	    'callback' =>'wl_DefaultProduct',
	    ]);  	   
	    register_rest_route('wl/v1', '/catagoryHomeOrderByDate', [
	    'methods'  => WP_REST_Server::READABLE,
	    'callback' =>'wl_catagoryHomeOrderByDate',
	    ]);  
	    register_rest_route('wl/v1', '/catagoryHomeOrderByDateProduct', [
	    'methods'  => WP_REST_Server::READABLE,
	    'callback' =>'wl_catagoryHomeOrderByDate',
	    ]);  
        register_rest_route('wl/v1', '/commentsTest', [
	    'methods'  => WP_REST_Server::READABLE,
	    'callback' =>'wlReviewCount',
	    ]); 
        register_rest_route('wl/v1', '/bannerOfParent', [
	    'methods'  => WP_REST_Server::READABLE,
	    'callback' =>'wlBannerOfParentPage',
	    ]);
        register_rest_route('wl/v1', '/bannerOfParent2', [
	    'methods'  => WP_REST_Server::READABLE,
	    'callback' =>'wlBannerOfParentPage2',
	    ]); 
        register_rest_route('wl/v1', '/catSpecial', [
	    'methods'  => WP_REST_Server::READABLE,
	    'callback' =>'wl_cat_specails',
	    ]); 
        register_rest_route('wl/v1', '/brandSpecial', [
	    'methods'  => WP_REST_Server::READABLE,
	    'callback' =>'wl_brand_specails',
	    ]); 
        register_rest_route('wl/v1', '/brands', [
	    'methods'  => WP_REST_Server::READABLE,
	    'callback' =>'wlGiveBrand',
	    ]); 
	    
        register_rest_route('wl/v1', '/sortingItem', [
	    'methods'  => WP_REST_Server::READABLE,
	    'callback' =>'wl_SortintItem',
	    ]); 
        register_rest_route('wl/v1', '/color', [
	    'methods'  => WP_REST_Server::READABLE,
	    'callback' =>'wl_Colors',
	    ]); 
});
    
?>