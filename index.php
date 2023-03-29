<?php 
include "html_build_functions.php";
include "validate_form_functions.php";
include "file_repository.php";
include "session_functions.php";
include "classes.php";
session_start();
#create session variables on first load
session_initialize();

#handle the request and return the page to be loaded
$page = getRequestedPage();
$data = process_Request($page);
showResponsePage($data);

function getRequestedPage(){
    if (is_POST()) { return $_POST['page'];}
    
    return $_GET["page"];
}

function get_total_price($products){
    $total_price = 0;
    foreach ($products as $product){
        $total_price += $product->get_price();
    }
    return $total_price;
}

function make_menu($data) {
    $menu = array("home" => "Home", "about" => "About",
    "contact" => "Contact", "webshop" => "Webshop", "top5" => "Top 5");
    if (isUserLoggedIn()){
        $menu["logout"] = "Loguit " . get_current_user_name();
        $menu["change_password"] = "Wachtwoord veranderen";
        $menu["shoppingcart"] = "Winkelwagen";
    } else {
        $menu["register"] =  "Registeer";
        $menu["login"] = "Login";
    }
    $menu["upload"] = "Upload";
    $data["menu"] = $menu;
    return $data;
}

function process_Request($page){
    $page = htmlspecialchars($page);
    $data = array("page"=>$page,"errors"=>array());
    switch ($page){
        case "home":
            break;
        case "about":
            break;
        case "contact":
            if (is_POST()){
                $data = validate_form_contact($data);

                $thanks = is_valid($data);
                $data["thanks"] = $thanks;
            }
            break;
        case "register":
            if (is_POST()){
                $data = validate_form_register($data);
                if (is_valid($data)){
                    save_user($data["email"],$data["name"],$data["password"]);
                    $data["page"] = "login";
                }
            } 
            break;
        case "login":
            if (is_POST()){
                $data = validate_form_login($data);
                if (is_valid($data)){
                    login_user($data);
                    $data["page"] = "home";
                }
            }
            break;
        case "logout":
            $data = logout_user($data);
            break;
        case "change_password":
            if (is_POST()){
                $data = validate_form_change_password($data);
                if (is_valid($data)) {
                    set_new_password($data["email"], $data["password"]);
                }
            }
            break;
        case "webshop":
            if (is_POST()){
                $data = validate_cart($data);
                if (is_valid($data)) {
                    switch ($data["action"]){
                        case "add":
                            add_to_cart($data["id_in_cart"], $data["amount"]);
                            break;
                        case "remove":
                            remove_from_cart($data["id_in_cart"]);	
                            break;
                    }

                    switch ($data["place"]){
                        case "detail":
                            $data["page"] = "shoppingcart";
                            $product_ids = get_product_id_from_cart();
                            if ($product_ids != NULL) {
                                $data['products'] = get_products_by_id($product_ids);
                                $data['total_price'] = get_total_price($data['products']);
                            }
                            break;
                        case "overview":
                            $data["id"] = NULL;
                            $product_ids = array(1,2,3,4,5);
                            $data['products'] = get_products_by_id($product_ids);
                            break;
                    }
                                              
                }               
            } else if (array_key_exists("id", $_GET)) {
                $data["id"] =  $_GET["id"];
                $product_ids = array($data["id"]);
                $data['products'] = get_products_by_id($product_ids);
            } else {
                $data["id"] = NULL;
                $product_ids = array(1,2,3,4,5);
                $data['products'] = get_products_by_id($product_ids);
            }
            
                
            
            break;
        case "shoppingcart":
            if (is_POST()){
                $data["order"] = get_cart();
                $data["ordered_product_ids"] = get_product_id_from_cart();
                empty_cart();
                
                $data["time"] = date("Y-m-d");
                $data["user_id"] = get_user_id();
                save_order($data);
                $data["page"] = "home";
            } else {
                $order = get_cart();
                $product_ids = array_keys(get_cart());
                if ($product_ids != NULL) {
                    $data["order"] = $order;
                    $data['products'] = get_products_by_id($product_ids);
                    $data['total_price'] = get_total_price($data['products']);
                }
            }
            break;
        case "top5":
            $data['products'] = get_products_top5();
            break;

        case "upload":
            if (is_POST()){
                $data = validate_upload($data);
                if (is_valid($data)) {
                    echo "asdfghj";
                    $target_dir = "Images/";
                    $file_name = basename($_FILES["fileToUpload"]["name"]);
                    $target_file = $target_dir . $file_name;
                    $uploadOk = 1;
                    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
                    // Check if image file is a actual image or fake image
                    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
                    
                    if($check !== false) {
                        echo "File is an image - " . $check["mime"] . ".";
                        $uploadOk = 1;
                    } else {
                        echo "File is not an image.";
                        $uploadOk = 0;
                    }
                    // Check if file already exists
                    if (file_exists($target_file)) {
                        echo "Sorry, file already exists.";
                        $uploadOk = 0;
                    }
                    // Check file size
                    if ($_FILES["fileToUpload"]["size"] > 500000) {
                        echo "Sorry, your file is too large.";
                        $uploadOk = 0;
                    }

                    // Allow certain file formats
                    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
                    && $imageFileType != "gif" && $imageFileType != "webp" ) {
                    echo "Sorry, only JPG, JPEG, PNG, WEBP & GIF files are allowed.";
                    $uploadOk = 0;
                    }
                    // Check if $uploadOk is set to 0 by an error
                    if ($uploadOk == 0) {
                        echo "Sorry, your file was not uploaded.";
                    // if everything is ok, try to upload file
                    } else {
                        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                        echo "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded.";
                        } else {
                        echo "Sorry, there was an error uploading your file.";
                        }
                    }
                    echo $file_name;
                    #$product = new Product(NULL,$data["title"],$data["description"],$file_name ,$data["price"]);
                    #save_product($product);

                }

            }
            break;
        default:
            $data["page"] = "home";
            break;
    }

    $data = make_menu($data);
    return $data;
}

function is_POST(){
    return ($_SERVER["REQUEST_METHOD"] == "POST");
}




?>