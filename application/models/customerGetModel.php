<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CustomerGetModel extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // Fetch all vendors or a single vendor if ID is provided
    public function get_vendors($user_id = null)
    {
        if ($user_id !== null) {
            $this->db->where('vendor_id', $user_id);
            return $this->db->get('vender')->row(); // Returns a single object
        }

        // $this->db->where('is_active', 1); // Only fetch active vendors
        // $this->db->order_by('vendor_name', 'ASC');
        return $this->db->get('vender')->result(); // Returns an array of objects
    }
}