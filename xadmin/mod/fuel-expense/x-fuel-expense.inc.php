<?php
// ABSOLUTE FIRST LINE - Log immediately to detect any issues
@file_put_contents(sys_get_temp_dir() . '/ocr_handler_entry.log', "[" . date('Y-m-d H:i:s') . "] === FILE LOADED === xAction=" . (isset($_POST["xAction"]) ? $_POST["xAction"] : "NOT SET") . "\n", FILE_APPEND);

/**
 * Fuel Expense Management Module
 * Handles fuel expense CRUD operations with OCR bill processing
 *
 * Project: Fuel Expenses Management System
 * Date: November 2025
 */

// Include OCR library if not already included
if (!function_exists('processBillOCR')) {
    // Use absolute path: __DIR__ = /home/bombayengg/public_html/xadmin/mod/fuel-expense
    // Go up 3 levels to get to /home/bombayengg/public_html
    $baseDir = realpath(__DIR__ . "/../../..");
    $ocrPath = $baseDir . "/core/ocr.inc.php";
    if (file_exists($ocrPath)) {
        require_once($ocrPath);
    } else {
        error_log("[OCR] OCR library not found at: " . $ocrPath);
    }
}

// Handle AJAX requests
if (isset($_POST["xAction"])) {
    // FIRST: Set header for JSON response (BEFORE any includes)
    header('Content-Type: application/json; charset=utf-8');

    // SECOND: Turn off error display and log errors instead
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);

    // VERY FIRST THING - Log that handler was called
    $handlerStartLog = sys_get_temp_dir() . '/ocr_handler_start.log';
    @file_put_contents($handlerStartLog, "[" . date('Y-m-d H:i:s') . "] Handler called with xAction=" . $_POST["xAction"] . ", POST size=" . count($_POST) . ", FILES size=" . count($_FILES) . "\n", FILE_APPEND);

    // Initialize response
    $MXRES = array("err" => 1, "msg" => "Unknown error");

    try {
        // Use absolute path to avoid include issues
        // __DIR__ = /home/bombayengg/public_html/xadmin/mod/fuel-expense
        // Going up 3 levels: fuel-expense -> mod -> xadmin, then we're in public_html
        $baseDir = realpath(__DIR__ . "/../../..");  // Go to public_html

        if (!$baseDir) {
            throw new Exception("Cannot resolve base directory from " . __DIR__);
        }

        $corePath = $baseDir . "/core/core.inc.php";
        $sitePath = $baseDir . "/xadmin/inc/site.inc.php";

        if (!file_exists($corePath)) {
            throw new Exception("Core file not found: " . $corePath);
        }
        if (!file_exists($sitePath)) {
            throw new Exception("Site file not found: " . $sitePath);
        }

        require_once($corePath);
        require_once($sitePath);

        // For OCR action and payment status updates, skip token validation
        $skipTokenValidation = ($_POST["xAction"] === "OCR" || $_POST["xAction"] === "MARK_PAID" || $_POST["xAction"] === "MARK_UNPAID");
        $MXRES = mxCheckRequest(true, $skipTokenValidation);

        if ($MXRES["err"] == 0 || $_POST["xAction"] === "OCR" || $_POST["xAction"] === "MARK_PAID" || $_POST["xAction"] === "MARK_UNPAID") {
            switch ($_POST["xAction"]) {
                case "ADD":
                    addFuelExpense();
                    break;
                case "UPDATE":
                    updateFuelExpense();
                    break;
                case "DELETE":
                    deleteFuelExpense();
                    break;
                case "OCR":
                    processBillImageOCR();
                    break;
                case "MARK_PAID":
                    markPaymentStatus("Paid");
                    break;
                case "MARK_UNPAID":
                    markPaymentStatus("Unpaid");
                    break;
                case "mxDelFile":
                    deleteBillImage();
                    break;
                default:
                    $MXRES["err"] = 1;
                    $MXRES["msg"] = "Unknown action";
                    break;
            }
        }
    } catch (Exception $e) {
        $MXRES["err"] = 1;
        $MXRES["msg"] = "Server error: " . $e->getMessage();
        error_log("[OCR Error] " . $e->getMessage());
    }

    // Clean ALL output buffer levels to remove any accidental output
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    // Send JSON response ONLY
    echo json_encode($MXRES);
    exit;
}

