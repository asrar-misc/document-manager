
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

<?php echo form_open($this->uri->uri_string(), 'class="form-horizontal" id=frm_assign_permission'); ?>
	<?php if (isset($users) && is_array($users) && count($users)) : ?>
    <table id="data-table" class="table table-condensed table-striped table-hover table-bordered">
			<thead>
				<tr>
					<th>Users</th>
					<th>Permission</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="8">
						<input type="submit" name="save" class="btn btn-primary" value="Assign" />
						or <a href="javascript:void(0);" class="btn btn-warning" onClick="jQuery('#myModal').modal('toggle');"><?php echo lang('document_cancel');?></a>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php foreach ($users as $user) : ?>
				<tr>
					<td><?php echo $user->username;?></td>
					<td>
						<label style="display: inline;">
							<input type="radio" value="0" name="permission[<?php echo $user->id;?>]"<?php echo array_key_exists($user->id, $user_access_array) && $user_access_array[$user->id]==0?' checked':''; ?> style="margin-top: 0px;">
							None
						</label>|
						<label style="display: inline;">
							<input type="radio" value="<?php echo Document::$PERMISSION_VIEW;?>" name="permission[<?php echo $user->id;?>]"<?php echo array_key_exists($user->id, $user_access_array) && $user_access_array[$user->id]==Document::$PERMISSION_VIEW?' checked':''; ?> style="margin-top: 0px;">
							View
						</label>|
						<label style="display: inline;">
							<input type="radio" value="<?php echo Document::$PERMISSION_DOWNLOAD;?>" name="permission[<?php echo $user->id;?>]"<?php echo array_key_exists($user->id, $user_access_array) && $user_access_array[$user->id]==Document::$PERMISSION_DOWNLOAD?' checked':''; ?> style="margin-top: 0px;">
							Download
						</label>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php else: ?>
			<div>You are the only user in the system.</div>
		<?php endif; ?>
    <?php echo form_close(); ?>
    