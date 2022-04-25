<?php

    include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
    include_once WC_ABSPATH . 'includes/wc-notice-functions.php';
    include_once WC_ABSPATH . 'includes/wc-template-hooks.php';

function wl_cart($request){

    $method = $_SERVER['REQUEST_METHOD'];
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $item_data = json_decode($request->get_body());  
    if ( null === WC()->session ) {
    $session_class = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' );
    WC()->session = new $session_class();
    WC()->session->get_session($user_id);
    WC()->session->init();
    WC()->session->set( 'wc_notices', null );
    }

    if ( null === WC()->customer ) {
    WC()->customer = new WC_Customer( $user_id, true );
    }
    if ( null === WC()->cart ) {
    WC()->cart = new WC_Cart();
    }
    global $wpdb;
    
    switch ($method){
        case 'GET':
    if($user_id == 0 or $user_id == null){
    $arr['code'] = 403;
    $arr['message'] = "forbidden";
    $arr['error'] = true;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 200);      
    }
    else{
    // $full_user_meta = get_user_meta($user_id,'_woocommerce_persistent_cart_1',true);
    $product_data = [];
    $i = 0;
    $quantity = 0;
    $regular_price = 0;
    $sale_price = 0;
    $coupon_price = 0;
    $full_user_meta =  WC()->cart->get_cart();
    if($full_user_meta) {
        foreach($full_user_meta as $sinle_user_meta) {
            $product_data[$i]['ID'] = $sinle_user_meta['product_id'];
            $product_data[$i]['name'] = wc_get_product($sinle_user_meta['product_id'])->name;
            $image_id = wc_get_product($sinle_user_meta['product_id'])->image_id;
            $product_data[$i]['image'] = get_post($image_id,"attachment")->guid;
            $product_data[$i]['quantity'] = $sinle_user_meta['quantity'];
            $product_data[$i]['inStock'] = intval(wc_get_product($sinle_user_meta['product_id'])->stock);
            $product_data[$i]['price'] = wc_get_product($sinle_user_meta['product_id'])->sale_price*$sinle_user_meta['quantity'];
            $r_price = wc_get_product($sinle_user_meta['product_id'])->regular_price*$sinle_user_meta['quantity'];
            $product_data[$i]['sale'] = $r_price - (wc_get_product($sinle_user_meta['product_id'])->sale_price*$sinle_user_meta['quantity']);
            $quantity +=  $sinle_user_meta['quantity'];
            $regular_price += $r_price; 
            $sale_price +=  $sinle_user_meta['line_subtotal'];
            $coupon_price += $sinle_user_meta['line_total'];
            $i++;
    }
    $products['items'] = $product_data;
    $products['totalNo'] = $quantity; 
    $products['totalPrice'] = $regular_price;    
    $products['totalSalePrice'] = $sale_price; 
    $coupun_str = implode(WC()->cart->get_applied_coupons());
    $products['coupon'] =$coupun_str == "" ? null: $coupun_str;
    $coupon = new WC_Coupon($coupun_str);
    $products['couponPrice'] = intval($coupon->get_amount());
    $products['totalFinal'] = $coupon_price; 
    $arr['code'] = 200;
    $arr['message'] =  "ok";
    $arr['error'] = false;
    $response = array();
    $response['responseCode'] = $arr;
    $response['data'] = $products; 
    return new WP_REST_Response($response, 200);
    }
    else
    {
    $arr['code'] = 404;
    $arr['message'] =  "Cart is Empty";
    $arr['error'] = true;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 200);  
    }    
    }
    break;
        case 'POST':
    if($user_id == 0 or $user_id == null){
    $arr['code'] = 403;
    $arr['message'] = "forbidden";
    $arr['error'] = true;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 200);      
    }
    else{
    $full_user_meta = WC()->cart->get_cart();
    $i= 0;
    if($item_data){
          if(WC()->cart->add_to_cart( $item_data->product_id, $item_data->quantity )){
          $i = 1;
        }
    }
    $updatedCart = [];
    foreach(WC()->cart->cart_contents as $key => $val) {
        unset($val['data']);
        $updatedCart[$key] = $val;
    }
    $full_user_meta['cart'] = $updatedCart;
    update_user_meta($user_id, '_woocommerce_persistent_cart_1', $full_user_meta);
    $wc_session_data = WC()->session->get('wc_notices');
    if ($i == 0){
    $arr['code'] = 403;
    $arr['message'] =  $wc_session_data['error'][0]['notice'] == null ?'Error':$wc_session_data['error'][0]['notice'];
    $arr['error'] = true;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 200);  
    }
    else
    {
    $arr['code'] = 200;
    $arr['message'] =  "ok";
    $arr['error'] = false;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 200); 
    }
    }
    break;
    
    
    case 'PUT':
    if($user_id == 0 or $user_id == null){
    $arr['code'] = 403;
    $arr['message'] = "forbidden";
    $arr['error'] = true;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 200);      
    }
    else{
    WC()->cart = new WC_Cart();
    $full_user_meta = WC()->cart->get_cart();
    $i = 0; 
    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
     if ( $cart_item['product_id'] == $item_data->product_id) {
        $inStok = wc_get_product($item_data->product_id)->stock;
        if($inStok >= $item_data->quantity){
        $cart_id = WC()->cart->generate_cart_id($item_data->product_id);
        $cart_item_id = WC()->cart->find_product_in_cart($cart_id);
        WC()->cart->set_quantity($cart_item_id ,$item_data->quantity);
        $i =1;
    }
    }
    }
    $updatedCart = [];
    foreach(WC()->cart->cart_contents as $key => $val) {
        unset($val['data']);
        $updatedCart[$key] = $val;
    }
    $full_user_meta['cart'] = $updatedCart;
    update_user_meta($user_id, '_woocommerce_persistent_cart_1', $full_user_meta);    
    $wc_session_data = WC()->session->get('wc_notices');
    if ($i == 0){
    $arr['code'] = 403;
    $arr['message'] =  $wc_session_data['error'][0]['notice'] == null ?'Error':$wc_session_data['error'][0]['notice'];
    $arr['error'] = true;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 200);  
    }
    else
    {
    $arr['code'] = 200;
    $arr['message'] = 'ok';
    $arr['error'] = false;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 200); 
    }
    }
    break;
    
    case 'DELETE':
    if($user_id == 0 or $user_id == null){
    $arr['code'] = 403;
    $arr['message'] = "forbidden";
    $arr['error'] = true;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 200);      
    }
    else{
    $i = WC()->cart->empty_cart();
    $wc_session_data = WC()->session->get('wc_notices');
    if ($wc_session_data['error']){
    $arr['code'] = 403;
    $arr['message'] =  $wc_session_data['error'][0]['notice'] == null ?'Error':$wc_session_data['error'][0]['notice'];
    $arr['error'] = true;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 200);  
    }
    else
    {
    $arr['code'] = 200;
    $arr['message'] =  "ok";
    $arr['error'] = false;
    $response = array();
    $response['responseCode'] = $arr;
    WC()->session->delete_session($user_id);
    WC()->session->forget_session();
    return new WP_REST_Response($response, 200); 
    }
    }    
    break;
    }
}

