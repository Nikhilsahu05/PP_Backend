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

        $banners = $this->home_model->get_all_banners();

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
        $this->form_validation->set_rules('party_immunity_id', '', 'required', array('required' => '%s'));

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
                // 'user_id'               => $authorized_user['account']->id,
                'user_id'               => 1,
                'organization_id'       => $this->input->post('organization_id'),
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
                'party_immunity_id'     => $this->input->post('party_immunity_id'),
                'created_at'            => date('Y-m-d H:i:s')
            ];
            if (empty($_FILES['cover_photo']['name'])) {
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
            }

            $insert_data = $this->party_model->insert_party($data);
            if($insert_data) {
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
        $this->form_validation->set_rules('party_immunity_id', '', 'required', array('required' => '%s'));
        
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
                'party_immunity_id'     => $this->input->post('party_immunity_id'),
                'updated_at'            => date('Y-m-d H:i:s')
            ];
            if (empty($_FILES['cover_photo']['name'])) {
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
            }
            $update_data = $this->party_model->update_party($data, $this->input->post('party_id'));
            if($update_data) {
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

        $this->form_validation->set_rules('city_id', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('name', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('description', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('latitude', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('longitude', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('type', '', 'required', array('required' => '%s'));
        $this->form_validation->set_rules('organization_immunity_id', '', 'required', array('required' => '%s'));

        if ($this->form_validation->run() == true) {
            $data = [
                'user_id'               => $authorized_user['account']->id,
                'city_id'               => $this->input->post('city_id'),
                'name'                 => $this->input->post('name'),
                'description'           => $this->input->post('description'),
                'latitude'              => $this->input->post('latitude'),
                'longitude'             => $this->input->post('longitude'),
                'type'                  => $this->input->post('type'),
                 'org_immunity_id'       => $this->input->post('organization_immunity_id'),
                'created_at'            => date('Y-m-d H:i:s')
            ];

            $insert_data = $this->party_model->insert_organization($data);
            if($insert_data) {
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('insert_success_organization_message')
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            } else {
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
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

    public function edit_organization_post() {
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
            $data = $this->party_model->get_organization_by_id($this->input->post('organization_id'));
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
        $this->form_validation->set_rules('organization_immunity_id', '', 'required', array('required' => '%s'));
        if ($this->form_validation->run() == true) {
            $data = [
                'user_id'               => $authorized_user['account']->id,
                'city_id'               => $this->input->post('city_id'),
                'name'                 => $this->input->post('name'),
                'description'           => $this->input->post('description'),
                'latitude'              => $this->input->post('latitude'),
                'longitude'             => $this->input->post('longitude'),
                'type'                  => $this->input->post('type'),
                'org_immunity_id'       => $this->input->post('organization_immunity_id'),
                'updated_at'            => date('Y-m-d H:i:s')
            ];
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
        if ($this->form_validation->run() == true) {
            $data = $this->party_model->get_user_organization_party_by_id($authorized_user['account']->id, $this->input->post('organization_id'));
            if($data) {
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
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('failed_subscription_message')
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }
 /*---------------------MR-08-02-2023---------------*/
public function web_party_get() { 
    
        $data = $this->party_model->party();
        if($data) {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('success_party_message'),
                $this->config->item('rest_data_field_name')     => $data
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('failed_party_message')
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }

            public function organization_immunity_get() {
        /* Check Authentications */
        $headers = $this->input->request_headers();
        
        $authorized_user = $this->general_model->check_authorization($headers);

        if ($authorized_user['status'] != 1) {
            $this->response([
                $this->config->item('rest_status_field_name') => $authorized_user['status'],
                $this->config->item('rest_message_field_name') => $authorized_user['message']
            ], REST_Controller::HTTP_OK);
        }

        $data = $this->party_model->organization_immunity();
        if($data) {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('success_org_immunity_message'),
                $this->config->item('rest_data_field_name')     => $data
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('failed_org_immunity_message')
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }

      public function party_immunity_get() {
        /* Check Authentications */
        $headers = $this->input->request_headers();
        
        $authorized_user = $this->general_model->check_authorization($headers);

        if ($authorized_user['status'] != 1) {
            $this->response([
                $this->config->item('rest_status_field_name') => $authorized_user['status'],
                $this->config->item('rest_message_field_name') => $authorized_user['message']
            ], REST_Controller::HTTP_OK);
        }

        $data = $this->party_model->party_immunity();
        if($data) {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('success_party_immunity_message'),
                $this->config->item('rest_data_field_name')     => $data
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('failed_pary_immunity_message')
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }      

}
