<?php

class Order extends CI_Model {
	// returns True if successful; False otherwise
	function create($order_details, $products) {
		$query = "INSERT INTO orders (billing_address_id, shipping_address_id, status, total, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())";
		$values = array($order_details['bill_id'], $order_details['ship_id'], $order_details['status'], $order_details['total']);
		if ($order) {
			$order = $this->db->query('SELECT LAST_INSERT_ID()')->row_array(); 
			$order_id = $order['LAST_INSERT_ID()'];

			$this->db->query($query,$values); 
			for ($i=0; $i < count($products); $i++) {
				// insert into order record
				$query = "INSERT INTO orders_has_products (quantity, product_id, order_id) VALUES (?, ?, ?)";
				$values = ($products[$i]['carts_has_products.quantity'], $products[$i]['id'], $order_id); 
				if (!$this->db->query($query,$values)) return False;
				// decrement and increment inventory and quantity sold, respectively
				$this->db->query("UPDATE products SET inventory = inventory - 1, quantity_sold = quantity_sold + 1 WHERE id = ?", array($products[$i]['id']));
				// return False if a product can't be added to the order. 
				
			}

			return True; 
		} else {
			return False; 
		}
	}

	// returns id of new address 
	function create_address($address_details) {
		$query = "INSERT INTO addresses (first_name, last_name, address, address2, zip, city, state, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())"; 
		$values = ($address_details['first_name'], $address_details['last_name'], $address_details['address'] $address_details['address2'], $address_details['zip'], $address_details['city'], $address_details['state']);
		$this->db->query($query,$values);

		$address = $this->db->query('SELECT LAST_INSERT_ID()')->row_array(); 
		$return $address['LAST_INSERT_ID()'];
	}

	function filter($subset_details) {
		$res_per_page = 5; 
		$start_index = $res_per_page*$subset_details['page'];
		$search = $subset_details['search'];

		$query = "SELECT * FROM orders LEFT JOIN addresses ON orders.billing_address_id = addresses.id WHERE (orders.id = $search OR addresses.first_name LIKE ? OR addresses.last_name LIKE ? OR addresses.address LIKE ? OR addresses.address2 LIKE ? OR addresses.zip LIKE ? OR addresses.city LIKE ? OR addresses.state LIKE ?) AND (status = ?) LIMIT ?,?"; 
		$values = array($search, $search, $search, $search, $search, $search, $search, $search, $subset_details['status'], $start_index,$res_per_page); 
	}

	function change_status_by_id($id, $status) {
		return $this->db->query("UPDATE orders WHERE orders.id = ? SET status = ?", array($id, $status)); 
	}

	function get_products_by_order_id($id) {
		$query = "SELECT * FROM orders_has_products LEFT JOIN products ON orders_has_products.id = products.id WHERE orders_has_products.order_id = ?";
		$values = array($id);

		return $this->db->query($query, $values)->record_array();
	}

	// returns row array 
	function get_order_by_id($id) {
		return $this->db->query("SELECT * FROM ORDERS LEFT JOIN addresses ON orders.billing_address_id = addresses.id WHERE orders.id = ?", array($id));
	}

	// returns records array 
	function get_all() {
		return $this->db->query("SELECT * FROM orders LEFT JOIN addresses ON orders.billing_address_id = addresses.id")->record_array(); 
	}
}

?>