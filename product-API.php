<?php

//Sub Catagories 
function wl_subcatagory($request){
    if ($request['catagory']==[]){
       $catagories = []; 
    }
    else{
    $parent_id = $request['catagory'];
    $terms = get_terms('product_cat',array('hide_empty' => false));
    $subterms = get_terms('product_cat',array('hide_empty' => false));

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
    foreach ($subterms as $subterm)
    {   
        if ($subterm->parent == $catagories[$y]['main']->term_id){
            $catagories[$y]['sub'][$i] = $subterm;
            $i++;
        }
    }
    }
    }
    
    if($catagories == []){
	return new WP_REST_Response("Catagory Not Found or has no subcatagories!", 404);
    }
    else
    {
    return new WP_REST_Response($catagories, 200);
    }

}

//Get single product
function wl_product($request) {
	$args = [
		'numberposts' => 999999,
		'post_type' => 'product'
	];
	
		$attach_args = [
		'numberposts' => 999999,
		'post_type' => 'attachment',
	];
	$t_attribs = wp_get_object_terms($request['id'], array_keys(get_taxonomies()));
	$y= 0;
	foreach ($t_attribs as $attrib){
	    if($attrib->taxonomy != 'product_type' & $attrib->taxonomy != 'product_cat' & $attrib->taxonomy != 'product_tag' & $attrib->taxonomy != 'pa_برند' & $attrib->taxonomy != 'pa_مدل'& $attrib->taxonomy != 'yith_product_brand' ){
	    $attribs[$y] = $attrib;
	    $y++;
	    }
	}
	
	$brand = wp_get_post_terms($request['id'],'pa_برند');
	$model = wp_get_post_terms($request['id'],'pa_مدل');
	$posts = get_posts($args);
	$attachments = get_posts($attach_args);
	$data = [];
	$i = 0;
    	foreach($posts as $post) {
	    
	    if ($post->ID==$request['id']){
	    try {
		$data[$i]['id'] = $post->ID;
		$data[$i]['title'] = $post->post_title;
        $data[$i]['regularPrice'] = $post->_regular_price;
        $data[$i]['salePrice'] = $post->_sale_price; 
		if (count($brand) <= 1 & $brand){
		$data[$i]['brand'] = $brand;
		}
		$data[$i]['model'] = $model;
		$data[$i]['sku'] = $post->_sku;
		$data[$i]['mainFeatures'] = $post->main_features;
		$attributes = $post->_product_attributes;
		$z = 0;
		foreach ($attributes as $attribute) {
		    if ($attribute['is_taxonomy'] == 0){
		     $pa_attribute[$z] = $attribute;
		     $z++;
		    }
		}
		$data[$i]['productAttributes'] = $pa_attribute;
		$data[$i]['otherAttributes'] = $attribs;
		$data[$i]['stock'] = $post->_stock;
		$data[$i]['averageRating'] = $post->_wc_average_rating;
		$data[$i]['weight'] = $post->_weight;
		$data[$i]['length'] = $post->_length;
		$data[$i]['width'] = $post->_width;
		$data[$i]['height'] = $post->_height;
		$data[$i]['positivePoints'] = $post->positive_points;
		$data[$i]['productGaranty'] = $post->product_garanty;
		$data[$i]['postExcerpt'] = $post->post_excerpt;	
		$data[$i]['postContent'] = $post->post_content;
		$data[$i]['featuredImage']['thumbnail'] = get_the_post_thumbnail_url($post->ID, 'thumbnail');
		$data[$i]['featuredImage']['medium'] = get_the_post_thumbnail_url($post->ID, 'medium');
		$data[$i]['featuredImage']['large'] = get_the_post_thumbnail_url($post->ID, 'large');
		
        
        //atachment
        foreach ($attachments as $attachment){
           
            if ($attachment->post_parent==$request['id']){
            $data[$i]['attachmentLink'] = $attachment->guid;
        }
        }
        }
        catch (Exception $e){
           echo $e; 
        }
        $i++;
	    }
		
	}
    
    if($data == []){
	return new WP_REST_Response("Product Not Found!", 404);
    }
    else
    {
    return new WP_REST_Response($data, 200);
    }
}

//reviews
	function wl_review($request){	
	    
	    $comment_args = [
		'numbercomments' => 999999,
	    ];
	    $comments = get_comments($comment_args);
		$y = 0;
		foreach($comments as $comment){
		    if ($comment->comment_post_ID==$request['id']){
		$data[$y]['commentPostId'] = $comment->comment_post_ID;
		$data[$y]['commentAuthor'] = $comment->comment_author;
        $data[$y]['commentDate'] = $comment->comment_date;
        $data[$y]['commentContent'] = $comment->comment_content;
        $data[$y]['commentType'] = $comment->comment_type;
        $y ++;
		}
        }
    if($data == []){
	return new WP_REST_Response("Product Not Found!", 404);
    }
    else
    {
    return new WP_REST_Response($data, 200);
    }
	}
	include '.../.../plugins/woocommerce/includes/wc-product-functions.php';
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
        $products_data[$i]['regularPrice'] = $product->_regular_price;
        $products_data[$i]['salePrice'] = $product->_sale_price; 
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
	$data=new \stdClass();
    $data->total=count($products_data);
    $data->per_page=4;
    $data->current_page=(int)$request['page'];
    $data->last_page=ceil(count($products_data)/$data->per_page);
    $data->from=($data->current_page-1)*$data->per_page+1;
    $data->to=$data->from+$data->per_page;
    $data->data=$products_data;
    
	if($products_data == []){
	return new WP_REST_Response("Product Not Found!", 404);
    }
    else
    {
    return new WP_REST_Response($data, 200);
    }   
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
    
    register_rest_route( 'wl/v1', '/review', [
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'wl_review',
    ]);

	register_rest_route('wl/v1', 'subcatagories', [
	    'methods'  => WP_REST_Server::READABLE,
	    'callback' =>'wl_subcatagory',
	    ]);
	    
    
});