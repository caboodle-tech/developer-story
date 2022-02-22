<?php
/**
 * Handler for the profile update form.
 */

namespace Module\Core\Forms;

class Profile extends \Module\Core\Forms {

    private $allowImageType = [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_WEBP];

    /**
     * Instantiate a new Login object.
     *
     * @param array $postData An associative array to use as the $_POST data.
     */
    public function __construct($postData = null) {
        parent::__construct($postData);
    }

    private function deleteOldPicture() {
        global $Database;
        $db = $Database->connect();

        if ($db === false) {
            $this->returnError('Could not connect to the database.', 500);
        }

        global $Session;

        $stmt = $db->prepare("SELECT `profile_picture` AS picture FROM `users_profile` WHERE `id`=? LIMIT 1;");
        $stmt->bind_param('s', $Session->userId);
        $stmt->execute();

        if ($stmt->errno) {
            // TODO: log this error $stmt->error;
            $this->returnError('There was an error attempting to query the database.', 500);
        }

        $data = $stmt->get_result()->fetch_assoc();
        if (strlen($data['picture']) > 0) {
            $file = abspath('public/' . $data['picture']);
            if (file_exists($file)) {
                unlink($file);
            }
        }

        $db->close();
    }

    private function isPictureValid() {
        if (isset($_FILES['picture'])) {
            if ($_FILES['picture']['error'] === 0 && $_FILES['picture']['size'] > 0) {
                $type = exif_imagetype($_FILES['picture']['tmp_name']);
                if (!in_array($type, $this->allowImageType)) {
                    return false;
                };
            }
        }
        return true;
    }

    /**
     * Handle form submissions to this module.
     *
     * @return void
     */
    public function processRequest() {
        // Do not process request if we are not supposed to.
        parent::processRequest();

        $fname  = trim($_POST['first-name']);
        $mname  = trim($_POST['middle-name']);
        $lname  = trim($_POST['last-name']);
        $email  = trim($_POST['email']);
        $vanity = trim($_POST['vanity']);

        global $Sanitize;

        if (empty($fname) || empty($lname) || empty($email)) {
            $this->returnError('One or more required form fields are empty.');
        }

        if (!$Sanitize->validName($fname) || !$Sanitize->validName($mname) || !$Sanitize->validName($lname)) {
            $this->returnError('Invalid characters detected in your name.');
        }

        if (!$Sanitize->validEmail($email)) {
            $this->returnError('Invalid email provided.');
        }

        if (!$Sanitize->validVanity($vanity)) {
            $this->returnError('Invalid vanity provided.');
        }

        if (!$this->isPictureValid()) {
            $this->returnError('Invalid file type for profile picture.');
        }

        $picture = $this->savePicture();

        if (strlen($picture) > 0) {
            $this->deleteOldPicture();
        }

        global $Session;
        global $Database;
        $db = $Database->connect();

        if ($db === false) {
            $this->returnError('Could not connect to the database.', 500);
        }

        // TODO: Fix vanity!!! Needs sperate update statment?

        $stmt = $db->prepare("UPDATE `users_profile` SET `first_name`=?,`middle_name`=?, `last_name`=?, `vanity`=?, `vanity_set_date`=SYSDATE(), `profile_picture`=? WHERE `id`=?;");
        $stmt->bind_param('ssssss', $fname, $mname, $lname, $vanity, $picture, $Session->userId);
        $stmt->execute();

        print_r($stmt->get_result());

        $db->close();

        exit();
        //outputResponse('TEST END', 200);
    }

    private function savePicture() {
        global $Database;

        $day  = date('j', time());
        $file = $_FILES['picture']['tmp_name'];
        $id   = $Database->newId(22);
        $size = getimagesize($_FILES['picture']['tmp_name']);

        $dir    = 'uploads/' . $day;
        $rel    = 'uploads/' . $day . '/' . $id . '.webp';
        $output = abspath('public/uploads/' . $day . '/' . $id . '.webp');

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
 
        switch(exif_imagetype($_FILES['picture']['tmp_name'])) {
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($file);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($file);
                break;
            case IMAGETYPE_WEBP:
                $image = imagecreatefromwebp($file);
                break;
        }

        // TODO: Keep aspect ratio but resize image to under 500 height or width.
        // TODO: Catch if $image becomes false!

        $image = imagescale($image, 500, -1, IMG_BICUBIC);

        $result = imagewebp($image, $output, 80);

        if (!$result) {
            $rel = '';
        }
        return $rel;
    }

}