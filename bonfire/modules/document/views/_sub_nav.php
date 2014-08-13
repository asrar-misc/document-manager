<ul class="nav nav-pills">
	<li <?php echo $this->uri->segment(4) == '' ? 'class="active"' : '' ?>>
		<a href="<?php echo site_url(SITE_AREA .'/content/document') ?>" id="list"><?php echo lang('document_list'); ?></a>
	</li>
	<?php if ($this->auth->has_permission('Document.Content.Create')) : ?>
	<li <?php echo $this->uri->segment(4) == 'create' ? 'class="active"' : '' ?> >
		<a href="<?php echo site_url(SITE_AREA .'/content/document/create') ?>" id="create_new"><?php echo lang('document_new'); ?></a>
	</li>
	<?php endif; ?>
</ul>