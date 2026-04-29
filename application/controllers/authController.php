<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AuthController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('authModel');
        header('Content-Type: application/json');
    }

    public function add_user() {
        $username = $this->input->post('username');
        $mobile = $this->input->post('mobile');
        
        if(empty($username) || empty($mobile)) {
            echo json_encode([
                'status' => false,
                'message' => 'username & mobile are required'
            ]);
            return;
        }

        $userdata = [ 'username' => $username, 'mobile' => $mobile ];

        if($this->authModel->insert_user($userdata)){
            echo json_encode([
                'status' => 200,
                'message' => 'User Added Successfully.'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Database Error: '
            ]);
        }
    }
}
?>
