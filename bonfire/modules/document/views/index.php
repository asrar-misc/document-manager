<?php $active_user_id = $this->auth->user_id(); ?>

<div class="admin-box">
	<h3 style="float: left;">Documents</h3><a id="upload_doc" href="<?php echo site_url('document/upload');?>" class="btn  btn-primary"  data-toggle="modal" data-target="#myModal" data-modal_title="Upload Document" style="float: right; margin-top: 12px;">Upload</a>
	<div class="clearfix"></div>
	<?php echo form_open($this->uri->uri_string()); ?>
		<table id="data-table" class="table table-condensed table-striped table-hover table-bordered">
			<thead>
				<tr>
					<?php if ($this->auth->has_permission('Document.Content.Delete') && isset($records) && is_array($records) && count($records)) : ?>
					<th class="column-check" style="width: 20px;"><input class="check-all" type="checkbox" /></th>
					<?php endif;?>
					
					<th style="width: 100px;">Name</th>
					<th style="width: 300px;">Type</th>
					<th style="width: 100px;">Size</th>
					<th style="width: 100px;">Owner</th>
					<th style="width: 100px;">Uploaded On</th>
					<th style="width: 150px;"></th>
				</tr>
			</thead>
			<?php if (isset($records) && is_array($records) && count($records)) : ?>
			<tfoot>
				<?php if ($this->auth->has_permission('Document.Content.Delete')) : ?>
				<tr>
					<td colspan="8">
						<?php echo lang('bf_with_selected') ?>
						<input type="submit" name="delete" id="delete-me" class="btn btn-danger" value="<?php echo lang('bf_action_delete') ?>" onclick="return confirm('<?php echo lang('document_delete_confirm'); ?>')">
					</td>
				</tr>
				<?php endif;?>
			</tfoot>
			<?php endif; ?>
			<tbody>
			<?php if (isset($records) && is_array($records) && count($records)) : ?>
			<?php foreach ($records as $record) : ?>
				<tr>
					<?php if ($this->auth->has_permission('Document.Content.Delete')) : ?>
					<td><input class="multi_record" type="checkbox" name="checked[]" value="<?php echo $record->id ?>" /></td>
					<?php endif;?>
					
					<td><?php echo $record->document_name ?></td>
				
					<td><?php echo $record->document_type?></td>
					<td><?php echo number_format(($record->document_size / 1048576),2).'MB';?></td>
					<td>
					<?php
						if ($record->document_owner_id == $this->auth->user_id())
							echo "self";
						else
							echo $users_arr[$record->document_owner_id]->username;
					?></td>
					
					<td><?php echo date('d-m-Y',strtotime($record->created_on));?></td>
					
					<td>
					<?php 
						echo '<span style="margin: 0px 5px;">';
							if (($this->auth->role_id() == Document::$ADMIN_ROLE_ID)||($record->document_owner_id == $active_user_id)||($acl[$record->id]->permission == Document::$PERMISSION_DOWNLOAD))
								echo anchor(site_url().'document/download_file/'. $record->id, '<i class="fa fa-download"></i>','title="Click to Download"');
							else 
								echo '<i class="fa fa-download"></i>';
						echo '</span>';
						
						echo '<span style="margin: 0px 5px;">';
							if (($this->auth->role_id() == Document::$ADMIN_ROLE_ID)||($record->document_owner_id == $active_user_id))
								echo anchor(site_url().'document/assign/'. $record->id, '<i class="fa fa-key"></i>','class="assign_permission" title="Grant Permission" data-toggle="modal" data-target="#myModal" data-modal_title="Assign Permissions for '.$record->document_name.'"');
							else
								echo '<i class="fa fa-key"></i>';
						echo '</span>';
					 
						echo '<span style="margin: 0px 5px;">';
							if (($this->auth->role_id() == Document::$ADMIN_ROLE_ID)||($record->document_owner_id == $active_user_id))
								echo anchor(site_url().'document/delete/'. $record->id, '<i class="fa fa-trash-o"></i>','title="Click to Delete" onclick="return confirm(\'Delete This Document?\');"');
							else
								echo '<i class="fa fa-trash-o"></i>';
						echo '</span>';
					?>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php else: ?>
				<tr>
					<td colspan="8">No records found that match your selection.</td>
				</tr>
			<?php endif; ?>
			</tbody>
		</table>
	<?php echo form_close(); ?>
</div>

<?php Assets::add_js("
		jQuery('.check-all').click(function(){
			var state = jQuery('.check-all:checked').val();
			if(typeof state === 'undefined')
			{
				jQuery('.multi_record').each(function(index,obj){
					jQuery(obj).prop('checked', false);
				});
			}
			else
			{
				jQuery('.multi_record').each(function(index,obj){
					jQuery(obj).prop('checked', true);
				});
			}
		})
		
		jQuery('.assign_permission').click(function(e){
			e.preventDefault();
			var obj = this;
			var url = jQuery(this).attr('href');
			jQuery('.modal-body').load(url,function(result){
				jQuery('#myModal').modal({show:true});
				jQuery('#myModalLabel').html(jQuery(obj).data('modal_title'));
			});
		});
		
		jQuery('#upload_doc').click(function(e){
			e.preventDefault();
			var obj = this;
			var url = jQuery(this).attr('href');
			jQuery('.modal-body').load(url,function(result){
				jQuery('#myModal').modal({show:true});
				jQuery('#myModalLabel').html(jQuery(obj).data('modal_title'));
			});
		});
		
		",'inline')?>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">Assign To</h4>
      </div>
      <div class="modal-body">
        
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->