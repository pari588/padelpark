<?php

// Start: To if user change category title from category modules regarding title will also update in contact us module.

function updateContactUsCategory($oldCategoryTitle = "", $categoryTitle = "", $modType = 0)

{

    global $DB;

    $DB->vals = array($categoryTitle, $oldCategoryTitle, $modType);

    $DB->types = "ssi";

    $DB->sql = "UPDATE " . $DB->pre . "contact_us SET categoryTitle=? WHERE categoryTitle=? AND modType=?";

    $DB->dbQuery();
}

//End.

// Start: To if user change product title from product modules regarding title will also update in contact us module.

function updateContactUsProduct($oldProductTitle = "", $productTitle = "", $modType = 0)

{

    global $DB;

    $DB->vals = array($productTitle, $oldProductTitle, $modType);

    $DB->types = "ssi";

    $DB->sql = "UPDATE " . $DB->pre . "contact_us SET productTitle=? WHERE productTitle=? AND modType=?";

    $DB->dbQuery();
}

// End.



// Start: To Get next no.

function getNextNo($param = array("tbl" => "voucher", "noCol" => "voucherNo", "dtCol" => "voucherDate", "prefix" => ""))

{

    global $DB;

    $iNo = 0;

    $arrYr = getFinancialYear();

    $yrFrom = $arrYr["start"] . "-04-01";

    $yrTo = $arrYr["end"] . "-03-31";



    $DB->sql = "SELECT " . $param["noCol"] . " FROM `" . $DB->pre . $param["tbl"] . "` WHERE " . $param["dtCol"] . " BETWEEN '$yrFrom' AND '$yrTo' ORDER BY " . $param["dtCol"] . " DESC LIMIT 1";

    $DB->dbRow();

    if ($DB->numRows > 0) {

        $arr = explode('/', $DB->row["voucherNo"] ?? 0);

        $iNo = end($arr);
    }



    $newID = str_pad(++$iNo, 3, "0", STR_PAD_LEFT);
    $str =   ($param["prefix"] ?? "") . '/' . $arrYr["start"] . '-' . $arrYr["end"] . '/' . $newID;
    return $str;


    return ($iNo + 1);
}

// End.

//Start: To get finacial year.

function getFinancialYear()

{

    $currMon = date("m");

    if ($currMon > 3 && date("Y-m-d H:i:s") >= (date("Y")) . "-04-01 00:00:00") {

        $fStart = date("y");

        $fEnd   = (date("y") + 1);
    } else {

        $fStart = (date("y") - 1);

        $fEnd   = date("y");
    }

    return array("start" => $fStart, "end" => $fEnd);
}

// End.

// Start: To check availabale balance amount in petty cash.

function balanceAmountcheck($amount = 0, $availableAmtFlag = 0)

{

    global $DB;
    $transactionType = $_POST['transactionType'] ?? 1;
    $response['count'] = 0;

    $response['msg'] = "";

    if ($transactionType == 2 || $availableAmtFlag == 1) {

        $DB->vals = array(1);

        $DB->types = "i";

        $DB->sql = "SELECT balanceAmount FROM `" . $DB->pre . "petty_cash_book` WHERE status=? ORDER BY pettyCashBookID DESC LIMIT 1";

        $result = $DB->dbRow();

        if ((isset($result['balanceAmount']) && $amount > $result['balanceAmount']) || $availableAmtFlag == 1) {

            $response['count'] = 1;

            $response['balanceAmount'] = $result['balanceAmount'] ?? 0;

            $response['msg'] = "Debit Amount is greater than Balance";
        }
    }

    return $response;
}

//End.

//Start: To update pettycash balance.

