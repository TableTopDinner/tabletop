<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

var_dump($_POST);
die();

// Define the Database connection
const SERVER_NAME = "160.153.93.162";
const USER_NAME = "tabletop_fb";
const PASSWORD = "Tabletop1!";
const DB_NAME = "tabletop_wp1";

// Define the Keys used in the array
const KEY_TITLE = "postTitle";
const KEY_POST_ID = "postId";
const KEY_EXPIRATION_DATE = "expirationDate";
const KEY_BASE_PRICE = "basePrice";
const KEY_DYNAMIC_PRICE = "dynamicPrice";
const KEY_MIN_PURCHASES = "minPurchases";
const KEY_MAX_PURCHASES = "maxPurchases";
const KEY_MAX_PURCHASES_PER_USER = "maxPurchasesPerUser";
const KEY_VALUE = "value";
const KEY_AMOUNT_SAVED = "amountSaved";
const KEY_HIGHLIGHTS = "highlights";
const KEY_FINE_PRINT = "finePrint";
const KEY_VOUCHER_EXPIRATION_DATE = "voucherExpirationDate";
const KEY_VOUCHER_HOW_TO_USE = "voucherHowToUse";
const KEY_VOUCHER_MAP = "voucherMap";
const KEY_VOUCHER_SERIAL_NUMBER = "voucherSerialNumber";
const KEY_MERCHANT_ID = "merchantId";
const KEY_VOUCHER_LOCATIONS = "voucherLocations";
const KEY_WP_ATTACHED_FILE = "wpAttachedFile";
const KEY_WP_ATTACHMENT_METADATA = "wpAttachmentMetaData";
const KEY_THUMBNAIL_ID = "thumbnailId";
const KEY_NUMBER_OF_PURCHASES = "numberOfPurchases";
const KEY_EDIT_LOCK = "editLock";
const KEY_EDIT_LAST = "editLast";
const KEY_REDIRECT_URL = "redirectUrl";
const KEY_TAXABLE = "taxable";
const KEY_TAX = "tax";
const KEY_SHIPPING = "shipping";
const KEY_SHIPPING_MODE = "shippingMode";
const KEY_SHIPPING_DYN_PRICE = "shippingDynPrice";
const KEY_RSS_EXCERPT = "rssExcerpt";
const KEY_VOUCHER_ID_PREFIX = "voucherIdPrefix";
const KEY_VOUCHER_LOGO = "voucherLogo";
const KEY_CAPTURE_BEFORE_EXPIRATION = "captureBeforeExpiration";
const KEY_PREVIEW_PRIVATE_KEY = "previewPrivateKey";
const KEY_FEATURED_CONTENT = "featuredContent";

// Define the Standard fields
const TAX_RATE = "standard-rate";
const SHIPPING_MODE = "flat-rate-5";
const SHIPPING_DYN_PRICE = "a:0:{}";

$array = array();
$array['success'] = false;

// Access the Post data
$data = $_POST;

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

// Initialize the Event Queries
$deletePostQuery = "DELETE FROM wp_post WHERE post_id = ?";
$insertPostQuery = "INSERT INTO wp_post(post_author post_date, post_date_gmt, post_content, post_title, post_excerpt,
          post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged,
          post_modified, post_modified_gmt, post_content_filtered, post_parent, guid, menu_order,
          post_type, post_mime_type, comment_count)";

