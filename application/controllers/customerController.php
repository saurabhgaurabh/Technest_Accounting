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
                    'message' => 'Customer with the duplicate value already exists.'
                ]);
                return;
            }
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
            // to fetch the customer name in the response message
            $customer_name = isset($data['customer_name']) ? $data['customer_name'] : 'Customer';
            //insert data into database
            if ($this->customerModel->insert_customer($data)) {
                echo json_encode([
                    'status' => true,
                    'message' => "Customer ${customer_name} Added Successfully."
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
