<?php

//Sub Catagories 
function wl_subcatagory($request){
    if ($request['catagory']==[]){
       $catagories = array(); 
    }
    else{
    $parent_id = $request['catagory'];
    $terms = get_terms('product_cat',array('hide_empty' => false));
    $subterms = get_terms('product_cat',array('hide_empty' => false));
    $catagories = array(); 

    $i= 0;
    foreach ($terms as $term)
    {
        if ($term->parent == $parent_id){
            $catagories[$i]['main'] = $term;
            $i++;
        }
    }
    $i=0;
    for ($y = 0; $y < count($catagories); $y++ ){
          $i = 0;
          foreach ($subterms as $subterm)
            {   
                if ($subterm->parent == $catagories[$y]['main']->term_id){
                    $catagories[$y]['sub'][$i] = $subterm;
                    $i++;
                }
            }
    }
    }
    $data = $catagories;
    $arr = array();
        $arr['code'] =$data == [] ? 404:200;
        $arr['message'] = $data == [] ? "Catagory Not Found or has no subcatagories":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $data == [] ? null:$data; 
        return new WP_REST_Response($response, $data == [] ? 404:200);
}

//####################################################################
add_action('rest_api_init', function() {

    register_rest_route('wl/v1', 'pBrand', [
		'methods' => 'GET',
		'callback' => 'wl_pBrand',
	]);
});
function wl_pBrand ($request){
        $data = null;
	    $brand = wp_get_post_terms($request['id'],'pa_برند');  
	    if (count($brand) <= 1 & $brand){
		$bdata = $brand;
		}
		$brand_name = $bdata[0]->name;
		global $wpdb;
        $results = $wpdb->get_results( "SELECT * FROM `h5lzmi4fu_posts` WHERE `post_name` LIKE '%$brand_name-logo%'", OBJECT );
        $data[0]->termId = $bdata[0]->term_id;
        $data[0]->brand = $bdata[0]->name;
        $data[0]->description = $bdata[0]->description == "" ? null:$bdata->description;
		$data[0]->logoImage = $results[0]->guid == "" ? null: $results[0]->guid;
		
	    $arr = array();
        $arr['code'] =$data == [] ? 404:200;
        $arr['message'] = $data == [] ? "Product Not Found":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $data == [] ? null:$data; 
        return new WP_REST_Response($response, $data == [] ? 404:200);
}

//####################################################################
add_action('rest_api_init', function() {

    register_rest_route('wl/v1', 'pModel', [
		'methods' => 'GET',
		'callback' => 'wl_pModel',
	]);
});
function wl_pModel($request){
	    $models = wp_get_post_terms($request['id'],'pa_مدل'); 
	    $i = 0;
	    foreach ($models as $model){
	    $model_data[$i]['id']= $model->term_id;
	    $model_data[$i]['model']= $model->name;
	    $model_data[$i]['description']= $model->description == "" ? null:$model->description;
	    }
	    $data = $model_data;
	    $arr = array();
        $arr['code'] =$data == [] ? 404:200;
        $arr['message'] = $data == [] ? "Product Not Found":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $data == [] ? null:$data; 
        return new WP_REST_Response($response, $data == [] ? 404:200);
}

//####################################################################
add_action('rest_api_init', function() {

    register_rest_route('wl/v1', 'pAttributes', [
		'methods' => 'GET',
		'callback' => 'wl_pAttributes',
	]);
});
function wl_pAttributes($request){
    
        $t_attribs = wp_get_object_terms($request['id'], array_keys(get_taxonomies()));
        
	    $y= 0;
    	foreach ($t_attribs as $attrib){
	    if($attrib->taxonomy != 'product_type' & $attrib->taxonomy != 'product_visibility' & $attrib->taxonomy != 'product_cat' & $attrib->taxonomy != 'product_tag' & $attrib->taxonomy != 'pa_برند' & $attrib->taxonomy != 'pa_مدل'& $attrib->taxonomy != 'yith_product_brand' ){
	   // $attribs[$y]['termId'] = $attrib->term_id;
	    $attribs[$y]['id'] = $y;
	    $str = $attrib->taxonomy;
	    $title = trim($str, "pa_");
	    $attribs[$y]['title'] = $title;
	    $attribs[$y]['value'] = $attrib->name;
	   //$attribs[$y] = $attrib;
	    $y++;
	    }
    	}
    	$args = [
		'numberposts' => 999999,
		'post_type' => 'product',
		'p' => $request['id']
	    ];
	    
	    $posts = get_posts($args);
	    $i = 0;
    	foreach($posts as $post) {
		$attributes = $post->_product_attributes;
		$z = 0;
		$pa_attribute = [];
		foreach ($attributes as $attribute) {
		    if ($attribute['is_taxonomy'] == 0){
		     $pa_attribute[$z]['id'] = $y;
		     $pa_attribute[$z]['title'] = $attribute['name'];
		     $pa_attribute[$z]['value'] = $attribute['value'];
		     $y++;
		     $z++;
		    }
		}
		$i++;
    	}
    	
	    $data = array_merge($attribs,$pa_attribute);
	    $arr = array();
        $arr['code'] =$data == [] ? 404:200;
        $arr['message'] = $data == [] ? "Product Not Found":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $data == [] ? null:$data; 
        return new WP_REST_Response($response, $data == [] ? 404:200);
}

