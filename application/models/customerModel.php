<?php
defined('BASEPATH') or exit('No direct script access allowed');

class customerModel extends CI_Model
{

    public function insert_customer($data)
    {
        return $this->db->insert('customers', $data);
    }
}
