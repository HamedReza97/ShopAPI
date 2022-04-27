<?php
    include_once WC_ABSPATH . 'includes/abstracts/abstract-wc-shipping-method.php';
    include_once WC_ABSPATH . 'includes/emails/class-wc-email.php';
    include_once WC_ABSPATH . 'includes/emails/class-wc-email-new-order.php';
function wl_orders($request){
    $method = $_SERVER['REQUEST_METHOD'];
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID == 0 ?null:$current_user->ID;
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
    if ($request['id']){
        $order_data = wc_get_order($request['id']);
        $order_items = $order_data->get_items();
        if ($user_id == $order_data->customer_id ){ 
        $i = 0;
        $product_data = [];
        foreach( $order_items as $item_id => $order_item ) {
        $product_id = $order_item->get_product_id();
        $quantity = $order_item->get_quantity();
        $total += $order_item->get_total();
        $product_data[$i]['ID'] = $product_id;
        $product_data[$i]['name'] = wc_get_product($product_id)->name;
        $image_id = wc_get_product($product_id)->image_id;
        $product_data[$i]['image'] = get_post($image_id,"attachment")->guid;
        $product_data[$i]['quantity'] = $quantity;
        $product_data[$i]['singlePrice'] = intval(wc_get_product($product_id)->regular_price);
        $product_data[$i]['totalPrice'] = $product_data[$i]['singlePrice'] * $quantity;
        $product_data[$i]['salePrice'] = intval(wc_get_product($product_id)->sale_price)* $quantity;
        $product_data[$i]['finalPrice'] = intval($order_item->get_total());
        $i++;
	    }
	    $data = $order_data->get_data();
	    $order_datas['orderId'] = $data['id'];
	    $order_datas['dateCreated'] = $data['date_created'];
	    $order_datas['status'] = $data['status'];
	    $order_datas['dateCompleted'] = $data['date_completed'];
	    $order_datas['datePaid'] = $data['date_paid'];
	    $order_datas['transactionId'] = $data['transaction_id'];
	    $order_datas['paymentMethod'] = $data['payment_method'];
	    $order_datas['paymentMethodTitle'] = $data['payment_method_title'];
	    $order_datas['shipping_total'] = $data['shipping_total'];
	    $order_datas['discountTotal'] = $data['discount_total'];
	    $order_datas['total'] = $data['total'];
	    $order_datas['customerNote'] = $data['customer_note'];
	    $order_datas['Products'] = $product_data;
	    $order_datas['billingAddress'] = $data['billing'];
    $arr = array();
        $arr['code'] = $order_datas == [] ? 404:200;
        $arr['message'] = $order_datas == [] ? "No order found for current user":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $order_datas == [] ? null:$order_datas; 
        return new WP_REST_Response($response, $order_datas == [] ? 404:200);
                 
        }
        else
        {
        $arr['code'] = 403;
        $arr['message'] = "forbidden";
        $arr['error'] = true;
        $response = array();
        $response['responseCode'] = $arr;
        return new WP_REST_Response($response, 200);    
        }
        
    }
        else
        {
    $orders = wc_get_orders(array(
    'customer_id' => $user_id,
    'return' => 'ids'
    ));
    
        $total = 0;
        $y = 0;
        foreach ($orders as $order){
        $order_data = wc_get_order($order);
        $order_items = $order_data->get_items();
        $i = 0;
        $product_data = [];
        foreach( $order_items as $item_id => $order_item ) {
        $product_id = $order_item->get_product_id();
        $quantity = $order_item->get_quantity();
        $total += $order_item->get_total();
        $product_data[$i]['ID'] = $product_id;
        $product_data[$i]['name'] = wc_get_product($product_id)->name;
        $image_id = wc_get_product($product_id)->image_id;
        $product_data[$i]['image'] = get_post($image_id,"attachment")->guid;
        $product_data[$i]['quantity'] = $quantity;
        $product_data[$i]['singlePrice'] = intval(wc_get_product($product_id)->regular_price);
        $product_data[$i]['totalPrice'] = $product_data[$i]['singlePrice'] * $quantity;
        $product_data[$i]['salePrice'] = intval(wc_get_product($product_id)->sale_price)* $quantity;
        $product_data[$i]['finalPrice'] = intval($order_item->get_total());
        $i++;
	    }
	    $data = $order_data->get_data();
	    $order_datas[$y]['orderId'] = $data['id'];
	    $order_datas[$y]['dateCreated'] = $data['date_created'];
	    $order_datas[$y]['status'] = $data['status'];
	    $order_datas[$y]['dateCompleted'] = $data['date_completed'];
	    $order_datas[$y]['datePaid'] = $data['date_paid'];
	    $order_datas[$y]['transactionId'] = $data['transaction_id'];
	    $order_datas[$y]['paymentMethod'] = $data['payment_method'];
	    $order_datas[$y]['paymentMethodTitle'] = $data['payment_method_title'];
	    $order_datas[$y]['shipping_total'] = $data['shipping_total'];
	    $order_datas[$y]['discountTotal'] = $data['discount_total'];
	    $order_datas[$y]['total'] = $data['total'];
	    $order_datas[$y]['customerNote'] = $data['customer_note'];
	    $order_datas[$y]['Products'] = $product_data;
	    $order_datas[$y]['billingAddress'] = $data['billing'];
	    $y++;
        }    
    $arr = array();
        $arr['code'] = $order_datas == [] ? 404:200;
        $arr['message'] = $order_datas == [] ? "No order found for current user":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $order_datas == [] ? null:$order_datas; 
        return new WP_REST_Response($response, $order_datas == [] ? 404:200);
        }  
}
}

