<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AuthController extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('authModel');
        $this->load->helper('custom');
        $this->load->library('email');
        $this->load->helper('jwt');
        header('Content-Type: application/json');
    }

    public function add_user()
    {
        try {
            $data = $this->input->post();
            $required_fields = ['username', 'mobile', 'email', 'password'];

            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    echo json_encode([
                        'status' => false,
                        'message' => ucfirst(str_ireplace('_', ' ', $field)) . ' is required.'
                    ]);
                    return;
                };
            }
            // check duplicate users/ values
            $exists = $this->db->group_start()
                ->where('username', $this->input->post('username'))
                ->or_where('mobile', $this->input->post('mobile'))
                ->group_end()->get('users');

            if ($exists->num_rows() > 0) {
                echo json_encode([
                    'status' => false,
                    'message' => 'User already exists.'
                ]);
                return;
            }

            // generate otp and insert data into database

            $newOtp = fourDigitCode();
            // Send OTP email
            if (!send_otp_email($data['email'], $newOtp)) {
                // Get the instance to print the debugger
                $CI = &get_instance();
                echo json_encode([
                    'status' => false,
                    'message' => 'Failed to send OTP',
                    'debug' => $CI->email->print_debugger()
                ]);
                return;
            }
            $userData =   [
                'username' => $data['username'] ?? null,
                'mobile' => $data['mobile'] ?? null,
                'email' => $data['email'] ?? null,
                'password' => $data['password'] ?? null,
                'otp' => $newOtp,
            ];

            $insert = $this->authModel->insert_user($userData);
            if ($insert) {
                echo json_encode([
                    'status' => true,
                    'message' => 'User created successfully.',
                    'data' => [
                        'username' => $data['username'] ?? null,
                        'mobile' => $data['mobile'] ?? null,
                        'email' => $data['email'] ?? null,
                        'password' => $data['password'] ?? null,
                        'otp' => $newOtp
                    ]
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => 'Failed to create user.',
                ]);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function verifyOTP()
    {
        try {
            $data = $this->input->post();
            $required_fields = ['user_id', 'email', 'otp'];
            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    echo json_encode([
                        'status' => false,
                        'message' => ucfirst(str_ireplace('_', ' ', $field)) . ' is required.'
                    ]);
                    return;
                }

                $isValid = $this->authModel->verify_otp($data['user_id'], $data['email'], $data['otp']);
                if ($isValid) {
                    $this->db->where('user_id', $data['user_id'])->update('users', ['flag' => 'verified']);
                    echo json_encode([
                        'status' => true,
                        'message' => 'OTP verified successfully.'
                    ]);
                    return;
                } else {
                    echo json_encode([
                        'status' => false,
                        'message' => 'Invalid OTP.'
                    ]);
                    return;
                }
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function login()
    {
        try {

            $data = $this->input->post();

            // ✅ Required fields
            if (empty($data['email']) || empty($data['password'])) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Email and password required'
                ]);
                return;
            }

            // 🔍 Get user (email + password)
            $user = $this->authModel->get_user_by_email($data['email'], $data['password']);


            if (!$user) {
                echo json_encode([
                    'status' => false,
                    'message' => 'User not found'
                ]);
                return;
            }

            $token = generate_jwt([
                'user_id' => $user->user_id,
                'email'   => $user->email
            ]);
            // ✅ Success response
            echo json_encode([
                'status' => true,
                'message' => 'Login successful',
                'token' => $token,
                'data' => [
                    'user_id' => $user->user_id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'mobile' => $user->mobile
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
