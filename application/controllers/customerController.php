<?php

use PhpParser\Node\Stmt\TryCatch;

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

    public function createCompany()
    {
        try {
            $companyData = $this->input->post();
            if (empty($companyData['user_id'])) {
                echo json_encode([
                    'status' => false,
                    'message' => 'User ID is required.'
                ]);
                return;
            }
            $userCheck = $this->db->get_where('users', array('user_id' => $companyData['user_id']));

            if (!$userCheck->num_rows() > 0) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Invalid User Id. This user does not exist.'
                ]);
                return;
            }
            $required_fields = ['company_name', 'gstin', 'state'];

            foreach ($required_fields as $field) {
                if (empty($companyData[$field])) {
                    echo json_encode([
                        'status' => false,
                        'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required.'
                    ]);
                    return;
                }
            }

            // check duplicate company
            $exists = $this->db->get_where('companies', array('gstin' => $companyData['gstin']));
            if ($exists->num_rows() > 0) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Company with the same GSTIN already exists.'
                ]);
                return;
            }
            $companyInsertion = [
                'company_name' => $companyData['company_name'],
                'gstin' => $companyData['gstin'],
                'state' => $companyData['state'],
                'user_id' => $companyData['user_id']
            ];

            if ($this->customerModel->insert_company($companyInsertion)) {
                echo json_encode([
                    'status' => true,
                    'message' => "Company '{$companyData['company_name']}' Added Successfully."
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

    public function createParties()
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
            if (empty($data['company_id'])) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Company ID is required.'
                ]);
                return;
            }
            $checkCompany = $this->db->get_where('companies', array('company_id' => $data['company_id']));
            if ($checkCompany->num_rows() <= 0) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Invalid Company Id. This company does not exist.'
                ]);
                return;
            }
            // check duplicate values
            $exists = $this->db->get_where('parties', array('name' => $data['name']));
            if ($exists->num_rows() > 0) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Party/Client with the duplicate value already exists.'
                ]);
                return;
            }
            //required fields validation
            $required_fields = ['party_type', 'name', 'mobile', 'email', 'state', 'address'];

            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    echo json_encode([
                        'status' => false,
                        'message' => ucfirst($field) . ' is required.'
                    ]);
                    return;
                }
            }
            $data['user_id'] = $userCheck->user_id;
            if ($this->customerModel->insert_parties($data)) {
                echo json_encode([
                    'status' => true,
                    'message' => "Party/Client '{$data['name']}' Added Successfully."
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
