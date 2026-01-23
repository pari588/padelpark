<?php
$id = 0;
$D = array();
$participants = array();
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "`=?";
    $D = $DB->dbRow();

    // Get participants
    $DB->vals = array($id);
    $DB->types = "i";
    $DB->sql = "SELECT sp.*, CONCAT(p.firstName, ' ', IFNULL(p.lastName,'')) as playerName, p.playerCode, p.phone
                FROM " . $DB->pre . "ipa_session_participant sp
                JOIN " . $DB->pre . "ipa_player p ON sp.playerID = p.playerID
                WHERE sp.sessionID = ? AND sp.status = 1
                ORDER BY sp.enrollmentDate";
    $participants = $DB->dbRows();
}

// Get coaches for dropdown
$DB->sql = "SELECT coachID, CONCAT(firstName, ' ', IFNULL(lastName,'')) as coachName FROM " . $DB->pre . "ipa_coach WHERE status=1 AND coachStatus='Active' ORDER BY firstName";
$coaches = $DB->dbRows();
$coachOpt = '<option value="">Select Coach</option>';
$currentCoach = $D["coachID"] ?? "";
foreach ($coaches as $c) {
    $sel = ($currentCoach == $c["coachID"]) ? ' selected="selected"' : '';
    $coachOpt .= '<option value="' . $c["coachID"] . '"' . $sel . '>' . htmlspecialchars($c["coachName"]) . '</option>';
}

// Get programs for dropdown
$DB->sql = "SELECT programID, programCode, programName FROM " . $DB->pre . "ipa_program WHERE status=1 ORDER BY programName";
$programs = $DB->dbRows();
$programOpt = '<option value="">Select Program</option>';
$currentProgram = $D["programID"] ?? "";
foreach ($programs as $p) {
    $sel = ($currentProgram == $p["programID"]) ? ' selected="selected"' : '';
    $programOpt .= '<option value="' . $p["programID"] . '"' . $sel . '>' . htmlspecialchars($p["programCode"] . ' - ' . $p["programName"]) . '</option>';
}

// Build session status dropdown
$statuses = array("Scheduled", "In-Progress", "Completed", "Cancelled", "No-Show");
$statusOpt = '';
$currentStatus = $D["sessionStatus"] ?? "Scheduled";
foreach ($statuses as $st) {
    $sel = ($currentStatus == $st) ? ' selected="selected"' : '';
    $statusOpt .= '<option value="' . $st . '"' . $sel . '>' . $st . '</option>';
}

// Build payment status dropdown
$payStatuses = array("Pending", "Paid", "Partial", "Refunded");
$payOpt = '';
$currentPay = $D["paymentStatus"] ?? "Pending";
foreach ($payStatuses as $ps) {
    $sel = ($currentPay == $ps) ? ' selected="selected"' : '';
    $payOpt .= '<option value="' . $ps . '"' . $sel . '>' . $ps . '</option>';
}

$arrFormBasic = array(
    array("type" => "text", "name" => "sessionCode", "value" => $D["sessionCode"] ?? "", "title" => "Session Code", "info" => '<span class="info">Auto-generated if blank</span>'),
    array("type" => "select", "name" => "programID", "value" => $programOpt, "title" => "Program"),
    array("type" => "select", "name" => "coachID", "value" => $coachOpt, "title" => "Coach", "validate" => "required"),
);

$arrFormSchedule = array(
    array("type" => "date", "name" => "sessionDate", "value" => $D["sessionDate"] ?? date("Y-m-d"), "title" => "Session Date", "validate" => "required"),
    array("type" => "time", "name" => "startTime", "value" => $D["startTime"] ?? "09:00", "title" => "Start Time", "validate" => "required"),
    array("type" => "time", "name" => "endTime", "value" => $D["endTime"] ?? "10:00", "title" => "End Time", "validate" => "required"),
);

