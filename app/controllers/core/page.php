<?php
/**
 * A base controller for all pages of the application. Designed to be extended
 * from and not used directly.
 */

namespace Controller\Core;

class Page {

    protected $data      = [];
    protected $template  = ''; 
    protected $variables = [];

    /**
     * Instantiate this class and all children with data needed to fulfill the request.
     *
     * @param object $data The Router data object containing information of the active request.
     */
    public function __construct($data = null) {
        if (!$data) {
            $this->data = $this->makeEmptyDataObject();
        } else {
            $this->data = $data;
        }
        $this->setTemplate('');
    }

    /**
     * Every child expects data from the requested route. This will provided an
     * empty request object so no errors are caused when something is missing.
     *
     * @return object An empty Router data object.
     */
    protected function makeEmptyDataObject() {
        return (object) [
            'appUrl'  => '',
            'reqUrl'  => '',
            'trimUrl' => '',
            'params'  => ''
        ];
    }

    /**
     * Show the requested page to the user.
     * 
     * @see Page->setTemplate How the template is set.
     *
     * @return void
     */
    public function render() {
        extract($this->variables);
        ob_start();
        /* phpcs:ignore PEAR.Files.IncludingFile.UseInclude */
        require $this->template;
        ob_end_flush();
    }
    
    /**
     * Set the path to this pages view (template) file. If the file cannot be
     * found then default to the home page.
     *
     * @param string $path The root relative path to the view file for this page.
     * 
     * @return void
     */
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