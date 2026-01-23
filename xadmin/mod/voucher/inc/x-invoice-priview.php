<?php
// Get dyanamic invoice data. 
include("../../../../core/core.inc.php");
global $DB;
$id = intval($_GET["id"] ?? 0);
$DB->vals = array(1, $id);
$DB->types = "ii";
$DB->sql = "SELECT * FROM `" . $DB->pre . "voucher` WHERE status=? AND voucherID=?";
$voucherDataArr = $DB->dbRow();
// End.
// Get Comman invoice titles.
$DB->vals = array(1, 1);
$DB->types = "ii";
$DB->sql = "SELECT invoiceTitle,invoiceAddr,pinCode,contactNo,contactMail,webUrl FROM `" . $DB->pre . "site_setting` WHERE status=? AND siteSettingID=?";
$siteInfoArr = $DB->dbRow();
// end.
?>
<!DOCTYPE html>
<html>

<head>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   <title>
      Voucher Invoice
   </title>
   <link rel="stylesheet" type="text/css" href="<?php echo ADMINURL . "/mod/voucher/inc/invoice-print.css"; ?>" />
   <link rel="stylesheet" type="text/css" href="<?php echo ADMINURL . "/mod/voucher/inc/invoice-print-pdf.css"; ?>" />
</head>

<body>
   <div id="wrap-print">
   <table border="0" class="header" cellspacing="0" cellpadding="0">
         <tr>
            <td valign="top" class="center pt pb">
               <h3 > <b >VOUCHER</b> </h3>
               <p style="height:5px;display:block;" >&nbsp;</p>
               <h1 class="mt"><?php echo $siteInfoArr["invoiceTitle"]; ?></h1>
            </td>
         </tr>
         <tr>
            <td class="center">
               <?php echo $siteInfoArr["invoiceAddr"] . " " . $siteInfoArr["pinCode"]; ?><br>
               <?php echo "Telefax: " . $siteInfoArr["contactNo"] . " // " . $siteInfoArr["contactMail"] . " // " . $siteInfoArr["webUrl"]; ?>
            </td>
         </tr>
         <tr>
            <td>&nbsp;</td>
         </tr>
      </table>
      <div class=""></div>
      <table border="0" class="header" cellspacing="0" cellpadding="0">
         <tr>
            <td><strong>VOUCHER NO: </strong><span class="bb-line"><?php echo $voucherDataArr["voucherNo"]; ?></span></td>
            <td class="right" width=""><strong>DATE: </strong>
               <span class="bb-line"><?php echo $voucherDataArr["voucherDate"]; ?></span>
            </td>
         </tr>
         <tr>
            <td>&nbsp;</td>
         </tr>
         <tr>
            <td colspan="2"><strong>DEBIT TO: </strong> <span class="line-width pb5"><?php echo $voucherDataArr["voucherDebitTo"]; ?></span></td>
         </tr>
      </table>
      <table border="0" cellspacing="0" cellpadding="0" class="tbl-items mtl full-border">
         <tr class="full-border">
            <th class="lp0 full-border" width="75%">PARTICULARS</th>
            <th class="center full-border" width="25%">AMOUNT</th>

         </tr>
         <tr class="full-border">
            <td class="full-border" width="75%"><?php echo $voucherDataArr["voucherTitle"]; ?></td>
            <td class="right full-border pr " width="25%"><?php echo $voucherDataArr["voucherAmt"]; ?></td>

         </tr>
         <tr class="full-border">
            <td class="full-border"><?php echo $voucherDataArr["voucherDesc"]; ?></td>
            <td class="full-border">&nbsp;</td>
         </tr>
         <tr class="full-border">
            <td class="full-border">&nbsp;</td>
            <td class="full-border">&nbsp;</td>
         </tr>
         <tr class="full-border">
            <td class="full-border">&nbsp;</td>
            <td class="full-border">&nbsp;</td>
         </tr>
         <tr class="full-border">
            <td class="full-border">&nbsp;</td>
            <td class="full-border">&nbsp;</td>
         </tr>
         <tr class="full-border">
            <td class="full-border">&nbsp;</td>
            <td class="full-border">&nbsp;</td>
         </tr>
         <tr class="full-border">
            <td class="full-border">&nbsp;</td>
            <td class="full-border">&nbsp;</td>
         </tr>

         <tr>
            <td class="full-border right pr"><strong>TOTAL</strong></td>
            <td class="right full-border pr "><b><?php echo $voucherDataArr["voucherAmt"]; ?></b></td>
         </tr>
         <tr>
            <td valign="top" colspan="8">
               <!-- <p>GST Payable on Reverse Charge: N/A</p> -->
               <p>
                  <strong>RECEIVED THE SUM OF RS, </strong> <span class="bb-line"><?php echo numberToWord(filter_var($voucherDataArr["voucherAmt"])); ?></span>
                  <!-- <strong>TCS(1%): </strong>  Only<br/>						 -->
               </p>
            </td>
         </tr>
      </table>
      <!-- <div class="black-line"></div> -->
      <table border="0" cellspacing="0" cellpadding="0" class="">
         <tr>
            <td width="33.33%" class="center pb0">
               <br /><br /><br /><br /><br />
               <h4 class="center">PREPARED BY</h4>
            </td>
            <td width="33.33%" class="center pl pb0">
               <br /><br /><br /><br /><br />
               <h4 class="center">AUTHORISED BY</h4>
            </td>
            <td width="33.33%" valign="top" class=" pl center pb0">

               <br /><br /><br /><br /><br />
               <h4 class="center">RECEIVED</h4>
            </td>
         </tr>
      </table>
      <div class="black-line"></div>
   </div>
</body>

</html>