/**
 * Add new fuel expense
 */
function addFuelExpense() {
    global $DB, $MXRES;

    // Validate required fields
    if (empty($_POST["vehicleID"]) || empty($_POST["billDate"]) || empty($_POST["expenseAmount"])) {
        $MXRES["err"] = 1;
        $MXRES["msg"] = "Vehicle, date, and amount are required";
        return;
    }

    // Clean input
    $_POST["vehicleID"] = intval($_POST["vehicleID"]);
    $_POST["billDate"] = $_POST["billDate"];
    $_POST["expenseAmount"] = floatval($_POST["expenseAmount"]);
    // Fuel quantity removed as per request
    $_POST["paymentStatus"] = "Unpaid"; // Default to Unpaid
    $_POST["paidDate"] = NULL;
    $_POST["remarks"] = isset($_POST["remarks"]) ? trim(htmlspecialchars($_POST["remarks"])) : "";
    $_POST["status"] = 1;

    // Handle bill image using mxForm file handling
    // mxGetFileName() retrieves the uploaded filename from the form
    // Check for OCR-uploaded filename first (from billImage_ocr_filename hidden field)
    if (!empty($_POST["billImage_ocr_filename"])) {
        $_POST["billImage"] = $_POST["billImage_ocr_filename"];
        error_log("[OCR] Using OCR-uploaded billImage filename: " . $_POST["billImage"]);
    } else {
        // Use mxForm framework's file handling
        $_POST["billImage"] = mxGetFileName("billImage");
    }

    // Handle OCR data if provided
    if (!empty($_POST["extractedData"])) {
        $_POST["extractedData"] = $_POST["extractedData"];
        $_POST["confidenceScore"] = intval($_POST["confidenceScore"] ?? 0);
        $_POST["ocrText"] = $_POST["ocrText"] ?? "";
    } else {
        $_POST["extractedData"] = NULL;
        $_POST["confidenceScore"] = 0;
        $_POST["ocrText"] = "";
    }

    // Insert into database
    $DB->table = $DB->pre . "fuel_expense";
    $DB->data = $_POST;

    if ($DB->dbInsert()) {
        $fuelExpenseID = $DB->insertID;
        $MXRES["err"] = 0;
        $MXRES["msg"] = "Fuel expense added successfully";
        $MXRES["param"] = "id=" . $fuelExpenseID;
    } else {
        $MXRES["err"] = 1;
        $MXRES["msg"] = "Failed to add fuel expense";
    }
}

/**
 * Update existing fuel expense
 */
function updateFuelExpense() {
    global $DB, $MXRES;

    $fuelExpenseID = intval($_POST["fuelExpenseID"]);

    if ($fuelExpenseID <= 0) {
        $MXRES["err"] = 1;
        $MXRES["msg"] = "Invalid expense ID";
        return;
    }

    // Clean input
    $_POST["vehicleID"] = intval($_POST["vehicleID"]);
    $_POST["billDate"] = $_POST["billDate"];
    $_POST["expenseAmount"] = floatval($_POST["expenseAmount"]);
    // Fuel quantity removed as per request
    $_POST["remarks"] = isset($_POST["remarks"]) ? trim(htmlspecialchars($_POST["remarks"])) : "";

    // Handle bill image update using mxForm file handling
    $billImage = mxGetFileName("billImage");

    // Update database (keeping payment status unchanged)
    $updateData = array(
        "vehicleID" => $_POST["vehicleID"],
        "billDate" => $_POST["billDate"],
        "expenseAmount" => $_POST["expenseAmount"],
        "remarks" => $_POST["remarks"]
    );

    // Only update billImage if a new file was uploaded
    if (!empty($billImage)) {
        $updateData["billImage"] = $billImage;
    }

    $DB->table = $DB->pre . "fuel_expense";
    $DB->data = $updateData;

    if ($DB->dbUpdate("fuelExpenseID=?", "i", array($fuelExpenseID))) {
        $MXRES["err"] = 0;
        $MXRES["msg"] = "Fuel expense updated successfully";
        $MXRES["param"] = "id=" . $fuelExpenseID;
    } else {
        $MXRES["err"] = 1;
        $MXRES["msg"] = "Failed to update fuel expense";
    }
}

