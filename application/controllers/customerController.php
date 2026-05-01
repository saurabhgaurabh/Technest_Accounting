<?php
defined('BASEPATH') or exit('No direct script access allowed');

class customerController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('customerModel');
        header('Content-Type: application/json');
    }

    public function createCustomer()
    {
        try {
            // check duplicate values
            $exists = $this->db->get_where('customers', array('mobile' => $this->input->post('mobile')));
            if ($exists->num_rows() > 0) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Customer with this mobile number already exists.'
                ]);
                return;
            }
            // allow only specific fields to be inserted
            // $allowed_fields = ['customer_name', 'mobile', 'email', 'address'];
            // $data = array_intersect_key($data, array_flip($allowed_fields));

            $data = $this->input->post();
            //required fields validation
            $required_fields = ['customer_name', 'mobile', 'email', 'address', 'postal_code'];

            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    echo json_encode([
                        'status' => false,
                        'message' => ucfirst($field) . ' is required.'
                    ]);
                    return;
                }
            }
            //insert data into database
            if ($this->customerModel->insert_customer($data)) {
                echo json_encode([
                    'status' => 200,
                    'message' => 'Customer Added Successfully.'
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => 'Database Error.'
                ]);
            }
        } catch (Exception $error) {
            echo json_encode([
                'status' => false,
                'message' => 'An error occurred: ' . $error->getMessage()
            ]);
        }
    }
}
