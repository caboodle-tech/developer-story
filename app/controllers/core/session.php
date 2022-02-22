<?php
/**
 * The session controller that wraps PHP's native $_SESSION.
 * 
 * @see https://www.php.net/manual/en/function.session-start.php#102460
 */
namespace Controller\Core;

class Session {

    private $sessionState = false;
    private static $instance;

    /**
     * Stores data in the session.
     * 
     * Example: $instance->foo = 'bar';
     *   
     * @param string $name  The name (key) of this property.
     * @param mixed  $value The value of this property.
     * 
     * @return void
     */
    public function __set($name , $value) {
        $_SESSION[$name] = $value;
    }
   
    /**
     * Gets data from the session.
     * 
     * Example: echo $instance->foo;
     *   
     * @param string $name The property name (key) to get.
     * 
     * @return mixed The value stored at this property if it exists.
     */
    public function __get($name) {
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }
    }
    
    /**
     * Checks if a session property exists.
     *
     * @param string $name The session property to check for.
     * 
     * @return boolean True if this session property exists, false otherwise.
     */
    public function __isset($name) {
        return isset($_SESSION[$name]);
    }

    /**
     * Unset a session property.
     *
     * @param string $name The session property to unset (remove).
     * 
     * @return void
     */
    public function __unset( $name ) {
        unset($_SESSION[$name]);
    }

    /**
     * Destroy (delete) the session and all its session data.
     *
     * @return boolean True if the session was destroyed, false otherwise.
     */
    public function destroy() {
        if ($this->sessionState === true ) {
            $this->sessionState = !session_destroy();
            unset($_SESSION);
            setcookie('devstory', time() - 36000);
            return !$this->sessionState;
        }
        return false;
    }

    /**
     * Get an instance of this class.
     *
     * @return Session An instance of this Session.
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        self::$instance->start();
        return self::$instance;
    }

    /**
     * Start or resume the session and make sure it is only accessible by HTTP.
     *
     * @return boolean True if the session has started/ reopened, false otherwise.
     */
    public function start() {
        if ($this->sessionState === false) {
            session_name('devstory');
            $this->sessionState = session_start();
            setcookie('devstory', session_id(), null, '/', null, null, true);
        }
        return $this->sessionState;
    }

    /**
     * Alias of Session->destroy.
     * 
     * @see Session->destroy 
     *
     * @return void
     */
    public function stop() {
        $this->destroy();
    }
}