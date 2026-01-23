<script type="text/javascript" src="<?php echo mxGetUrl($TPL->modUrl . '/inc/js/x-holidays.inc.js'); ?>"></script>
<!-- To desable sunday  -->
<style type="text/css">
    td.sun {
        pointer-events: none;
    }
</style>
<!-- To desable sunday -->
<?php
//Start: Set default values
$months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
$hTypCls = array('off-holiday', 'nat-holiday', 'fest-holiday', 'oth-holiday');
$HOLIDAYTYEP = array("1" => "Official Holiday", "2" => "National Holiday", "3" => "Indian Festivals", "4" => "Other");
$current_month = date('n');
$current_year = $_GET['year']??0 > 0 ? $_GET['year'] : date('Y');
$current_day = date('d');
$month = 0;
$holidays = getHolidays($current_year);
$last5y = date('Y') - 5;
$nxt2y = date('Y') + 2;
// End
?>
<div class="wrap-right">
    <?php echo getPageNav("", "", array("trash", "add", "print", "export")); ?>
    <div class="wrap-data">
        <div class="calender">
            <div class="select-year">
                <label>Select Year</label>
                <div class="select-field">
                    <select name="year" id="year" style="text-align: center;">
                        <option value="">--Select year--</option>
                        <?php for ($i = $last5y; $i <= $nxt2y; $i++) : ?>
                            <option value="<?php echo $i; ?>" <?php if ($i == $current_year) : ?>selected<?php endif; ?>><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <input type="hidden" name="xAction" value="saveHoliday">
            <table cellpadding="0" cellspacing="12" border="0" class="calender">
                <tbody>
                    <?php for ($row = 1; $row <= 3; $row++) : ?>
                        <tr>
                            <?php for ($column = 1; $column <= 4; $column++) : ?>
                                <td class="months <?php echo $column == 4 ? "last" : ""; ?>" style="display:none;">
                                    <?php
                                    $month++;
                                    $first_day_in_month = date('w', mktime(0, 0, 0, $month, 1, $current_year));
                                    $month_days = date('t', mktime(0, 0, 0, $month, 1, $current_year));
                                    // in PHP, Sunday is the first day in the week with number zero (0)
                                    // to make our calendar works we will change this to (7)
                                    if ($first_day_in_month == 0) {
                                        $first_day_in_month = 7;
                                    }
                                    ?>
                                    <table cellpadding="1" cellspacing="0" border="0">
                                        <tr class="month-title">
                                            <th colspan="7"><?php echo $months[$month - 1]; ?></th>
                                        </tr>
                                        <tr class="days">
                                            <td class="first">Mo</td>
                                            <td>Tu</td>
                                            <td>We</td>
                                            <td>Th</td>
                                            <td>Fr</td>
                                            <td class="sat">Sa</td>
                                            <td class="sun last">Su</td>
                                        </tr>
                                        <tr>
                                            <?php for ($i = 1; $i < $first_day_in_month; $i++) :
                                                $cls = ($i == 1) ? 'first' : '';
                                            ?>
                                                <td class="<?php echo $cls; ?>"> </td>
                                            <?php endfor; ?>
                                            <?php for ($day = 1; $day <= $month_days; $day++) :
                                                $pos = ($day + $first_day_in_month - 1) % 7;
                                                $date = date('Y-m-d', strtotime($current_year . "-" . $month . "-" . $day));
                                                $disabledDate = "";
                                                $class = (($day == $current_day) && ($month == $current_month)) ? 'today' : 'day';
                                                $class .= ($pos == 0) ? ' sun last' : '';
                                                $class .= ($pos == 1) ? ' first' : '';
                                               $title="
";                                                if (isset($holidays[$date])) {
                                                    
                                                    $class .= " " . $hTypCls[$holidays[$date]['holidayType'] - 1];
   $title= $HOLIDAYTYEP[$holidays[$date]['holidayType']] . "-" . $holidays[$date]['ahReason'??""];
                                                }

                                        ?>
                                                <td class="<?php echo $class; ?>">
                                                   <a href="#" class="date" title="<?php echo $title; ?>"><?php echo $day; ?></a>                                                    <?php if (in_array("add", $TPL->access)) { ?>
                                                        <div class="leave-details" style="display:none">
                                                            <ul>
                                                                <li>
                                                                    <div class="select-field">

                                                                        <select name="holidayType[]">
                                                                            <option value="">--Select holiday type--</option>
                                                                            <?php echo getArrayDD(["data" => array("data" => $HOLIDAYTYEP), "selected" => ($holidays[$date]['holidayType'] ??0)]);//getArrayDD($HOLIDAYTYEP, $holidays[$date]['holidayType']); ?>
                                                                        </select>
                                                                    </div>
                                                                </li>
                                                                <li>
                                                                    <textarea name="holidayReason[]" class="holiday-reason" placeholder="Reason"><?php echo $holidays[$date]['ahReason']?? ""; ?></textarea>
                                                                </li>
                                                                <li>
                                                                    <a href="#" class="apply" title="Add Holiday"></a>
                                                                    <input type="hidden" name="date[]" value="<?php echo $date; ?>" />
                                                                    <a href="#" class="cancel <?php if ($holidays[$date]['holidayType']??0 > 0) : ?>del-holiday<?php endif; ?>" title="Cancel Holiday"></a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    <?php } ?>
                                                    <?php //endif;
                                                    ?>
                                                </td>
                                                <?php if ($pos == 0) echo '</tr>'; ?>
                                            <?php endfor; ?>
                                        </tr>
                                    </table>
                                </td>
                            <?php endfor; ?>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
            <!--</form>-->
        </div>
    </div>
</div>