<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Document_model extends BF_Model {

	protected $table		= "document";
	protected $key			= "id";
	protected $soft_deletes	= true;
	protected $date_format	= "datetime";
	protected $set_created	= true;
	protected $set_modified = true;
	protected $created_field = "created_on";
	protected $modified_field = "modified_on";
	
	public function get_documents_by_userid($user_id)
	{
		$query = "
		select * from bf_document
		where bf_document.deleted = 0 and (bf_document.document_owner_id = ".$user_id."
		or bf_document.id IN (
			select bf_document_access.document_id 
			from bf_document_access 
			where bf_document_access.user_id = ".$user_id." and bf_document_access.permission <> 0));";
		
		return $this->db->query($query)->result();
	}
}
