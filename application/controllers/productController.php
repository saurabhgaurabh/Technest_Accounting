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

    public function categories()
    {
        $query = $this->db->get_where('categories', array('name' => $this->input->post('name')));

        if ($query->num_rows() > 0) {
            echo json_encode([
                'status' => false,
                'message' => 'Category already exist in the database.'
            ]);
            return;
        }
        $name = $this->input->post('name');
        // $categoryCode = $this->input->post('category_code');
        $categoryCode = fourDigitCode();
        $description = $this->input->post('description');

        if (!$name || !$categoryCode || !$description) {
            echo json_encode([
                'status' => false,
                'message' => 'name, category_code and description are required'
            ]);
            return;
        }

        $categoryData = [
            'name' => $name,
            'category_code' => $categoryCode,
            'description' => $description
        ];

        if ($this->productModel->createCategory($categoryData)) {
            echo json_encode(['status' => true, 'message' => "Category ${name} created successfully."]);
        } else {
            echo json_encode(['status' => false, 'message' => "Failed to Create Category ${name}."]);
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
            $queryExists = $this->db->get_where('items', array('item_code' => $this->input->post('item_code')));
            if($queryExists->num_rows() > 0){
                echo json_encode([
                    'status' => false,
                    'message' => 'Item already exist in the database.'
                ]);
                return;
            }
            $itemsData = $this->input->post();
            $required_fields = ['item_name', 'item_code', 'category', 'description'];

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
}