$deleteQuery = "DELETE FROM wp_postmeta WHERE post_id = ?";
$insertQuery = "INSERT INTO wp_postmeta(post_id, meta_key, meta_value) VALUES
					(?, '_expiration_date', ?),
					(?, '_base_price', ?),
					(?, '_dynamic_price', ?),
					(?, '_min_purchases', ?),
					(?, '_max_purchases', ?),
					(?, '_max_purchases_per_user', ?),
					(?, '_value', ?),
					(?, '_amount_saved', ?),
					(?, '_highlights', ?),
					(?, '_fine_print', ?),
					(?, '_voucher_expiration_date', ?),
					(?, '_voucher_how_to_use', ?),
					(?, '_voucher_map', ?),
					(?, '_voucher_serial_number', ?),
					(?, '_merchant_id', ?),
					(?, '_voucher_locations', ?),
					(?, '_thumbnail_id', ?)
					(?, '_number_of_purchases', ?),
					(?, '_edit_lock', ?),
					(?, '_edit_last', ?),
					(?, '_redirect_url', ?),
					(?, '_taxable', ?),
					(?, '_tax', ?),
					(?, '_shipping', ?),
					(?, '_shipping_mode', ?),
					(?, '_shipping_dyn_price', ?),
					(?, '_rss_excerpt', ?),
					(?, '_voucher_id_prefix', ?),
					(?, '_voucher_logo', ?),
					(?, '_capture_before_expiration', ?),
					(?, '_preview_private_key', ?),
					(?, '_featured_content', ?)";

$thumbnailQuery = "INSERT INTO wp_postmeta(post_id, meta_key, meta_value) VALUES
					(?, '_wp_attached_file', ?),
					(?, '_wp_attachment_metadata', ?)";

// Delete current post meta data
$stmt = $conn->prepare($deleteQuery);
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
$stmt = $conn->prepare($insertQuery);
$stmt->bind_param("isididiiiiiiididisisisisisisiiisiiiiisisisisisisisisisisisisisis",
	$data[KEY_POST_ID], $data[KEY_EXPIRATION_DATE],
	$data[KEY_POST_ID], $data[KEY_BASE_PRICE],
	$data[KEY_POST_ID], $data[KEY_DYNAMIC_PRICE],
	$data[KEY_POST_ID], $data[KEY_MIN_PURCHASES],
	$data[KEY_POST_ID], $data[KEY_MAX_PURCHASES],
	$data[KEY_POST_ID], $data[KEY_MAX_PURCHASES_PER_USER],
	$data[KEY_POST_ID], $data[KEY_VALUE],
	$data[KEY_POST_ID], $data[KEY_AMOUNT_SAVED],
	$data[KEY_POST_ID], $data[KEY_HIGHLIGHTS],
	$data[KEY_POST_ID], $data[KEY_FINE_PRINT],
	$data[KEY_POST_ID], $data[KEY_VOUCHER_EXPIRATION_DATE],
	$data[KEY_POST_ID], $data[KEY_VOUCHER_HOW_TO_USE],
	$data[KEY_POST_ID], $data[KEY_VOUCHER_MAP],
	$data[KEY_POST_ID], $data[KEY_VOUCHER_SERIAL_NUMBER],
	$data[KEY_POST_ID], $data[KEY_MERCHANT_ID],
	$data[KEY_POST_ID], $data[KEY_VOUCHER_LOCATIONS],
	$data[KEY_POST_ID], $data[KEY_THUMBNAIL_ID],
	$data[KEY_POST_ID], $data[KEY_NUMBER_OF_PURCHASES],
	$data[KEY_POST_ID], $data[KEY_EDIT_LOCK],
	$data[KEY_POST_ID], $data[KEY_EDIT_LAST],
	$data[KEY_POST_ID], $data[KEY_REDIRECT_URL],
	$data[KEY_POST_ID], $data[KEY_TAXABLE],
	$data[KEY_POST_ID], TAX_RATE,
	$data[KEY_POST_ID], $data[KEY_SHIPPING],
	$data[KEY_POST_ID], SHIPPING_MODE,
	$data[KEY_POST_ID], SHIPPING_DYN_PRICE,
	$data[KEY_POST_ID], $data[KEY_RSS_EXCERPT],
	$data[KEY_POST_ID], $data[KEY_VOUCHER_ID_PREFIX],
	$data[KEY_POST_ID], $data[KEY_VOUCHER_LOGO],
	$data[KEY_POST_ID], $data[KEY_CAPTURE_BEFORE_EXPIRATION],
	$data[KEY_POST_ID], $data[KEY_FEATURED_CONTENT]
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
