<?php

namespace Controller\Core;

class Form extends Page {

    protected $form = '';

    public function __construct($data) {
        parent::__construct($data);
        $this->setForm();
    }

    public function render() {
        if (file_exists($this->form)) {
            require $this->form;
        } else {
            echo 'Form 404: ' . $this->form;
        }
    }

    protected function setForm() {
        $path       = str_replace('form/', '', $this->data->trimUrl);
        $path       = str_replace('_', '/', $path);
        $this->form = abspath('app/modules/core/forms/' . $path . '.php');
    }
}