<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Define the Database connection
const SERVER_NAME = "160.153.93.162";
const USER_NAME = "tabletop_fb";
const PASSWORD = "Tabletop1!";
const DB_NAME = "tabletop_wp1";

// Define the Keys used in the array
const KEY_POST_ID = "postId";
const KEY_EDIT_LOCK = "editLock";
const KEY_EDIT_LAST = "editLast";
const KEY_CONTACT_NAME = "contactName";
const KEY_CONTACT_TITLE = "contactTitle";
const KEY_CONTACT_STREET = "contactStreet";
const KEY_CONTACT_CITY = "contactCity";
const KEY_CONTACT_STATE = "contactState";
const KEY_CONTACT_POSTAL_CODE = "contactPostalCode";
const KEY_CONTACT_COUNTRY = "contactCountry";
const KEY_CONTACT_PHONE = "contactPhone";
const KEY_WEBSITE = "website";
const KEY_FACEBOOK = "facebook";
const KEY_TWITTER = "twitter";
const KEY_WP_ATTACHED_FILE = "wpAttachedFile";
const KEY_WP_ATTACHMENT_METADATA = "wpAttachmentMetaData";

$array = array();
$array['success'] = false;

// Access the Post data
$data = (array) json_decode($_POST['data']);

if ($data == null) {
	echo ("ERROR: DATA NULL\n");
	die();
}

// Store the Request to a file
$file = file_get_contents('data.json');
unset($file); // Prevent memory leaks for large json.
file_put_contents('data.json',json_encode($data));

// Create connection
$conn = new mysqli(SERVER_NAME, USER_NAME, PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
	echo json_encode($array);
    die();
}

mysqli_report(MYSQLI_REPORT_ALL);

// Initialize the Restaurant Queries
$deleteQuery = "DELETE FROM wp_postmeta WHERE post_id = ?";
$insertQuery = "INSERT INTO wp_postmeta(post_id, meta_key, meta_value) VALUES 
					(?, '_edit_lock', ?),
					(?, '_edit_last', ?),
					(?, '_contact_name', ?),
					(?, '_contact_title', ?),
					(?, '_contact_street', ?),
					(?, '_contact_city', ?),
					(?, '_contact_state', ?),
					(?, '_contact_postal_code', ?),
					(?, '_contact_country', ?),
					(?, '_contact_phone', ?),
					(?, '_website', ?),
					(?, '_facebook', ?),
					(?, '_twitter', ?)";

$thumbnailQuery = "INSERT INTO wp_postmeta(post_id, meta_key, meta_value) VALUES
					(?, '_wp_attached_file', ?),
					(?, '_wp_attachment_metadata', ?)";

// Delete current post meta data
$stmt = $conn->prepare($deleteQuery);
$stmt->bind_param("i", $data[KEY_POST_ID]);
$stmt->execute();

// Insert new post meta data
$stmt = $conn->prepare($insertQuery);
$stmt->bind_param("isiiisisisisisisisisisisis",
	$data[KEY_POST_ID], $data[KEY_EDIT_LOCK],
	$data[KEY_POST_ID], $data[KEY_EDIT_LAST],
	$data[KEY_POST_ID], $data[KEY_CONTACT_NAME],
	$data[KEY_POST_ID], $data[KEY_CONTACT_TITLE],
	$data[KEY_POST_ID], $data[KEY_CONTACT_STREET],
	$data[KEY_POST_ID], $data[KEY_CONTACT_CITY],
	$data[KEY_POST_ID], $data[KEY_CONTACT_STATE],
	$data[KEY_POST_ID], $data[KEY_CONTACT_POSTAL_CODE],
	$data[KEY_POST_ID], $data[KEY_CONTACT_COUNTRY],
	$data[KEY_POST_ID], $data[KEY_CONTACT_PHONE],
	$data[KEY_POST_ID], $data[KEY_WEBSITE],
	$data[KEY_POST_ID], $data[KEY_FACEBOOK],
	$data[KEY_POST_ID], $data[KEY_TWITTER]
);
$stmt->execute();

/*
$stmt = $conn->prepare($thumbnailQuery);
$stmt->bind_param("isis",
	$data[KEY_POST_ID], $data[KEY_WP_ATTACHED_FILE],
	$data[KEY_POST_ID], $data[KEY_WP_ATTACHMENT_METADATA]
);
$stmt->execute();
*/

$array['success'] = true;
echo json_encode($array);

$conn->close();

// Release memory
unset($data);
unset($array);
unset($stmt);
unset($deleteQuery);
unset($insertQuery);
unset($thumbnailQuery);
unset($conn);

?>