function updatePettycashBalance()
{
    global $DB;
    $DB->vals = array(1);
    $DB->types = "i";
    $DB->sql = "SELECT pettyCashBookID,transactionType,amount FROM `" . $DB->pre . "petty_cash_book` WHERE status=? ORDER BY transactionDate ASC,pettyCashBookID ASC";
    $res = $DB->dbRows();
    $balanceAmount = 0;
    if ($DB->numRows > 0) {
        foreach ($res as $value) {
            if ($value['transactionType'] == 1) {
                $balanceAmount += $value['amount'];
            }
            if ($value['transactionType'] == 2) {
                $balanceAmount -= $value['amount'];
            }
            $pettyCashBookID = $value['pettyCashBookID'];
            $DB->table = $DB->pre . "petty_cash_book";
            $DB->data = array("balanceAmount" => $balanceAmount);
            $DB->dbUpdate("pettyCashBookID=?", "i", array($pettyCashBookID));
        }
    }
}

//End.



//Function for Send Email

// function sendEmail($emailData = array())

// {

//     // print_r(($emailData));

//     // exit;

//     require_once(ABSLIBPATH . "lib/phpmailer/MXPHPMailer.php");

//     $mail = new PHPMailer();

//     $mail->isSMTP();



//     $mail->SMTPDebug = false; //true

//     $mail->Host     = HOSTNAME;  // Specify main and backup SMTP servers

//     $mail->SMTPAuth = true;                               // Enable SMTP authentication

//     $mail->Username = USERNAME;                 // SMTP username
//     $mail->Password = PASSWORD;                // SMTP password

//     $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted

//     $mail->Port = 587;



//     if (isset($emailData['fromEmail']) && isset($emailData['fromName'])) {

//         $mail->From = $emailData['fromEmail'];

//         $mail->FromName = $emailData['fromName'];
//     } else {
//         $mail->FromName = 'Bombay Engineering Syndicate';
//         $mail->From = 'info@bombayengg.net';
//     }

//     $mail->SetFrom($mail->From, $mail->FromName);



//     if (isset($emailData['toEmail']) && sizeof($emailData['toEmail']) > 0) {

//         foreach ($emailData['toEmail'] as $email => $name) {

//             $mail->AddAddress($email, $name);
//         }
//     }



//     if (isset($emailData['ccEmail']) && sizeof($emailData['ccEmail']) > 0) {

//         // print_r($emailData['ccEmail']);

//         foreach ($emailData['ccEmail'] as $email => $name) {

//             // echo  $email;

//             $mail->AddCC($email, $name);
//         }
//     }

//     //  exit;



//     if (isset($emailData['bccEmail']) && sizeof($emailData['bccEmail']) > 0) {

//         foreach ($emailData['bccEmail'] as $email => $name) {

//             $mail->AddBCC($email, $name);
//         }
//     }



//     if (isset($emailData['attachment']) && $emailData['attachment'] != "") {

//         $attachment = explode(",", $emailData['attachment']);

//         foreach ($attachment as $file) {

//             $mail->AddAttachment($file);
//         }
//     }



//     $mail->Subject = $emailData['subject'];

//     $mail->Body    = $emailData['body'];

//     $mail->ContentType = "text/html";

//     //   $mail->ccEmail = $emailData['ccEmail'];

//     // print_r($mail);

//     // exit;

//     if ($mail->Send()) {



//         return true;
//     } else {

//         return false;
//     }
// }

//End

function getRoleIDS($rolearr)

{

    $roleIDArr = array();

    if ($rolearr != '') {

        global $DB;

        $cnd = '';

        $newcnd = '';

        foreach ($rolearr as $k => $v) {

            $v = strtolower($v);

            $cnd .= " LOWER(roleName) LIKE '%" . $v . "%' OR";
        }

        $newcnd = rtrim($cnd, "OR");

        $DB->vals = array(1);

        $DB->types = "i";

        $DB->sql = "select roleID FROM " . $DB->pre . "x_admin_role WHERE status = ? AND " . $newcnd;

        $data = $DB->dbRows();

        if ($DB->numRows > 0) {

            foreach ($data as $k => $roleData) {

                array_push($roleIDArr, $roleData['roleID']);
            }
        }
    }

    return $roleIDArr;
}

