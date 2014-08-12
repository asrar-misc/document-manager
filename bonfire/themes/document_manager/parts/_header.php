<?php
	// Setup our default assets to load.
	Assets::add_js(array( 'jquery-ui-1.8.13.min.js','bootstrap.min.js','jquery.dataTables.js','bootstrap-datepicker.js' ));
	Assets::add_css( array('bootstrap.min.css', 'bootstrap-responsive.min.css','cosmo.min.css','handsontable.css','font-awesome/css/font-awesome.min.css','bootstrap-dataTables.css','bootstrap-datepicker.css','app.css'));
			
	$inline  = '$(".dropdown-toggle").dropdown();';
	$inline  = '$(".date_picker").datepicker({format : "yyyy-mm-dd"});';
	
	$inline .= '$(".tooltips").tooltip();';
	$inline .= '$(".login-btn").click(function(e){ e.preventDefault(); $("#modal-login").modal(); });';
	$inline .= '$("#data-table").dataTable();';

	Assets::add_js( $inline, 'inline' );

	Template::block('header', 'parts/head');

	Template::block('topbar', 'parts/topbar');
?>
