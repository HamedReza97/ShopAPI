<?php
    include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
    include_once WC_ABSPATH . 'includes/wc-notice-functions.php';
    include_once WC_ABSPATH . 'includes/wc-template-hooks.php';
//reviews
	function wl_review($request){	
	    if ($request['page'] == ""){
	        $request['page'] = 1;
	    }
	    $comment_args = [
	    'post_id' => $request['id'],
	    'type' => 'review'
	    ];

	    $comments = get_comments($comment_args);
		$y = 0;
		foreach($comments as $comment){
		    if ($comment->comment_post_ID==$request['id']){
		if ($comment->comment_parent == 0){
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
	
function wl_postReview($request){
    $method = $_SERVER['REQUEST_METHOD'];
    switch ($method) {
    case 'PUT':
    $current_user = wp_get_current_user();
    if ($current_user->ID == 0){
        $output = 1;
        $comment_data = [];
        $arr = array();
        $arr['code'] = 403;
        $arr['message'] = "Forbidden";
        $arr['error'] = true;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = null; 
        return new WP_REST_Response($response, 403);
    }
    else{
    $comment = json_decode($request->get_body());
    $comment_data['comment_type'] = $comment->comment_type;
    $comment_data['comment_ID'] = $request['comment_ID'];
    $comment_data['comment_post_ID'] = $comment->comment_post_ID;
    $comment_data['comment_content'] = $comment->comment_content;
    $comment_data['comment_parent'] = $comment->comment_parent;
    $comment_data['comment_date'] = current_time('mysql');
    $comment_data['comment_date_gtm'] = current_time('mysql',true);
    $comment_data['user_id'] = $current_user->ID;
    $comment_data['comment_author'] = $current_user->display_name;
    $comment_data['comment_author_email'] = $current_user->user_email;
    $comment_data['comment_author_IP'] = $_SERVER['REMOTE_ADDR'];
    $comment_data['comment_meta']['rating'] = $comment->comment_meta->rating;
    $comment_data['comment_meta']['comment_title'] = $comment->comment_meta->comment_title;
    $comment_data['comment_meta']['comment_advantages'] = $comment->comment_meta->comment_advantages;
    $comment_data['comment_meta']['comment_disadvantages'] = $comment->comment_meta->comment_disadvantages;
   wp_update_comment( $comment_data );
    if ( is_wp_error( $comment_data ) ) {
            $output = 1;
        }
    }
    break;
    case 'POST':
    $current_user = wp_get_current_user();
    if ($current_user->ID == 0){
        $output = 1;
        $comment_data = [];
        $arr = array();
        $arr['code'] = 403;
        $arr['message'] = "Forbidden";
        $arr['error'] = true;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = null; 
        return new WP_REST_Response($response, 403);
    }
    else{
    
    if ($request->get_body()){
    $comment = json_decode($request->get_body());
    $comment_data['comment_type'] = $comment->comment_type;
    $comment_data['comment_post_ID'] = $comment->comment_post_ID;
    $comment_data['comment_content'] = $comment->comment_content;
    $comment_data['comment_parent'] = $comment->comment_parent;
    $comment_data['comment_date'] = current_time('mysql');
    $comment_data['comment_date_gtm'] = current_time('mysql',true);
    $comment_data['user_id'] = $current_user->ID;
    $comment_data['comment_author'] = $current_user->display_name;
    $comment_data['comment_author_email'] = $current_user->user_email;
    $comment_data['comment_author_IP'] = $_SERVER['REMOTE_ADDR'];
    $comment_data['comment_meta']['rating'] = $comment->comment_meta->rating;
    $comment_data['comment_meta']['comment_title'] = $comment->comment_meta->comment_title;
    $comment_data['comment_meta']['comment_advantages'] = $comment->comment_meta->comment_advantages;
    $comment_data['comment_meta']['comment_disadvantages'] = $comment->comment_meta->comment_disadvantages;
    $comment_id = wp_insert_comment($comment_data);
    $comment_data['comment_ID'] = $comment_id;
    }
    // return $comment_data;
    if ( is_wp_error( $comment_data ) ) {
            $output = 1;
        }
    }
    break;
    case 'DELETE':
        $current_user = wp_get_current_user();
        if ($current_user->ID == 0){
        $output = 1;
        $comment_data = [];
        $arr = array();
        $arr['code'] = 403;
        $arr['message'] = "Forbidden";
        $arr['error'] = true;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = null; 
        return new WP_REST_Response($response, 403);
        }
        else{
        if(get_comment($request['comment_ID']) & get_comment($request['comment_ID'])->user_id == $current_user->ID){
        wp_delete_comment($request['comment_ID'],true);
        $comment_data = [];
        $comment_data['message'] = 'Review has been removed';
        }
        else
        {
        $output = 1;
        $comment_data = [];
        $arr = array();
        $arr['code'] = 403;
        $arr['message'] = "Forbidden";
        $arr['error'] = true;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = null; 
        return new WP_REST_Response($response, 403);
        }
        }
    break;    
    }
    $arr = array();
        $arr['code'] =$output == 1 ? 404:200;
        $arr['message'] = $output == 1 ? "Operation Failed":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $output == 1 ? null:$comment_data; 
        return new WP_REST_Response($response, $output == 1 ? 404:200);
}


add_action('rest_api_init', function() {
	register_rest_route('wl/v1', 'postReview', [
		'methods' => array('GET','POST', 'PUT', 'DELETE'),
		'callback' => 'wl_postReview',
	]);
	
	register_rest_route( 'wl/v1', '/review', [
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'wl_review',
    ]);

});