$arrFormStatus = array(
    array("type" => "select", "name" => "sessionStatus", "value" => $statusOpt, "title" => "Session Status"),
    array("type" => "select", "name" => "paymentStatus", "value" => $payOpt, "title" => "Payment Status"),
    array("type" => "text", "name" => "totalRevenue", "value" => $D["totalRevenue"] ?? "0", "title" => "Total Revenue (Rs.)", "validate" => "number"),
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <h2 class="form-head">Session Details</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormBasic); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Schedule</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormSchedule); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Status & Revenue</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormStatus); ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>

    <?php if ($TPL->pageType == "edit" || $TPL->pageType == "view") { ?>
    <div class="wrap-data" style="margin-top:20px;">
        <h2 class="form-head">Participants (<?php echo count($participants); ?>)</h2>

        <?php if ($TPL->pageType == "edit") { ?>
        <div style="margin-bottom:15px; padding:15px; background:var(--charcoal); border-radius:var(--radius-md);">
            <div style="display:flex; gap:10px; align-items:center;">
                <input type="text" id="playerSearch" placeholder="Search player by name or code..." style="flex:1; padding:8px 12px;">
                <button type="button" class="btn btn-success btn-sm" onclick="addPlayerToSession()"><i class="fa fa-plus"></i> Add</button>
            </div>
            <div id="playerResults" style="margin-top:10px; display:none;"></div>
        </div>
        <?php } ?>

        <?php if (count($participants) > 0) { ?>
        <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
            <thead>
                <tr>
                    <th width="15%">Code</th>
                    <th width="25%">Player Name</th>
                    <th width="15%">Phone</th>
                    <th width="15%">Attendance</th>
                    <th width="15%">Amount</th>
                    <th width="15%">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($participants as $part) {
                    $attColors = array("Enrolled" => "info", "Attended" => "success", "No-Show" => "danger", "Cancelled" => "secondary");
                ?>
                <tr>
                    <td align="center"><?php echo htmlspecialchars($part["playerCode"]); ?></td>
                    <td><?php echo htmlspecialchars($part["playerName"]); ?></td>
                    <td align="center"><?php echo htmlspecialchars($part["phone"]); ?></td>
                    <td align="center">
                        <span class="badge badge-<?php echo $attColors[$part["attendanceStatus"]] ?? "secondary"; ?>"><?php echo $part["attendanceStatus"]; ?></span>
                    </td>
                    <td align="right">Rs. <?php echo number_format($part["amountPaid"], 0); ?></td>
                    <td align="center">
                        <?php if ($part["attendanceStatus"] == "Enrolled") { ?>
                        <button type="button" class="btn btn-sm btn-success" onclick="markAttendance(<?php echo $part["spID"]; ?>, 'Attended')" title="Mark Attended"><i class="fa fa-check"></i></button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="markAttendance(<?php echo $part["spID"]; ?>, 'No-Show')" title="Mark No-Show"><i class="fa fa-times"></i></button>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php } else { ?>
        <p style="color:#888; text-align:center; padding:30px;">No participants enrolled yet.</p>
        <?php } ?>
    </div>
    <?php } ?>
</div>

<?php if ($TPL->pageType == "edit") { ?>
<script>
var selectedPlayerID = null;

$('#playerSearch').on('keyup', function() {
    var search = $(this).val();
    if (search.length < 2) {
        $('#playerResults').hide();
        return;
    }

    $.ajax({
        url: '<?php echo ADMINURL; ?>/mod/ipa-session/x-ipa-session.inc.php',
        type: 'POST',
        data: {xAction: 'GET_PLAYERS', search: search},
        dataType: 'json',
        success: function(r) {
            if (r.err == 0 && r.players && r.players.length > 0) {
                var html = '<div style="background:var(--ink); border-radius:var(--radius-sm); max-height:200px; overflow-y:auto;">';
                for (var i = 0; i < r.players.length; i++) {
                    var p = r.players[i];
                    html += '<div class="player-option" data-id="' + p.playerID + '" style="padding:8px 12px; cursor:pointer; border-bottom:1px solid var(--slate);" onmouseover="this.style.background=\'var(--amber-glow)\'" onmouseout="this.style.background=\'transparent\'">';
                    html += '<strong>' + p.playerCode + '</strong> - ' + p.playerName;
                    if (p.phone) html += ' <small>(' + p.phone + ')</small>';
                    html += '</div>';
                }
                html += '</div>';
                $('#playerResults').html(html).show();

                $('.player-option').on('click', function() {
                    selectedPlayerID = $(this).data('id');
                    $('#playerSearch').val($(this).text().trim());
                    $('#playerResults').hide();
                });
            } else {
                $('#playerResults').html('<div style="padding:10px; color:#888;">No players found</div>').show();
            }
        }
    });
});

function addPlayerToSession() {
    if (!selectedPlayerID) {
        alert('Please search and select a player first');
        return;
    }

    $.ajax({
        url: '<?php echo ADMINURL; ?>/mod/ipa-session/x-ipa-session.inc.php',
        type: 'POST',
        data: {
            xAction: 'ADD_PARTICIPANT',
            sessionID: <?php echo $id; ?>,
            playerID: selectedPlayerID,
            amountPaid: 0
        },
        dataType: 'json',
        success: function(r) {
            if (r.err == 0) {
                location.reload();
            } else {
                alert(r.msg || 'Error adding participant');
            }
        }
    });
}

function markAttendance(spID, status) {
    $.ajax({
        url: '<?php echo ADMINURL; ?>/mod/ipa-session/x-ipa-session.inc.php',
        type: 'POST',
        data: {
            xAction: 'MARK_ATTENDANCE',
            spID: spID,
            attendanceStatus: status
        },
        dataType: 'json',
        success: function(r) {
            if (r.err == 0) {
                location.reload();
            } else {
                alert(r.msg || 'Error marking attendance');
            }
        }
    });
}
</script>
<?php } ?>
