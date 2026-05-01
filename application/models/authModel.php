<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AuthModel extends CI_Model
{

    public function insert_user($data)
    {
        return $this->db->insert('users', $data);
    }
}
