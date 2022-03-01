<?php
/**
 * Handler for the profile update form.
 */

namespace Module\Core\Forms;

class Profile extends \Module\Core\Forms {

    private $allowImageType = [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_WEBP];

    /**
     * Instantiate a new Profile object.
     *
     * @param array $postData An associative array to use as the $_POST data.
     */
    public function __construct($postData = null) {
        parent::__construct($postData);
    }

    /**
     * Delete the active users current profile picture from the server.
     *
     * @return void
     */
    private function deleteOldPicture() {
        global $User;

        $picture = $User->profilePicture;
        
        if (strlen($picture) > 0) {
            $file = abspath('public/' . $picture);
            if (file_exists($file)) {
                if (unlink($file) === false) {
                    // TODO: Log error, we now have an orphaned file.
                }
            }
        }
    }

    /**
     * Check if the profile picture the user may have upload is in the allowed
     * image list; $this->allowImageType
     *
     * @return boolean
     */
    private function isPictureValid() {
        if (isset($_FILES['picture'])) {
            if ($_FILES['picture']['error'] === 0 && $_FILES['picture']['size'] > 0) {
                $type = exif_imagetype($_FILES['picture']['tmp_name']);
                if (!in_array($type, $this->allowImageType)) {
                    return false;
                };
            }
        }
        // The picture is either a valid type or was not uploaded.
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

        // Check that profile information is all valid:
        global $Sanitize;
        $fname  = trim($_POST['first-name']);
        $mname  = trim($_POST['middle-name']);
        $lname  = trim($_POST['last-name']);
        $email  = trim($_POST['email']);
        $vanity = trim($_POST['vanity']);

        if (empty($fname) || empty($lname) || empty($email)) {
            outputResponse('One or more required form fields are empty.', 400);
        }

        if (!$Sanitize->validName($fname) || !$Sanitize->validName($mname) || !$Sanitize->validName($lname)) {
            outputResponse('Invalid characters detected in your name.', 400);
        }

        if (!$Sanitize->validEmail($email)) {
            outputResponse('Invalid email provided.', 400);
        }

        if (!$Sanitize->validVanity($vanity)) {
            outputResponse('Invalid vanity provided.', 400);
        }

        // If a new profile picture was uploaded handle saving it now.
        $this->savePicture();

        // Open a connection to the database now to run the rest of the checks and update.
        global $Session;
        global $Database;
        global $User;
        $id = $User->getUserId();
        $db = $Database->connect();
        $db->begin_transaction();

        // Verify vanity uniqueness:
        $stmt = $db->prepare("SELECT a.existing, b.current FROM (SELECT COUNT(id) AS existing FROM user_profile WHERE id!=? AND vanity=? AND vanity!='') AS a JOIN (SELECT vanity AS current FROM user_profile WHERE id=?) AS b;");
        $stmt->bind_param('sss', $id, $vanity, $id);
        $stmt->execute();
        
        // Stop if we ran into a database error.
        if ($stmt->errno) {
            // TODO: Log this: Vanity check returned an unexpected result from the database.
            outputResponse('There was a database error, please try again later.', 500);
        }

        // Stop if this vanity is already taken.
        $result = $stmt->get_result()->fetch_assoc();
        if ($result['existing'] > 0) {
            outputResponse('This vanity is already is use by another user.', 400);
        }
        $stmt->free_result();

        /*
         * If the $vanity is an empty string change it to NULL; otherwise we
         * will run into database errors because vanity must be unique.
         */
        if (empty($vanity)) {
            $vanity = null;
        }
        
        // Does the $vanity value match the users existing value?
        if ($vanity === $result['current']) {
            // Yes, skip updating it.
            $stmt = $db->prepare("UPDATE user_profile SET first_name=?,middle_name=?, last_name=? WHERE id=?;");
            $stmt->bind_param('ssss', $fname, $mname, $lname, $id);
        } else {
            // No update it as well.
            $stmt = $db->prepare("UPDATE user_profile SET first_name=?,middle_name=?, last_name=?, vanity=?, vanity_set_date=SYSDATE() WHERE id=?;");
            $stmt->bind_param('sssss', $fname, $mname, $lname, $vanity, $id);
        }
        $stmt->execute();

        /*
         * If we ran into a database error log it. There is nothing else we can
         * do at this point, we may have
         */ 
        if ($stmt->errno) {
            // TODO: Log this.
            $db->rollback();
            outputResponse('There was a database error, please try again later.', 500);
        }

        // Wrap up and return successfully.
        $db->commit();
        $db->close();
        outputResponse('Profile information successfully updated.', 201);
    }

    /**
     * Reduce both width and height dimensions down to less than or equal to a
     * requested limit. This will maintain the aspect ratio of the original
     * dimensions.
     *
     * @param int $limit  The size to reduce width and height down to.
     * @param int $width  The original width.
     * @param int $height The original height.
     * 
     * @return object An object with the reduced `width` and `height`. 
     */
    private function reduceWithin($limit, $width, $height) {
        if ($width > $limit) {
            $percent = $limit / $width;
            $width   = $limit;
            $height  = round($height * $percent);
        }
        if ($height > $limit) {
            $percent = $limit / $height;
            $height  = $limit;
            $width   = round($width * $percent);
        }
        if ($width > $limit || $height > $limit) {
            return reduceWithin($limit, $width, $height);
        }
        return (object) [
            'height' => $height,
            'width'  => $width
        ];
    }

    /**
     * If the user uploaded a new profile picture save it to the server, update
     * the database, and remove the old picture from the server.
     *
     * @return void
     */
    private function savePicture() {
        
        // If we do not have a picture to save return now.
        if (isset($_FILES['picture'])) {
            if ($_FILES['picture']['error'] != 0 || $_FILES['picture']['size'] === 0) {
                return;
            }
        }

        // If the picture is not a valid type warn the user.
        if (!$this->isPictureValid()) {
            outputResponse('Invalid file type for profile picture.', 400);
        }

        // Connect to the database now.
        global $Database;
        global $User;
        $db = $Database->connect();
        $id = $User->getUserId();

        // Get necessary information.
        $day  = date('j', time());
        $file = $_FILES['picture']['tmp_name'];
        $out  = $Database->newId(22);
        $size = getimagesize($_FILES['picture']['tmp_name']);

        // Build the various paths we need to save the picture.
        $dir    = 'uploads/' . $day;
        $rel    = 'uploads/' . $day . '/' . $out . '.webp';
        $output = abspath('public/uploads/' . $day . '/' . $out . '.webp');

        // Make any missing directory structure we need.
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // Get the pictures data so we can work on it:
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

        // Check the images dimensions and resize it if it's over our limit.
        $width  = imagesx($image);
        $height = imagesy($image);
        if ($width > 500 || $height > 500) {
            $dims  = $this->reduceWithin(500, $width, $height);
            $image = imagescale($image, $dims->width, $dims->height, IMG_BICUBIC);
        }

        // Convert the image into a webp image and save it on the server.
        if (imagewebp($image, $output, 80) === false) {
            // LOG ERROR and don't remove old image.
            return;
        }

        // Update the users database record to point to this new image.
        $db->begin_transaction();
        $stmt = $db->prepare("UPDATE user_profile SET profile_picture=? WHERE id=?;");
        $stmt->bind_param('ss', $rel, $id);
        $stmt->execute();

        if ($stmt->errno) {
            // TODO Log error attempting to save new picture and keep old.
            $db->rollback();
            return;
        }
        $db->commit();

        // Remove the old picture from the server.
        $this->deleteOldPicture();
    }

}