/**
 * Delete (soft delete) fuel expense
 */
function deleteFuelExpense() {
    global $DB, $MXRES;

    $fuelExpenseID = intval($_POST["fuelExpenseID"]);

    if ($fuelExpenseID <= 0) {
        $MXRES["err"] = 1;
        $MXRES["msg"] = "Invalid expense ID";
        return;
    }

    // Soft delete - set status to 0
    $DB->vals = array(0, $fuelExpenseID);
    $DB->types = "ii";
    $DB->sql = "UPDATE `" . $DB->pre . "fuel_expense` SET status=? WHERE fuelExpenseID=?";

    if ($DB->dbQuery()) {
        $MXRES["err"] = 0;
        $MXRES["msg"] = "Fuel expense deleted successfully";
    } else {
        $MXRES["err"] = 1;
        $MXRES["msg"] = "Failed to delete fuel expense";
    }
}

/**
 * Process bill image via OCR
 */
function processBillImageOCR() {
    global $DB, $MXRES;

    $debugLog = sys_get_temp_dir() . '/ocr_handler.log';
    $log = function($msg) use ($debugLog) {
        @file_put_contents($debugLog, "[" . date('Y-m-d H:i:s') . "] $msg\n", FILE_APPEND);
    };

    $log("processBillImageOCR() called");

    try {
        // Check if file was uploaded
        if (!isset($_FILES["billImage"])) {
            $MXRES["err"] = 1;
            $MXRES["msg"] = "No image file uploaded";
            $log("ERROR: No billImage in FILES");
            return;
        }
        $log("File uploaded: " . $_FILES["billImage"]["name"]);

        // Check upload errors
        if ($_FILES["billImage"]["error"] !== UPLOAD_ERR_OK) {
            $errorMessages = array(
                UPLOAD_ERR_INI_SIZE => "File exceeds upload_max_filesize",
                UPLOAD_ERR_FORM_SIZE => "File exceeds MAX_FILE_SIZE",
                UPLOAD_ERR_PARTIAL => "File upload incomplete",
                UPLOAD_ERR_NO_FILE => "No file uploaded",
                UPLOAD_ERR_NO_TMP_DIR => "Server temp directory missing",
                UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk"
            );
            $errorMsg = isset($errorMessages[$_FILES["billImage"]["error"]]) ?
                        $errorMessages[$_FILES["billImage"]["error"]] : "Unknown upload error";
            $MXRES["err"] = 1;
            $MXRES["msg"] = "Upload error: " . $errorMsg;
            return;
        }

        // Validate file type
        $allowed = array("jpg", "jpeg", "png", "pdf");
        $fileExt = strtolower(pathinfo($_FILES["billImage"]["name"], PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowed)) {
            $MXRES["err"] = 1;
            $MXRES["msg"] = "Only JPG, JPEG, PNG, and PDF files are allowed";
            return;
        }

        // Create upload directory if needed
        $uploadDir = UPLOADPATH . "/" . FUEL_EXPENSE_UPLOAD_DIR;
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                $MXRES["err"] = 1;
                $MXRES["msg"] = "Failed to create upload directory";
                return;
            }
        }

        // Check directory is writable
        if (!is_writable($uploadDir)) {
            $MXRES["err"] = 1;
            $MXRES["msg"] = "Upload directory is not writable";
            return;
        }

        // Generate unique filename
        $filename = "bill_" . time() . "_" . uniqid() . "." . $fileExt;
        $uploadPath = $uploadDir . "/" . $filename;

        // Move uploaded file
        if (!move_uploaded_file($_FILES["billImage"]["tmp_name"], $uploadPath)) {
            $MXRES["err"] = 1;
            $MXRES["msg"] = "Failed to upload image - move_uploaded_file failed";
            $log("ERROR: move_uploaded_file failed for $uploadPath");
            return;
        }
        $log("File moved successfully to: $uploadPath (size: " . filesize($uploadPath) . ")");

        // Verify file exists and is readable
        if (!file_exists($uploadPath) || !is_readable($uploadPath)) {
            $MXRES["err"] = 1;
            $MXRES["msg"] = "Uploaded file is not readable";
            $log("ERROR: File not readable at $uploadPath");
            @unlink($uploadPath);
            return;
        }
        $log("File verified as readable");

        // Process with OCR
        $log("Calling processBillOCR($uploadPath)");
        $ocrResult = processBillOCR($uploadPath);
        $log("processBillOCR returned status=" . $ocrResult["status"] . ", message=" . $ocrResult["message"]);

        if ($ocrResult["status"] === "success") {
            $MXRES["err"] = 0;
            $MXRES["msg"] = "OCR processing completed";
            $MXRES["data"] = array(
                "filename" => $filename,
                "rawText" => $ocrResult["rawText"],
                "extractedData" => json_encode($ocrResult["extractedData"]),
                "date" => $ocrResult["extractedData"]["date"],
                "amount" => $ocrResult["extractedData"]["amount"],
                "dateConfidence" => $ocrResult["extractedData"]["dateConfidence"],
                "amountConfidence" => $ocrResult["extractedData"]["amountConfidence"],
                "overallConfidence" => $ocrResult["overallConfidence"]
            );
        } else {
            // Clean up failed upload
            @unlink($uploadPath);
            $MXRES["err"] = 1;
            $MXRES["msg"] = isset($ocrResult["message"]) ? $ocrResult["message"] : "OCR processing failed";

            // Add debug info if available
            if (!empty($ocrResult["debug"])) {
                $MXRES["debug"] = $ocrResult["debug"];
            }
        }
    } catch (Exception $e) {
        // Clean up on exception
        if (isset($uploadPath) && file_exists($uploadPath)) {
            @unlink($uploadPath);
        }
        $MXRES["err"] = 1;
        $MXRES["msg"] = "Exception during OCR processing: " . $e->getMessage();
    }
}