function getRoleInfo($roleID = 0)
{
    global $DB;
    $hierarchyID = 0;
    if ($roleID != "SUPER") {
        //echo "SELECT hierarchyID,allowAllAccess FROM `".$DB->pre."admin_role` WHERE status='1' AND roleAID='".$roleAID."'";
        $roleData = $DB->dbRow("SELECT hierarchyID,allowAllAccess FROM `" . $DB->pre . "x_admin_role` WHERE status='1' AND roleID='" . $roleID . "'");
        if ($DB->numRows > 0) {
            $hierarchyID = $roleData['hierarchyID'];
            $allowAllAccess = $roleData['allowAllAccess'];
        }
    } else {
        $allowAllAccess = 1;
    }
    return array("hierarchyID" => $hierarchyID, "allowAllAccess" => $allowAllAccess);
}

function getFinancialArray($year = '')
{
    $startMonth = 04;
    $startYear = 2011;
    //        foreach("")
    $fromDate = "$startYear-$startMonth-01";
    $time = strtotime($fromDate);
    $dates = array();
    if ($year == '')
        $year = date('Y');
    for ($i = 0; $i <= $year - $startYear; $i++) {
        $date = date('Y-m-d', mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time) + $i));
        $dates[date('Y', strtotime($date))] = 'Year ' . date('Y', strtotime($date));
    }
    return $dates;
}
function getFinancialArrayDD($selected = "")
{
    $options = "";
    $dates = getFinancialArray();
    //        print_r($dates);
    if (!empty($dates) && sizeof($dates) >= 1 && is_array($dates)) {
        foreach ($dates as $k => $v) {
            if ($v != "") {
                $sel = "";
                if ("$k" == "$selected") $sel = ' selected="selected"';
                $options .= "\n<option value=\"" . $k . "\"" . $sel . ">" . $v . "</option>";
            }
        }
    }
    return $options;
}

function getCommunicationEmail()
{
    global $DB;
    $newArr = array();
    $DB->table = $DB->pre . "site_setting";
    $DB->vals = array(1, 1);
    $DB->types = "ii";
    $DB->sql = "select communicationEmail FROM `" . $DB->pre . "site_setting`  WHERE siteSettingID = ?  AND status = ?";
    $communicationEmail = $DB->dbRow();
    $ccEmailArr = implode($communicationEmail);
    $emailArr = explode(",", $ccEmailArr);

    foreach ($emailArr as $key => $val) {
        $newArr[$val] = $val;
    }
    return $newArr;
}


function calculateBalanceAmount($fromDate = "", $todate = "")
{
    global $DB;
    $response['creditAmount'] = $response['debitAmount'] = 0;
    $vals = array();
    $where = "";
    $types = "";
    if ($fromDate != "") {
        array_push($vals, $fromDate);
        $where .= " AND transactionDate >= ? ";
        $types .= "s";
    }
    if ($todate != "") {
        array_push($vals, $todate);
        $where .= " AND transactionDate <= ?";
        $types .= "s";
    }
    $DB->vals = array(1);
    $DB->vals = array_merge($DB->vals, $vals);
    $DB->types = "i" . $types;
    $DB->sql = "SELECT SUM(amount) as totalAmount,transactionType FROM `" . $DB->pre . "credit_debit` WHERE status = ? " . $where . " GROUP BY transactionType ";
    $creditDebitData = $DB->dbRows();

    // echo "<pre>";
    // print_r($creditDebitData);
    // exit;
    if ($DB->numRows > 0) {
        foreach ($creditDebitData as $k => $v) {
            if ($v['transactionType'] == 1) {
                $response['creditAmount'] = $v['totalAmount'];
            } else if ($v['transactionType'] == 2) {
                $response['debitAmount'] = $v['totalAmount'];
            }
        }
    }
    return $response;
}


// Start: To check availabale balance amount in petty cash.

function checkAvailableBalanceAmount($amount = 0, $availableAmtFlag = 0)

