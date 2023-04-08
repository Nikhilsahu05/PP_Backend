<?php defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
ob_start();
/**
 * Class Refund
 * Create class for refund handling
*/
class Order extends REST_Controller {

    public function __construct() {
        parent::__construct();
        /* Load :: Models */
        $this->load->model('v1/order_model');
         $this->lang->load('API/order_history');
        $this->form_validation->set_error_delimiters(' | ', '');
       

    }

    public function create_order_post() {
        /* Check Authentications */
        $headers = $this->input->request_headers();
        
        $authorized_user = $this->general_model->check_authorization($headers);

        if ($authorized_user['status'] != 1) {
            $this->response([
                $this->config->item('rest_status_field_name') => $authorized_user['status'],
                $this->config->item('rest_message_field_name') => $authorized_user['message']
            ], REST_Controller::HTTP_OK);
        }
        /* End Check Authentications */

        $this->form_validation->set_rules('party_id', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('amount', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('papular_status', '', 'required', array('required' => '%s'));

        if ($this->form_validation->run() == true) {
             $order_num=$this->order_model->get_order_data();
             
            $data = [
                'user_id'              => $authorized_user['account']->id,
                'order_number'        =>$order_num,
                'party_id'             => $this->input->post('party_id'),
                'organization_id'      => @$this->input->post('organization_id'),
                'user_name'            => @$authorized_user['account']->first_name,
                'email_id'             => @$authorized_user['account']->email,
                'phone_number'         => @$authorized_user['account']->phone,
                 'amount'    => $this->input->post('amount'),
                'created_date'            => date('Y-m-d H:i:s')
            ];
           
               // print_r($data); die;
            $insert_data = $this->order_model->insert($data);
            if($insert_data) {
                 if($this->input->post('papular_status')==1)
                 {
                  $this->order_model->update_party(array('papular_status'=>$this->input->post('papular_status')),$this->input->post('party_id'));    
                 }
                
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('order_create_success'),
                       'order_id'    => $insert_data
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            } else {
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('order_create_fail')
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
        } else {
            $this->response([
            $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
            $this->config->item('rest_message_field_name') => 'Empty request parameter(s). [ ' . ltrim(str_replace("\n", '', validation_errors()), ' |') . ' ]'
                ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
         
    }





}