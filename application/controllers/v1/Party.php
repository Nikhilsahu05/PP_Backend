<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

/**
 * Class Home
 * Create class for app home screen handling
*/
class Party extends REST_Controller {

    public function __construct() {
        parent::__construct();

        /* Load :: Helper */
        $this->lang->load('API/party');
        /* Load :: Models */
        $this->load->model('v1/party_model');

        $this->form_validation->set_error_delimiters(' | ', '');
    }

    /**
     * Home Banner 
     * Method (POST)
     */
    public function banners_get() {

        $banners = $this->party_model->get_all_banners();

        if ($banners) {
            $banner_array = [];
            foreach ($banners as $key => $banner) {
                $banner->banner_image = !empty($banner->banner_image) ? base_url($banner->banner_image) : "";
            }

            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('banner_found'),
                $this->config->item('rest_data_field_name')     => $banners
            ], REST_Controller::HTTP_OK);

        } else {
            $this->response([
                $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name') => $this->lang->line('banner_not_set_yet')
            ], REST_Controller::HTTP_OK);
        }
    }

    /**
     * Discount Restaurant 
     * Method (POST)
     */
    public function percentage_off_post() {

        $latitude = !empty($this->input->post('latitude')) ? $this->input->post('latitude') : "";
        $longitude = !empty($this->input->post('longitude')) ? $this->input->post('longitude') : "";

        $data = $this->home_model->get_percentage_off($latitude, $longitude);

        if ($data) {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('percentage_off_found'),
                $this->config->item('rest_data_field_name')     => $data
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('percentage_off_not_found')
            ], REST_Controller::HTTP_OK);
        }
    }

    public function filter_categories_post() {

        $latitude = !empty($this->input->post('latitude')) ? $this->input->post('latitude') : "";
        $longitude = !empty($this->input->post('longitude')) ? $this->input->post('longitude') : "";

        $data = $this->home_model->get_filter_categories($latitude, $longitude);

        if ($data) {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('percentage_off_found'),
                $this->config->item('rest_data_field_name')     => $data
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('percentage_off_not_found')
            ], REST_Controller::HTTP_OK);
        }
    }

    public function add_post() {
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

        $this->form_validation->set_rules('title', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('description', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('start_date', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('end_date', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('start_time', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('end_time', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('latitude', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('longitude', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('type', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('gender', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('start_age', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('end_age', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('person_limit', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('status', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('party_amenitie_id', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('phone_number', '', 'required', array('required' => '%s'));

         if(!empty(check_comma_separated_value($this->input->post('gender'))))
            {
                 $this->response([
                    $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name') => 'Invailed gender in comma separated value',
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }

        /*---------------------Comma separated validation----------------*/
          if(!empty(check_comma_separated_value($this->input->post('party_amenitie_id'))))
            {
                 $this->response([
                    $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name') => 'Invailed party amenitie in comma separated value',
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
        if ($this->form_validation->run() == true) {
            if (strtotime($this->input->post('end_date')) < strtotime($this->input->post('start_date'))) {
                $this->response([
                    $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name') => 'End Date Cannot Be Less Than Start Date',
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            } elseif (strtotime($this->input->post('end_date')) == strtotime($this->input->post('start_date'))) {
                if ($this->input->post('end_time') < $this->input->post('start_time')) {
                    $this->response([
                        $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                        $this->config->item('rest_message_field_name') => 'End Time Cannot Be Less Than Start Time',
                            ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
                }
            } elseif ($this->input->post('start_age') > $this->input->post('end_age')) {
                $this->response([
                    $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name') => 'End Age Cannot Be Less Than Start Age',
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            } 
            $data = [
                 'user_id'               => $authorized_user['account']->id,
                'organization_id'       => $this->input->post('organization_id'),
                'phone_number'       => $this->input->post('phone_number'),

                'title'                 => $this->input->post('title'),
                'description'           => $this->input->post('description'),
                'start_date'            => strtotime($this->input->post('start_date')),
                'end_date'              => strtotime($this->input->post('end_date')),
                'start_time'            => $this->input->post('start_time'),
                'end_time'              => $this->input->post('end_time'),
                'latitude'              => $this->input->post('latitude'),
                'longitude'             => $this->input->post('longitude'),
                'type'                  => $this->input->post('type'),
                'gender'                => $this->input->post('gender'),
                'start_age'             => $this->input->post('start_age'),
                'end_age'               => $this->input->post('end_age'),
                'person_limit'          => $this->input->post('person_limit'),
                'status'                => $this->input->post('status'),
                'party_amenitie_id'     => $this->input->post('party_amenitie_id'),
                'offers'     => $this->input->post('offers'),
                'ladies'     => $this->input->post('ladies'),
                'stag'     => $this->input->post('stag'),
                'couples'     => $this->input->post('couples'),
                'others'     => $this->input->post('others'),
                'created_at'            => date('Y-m-d H:i:s'),
                'cover_photo'=> $this->input->post('cover_photo')
            ];
            /*if (empty($_FILES['cover_photo']['name'])) {
                $this->form_validation->set_rules('cover_photo', 'cover_photo', 'required', array('required' => '%s'));
                $this->response([
                    $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name') => 'Empty request parameter(s) [ cover_photo ]',
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            } else {
                $file_name = 'party_' . time() . rand(100, 999);
                $config = [
                    'upload_path' => './upload/party/',
                    'file_name' => $file_name,
                    'allowed_types' => 'png|jpg|jpeg',
                    'max_size' => 50480,
                    'max_width' => 20480,
                    'max_height' => 20480,
                    'file_ext_tolower' => TRUE,
                    'remove_spaces' => TRUE,
                ];
                $this->load->library('upload/', $config);
                if ($this->upload->do_upload('cover_photo')) {
                    $uploadData = $this->upload->data();
                    $data['cover_photo'] = 'upload/party/' . $uploadData['file_name'];
                } else {
                    $this->response([
                        $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                        $this->config->item('rest_message_field_name')  => $this->upload->display_errors()
                            ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
                }
            }*/

            $insert_data = $this->party_model->insert_party($data);
            if($insert_data) {
                 /*-------------Create Notification------------*/
                  $noti_arr=array('notification_title'=>'Party Post','notification_message'=>'Congratulations! your party is posted and its under review.','notification_type'=>2,'notification_type_name'=>'Party Post','user_id'=>$authorized_user['account']->id);
                  $this->general_model->insert('notifications',$noti_arr);
                /*--------------------------------------------*/
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('insert_success_party_message')
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            } else {
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('insert_failed_party_message')
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
        } else {
            $this->response([
            $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
            $this->config->item('rest_message_field_name') => 'Empty request parameter(s). [ ' . ltrim(str_replace("\n", '', validation_errors()), ' |') . ' ]'
                ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }
 
    public function edit_post() {
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
        if ($this->form_validation->run() == true) {
            $data = $this->party_model->get_party_by_id($this->input->post('party_id'));
            if ($data) {
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('get_success_party_message'),
                    $this->config->item('rest_data_field_name') => $data
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            } else {
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('get_failed_party_data')
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
        } else {
            $this->response([
            $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
            $this->config->item('rest_message_field_name') => 'Empty request parameter(s). [ ' . ltrim(str_replace("\n", '', validation_errors()), ' |') . ' ]'
                ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }

    public function update_post() {
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
        $this->form_validation->set_rules('title', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('description', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('start_date', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('end_date', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('start_time', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('end_time', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('latitude', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('longitude', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('type', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('gender', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('start_age', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('end_age', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('person_limit', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('status', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('party_amenitie_id', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('phone_number', '', 'required', array('required' => '%s'));
        
        
        if(!empty(check_comma_separated_value($this->input->post('gender'))))
            {
                 $this->response([
                    $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name') => 'Invailed gender in comma separated value',
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }

        /*---------------------Comma separated validation----------------*/
          if(!empty(check_comma_separated_value($this->input->post('party_amenitie_id'))))
            {
                 $this->response([
                    $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name') => 'Invailed party amenitie in comma separated value',
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
        if ($this->form_validation->run() == true) {
            if (strtotime($this->input->post('end_date')) < strtotime($this->input->post('start_date'))) {
                $this->response([
                    $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name') => 'End Date Cannot Be Less Than Start Date',
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            } elseif (strtotime($this->input->post('end_date')) == strtotime($this->input->post('start_date'))) {
                if ($this->input->post('end_time') < $this->input->post('start_time')) {
                    $this->response([
                        $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                        $this->config->item('rest_message_field_name') => 'End Time Cannot Be Less Than Start Time',
                            ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
                }
            } elseif ($this->input->post('start_age') > $this->input->post('end_age')) {
                $this->response([
                    $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name') => 'End Age Cannot Be Less Than Start Age',
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            } 
            $organization_id = $this->input->post('organization_id');
            $data = [
                'user_id'               => $authorized_user['account']->id,
                'title'                 => $this->input->post('title'),
                'phone_number'                 => $this->input->post('phone_number'),
                'description'           => $this->input->post('description'),
                'start_date'            => strtotime($this->input->post('start_date')),
                'end_date'              => strtotime($this->input->post('end_date')),
                'start_time'            => $this->input->post('start_time'),
                'organization_id'       => !empty($organization_id) ? $organization_id : "0",
                'end_time'              => $this->input->post('end_time'),
                'latitude'              => $this->input->post('latitude'),
                'longitude'             => $this->input->post('longitude'),
                'type'                  => $this->input->post('type'),
                'gender'                => $this->input->post('gender'),
                'start_age'             => $this->input->post('start_age'),
                'end_age'               => $this->input->post('end_age'),
                'person_limit'          => $this->input->post('person_limit'),
                'status'                => $this->input->post('status'),
                'party_amenitie_id'     => $this->input->post('party_amenitie_id'),
                'offers'     => $this->input->post('offers'),
                'ladies'     => $this->input->post('ladies'),
                'stag'     => $this->input->post('stag'),
                'couples'     => $this->input->post('couples'),
                'others'     => $this->input->post('others'),
                'updated_at'            => date('Y-m-d H:i:s'),
                'cover_photo'=> $this->input->post('cover_photo'),
                'approval_status'=>'0'

            ];
            /*if (empty($_FILES['cover_photo']['name'])) {
                $this->form_validation->set_rules('cover_photo', 'cover_photo', 'required', array('required' => '%s'));
                $this->response([
                    $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name') => 'Empty request parameter(s) [ cover_photo ]',
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            } else {
                $get_data = $this->party_model->get_party_by_id($this->input->post('party_id'));
                if (!empty($get_data)) {
                    if (is_file($get_data->cover_photo)) {
                        unlink($get_data->cover_photo);
                    }
                } else {
                    $this->response([
                        $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                        $this->config->item('rest_message_field_name')  => $this->lang->line('update_failed_party_message')
                            ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
                }
                $file_name = 'party_' . time() . rand(100, 999);
                $config = [
                    'upload_path' => './upload/party/',
                    'file_name' => $file_name,
                    'allowed_types' => 'png|jpg|jpeg',
                    'max_size' => 50480,
                    'max_width' => 20480,
                    'max_height' => 20480,
                    'file_ext_tolower' => TRUE,
                    'remove_spaces' => TRUE,
                ];
                $this->load->library('upload/', $config);
                if ($this->upload->do_upload('cover_photo')) {
                    $uploadData = $this->upload->data();
                    $data['cover_photo'] = 'upload/party/' . $uploadData['file_name'];
                } else {
                    $this->response([
                        $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                        $this->config->item('rest_message_field_name')  => $this->upload->display_errors()
                            ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
                }
            }*/
            $update_data = $this->party_model->update_party($data, $this->input->post('party_id'));
            if($update_data) {
                /*-------------Create Notification------------*/
                  $noti_arr=array('notification_title'=>'Party Post','notification_message'=>'Congratulations! your party is posted and its under review.','notification_type'=>2,'notification_type_name'=>'Party Post','user_id'=>$authorized_user['account']->id);
                  $this->general_model->insert('notifications',$noti_arr);
                /*--------------------------------------------*/
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('update_success_party_message')
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            } else {
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('update_failed_party_message')
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
        } else {
            $this->response([
            $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
            $this->config->item('rest_message_field_name') => 'Empty request parameter(s). [ ' . ltrim(str_replace("\n", '', validation_errors()), ' |') . ' ]'
                ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }

    public function add_organization_post() {
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

        //$this->form_validation->set_rules('city_id', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('name', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('description', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('latitude', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('longitude', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('type', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('organization_amenitie_id', '', 'required', array('required' => '%s'));

        if ($this->form_validation->run() == true) {
            $data = [
                'user_id'               => $authorized_user['account']->id,
                'city_id'               => @$this->input->post('city_id'),
                'name'                 => $this->input->post('name'),
                'description'           => $this->input->post('description'),
                'branch'=> $this->input->post('branch'),
                'latitude'              => $this->input->post('latitude'),
                'longitude'             => $this->input->post('longitude'),
                'type'                  => $this->input->post('type'),
                 'org_amenitie_id'       => $this->input->post('organization_amenitie_id'),
                'created_at'            => date('Y-m-d H:i:s'),
                'profile_pic'                  => $this->input->post('profile_pic'),
                'timeline_pic'                  => $this->input->post('timeline_pic')

            ];

            /* $profile_pic_name = 'org_profile_pic_' . time() . rand(100, 999);
             $timeline_pic_name = 'org_timeline_pic_' . time() . rand(100, 999);
                $config = [
                    'upload_path' => './upload/organization/',
                    'file_name' => $profile_pic_name,
                    'allowed_types' => 'png|jpg|jpeg',
                    'max_size' => 50480,
                    'max_width' => 20480,
                    'max_height' => 20480,
                    'file_ext_tolower' => TRUE,
                    'remove_spaces' => TRUE,
                ];
                $this->load->library('upload/', $config);
                if ($this->upload->do_upload('profile_pic')) {
                    $uploadData = $this->upload->data();
                    $data['profile_pic'] = 'upload/organization/' . $uploadData['file_name'];
                }
              $config2 = [
                    'upload_path' => './upload/organization/',
                    'file_name' => $timeline_pic_name,
                    'allowed_types' => 'png|jpg|jpeg',
                    'max_size' => 50480,
                    'max_width' => 20480,
                    'max_height' => 20480,
                    'file_ext_tolower' => TRUE,
                    'remove_spaces' => TRUE,
                ];
                $this->load->library('upload/', $config2);
                if ($this->upload->do_upload('timeline_pic')) {
                   $uploadData = $this->upload->data();
                    $data['timeline_pic'] = 'upload/organization/' . $uploadData['file_name'];
                }*/
               // print_r($data); die;
            $insert_data = $this->party_model->insert_organization($data);
            if($insert_data) {
                  /*-------------Create Notification------------*/
                  $noti_arr=array('notification_title'=>'Organization Registration ','notification_message'=>'Welcome aboard! Your registration is complete and your profile is under review.','notification_type'=>1,'notification_type_name'=>'Organization Registration','user_id'=>$authorized_user['account']->id);
                  $this->general_model->insert('notifications',$noti_arr);
                /*--------------------------------------------*/
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('insert_success_organization_message')
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            } else {
                $this->response([
                    $this->config->item('rest_status_field_name')   =>$this->config->item('rest_status_code_two'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('insert_failed_organization_message')
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
        } else {
            $this->response([
            $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
            $this->config->item('rest_message_field_name') => 'Empty request parameter(s). [ ' . ltrim(str_replace("\n", '', validation_errors()), ' |') . ' ]'
                ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }

    public function organization_details_post() {
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
        //print_r($authorized_user['account']->id); die;
        //$this->form_validation->set_rules('organization_id', '', 'required', array('required' => '%s'));
        //if ($this->form_validation->run() == true) {
            $data = $this->party_model->get_organization_by_id($authorized_user['account']->id);
            if ($data) {
                $res = $this->party_model->get_notification_count($authorized_user['account']->id);
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('get_success_organization_message'),
                    'notification_count'=>$res,
                    'user_phone_number'=>$authorized_user['account']->phone,
                    $this->config->item('rest_data_field_name') => $data
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            } else {
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('get_failed_organization_data'),

                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
      /* } else {
            $this->response([
            $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
            $this->config->item('rest_message_field_name') => 'Empty request parameter(s). [ ' . ltrim(str_replace("\n", '', validation_errors()), ' |') . ' ]'
                ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }*/
    }

    public function update_organization_post() {
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

        $this->form_validation->set_rules('organization_id', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('city_id', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('name', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('description', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('latitude', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('longitude', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('type', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('organization_amenitie_id', '', 'required', array('required' => '%s'));
        if ($this->form_validation->run() == true) {
                //echo $authorized_user['account']->id; die;

            $data = [
                'user_id'               => $authorized_user['account']->id,
                'city_id'               => $this->input->post('city_id'),
                'name'                 => $this->input->post('name'),
                'branch'=> $this->input->post('branch'),
                'description'           => $this->input->post('description'),
                'latitude'              => $this->input->post('latitude'),
                'longitude'             => $this->input->post('longitude'),
                'type'                  => $this->input->post('type'),
                'org_amenitie_id'       => $this->input->post('organization_amenitie_id'),
                'updated_at'            => date('Y-m-d H:i:s'),
                'profile_pic'                  => $this->input->post('profile_pic'),

                'timeline_pic'                  => $this->input->post('timeline_pic')

            ];
             
             /* $profile_pic_name = 'org_profile_pic_' . time() . rand(100, 999);
             $timeline_pic_name = 'org_timeline_pic_' . time() . rand(100, 999);
                $config = [
                    'upload_path' => './upload/organization/',
                    'file_name' => $profile_pic_name,
                    'allowed_types' => 'png|jpg|jpeg',
                    'max_size' => 50480,
                    'max_width' => 20480,
                    'max_height' => 20480,
                    'file_ext_tolower' => TRUE,
                    'remove_spaces' => TRUE,
                ];
                $this->load->library('upload/', $config);
                if ($this->upload->do_upload('profile_pic')) {
                    $uploadData = $this->upload->data();
                    $data['profile_pic'] = 'upload/organization/' . $uploadData['file_name'];
                }
              $config2 = [
                    'upload_path' => './upload/organization/',
                    'file_name' => $timeline_pic_name,
                    'allowed_types' => 'png|jpg|jpeg',
                    'max_size' => 50480,
                    'max_width' => 20480,
                    'max_height' => 20480,
                    'file_ext_tolower' => TRUE,
                    'remove_spaces' => TRUE,
                ];
                $this->load->library('upload/', $config2);
                if ($this->upload->do_upload('timeline_pic')) {
                   $uploadData = $this->upload->data();
                    $data['timeline_pic'] = 'upload/organization/' . $uploadData['file_name'];
                }*/

            $update_data = $this->party_model->update_organization($data, $this->input->post('organization_id'));
            if($update_data) {
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('update_success_organization_message')
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            } else {
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('update_failed_organization_message')
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
        } else {
            $this->response([
            $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
            $this->config->item('rest_message_field_name') => 'Empty request parameter(s). [ ' . ltrim(str_replace("\n", '', validation_errors()), ' |') . ' ]'
                ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }

    public function get_user_all_individual_party_get() {
        /* Check Authentications */
        $headers = $this->input->request_headers();
        
        $authorized_user = $this->general_model->check_authorization($headers);

        if ($authorized_user['status'] != 1) {
            $this->response([
                $this->config->item('rest_status_field_name') => $authorized_user['status'],
                $this->config->item('rest_message_field_name') => $authorized_user['message']
            ], REST_Controller::HTTP_OK);
        }

        $data = $this->party_model->get_user_all_individual_party($authorized_user['account']->id);
        if($data) {
             foreach ($data as $key => $value) {
                $data[$key]->start_time=date('h:i A', strtotime($value->start_time));
                 $data[$key]->end_time=date('h:i A', strtotime($value->end_time));
               }
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('success_party_found_message'),
                $this->config->item('rest_data_field_name')     => $data
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('failed_party_found_message')
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }

    public function get_user_organization_party_by_id_post() {
        /* Check Authentications */
        $headers = $this->input->request_headers();
        
        $authorized_user = $this->general_model->check_authorization($headers);

        if ($authorized_user['status'] != 1) {
            $this->response([
                $this->config->item('rest_status_field_name') => $authorized_user['status'],
                $this->config->item('rest_message_field_name') => $authorized_user['message']
            ], REST_Controller::HTTP_OK);
        }
        $this->form_validation->set_rules('organization_id', '', 'required', array('required' => '%s'));
          $this->form_validation->set_rules('status', '', 'required', array('required' => '%s'));
        if ($this->form_validation->run() == true) {
            $data = $this->party_model->get_user_organization_party_by_id($authorized_user['account']->id, $this->input->post('organization_id'),$this->input->post('status'));
            if($data) {

                foreach ($data as $key => $value) {
                    $data[$key]->start_date = date('Y-m-d', $value->start_date);
                    $data[$key]->end_date = date('Y-m-d', $value->end_date);
                     $data[$key]->start_time=date('h:i A', strtotime($value->start_time));
                     $data[$key]->end_time=date('h:i A', strtotime($value->end_time));
                }
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('success_organization_party_found_message'),
                    $this->config->item('rest_data_field_name')     => $data
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            } else {
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('failed_organization_party_found_message')
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
        } else {
            $this->response([
            $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
            $this->config->item('rest_message_field_name') => 'Empty request parameter(s). [ ' . ltrim(str_replace("\n", '', validation_errors()), ' |') . ' ]'
                ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }

    public function party_type_get() {
        /* Check Authentications */
        $headers = $this->input->request_headers();
        
        $authorized_user = $this->general_model->check_authorization($headers);

        if ($authorized_user['status'] != 1) {
            $this->response([
                $this->config->item('rest_status_field_name') => $authorized_user['status'],
                $this->config->item('rest_message_field_name') => $authorized_user['message']
            ], REST_Controller::HTTP_OK);
        }

        $data = $this->party_model->party_type();
        if($data) {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('success_party_type_message'),
                $this->config->item('rest_data_field_name')     => $data
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('failed_party_type_message')
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }

    public function cities_get() {
        /* Check Authentications */
        $headers = $this->input->request_headers();
        
        $authorized_user = $this->general_model->check_authorization($headers);

        if ($authorized_user['status'] != 1) {
            $this->response([
                $this->config->item('rest_status_field_name') => $authorized_user['status'],
                $this->config->item('rest_message_field_name') => $authorized_user['message']
            ], REST_Controller::HTTP_OK);
        }

        $data = $this->party_model->cities();
        if($data) {
            
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('success_cities_message'),
                $this->config->item('rest_data_field_name')     => $data
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('failed_cities_message')
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }

    public function join_party_post() {
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
        if ($this->form_validation->run() == true) {
            $check_join = $this->party_model->get_joined_details($authorized_user['account']->id, $this->input->post('party_id'));
            if ($check_join) {
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('join_already_joined_message')
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
            $party = $this->party_model->get_party_by_id($this->input->post('party_id'));
            $user = $this->party_model->get_user_details($authorized_user['account']->id);
            if (!empty($party) && !empty($user)) {
                $genders = explode(',', $party->gender);
                if (!(in_array('3', $genders)) ) {
                    if (!(in_array($user->gender_id, $genders)) ) {
                        $this->response([
                            $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                            $this->config->item('rest_message_field_name')  => $this->lang->line('gender_failed_message')
                                ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
                    }
                }
                if (! (intval($user->age) >= $party->start_age && intval($user->age) <= $party->end_age) ) {
                    $this->response([
                        $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                        $this->config->item('rest_message_field_name')  => $this->lang->line('age_failed_message')
                            ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
                }
                $join_user = $this->party_model->get_join_users($this->input->post('party_id'));
                if (! (intval($party->person_limit) > $join_user) ) {
                    $this->response([
                        $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                        $this->config->item('rest_message_field_name')  => $this->lang->line('party_failed_message')
                            ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
                }

                $join_party = $this->party_model->join_party($authorized_user['account']->id, $this->input->post('party_id'));
                if($join_party) {
                    $this->response([
                        $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                        $this->config->item('rest_message_field_name')  => $this->lang->line('join_success_message')
                            ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
                } else {
                    $this->response([
                        $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                        $this->config->item('rest_message_field_name')  => $this->lang->line('join_failed_message')
                            ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
                }
            } else {
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('user_or_party_failed_message')
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
        } else {
            $this->response([
            $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
            $this->config->item('rest_message_field_name') => 'Empty request parameter(s). [ ' . ltrim(str_replace("\n", '', validation_errors()), ' |') . ' ]'
                ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }

    public function subscriptions_get() {
        /* Check Authentications */
        $headers = $this->input->request_headers();
        
        $authorized_user = $this->general_model->check_authorization($headers);

        if ($authorized_user['status'] != 1) {
            $this->response([
                $this->config->item('rest_status_field_name') => $authorized_user['status'],
                $this->config->item('rest_message_field_name') => $authorized_user['message']
            ], REST_Controller::HTTP_OK);
        }

        $host_subscriptions = $this->party_model->host_subscriptions();
        $view_subscriptions = $this->party_model->view_subscriptions();
        if(!empty($host_subscriptions) || !empty($view_subscriptions)) {
             $arr=array('post_subscriptions'=>$host_subscriptions,'view_subscriptions'=>$view_subscriptions);
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('success_subscription_message'),
                $this->config->item('rest_data_field_name')     => $arr
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('failed_subscription_message')
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }
 /*---------------------MR-08-02-2023---------------*/
public function web_party_get() { 
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        $data = $this->party_model->party();
        if($data) {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('success_party_message'),
                $this->config->item('rest_data_field_name')     => $data
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('failed_party_message')
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }

            public function organization_amenities_get() {
        /* Check Authentications */
        $headers = $this->input->request_headers();
        
        $authorized_user = $this->general_model->check_authorization($headers);

        if ($authorized_user['status'] != 1) {
            $this->response([
                $this->config->item('rest_status_field_name') => $authorized_user['status'],
                $this->config->item('rest_message_field_name') => $authorized_user['message']
            ], REST_Controller::HTTP_OK);
        }

        $data = $this->party_model->organization_amenities();
        if($data) {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('success_org_amenitie_message'),
                $this->config->item('rest_data_field_name')     => $data
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('failed_org_amenitie_message')
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }

      public function party_amenities_get() {
        /* Check Authentications */
        $headers = $this->input->request_headers();
        
        $authorized_user = $this->general_model->check_authorization($headers);

        if ($authorized_user['status'] != 1) {
            $this->response([
                $this->config->item('rest_status_field_name') => $authorized_user['status'],
                $this->config->item('rest_message_field_name') => $authorized_user['message']
            ], REST_Controller::HTTP_OK);
        }

        $data = $this->party_model->party_amenities();
        if($data) {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('success_party_amenities_message'),
                $this->config->item('rest_data_field_name')     => $data
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('failed_party_amenities_message')
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }

     /*---------------------MR-08-02-2023---------------*/
public function web_regular_party_get() { 
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        $data = $this->party_model->web_regular_party();
        if($data) {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('success_party_message'),
                $this->config->item('rest_data_field_name')     => $data
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('failed_party_message')
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }


     public function organisation_dashboard_status_post() {
        /* Check Authentications */
        $headers = $this->input->request_headers();
        
        $authorized_user = $this->general_model->check_authorization($headers);

        if ($authorized_user['status'] != 1) {
            $this->response([
                $this->config->item('rest_status_field_name') => $authorized_user['status'],
                $this->config->item('rest_message_field_name') => $authorized_user['message']
            ], REST_Controller::HTTP_OK);
        }
        $this->form_validation->set_rules('name', '', 'required', array('required' => '%s'));
        if($this->form_validation->run() == true) {
        $name = $this->input->post('name');
        $data = $this->party_model->get_organisation($name);
        if($data) {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                //$this->config->item('rest_message_field_name')  => $this->lang->line('success_party_amenitie_message'),
                $this->config->item('rest_message_field_name')     =>'Organization data already exists'
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name')  => 'Organization data not exists'
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }

     }else{
            $this->response([
            $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
            $this->config->item('rest_message_field_name') => 'Empty request parameter(s). [ ' . ltrim(str_replace("\n", '', validation_errors()), ' |') . ' ]'
                ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    } 

    public function papular_and_regular_party_post() { 
    /* Check Authentications */
        $headers = $this->input->request_headers();
        
        $authorized_user = $this->general_model->check_authorization($headers);

        if ($authorized_user['status'] != 1) {
            $this->response([
                $this->config->item('rest_status_field_name') => $authorized_user['status'],
                $this->config->item('rest_message_field_name') => $authorized_user['message']
            ], REST_Controller::HTTP_OK);
        }
        $this->form_validation->set_rules('party_type', '', 'required', array('required' => '%s'));
        if($this->form_validation->run() == true) {
        $data = $this->party_model->regular_papular_party_get($this->input->post('party_type'));
        if($data) {
             foreach ($data as $key => $value) {
                $data[$key]->start_time=date('h:i A', strtotime($value->start_time));
                 $data[$key]->end_time=date('h:i A', strtotime($value->end_time));
               }
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('success_party_message'),
                $this->config->item('rest_data_field_name')     => $data
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('failed_party_message')
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }

    }else{
            $this->response([
            $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
            $this->config->item('rest_message_field_name') => 'Empty request parameter(s). [ ' . ltrim(str_replace("\n", '', validation_errors()), ' |') . ' ]'
                ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }

        public function organization_pdf_post() { 
    /* Check Authentications */
        $headers = $this->input->request_headers();
        
        $authorized_user = $this->general_model->check_authorization($headers);

        if ($authorized_user['status'] != 1) {
            $this->response([
                $this->config->item('rest_status_field_name') => $authorized_user['status'],
                $this->config->item('rest_message_field_name') => $authorized_user['message']
            ], REST_Controller::HTTP_OK);
        }
    
        $data = $this->party_model->organization_pdf_get();
        if($data) {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('success_organization_pdf_message'),
                $this->config->item('rest_data_field_name')     => $data[0]
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('failed_organization_pdf_message')
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }

  
    }



    public function organization_pdf_details_post() {
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

        $this->form_validation->set_rules('organization_id', '', 'required', array('required' => '%s'));
        if ($this->form_validation->run() == true) {
            $data = $this->party_model->get_organization_pdf_by_id($this->input->post('organization_id'),$authorized_user['account']->id);
            if ($data) {
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('get_success_organization_message'),
                    $this->config->item('rest_data_field_name') => $data
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            } else {
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('get_failed_organization_data')
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
        } else {
            $this->response([
            $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
            $this->config->item('rest_message_field_name') => 'Empty request parameter(s). [ ' . ltrim(str_replace("\n", '', validation_errors()), ' |') . ' ]'
                ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }

 public function update_organization_pdf_post() {
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
        $data=array();
        $this->form_validation->set_rules('organization_id', '', 'required', array('required' => '%s'));
        if(empty($_FILES['pdf_a']['name']))
        {
             $this->form_validation->set_rules('pdf_a', '', 'required', array('required' => '%s'));
        }
          if(empty($_FILES['pdf_b']['name']))
        {
             $this->form_validation->set_rules('pdf_b', '', 'required', array('required' => '%s'));
        }
        if ($this->form_validation->run() == true) {
            $data = [
                'user_id'               => $authorized_user['account']->id,
            ];
            $res = $this->party_model->get_organization_pdf_by_id($this->input->post('organization_id'),$authorized_user['account']->id);

            if (!empty($res)) {
                 $pdf_a = 'org_pdf_a_'.time().rand(100, 999);
              $pdf_b = 'org_pdf_b_'.time().rand(100, 999);
                $config = [
                    'upload_path' => './upload/organization/',
                    'file_name' => $pdf_a,
                    'allowed_types' => 'pdf',
                    'remove_spaces' => TRUE,
                ];
                $this->load->library('upload/', $config);
                if ($this->upload->do_upload('pdf_a')) {
                    $uploadData = $this->upload->data();
                    $data['pdf_a'] = 'upload/organization/' . $uploadData['file_name'];
                }
              $config2 = [
                    'upload_path' => './upload/organization/',
                    'file_name' => $pdf_b,
                    'allowed_types' => 'pdf',
                    'remove_spaces' => TRUE,
                ];
                $this->load->library('upload/', $config2);
                if ($this->upload->do_upload('pdf_b')) {
                   $uploadData = $this->upload->data();
                    $data['pdf_b'] = 'upload/organization/' . $uploadData['file_name'];
                }
                 $data['updated_at']=date('Y-m-d H:i:s');
                 unset($data['user_id']);
                 $res_data = $this->party_model->update_organization_pdf($data,$authorized_user['account']->id,$this->input->post('organization_id'));
               }else{
                   $pdf_a = 'org_pdf_a_'.time().rand(100, 999);
              $pdf_b = 'org_pdf_b_'.time().rand(100, 999);
                $config = [
                    'upload_path' => './upload/organization/',
                    'file_name' => $pdf_a,
                    'allowed_types' => 'pdf',
                    'remove_spaces' => TRUE,
                ];
                $this->load->library('upload/', $config);
                if ($this->upload->do_upload('pdf_a')) {
                    $uploadData = $this->upload->data();
                    $data['pdf_a'] = 'upload/organization/' . $uploadData['file_name'];
                }
              $config2 = [
                    'upload_path' => './upload/organization/',
                    'file_name' => $pdf_b,
                    'allowed_types' => 'pdf',
                    'remove_spaces' => TRUE,
                ];
                $this->load->library('upload/', $config2);
                if ($this->upload->do_upload('pdf_b')) {
                   $uploadData = $this->upload->data();
                    $data['pdf_b'] = 'upload/organization/' . $uploadData['file_name'];
                }
                  $data['created_at']= date('Y-m-d H:i:s');
                  $data['organization_id']= $this->input->post('organization_id');
                  $res_data = $this->party_model->insert_organization_pdf($data);
               }
            if($res_data) {
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('success_org_add_pdf_message')
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            } else {
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('failed_org_add_message')
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
        } else {
            $this->response([
            $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
            $this->config->item('rest_message_field_name') => 'Empty request parameter(s). [ ' . ltrim(str_replace("\n", '', validation_errors()), ' |') . ' ]'
                ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }

        public function notification_read_status_update_post() { 
    /* Check Authentications */
        $headers = $this->input->request_headers();
        
        $authorized_user = $this->general_model->check_authorization($headers);

        if ($authorized_user['status'] != 1) {
            $this->response([
                $this->config->item('rest_status_field_name') => $authorized_user['status'],
                $this->config->item('rest_message_field_name') => $authorized_user['message']
            ], REST_Controller::HTTP_OK);
        }
    
        $data = $this->party_model->notification_read_status_update($authorized_user['account']->id);
        if($data) {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('notification_read_status_update_success')
               
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('notification_read_status_update_failed')
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }

  
    }

public function delete_organization_post() {
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

        $this->form_validation->set_rules('organization_id', '', 'required', array('required' => '%s'));
        if ($this->form_validation->run() == true) {
                //echo $authorized_user['account']->id; die;

            $data = [
                //'user_id'               => $authorized_user['account']->id,
                'is_deleted'               => '1'
            ];
             
            $delete_data = $this->party_model->delete_organization($data, $this->input->post('organization_id'));
            if($delete_data) {
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('delete_success_organization_message')
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            } else {
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('delete_failed_organization_message')
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
        } else {
            $this->response([
            $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
            $this->config->item('rest_message_field_name') => 'Empty request parameter(s). [ ' . ltrim(str_replace("\n", '', validation_errors()), ' |') . ' ]'
                ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }

     public function party_history_post() { 
    /* Check Authentications */
        $headers = $this->input->request_headers();
        
        $authorized_user = $this->general_model->check_authorization($headers);

        if ($authorized_user['status'] != 1) {
            $this->response([
                $this->config->item('rest_status_field_name') => $authorized_user['status'],
                $this->config->item('rest_message_field_name') => $authorized_user['message']
            ], REST_Controller::HTTP_OK);
        }
        $this->form_validation->set_rules('organization_id', '', 'required', array('required' => '%s'));
        if($this->form_validation->run() == true) {
            
        $data = $this->general_model->getAll('party',array('user_id'=>$authorized_user['account']->id,'organization_id'=>$this->input->post('organization_id'),'is_deleted'=>'0','active'=>'1'));
        if($data) {
             foreach ($data as $key => $value) {
                $data[$key]->start_time=date('h:i A', strtotime($value->start_time));
                 $data[$key]->end_time=date('h:i A', strtotime($value->end_time));
               }
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('success_party_message'),
                $this->config->item('rest_data_field_name')     => $data
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('failed_party_message')
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }

    }else{
            $this->response([
            $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
            $this->config->item('rest_message_field_name') => 'Empty request parameter(s). [ ' . ltrim(str_replace("\n", '', validation_errors()), ' |') . ' ]'
                ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }                 

}
