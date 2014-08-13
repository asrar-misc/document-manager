
<?php if (validation_errors()) : ?>
	<div class="alert alert-block alert-error fade in ">
		<a class="close" data-dismiss="alert">&times;</a>
		<h4 class="alert-heading">Please fix the following errors :</h4>
		<?php echo validation_errors(); ?>
	</div>
<?php endif; ?>
<?php // Change the css classes to suit your needs
if( isset($document) ) {
	$document = (array)$document;
}
$id = isset($document['id']) ? $document['id'] : '';
?>
<div>
	<?php echo form_open($this->uri->uri_string(), 'class="form-horizontal" enctype="multipart/form-data"'); ?>
		<fieldset>
			<div class="control-group <?php echo form_error('document_name') ? 'error' : ''; ?>">
				<?php //echo form_label('Name'. lang('bf_form_label_required'), 'document_name', array('class' => "control-label") ); ?>
				<div class='controls'><input multiple="multiple" id="document_name" type="file" name="document_name" />
					<span class="help-inline"><?php echo form_error('document_name'); ?></span>
				</div>
			</div>
			
			
			<div class="form-actions">
				<input type="submit" name="save" class="btn btn-primary" value="Upload" />
				or <a href="javascript:void(0);" class="btn btn-warning" onClick="jQuery('#myModal').modal('toggle');"><?php echo lang('document_cancel');?></a>
			</div>
		</fieldset>
	<?php echo form_close(); ?>
</div>
