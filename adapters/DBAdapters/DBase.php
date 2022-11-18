<?php
	
	namespace DB;
	
	require 'DBase/dbase.inc.php';
	
	class DBase extends Adapter {
		
		function getName () {
			return 'dbase';
		}
		
		function getTitle () {
			return 'DBase';
		}
		
		function getVersion () {
			return '1.0';
		}
		
		function version (): string {
			return '';
		}
		
		protected function doConnect () {
			return dbase_open ($this->config['base_name'], 2);
		}
		
		protected function connectErrorText () {
			return 'Database file '.$this->config['base_name'].' not found';
		}
		
		protected function init () {
			$this->query_id = $this->db_id;
		}
		
		function doQuery ($query = [], $options = []) {
			
			if ($query and $query[0] == 'create')
				dbase_create ($this->config['base_name'], $query[2]);
			
			return $this->query_id;
			
		}
		
		protected function getAssoc ($query_id, $id) {
			
			$this->record++;
			if ($id <= 0) $id = $this->record;
			
			return dbase_get_record_with_names ($query_id, $id);
			
		}
		
		protected function getRow ($query_id) {
			return $this->getArray ($query_id);
		}
		
		protected $record = 0;
		
		protected function getArray ($query_id) {
			
			$this->record++;
			return dbase_get_record ($query_id, $this->record);
			
		}
		
		protected function getFields ($query_id) {
			return dbase_get_header_info ($query_id);
		}
		
		function insert_id () {
			return $this->rowsNum ();
		}
		
		function close () {
			dbase_close ($this->db_id);
		}
		
		protected function rowsNum ($query_id) {
			return dbase_numrecords ($query_id);
		}
		
		function __destruct () {
			$this->close ();
		}
		
	}