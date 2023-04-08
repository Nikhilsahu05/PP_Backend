<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

/**
 * Class Account
 * Create class for account handling
*/
class Account extends REST_Controller {

    public function __construct() {
        parent::__construct();
        /* Load :: Helper */
        $this->lang->load('auth');
        $this->lang->load('API/account');
        /* Load :: Models */
        $this->load->model('v1/account_model');

        $this->form_validation->set_error_delimiters(' | ', '');
    }

    /**
     * User login
     * Method (POST)
     */
    
    
    public function login_post() {

        $this->form_validation->set_rules('phone', '', 'required', array('required' => '%s'));

        if ($this->form_validation->run() == true) {
            
            $phone = $this->input->post('phone');
            $login = $this->ion_auth->phone_login_api($phone);
        
            if ($login['status'] == 1) {
                // create token data
                $token_create = (object) [
                    'id' => (int) $login['data']->id,                  
                    'iat' => now()
                ];
                // Generate token
                $token_data = AUTHORIZATION::generateToken($token_create);

                $data = [
                    'user_id'           => (int) $login['data']->id,
                    'email'             => !empty($login['data']->email) ? $login['data']->email : "",
                    'full_name'         => !empty($login['data']->first_name) ? $login['data']->first_name : "",
                    'country_code'      => !empty($login['data']->first_name) ? $login['data']->country_code : "",
                    'profile_picture'   => !empty($login['data']->profile_picture) ? base_url($login['data']->profile_picture) : "",
                    'phone'             => $login['data']->phone,
                    'is_verified_phone' => (int) $login['data']->is_verified_phone,
                    'first_time' => (int) $login['data']->first_time,
                     'otp'               => $login['data']->otp,
                    'unique_id'         => $login['data']->unique_id,
                    'token'             => $token_data
                ];
                
                $this->response([
                    $this->config->item('rest_status_field_name')   => $login['status'],
                    $this->config->item('rest_message_field_name')  => $login['message'],
                    $this->config->item('rest_data_field_name')     => $data
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            } else {
                $this->response([
                $this->config->item('rest_status_field_name') => $login['status'],
                $this->config->item('rest_message_field_name') => $login['message'],
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
        } else {
            $this->response([
                $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name') => 'Empty request parameter(s). [ ' . ltrim(str_replace("\n", '', validation_errors()), ' |') . ' ]'
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }
    
    // public function login_post() {

    //     $this->form_validation->set_rules('email', '', 'required', array('required' => '%s'));
    //     $this->form_validation->set_rules('password', '', 'required', array('required' => '%s'));

    //     if ($this->form_validation->run() == true) {
    //         $identity = $this->input->post('email');
    //         $password = $this->input->post('password');

    //         $login = $this->ion_auth->user_login_api($identity, $password);

    //         if ($login['status'] == 1) {
    //             // create token data
    //             $token_create = (object) [
    //                 'id' => (int) $login['data']->id,                  
    //                 'iat' => now()
    //             ];
    //             // Generate token
    //             $token_data = AUTHORIZATION::generateToken($token_create);

    //             $data = [
    //                 'user_id'           => (int) $login['data']->id,
    //                 'email'             => $login['data']->email,
    //                 'full_name'         => $login['data']->first_name,
    //                 'country_code'      => !empty($login['data']->country_code) ? $login['data']->country_code : "",
    //                 'profile_picture'   => !empty($login['data']->profile_picture) ? base_url($login['data']->profile_picture) : "",
    //                 'phone'             => $login['data']->phone,
    //                 'is_verified_phone' => (int) $login['data']->is_verified_phone,
    //                 'unique_id'         => $login['data']->unique_id,
    //                 'token'             => $token_data
    //             ];
                
    //             $this->response([
    //                 $this->config->item('rest_status_field_name')   => $login['status'],
    //                 $this->config->item('rest_message_field_name')  => $login['message'],
    //                 $this->config->item('rest_data_field_name')     => $data
    //                     ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    //         } else if($login['status'] == 2){

    //             $data_status = [
    //                 'user_id' => (int) $login['data']->id,
    //                 'email' => $login['data']->email,
    //                 'full_name' => $login['data']->first_name,
    //                 'country_code' => !empty($login['data']->country_code) ? $login['data']->country_code : "",
    //                 'profile_picture' => !empty($login['data']->profile_picture) ? base_url($login['data']->profile_picture) : "",
    //                 'phone' => $login['data']->phone,
    //                 'unique_id' => $login['data']->unique_id,
    //                 'is_verified_phone' => (int) $login['data']->is_verified_phone
    //             ];

    //             $this->response([
    //                 $this->config->item('rest_status_field_name')   => $login['status'],
    //                 $this->config->item('rest_message_field_name')  => $login['message'],
    //                 $this->config->item('rest_data_field_name')     => $data_status
    //             ], REST_Controller::HTTP_OK);

    //         }else {
    //             $this->response([
    //             $this->config->item('rest_status_field_name') => $login['status'],
    //             $this->config->item('rest_message_field_name') => $login['message'],
    //                 ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    //         }
    //     } else {
    //         $this->response([
    //             $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
    //             $this->config->item('rest_message_field_name') => 'Empty request parameter(s). [ ' . ltrim(str_replace("\n", '', validation_errors()), ' |') . ' ]'
    //                 ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    //     }
    // }
    
    public function social_signup_post() {

        $this->form_validation->set_rules('social_id', '', 'required');
        $this->form_validation->set_rules('type', '', 'required');

        if ($this->form_validation->run() == true) {
            $social = $this->ion_auth->login_social($this->input->post('social_id'));
            if ($social['success'] == 1) {

                if ($social['data']->is_deleted == 1) {
                    $this->response([
                        $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                        $this->config->item('rest_message_field_name') => $this->lang->line('social_signup_account_not_exist')
                    ], REST_Controller::HTTP_OK);
                }

                // create token data
                $token_create = (object) [
                    'id' => (int) $social['data']->id,                  
                    'iat' => now()
                ];
                // Generate token
                $token_data = AUTHORIZATION::generateToken($token_create);

                $responseArray = ([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('login_successful'),
                    $this->config->item('rest_data_field_name')     => [
                        'user_id'           => (int) $social['data']->id,
                        'full_name'         => !empty($social['data']->first_name) ? $social['data']->first_name : "",
                        'email'             => !empty($social['data']->email) ? $social['data']->email  : "",
                        'profile_picture'   => (!empty($social['data']->profile_picture) ? $social['data']->profile_picture : ""),
                        'phone'             => !empty($social['data']->phone) ? $social['data']->phone : "",
                        'unique_id'         => $social['data']->unique_id,
                        'first_time'         => $social['data']->first_time,
                        'token'             => $token_data
                    ],
                ]);
                $this->response($responseArray, REST_Controller::HTTP_OK);

            } else if ($social['success'] == 2) {
                /* -- Upload Profile Picture -- */
                $profile_picture = $this->input->post('profile_picture');

                if (!empty($profile_picture)) {
                    $img_data = file_get_contents($profile_picture);
                    $avatar_name = time() . rand(100, 999) . '.jpg';
                    $file = 'upload/avatar/' . $avatar_name;
                    $success = file_put_contents($file, $img_data);
                    if ($success) {
                        $profile_pic = $file;
                    } else {
                        $profile_pic = NULL;
                    }
                } else {
                    $profile_pic = NULL;
                }
                /* -- End Upload Profile Picture -- */

                $identity = $this->input->post('email');
                $email = $this->input->post('email');
                $password = $this->input->post('social_id');
                $latitude = $this->input->post('latitude');
                $longitude = $this->input->post('longitude');

                $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                $code =  substr(str_shuffle($str_result),0, 6);
                $additional_data = [
                    'social_id'         => $this->input->post('social_id'),
                    'first_name'        => $this->input->post('full_name'),
                    'email'             => $identity,
                    'type'              => $this->input->post('type'),
                    'profile_picture'   => $profile_pic,
                    'unique_id'         => $code.'_'.time(),
                    'register_date'     => date('Y-m-d'),
                    'latitude'          => !empty($latitude) ? $latitude : "",
                    'longitude'         => !empty($longitude) ? $longitude : "",
                ];

                if (!empty($email)) {
                    $user = $this->account_model->check_user_email_exist($email);

                    if (!empty($user)) {

                        if($user->is_deleted == 1){
                            $this->response([
                                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                                $this->config->item('rest_message_field_name')  => $this->lang->line('social_signup_account_not_exist')
                            ], REST_Controller::HTTP_OK);
                        }
                        
                        $update_data = [
                            'social_id' => $this->input->post('social_id'),
                            'active' => '1',
                            'activation_code' => NULL
                        ];

                        $isUpdate = $this->account_model->update_user_data(['id' => $user->id], $update_data);

                        if ($isUpdate) {
                            // create token data
                            $token_create = (object) [
                                'id' => (int) $user->id,                  
                                'iat' => now()
                            ];
                            // Generate token
                            $token_data = AUTHORIZATION::generateToken($token_create);

                            $this->response([
                                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                                $this->config->item('rest_message_field_name')  => $this->lang->line('login_successful'),
                                $this->config->item('rest_data_field_name')     => [
                                    'user_id' => (int) $user->id,
                                    'full_name' => !empty($user->first_name) ? $user->first_name : "",
                                    'email' => !empty($user->email) ? $user->email  : "",
                                    'profile_picture' => (!empty($user->profile_picture) ? $user->profile_picture : ""),
                                    'unique_id' => $user->unique_id,
                                    'first_time' => $user->first_time,
                                    'token' => $token_data
                                ],
                            ], REST_Controller::HTTP_OK);
                        } else {
                            $this->response([
                                $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                                $this->config->item('rest_message_field_name') => $this->lang->line('social_signup_something_wrong')
                            ], REST_Controller::HTTP_OK);
                        }

                    } else {
                        $register = $this->ion_auth->social_register($identity, $password, $email, $additional_data);

                        if ($register['success'] == 1) {

                            // create token data
                            $token_create = (object) [
                                'id' => (int) $register['id'],                  
                                'iat' => now()
                            ];
                            // Generate token
                            $token_data = AUTHORIZATION::generateToken($token_create);

                            $this->response([
                                $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_one'),
                                $this->config->item('rest_message_field_name') => $this->lang->line('login_successful'),
                                'data' => [
                                    'user_id' => (int) $register['id'],
                                    'full_name' => !empty($this->input->post('full_name')) ? $this->input->post('full_name') : "",
                                    'email' => !empty($email) ? $email  : "",
                                    'profile_picture' => (!empty($profile_pic) ? $profile_pic : ""),
                                    'unique_id' => $additional_data['unique_id'],
                                    'first_time' => "1",
                                    'token' => $token_data
                                ]
                            ], REST_Controller::HTTP_OK);
                        } else {
                            // Set the response and exit
                            $this->response([
                                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                                $this->config->item('rest_message_field_name')  => $this->lang->line('social_signup_something_wrong')
                            ], REST_Controller::HTTP_OK);
                        }
                    }
                } else {

                    $register = $this->ion_auth->social_register($identity, $password, $email, $additional_data);

                    if ($register['success'] == 1) {

                        // create token data
                        $token_create = (object) [
                            'id' => (int) $register['id'],                  
                            'iat' => now()
                        ];
                        // Generate token
                        $token_data = AUTHORIZATION::generateToken($token_create);
                        $this->response([
                            $this->config->item('rest_status_field_name')  => $this->config->item('rest_status_code_one'),
                            $this->config->item('rest_message_field_name') => $this->lang->line('login_successful'),
                            'data' => [
                                'user_id'           => (int) $register['id'],
                                'full_name'         => !empty($this->input->post('full_name')) ? $this->input->post('full_name') : "",
                                'email'             => !empty($email) ? $email  : "",
                                'profile_picture'   => (!empty($profile_pic) ? $profile_pic : ""),
                                'unique_id'         => $additional_data['unique_id'],
                                'first_time'        => "1",
                                'token'             => $token_data
                            ]
                        ], REST_Controller::HTTP_OK);
                    } else {
                        // Set the response and exit
                        $this->response([
                            $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                            $this->config->item('rest_message_field_name')  => $this->lang->line('social_signup_something_wrong')
                        ], REST_Controller::HTTP_OK);
                    }
                }
            } else {
                // Set the response and exit
                $this->response([
                    $this->config->item('rest_status_field_name') => $social['success'],
                    $this->config->item('rest_message_field_name') => $social['message']
                        ], REST_Controller::HTTP_OK); 
            }
        } else {
            // Set the response and exit
            $this->response([
                $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name') => 'Empty request parameter(s). [ social_id | type ]'
            ], REST_Controller::HTTP_OK); 
        }
    }

    /**
     * Change Verified Phone Status
     * Method (POST)
     */
    public function change_verified_phone_status_post()
    {
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

        $isUpdate = $this->account_model->update_user_data(['id' => $authorized_user['account']->id], ['is_verified_phone' => 1,'first_time' => 0]);

        if($isUpdate){

            // create token data
            $token_create = (object) [
                'id' => (int) $authorized_user['account']->id,                  
                'iat' => now()
            ];
            // Generate token
            $token_data = AUTHORIZATION::generateToken($token_create);

            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('phone_change_success'),
                $this->config->item('rest_data_field_name')     => [
                    'user_id' => (int) $authorized_user['account']->id,
                    'full_name' => !empty($authorized_user['account']->first_name) ? $authorized_user['account']->first_name : "",
                    'email' => !empty($authorized_user['account']->email) ? $authorized_user['account']->email  : "",
                    'profile_picture' => (!empty($authorized_user['account']->profile_picture) ? base_url($authorized_user['account']->profile_picture) : ""),
                    'phone' => !empty($authorized_user['account']->phone) ? $authorized_user['account']->phone : "",
                    'country_code' => !empty($authorized_user['account']->country_code) ? $authorized_user['account']->country_code : "",
                    'is_verified_phone' => 1,
                    'first_time' => 0,
                    'unique_id' => $authorized_user['account']->unique_id,
                    'token' => $token_data,
                ],
            ], REST_Controller::HTTP_OK);
        }else{
            $this->response([
                $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name') => $this->lang->line('something_wrong')
            ], REST_Controller::HTTP_OK);
        }
    }

    /**
     * User forgot password
     * Method (POST)
     */
    public function forgot_password_post() {

        // setting validation rules by checking whether identity is username or email
        if ($this->config->item('email', 'ion_auth') != 'email') {
            $this->form_validation->set_rules('email', $this->lang->line('forgot_password_identity_label'), 'required');
        } else {
            $this->form_validation->set_rules('email', $this->lang->line('forgot_password_validation_email_label'), 'required|valid_email');
        }


        if ($this->form_validation->run() == false) {
            // Set the response and exit
            $this->response([
                $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name') => 'Empty request parameter(s). [ email ]'
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {

            $identity_column = $this->config->item('identity', 'ion_auth');
            $identity = $this->ion_auth->where($identity_column, $this->input->post('email'))->users()->row();

            if (empty($identity)) {
                if ($this->config->item('identity', 'ion_auth') != 'email') {
                    $this->ion_auth->set_error('forgot_password_identity_not_found');
                } else {
                    $this->ion_auth->set_error('forgot_password_email_not_found');
                }

                // Set the response and exit
                $this->response([
                    $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name') => $this->lang->line('forgot_password_email_not_found'),
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }

            // run the forgotten password method to email an activation code to the user
            $forgotten = $this->ion_auth->forgotten_password($identity->email);
            
            if ($forgotten) {
                $this->data['forgotten'] = $forgotten;
                $message = $this->load->view($this->config->item('email_templates', 'ion_auth') . $this->config->item('email_forgot_password', 'ion_auth'), $this->data, true);

                $mail = new PHPMailer;
                $mail->isSMTP();
                $mail->Host = $this->config->item('aws_ses_host');
                $mail->SMTPAuth = true;
                $mail->Username = $this->config->item('aws_ses_username');
                $mail->Password = $this->config->item('aws_ses_password');
                $mail->SMTPSecure = 'ssl';
                $mail->Port = 465;
                $mail->setFrom($this->config->item('admin_email', 'ion_auth'), $this->config->item('site_title', 'ion_auth'));
                $mail->addAddress($forgotten['identity'], $this->config->item('site_title', 'ion_auth'));
                $mail->isHTML(true);
                $mail->Subject = $this->config->item('site_title', 'ion_auth') . ' - ' . $this->lang->line('email_forgotten_password_subject');
                $mail->Body = $message;
                if ($mail->send()) {
                    $this->response([
                        $this->config->item('rest_status_field_name') => 1,
                        $this->config->item('rest_message_field_name') => $this->lang->line('forgot_password_email_sent_successful') . $forgotten['identity']
                            ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
                } else {
                    $this->response([
                        $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                        $this->config->item('rest_message_field_name') =>  $this->lang->line('forgot_password_email_sent_unsuccessful') . $identity->email
                            ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
                }
            } else {
                // Set the response and exit
                $this->response([
                    $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name') => $this->lang->line('forgot_password_email_sent_unsuccessful') . $identity->email
                        ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
        }
    }

    /**
     * Get Profile
     * Method (GET)
    */
    public function get_profile_get()
    {
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

        // create token data
        $token_create = (object) [
            'id' => (int) $authorized_user['account']->id,                  
            'iat' => now()
        ];
        // Generate token
        $token_data = AUTHORIZATION::generateToken($token_create);
        if ($authorized_user['account']->gender_id == 1) {
            $authorized_user['account']->gender = 'male';
        } else if ($authorized_user['account']->gender_id == 2) {
            $authorized_user['account']->gender = 'female';
        } else {
            $authorized_user['account']->gender = 'other';
        }
        $city = $this->general_model->getOne('cities', ['id' => $authorized_user['account']->city_id]);
        $data = [
            'user_id'           => (int) $authorized_user['account']->id,
            'email'             => !empty($authorized_user['account']->email) ? $authorized_user['account']->email : "",
            'full_name'         => !empty($authorized_user['account']->first_name) ? $authorized_user['account']->first_name : "",
            'country_code'      => !empty($authorized_user['account']->country_code) ? $authorized_user['account']->country_code : "",
            'dob'               => !empty($authorized_user['account']->dob) ? $authorized_user['account']->dob : "",
            'gender'            => !empty($authorized_user['account']->gender) ? $authorized_user['account']->gender : "",
            'city'              => !empty($city->name) ? $city->name : "",
            'phone'             => !empty($authorized_user['account']->phone) ? $authorized_user['account']->phone : "",
            'is_verified_phone' => (int) $authorized_user['account']->is_verified_phone,
            'unique_id'         => $authorized_user['account']->unique_id,
            'social_id'         => $authorized_user['account']->social_id,
            'profile_picture'   => (!empty($authorized_user['account']->profile_picture) ? $authorized_user['account']->profile_picture : ""),
            'first_time'        => $authorized_user['account']->first_time,
            'token'             => $token_data
        ];
        
        $this->response([
            $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
            $this->config->item('rest_message_field_name')  => $this->lang->line('profile_found'),
            $this->config->item('rest_data_field_name')     => $data
        ], REST_Controller::HTTP_OK);
    }

    /**
     * Check Mobile number before update
     * Method (POST)
     */
    public function check_phone_exists_post()
    {
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

        $this->form_validation->set_rules('phone','','required');

        if ($this->form_validation->run() == true) {
            $phone = $this->input->post('phone');

            if ($phone == $authorized_user['account']->phone) {
                $this->response([
                    $this->config->item('rest_status_field_name')       => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name')      => $this->lang->line('profile_phone_already_registered'),
                ], REST_Controller::HTTP_OK);
            }

            $phone_exist = $this->general_model->getOne('users', ['phone' => $phone]);

            if (!empty($phone_exist)) {
                if ($phone_exist->id != $authorized_user['account']->id) {
                    $this->response([
                        $this->config->item('rest_status_field_name')       => $this->config->item('rest_status_code_zero'),
                        $this->config->item('rest_message_field_name')      => $this->lang->line('profile_phone_already_exists')
                    ], REST_Controller::HTTP_OK);
                }
            } else {
                $this->response([
                    $this->config->item('rest_status_field_name')       => $this->config->item('rest_status_code_one'),
                    $this->config->item('rest_message_field_name')      => 'New'
                ], REST_Controller::HTTP_OK);
            }
        } else {
            /* Empty request parameter */
            $this->response([
                $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name') => 'Empty request parameter(s) [phone]'
                ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }

    /**
     * Change Verified Phone Status
     * Method (POST)
     */
    public function change_phone_number_post()
    {
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

        $this->form_validation->set_rules('phone','','required');

        if ($this->form_validation->run() == true) {
            $phone         = $this->input->post('phone');
            $country_code  = $this->input->post('country_code');

            $isUpdate = $this->general_model->update('users', ['id' => $authorized_user['account']->id], ['phone' => $phone, 'country_code' => $country_code]);

            if ($isUpdate) {
                // create token data
                $token_create = (object) [
                    'id' => (int) $authorized_user['account']->id,                  
                    'iat' => now()
                ];
                // Generate token
                $token_data = AUTHORIZATION::generateToken($token_create);

                $data = [
                    'user_id'           => (int) $authorized_user['account']->id,
                    'email'             => $authorized_user['account']->email,
                    'full_name'         => $authorized_user['account']->first_name,
                    'country_code'      => !empty($authorized_user['account']->country_code) ? $authorized_user['account']->country_code : "",
                    'phone'             => !empty($phone) ? $phone : $authorized_user['account']->phone,
                    'is_verified_phone' => (int) $authorized_user['account']->is_verified_phone,
                    'unique_id'         => $authorized_user['account']->unique_id,
                    'profile_picture'   => (!empty($authorized_user['account']->profile_picture) ? base_url($authorized_user['account']->profile_picture) : ""),
                    'token'             => $token_data
                ];
                
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('profile_phone_number_change_success'),
                    $this->config->item('rest_data_field_name')     => $data
                ], REST_Controller::HTTP_OK);
            }else{
                $this->response([
                    $this->config->item('rest_status_field_name')  => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name') => $this->lang->line('profile_phone_number_change_failed')
                ], REST_Controller::HTTP_OK);
            }
        } else {
            /* Empty request parameter */
            $this->response([
                $this->config->item('rest_status_field_name')  => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name') => 'Empty request parameter(s) [phone]'
                ], REST_Controller::HTTP_OK);
        }
    }

    /**
     * Edit Profile
     * Method (POST)
     */
    public function edit_profile_post()
    {
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

        $this->form_validation->set_rules('full_name','','required');
        $this->form_validation->set_rules('dob','','required');
        $this->form_validation->set_rules('gender_id','','required');
        $this->form_validation->set_rules('city_id','','required');
        $this->form_validation->set_rules('phone','','required');

        if ($this->form_validation->run() == true) {
            $full_name  = $this->input->post('full_name');
            $dob  = $this->input->post('dob');
            $gender_id  = $this->input->post('gender_id');
            $city_id  = $this->input->post('city_id');
            $phone  = $this->input->post('phone');
            $update_data = [
                'first_name' => $full_name,
                'dob' => $dob,
                'gender_id' => $gender_id,
                'phone' => !empty($phone) ? $phone : "",
                'city_id' => $city_id,
                'first_time' => 0,
            ];
            if (!empty($_FILES['profile_picture']['name'])) {
                $get_data = $this->general_model->getOne('users', ['id' => $authorized_user['account']->id]);
                if (!empty($get_data)) {
                    if (file_exists(base_url($get_data->profile_picture))) {
                        unlink($get_data->profile_picture);
                    }
                } else {
                    $this->response([
                        $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                        $this->config->item('rest_message_field_name')  => $this->lang->line('update_failed_party_message')
                            ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
                }
                $file_name = time() . rand(100, 999);
                $config = [
                    'upload_path' => './upload/avatar/',
                    'file_name' => $file_name,
                    'allowed_types' => 'png|jpg|jpeg',
                    'max_size' => 50480,
                    'max_width' => 20480,
                    'max_height' => 20480,
                    'file_ext_tolower' => TRUE,
                    'remove_spaces' => TRUE,
                ];
                $this->load->library('upload/', $config);
                if ($this->upload->do_upload('profile_picture')) {
                    $uploadData = $this->upload->data();
                    $update_data['profile_picture'] = 'upload/avatar/' . $uploadData['file_name'];
                } else {
                    $this->response([
                        $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                        $this->config->item('rest_message_field_name')  => $this->upload->display_errors()
                            ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
                }
            }
            $isUpdate = $this->general_model->update('users', ['id' => $authorized_user['account']->id], $update_data);

            if ($isUpdate) {
                $authorized_user['account'] = $this->general_model->getOne('users', ['id' => $authorized_user['account']->id]);
                // create token data
                $token_create = (object) [
                    'id' => (int) $authorized_user['account']->id,                  
                    'iat' => now()
                ];
                // Generate token
                $token_data = AUTHORIZATION::generateToken($token_create);
                if ($authorized_user['account']->gender_id == 1) {
                    $authorized_user['account']->gender = 'male';
                } else if ($authorized_user['account']->gender_id == 2) {
                    $authorized_user['account']->gender = 'female';
                } else {
                    $authorized_user['account']->gender = 'other';
                }
                $city = $this->general_model->getOne('cities', ['id' => $authorized_user['account']->city_id]);
                $data = [
                    'user_id'           => (int) $authorized_user['account']->id,
                    'email'             => !empty($authorized_user['account']->email) ? $authorized_user['account']->email : "",
                    'full_name'         => !empty($authorized_user['account']->first_name) ? $authorized_user['account']->first_name : "",
                    'dob'               => !empty($authorized_user['account']->dob) ? $authorized_user['account']->dob : "",
                    'gender'            => !empty($authorized_user['account']->gender) ? $authorized_user['account']->gender : "",
                    'city'              => !empty($city->name) ? $city->name : "",
                    'phone'             => !empty($authorized_user['account']->phone) ? $authorized_user['account']->phone : "",
                    'is_verified_phone' => (int) $authorized_user['account']->is_verified_phone,
                    'unique_id'         => $authorized_user['account']->unique_id,
                    'first_time'         => $authorized_user['account']->first_time,
                    'profile_picture'   => (!empty($authorized_user['account']->profile_picture) ? $authorized_user['account']->profile_picture : ""),
                    'token'             => $token_data
                ];
                
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('profile_update_success'),
                    $this->config->item('rest_data_field_name')     => $data
                ], REST_Controller::HTTP_OK);
            
            } else {
                $this->response([
                    $this->config->item('rest_status_field_name')  => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name') => $this->lang->line('profile_update_fail')
                ], REST_Controller::HTTP_OK);
            }
        } else {
            /* Empty request parameter */
            $this->response([
                $this->config->item('rest_status_field_name')  => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name') => 'Empty request parameter(s). [ ' . ltrim(str_replace("\n", '', validation_errors()), ' |') . ' ]'
            ], REST_Controller::HTTP_OK); 
        }
    }

    /**
     * Change password for user
     * Method (POST)
     */
    public function change_password_post(){
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

        $this->form_validation->set_rules('current_password','','required');
        $this->form_validation->set_rules('new_password','','required');

        if ($this->form_validation->run() == true) {

            $data = $this->general_model->getOne('users', ['id' => $authorized_user['account']->id]);

            $identity = $data->email;

            $old = $this->input->post('current_password');
            $new = $this->input->post('new_password');

            $change = $this->ion_auth->change_password($identity, $old, $new);
            
            if ($change) {
                $this->response([
                    $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_one'),
                    $this->config->item('rest_message_field_name') => $this->lang->line('change_password_successful'),
                ], REST_Controller::HTTP_OK); 
            } else {
                $this->response([
                    $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name') => $this->lang->line('change_password_unsuccessful')
                ], REST_Controller::HTTP_OK); 
            }
        } else {
            $this->response([
                $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name') => 'Empty request parameter(s) [current_password | new_password ]'
            ], REST_Controller::HTTP_OK);
        }
    }

    /**
     * Get User Earning Point List
     * Method (POST)
     */
    public function earning_points_get(){
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

        $list = $this->account_model->get_earning_points($authorized_user['account']->id);

        if ($list) {
            $data = [
                'list' => $list,
                'points' => $authorized_user['account']->points,
                'earning_amount' => $authorized_user['account']->earning_amount
            ];
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('earning_points_list_found'),
                $this->config->item('rest_data_field_name')     => $data
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('earning_points_list_empty')
            ], REST_Controller::HTTP_OK);
        }
    }

    /**
     * Change Profile Picture
     * Method (POST)
     */
    public function change_profile_picture_post()
    {
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

        if (isset($_FILES['profile_picture']) && !empty($_FILES['profile_picture']['name'])) {
                $config['upload_path'] = './upload/avatar/';
                $config['allowed_types'] = 'jpg|jpeg|png';
                
                $config['file_ext_tolower'] = TRUE;
                $config['remove_spaces'] = TRUE;
                $config['encrypt_name'] = TRUE;

                $this->load->library('upload/', $config);
                $this->upload->initialize($config);
                if ($this->upload->do_upload('profile_picture')) {
                    $uploadData = $this->upload->data();
                    if(!empty($authorized_user['account']->profile_picture)){
                      unlink($authorized_user['account']->profile_picture);
                    }

                    $profile_picture = 'upload/avatar/'.$uploadData['file_name'];
                } else {
                    $error = array('error' => $this->upload->display_errors());
                    $this->response([
                        $this->config->item('rest_result_code_field_name')  => $this->config->item('rest_status_code_zero'),
                        $this->config->item('rest_message_field_name')      => strip_tags($error['error'])
                    ], REST_Controller::HTTP_OK);
                }
        } else {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('profile_picture_not_uploaded')
            ], REST_Controller::HTTP_OK);
        }

        $is_update = $this->account_model->update_user_data(['id' => $authorized_user['account']->id], ['profile_picture' => $profile_picture]);

        if ($is_update) {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('profile_picture_change_success'),
                $this->config->item('rest_data_field_name')     => ['profile_picture' => base_url($profile_picture)]
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name')  => $this->lang->line('profile_picture_change_failed')
            ], REST_Controller::HTTP_OK);
        }
    }

    /**
     * Referral code check
     * Method (POST)
     */
    public function referral_code_check_post()
    {
        $this->form_validation->set_rules('referral_code', 'referral_code', 'required');

        if ($this->form_validation->run() == true) {
            if ($this->account_model->check_referral_code($this->input->post('referral_code'))) {
                $this->response([
                    $this->config->item('rest_status_field_name')  => $this->config->item('rest_status_code_one'),
                    $this->config->item('rest_message_field_name') => $this->lang->line('referral_code_found')
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    $this->config->item('rest_status_field_name')  => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name') => $this->lang->line('referral_code_not_found')
                ], REST_Controller::HTTP_OK);
            }
        } else {
            /* Empty request parameter */
            $this->response([
                $this->config->item('rest_status_field_name')  => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name') => 'Empty request parameter(s) [referral_code]'
            ], REST_Controller::HTTP_OK);
        }
    }

    public function update_user_token_post() {
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
        $this->form_validation->set_rules('device_id', '', 'required', ['required' => '%s']);
        $this->form_validation->set_rules('device_token', '', 'required', ['required' => '%s']);
        if ($this->form_validation->run() == true) {
            $data = [
                'user_id'        => $authorized_user['account']->id,
                'device_token'   => $this->input->post('device_token'),
                'device_id'      => $this->input->post('device_id'),
                'last_update_on' => time()
            ];
            $update = $this->account_model->update_user_token($data);
            if ($update) {
                $this->response([
                    $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_one'),
                    $this->config->item('rest_message_field_name') => $this->lang->line('user_device_token_success')
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name') => $this->lang->line('user_device_token_failed')
                ], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response([
                $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name') => 'Empty request parameter(s). [ ' . ltrim(str_replace("\n", '', validation_errors()), ' |') . ' ]'
            ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }

    /** delete user account
     *
     * Method (POST)
     */
    public function delete_user_account_post(){
        /* Check Authentications */
        $headers = $this->input->request_headers();
        $authorized_user = $this->general_model->check_authorization($headers);
        if ($authorized_user['status'] != 1) {
            $this->response([
                $this->config->item('rest_status_field_name') => $authorized_user['status'],
                $this->config->item('rest_message_field_name') => $authorized_user['message']
            ], REST_Controller::HTTP_OK);
        }

        $user_id = $authorized_user['account']->id;

        if ($authorized_user['account']->is_deleted == 1) {
            $this->response([
                $this->config->item('rest_status_field_name') => 0,
                $this->config->item('rest_message_field_name') => $this->lang->line('user_account_already_deleted'),
            ], REST_Controller::HTTP_OK);
        }

        /* End Check Authentications */
        $this->form_validation->set_rules('is_social', '', 'required', ['required' => '%s']);
        if ($this->input->post('is_social') == 1) {
            $this->form_validation->set_rules('email', '', 'required', ['required' => '%s']);
        } else {
            $this->form_validation->set_rules('password', '', 'required', ['required' => '%s']);
        }
        if ($this->form_validation->run() == FALSE) {
            $this->response([
            $this->config->item('rest_status_field_name') => 0,
            $this->config->item('rest_message_field_name') => 'Empty request parameter(s). [ ' . ltrim(str_replace("\n", '', validation_errors()), ' |') . ' ]'
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }

        $password = $this->input->post('password');
        $email = $this->input->post('email');

        if ($this->input->post('is_social') == 1) {
            if (empty($authorized_user['account']->email)) {
                $this->response([
                    $this->config->item('rest_status_field_name') => 0,
                    $this->config->item('rest_message_field_name') => $this->lang->line('user_account_delete_email_not_found'),
                ], REST_Controller::HTTP_OK);
            }

            if ($authorized_user['account']->email == $email) {
                /* delete user account */
                $delete = $this->account_model->delete_user_account($user_id);
                if ($delete) {
                    /* return message account deleted successfully */
                    $this->response([
                        $this->config->item('rest_status_field_name') => 1,
                        $this->config->item('rest_message_field_name') => $this->lang->line('user_account_delete_successfully')
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
                } else {
                    $this->response([
                        $this->config->item('rest_status_field_name') => 0,
                        $this->config->item('rest_message_field_name') => $this->lang->line('user_account_delete_unsuccessfully')
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code      
                }
            } else {
                $this->response([
                    $this->config->item('rest_status_field_name') => 0,
                    $this->config->item('rest_message_field_name') => $this->lang->line('user_account_email_incorrect')
                ], REST_Controller::HTTP_OK);
            }
        } else {
            /* Check appsword correct or not */
            $password_status = $this->ion_auth_model->check_password($password,$user_id);

            if ($password_status == 1) {
                $user = $this->ion_auth->user($user_id)->row();

                /* delete user account */
                $delete = $this->account_model->delete_user_account($user_id);
                if ($delete) {
                    /* return message account deleted successfully */
                    $this->response([
                        $this->config->item('rest_status_field_name') => 1,
                        $this->config->item('rest_message_field_name') => $this->lang->line('user_account_delete_successfully')
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
                }else{
                    $this->response([
                        $this->config->item('rest_status_field_name') => 0,
                        $this->config->item('rest_message_field_name') => $this->lang->line('user_account_delete_unsuccessfully')
                    ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code      
                }
            } else if ($password_status == 2) {
                /* return message if password incorrect */
                $this->response([
                    $this->config->item('rest_status_field_name') => 0,
                    $this->config->item('rest_message_field_name') => $this->lang->line('user_account_password_incorrect')
                ], REST_Controller::HTTP_OK);
            } else {
                /* return message if something went wrong */
                $this->response([
                    $this->config->item('rest_status_field_name') => 0,
                    $this->config->item('rest_message_field_name') => $this->lang->line('user_account_somthing_wrong')
                ], REST_Controller::HTTP_OK);
            }
        }
    }


    public function notification_list_get() {
        /* Check Authentications */
        $headers = $this->input->request_headers();
        $authorized_user = $this->general_model->check_authorization($headers);
        if ($authorized_user['status'] != 1) {
            $this->response([
                $this->config->item('rest_status_field_name') => $authorized_user['status'],
                $this->config->item('rest_message_field_name') => $authorized_user['message']
            ], REST_Controller::HTTP_OK);
        }
        $offset = $this->input->get('offset') * getenv('LIMIT_NOTIFICATION_LIST');
        $notifications = $this->account_model->notification_list($authorized_user['account']->id, $offset, getenv('LIMIT_NOTIFICATION_LIST'));
        if ($notifications) {
            $this->response([
                $this->config->item('rest_status_field_name') => 1,
                $this->config->item('rest_message_field_name') => 'Found',
                $this->config->item('rest_data_field_name') => $notifications
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                $this->config->item('rest_status_field_name') => 0,
                $this->config->item('rest_message_field_name') => $this->lang->line('notification_list_empty'),
            ], REST_Controller::HTTP_OK);
        }
    }

 /**
     * Send OTP
     * Method (POST)
     */
    public function send_otp_post()
    {
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
        $this->form_validation->set_rules('phone','','required');
        if ($this->form_validation->run() == true) {
            $phone = $this->input->post('phone');
            $phone_exist = $this->general_model->getOne('users', ['phone' => $phone]);
            if (!empty($phone_exist)) {
                
                if ($phone_exist->id==$authorized_user['account']->id) {
                /*-------send sms on mobile number------------*/
                $opt=get_otp();
                $current_date=date('Y-m-d H:i:s');
                $otp_expiry_date= date('Y-m-d H:i:s', strtotime('+5 minutes', strtotime($current_date)));
                $update_arr=array('otp'=>$opt,'otp_expiry_date'=>$otp_expiry_date);
                $message=$opt." is the OTP to SignUp on PartyPeople .Do not share to anyone.";
                $this->db->update('users',$update_arr,['id' =>$authorized_user['account']->id]);
                 send_sms(array('mobile_nuber'=>$phone_exist->phone,'message'=>$message,'template_id'=>'1207167508255462029'));
                
                /*--------------------------------------------*/ 
                 $token_create = (object) [
                    'id' => (int) $authorized_user['account']->id,                  
                    'iat' => now()
                ];
                // Generate token
                $token_data = AUTHORIZATION::generateToken($token_create);
                 $data = [
                    'user_id'           => (int) $phone_exist->id,
                    'is_verified_phone' => $phone_exist->is_verified_phone,
                    'otp'               => $opt,
                    'unique_id'         => $phone_exist->unique_id,
                    'token'             => $token_data
                ];   
                    $this->response([
                        $this->config->item('rest_status_field_name')       => $this->config->item('rest_status_code_one'),
                        $this->config->item('rest_message_field_name')      =>'Found',
                         $this->config->item('rest_data_field_name')     => $data
                    ], REST_Controller::HTTP_OK);
                }
            } else {
                $this->response([
                    $this->config->item('rest_status_field_name')       => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name')      => 'Invailed mobile number'
                ], REST_Controller::HTTP_OK);
            }
        } else {
            /* Empty request parameter */
            $this->response([
                $this->config->item('rest_status_field_name') => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name') => 'Empty request parameter(s) [phone]'
                ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
    }

         public function otp_verify_post()
    {
        /* Check Authentications */
        $headers = $this->input->request_headers();
        
        $authorized_user = $this->general_model->check_authorization($headers);
         //print_r($authorized_user); die;
        if ($authorized_user['status'] != 1) {
            $this->response([
                $this->config->item('rest_status_field_name') => $authorized_user['status'],
                $this->config->item('rest_message_field_name') => $authorized_user['message']
            ], REST_Controller::HTTP_OK);
        }
        /* End Check Authentications */
        $this->form_validation->set_rules('otp','','required');
        if ($this->form_validation->run() == true) {
           $otp  = $this->input->post('otp');
           $authorized_user['account'] = $this->general_model->getOne('users', ['id' => $authorized_user['account']->id,'otp'=>$otp]);
        
            if ($authorized_user['account']) {
                /*------------otp remove-----------*/
                 $update_data = [
                'otp' =>'',
                'is_verified_phone' =>1];
                 $isUpdate = $this->general_model->update('users', ['id' => $authorized_user['account']->id,'otp'=>$otp], $update_data);
                /*----------------------------------*/
                // create token data
                $token_create = (object) [
                'id' => (int) $authorized_user['account']->id,                  
                'iat' => now()
                ];
                // Generate token
                $token_data = AUTHORIZATION::generateToken($token_create);
                $account = $this->general_model->getOne('users', ['id' => $authorized_user['account']->id]);

                   $data = [
                    'user_id'           => (int) $account->id,
                    'email'             => !empty($account->email) ? $account->email : "",
                    'full_name'         => !empty($account->first_name) ? $account->first_name : "",
                    'country_code'      => !empty($account->first_name) ? $account->country_code : "",
                    'profile_picture'   => !empty($account->profile_picture) ? base_url($account->profile_picture) : "",
                    'phone'             => $account->phone,
                    'is_verified_phone' =>  $account->is_verified_phone,
                    'first_time'        => (int) $account->first_time,
                    'unique_id'         => $account->unique_id,
                    'token'             => $token_data
                ];
                
                $this->response([
                    $this->config->item('rest_status_field_name')   => $this->config->item('rest_status_code_one'),
                    $this->config->item('rest_message_field_name')  => $this->lang->line('otp_verify'),
                    $this->config->item('rest_data_field_name')     => $data
                ], REST_Controller::HTTP_OK);
            
            } else {
                $this->response([
                    $this->config->item('rest_status_field_name')  => $this->config->item('rest_status_code_zero'),
                    $this->config->item('rest_message_field_name') => $this->lang->line('otp_verify_fail')
                ], REST_Controller::HTTP_OK);
            }
        } else {
            /* Empty request parameter */
            $this->response([
                $this->config->item('rest_status_field_name')  => $this->config->item('rest_status_code_zero'),
                $this->config->item('rest_message_field_name') => 'Empty request parameter(s). [ ' . ltrim(str_replace("\n", '', validation_errors()), ' |') . ' ]'
            ], REST_Controller::HTTP_OK); 
        }
    }

    
}
