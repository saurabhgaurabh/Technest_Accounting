<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AuthController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('authModel');
        // Setting JSON header for the entire controller.
        // This is acceptable for a dedicated API controller.
        header('Content-Type: application/json');
    }

   
}
?>