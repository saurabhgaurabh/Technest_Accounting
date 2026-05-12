<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    class ProductModel extends CI_Model {

        public function createItemGroup($data){
            return $this->db->insert('item_groups', $data);
        }

        public function model_of_createItems($data){
            return $this->db->insert('items', $data);
        }
        
        public function model_of_purchase($data){
            return $this->db->insert('purchases', $data);
        }
        public function model_of_purchase_items($purchaseData){
            return $this->db->insert('purchases', $purchaseData);
        }

        public function model_of_sell_items($sellData){
            return $this->db->insert('sales', $sellData);
        }
    }
?>