<script type="text/javascript" src="<?php echo mxGetUrl(ADMINURL . "/mod/sales/inc/js/x-sales.inc.js"); ?>"></script>
<?php
$strBtnEi = '';
$arrProduct = $D = [];
$unitArr = getUnitArray();
//--------START Einvoice-----------
$DB->sql = "SELECT stateID FROM `" . $DB->pre . "site_setting` WHERE 1 " . mxWhere();
//$DB->showSql();
$arrSett = $DB->dbRow();
//exit;
//--------END Einvoice-----------
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
  $id = intval($_GET["id"]) ?? 0;
  $DB->vals  = array(1, $id);
  $DB->types = "ii";
  $DB->sql   = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND " . $MXMOD["PK"] . " =?";
  $D = $DB->dbRow();

  $DB->vals = array($id);
  $DB->types = "i";
  $DB->sql = "SELECT I.*,P.productSku AS productTitle FROM `" . $DB->pre . "sales_details` AS I 
              LEFT JOIN `" . $DB->pre . "product_sku` AS P ON I.productID = P.productSkuID 
              WHERE I.salesID=?";
  $DB->dbRows();
  if ($DB->numRows > 0) {
    foreach ($DB->rows as $k => $v) {
      $v['unitID'] = getArrayDD(array("data" => $unitArr, "selected" => ($v['unitID'] ?? "")));
      $v['productTitle'] = $v['productTitle'];
      $arrProduct[]  = $v;
    }
  }
} else {
  $arrProduct[]["unitID"] = getArrayDD(["data" => $unitArr]);
}

$customerOpt = getCustomerDD(($D["customerID"] ?? 0));
$currencyOpt = getCurrencyDD(($D["currencyID"] ?? "1"));

$arrForm = array(
  array("type" => "select", "name" => "customerID", "value" => $customerOpt, "title" => "Customer Name",  "validate" => "required", "attrp" => ' class="customerID c3"'),
  array("type" => "hidden", "name" => "stateID", "value" => $arrSett["stateID"] ?? "")
);


$MXFRM = new mxForm();
?>
<div class="wrap-right">
  <?php echo getPageNav(); ?>
  <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
    <div class="wrap-form f100">
      <ul class="tbl-form">
        <?php echo $MXFRM->getForm($arrForm); ?>
      </ul>
    </div>

    <div class="wrap-form f100">
      <?php echo $MXFRM->getFormG(array("flds" => getProductFields(["rateFld" => "prodSaleRate", "detailFld" => "salesDID"]), "vals" => $arrProduct, "tfoot" => getProductFooter($D), "class" => " products small")); ?>
    </div>
    <?php echo $MXFRM->closeForm(); ?>
  </form>
</div>