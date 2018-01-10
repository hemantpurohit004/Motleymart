<?php

$role=array
(
	1=>"Admin",2=>"Sales Manager",3=>"Product Manager"
);


$product_manager_views=array("product_default"); //"orders_default","orders_order",
$salesmanager_views=array("orders_storeorder",
										"orders_mycustomer","orders_customerdetails",
										"managecoupon_default","managecoupon_form"
										);
				
$rolearray=Array ();
$rolearray[2]=$salesmanager_views;
$rolearray[3]=$product_manager_views;

$universal_role=1;//if role is one then has all access 
?>