//####################################################################
add_action('rest_api_init', function() {

    register_rest_route('wl/v1', 'pFeatures', [
		'methods' => 'GET',
		'callback' => 'wl_pFeatures',
	]);
});
function wl_pFeatures($request){
        $args = [
		'numberposts' => 999999,
		'post_type' => 'product',
		'p' => $request['id']
	    ];
	    
	    $posts = get_posts($args);
	    $i = 0;
    	foreach($posts as $post) {
		$mainFeatures = $post->main_features; 
		$i++;
    	}
	    
	    $data = $mainFeatures == "" ? null:$mainFeatures;
	    $arr = array();
        $arr['code'] =$data == [] ? 404:200;
        $arr['message'] = $data == [] ? "Product Not Found":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $data == [] ? null:$data; 
        return new WP_REST_Response($response, $data == [] ? 404:200);
}

//####################################################################
add_action('rest_api_init', function() {

    register_rest_route('wl/v1', 'pAttachments', [
		'methods' => 'GET',
		'callback' => 'wl_pAttachments',
	]);
});

function wl_pAttachments($request) {
    
    $attach_id = get_post_meta($request['id'],_product_image_gallery,true);
    $id = explode(',',$attach_id);
    $attach_args = [
    'post_type' => 'attachment',
    'post__in' => $id
    ];
	
	$attachments = get_posts($attach_args);
    $i=0;
    foreach($attachments as $attachment){
        $attach[$i]['image'] = $attachment->guid;
        $i++;
    }
	$data = $attach;
    $arr = array();
        $arr['code'] =$data == [] ? 404:200;
        $arr['message'] = $data == [] ? "Product Not Found":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $data == [] ? null:$data; 
        return new WP_REST_Response($response, $data == [] ? 404:200);
}

//####################################################################

//Get single product
function wl_product($request) {
	$args = [
		'numberposts' => 999999,
		'post_type' => 'product',
		'p' => $request['id']
	];
	
		$attach_args = [
		'numberposts' => 999999,
		'post_type' => 'attachment',
		
	];
	$posts = get_posts($args);
	$attachments = get_posts($attach_args);
	$data = [];
	$i = 0;
    	foreach($posts as $post) {
		$data[$i]['id'] = $post->ID;
		$data[$i]['title'] = $post->post_title;
        $data[$i]['regularPrice'] = $post->_regular_price == "" ? null:$post->_regular_price;
        $data[$i]['salePrice'] = $post->_sale_price == "" ? null:$post->_sale_price; 
		$data[$i]['sku'] = $post->_sku;
		$data[$i]['stock'] = $post->_stock;
		$data[$i]['averageRating'] = $post->_wc_average_rating;
		$data[$i]['weight'] = $post->_weight == "" ? null:$post->_weight;
		$data[$i]['length'] = $post->_length == "" ? null:$post->_length;
		$data[$i]['width'] = $post->_width == "" ? null:$post->_width;
		$data[$i]['height'] = $post->_height == "" ? null:$post->_height;
		$data[$i]['positivePoints'] = $post->positive_point == "" ? null:$post->positive_point;
		$data[$i]['productGaranty'] = $post->product_garanty == "" ? null:$post->product_garanty;
		$data[$i]['postExcerpt'] = $post->post_excerpt == "" ? null:$post->post_excerpt;	
		$data[$i]['postContent'] = $post->post_content == "" ? null:$post->post_content;
        $i++;
	}
	
    $arr = array();
        $arr['code'] =$data == [] ? 404:200;
        $arr['message'] = $data == [] ? "Product Not Found":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $data == [] ? null:$data; 
        return new WP_REST_Response($response, $data == [] ? 404:200);
}