{
    global $DB;
    $transactionType = $_POST['transactionType'] ?? 1;
    $response['count'] = 0;
    $response['msg'] = "";
    if ($transactionType == 2 || $availableAmtFlag == 1) {
        $DB->vals = array(1);
        $DB->types = "i";
        $DB->sql = "SELECT balanceAmount FROM `" . $DB->pre . "credit_debit` WHERE status=? ORDER BY transactionDate DESC,creditDebitID DESC LIMIT 1";
        $result = $DB->dbRow();
        if ((isset($result['balanceAmount']) && $amount > $result['balanceAmount']) || $availableAmtFlag == 1 || $DB->numRows == 0) {
            $response['count'] = 1;
            $response['balanceAmount'] = $result['balanceAmount'] ?? 0;
            $response['msg'] = "Debit Amount is greater than Balance";
        }
    }
    return $response;
}

//End.

//Start: To update pettycash balance.

function updateCreditDebitBalance($transactionDate = "", $balanceAmount = 0)
{

    global $DB;
    $vals = array();
    $types = $where = "";

    if ($transactionDate != "") {
        $vals = array($transactionDate);
        $types = "s";
        $where = " AND transactionDate > ?";
    }
    $DB->vals = array(1);
    $DB->vals = array_merge($DB->vals, $vals);
    $DB->types = "i" . $types;
    $DB->sql = "SELECT creditDebitID,transactionType,amount FROM `" . $DB->pre . "credit_debit` WHERE status=? " . $where . " ORDER BY transactionDate ASC,creditDebitID ASC";
    $res = $DB->dbRows();


    if ($DB->numRows > 0) {
        foreach ($res as $value) {
            if ($value['transactionType'] == 1) {
                $balanceAmount += $value['amount'];
            }
            if ($value['transactionType'] == 2) {
                $balanceAmount -= $value['amount'];
            }
            $creditDebitID = $value['creditDebitID'];
            $DB->table = $DB->pre . "credit_debit";
            $DB->data = array("balanceAmount" => $balanceAmount);
            $DB->dbUpdate("creditDebitID=?", "i", array($creditDebitID));
        }
    }
}

//End.


function getItemSizeList($type = 0)
{
    global $arrItemT, $arrItemC, $arrItemD;
    $strF = '<th align="center" width="1%"><i class="chk"><input class="allv" type="checkbox" /><em></em></i></th>';
    if ($type == 1) {
        $strF = '<th align="center" width="1%"><a href="#" class="del"></th>';
    }
?>
    <link rel="stylesheet" type="text/css" href="<?php echo mxGetUrl(ADMINURL  . '/inc/item-size/item-size.css'); ?>" />
    <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(ADMINURL  . "/inc/item-size/item-size.inc.js"); ?>"></script>
    <table width="100%" border="0" cellspacing="0" cellpadding="3" class="tbl-list small" id="tbl-size-list">
        <thead>
            <tr id="search-fld">
                <?php echo $strF; ?>
                <th align="center" width="58"><input type="text" name="sl" class="srch" value="" index="1" placeholder="LENGTH"></th>
                <th align="center" width="52"><input type="text" name="sw" class="srch" value="" index="2" placeholder="WIDTH"></th>
                <th align="center" width="52"><input type="text" name="sh" class="srch" value="" index="3" placeholder="HEIGHT"></th>
                <!-- <th align="center" width="60"><input type="text" name="st" class="srch" value="" index="4" placeholder="TYPE"></th>
                <th align="center"><input type="text" name="sc" class="srch" value="" index="5" placeholder="CATEGORY"></th>
                <th align="center"><input type="text" name="sd" class="srch" value="" index="6" placeholder="DESIGN"></th> -->
                <th align="center">
                    <div class="select-box">
                        <select title="Item Type" name="itemTypeIDS" class="srch">
                            <option value="0">TYPE</option>
                            <?php echo getArrayDD(array("data" => $arrItemT, "selected" => ($_GET['itemTypeIDS'] ?? ""))); ?>
                        </select>
                    </div>
                </th>
                <th align="center">
                    <div class="select-box">
                        <select title="Size Category" name="itemCategoryIDS" class="srch">
                            <option value="0">CATEGORY</option>
                            <?php echo getArrayDD(array("data" => $arrItemC, "selected" => ($_GET['itemCategoryIDS'] ?? ""))); ?>
                        </select>
                    </div>
                </th>
                <th align="center">
                    <div class="select-box">
                        <select title="Size Design" name="itemDesignIDS" class="srch">
                            <option value="0">DESIGN</option>
                            <?php echo getArrayDD(array("data" => $arrItemD, "selected" => ($_GET['itemDesignIDS'] ?? ""))); ?>
                        </select>
                    </div>
                </th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
<?php
}



