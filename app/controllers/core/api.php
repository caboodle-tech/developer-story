<?php
/**
 * A catch all controller that the Router sends third party API requests to. Api
 * will attempt to locate the correct handler and pass the request onto it; see
 * `setModule` for how this is done.
 */

namespace Controller\Core;

class Api extends Page {

    protected $module = '';

    /**
     * Instantiate a new instance of Oauth.
     *
     * @param object $data The Router data object containing information of the active request.
     */
    public function __construct($data) {
        parent::__construct($data);
        $this->setModule();
    }

    /**
     * Overwrite the default render method to pass this request onto the appropriate module.
     *
     * @return void
     */
    public function render() {
        if (class_exists($this->module)) {
            $handler = new $this->module();
            $handler->processRequest();
        } else {
            // TODO: Log error. $this->form;
            outputResponse('Could not locate the requested form handler.', 502);
        }
    }

    /**
     * Translates the requested URL into the class path (namespace) of this handler.
     * 
     * On the front end all api routes should start with `api/` and follow
     * with the specific module used to handle this api request.
     * 
     * Examples:
     * 
     * `./oauth/so` becomes '\Modules\Core\Api\So.php'.
     * `./oauth/hr/employee` becomes '\Modules\Core\Api\Hr\Employee'.
     *
     * @return void
     */
    protected function setModule() {
        $class = str_replace('api/', 'module/core/api/', $this->data->trimUrl);
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
        $this->module = '\\' . implode('\\', $class);
    }
}