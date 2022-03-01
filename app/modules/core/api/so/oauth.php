<?php
/**
 * Handler for the Stack Overflow Oauth process.
 */
namespace Module\Core\Api\So;

class Oauth extends \Module\Core\Forms {
    
    /**
     * Instantiate a new Oauth object.
     *
     * @param array $postData An associative array to use as the $_POST data.
     */
    public function __construct($postData = null) {
        parent::__construct($postData);
    }

    /**
     * Overwrite the `processRequest` method to respond to oauth communications
     * from the Stack Overflow API.
     *
     * @return void
     */
    public function processRequest() {
        // If we encountered an error redirect to the dashboard now.
        if (isset($_GET['error']) && isset($_GET['error_description'])) {
            setcookie(
                'report_error',
                json_encode(
                    [
                        'for'   => 'profile_connections',
                        'error' => urldecode($_GET['error_description']) . '.'
                    ]
                ),
                null,
                '/'
            );
            redirect(SITE_ROOT . 'dashboard');
        }

        // If this is the first response from Stack Overflow complete the handshake.
        if (isset($_GET['code'])) {
            $url = 'https://stackoverflow.com/oauth/access_token/json';
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

            $code   = $_GET['code'];
            $url    = SITE_ROOT . 'api/so_oauth';
            $secret = STACKOVERFLOW;
            $data   = "client_id=22934&client_secret=$secret&code=$code&redirect_uri=$url";
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

            $json = json_decode(curl_exec($curl));
            curl_close($curl);

            global $Database;
            global $Session;
            $db = $Database->connect();


            $token = $json->access_token;

            $stmt = $db->prepare("UPDATE `user_connections` SET `connected` = JSON_SET(`connected`, '$.connections.so', JSON_OBJECT('accessToken', '$token')) WHERE `id`=?;");
            $stmt->bind_param('s', $Session->userId);
            $stmt->execute();

            if ($stmt->errno) {
                // TODO: log this error $stmt->error;
                outputResponse('There was an error attempting to update the user connections.', 500);
            }
            $stmt->free_result();

            // TODO: Set success notice message.

        }

        if (isset($_POST['access_token'])) {
            echo 'YES';
            global $User;
            $User->mustBeLoggedIn();
            print_r($_POST);
            die();
        }

        // redirect(SITE_ROOT . 'dashboard');
    }

}