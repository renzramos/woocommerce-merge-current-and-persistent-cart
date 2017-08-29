<?php
function wc_custom_user_redirect( $redirect, $user ) {
    if($_COOKIE['_redirect_url'] == '') {
       
        if (sizeof(WC()->cart->get_cart()) != 0) {
            return home_url('checkout');
        }else{
            return home_url('my-account');  
        }
    }else{
        $redirect = $_COOKIE['_redirect_url'];
    }
    
    
    
    // declaration 
    $final_items_key = array();
   
   
    // get current cart sessions 
    global $woocommerce;
    
    $items = $woocommerce->cart->get_cart();
    $current_items = array();
    foreach($items as $item => $values) { 
        $product_id =  $values['data']->get_id();
        $quantity = $values['quantity'];
        
        $current_items[$product_id] = array(
            'product_id' => $product_id,    
            'quantity' => $quantity,    
        );
        
        $final_items_key[$product_id] = $product_id;
    } 
    
    // persistent cart
    $persistent_cart = get_user_meta( $user->ID, '_woocommerce_persistent_cart_' . get_current_blog_id(), true );
    $persistent_cart_items  = $persistent_cart['cart'];
    $persistent_items = array();
    foreach ($persistent_cart_items as $item){
        
        $product_id =  $item['product_id'];
        $quantity = $item['quantity'];
        
        $persistent_items[$product_id] = array(
            'product_id' => $product_id,    
            'quantity' => $quantity,    
        );
        
        $final_items_key[$product_id] = $product_id;
    }
    
    
    // check final keys
    $final_items = array();
    foreach ($final_items_key as $key){
        $product_id = $key;
        $quantity = 0;
        
        // check current sessions
        if (!empty($current_items[$product_id])){
            $quantity+= $current_items[$product_id]['quantity'];
        }
        
        // check persitent cart
        if (!empty($persistent_items[$product_id])){
            $quantity+= $persistent_items[$product_id]['quantity'];
        }
        
        // final item
        $final_items[$product_id] = array(
            'product_id' => $product_id,    
            'quantity' => $quantity,    
        );
        
    }
   
    // empty session but retain persistent cart
    WC()->cart->empty_cart(); 
    
    // add previous cart items
    foreach ($final_items as $item){
        $woocommerce->cart->add_to_cart($item['product_id'], $item['quantity']);
    }
    
   
    return $redirect;
}
add_filter( 'woocommerce_login_redirect', 'wc_custom_user_redirect', 10, 2 );

?>