//Get Similar Products
	include_once WP_PLUGIN_DIR . '/woocommerce/woocommerce.php';
	function wl_similarproducts ($request){
	$ids = wc_get_related_products($request['id'],  $limit = -1);
	$i = 0;
	foreach ($ids as $id){
	    
	    $products[$i] = get_post($id);
	    $i++;
	}
	
	$i=0;
	foreach($products as $product) {
		$products_data[$i]['id'] = $product->ID;
		$products_data[$i]['title'] = $product->post_title;
        $products_data[$i]['regularPrice'] = $product->_regular_price == "" ? null:$product->_regular_price;
        $products_data[$i]['salePrice'] = $product->_sale_price == "" ? null:$product->_sale_price; 
        if($post->_sale_price){
        $products_data[$i]['salePrecent'] = (int)round((($product->_regular_price - $product->_sale_price) / $product->_regular_price) *100);
        }
        $products_data[$i]['featuredImage']['thumbnail'] = get_the_post_thumbnail_url($product->ID, 'thumbnail');
		$products_data[$i]['featuredImage']['medium'] = get_the_post_thumbnail_url($product->ID, 'medium');
		$products_data[$i]['featuredImage']['large'] = get_the_post_thumbnail_url($product->ID, 'large');
		$products_data[$i]['stock'] = $product->_stock;
		$products_data[$i]['averageRating'] = $product->_wc_average_rating;
		$products_data[$i]['totalSales'] = $product->total_sales;
		
		
		$i++;
	}
	$pData = array_chunk($products_data, 10);
	if ($request['page'] ==  []){
	$outputs = $pData[0];
	}
	else
	{
	$outputs = $pData[$request['page']-1];
	}
	
	$data=new \stdClass();
    $data->total=count($products_data);
    $data->perPage=10;
    $data->currentPage=(int)$request['page'];
    $data->lastPage=ceil(count($products_data)/$data->perPage);
    $data->from=($data->currentPage-1)*$data->perPage+1;
    $data->to=$data->from+$data->perPage;
    $data->data=$outputs;
	

        $arr = array();
        $arr['code'] =$data == [] ? 404:200;
        $arr['message'] = $data == [] ? "No Similar Product Found":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $data == [] ? null:$data; 
        return new WP_REST_Response($response, $data == [] ? 404:200);
	}
	
