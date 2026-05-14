<?php
defined('BASEPATH') or exit('No direct script access allowed');

class customerModel extends CI_Model
{

    public function insert_company($data)
    {
        return $this->db->insert('companies', $data);
    }

    public function insert_vender($data)
    {
        return $this->db->insert('vender', $data);
    }

    public function insert_customer($data)
    {
        return $this->db->insert('customers', $data);
    }
}
