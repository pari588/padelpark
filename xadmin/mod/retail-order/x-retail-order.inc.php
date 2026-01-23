<?php
function saveInvoiceDetail($salesID = 0)
{
    if ($salesID) {
        global $DB;
        //$unitData     = getDataArray($DB->pre . "unit", "unitID", "unitName",  array(), " unitName ASC ");
        $unitData = getUnitArray();
        for ($k = 0; $k < count($_POST["productID"]); $k++) {
            $_POST['unitName'][$k] = $unitData["data"][$_POST['unitID'][$k]];
            $arrIn = array(
                "salesID" => $salesID,
                "productID"   => $_POST["productID"][$k],
                "hsnCode"     => $_POST["hsnCode"][$k],
                "unitID"      => $_POST["unitID"][$k],
                "unitName"    => $_POST["unitName"][$k],
                "prodSaleRate" => $_POST["prodSaleRate"][$k],
                "quantity"     => $_POST["quantity"][$k],
                "amount"       => $_POST["amount"][$k],
                "taxRate"   => $_POST["taxRate"][$k],
                "totalAmt"     => $_POST["totalAmt"][$k],
                "cgstAmt"     => $_POST["cgstAmt"][$k],
                "sgstAmt"     => $_POST["sgstAmt"][$k],
                "igstAmt"     => $_POST["igstAmt"][$k],
                "salesDate" => date("Y-m-d")
            );

            if (intval($_POST["salesDID"][$k]) > 0) {
                $arrIn["salesDID"] = intval($_POST["salesDID"][$k]);
            }
            $DB->table = $DB->pre . "sales_details";
            $DB->data = $arrIn;
            $DB->dbInsert();
        }
    }
}

function addInvoice()
{
    global $DB;
    if (!isset($_POST["isCanceled"]))
        $_POST["isCanceled"] = 0;

    $DB->table = $DB->pre . "sales";
    $DB->data = $_POST;
    $productArr = array();
    $productArr = $_POST['productID'];
    $productStr = implode(",", $productArr);
    if ($DB->dbInsert()) {
        $salesID = $DB->insertID;
        if ($salesID) {
            saveInvoiceDetail($salesID);
            updateTotalQty($productStr);
            setResponse(["err" => 0, "param" => "id=$salesID"]);
        }
    } else {
        setResponse(["err" => 1]);
    }
}

function updateInvoice()
{
    global $DB;
    if (!isset($_POST["isCanceled"]))
        $_POST["isCanceled"] = 0;

    $salesID = intval($_POST["salesID"]);
    $DB->table = $DB->pre . "sales";
    $DB->data = $_POST;
    if ($DB->dbUpdate("salesID=?", "i", array($salesID))) {
        if (isset($_POST["salesDID"]) &&  count($_POST["salesDID"]) > 0) {

            //Get old product ids to update stock qty
            $invoiceDtlWhere = array("sql" => "salesID = ?", "types" => "i", "vals" => array($salesID));
            $params = ["table" => $DB->pre . "sales_details", "key" => "productID", "val" => "productID", "where" => $invoiceDtlWhere];
            $oldProdIds = getDataArray($params);
            $oldProdIds = count($oldProdIds) > 0 ? $oldProdIds['data'] : [];

            //Get unique product ids from old array and new array
            $uniqueProdIDArr = array_unique(array_merge($oldProdIds, $_POST['productID']), SORT_REGULAR);
            $productIDs = implode(",", $uniqueProdIDArr);

            $DB->vals = array($salesID);
            $DB->types = "i";
            $DB->sql = "DELETE FROM " . $DB->pre . "sales_details WHERE salesID=?";
            if ($DB->dbQuery()) {
                saveInvoiceDetail($salesID);
            }
            // function to update the totalSaleQty,totalPurchaseQty,totalBalanceQty fields in product table.
            updateTotalQty($productIDs);
        }

        // deletePrintDoc($salesID, "INV");
        setResponse(["err" => 0, "param" => "id=$salesID"]);
    } else {
        setResponse(["err" => 1]);
    }
}

//----------------------------------------------------------------
function getProductData()
{
    return getProduct('isSP');
}

//Added by Pramod Badgujar || 12 march 2024
function salesTrash()
{
    global $DB;
    $response['msg'] = "Something went wrong.";
    $response['err'] = 1;
    $invoiceArr = array();
    $status = $_POST['status'];
    $salesIDs = $_POST['salesIDs'];
    if ($salesIDs) {
        $DB->vals = array();
        $DB->types = "";
        $DB->sql = "SELECT S.salesID,SD.productID 
                    FROM `" . $DB->pre . "sales` AS S
                    LEFT JOIN `" . $DB->pre . "sales_details` AS SD ON SD.salesID = S.salesID
                    WHERE S.salesID IN ($salesIDs)";
        $salesResult = $DB->dbRows();
        foreach ($salesResult as $value) {
            $salesArr[] = $value['productID'];
            $DB->table = $DB->pre . "sales_details";
            $DB->data = array("status" => $status);
            $DB->dbUpdate("salesID=?", "i", array($value['salesID']));
        }
        $salesStr = implode(',', $salesArr);
        if ($DB->numRows > 0) {
            updateTotalQty($salesStr);
            $response['err'] = 0;
            $response['msg'] = 'Selected sales are successfully trashed';
        }
    }
    return $response;
}


if (isset($_POST["xAction"])) {
    require("../../../core/core.inc.php");
    require(ADMINPATH . "/inc/site.inc.php");
    $MXRES = mxCheckRequest();

    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD":
                addInvoice();
                break;
            case "UPDATE":
                // require_once("inc/invoice-print.inc.php");
                updateInvoice();
                break;
            case 'getProductData':
                echo getProductData();
                exit;
                break;
            case "salesTrash":
                $MXRES = salesTrash();
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    setModVars(array("TBL" => "sales", "PK" => "salesID"));
}
