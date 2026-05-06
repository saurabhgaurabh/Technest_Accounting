<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    class ProductModel extends CI_Model {

        public function createCategory($data){
            return $this->db->insert('categories', $data);
        }

        public function model_of_sub_Category($data){
            return $this->db->insert('subcategories', $data);
        }

        public function model_of_create_items($data){
            return $this->db->insert('items', $data);
        }
        
    }
?>