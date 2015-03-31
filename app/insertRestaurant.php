<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

var_dump($_POST);
die();

function create_slug($string){
   $slug=preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
   return $slug;
}

// Define constants for table rows
const AUTHOR_ID = 1;

// Define the Database connection
const SERVER_NAME = "160.153.93.162";
const USER_NAME = "tabletop_fb";
const PASSWORD = "Tabletop1!";
const DB_NAME = "tabletop_wp1";

// Define the Keys used in the array
const KEY_TITLE = "postTitle";
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

$today = new DateTime("NOW");

$array = array();
$array['success'] = false;

// Access the Post data
$data = $_POST;

if ($data == null) {
	echo ("ERROR: DATA NULL\n");
	die();
}

// Store the Request to a file
$fileData = file_get_contents('data.json');
unset($fileData); // Prevent memory leaks for large json.
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
$deletePostQuery = "DELETE FROM wp_post WHERE post_id = ?";
$insertPostQuery = "INSERT INTO wp_post(post_author post_date, post_date_gmt, post_content, post_title, post_excerpt,
          post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged,
          post_modified, post_modified_gmt, post_content_filtered, post_parent, guid, menu_order,
          post_type, post_mime_type, comment_count)";

$deleteMetadataQuery = "DELETE FROM wp_postmeta WHERE post_id = ?";
$insertMetadataQuery = "INSERT INTO wp_postmeta(post_id, meta_key, meta_value) VALUES
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

$thumbnailMetaDataQuery = "INSERT INTO wp_postmeta(post_id, meta_key, meta_value) VALUES
					(?, '_wp_attached_file', ?),
					(?, '_wp_attachment_metadata', ?)";

// Delete current post meta data
$stmt = $conn->prepare($deleteMetadataQuery);
$stmt->bind_param("i", $data[KEY_POST_ID]);
$stmt->execute();

// Delete current post data
$stmt = $conn->prepare($deletePostQuery);
$stmt->bind_param("i", $data[KEY_POST_ID]);
$stmt->execute();

// Insert new post data
$stmt = $conn->prepare($insertPostQuery);
$stmt->bind_param("isssssssssssssssisissi"
  AUTHOR_ID,
  $today->format("Y-m-d H:i:s"),
  $today->format("Y-m-d H:i:s"),
  null,
  $data[KEY_TITLE],
  null,
  "publish",
  "open",
  "open",
  null,
  create_slug($data[KEY_TITLE],
  null,
  null,
  $today->format("Y-m-d H:i:s"),
  $today->format("Y-m-d H:i:s"),
  null,
  0,
  "http://www.tabletopdine.com/dev/",
  0,
  "wg_merchant",
  null,
  0
);
$stmt->execute();

// Insert new post data for revision row
$stmt = $conn->prepare($insertPostQuery);
$stmt->bind_param("isssssssssssssssisissi"
  AUTHOR_ID,
  $today->format("Y-m-d H:i:s"),
  $today->format("Y-m-d H:i:s"),
  null,
  $data[KEY_TITLE],
  null,
  "inherit",
  "open",
  "open",
  null,
  "revision-v1",
  null,
  null,
  $today->format("Y-m-d H:i:s"),
  $today->format("Y-m-d H:i:s"),
  null,
  0,
  "http://www.tabletopdine.com/dev/revision-v-1",
  0,
  "wg_merchant",
  null,
  0
);
$stmt->execute();

// Insert new post meta data
$stmt = $conn->prepare($insertMetadataQuery);
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
$stmt = $conn->prepare($thumbnailMetadataQuery);
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
unset($deleteMetadataQuery);
unset($insertMetadataQuery);
unset($thumbnailMetadataQuery);
unset($conn);

?>
