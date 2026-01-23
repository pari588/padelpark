<script language="javascript" type="text/javascript" src="<?php echo mxGetUrl($TPL->modUrl . 'inc/masonry.pkgd.min.js'); ?>"></script>
<?php $MXTOTREC = 0; ?>
<style>
/* THEME =============== */
body.light ul.dashboard>li>ul>li{border-color: #b7b7b7;}
/* moderate */
body.moderate ul.dashboard{background-color: #666666;}
/* THEME =============== */
ul.dashboard{width: 100%;display: flex;flex-flow: row wrap;margin-left: 0px;overflow-x: scroll;overflow-y: scroll;scrollbar-width: thin;position: relative;align-items: flex-start;height: 100% !important;align-content: flex-start;}
ul.dashboard>li{width: calc(20% - 4px);margin-right: 4px; margin-bottom: 4px;position: relative;background-color: rgba(0,0,0,.05);}
ul.dashboard>li>a{padding: 12px 12px 10px;font-size: 1.4rem;line-height: 2.2rem;font-weight: 400;width: 100%;display: inline-block;}
ul.dashboard li a.add::after,ul.dashboard li a.down-arrow,ul.dashboard>li>a.add {display: none !important;}
ul.dashboard>li>ul {float: left;width: 100%;display: flex;justify-content: flex-start;align-items: flex-start;flex-flow: row wrap;}
ul.dashboard>li>ul>li a:last-child{padding: 14px 15px 14px 40px;z-index: 2;line-height: 2.2rem;}
ul.dashboard li a.add{float: left;position: absolute;left: 0;right: auto;top: 50%;transform: translateY(-50%);}
/* RESPONCIVE */
@media screen and (max-width:1500px) {
    ul.dashboard>li{width: calc(25% - 4px);} 
}
@media screen and (max-width:1024px) {
    ul.dashboard>li{width: calc(50% - 4px);} 
}
@media screen and (max-width:767px) {
    ul.dashboard>li{width: calc(100% - 4px);} 
}
</style>
<div class="wrap-right">
	<?php echo getPageNav("", "", array("trash", "add", "print", "export")); ?>
	<div class="wrap-data">
		<ul class="dashboard main-nav">
			<?php echo getAdminSMenu(); ?>
		</ul>
	</div>
</div>

<script>
	$(".dashboard > li").addClass('grid-item');
	$(".dashboard").masonry({
		itemSelector: ".grid-item"
	});
</script>