function getCustomerDD($customerID = 0)
{
    global $DB;
    if (isset($customerID) && $customerID > 0) {
        $whereArr = array("sql" => "status=? AND (isActive=? OR customerID =?) ", "types" => "iii", "vals" => array(1, 1, ($D['customerID'] ?? 0)));
    } else {
        $whereArr = array("sql" => "status=? AND isActive=?", "types" => "ii", "vals" => array(1, 1));
    }

    $extFields = array("stateID", "postalCode");
    $params = ["table" => $DB->pre . "customer", "key" => "customerID", "val" => "customerName", "selected" => $customerID, "where" => $whereArr, "order" => "customerName ASC", "extFields" => $extFields];
    $custOpt  = getTableDD($params);
    return $custOpt;
}


function getCurrencyDD($currencyID = 0)
{
    global $DB;

    $whereArr = array("sql" => "status=?", "types" => "i", "vals" => array(1));
    $extFields = array("rate");
    $params = ["table" => $DB->pre . "currency", "key" => "currencyID", "val" => "currencyName", "selected" => $currencyID, "where" => $whereArr, "order" => "currencyName ASC", "extFields" => $extFields, "lang" => false];
    $currencyOpt  = getTableDD($params);
    return $currencyOpt;
}

function getUnitArray()
{
    global $DB;
    $unitWhere    = array("sql" => "status=? ", "types" => "i", "vals" => array(1));
    $params = ["table" => $DB->pre . "unit", "key" => "unitID", "val" => "unitName", "where" => $unitWhere, "order" => "unitName ASC", "lang" => false];
    //$unitOpt  = getTableDD($params);
    $unitArr  = getDataArray($params);
    // echo $unitOpt  = getArrayDD(array("data" => $unitArr, "selected" => $unitID, "extFields" => $extFields));
    return $unitArr;
}

function getProductFields($param = ["rateFld" => "prodSaleRate", "detailFld" => "salesDID"])
{
    $arrFrmProd = array(
        array("type" => "hidden", "name" => $param["detailFld"]),
        array("type" => "hidden", "name" => "productID", "class" => "productID"),
        //array("type" => "hidden", "name" => "isSPIsPP", "class" => "isSPIsPP"),
        array("type" => "autocomplete", "name" => "productTitle", "title" => "Product Sku", "validate" => "required", "attrp" => ' width="16%"', "params" => array("xAction" => "getProductData", "callback" => "callbackProduct")),
        // array("type" => "text", "name" => "productDesc", "title" => "Product Description", "attrp" => ' width="16%"'),
        array("type" => "text", "name" => "hsnCode", "title" => "HSN", "validate" => "required",  "attr" => " readonly='readonly' ", "class" => "right hsnCode"),
        array("type" => "select", "name" => "unitID", "title" => "Unit", "validate" => "required", "class" => "right unitID"),
        array("type" => "text", "name" => "quantity", "title" => "QTY", "validate" => "required,number", "attr" => ' onkeyup="calculateAmount();"', "class" => "right quantity"),
        array("type" => "text", "name" => $param["rateFld"], "title" => "Rate", "validate" => "required,number", "attr" => ' onkeyup="calculateAmount();"', "class" => "right productRate"),
        array("type" => "text", "name" => "amount", "title" => "AMT", "validate" => "number",  "attr" => " readonly='readonly'", "class" => "right amount"),
        //array("type" => "text", "name" => "taxableAmt", "title" => "Taxable", "attr" => " readonly='readonly'", "class" => "right taxableAmt"),
        array("type" => "text", "name" => "taxRate", "title" => "Tax%", "validate" => "number", "attr" => " readonly='readonly' ", "class" => "right taxRate", "attrp" => ' width="3%"'),
        array("type" => "text", "name" => "cgstAmt", "title" => "CGST", "validate" => "number", "attr" => " readonly='readonly'", "class" => "right cgstAmt"),
        array("type" => "text", "name" => "sgstAmt", "title" => "SGST", "validate" => "number", "attr" => " readonly='readonly'", "class" => "right sgstAmt"),
        array("type" => "text", "name" => "igstAmt", "title" => "IGST", "validate" => "number", "attr" => " readonly='readonly'", "class" => "right igstAmt"),
        array("type" => "text", "name" => "totalAmt", "title" => "TOT", "validate" => "number", "attr" => " readonly='readonly'", "class" => "right totalAmt"),
    );
    return $arrFrmProd;
}

