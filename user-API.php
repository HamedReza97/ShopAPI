<?php

function wl_changepass($request){
    $method = $_SERVER['REQUEST_METHOD'];
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $user_info = json_decode($request->get_body());
    
    if ($user_info->oldPassword){
            // return wp_check_password($user_info->oldPassword, $current_user->user_pass, $current_user->ID );
        if ( wp_check_password($user_info->oldPassword, $current_user->user_pass, $current_user->ID ) ) {
                 if($user_info->newPassword != "" & $user_info->newPassword == $user_info->confirmPassword ){
                  wp_set_password($user_info->newPassword,$user_id);
                  $arr['code'] = 200;
                    $arr['message'] =  "Password Changed";
                    $arr['error'] = false;
                    $response = array();
                    $response['responseCode'] = $arr;
                    return new WP_REST_Response($response, 200); 
                 }
                 else
                 {
                    $arr['code'] = 404;
                    $arr['message'] =  "New Password Does'nt match or Empty";
                    $arr['error'] = true;
                    $response = array();
                    $response['responseCode'] = $arr;
                    return new WP_REST_Response($response, 404);  
                 }
        } else {
        $arr['code'] = 404;
        $arr['message'] =  "Password is Incorrect";
        $arr['error'] = true;
        $response = array();
        $response['responseCode'] = $arr;
        return new WP_REST_Response($response, 404);
            }

        }
}

//########################## کاربر فعلی########################
function wl_currentuser($request){
    $method = $_SERVER['REQUEST_METHOD'];
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    switch ($method){
        case 'GET':
    if ($user_id != null){
    $data["user_name"] = $current_user->user_login == "" ? null:$current_user->user_login;
    $data["email"] = $current_user->user_email == "" ? null:$current_user->user_email;
    $data["nickName"]   = get_usermeta($user_id,'nickname') == "" ? null:get_usermeta($user_id,'nickname');
    $data["firstName"] = get_usermeta($user_id,'first_name') == "" ? null:get_usermeta($user_id,'first_name') ;
    $data["lastName"]  = get_usermeta($user_id,'last_name') == "" ? null:get_usermeta($user_id,'last_name');
    
    if (get_usermeta($user_id,'billing_phone') != ""){
        $data["mobile"] = get_usermeta($user_id,'billing_phone') == "" ? null:get_usermeta($user_id,'billing_phone');
      
    }
    else if(get_usermeta($user_id,'digits_phone')!= ""){
    $data["mobile"] = get_usermeta($user_id,'digits_phone')== "" ? null:get_usermeta($user_id,'digits_phone');    
    }
    else{
        $data["mobile"] = '0' + get_usermeta($user_id,'digits_phone_no')== "" ? null:get_usermeta($user_id,'digits_phone_no'); 
    }
    
    }
    break;
    
    case 'PUT':
        
        $user_info = json_decode($request->get_body());
        if ($user_info->firstName){
            $user['firstName'] = $user_info->firstName;
        $user_name = update_user_meta($user_id,'first_name',$user_info->firstName);
        if ( is_wp_error( $user_name ) ) {
              $arr['code'] = 404;
                    $arr['message'] =  $user_name->errors;
                    $arr['error'] = true;
                    $response = array();
                    $response['responseCode'] = $arr;
                    return new WP_REST_Response($response, 404);  
        }
        }
        if($user_id == 0)
        {
        $data = [];    
        }
        else{
        if ($user_info->lastName){
            $user['lastName'] = $user_info->lastName;
        update_user_meta($user_id,'last_name',$user_info->lastName);
        }
        if ($user_info->phone){
            if(get_usermeta($user_id,'billing_phone')){
                update_user_meta($user_id,'billing_phone',$user_info->phone);
                $user['phone'] = $user_info->phone;
            }
            else{
                $user['phone'] = $user_info->phone;
        update_user_meta($user_id,'digits_phone',"+98"+$user_info->phone);
        }
        }
        if ($user_info->email){
            $user['email'] = $user_info->email;
        $user_email = wp_update_user( array( 'ID' => $user_id, 'user_email' => $user_info->email ) );
         if ( is_wp_error( $user_email ) ) {
              $arr['code'] = 404;
                    $arr['message'] =  $user_email->errors;
                    $arr['error'] = true;
                    $response = array();
                    $response['responseCode'] = $arr;
                    return new WP_REST_Response($response, 404);  
        }
        }
        $data = $user;
        }
    break;
    }
     $arr = array();
        $arr['code'] =$data == [] ? 404:200;
        $arr['message'] = $data == [] ? "User not loged in or not found":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $data ==[] ? null:$data; 
        return new WP_REST_Response($response, $data == [] ? 404:200);
}

