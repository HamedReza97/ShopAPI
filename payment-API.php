<?php
include_once WP_PLUGIN_DIR. '/parsian-woocommerce-ipg/parsian-woocommerce-ipg.php';

function wl_payment($request){
    
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
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
    $order = wc_get_order($request['orderId']);
    if ($user_id == $order->customer_id ){ 
    WC()->session->order_awaiting_payment = $order->id;
    $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
    $result = $available_gateways[ $request['gatewayId'] ]->process_payment( $order->id );
    if ( $result['result'] == 'success' ) {
        $result = apply_filters( 'woocommerce_payment_successful_result', $result, $order->id );
        
        $arr['code'] = 200;
        $arr['message'] =  "ok";
        $arr['error'] = false;
        $arr['data'] = $order->id;
        $response = array();
        $response['data'] = $result['redirect'] ;
        $response['responseCode'] = $arr;
        return new WP_REST_Response($response, 200);
        exit;
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
    $arr['code'] = 403;
    $arr['message'] = "forbidden";
    $arr['error'] = true;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 403);      
    }
    $wc_session_data = WC()->session->get('wc_notices');
    $arr['code'] = 404;
    $arr['message'] =  $wc_session_data['error'][0]['notice'] == null ?'Error':$wc_session_data['error'][0]['notice'];
    $arr['error'] = true;
    $response = array();
    $response['responseCode'] = $arr;
    return new WP_REST_Response($response, 200);     
}

add_action('rest_api_init', function() {
	register_rest_route('wl/v1', 'payment', [
		'methods' => array('GET','POST'),
		'callback' => 'wl_payment',
	]);
});