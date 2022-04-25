<?php
    
    
    // request = {position:int , banner}
    //banner {  }
function addSlideShow($req){
if ( ! function_exists( 'wp_handle_upload' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
}

$uploadedfile = $_FILES['file'];


$upload_overrides = array(
    'test_form' => false
);
 
$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

    
if ( $movefile && ! isset( $movefile['error'] ) ) {
$bannerData['post_title'] = $req['title'];
$type = 0;
$position = $req['position'];
$intent = $req['intent'];
$bannerData['post_excerpt'] = $position ;
$bannerData['post_mime_type'] = $type ;
$bannerData['ID']=$req['id'];
$bannerData['post_parent'] = 0;
$bannerData['post_type'] ='PiAppAttachment'; 
$bannerData['guid'] = $movefile['url'];
$bannerData['post_content_filtered'] = $intent;
    wp_update_post($bannerData);
    $response['code'] = 200;
    return new WP_REST_Response($response, 200);

    var_dump( $bannerData );
} else {
    $response['code'] = 500;
    $response['msg'] = $movefile['error'];
    return new WP_REST_Response($response, 500);

}

}




function updateBanners($req){
if ( ! function_exists( 'wp_handle_upload' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
}

$uploadedfile = $_FILES['file'];


$upload_overrides = array(
    'test_form' => false
);
 
$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

    
if ( $movefile && ! isset( $movefile['error'] ) ) {
$bannerData['post_title'] = $req['title'];
$type = $req['type'];
$position = $req['position'];
$intent = $req['intent'];
$bannerData['post_excerpt'] = $position ;
$bannerData['post_mime_type'] = $type ;
$bannerData['ID']=$req['id'];
$bannerData['post_parent'] = 0;
$bannerData['post_type'] ='PiAppAttachment'; 
$bannerData['guid'] = $movefile['url'];
$bannerData['post_content_filtered'] = $intent;
    wp_update_post($bannerData);
    $response['code'] = 200;
    return new WP_REST_Response($response, 200);

    var_dump( $bannerData );
} else {
    $response['code'] = 500;
    $response['msg'] = $movefile['error'];
    return new WP_REST_Response($response, 500);

}

}

function addProductBox($req){
if ( ! function_exists( 'wp_handle_upload' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
}
if($req['type'] == 'special'){
    privateSpecial($req);
}
else if($req['type']=='normal'){
    privateNormal($req);
}
}

function privateNormal($req){

$addProductBox['post_title'] = $req['title'];
$type = $req['type'];
$position = $req['position'];
$orderby = $req['orderby'];
$addProductBox['comment_count'] = $position ;
$addProductBox['post_name'] = $type ;
$addProductBox['post_excerpt']=$req['isMore'];
if(array_key_exists('publish',$req) && $req['publish'] !='publish' ){
$addProductBox['post_status'] = $req['publish'];
}
$addProductBox['post_parent'] = $req['page'];
$addProductBox['post_type'] ='PiAppHomeProductBox'; 
$addProductBox['post_content_filtered'] = $orderby;
$addProductBox['post_content'] = $req['time'];
$addProductBox['post_mime_type'] = $req['colors'];
$addProductBox['guid'] = $req['CatId'];
    wp_insert_post($addProductBox);
    $response['code'] = 200;
    return new WP_REST_Response($response, 200);

    var_dump( $addProductBox );

}


function privateSpecial($req){
$uploadedfile = $_FILES['title'];
$upload_overrides = array(
    'test_form' => false
);
$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

    
if ( $movefile && ! isset( $movefile['error'] ) ) {
$addProductBox['post_title'] = $movefile['url'];
$type = $req['type'];
$position = $req['position'];
$orderby = $req['orderby'];
$addProductBox['comment_count'] = $position ;
$addProductBox['post_name'] = $type ;
$addProductBox['post_excerpt']=$req['isMore'];
$addProductBox['post_parent'] = $req['page'];
$addProductBox['post_type'] ='PiAppHomeProductBox'; 
$addProductBox['post_content_filtered'] = $orderby;
$addProductBox['post_content'] = $req['time'];
$addProductBox['post_mime_type'] = $req['colors'];
$addProductBox['guid'] = $req['CatId'];
if(array_key_exists('publish',$req) && $req['publish'] !='publish' ){
$addProductBox['post_status'] = $req['publish'];
}
wp_insert_post($addProductBox);
    $response['code'] = 200;
return new WP_REST_Response($response, 200);

    var_dump( $addProductBox );
} else {
    $response['code'] = 500;
    $response['msg'] = $movefile['error'];
    return new WP_REST_Response($response, 500);

}
}



add_action('rest_api_init', function() {

	register_rest_route('wl/v1', '/updateBanner', [
		'methods' => array('GET','POST', 'PUT', 'DELETE'),
	    'callback' =>'updateBanners',
	    ]);  
	register_rest_route('wl/v1', '/addSlideShow', [
		'methods' => array('GET','POST', 'PUT', 'DELETE'),
	    'callback' =>'addSlideShow',
	    ]);
	register_rest_route('wl/v1', '/addProductBox', [
		'methods' => array('GET','POST', 'PUT', 'DELETE'),
	    'callback' =>'addProductBox',
	    ]);
});

?>