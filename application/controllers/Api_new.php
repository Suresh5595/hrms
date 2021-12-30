<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Api_new extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('api_new/common_model');

    }

    public function index()
    {
       
    }
}