function getProductFooter($D = [])
{
    //  <input type="text" name="totRate" id="totRate" value="' . (number_format(($D['totRate'] ?? 0), 2, ".", "")) . '" readonly="readonly" title="Tot Rate" placeholder="Tax" xtype="text">
    return '<tr>
                <th colspan="3"></th>
                <th><input type="text" name="totQuantity" id="totQuantity" value="' . (number_format(($D['totQuantity'] ?? 0), 2, ".", "")) . '" readonly="readonly" title="QTY" placeholder="QTY" xtype="text"></th>
                <th></th>
                <th><input type="text" name="totProductAmt" id="totProductAmt" value="' . (number_format(($D['totProductAmt'] ?? 0), 2, ".", "")) . '" readonly="readonly" title="AMT" placeholder="AMT" xtype="text"></th>
                
                <th>
                    <input type="hidden" name="totTaxAmt" id="totTaxAmt" value="' . (number_format(($D['totTaxAmt'] ?? 0), 2, ".", "")) . '" readonly="readonly" title="Taxable" placeholder="Tax" xtype="text">
                   
                </th>
                <th><input type="text" name="totCGST" id="totCGST" value="' . (number_format(($D['totCGST'] ?? 0), 2, ".", "")) . '" readonly="readonly" title="CGST" placeholder="CGST" xtype="text"></th>
                <th><input type="text" name="totSGST" id="totSGST" value="' . (number_format(($D['totSGST'] ?? 0), 2, ".", "")) . '" readonly="readonly" title="SGST" placeholder="SGST" xtype="text"></th>
                <th><input type="text" name="totIGST" id="totIGST" value="' . (number_format(($D['totIGST'] ?? 0), 2, ".", "")) . '" readonly="readonly" title="IGST" placeholder="IGST" xtype="text"></th>
                <th><input type="text" name="subTotal" id="subTotal" value="' . (number_format(($D['subTotal'] ?? 0), 2, ".", "")) . '" readonly="readonly" title="TOT" placeholder="TOT" xtype="text"></th>
                <th></th>
            </tr>

            <tr>
                <th colspan="10" align="right">Grand Total </th>
                <th> <input type="text" name="grandTotal" id="grandTotal" value="' . (number_format(($D['grandTotal'] ?? 0.00), 2, ".", "")) . '" readonly="readonly" title="Grand Total" placeholder="Grand Total" xtype="text"></th>
                <th></th>
            </tr>';
}

