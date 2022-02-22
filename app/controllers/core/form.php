<?php
/**
 * A catch all controller that the Router sends form submissions to. Form will
 * attempt to locate the correct form and pass the request onto it; see `setClass`
 * for how this is done.
 */

namespace Controller\Core;

class Form extends Page {

    protected $class = '';

    /**
     * Instantiate a new instance of Form.
     *
     * @param object $data The Router data object containing information of the active request.
     */
    public function __construct($data) {
        parent::__construct($data);
        $this->setClass();
    }

    /**
     * Overwrite the default render method to pass this request onto the appropriate module.
     *
     * @return void
     */
    public function render() {
        if (class_exists($this->class)) {
            $handler = new $this->class();
            $handler->processRequest();
        } else {
            /**
             * Do not allow direct access to form handlers. Silently redirect the
             * user back to the home page and do not alert them of anything.
             */
            if (strtoupper($_SERVER['REQUEST_METHOD']) === 'GET') {
                header('Location: ' . SITE_ROOT);
                exit();
            }
            // TODO: Log error. $this->form;
            outputResponse('Could not locate the requested form handler.', 502);
        }
    }

    /**
     * Translates the requested URL into the class path (namespace) of this handler.
     * 
     * On the front end forms should use `actions` that start with `form/` and
     * contain the relative path to the class from the `app/modules/core/forms/`
     * directory. Use underscores (_) as directory separators.
     * 
     * Examples:
     * 
     * An `action` of `./form/join` becomes '\Modules\Core\Forms\Join.php'.
     * An `action` of `./form/hr_new-hire_info` becomes '\Modules\Core\Forms\Hr\NewHire\Info'.
     *
     * @return void
     */
    protected function setClass() {
        $class = str_replace('form/', 'module/core/forms/', $this->data->trimUrl);
        $class = str_replace('_', '/', $class);
        $class = explode('/', $class);
        foreach ($class as $index => $val) {
            if (strpos($val, '-') !== false) {
                $val = explode('-', $val);
                foreach ($val as $i => $v) {
                    $val[$i] = ucfirst(strtolower($v));
                }
                $val = implode('', $val);
            }
            $class[$index] = ucfirst(strtolower($val));
        }
        $this->class = '\\' . implode('\\', $class);
    }
}