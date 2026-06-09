<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CustomerGetController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('customerGetModel');
        
        // Ensure the response is always JSON
        $this->output->set_content_type('application/json');
    }

    // GET API: api/vendor OR api/vendor/[:id]
    public function getVender($user_id = null)
    {
        // 🔐 Optional: Put your JWT validation helper check here
        // $user_data = validate_jwt_request(); 

        try {
            $data = $this->customerGetModel->get_vendors($user_id);

            if ($user_id !== null && !$data) {
                $this->output->set_status_header(404);
                echo json_encode([
                    'status' => false,
                    'message' => 'Vendor not found'
                ]);
                return;
            }

            // ✅ Success Response
            echo json_encode([
                'status' => true,
                'message' => $user_id ? 'Vendor details retrieved' : 'All vendors retrieved',
                'data' => $data
            ]);

        } catch (Exception $e) {
            $this->output->set_status_header(500);
            echo json_encode([
                'status' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ]);
        }
    }
}