function getProduct($prodcutType = "")
{
    global $DB;
    $json      = array();

    $DB->vals  = array(trim($_REQUEST['searchString']), 1, 1);
    $DB->types = "sii";

    $whrType = $priceFld = "";
    if (isset($prodcutType) && $prodcutType != '') {
        $whrType = " AND $prodcutType = ?";
        array_push($DB->vals,  1);
        $DB->types = $DB->types . "i";
        if ($prodcutType == 'isSP') {
            $priceFld = "prodSaleRate";
        } else {
            $priceFld = "prodPurchaseRate";
        }
    }

    $DB->vals  = array(trim($_REQUEST['searchString']), 1);
    $DB->types = "si";

    //,P.isSP,P.isPP
    $DB->sql = "SELECT P.productSku,P.productSkuID,H.hsnNo AS hsnCode,P.unitID,H.taxRate FROM `" . $DB->pre . "product_sku` P 
                LEFT JOIN " . $DB->pre . "hsn AS H ON H.hsnID = P.hsnID
                WHERE P.productSku LIKE CONCAT('%',?,'%') AND P.status=? " .  mxWhere("P.") . " ORDER BY P.productSku ASC LIMIT 50";
    $data = $DB->dbRows();
    if ($DB->numRows > 0) {
        foreach ($data as  $k => $v) {

            $json[] = array(
                "value" => $v["productSku"],
                "label" => $v["productSku"],
                "data" => array(
                    "productID" => $v["productSkuID"],
                    "hsnCode" => $v["hsnCode"],
                    "unitID" => $v["unitID"],
                    "quantity" => 1,
                    "taxRate" => $v["taxRate"]
                )
            );
        }
    }
    return json_encode($json);
}


function updateTotalQty($productID = 0)
{
    global $DB;
    $response['err'] = 1;
    $response['msg'] = 'Something went wrong';
    if ($productID > 0) {
        $DB->types = "i";
        $DB->vals = array(1);
        $DB->sql = "SELECT P.productSkuID,ID.quantity as totalSaleQty ,OD.purchQty as totalPurchaseQty, (CAST(OD.purchQty AS DECIMAL)-CAST(ID.quantity AS DECIMAL)) as totalBalanceQty
                    FROM `" . $DB->pre . "product_sku` AS P
                    LEFT JOIN ( SELECT SUM(quantity)  AS quantity,productID FROM `" . $DB->pre . "sales_details` AS ID 
                    LEFT JOIN `" . $DB->pre . "sales` AS I ON I.salesID = ID.salesID
                    WHERE ID.status=1 AND I.status=1 GROUP BY ID.productID) AS ID ON P.productSkuID = ID.productID 
                    LEFT JOIN ( SELECT SUM(quantity)  AS purchQty,productID FROM `" . $DB->pre . "purchase_details` AS POID
                    LEFT JOIN `" . $DB->pre . "purchase` AS PO ON POID.purchaseID = PO.purchaseID
                    WHERE PO.status=1 AND POID.status=1 GROUP BY POID.productID) AS OD ON P.productSkuID = OD.productID
                    WHERE P.status=? AND P.productSkuID IN ($productID) GROUP BY P.productSkuID";
        $DB->dbRows();
        if ($DB->numRows > 0) {
            foreach ($DB->rows as $value) {
                $value['currentStock'] = floatval($value['totalPurchaseQty']) - floatval($value['totalSaleQty']);
                $DB->table = $DB->pre . "product_sku";
                $DB->data = $value;
                $DB->dbUpdate("productSkuID=?", "i", array($value['productSkuID']));
            }
        }
    }
    return $response;
}

function deletePrintDoc($docID = 0, $docType = "")
{
    if (isset($docID) && isset($docType) && $docID > 0 && $docType != "") {
        global $DOCID, $DOCTYPE, $ARRDOCTYPE;
        $DOCID = $docID;
        $DOCTYPE = $docType;
        $DOCNAME = $ARRDOCTYPE[$DOCTYPE]["docname"];
        $params = getPrintParam("html");
        if (isset($params["fileName"])) {
            $uploadPath = UPLOADPATH . "/$DOCNAME/" . $params["fileName"];
            if (file_exists($uploadPath . ".html"))
                unlink($uploadPath . ".html");
            if (file_exists($uploadPath . ".pdf"))
                unlink($uploadPath . ".pdf");
        }
    }
}

