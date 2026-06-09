<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AuthModel extends CI_Model
{
    public function insert_user($data)
    {
        return $this->db->insert('users', $data); 
    }

    public function verify_otp($user_id, $email, $otp)
    {
        $query = $this->db->where('user_id', $user_id)
            ->where('email', $email) 
            ->where('otp', $otp)
            ->get('users'); 
        return $query->num_rows() > 0;
    } 

    public function get_user_by_email($email)
    {
        return $this->db
            ->where('email', $email)
            ->limit(1)
            ->get('users')
            ->row();
    }
}