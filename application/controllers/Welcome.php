<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {
    
    public function index() {
        // Jangan redirect, kasih 404 aja
        show_404();
    }
}