function getPrintParam($fileExt = "")
{
    $params = [];
    if ($fileExt != "") {

        global $DOCID, $DOCTYPE, $ARRDOCTYPE;
        $DOCNAME = $ARRDOCTYPE[$DOCTYPE]["docname"];
        $fileName = getPrintFileName();
        if ($fileName !== "") {
            $orgID = 0;
            if (!isset($_SESSION[SITEURL]['ORGID'])) {
                echo "<h1>Please login as organization to print...</h1>";
                exit;
            }
            $contentUrl = ADMINURL . "/inc/print/print-content.php?docID=" . $DOCID . "&docType=" . $DOCTYPE . "&orgID=" . $_SESSION[SITEURL]['ORGID'];

            $params = ["fileName" => $fileName, "uploadDir" => $DOCNAME, "contentUrl" => $contentUrl, "fileExt" => $fileExt];
        }
    }
    return $params;
}

function getBalanceAmount()
{
    global $DB;
    $res = 0;
    $DB->vals = array(1);
    $DB->types = "i";
    $DB->sql = "SELECT balanceAmount FROM `" . $DB->pre . "petty_cash_book` WHERE status=? ORDER BY pettyCashBookID DESC LIMIT 1";
    $DB->dbRow();
    if ($DB->numRows > 0) {
        $res = $DB->row['balanceAmount'];
    }
    return $res;
}

function addPettyCashBook($dataArr = [])
{
    global $DB;
    $response = array("err" => 1, "param" => "", "msg" => "");
    $balanceAmount = getBalanceAmount();
    if ($dataArr['transactionType'] == 1) {
        $dataArr['balanceAmount'] = ((float)$balanceAmount + (float) $dataArr['amount']);
    } else if ($dataArr['transactionType'] == 2) {
        $dataArr['balanceAmount'] = ((float)$balanceAmount - (float)$dataArr['amount']);
    }
    $dataArr["doc1"] = mxGetFileName("doc1");
    $dataArr["doc2"] = mxGetFileName("doc2");
    $dataArr["doc3"] = mxGetFileName("doc3");
    $dataArr["doc4"] = mxGetFileName("doc4");
    $dataArr["doc5"] = mxGetFileName("doc5");
    $dataArr["dateAdded"] = date('Y-m-d H:i:s');

    $DB->table = $DB->pre . "petty_cash_book";
    $DB->data = $dataArr;
    $result = balanceAmountcheck($dataArr['amount']);
    $respMsg = $result["msg"];
    if ($result["count"] == 0) {
        if ($DB->dbInsert()) {
            $pettyCashBookID = $DB->insertID;
            if ($pettyCashBookID) {
                $response = array("err" => 0, "param" => "id=$pettyCashBookID");
            }
        } else {
            $response = array("err" => 1, 'msg' => 'Error occured while adding petty cash book');
        }
    } else {
        $response = array("err" => 1, "param" => "", "msg" => "$respMsg");
    }
    return $response;
}


function addVoucherData($vData = [])
{
    global $DB;
    $response = array("err" => 1, 'msg' => 'Something went wrong!');
    if (isset($vData["voucherNo"]))
        $vData["voucherNo"] = cleanTitle($vData["voucherNo"]);
    if (isset($vData["voucherDebitTo"]))
        $vData["voucherDebitTo"] = cleanTitle($vData["voucherDebitTo"]);
    if (isset($vData["voucherDate"]))
        $vData["voucherDate"] = $vData["voucherDate"];
    if (isset($vData["voucherDesc"]))
        $vData["voucherDesc"] = trim($vData["voucherDesc"]);

    $DB->table = $DB->pre . "voucher";
    $DB->data = $vData;
    $DB->vals = array(1);
    $DB->types = "i";
    $DB->sql = "SELECT balanceAmount FROM `" . $DB->pre . "petty_cash_book" . "` WHERE status=? ORDER BY pettyCashBookID DESC LIMIT 1";
    $res = $DB->dbRow();

    if ($vData['voucherAmt'] > $res['balanceAmount']) {
        $response = array("err" => 1, 'msg' => 'Voucher amount is greater than Balance amount');
    } else {
        $response = array("err" => 1, 'msg' => 'Error occured while adding petty cash book');
        if ($DB->dbInsert()) {
            $voucherID = $DB->insertID;
            $response = array("err" => 0, "voucherID" => $voucherID, "param" => "id=$voucherID");
        }
    }
    return $response;
}