function wl_coupon($request){
    $method = $_SERVER['REQUEST_METHOD'];
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $item_data = json_decode($request->get_body());  
    
    if($user_id == 0 or $user_id == null){
    $arr['code'] = 403;
    $arr['message'] = "forbidden";
    $arr['error'] = true;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 200);      
    }
    else{
    if ( null === WC()->session ) {
    $session_class = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' );
    WC()->session = new $session_class();
    WC()->session->get_session($user_id);
    WC()->session->init();
    WC()->session->set( 'wc_notices', null );
    }
    if ( null === WC()->customer ) {
    WC()->customer = new WC_Customer( $user_id, true );
    }
    if ( null === WC()->cart ) {
    WC()->cart = new WC_Cart();
    }
    WC()->cart->get_cart();
    $current_coupon = WC()->cart->get_applied_coupons();
    $coupon_code = ($item_data->coupon);
    switch ($method){
        
    case 'POST':
    $i = 0;
    $coupon_notice = "error";
    $coupon = new WC_Coupon($coupon_code);
    if(!$current_coupon){
    if($coupon->is_valid()){
    WC()->cart->apply_coupon( $coupon_code );
    $i = 1;
    }
    else{$coupon_notice = 'Coupon is invalid';}    
    }
    else{$coupon_notice = 'Another coupon has applied';}
    $wc_session_data = WC()->session->get('wc_notices');
    if ($wc_session_data['error'] or $i==0){
    $arr['code'] = 403;
    $arr['message'] =  $wc_session_data['error'] == null ?$coupon_notice:$wc_session_data['error'];
    $arr['error'] = true;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 200);  
    }
    else
    {
    $arr['code'] = 200;
    $arr['message'] =  "ok";
    $arr['error'] = false;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 200); 
    }
    break;
    
    case 'DELETE':
    $i = 0;
    if($current_coupon){
    WC()->cart->remove_coupon( $coupon_code );
    $i = 1;
    }
    $wc_session_data = WC()->session->get('wc_notices');
    if ($wc_session_data['error'] or $i==0){
    $arr['code'] = 403;
    $arr['message'] =  $wc_session_data['error'][0]['notice'] == null ?'Error':$wc_session_data['error'][0]['notice'];
    $arr['error'] = true;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 200);  
    }
    else
    {
    $arr['code'] = 200;
    $arr['message'] =  "ok";
    $arr['error'] = false;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 200); 
    }
    break;
    }
    }
}


add_action('rest_api_init', function() {
	register_rest_route('wl/v1', 'cart', [
	    'methods'=> array('GET','POST', 'PUT', 'DELETE'),
	    'callback' =>'wl_cart'
	    ]);
	 register_rest_route('wl/v1', 'coupon', [
	    'methods'=> array('GET','POST', 'PUT', 'DELETE'),
	    'callback' =>'wl_coupon'
	    ]);
	    
});