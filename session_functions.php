<?php

function session_initialize(){
    if ($_SESSION == array()){
        $_SESSION["user_name"] = NULL;
        $_SESSION["user_email"] = NULL;
        $_SESSION["cart"] = array();
    }
}
function login_user($email,$name){
    $_SESSION["user_email"] = $email;
    $_SESSION["user_name"] = $name;
    $_SESSION["cart"] = array();
    
}

function get_current_user_name() {
    return $_SESSION["user_name"];
}

function get_current_user_data() {
    return get_user_data_from_email($_SESSION["user_email"]);
}

function logout_user($data){
    session_unset();
    session_initialize();
    $data["page"] = "home";
    return $data;
}

function isUserLoggedIn(){
    return ($_SESSION["user_name"] != NULL);
}

function add_to_cart($product_id){
    array_push($_SESSION["cart"], $product_id);
}

function remove_from_cart($product_id){
    if (($key = array_search($product_id, $_SESSION["cart"])) !== false) {
        unset($_SESSION["cart"][$key]);
    }
}

function get_cart(){
    return $_SESSION["cart"];
}
?>