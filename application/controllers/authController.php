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
        $this->load->helper('url');
        $this->load->helper('jwt');
        header('Content-Type: application/json');
    }

    public function add_user()
    {
        try {
            $data = $this->input->post();
            $required_fields = ['username', 'mobile', 'email', 'password'];

            // 1. Validate all required fields
            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    echo json_encode([
                        'status' => false,
                        'message' => ucfirst(str_ireplace('_', ' ', $field)) . ' is required.'
                    ]);
                    return;
                }
            }

            // 2. Check duplicate users (username, mobile, or email)
            $exists = $this->db->group_start()
                ->where('username', $data['username'])
                ->or_where('mobile', $data['mobile'])
                ->or_where('email', $data['email'])
                ->group_end()->get('users');

            if ($exists->num_rows() > 0) {
                echo json_encode([
                    'status' => false,
                    'message' => 'User already exists with this username, mobile, or email.'
                ]);
                return;
            }

            // 3. Generate OTP
            $newOtp = fourDigitCode();

            // Send OTP email
            if (!send_otp_email($data['email'], $newOtp)) {
                $CI = &get_instance();
                echo json_encode([
                    'status' => false,
                    'message' => 'Failed to send OTP',
                    'debug' => $CI->email->print_debugger()
                ]);
                return;
            }

            // Hash the password if possible (highly recommended in production)
            // Example: 'password' => password_hash($data['password'], PASSWORD_BCRYPT)
            $userData = [
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

            // Validate all required fields first
            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    echo json_encode([
                        'status' => false,
                        'message' => ucfirst(str_ireplace('_', ' ', $field)) . ' is required.'
                    ]);
                    return;
                }
            }

            // Verify the OTP after completing validation
            $isValid = $this->authModel->verify_otp($data['user_id'], $data['email'], $data['otp']);
            if ($isValid) {
                $this->db->where('user_id', $data['user_id'])->update('users', ['flag' => 1, 'status' => 1]);
                echo json_encode([
                    'status' => true,
                    'message' => 'OTP verified successfully.'
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => 'Invalid OTP.'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function login()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            // throw new Exception('data', json_encode($data));
            if (empty($data)) {
                $data = $this->input->post();
            }


            $required_fields = ['email', 'password'];

            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    echo json_encode([
                        'status' => false,
                        'message' => ucfirst($field) . ' is required.'
                    ]);
                    return;
                }
            }

            $email = strtolower(trim($data['email']));
            $user = $this->authModel->get_user_by_email($email);

            if (!$user) {
                echo json_encode([
                    'status' => false,
                    'message' => 'User not found'
                ]);
                return;
            }

            // Match user's password (If using plain text)
            if ($user->password !== $data['password']) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Invalid password'
                ]);
                return;
            }

            // Generate JWT Token
            $jwt = test_token([
                'user_id' => $user->user_id,
                'email' => $user->email,
                'username' => $user->username
            ]);

            echo json_encode([
                'status' => true,
                'message' => 'Login successful',
                'token' => $jwt,
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
                'message' => 'JWT Error: ' . $e->getMessage()
            ]);
        }
    }
}