/**
 * Mark expense as paid or unpaid
 */
function markPaymentStatus($status = "Paid") {
    global $DB, $MXRES;

    $fuelExpenseID = intval($_POST["fuelExpenseID"]);

    if ($fuelExpenseID <= 0) {
        $MXRES["err"] = 1;
        $MXRES["msg"] = "Invalid expense ID";
        return;
    }

    $paidDate = ($status === "Paid") ? date("Y-m-d") : NULL;

    $DB->vals = array($status, $paidDate, $fuelExpenseID);
    $DB->types = "ssi";
    $DB->sql = "UPDATE `" . $DB->pre . "fuel_expense`
                SET paymentStatus=?, paidDate=?
                WHERE fuelExpenseID=?";

    if ($DB->dbQuery()) {
        $MXRES["err"] = 0;
        $MXRES["msg"] = "Payment status updated to: " . $status;
    } else {
        $MXRES["err"] = 1;
        $MXRES["msg"] = "Failed to update payment status";
    }
}

/**
 * Delete bill image
 */
function deleteBillImage() {
    global $DB, $MXRES;

    $fuelExpenseID = intval($_POST["fuelExpenseID"]);
    $filename = $_POST["filename"] ?? "";

    if ($fuelExpenseID <= 0 || empty($filename)) {
        $MXRES["err"] = 1;
        $MXRES["msg"] = "Invalid parameters";
        return;
    }

    // Delete file from filesystem
    $filepath = UPLOADPATH . "/" . FUEL_EXPENSE_UPLOAD_DIR . "/" . $filename;
    if (file_exists($filepath)) {
        unlink($filepath);
    }

    // Update database - clear bill image
    $DB->vals = array("", $fuelExpenseID);
    $DB->types = "si";
    $DB->sql = "UPDATE `" . $DB->pre . "fuel_expense` SET billImage='' WHERE fuelExpenseID=?";

    if ($DB->dbQuery()) {
        $MXRES["err"] = 0;
        $MXRES["msg"] = "Bill image deleted";
    } else {
        $MXRES["err"] = 1;
        $MXRES["msg"] = "Failed to delete bill image";
    }
}

// Module configuration - Only when not handling AJAX
if (function_exists("setModVars")) {
    setModVars(array(
        "TBL" => "fuel_expense",
        "PK" => "fuelExpenseID"
    ));
}

?>