//Custom Catagory Filters	
	function wl_catagoryfilter($request) {
	if($request['catagory']){
	$custom_catagorys = get_posts( array(
	'numberposts' => 999999,
    'post_type' => 'product',
    'post_status' => 'publish',
    'tax_query' => array(
        array(
        'taxonomy' => 'product_cat',
        'field'    => 'term_id',
        'terms'     =>  [$request['catagory']],
        'operator'  => 'IN'
            )
        ),
    ));
	}
    else{
    
    $custom_catagorys = get_posts( array(
	'numberposts' => 999999,
    'post_type' => 'product',
    'post_status' => 'publish',
    'tax_query' => array(
        array(
        'taxonomy' => 'pa_برند',
        'field'    => 'name',
        'terms'     =>  [$request['brand']],
        'operator'  => 'IN'
            )
        ),
    ));
    }
    $i=0;
    foreach($custom_catagorys as $custom_catagory) {
		$products[$i]['id'] = $custom_catagory->ID;
		$products[$i]['title'] = $custom_catagory->post_title;
        $products[$i]['regularPrice'] = $custom_catagory->_regular_price == "" ? null:$custom_catagory->_regular_price;
        $products[$i]['salePrice'] = $custom_catagory->_sale_price == "" ? null:$custom_catagory->_sale_price; 
        if($post->_sale_price){
        $products[$i]['salePrecent'] = (int)round((($custom_catagory->_regular_price - $custom_catagory->_sale_price) / $custom_catagory->_regular_price) *100);
        }
        $products[$i]['featuredImage']['thumbnail'] = get_the_post_thumbnail_url($custom_catagory->ID, 'thumbnail');
		$products[$i]['featuredImage']['medium'] = get_the_post_thumbnail_url($custom_catagory->ID, 'medium');
		$products[$i]['featuredImage']['large'] = get_the_post_thumbnail_url($custom_catagory->ID, 'large');
		$products[$i]['stock'] = $custom_catagory->_stock;
		$brand = wp_get_post_terms($custom_catagory->ID,'pa_برند');
		if (count(brand)<=1){
		$products[$i]['brand'] = $brand[0]->name;
		}
		$products[$i]['averageRating'] = $custom_catagory->_wc_average_rating;
		$products[$i]['totalSales'] = $custom_catagory->total_sales;
		
		$i++;
	}
	$i = 0;
	if ($request['minPrice']){
	$min_price = $request['minPrice'];
	}
	else
	{
    $min_price = 0;	        
	}
	if ($request['maxPrice']){
	$max_price = $request['maxPrice'];
	}
	else
	{
    $max_price = 9999999999;	        
	}
	foreach ($products as $product){
	    if($product['salePrice']){
	    if ($product['salePrice'] <= $max_price & $product['salePrice'] >= $min_price){
	    if($request['brand']){
	    if (stripos($request['brand'], $product['brand'])!== false ){
	    $output[$i] = $product;
	    $i++;
	    }
	    }
	    else{
	      $output[$i] =  $product;
	      $i++;
	    }  
	    }
	    }
	    else{
	    if ($product['regularPrice'] <= $max_price & $product['regularPrice'] >= $min_price){
	    if($request['brand']){
	    if (stripos($request['brand'], $product['brand'])!== false ){
	    $output[$i] = $product;
	    $i++;
	    }
	    }
	    else{
	      $output[$i] =  $product;
	      $i++;
	    }  
	    }
	    }
	}
	foreach ($output as $outpu){
	     if($outpu['regularPrice'] != ""){
	      $count[$i] = $outpu;
	      $i++;
	     }   
	    }
	    
	$pData = array_chunk($output, 10);
	if ($request['page'] ==  []){
	$outputs = $pData[0];
	}
	else
	{
	$outputs = $pData[$request['page']-1];
	}
	$i = 0;
	if($request['sortby']){
	    foreach ($outputs as $output){
	     if($output['regularPrice'] != ""){
	      $outdata[$i] = $output;
	      $i++;
	     }   
	    }
	 $outdata = wp_list_sort($outdata, 'regularPrice', $request['sortby'] );  
	}
	else {
	    $outdata = $outputs;
	}
	$data=new \stdClass();
    $data->total=count($count);
    $data->perPage=10;
    $data->currentPage=(int)$request['page'];
    $data->lastPage=ceil(count($count)/$data->perPage);
    $data->from=($data->currentPage-1)*$data->perPage+1;
    $data->to=$data->from+$data->perPage;
    $data->data=$outdata;
    
	    $arr = array();
        $arr['code'] = $outdata == [] ? 404:200;
        $arr['message'] = $outdata == [] ? "No Product Found":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $outdata == [] ? null:$data; 
        return new WP_REST_Response($response, $outdata == [] ? 404:200);
     
}