function wl_createOrder($request){
    $method = $_SERVER['REQUEST_METHOD'];
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID == 0 ?null:$current_user->ID;
    $item_data = json_decode($request->get_body());  
    
    if($user_id == 0 or $user_id == null){
    $arr['code'] = 403;
    $arr['message'] = "forbidden";
    $arr['error'] = true;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 403);      
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
    $items = WC()->cart->get_cart();
    if($items){
    if ($user_id != null){
    }
    $note = $item_data->note;
    $args = array(
        'customer_id'   => $user_id,
        'status'        => 'wc-pending',
        'customer_note' => $note,
    );
    $coupon = implode(WC()->cart->get_applied_coupons());
    $order = wc_create_order($args);
    foreach($items as $item => $values) {
    $product_id = $values['product_id'];
    $product = wc_get_product($product_id);
    $quantity = (int)$values['quantity'];
    $done = $order->add_product($product, $quantity);
    }
    // if(!WC()->customer->get_shipping()){
    if($item_data->address){
    $address['first_name'] = $item_data->address->firstName;
    $address['last_name'] = $item_data->address->lastName;
    $address['company'] = $item_data->address->company;
    $address['address_1'] = $item_data->address->address1;
    $address['address_2'] = $item_data->address->address2;
    $address['state'] = $item_data->address->state == null ? "THR":$item_data->address->state;
    $address['country'] = $item_data->address->country== null ? "IR":$item_data->address->country;
    $address['city'] = $item_data->address->city;
    $address['postcode'] = $item_data->address->postcode;
    $address['phone'] = $item_data->address->phone;
    $address['mobile'] = $item_data->address->mobile;
    $address['email'] = $item_data->address->email;  
    }
    else{
    $address['first_name'] = get_user_meta( $current_user->ID, 'billing_first_name', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_first_name', true );
    $address['last_name'] = get_user_meta( $current_user->ID, 'billing_last_name', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_last_name', true );
    $address['company'] = get_user_meta( $current_user->ID, 'billing_company', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_company', true );
    $address['address_1'] = get_user_meta( $current_user->ID, 'billing_address_1', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_address_1', true ); 
    $address['address_2'] = get_user_meta( $current_user->ID, 'billing_address_2', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_address_2', true );
    $state = get_user_meta( $current_user->ID, 'billing_state', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_state', true );
    $country = get_user_meta( $current_user->ID, 'billing_country', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_country', true );
    $address['state'] = $state;
    $address['country'] = $country;
    $address['city'] = get_user_meta( $current_user->ID, 'billing_city', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_city', true );
    $address['postcode'] = get_user_meta( $current_user->ID, 'billing_postcode', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_postcode', true );
    $address['phone'] = get_user_meta( $current_user->ID, 'billing_phone', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_phone', true ); 
    $address['mobile'] = get_user_meta( $current_user->ID, 'billing_mobile', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_mobile', true );
    $address['email'] = get_user_meta( $current_user->ID, 'billing_email', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_email', true );  
    if (!isset($address['first_name']) && !isset($address['phone'])){
    $address = null;    
    }
    }
    $order->set_address( $address, 'billing' );
    $order->set_address( $address, 'shipping' );
    // }
    // else
    // {
    // $order->set_address( WC()->customer->get_billing(), 'billing' );
    // $order->set_address( WC()->customer->get_shipping(), 'shipping' );    
    // }
    $cart_hash = md5(json_encode(wc_clean(WC()->cart->get_cart_for_session())) . WC()->cart->total);
    $order->set_created_via('checkout');
    $order->set_cart_hash($cart_hash);
    $order->apply_coupon($coupon);
    WC()->shipping->load_shipping_methods();
    if ($item_data->shipping)
    {
    $ship_rate_ob = new WC_Shipping_Rate();
    $ship_rate_ob->id= $item_data->shipping->id;
    $ship_rate_ob->label=   $item_data->shipping->label;
    $ship_rate_ob->taxes=array();
    $ship_rate_ob->cost = intval($item_data->shipping->cost);
    $order->add_shipping($ship_rate_ob);
    }
    else{
    $shipping_methods = WC()->shipping->get_shipping_methods();
    $selected_shipping_method = $shipping_methods['advanced_shipping'];
    $packages = WC()->cart->get_shipping_packages();
    $shipping = WC()->shipping->calculate_shipping($packages);
    foreach(WC()->shipping->packages as $package){
    foreach($package['rates'] as $rate){
       $shipping_cost = $rate->get_cost(); 
    }    
    }
    $ship_rate_ob = new WC_Shipping_Rate();
    $ship_rate_ob->id= $selected_shipping_method->id;
    $ship_rate_ob->label='پست پیشتاز';
    $ship_rate_ob->taxes=array();
    $ship_rate_ob->cost=$shipping_cost; 
    $order->add_shipping($ship_rate_ob);
    }
    $order->set_payment_method( WC()->payment_gateways->payment_gateways()[$item_data->paymentMethod] );
    $order->calculate_totals();
    $order->update_status( 'wc-pending' );
    $order->save();
    $order_id = $order->save();
    WC()->cart->empty_cart();
  }
  if ($done == true){
     if ($user_id == $order->customer_id ){ 
    WC()->session->order_awaiting_payment = $order->id;
    $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
    $result = $available_gateways[ $item_data->paymentMethod]->process_payment( $order->id );
    if ( $result['result'] == 'success' ) {
        $result = apply_filters( 'woocommerce_payment_successful_result', $result, $order->id );
          $arr['code'] = 200;
    $arr['message'] =  "ok";
    $arr['error'] = false;
    $arr['data'] = $order_id;
    $response = array();
    $response['data'] = $result['redirect'] ;
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 200);
       
        exit;
    }
          }
  $arr['code'] = 200;
    $arr['message'] =  "ok";
    $arr['error'] = false;
    $arr['data'] = $order_id;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 200);
  }
  else
  {
    $wc_session_data = WC()->session->get('wc_notices');
    $arr['code'] = 404;
    $arr['message'] =  $wc_session_data['error'][0]['notice'] == null ?'Error':$wc_session_data['error'][0]['notice'];
    $arr['error'] = true;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 200); 
  }
}
}


function wl_cancellOrder ($request){
    $method = $_SERVER['REQUEST_METHOD'];
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID == 0 ?null:$current_user->ID;
    $item_data = json_decode($request->get_body());  
    
    if($user_id == 0 or $user_id == null){
    $arr['code'] = 403;
    $arr['message'] = "forbidden";
    $arr['error'] = true;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 403);      
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
    if($item_data->orderId){
    $order = wc_get_order($item_data->orderId);
    $order->update_status( 'wc-cancelled' );
    $order->save();
    $arr['code'] = 200;
    $arr['message'] =  "ok";
    $arr['error'] = false;
    $arr['data'] = $order->id;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 200);
    }
  else
  {
    $wc_session_data = WC()->session->get('wc_notices');
    $arr['code'] = 403;
    $arr['message'] =  $wc_session_data['error'][0]['notice'] == null ?'Error':$wc_session_data['error'][0]['notice'];
    $arr['error'] = true;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 200); 
  }
    }
}

function wl_editOrder ($request){
    $method = $_SERVER['REQUEST_METHOD'];
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID == 0 ?null:$current_user->ID;
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
    if($item_data->orderId){
    $order = wc_get_order($item_data->orderId);
    if($item_data->address){
    $address['first_name'] = $item_data->address->firstName;
    $address['last_name'] = $item_data->address->lastName;
    $address['company'] = $item_data->address->company;
    $address['address_1'] = $item_data->address->address1;
    $address['address_2'] = $item_data->address->address2;
    $address['state'] = $item_data->address->state == null ? "THR":$item_data->address->state;
    $address['country'] = $item_data->address->country== null ? "IR":$item_data->address->country;
    $address['city'] = $item_data->address->city;
    $address['postcode'] = $item_data->address->postcode;
    $address['phone'] = $item_data->address->phone;
    $address['mobile'] = $item_data->address->mobile;
    $address['email'] = $item_data->address->email; 
    $order->set_address( $address, 'billing' );
    $order->set_address( $address, 'shipping' );
    if($item_data->paymentMethod){
    $order->set_payment_method( WC()->payment_gateways->payment_gateways()[$item_data->paymentMethod] );
    }
    $order->save();
    }
    $arr['code'] = 200;
    $arr['message'] =  "ok";
    $arr['error'] = false;
    $arr['data'] = $order->id;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 200);
    }
    else
    {
    $wc_session_data = WC()->session->get('wc_notices');
    $arr['code'] = 403;
    $arr['message'] =  $wc_session_data['error'][0]['notice'] == null ?'Error':$wc_session_data['error'][0]['notice'];
    $arr['error'] = true;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 200); 
  }
    }
}

function wl_getShipping ($request){
    $method = $_SERVER['REQUEST_METHOD'];
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID == 0 ?null:$current_user->ID;
    $item_data = json_decode($request->get_body());  
    if($user_id == 0 or $user_id == null){
    return $user_id;
    $arr['code'] = 403;
    $arr['message'] = "forbidden";
    $arr['error'] = true;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 403);      
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
    
    if($item_data->address){
    $address['first_name'] = $item_data->address->firstName;
    $address['last_name'] = $item_data->address->lastName;
    $address['company'] = $item_data->address->company;
    $address['address_1'] = $item_data->address->address1;
    $address['address_2'] = $item_data->address->address2;
    $address['state'] = $item_data->address->state == null ? "THR":$item_data->address->state;
    $address['country'] = $item_data->address->country== null ? "IR":$item_data->address->country;
    $address['city'] = $item_data->address->city;
    $address['postcode'] = $item_data->address->postcode;
    $address['phone'] = $item_data->address->phone;
    $address['mobile'] = $item_data->address->mobile;
    $address['email'] = $item_data->address->email; 
    }
    else
    {
    $address['first_name'] = get_user_meta( $current_user->ID, 'billing_first_name', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_first_name', true );
    $address['last_name'] = get_user_meta( $current_user->ID, 'billing_last_name', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_last_name', true );
    $address['company'] = get_user_meta( $current_user->ID, 'billing_company', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_company', true );
    $address['address_1'] = get_user_meta( $current_user->ID, 'billing_address_1', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_address_1', true ); 
    $address['address_2'] = get_user_meta( $current_user->ID, 'billing_address_2', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_address_2', true );
    $state = get_user_meta( $current_user->ID, 'billing_state', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_state', true );
    $country = get_user_meta( $current_user->ID, 'billing_country', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_country', true );
    $address['state'] = $state;
    $address['country'] = $country;
    $address['city'] = get_user_meta( $current_user->ID, 'billing_city', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_city', true );
    $address['postcode'] = get_user_meta( $current_user->ID, 'billing_postcode', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_postcode', true );
    $address['phone'] = get_user_meta( $current_user->ID, 'billing_phone', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_phone', true ); 
    $address['mobile'] = get_user_meta( $current_user->ID, 'billing_mobile', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_mobile', true );
    $address['email'] = get_user_meta( $current_user->ID, 'billing_email', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_email', true );  
    }

    WC()->customer->set_shipping_state($address['state']);
    WC()->customer->set_shipping_city($address['city']);
    $bh_packages =  WC()->cart->get_shipping_packages();
    $bh_packages[0]['destination']['state'] = $address['state'];
    $bh_packages[0]['destination']['postcode'] = $address['postcode'];
    $bh_packages[0]['destination']['city'] = $address['city'];
    $bh_packages[0]['destination']['address'] = $address['address_1'];
    $bh_packages[0]['destination']['address_2'] = $address['address_2'];
    $bh_packages[0]['destination']['mobile'] = $address['mobile'];
    $bh_packages[0]['destination']['email'] = $address['email'];
    $bh_packages[0]['destination']['phone'] = $address['phone'];
    $bh_shipping_methods = array();
    foreach( $bh_packages as $bh_package_key => $bh_package ) {
        $bh_shipping_methods[$bh_package_key] = WC()->shipping->calculate_shipping_for_package($bh_package, $bh_package_key);
    }
    
    // return WC()->cart->get_shipping_packages();
     $shippingArr = $bh_shipping_methods[0]['rates'];
    if(!empty($shippingArr)) {
        $responses = array();
        foreach ($shippingArr as $value) {
            if ($value->cost == 0){
            if($address['state'] == "THR" && $address['city'] == "تهران"){
            $shipping['id'] = $value->id;
            $shipping['methodId'] = $value->method_id;
            $shipping['label'] = $value->label;
            $shipping['cost'] = $value->cost;
            $responses['shipping'][] = $shipping;
            }
            }
            else
            {
            $shipping['id'] = $value->id;
            $shipping['methodId'] = $value->method_id;
            $shipping['label'] = $value->label;
            $shipping['cost'] = $value->cost;
            $responses['shipping'][] = $shipping;    
            }
    }
    
    $arr['code'] = 200;
    $arr['message'] =  "ok";
    $arr['error'] = false;
    $arr['data'] = $responses;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 200);
    }
  else
  {
    $wc_session_data = WC()->session->get('wc_notices');
    $arr['code'] = 403;
    $arr['message'] =  $wc_session_data['error'][0]['notice'] == null ?'Error':$wc_session_data['error'][0]['notice'];
    $arr['error'] = true;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 200); 
  }
    }
}

function wl_paymentMethod($request){
   $method = $_SERVER['REQUEST_METHOD'];
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID == 0 ?null:$current_user->ID;
    // $payment_data = json_decode($request->get_body());  
    
    if($user_id == 0 or $user_id == null){
    $arr['code'] = 403;
    $arr['message'] = "forbidden";
    $arr['error'] = true;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 403);      
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
    // $order = wc_get_order($request['orderId']);
    if ($user_id != null){ 
    // WC()->session->order_awaiting_payment = $order->id;
    $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
    $i=0;
    foreach ($available_gateways as $getway){
    $available_gateway[$i]['id'] = $getway->id;
    $available_gateway[$i]['title'] = $getway->title;
    $available_gateway[$i]['icon'] = $getway->icon;
    $i++;
    }
    $arr['code'] = 200;
    $arr['message'] =  "ok";
    $arr['error'] = false;
    $arr['data'] = $available_gateway;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 200);
    }
    else
    {
    $arr['code'] = 403;
    $arr['message'] = "forbidden";
    $arr['error'] = true;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 403);      
    }
    $wc_session_data = WC()->session->get('wc_notices');
    $arr['code'] = 403;
    $arr['message'] =  $wc_session_data['error'][0]['notice'] == null ?'Error':$wc_session_data['error'][0]['notice'];
    $arr['error'] = true;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 200); 
  }  
}

add_action('rest_api_init', function() {
	register_rest_route('wl/v1', 'orders', [
		'methods' => 'GET',
		'callback' => 'wl_orders',
	]);
    register_rest_route('wl/v1', 'createorder', [
		'methods' => array('GET','POST'),
		'callback' => 'wl_createOrder',
	]);
	register_rest_route('wl/v1', 'cancellorder', [
		'methods' => array('GET','DELETE'),
		'callback' => 'wl_cancellOrder',
	]);
	register_rest_route('wl/v1', 'editorder', [
		'methods' => array('GET','PUT'),
		'callback' => 'wl_editOrder',
	]);
	register_rest_route('wl/v1', 'getShipping', [
		'methods' => array('GET','PUT'),
		'callback' => 'wl_getShipping',
	]);
	register_rest_route('wl/v1', 'paymentMethod', [
		'methods' => array('GET','PUT'),
		'callback' => 'wl_paymentMethod',
	]);
});