<?php
/**
 * Vendor Document - Add/Edit View (xadmin standard layout)
 */

// Note: x-vendor-document.inc.php is already included by xadmin framework

$id = 0;
$D = array();

if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["documentID"] ?? $_GET["id"] ?? 0);
    $DB->sql = "SELECT d.*, v.legalName, v.vendorCode
                FROM mx_vendor_document d
                JOIN mx_vendor_onboarding v ON d.vendorID = v.vendorID
                WHERE d.documentID = ?";
    $DB->vals = array($id);
    $DB->types = "i";
    $D = $DB->dbRow();
}

$vendorID = intval($_GET["vendorID"] ?? $D["vendorID"] ?? 0);

// Get vendor info if creating new document for specific vendor
$vendorInfo = null;
if ($vendorID > 0) {
    $DB->sql = "SELECT vendorID, legalName, vendorCode FROM mx_vendor_onboarding WHERE vendorID = ? AND status = 1";
    $DB->vals = array($vendorID);
    $DB->types = "i";
    $vendorInfo = $DB->dbRow();
}

// Get vendors list for dropdown if no vendor specified
$vendorOpt = "";
if ($vendorInfo) {
    $vendorOpt = '<option value="' . $vendorInfo["vendorID"] . '" selected>' . htmlentities($vendorInfo["legalName"] . " (" . $vendorInfo["vendorCode"] . ")") . '</option>';
} else {
    $whrArr = array("sql" => "status = 1 AND vendorStatus = 'Approved'", "types" => "", "vals" => array());
    $vendorOpt = getTableDD(["table" => $DB->pre . "vendor_onboarding", "key" => "vendorID", "val" => "legalName", "selected" => ($D["vendorID"] ?? ""), "where" => $whrArr, "order" => "legalName ASC"]);
}

// Document types
$documentTypes = array(
    "GST Certificate",
    "PAN Card",
    "Company Registration",
    "MSME Certificate",
    "Bank Statement",
    "Cancelled Cheque",
    "Trade License",
    "ISO Certificate",
    "Quality Certificate",
    "Other"
);
$docTypeOpt = "";
foreach ($documentTypes as $type) {
    $sel = (($D["documentType"] ?? "") == $type) ? ' selected' : '';
    $docTypeOpt .= '<option value="' . $type . '"' . $sel . '>' . $type . '</option>';
}

// Build form array
$arrForm = array(
    array("type" => "select", "name" => "vendorID", "value" => $vendorOpt, "title" => "Vendor *", "validate" => "required"),
    array("type" => "select", "name" => "documentType", "value" => $docTypeOpt, "title" => "Document Type *", "validate" => "required"),
    array("type" => "text", "name" => "documentName", "value" => $D["documentName"] ?? "", "title" => "Document Name", "info" => '<span class="info">Optional display name</span>'),
    array("type" => "text", "name" => "documentNumber", "value" => $D["documentNumber"] ?? "", "title" => "Document Number", "info" => '<span class="info">Certificate/GST number etc.</span>'),
    array("type" => "text", "name" => "issueDate", "value" => $D["issueDate"] ?? "", "title" => "Issue Date", "params" => array("type" => "date")),
    array("type" => "text", "name" => "expiryDate", "value" => $D["expiryDate"] ?? "", "title" => "Expiry Date", "params" => array("type" => "date"), "info" => '<span class="info">Leave empty if no expiry</span>'),
    array("type" => "file", "name" => "documentFile", "value" => $D["filePath"] ?? "", "title" => ($id ? "File" : "File *"), "info" => '<span class="info">PDF, JPG, PNG, DOC (Max 5MB)</span>'),
    array("type" => "textarea", "name" => "notes", "value" => $D["notes"] ?? "", "title" => "Notes", "params" => array("rows" => 3))
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>

    <?php if ($id && isset($D["verificationStatus"])): ?>
    <div class="wrap-data" style="margin-bottom:10px; padding:15px; background:#f8f9fa; border-radius:4px;">
        <div class="row">
            <div class="col-md-6">
                <strong>Verification Status:</strong> <?php echo getDocumentStatusBadge($D["verificationStatus"]); ?>
                <?php if ($D["verifiedAt"]): ?>
                    <br><small class="text-muted">Verified: <?php echo date("d-M-Y H:i", strtotime($D["verifiedAt"])); ?></small>
                <?php endif; ?>
                <?php if ($D["rejectionReason"]): ?>
                    <br><small class="text-danger"><strong>Reason:</strong> <?php echo htmlentities($D["rejectionReason"]); ?></small>
                <?php endif; ?>
            </div>
            <div class="col-md-6 text-right">
                <?php if ($D["verificationStatus"] == "Pending"): ?>
                <button type="button" class="btn btn-sm btn-success" onclick="verifyDocument()">
                    <i class="fa fa-check"></i> Verify
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="rejectDocument()">
                    <i class="fa fa-times"></i> Reject
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <input type="hidden" name="documentID" value="<?php echo $id; ?>">
        <div class="wrap-form f50">
            <ul class="tbl-form"><?php echo $MXFRM->getForm($arrForm); ?></ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>

    <?php if (!empty($D["filePath"])): ?>
    <div class="wrap-data" style="margin-top:15px;">
        <h5><i class="fa fa-file"></i> Current File</h5>
        <p>
            <a href="<?php echo UPLOADURL; ?>vendor-documents/<?php echo $D["filePath"]; ?>" target="_blank" class="btn btn-outline-primary">
                <i class="fa fa-download"></i> <?php echo htmlentities($D["filePath"]); ?>
            </a>
        </p>
    </div>
    <?php endif; ?>
</div>

<?php if ($id): ?>
<script>
function verifyDocument() {
    if (confirm('Verify this document?')) {
        $.post('<?php echo ADMINURL; ?>vendor-document-list', {
            xAction: 'VERIFY',
            documentID: <?php echo $id; ?>,
            xToken: '<?php echo $_SESSION[SITEURL][CSRF_TOKEN]; ?>'
        }, function(response) {
            alert(response.msg);
            if (response.err == 0) location.reload();
        }, 'json');
    }
}

function rejectDocument() {
    var reason = prompt('Enter reason for rejection:');
    if (reason && reason.trim()) {
        $.post('<?php echo ADMINURL; ?>vendor-document-list', {
            xAction: 'REJECT',
            documentID: <?php echo $id; ?>,
            rejectionReason: reason,
            xToken: '<?php echo $_SESSION[SITEURL][CSRF_TOKEN]; ?>'
        }, function(response) {
            alert(response.msg);
            if (response.err == 0) location.reload();
        }, 'json');
    }
}
</script>
<?php endif; ?>

<script>
// Define required JavaScript variables for form submission
var MODINCURL = '<?php echo ADMINURL; ?>/mod/vendor-document/x-vendor-document.inc.php';
var MODURL = '<?php echo ADMINURL; ?>/mod/vendor-document/';
var ADMINURL = '<?php echo ADMINURL; ?>';
var PAGETYPE = '<?php echo $TPL->pageType ?? "add"; ?>';
</script>
