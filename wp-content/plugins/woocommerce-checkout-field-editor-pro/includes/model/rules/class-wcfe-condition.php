<?php
/**
 * 
 *
 * @author      ThemeHiGH
 * @category    Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_Condition')):

class WCFE_Condition {
	const PRODUCT = 'product';
	const PRODUCT_VARIATION = 'product_variation';
	const CATEGORY = 'category';
	const FIELD = 'field';
	
	const USER_ROLE_EQ = 'user_role_eq';
	const USER_ROLE_NE = 'user_role_ne';
	
	const CART_CONTAINS = 'cart_contains'; 
	const CART_NOT_CONTAINS = 'cart_not_contains'; 
	const CART_ONLY_CONTAINS = 'cart_only_contains';
	
	const CART_TOTAL_EQ = 'cart_total_eq'; 
	const CART_TOTAL_GT = 'cart_total_gt'; 
	const CART_TOTAL_LT = 'cart_total_lt';
	
	const CART_SUBTOTAL_EQ = 'cart_subtotal_eq'; 
	const CART_SUBTOTAL_GT = 'cart_subtotal_gt'; 
	const CART_SUBTOTAL_LT = 'cart_subtotal_lt';
	
	/*const COUNT_EQ = 'count_eq'; 
	const COUNT_GT = 'count_gt'; 
	const COUNT_LT = 'count_lt';*/
		
	const VALUE_EMPTY = 'empty';
	const VALUE_NOT_EMPTY = 'not_empty';
	
	const VALUE_EQ = 'value_eq';
	const VALUE_NE = 'value_ne'; 
	const VALUE_GT = 'value_gt'; 
	const VALUE_LT = 'value_le';
	
	public $operand_type = '';
	public $operand = '';
	public $operator = '';
	public $value = '';
	
	public $show_when_str = '';
		
	public function __construct() {
		
	}	
	
	/*public function is_blank($value) {
		return empty($value) && !is_numeric($value);
	}*/
	
	/*public function is_valid(){
		$total_operators = array(self::CART_TOTAL_EQ, self::CART_TOTAL_GT, self::CART_TOTAL_LT, self::CART_SUBTOTAL_EQ, self::CART_SUBTOTAL_GT, self::CART_SUBTOTAL_LT, 
							self::USER_ROLE_EQ, self::USER_ROLE_NE);
		
		if(!empty($this->operand_type) && !empty($this->operator)){
			return true;
		}else if(!empty($this->operator) && in_array($this->operator, $total_operators) && !$this->is_blank($this->operand)){
			return true;
		}
		return false;
	}*/
	
	/*public function is_subset_of($arr1, $arr2){
		if(is_array($arr1) && is_array($arr2)){
			foreach($arr2 as $value){
				if(!in_array($value, $arr1)){
					return false;
				}
			}
		}
		return true;
	}*/
	
	/*public function get_user_roles($user = false) {
		$user = $user ? new WP_User( $user ) : wp_get_current_user();
		
		if(!($user instanceof WP_User))
		   return false;
		   
		$roles = $user->roles;
		return $roles;
	}*/
	
	/*public function get_wpml_translated_taxonomy($slug){
		//if(function_exists('icl_object_id')){
		//	$english_ID_lang = icl_object_id ($slug, 'category', true, ICL_LANGUAGE_CODE);
		/}
		
		$translated_slug = $slug;
		if(defined('ICL_LANGUAGE_CODE')){
			$translated_slug = ICL_LANGUAGE_CODE != 'en' ? $slug.'-'.ICL_LANGUAGE_CODE : $slug;
			$translated_slug = apply_filters( 'thwcfe_cr_wpml_translated_taxonomy', $translated_slug, $slug, ICL_LANGUAGE_CODE );
		}
		return $translated_slug;
	}*/
	
	/*public function check_for_wpml_translations($categoties){
		if(apply_filters( 'thwcfe_cr_use_wpml_translated_taxonomy', false )){
			if(is_array($categoties)){
				foreach($categoties as $key => $value){
					$categoties[$key] = $this->get_wpml_translated_taxonomy($value);
				}
			}
		}		
		return $categoties;
	}*/
	
	/**public function is_satisfied($products, $categories, $product_variations=false){
		$satisfied = true;
		if($this->is_valid()){
			$op_type  = $this->operand_type;
			$operator = $this->operator;
			$operands = $this->operand;
			
			if($op_type == self::PRODUCT && is_array($products)){
				$intersection = array();
				if(is_array($operands) && in_array('-1', $operands)){
					$operands = WCFE_Checkout_Fields_Utils::load_products(true);
				}
				
				if(is_array($products) && is_array($operands)){
					$intersection = array_intersect($products, $operands);
				}
				
				if($operator == self::CART_CONTAINS) {
					if(!$this->is_subset_of($products, $operands)){
					//if($intersection != $values){
						return false;
					}
				}else if($operator == self::CART_NOT_CONTAINS){
					if(!empty($intersection)){
						return false;
					}
				}else if($operator == self::CART_ONLY_CONTAINS){
					if($products != $operands){
						return false;
					}
				}
			}else if($op_type == self::PRODUCT_VARIATION && is_array($product_variations)){
				$intersection = array();
				$operands = is_array($operands) ? $operands : explode(',', $operands);
								
				if(is_array($product_variations) && is_array($operands)){
					$intersection = array_intersect($product_variations, $operands);
				}
				
				if($operator == self::CART_CONTAINS) {
					if(!$this->is_subset_of($product_variations, $operands)){
						return false;
					}
				}else if($operator == self::CART_NOT_CONTAINS){
					if(!empty($intersection)){
						return false;
					}
				}else if($operator == self::CART_ONLY_CONTAINS){
					if($product_variations != $operands){
						return false;
					}
				}
			}else if($op_type == self::CATEGORY && is_array($categories)){
				$intersection = array();
				if(is_array($operands) && in_array('-1', $operands)){
					$operands = WCFE_Checkout_Fields_Utils::load_products_cat(true);
				}
				$operands = $this->check_for_wpml_translations($operands);
				
				if(is_array($categories) && is_array($operands)){
					$intersection = array_intersect($categories, $operands);
				}
				
				if($operator == self::CART_CONTAINS) {
					if(!$this->is_subset_of($categories, $operands)){
						return false;
					}
				}else if($operator == self::CART_NOT_CONTAINS){
					if(!empty($intersection)){
						return false;
					}
				}else if($operator == self::CART_ONLY_CONTAINS){
					if($categories != $operands){
						return false;
					}
				}
			}else if($operator == self::USER_ROLE_EQ || $operator == self::USER_ROLE_NE){
				$user_roles = $this->get_user_roles();
				
				if(is_array($user_roles) && is_array($operands)){
					$intersection = array_intersect($user_roles, $operands);
					
					if($operator == self::USER_ROLE_EQ) {
						if(empty($intersection)){
							return false;
						}
					}else if($operator == self::USER_ROLE_NE){
						if(!empty($intersection)){
							return false;
						}
					}
				}
			}else{
				if(is_numeric($operands)){
					$cart_total = WC()->cart->total;
					$cart_subtotal = WC()->cart->subtotal;
					
					if($operator == self::CART_TOTAL_EQ){
						if($cart_total != $operands){
							return false;
						}
					}else if($operator == self::CART_TOTAL_GT){
						if($cart_total <= $operands){
							return false;
						}
					}else if($operator == self::CART_TOTAL_LT){
						if($cart_total >= $operands){
							return false;
						}
					}else if($operator == self::CART_SUBTOTAL_EQ){
						if($cart_subtotal != $operands){
							return false;
						}
					}else if($operator == self::CART_SUBTOTAL_GT){
						if($cart_subtotal <= $operands){
							return false;
						}
					}else if($operator == self::CART_SUBTOTAL_LT){
						if($cart_subtotal >= $operands){
							return false;
						}
					}
				}
			}
			//else if($operator == self::EMPTY){
				
			//}else if($operator == self::NOT_EMPTY){
				
			//}
		}
		return $satisfied;
	}***/
			
	/*public function is_satisfied($product, $categories){
		$satisfied = true;
		if($this->is_valid()){
			if($this->operand_type == self::PRODUCT){
				if($this->operator == self::EQUALS) {
					if($this->value != $product){
						return false;
					}
				}else if($this->operator == self::NOT_EQUALS){
					if($this->value == $product){
						return false;
					}
				}
			}else if($this->operand_type == self::CATEGORY){
				if($this->operator == self::EQUALS) {
					if(!in_array($this->value, $categories)){
						return false;
					}
				}else if($this->operator == self::NOT_EQUALS){
					if(in_array($this->value, $categories)){
						return false;
					}
				}
			}
		}
		return $satisfied;
	}*/
	
	/*public function set_operand_type($operand_type){
		$this->operand_type = $operand_type;
	}	
	public function get_operand_type(){
		return $this->operand_type;
	}
	
	public function set_operand($operand){
		$this->operand = $operand;
	}	
	public function get_operand(){
		return $this->operand;
	}
	
	public function set_operator($operator){
		$this->operator = $operator;
	}	
	public function get_operator(){
		return $this->operator;
	}
	
	public function set_value($value){
		$this->value = $value;
	}	
	public function get_value(){
		return $this->value;
	}*/
	
	public function set_property($name, $value){
		if(property_exists($this, $name)){
			$this->$name = $value;
		}
	}
	
	public function get_property($name){
		if(property_exists($this, $name)){
			return $this->$name;
		}else{
			return '';
		}
	}
}

endif;