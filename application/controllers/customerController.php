<?php
defined('BASEPATH') or exit('No direct script access allowed');

class customerController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('customerModel');
        $this->load->helper('custom');
        header('Content-Type: application/json');
    }

    public function createCustomer()
    {
        try {
            $data = $this->input->post();
            if (empty($data['user_id'])) {
                echo json_encode(['status' => false, 'message' => 'User ID is required.']);
                return;
            }
            $userCheck = $this->db->get_where('users', array('user_id' => $data['user_id']))->row();

            if (!$userCheck) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Invalid user_id. This user does not exist.'
                ]);
                return;
            }
            // check duplicate values
            $exists = $this->db->get_where('customers', array('mobile' => $data['mobile']));
            if ($exists->num_rows() > 0) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Customer with the duplicate value already exists.'
                ]);
                return;
            }
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
            $customerCode = fourDigitCode();
            $data['customer_code'] = $customerCode;
            $data['user_id'] = $userCheck->user_id;
            $customer_name = $data['customer_name'];
            //insert data into database
            if ($this->customerModel->insert_customer($data)) {
                echo json_encode([
                    'status' => true,
                    'message' => "Customer '{$customer_name}' Added Successfully."
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
