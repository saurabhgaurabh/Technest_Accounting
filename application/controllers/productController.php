<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class ProductController extends CI_Controller {
    public function __construct(){
        parent::__construct();
        $this->load->database();
        $this->load->model('productModel');
        $this->load->helper('custome');
        header('Content-Type: application/json');
    }

    public function categories(){
        $query = $this->db->get_where('categories', array('name' => $this->input->post('name')));
    //  $this->db->where('name', $this->input->post('name'));
        if($query-> num_rows() > 0 ){
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

        if(!$name ||!$categoryCode || !$description){
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

        if($this->productModel->createCategory($categoryData)){
            echo json_encode([ 'status' => true, 'message' => "Category ${name} created successfully." ]);
        }else{
            echo json_encode([ 'status' => false, 'message' => "Failed to Create Category ${name}."]);
        };
    }
}

?>