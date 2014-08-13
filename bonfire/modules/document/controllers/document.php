<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Document extends Authenticated_Controller {

	//--------------------------------------------------------------------
	private static $FILE_PATH = 'data\\';
	
	public static $ADMIN_ROLE_ID = '1';
	
	public static $PERMISSION_VIEW = '1';
	public static $PERMISSION_DOWNLOAD = '3';
	
	public function __construct()
	{
		parent::__construct();

		$this->auth->restrict();
		
		$this->load->model('document_model', null, true);
		$this->load->model('document_access_model', null, true);
		
		$this->load->model('users/User_model','user_model');
		
		$this->load->library('users/auth');
		
		$this->lang->load('document');
		
		Template::set_block('sub_nav', '_sub_nav');
	}

	//--------------------------------------------------------------------

	public function index()
	{
		// Deleting anything?
		if (isset($_POST['delete']))
		{
			$checked = $this->input->post('checked');

			if (is_array($checked) && count($checked))
			{
				$result = FALSE;
				foreach ($checked as $pid)
				{
					$result = $this->document_model->delete($pid);
				}

				if ($result)
				{
					Template::set_message(count($checked) .' '. lang('document_delete_success'), 'success');
				}
				else
				{
					Template::set_message(lang('document_delete_failure') . $this->document_model->error, 'error');
				}
			}
		}
		
		$active_user_id = $this->auth->user_id();
		
		if ($this->auth->role_id() == self::$ADMIN_ROLE_ID)
		{
			$this->document_model->where('deleted',0);
			$records = $this->document_model->find_all();
			
			$this->document_access_model->where('permission <> ',0);
			$access_list = $this->document_access_model->find_all();
		}
		else 
		{
			$records = $this->document_model->get_documents_by_userid($active_user_id);
			
			$this->document_access_model->where('permission <> ',0);
			$access_list = $this->document_access_model->find_all_by('user_id',$active_user_id);
		}

		$acl = array();
		if (!empty($access_list))
		{
			foreach ($access_list as $access)
				$acl[$access->document_id] = $access;
		}
		
		$users		= $this->user_model->find_all();
		$users_arr	= array();
		foreach ($users as $user)
			$users_arr[$user->id] = $user;
		
		Template::set('users_arr', $users_arr);
		
		Template::set('acl', $acl);
		Template::set('records', $records);
		
		Template::set('toolbar_title', 'Manage document');
		Template::render();
	}

	//--------------------------------------------------------------------

	public function upload()
	{
		$this->auth->restrict('Document.Content.Create');

		if ($this->input->post('save'))
		{
			if ($insert_id = $this->save_document())
			{
				// Log the activity
				$this->activity_model->log_activity($this->auth->user_id(), lang('document_act_create_record').': ' . $insert_id . ' : ' . $this->input->ip_address(), 'document');

				Template::set_message(lang('document_create_success'), 'success');
				Template::redirect('/document');
			}
			else
			{
				Template::set_message(lang('document_create_failure') . $this->document_model->error, 'error');
			}
		}

		Template::set('toolbar_title', lang('document_create') . ' document');
		Template::render();
	}
	
	public function delete($document_id)
	{
		$document = $this->document_model->find($document_id);
		
		if (($this->auth->role_id() == self::$ADMIN_ROLE_ID)||($document->document_owner_id == $this->auth->user_id()))
		{
			$result = $this->document_model->delete($document->id);
			if ($result)
				Template::set_message(lang('document_delete_success'), 'success');
			else
				Template::set_message(lang('document_delete_failure') . $this->document_model->error, 'error');
		}
		Template::redirect('/document');
	}
	
	public function download_file($id)
	{
		if ($this->has_permission_download($id))
		{
			$document = $this->document_model->find($id);
			
			$file	=	self::$FILE_PATH.$document->document_name;
			
			ob_clean();
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: public"); 
			header("Content-Description: File Transfer");
			header("Content-Type:".$document->document_name); // Send type of file
			$header="Content-Disposition: attachment; filename=".$document->document_name; // Send File Name
			header($header );
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".$document->document_size); // Send File Size
			@readfile($file);
			exit;
		}
		else 
		{
			Template::set_message('You do not have permission to download this document', 'error');
			Template::redirect('/document');
		}

	}
	
	//--------------------------------------------------------------------
	public function assign($document_id)
	{
		if ($this->has_permission_assign($document_id))
		{
			$document = $this->document_model->find($document_id);
			
			if ($this->input->post('save'))
			{
				if ($this->save_permissions($document_id))
				{
					// Log the activity
					$this->activity_model->log_activity($this->auth->user_id(), 'Assigned permission : ' . $this->input->ip_address(), 'document');
	
					Template::set_message('Permission Assigned successffully', 'success');
					Template::redirect('/document');
				}
				else
				{
					Template::set_message(lang('document_create_failure') . $this->document_model->error, 'error');
				}
			}
	
			$document_access = $this->document_access_model->find_all_by('document_id',$document_id);
			
			$this->load->model('users/user_model');
			
			$this->user_model->where('users.id <> ',$document->document_owner_id);
			$this->user_model->where('users.id <> ',$this->auth->user_id());
			$this->user_model->where('users.id <> ',self::$ADMIN_ROLE_ID);
			
			$users = $this->user_model->find_all();
			
			$user_access_array = array();
			
			if($document_access)
			{
				foreach ($document_access as $d)
					$user_access_array[$d->user_id] = $d->permission;
			}
			
			Template::set('users', $users);
			
			Template::set('user_access_array', $user_access_array);
			Template::set('toolbar_title', lang('document_access_assign') . ' document');
			Template::render();
		}
		else 
		{
			Template::set_message('Only owner of the document can assign permission.', 'error');
			Template::redirect('/document');
		}
	}	
	//--------------------------------------------------------------------
	
	//--------------------------------------------------------------------
	// !PRIVATE METHODS
	//--------------------------------------------------------------------

	private function save_document()
	{
		$this->form_validation->set_rules('document_name','Name','required');

		if ($this->form_validation->run() === FALSE)
		{
			return FALSE;
		}

		if($_FILES['document_name']['error']==0)
		{
			if (copy ( $_FILES['document_name']['tmp_name'], self::$FILE_PATH.$_FILES['document_name']['name']))
			{
				$data = array();
				$data['document_name']		= $_FILES['document_name']['name'];
				$data['document_type']		= $_FILES['document_name']['type'];
				$data['document_size']		= $_FILES['document_name']['size'];
				$data['document_owner_id']	= $this->auth->user_id();
		
				$id = $this->document_model->insert($data);
	
				if (is_numeric($id))
				{
					$return = $id;
				} else
				{
					$return = FALSE;
				}
							
			}
			else 
			{
				$return = FALSE;
			}
		}
		else 
		{
			$return = FALSE;
		}
		
		return $return;
	}

	//--------------------------------------------------------------------
	private function save_permissions($document_id)
	{
		if(isset($_POST['permission'])&&is_array($_POST['permission'])&& count($_POST['permission'])!=0)
		{
			$document_access = $this->document_access_model->find_all_by('document_id',$document_id);
			$existing_user_for_doc = array();
			
			if($document_access)
			{
				foreach ($document_access as $d)
					array_push($existing_user_for_doc, $d->user_id);
			}
			
			$data_insert = array();
			$data_update = array();
			foreach ($_POST['permission'] as $user_id => $permission)
			{
				$record = array();

				$record['user_id']		=	$user_id;
				$record['permission']	=	$permission;
				
				if (in_array($user_id, $existing_user_for_doc))
				{
					array_push($data_update, $record);
				}
				else
				{
					$record['document_id']	=	$document_id;
					
					array_push($data_insert, $record);
				}
			}
			
			$success = false;
			$this->db->trans_begin();
			
			if(!empty($data_insert))
			{
				if($this->document_access_model->insert_batch($data_insert))
				{
					if(!empty($data_update))
					{
						if($this->document_access_model->update_batch($data_update,'user_id'))
							$success = true;
					}
					else 
					{
						$success = true;
					}
				}
			}
			else 
			{
				if(!empty($data_update))
				{
					if($this->document_access_model->update_batch($data_update,'user_id'))
						$success = true;
				}
			}
			
			if ($success)
			{
				$this->db->trans_commit();
				return true;
			}
			else 
			{
				$this->db->trans_rollback();
				return false;
			}
			
		}
		return false;
	}
	
	private function has_permission_download($document_id)
	{
		if ($this->auth->role_id() == self::$ADMIN_ROLE_ID)
		{
			return true;
		}
		else 
		{
			$document = $this->document_model->find($document_id);
			if ($document->document_owner_id == $this->auth->user_id())
			{
				return true;
			}
			else 
			{
				$this->document_access_model->where('document_id',$document_id);
				$this->document_access_model->where('user_id',$this->auth->user_id());
				$this->document_access_model->where('permission',self::$PERMISSION_DOWNLOAD);
				$permission = $this->document_access_model->find_all();
				
				if (!empty($permission))
					return true;
				else 
					return false;
			}
		}
	}
	
	private function has_permission_assign($document_id)
	{
		
		if ($this->auth->role_id() == self::$ADMIN_ROLE_ID)
		{
			return true;
		}
		else 
		{
			$document = $this->document_model->find($document_id);
			if ($document->document_owner_id == $this->auth->user_id())
				return true;
			else 
				return false;
		}
	}
}