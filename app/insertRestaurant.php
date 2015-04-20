<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// ini_set('display_errors',1);
// ini_set('display_startup_errors',1);
// error_reporting(-1);

var_dump($_POST);

function create_slug($string){
   $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
   return $slug;
}

// Define the Database connection
const SERVER_NAME = "160.153.93.162";
const USER_NAME = "tabletop_fb";
const PASSWORD = "Tabletop1!";
const DB_NAME = "tabletop_wp1";

// Define the Keys used in the array
const KEY_DESCRIPTION = "description";
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

// Define the standard fields
$authorId = 2;
$empty = "";
$zero = 0;

$id = 0;

$facebook = "http://www.facebook.com";
$twitter = "http://www.twitter.com";

date_default_timezone_set("America/Phoenix");
$today = (new DateTime("NOW"))->format("Y-m-d H:i:s");
date_default_timezone_set("Europe/London");
$todayGmt = (new DateTime("NOW"))->format("Y-m-d H:i:s");

$array = array();
$array['success'] = false;

// Access the Post data
$data = $_POST;

if (empty($data)) {
	echo "ERROR: DATA NULL\n";
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
  echo "ERROR - Could not connect to database\n";
  die();
}

mysqli_report(MYSQLI_REPORT_ALL);

// Initialize the Restaurant Queries
$selectIdQuery = "SELECT MAX(ID) FROM wp_posts";
$deletePostQuery = "DELETE FROM wp_posts WHERE ID = ?";
$insertPostQuery = "INSERT INTO wp_posts(post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt,
          post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged,
          post_modified, post_modified_gmt, post_content_filtered, post_parent, guid, menu_order,
          post_type, post_mime_type, comment_count) VALUES
          (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

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

if ($result = $conn->query($selectIdQuery)) {
  $row = $result->fetch_array(MYSQLI_NUM);

  if (is_numeric($row[0])) {
    $id = $row[0] + 1;
  }
} else {
  echo "ERROR - Could not retrieve ID from the wp_posts table\n";
  die();
}

// Delete current post meta data
$stmt = $conn->prepare($deleteMetadataQuery);
$stmt->bind_param("i", $id);
$stmt->execute();

// Delete current post data
$stmt = $conn->prepare($deletePostQuery);
$stmt->bind_param("i", $id);
$stmt->execute();

// Insert new post data
$stmt = $conn->prepare($insertPostQuery);
$stmt->bind_param("isssssssssssssssisissi",
  $authorId,
  $today,
  $todayGmt,
  $data[KEY_DESCRIPTION],
  $data[KEY_TITLE],
  $empty,
  $a = "publish",
  $b = "open",
  $c = "open",
  $empty,
  create_slug($data[KEY_TITLE]),
  $empty,
  $empty,
  $today,
  $todayGmt,
  $empty,
  $zero,
  $d = ("http://www.tabletopdine.com/dev/?post_type=wg_merchant&#038;p=" . $id),
  $zero,
  $e = "wg_merchant",
  $empty,
  $zero
);
$stmt->execute();

// Insert new post data for revision row
$stmt = $conn->prepare($insertPostQuery);
$stmt->bind_param("isssssssssssssssisissi",
  $authorId,
  $today,
  $todayGmt,
  $empty,
  $data[KEY_TITLE],
  $empty,
  $a = "inherit",
  $b = "open",
  $c = "open",
  $empty,
  $d = ($id . "-revision-v1"),
  $empty,
  $empty,
  $today,
  $todayGmt,
  $empty,
  $id,
  $e = ("http://www.tabletopdine.com/dev/" . $id . "-revision-v-1"),
  $zero,
  $f = "revision",
  $empty,
  $zero
);
$stmt->execute();

// Insert new post meta data
$stmt = $conn->prepare($insertMetadataQuery);
$stmt->bind_param("isiiisisisisisisisisisisis",
	$id, $data[KEY_EDIT_LOCK],
	$id, $data[KEY_EDIT_LAST],
	$id, $data[KEY_CONTACT_NAME],
	$id, $data[KEY_CONTACT_TITLE],
	$id, $data[KEY_CONTACT_STREET],
	$id, $data[KEY_CONTACT_CITY],
	$id, $data[KEY_CONTACT_STATE],
	$id, $data[KEY_CONTACT_POSTAL_CODE],
	$id, $data[KEY_CONTACT_COUNTRY],
	$id, $data[KEY_CONTACT_PHONE],
	$id, $data[KEY_WEBSITE],
	$id, ($data[KEY_FACEBOOK] != null && is_string($data[KEY_FACEBOOK]) && strlen($data[KEY_FACEBOOK]) > 0) ? $data[KEY_FACEBOOK] : $facebook,
	$id, ($data[KEY_TWITTER] != null && is_string($data[KEY_TWITTER]) && strlen($data[KEY_TWITTER]) > 0) ? $data[KEY_TWITTER] : $twitter
);
$stmt->execute();

// $stmt = $conn->prepare($thumbnailMetadataQuery);
// $stmt->bind_param("isis",
// 	$id, $data[KEY_WP_ATTACHED_FILE],
// 	$id, $data[KEY_WP_ATTACHMENT_METADATA]
// );
// $stmt->execute();

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
