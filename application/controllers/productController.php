<?php
defined('BASEPATH') or exit('No direct script access allowed');


class ProductController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('productModel');
        $this->load->helper('custom');
        header('Content-Type: application/json');
    }

    public function createItemGroup()
    {
        $itemGroupData = $this->input->post();
        $query = $this->db->get_where('item_groups', array('group_name' => $this->input->post('group_name')));

        if ($query->num_rows() > 0) {
            echo json_encode([
                'status' => false,
                'message' => 'Group Name already exist in the database.'
            ]);
            return;
        }

        if(empty($itemGroupData['company_id'])){
            echo json_encode([
                'status' => false,
                'message' => 'Company ID is required.'
            ]);
            return;
        }
        $checkCompany = $this->db->get_where('companies', array('company_id' => $itemGroupData['company_id']));
        if($checkCompany->num_rows() <= 0){
            echo json_encode([
                'status' => false,
                'message' => 'Invalid Company Id. This company does not exist.'
            ]);
            return; 
        }

       $required_fields = ['group_name'];

        foreach($required_fields as $field){
            if(empty($itemGroupData[$field])){
                echo json_encode([
                    'status' => 'false',
                    'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required.'
                ]);
                return;
            }
        }
    
        $categoryData = [
            'group_name' => $itemGroupData['group_name'],
            'company_id' => $itemGroupData['company_id'],
            'description' => $itemGroupData['description']
        ];

        if ($this->productModel->createItemGroup($categoryData)) {
            echo json_encode(['status' => true, 'message' => "Category '{$categoryData['group_name']} created successfully."]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => "Failed to Create Category '{$categoryData['group_name']}."
            ]);
        };
    }

    public function subCategory()
    {
        try {
            // check duplicate values 
            $exists = $this->db->get_where('subcategories', array('name' => $this->input->post('name')));
            if ($exists->num_rows() > 0) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Sub Category already exists & Duplicate values are not allowed.'
                ]);
                return;
            }

            $globalInputValue = $this->input->post();
            $required_fields = ['category_id', 'name', 'description'];

            foreach ($required_fields as $field) {
                if (empty($globalInputValue[$field])) {
                    echo json_encode([
                        'status' => false,
                        'message' => ucfirst($field) . 'is required.'
                    ]);
                    return;
                }
            }

            // verify if the category_id exists in the categories table
            $categoryExists = $this->db->get_where('categories', array('category_id' => $globalInputValue['category_id']))->num_rows();
            if (!$categoryExists) {
                echo json_encode([
                    'status' => false,
                    'message' => 'The selected Category ID does not exist.'
                ]);
                return;
            }

            // check subcategory already exists or not
            $duplicateCheck = $this->db->get_where('subcategories', [
                'name' => $globalInputValue['name'],
                'category_id' => $globalInputValue['category_id']
            ]);

            if ($duplicateCheck->num_rows() > 0) {
                echo json_encode([
                    'status' => false,
                    'message' => "Sub-category '{$data['name']}' already exists under {$categoryExists->name}."
                ]);
                return;
            }

            // create variable to print the name in the response message
            $categoryName = isset($globalInputValue['name']) ? $globalInputValue['name'] : 'Sub Category';
            // load model
            if ($this->productModel->model_of_sub_Category($globalInputValue)) {
                echo json_encode([
                    'status' => true,
                    'message' => "Sub Category ${categoryName} Created Successfully."
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => "Failed to Create Sub Category."
                ]);
            }
        } catch (Exception $error) {
            echo json_encode([
                'status' => false,
                'message' => 'An error occurred: ' . $error->getMessage()
            ]);
        };
    }

    public function createItems()
    {
        try {

            $itemsData = $this->input->post();
            $required_fields = ['item_name', 'item_code', 'category', 'description'];

            if (empty($itemsData['user_id'])) {
                echo json_encode([
                    'status' => false,
                    'message' => 'User ID is required.'
                ]);
                return;
            }
            $userCheck = $this->db->get_where('users', array('user_id' => $itemsData['user_id']))->row();
            if (!$userCheck > 0) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Invalid user_id. This user does not exist.'
                ]);
                return;
            }
            $queryExists = $this->db->get_where('items', array('item_name' => $this->input->post('item_name')));
            if ($queryExists->num_rows() > 0) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Item already exist in the database.'
                ]);
                return;
            }


            // 1. Validation
            foreach ($required_fields as $field) {
                if (empty($itemsData[$field])) {
                    echo json_encode([
                        'status' => false,
                        'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required.'
                    ]);
                    return;
                }
            }
            $itemCode = fourDigitCode();
            $itemsData['item_code'] = $itemCode;
            $exists = $this->db->get_where('items', array('item_code' => $itemsData['item_code']));

            if ($exists->num_rows() > 0) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Item code already exists.'
                ]);
                return;
            }

            // 3. Insert via Model
            $itemName = $itemsData['item_name'];

            if ($this->productModel->model_of_create_items($itemsData)) {
                echo json_encode([
                    'status' => true,
                    'message' => "Item '{$itemName}' created successfully."
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => "Failed to create item '{$itemName}'."
                ]);
            }
        } catch (Exception $error) {
            echo json_encode([
                'status' => false,
                'message' => 'An error occurred: ' . $error->getMessage()
            ]);
        }
    }

    public function purchaseItems()
    {
        try {

            $purchaseData = $this->input->post();
            $required_fields = ['product_name', 'mobile', 'address'];

            foreach ($required_fields as $field) {
                if (empty($purchaseData[$field])) {
                    echo json_encode([
                        'status' => false,
                        'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required.'
                    ]);
                    return;
                }
            }
            if (empty($purchaseData['user_id'])) {
                echo json_encode([
                    'status' => false,
                    'message' => 'User ID is required.'
                ]);
                return;
            }
            $userCheck = $this->db->get_where('users', array('user_id'  => $purchaseData['user_id']));
            if (!$userCheck->num_rows() > 0) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Invalid user_id. This user does not exist.'
                ]);
                return;
            }
            $checkExists = $this->db->get_where('purchases', array('product_name' => $purchaseData['product_name']));
            if ($checkExists->num_rows() > 0) {
                echo json_encode([
                    'status' => false,
                    'message' => "Purchase of '{$purchaseData['product_name']}' already exists."
                ]);
                return;
            }
            if ($this->productModel->model_of_purchase_items($purchaseData)) {
                echo json_encode([
                    'status' => true,
                    'message' => "Purchase of '{$purchaseData['product_name']}' successfully."
                ]);
                return;
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => "Failed to record purchase of '{$purchaseData['product_name']}'."
                ]);
                return;
            }
        } catch (Exception $error) {
            echo json_encode([
                'status' => false,
                'message' => 'An error occurred: ' . $error->getMessage()
            ]);
        }
    }

    public function sellItems()
    {
        try {
            $sellData = $this->input->post();
            $required_fields = ['total_amount', 'mobile', 'postal_code'];
            foreach ($required_fields as $field) {
                if (empty($sellData[$field])) {
                    echo json_encode([
                        'status' => false,
                        'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required.'
                    ]);
                    return;
                }
            }
            $checkExists = $this->db->get_where('sales', array('user_id' => $sellData['user_id'], 'total_amount' => $sellData['total_amount']));
            if ($checkExists->num_rows() > 0) {
                echo json_encode([
                    'status' => false,
                    'message' => "Sale with ID '{$sellData['user_id']}' already exists."
                ]);
                return;
            }
            if (empty($sellData['user_id'])) {
                echo json_encode([
                    'status' => false,
                    'message' => 'User ID is required.'
                ]);
                return;
            }
            $userCheck = $this->db->get_where('users', array('user_id'  => $sellData['user_id']));
            if (!$userCheck->num_rows() > 0) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Invalid user_id. This user does not exist.'
                ]);
                return;
            }
            if ($this->productModel->model_of_sell_items($sellData)) {
                echo json_encode([
                    'status' => true,
                    'message' => "Sale of '{$sellData['total_amount']}' successfully."
                ]);
                return;
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => "Failed to record sale of '{$sellData['total_amount']}'."
                ]);
                return;
            }
        } catch (Exception $error) {
            echo json_encode([
                'status' => false,
                'message' => 'An error occurred: ' . $error->getMessage()
            ]);
        }
    }
}