function wl_search($request){
    
     $search = $request['name'];
        global $wpdb;
          $ids = $wpdb->get_results( "SELECT `ID` FROM `h5lzmi4fu_posts` WHERE `post_title` LIKE '%%$search%%' AND `post_type` LIKE 'product'", OBJECT );

foreach ($ids as $id){
	    
	    $products[$i] = get_post($id->ID);
	    $i++;
	}
	
	$i=0;
	foreach($products as $product) {
		$products_data[$i]['id'] = $product->ID;
		$products_data[$i]['title'] = $product->post_title;
        $products_data[$i]['regularPrice'] = $product->_regular_price == "" ? null:$product->_regular_price;
        $products_data[$i]['salePrice'] = $product->_sale_price == "" ? null:$product->_sale_price; 
        if($post->_sale_price){
        $products_data[$i]['salePrecent'] = (int)round((($product->_regular_price - $product->_sale_price) / $product->_regular_price) *100);
        }
        $products_data[$i]['featuredImage']['thumbnail'] = get_the_post_thumbnail_url($product->ID, 'thumbnail');
		$products_data[$i]['featuredImage']['medium'] = get_the_post_thumbnail_url($product->ID, 'medium');
		$products_data[$i]['featuredImage']['large'] = get_the_post_thumbnail_url($product->ID, 'large');
		$products_data[$i]['stock'] = $product->_stock;
		$products_data[$i]['averageRating'] = $product->_wc_average_rating;
		$products_data[$i]['totalSales'] = $product->total_sales;
		$brand = wp_get_post_terms($product->ID,'pa_برند');
		$catagories = wp_get_post_terms($product->ID,'product_cat');
		$y=0;
		foreach ($catagories as $catagory){
		$products_data[$i]['catagoryName'][$y] = $catagory->name;
		$products_data[$i]['catagoryId'][$y] = $catagory->term_id;
		$y++;
		}
		if (count(brand)<=1){
		$products_data[$i]['brand'] = $brand[0]->name;
		}
		
		$i++;
	}
	$i = 0;
	if ($request['minPrice']){
	$min_price = $request['minPrice'];
	}
	else
	{
    $min_price = 0;	        
	}
	if ($request['maxPrice']){
	$max_price = $request['maxPrice'];
	}
	else
	{
    $max_price = 9999999999;	        
	}
	$i=0;
	foreach ($products_data as $product){
		    if($request['catagory']){
	        if(in_array($request['catagory'],$product['catagoryId'])){
        $product_cat[$i] = $product; 
        $i++;
	    }
	    }
	    else{
	        $product_cat[$i] = $product;
	        $i++;
	    }
	}
	$i=0;
	foreach ($product_cat as $product){
	    if($product['salePrice']){
	    if ($product['salePrice'] <= $max_price & $product['salePrice'] >= $min_price){
	    if($request['brand']){
	    if (stripos($request['brand'], $product['brand'])!== false ){
	    $output[$i] = $product;
	    $i++;
	    }
	    }
	    else{
	      $output[$i] =  $product;
	      $i++;
	    }  
	    }
	    }
	    else{
	    if ($product['regularPrice'] <= $max_price & $product['regularPrice'] >= $min_price){
	    if($request['brand']){
	    if (stripos($request['brand'], $product['brand'])!== false ){
	    $output[$i] = $product;
	    $i++;
	    }
	    }
	    else{
	      $output[$i] =  $product;
	      $i++;
	    }  
	    }
	    }
	}
	foreach ($output as $outpu){
	     if($outpu['regularPrice'] != ""){
	      $count[$i] = $outpu;
	      $i++;
	     }   
	    }
	    
	$pData = array_chunk($output, 10);
	if ($request['page'] ==  []){
	$outputs = $pData[0];
	}
	else
	{
	$outputs = $pData[$request['page']-1];
	}
	$i = 0;
	if($request['sortby']){
	    foreach ($outputs as $output){
	     if($output['regularPrice'] != ""){
	      $outdata[$i] = $output;
	      $i++;
	     }   
	    }
	 $outdata = wp_list_sort($outdata, 'regularPrice', $request['sortby'] );  
	}
	else {
	    $outdata = $outputs;
	}

	$data=new \stdClass();
    $data->total=count($count);
    $data->perPage=10;
    $data->currentPage=(int)$request['page'];
    $data->lastPage=ceil(count($count)/$data->perPage);
    $data->from=($data->currentPage-1)*$data->perPage+1;
    if ($data->total <= $data->perPage){
    $data->to=$data->total;    
    }
    else{
    $data->to=$data->from+$data->perPage;
    }
    $data->data=$outputs;
	

        $arr = array();
        $arr['code'] =$outputs == [] ? 404:200;
        $arr['message'] = $outputs == [] ? "No Product was Found":"ok";
        $arr['error'] = false;
        $response = array();
        $response['responseCode'] = $arr;
        $response['data'] = $outputs == [] ? null:$data; 
        return new WP_REST_Response($response, $outputs == [] ? 404:200);
        }



//Define custom endpoint
add_action('rest_api_init', function() {

    register_rest_route('wl/v1', 'products', [
		'methods' => 'GET',
		'callback' => 'wl_products',
	]);
	
	register_rest_route('wl/v1', 'similarproducts', [
		'methods' => 'GET',
		'callback' => 'wl_similarproducts',
	]);
	
	register_rest_route('wl/v1', 'comments', [
	    'methods'=> "GET",
	    'callback' =>'wl_comments',
	    ]);
	    
	register_rest_route( 'wl/v1', 'comments/(?P<comment_post_ID>[a-zA-Z0-9-]+)', [
		'methods' => 'GET',
		'callback' => 'wl_comment',
    ]);
    
      register_rest_route( 'wl/v1', '/product', [
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'wl_product',
    ]);

	register_rest_route('wl/v1', 'subcatagories', [
	    'methods'  => WP_REST_Server::READABLE,
	    'callback' =>'wl_subcatagory',
	    ]);
	    
	register_rest_route('wl/v1', 'catagoryfilter', [
	    'methods'  => WP_REST_Server::READABLE,
	    'callback' =>'wl_catagoryfilter',
	    ]);    
    	register_rest_route('wl/v1', 'search', [
		'methods' => array('GET'),
		'callback' => 'wl_search',
	]);
});