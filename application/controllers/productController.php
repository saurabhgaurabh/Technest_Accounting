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

        if (empty($itemGroupData['company_id'])) {
            echo json_encode([
                'status' => false,
                'message' => 'Company ID is required.'
            ]);
            return;
        }
        $checkCompany = $this->db->get_where('companies', array('company_id' => $itemGroupData['company_id']));
        if ($checkCompany->num_rows() <= 0) {
            echo json_encode([
                'status' => false,
                'message' => 'Invalid Company Id. This company does not exist.'
            ]);
            return;
        }

        $required_fields = ['group_name'];

        foreach ($required_fields as $field) {
            if (empty($itemGroupData[$field])) {
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

    public function Items()
    {
        try {
            $itemData = $this->input->post();
            $required_fields = ['item_name', 'sku', 'item_type', 'min_stock'];
            foreach ($required_fields as $field) {
                if (empty($itemData[$field])) {
                    echo json_encode([
                        'status' => false,
                        'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'
                    ]);
                    return;
                }
            }
            $checkExists = $this->db->get_where('items', array('item_name' => $itemData['item_name']));
            if ($checkExists->num_rows() > 0) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Item already exist in the database.'
                ]);
                return;
            }
            if (empty($itemData['company_id'])) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Company ID is required.'
                ]);
                return;
            }
            $checkCompany = $this->db->get_where('companies', array('company_id' => $itemData['company_id']));
            if ($checkCompany->num_rows() <= 0) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Invalid Company Id. This company does not exist.'
                ]);
                return;
            }

            $itemsValues = [
                'item_name' => $itemData['item_name'] ?? null,
                'sku' => $itemData['sku'] ?? null,
                'item_type' => $itemData['item_type'] ?? null,
                'hsn_code' => $itemData['hsn_code'] ?? null,
                'sac_code' => $itemData['sac_code'] ?? null,
                'gst_percent' => $itemData['gst_percent'] ?? null,
                'min_stock' => $itemData['min_stock'] ?? null,
                'company_id' => $itemData['company_id'] ?? null,
                'group_id' => $itemData['group_id'] ?? null,
            ];
            if ($this->productModel->model_of_createItems($itemsValues)) {
                echo json_encode([
                    'status' => true,
                    'message' => "Item '{$itemData['item_name']}' created successfully."
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => "Failed to create item '{$itemData['item_name']}"
                ]);
            }
        } catch (Exception $error) {
            echo json_encode([
                'status' => false,
                'message' => 'An error occurred: ' . $error->getMessage()
            ]);
        };
    }

    public function purchase()
    {
        try {
            $purchaseData = $this->input->post();
            $required_fields = ['bill_no'];
            foreach ($required_fields as $field) {
                if (empty($purchaseData[$field])) {
                    echo json_encode([
                        'status' => false,
                        'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required.'
                    ]);
                    return;
                }
            }
            $checkExists = $this->db->get_where('purchases', array('bill_no' => $purchaseData['bill_no']));
            if ($checkExists->num_rows() > 0) {
                echo json_encode([
                    'status' => false,
                    'message' => "Purchase with ID '{$purchaseData['bill_no']}' already exists."
                ]);
                return;
            }
            if (empty($purchaseData['company_id'])) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Company ID is required.'
                ]);
                return;
            }
            $checkCompany = $this->db->get_where('companies', array('company_id' => $purchaseData['company_id']));
            if ($checkCompany->num_rows() <= 0) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Invalid Company Id. This company does not exist.'
                ]);
                return;
            }
            if (empty($purchaseData['vendor_id'])) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Vender ID is required.'
                ]);
                return;
            }
            $checkVender = $this->db->get_where('parties', array('party_id' => $purchaseData['vendor_id']));
            if ($checkVender->num_rows() <= 0) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Invalid Vender'
                ]);
                return;
            }
            $insertPurchase = [
                'bill_no' => $purchaseData['bill_no'],
                'company_id' => $purchaseData['company_id'],
                'vendor_id' => $purchaseData['vendor_id'],
                'purchase_date' => $purchaseData['purchase_date'],
                'subtotal' => $purchaseData['subtotal'],
                'tax_total' => $purchaseData['tax_total'],
                'grand_total' => $purchaseData['grand_total'],
                'payment_status' => $purchaseData['payment_status']
            ];

            if ($this->productModel->model_of_purchase($insertPurchase)) {
                echo json_encode([
                    'status' => false,
                    'message' => "Purchase of Bill No.'{$purchaseData['bill_no']}' successfully."
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

    public function purchaseItems()
    {
        try {

            $purchaseData = $this->input->post();
            $required_fields = ['purchase_id', 'item_id', 'quantity', 'rate', 'total'];

            foreach ($required_fields as $field) {
                if (empty($purchaseData[$field])) {
                    echo json_encode([
                        'status' => false,
                        'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required.'
                    ]);
                    return;
                }
            }
            // $checkDuplicate = $this->db->get_where('purchase_items', 
            // array('purchase_id' => $purchaseData['purchase_id'], 'item_id' => $purchaseData['item_id']));
            // if ($checkDuplicate->num_rows() > 0) {
            //     echo json_encode([
            //         'status' => false,
            //         'message' => ""
            //     ]);
            //     return;
            // }
            if (empty($purchaseData['purchase_id'])) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Purchase ID is required.'
                ]);
                return;
            }
            $purchaseCheck = $this->db->get_where('purchases', array('purchase_id'  => $purchaseData['purchase_id']));
            if (!$purchaseCheck->num_rows() < 0) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Invalid Purchase ID.'
                ]);
                return;
            }
            if (empty($purchaseData['item_id'])) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Item ID is required.'
                ]);
                return;
            }
            $itemCheck = $this->db->get_where('items', array('item_id'  => $purchaseData['item_id']));
            if (!$itemCheck->num_rows() < 0) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Invalid Item ID. This user does not exist.'
                ]);
                return;
            }
            $insertPurchaseItems = [
                'purchase_id' => $purchaseData['purchase_id'],
                'item_id' => $purchaseData['item_id'],
                'quantity' => $purchaseData['quantity'],
                'rate' => $purchaseData['rate'],
                'total' => $purchaseData['total']
            ];
            if ($this->productModel->model_of_purchase_items($insertPurchaseItems)) {
                echo json_encode([
                    'status' => true,
                    'message' => "Purchase Items '{$insertPurchaseItems['quantity']}' successfully."
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