function wl_address($request){
    $method = $_SERVER['REQUEST_METHOD'];
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $user_id = $current_user->ID == 0 ?null:$current_user->ID;
    if ($user_id != null){
    $user_info = get_user_meta($current_user->ID);    
    $address['firstName'] = get_user_meta( $current_user->ID, 'billing_first_name', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_first_name', true );
    $address['lastName'] = get_user_meta( $current_user->ID, 'billing_last_name', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_last_name', true );
    $address['company'] = get_user_meta( $current_user->ID, 'billing_company', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_company', true );
    $address['address1'] = get_user_meta( $current_user->ID, 'billing_address_1', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_address_1', true ); 
    $address['address2'] = get_user_meta( $current_user->ID, 'billing_address_2', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_address_2', true );
    $state = get_user_meta( $current_user->ID, 'billing_state', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_state', true );
    $country = get_user_meta( $current_user->ID, 'billing_country', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_country', true );
    $address['state'] = $state;
    $address['formattedState'] = WC()->countries->states[$country][$state];
    $address['city'] = get_user_meta( $current_user->ID, 'billing_city', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_city', true );
    $address['postcode'] = get_user_meta( $current_user->ID, 'billing_postcode', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_postcode', true );
    $address['phone'] = get_user_meta( $current_user->ID, 'billing_phone', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_phone', true ); 
    $address['mobile'] = get_user_meta( $current_user->ID, 'billing_mobile', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_mobile', true );
    $address['email'] = get_user_meta( $current_user->ID, 'billing_email', true ) == "" ? null:get_user_meta( $current_user->ID, 'billing_email', true );  
    if (!isset($address['firstName']) && !isset($address['phone'])){
    $address = null;    
    }
    }
    switch ($method){
        case 'GET':
    if ($user_id != null){
    $data = $address; 
    }
    else{
        $arr['code'] = 403;
        $arr['message'] =  "Forbidden";
        $arr['error'] = true;
        $response = array();
        $response['responseCode'] = $arr;
        return new WP_REST_Response($response, 403);      
    }
    
    $arr = array();
        $arr['code'] =$data == [] ? 404:200;
        $arr['message'] = $data == [] ? "No address was found":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $data ==[] ? null:$data; 
        return new WP_REST_Response($response, $data == [] ? 404:200);
    break;
    case 'POST':
    if ($user_id != null){
    if ($request->get_body()){
    $newAddress = json_decode($request->get_body());    
    if ($address == []){
    add_user_meta($user_id,'billing_first_name', $newAddress->firstName);
    add_user_meta($user_id,'billing_last_name', $newAddress->lastName);
    add_user_meta($user_id,'billing_company', $newAddress->company);
    add_user_meta($user_id,'billing_address_1', $newAddress->address1);
    add_user_meta($user_id,'billing_address_2', $newAddress->address2);
    add_user_meta($user_id,'billing_city', $newAddress->city);
    add_user_meta($user_id,'billing_postcode', $newAddress->postcode);
    add_user_meta($user_id,'billing_country', 'IR');
    add_user_meta($user_id,'billing_state', $newAddress->state);
    add_user_meta($user_id,'billing_phone', $newAddress->phone);
    add_user_meta($user_id,'billing_mobile', $newAddress->mobile);
    add_user_meta($user_id,'billing_email', $newAddress->email);    
    $data = $newAddress;
    }
    
     $arr = array();
        $arr['code'] =$data == [] ? 404:200;
        $arr['message'] = $data == [] ? "Can't add other address":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $data ==[] ? null:$data; 
        return new WP_REST_Response($response, $data == [] ? 404:200);
    }
    }
    else{
        $arr['code'] = 403;
        $arr['message'] =  "Forbidden";
        $arr['error'] = true;
        $response = array();
        $response['responseCode'] = $arr;
        return new WP_REST_Response($response, 403);      
    }    
    break;
    case 'PUT':
    if ($user_id != null){
    if ($request->get_body()){
    $newAddress = json_decode($request->get_body());
    if ($address != []){
    update_user_meta($user_id,'billing_first_name', $newAddress->firstName);
    update_user_meta($user_id,'billing_last_name', $newAddress->lastName);
    update_user_meta($user_id,'billing_company', $newAddress->company);
    update_user_meta($user_id,'billing_address_1', $newAddress->address1);
    update_user_meta($user_id,'billing_address_2', $newAddress->address2);
    update_user_meta($user_id,'billing_city', $newAddress->city);
    update_user_meta($user_id,'billing_postcode', $newAddress->postcode);
    update_user_meta($user_id,'billing_country', 'IR');
    update_user_meta($user_id,'billing_state', $newAddress->state);
    update_user_meta($user_id,'billing_phone', $newAddress->phone);
    update_user_meta($user_id,'billing_mobile', $newAddress->mobile);
    update_user_meta($user_id,'billing_email', $newAddress->email);
    $data = $newAddress;
    }
    else{
    $arr['code'] = 404;
        $arr['message'] =  "Address was not found";
        $arr['error'] = true;
        $response = array();
        $response['responseCode'] = $arr;
        return new WP_REST_Response($response, 404);
    }
         $arr = array();
        $arr['code'] =$data == [] ? 404:200;
        $arr['message'] = $data == [] ? "No address was found":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $data ==[] ? null:$data; 
        return new WP_REST_Response($response, $data == [] ? 404:200);
    }
    }
    else
    {
    $arr['code'] = 403;
        $arr['message'] =  "Forbidden";
        $arr['error'] = true;
        $response = array();
        $response['responseCode'] = $arr;
        return new WP_REST_Response($response, 403);     
    }
    break;
    case 'DELETE':
        
    if ($user_id != null){
    if ($address != []){
    delete_user_meta($user_id,'billing_first_name');
    delete_user_meta($user_id,'billing_last_name');
    delete_user_meta($user_id,'billing_company');
    delete_user_meta($user_id,'billing_address_1');
    delete_user_meta($user_id,'billing_address_2');
    delete_user_meta($user_id,'billing_city');
    delete_user_meta($user_id,'billing_postcode');
    delete_user_meta($user_id,'billing_country');
    delete_user_meta($user_id,'billing_state');
    delete_user_meta($user_id,'billing_phone');
    delete_user_meta($user_id,'billing_mobile');
    delete_user_meta($user_id,'billing_email');     
    $arr['code'] = 200;
        $arr['message'] =  "Address was deleted";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        return new WP_REST_Response($response, 200);
    }
    else{
    $arr['code'] = 404;
        $arr['message'] =  "Address was not found";
        $arr['error'] = true;
        $response = array();
        $response['responseCode'] = $arr;
        return new WP_REST_Response($response, 404);
    }
    }
    else
    {
    $arr['code'] = 403;
        $arr['message'] =  "Forbidden";
        $arr['error'] = true;
        $response = array();
        $response['responseCode'] = $arr;
        return new WP_REST_Response($response, 403);     
    }    
    break;
    }
    }
//Define custom endpoint
add_action('rest_api_init', function() {
	register_rest_route('wl/v1', 'currentuser', [
	    'methods'=> array('GET','POST', 'PUT', 'DELETE'),
	    'callback' =>'wl_currentuser',
	    ]);   
	    
	register_rest_route('wl/v1', 'changepass', [
	    'methods'=> array('GET','POST', 'PUT', 'DELETE'),
	    'callback' =>'wl_changepass',
	    ]);  
	register_rest_route('wl/v1', 'address', [
	    'methods'=> array('GET','POST', 'PUT', 'DELETE'),
	    'callback' =>'wl_address',
	    ]);
});