<?php

namespace Controller\Core;

class Page {

    protected $data      = [];
    protected $template  = ''; 
    protected $variables = [];

    public function __construct($data = null) {
        if (!$data) {
            $this->data = $this->makeEmptyDataObject();
        } else {
            $this->data = $data;
        }
        $this->setTemplate('');
    }

    protected function makeEmptyDataObject() {
        return (object) [
            'appUrl'  => '',
            'reqUrl'  => '',
            'trimUrl' => '',
            'params'  => ''
        ];
    }

    public function render() {
        extract($this->variables);
        ob_start();
        require $this->template;
        ob_end_flush();
    }

    public function setTemplate($path) {
        if (!empty($path)) {
            $abspath = absPath($path);
            if (file_exists($abspath)) {
                $this->template = $abspath;
                return;
            }
        }
        $this->template = absPath('app/views/core/page/home.phtml');
    }
}