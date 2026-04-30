<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    class ProductModel extends CI_Model {

        public function createCategory($data){
            return $this->db->insert('categories', $data);
        }
        
    }
?>