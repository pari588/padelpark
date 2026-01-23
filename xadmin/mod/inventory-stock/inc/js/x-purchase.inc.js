$(document).ready(function () {
  CSTATEID = $("select#vendorID").find(':selected').attr("stateid");

  initDocuments("vendor",0);
  setDelEvent();
  $('table.grp-wrap a.add').click(function(){
    setTimeout(function(){
      setDelEvent();
    },200)
  })
});

function setDelEvent(){
  $('.del').bind('click',function(){
    calculateAmount();
  });
}


function calculateAmount() {

var subTotal = 0;
var totQuantity = 0;
var totProductAmt = 0;
var totTaxAmt = 0;
var totCGST = 0;
var totSGST = 0;
var totIGST = 0;
var tcsAmount = 0;
var grandTotal = 0;
var totRate = 0;

$("table.tbl-list  tbody tr").each(function () {
  var quantity = parseFloat($(this).find("input.quantity").val());
  if (!quantity) quantity = 0;

  var rate = 0;
  if ($(this).find("input.productRate").length)
    rate = parseFloat($(this).find("input.productRate").val());

  if (!rate) rate = 0;

  var amount = parseFloat(parseFloat(rate) * parseFloat(quantity));
  if (!amount) amount = 0;

  $(this).find("input.amount").val(amount.toFixed(2));

  var taxper = parseFloat($(this).find("input.taxRate").val());
  if (!taxper) taxper = 0;

  var taxAmount = parseFloat((amount * taxper) / 100);
  var igstAmt = taxAmount;
  var cgstAmt = sgstAmt = parseFloat(taxAmount / 2);


  if (STATEIDC != $("input#stateID").val()) {
    cgstAmt = sgstAmt = 0;
  } else {
    igstAmt = 0;
  }

  if (!igstAmt) igstAmt = 0;
  if (!cgstAmt) cgstAmt = 0;
  if (!sgstAmt) sgstAmt = 0;

  totalAmt = (parseFloat(amount) + parseFloat(taxAmount));
  if (!totalAmt) totalAmt = 0;

  $(this).find("input.cgstAmt").val(cgstAmt.toFixed(2));
  $(this).find("input.sgstAmt").val(sgstAmt.toFixed(2));
  $(this).find("input.igstAmt").val(igstAmt.toFixed(2));
  $(this).find("input.totalAmt").val(totalAmt.toFixed(2));

  totQuantity = parseFloat(totQuantity) + parseFloat(quantity);
  totProductAmt = parseFloat(totProductAmt) + parseFloat(amount);
  totCGST = parseFloat(totCGST) + parseFloat(cgstAmt);
  totSGST = parseFloat(totSGST) + parseFloat(sgstAmt);
  totIGST = parseFloat(totIGST) + parseFloat(igstAmt);
  totTaxAmt = parseFloat(totCGST) + parseFloat(totSGST) + parseFloat(totIGST);
  totRate = parseFloat(totRate) + parseFloat(taxper);

  subTotal = parseFloat(subTotal) + parseFloat(totalAmt);

  grandTotal = subTotal;
  // if ($("input.tcsPercent").val() > 0) {
  //   tcsAmount = parseFloat((parseFloat($("input.tcsPercent").val()) * subTotal) / 100);
  //   grandTotal = parseFloat(subTotal) + parseFloat(tcsAmount);
  // }

  // $("#tcsAmount").val(tcsAmount.toFixed(2));
});

$("#totQuantity").val(totQuantity.toFixed(2));
$("#totProductAmt").val(totProductAmt.toFixed(2));
$("#totCGST").val(totCGST.toFixed(2));
$("#totSGST").val(totSGST.toFixed(2));
$("#totIGST").val(totIGST.toFixed(2));
$("#totRate").val(totRate.toFixed(2));
$("#totTaxAmt").val(totTaxAmt.toFixed(2));
$("#subTotal").val(subTotal.toFixed(2));
$("#grandTotal").val(grandTotal.toFixed(2));
}

function callbackProduct(data, el, p) {
el = el.closest(".grp-set");
if (el.length > 0 && (p.tagwrap == "" || p.tagwrap == "undefined")) {
  el.find("input.productID").val(data.productID);
  el.find("input.productDesc").val(data.productDesc);
  el.find("input.hsnCode").val(data.hsnCode);
  el.find("div.unitID").find("select").val(data.unitID);
  el.find("input.quantity").val(data.quantity);
  el.find("input.taxRate").val(data.taxRate);
  el.find("input.prodSaleRate").val(data.prodSaleRate);
  el.find("input.prodPurchaseRate").val(data.prodPurchaseRate);
  calculateAmount();
}
}

var STATEIDC = 0;
function setCustVendStateID(type) {
var optSelected = $("form#frmAddEdit select#" + type + "ID option:selected");

if (optSelected.length > 0) {
  var stateID = optSelected.attr("stateID");
  if (typeof stateID !== "undefined")
    STATEIDC = stateID;
}
}

function initDocuments(type, flg) {
if (typeof type !== "undefined") {
  $("form#frmAddEdit select#" + type + "ID").change(function () {
    
    setCustVendStateID(type);
    if (typeof flg !== "undefined" && flg > 0) {
      var customerID = $(this).val();
      var optDefault = '<option value="" class="default">--CONSIGNEE NAME (SHIPPING TO)--</option>';
      var optConsignee = $("form#frmAddEdit select#consigneeID");
      optConsignee.html(optDefault);
      showMxLoader();
      $.mxajax({
        url: MODINCURL,
        data: { customerID: customerID, xAction: "getConsigneeDD" },
        type: "POST",
        dataType: 'json'
      }).then(function (data) {
        hideMxLoader();
        if (data.err == 0) {
          if (data.data !== "") {
            $("form#frmAddEdit select#consigneeID").html(optDefault + data.data);
          }
        }
      });
    }
  });

  // $("input#tcsPercent").on('keyup keypress blur change', function (e) {
  //   calculateAmount();
  // });

  $('#vendorID').on("change",()=>{
    calculateAmount();
  });

  setCustVendStateID(type);
}
}


//Added By Pramod Badgujar || 12 march 2024
function MXTRASHPOST(result, params) {
   // console.log(params);
   if (params.xAction == "trash") {
    var status = 0;
  } else {
    var status = 1;
  }
  if (result.err == 0) {
    if (params && params.id) {
      showMxLoader();
      $.mxajax({
        type: "post",
        data: { purchaseIDs: params.id, xAction: "purchaseTrash",status: status, },
        url: ADMINURL + '/mod/purchase/x-purchase.inc.php',
        dataType: "json",
      }).then(function (data) {
        hideMxLoader();
        if (data.err == 0) {	
          // $.mxalert({ msg: data.msg });
        } else {
          $.mxalert({ msg: data.msg, title: "Can't trash" })
        }
      });
    }
  }
}
