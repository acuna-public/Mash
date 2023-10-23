<?php
	
	namespace DB;
	
	class MySQL extends Adapter {
		
		function getName () {
			return 'mysqli';
		}
		
		function getTitle () {
			return 'MySQL';
		}
		
		function getVersion () {
			return '2.0';
		}
		
		function table_quote () {
			return '`';
		}
		
		protected function getDefConfig () {
			
			return [
				
				'port' => 3306,
				'user' => 'root',
				'password' => '',
				'collate' => 'utf8',
				
			];
			
		}
		
		function version (): string {
			return mysqli_get_server_info ($this->db_id);
		}
		
		protected function doConnect () {
			return mysqli_connect ($this->config['host'], $this->config['user'], $this->config['password'], $this->config['base_name'], $this->config['port']);
		}
		
		protected function increment ($data) {
			return strtoupper ($data[0].($data[1] ? '('.$data[1].')' : '').' '.(($data[2] == 'null') ? 'null' : 'not null').' '.$data[3]).',';
		}
		
		protected function enum ($data, $items) {
			return strtoupper ($data[0].'('.$items.') '.(($data[2] == 'null') ? 'null' : 'not null').' default \''.$data[3].'\'').',';
		}
		
		protected function indexKeys ($key, $index) {
			return ' '.$this->addquotes ($key).' ('.trim ($index, ', ').')';
		}
		
		protected function engine ($engine) {
			return ' ENGINE='.$engine.' /*!40101 DEFAULT CHARACTER SET '.$this->addquotes ($this->config['collate']).' COLLATE '.$this->addquotes ($this->config['collate'].'_general_ci').' */';
		}
		
		protected function connectErrorCode () {
			return mysqli_connect_errno ();
		}
		
		protected function connectErrorText () {
			return mysqli_connect_error ();
		}
		
		protected function errorCode () {
			return mysqli_errno ($this->db_id);
		}
		
		protected function errorText () {
			return mysqli_error ($this->db_id);
		}
		
		protected function selectDB (): bool {
			return mysqli_select_db ($this->db_id, $this->config['base_name']);
		}
		
		protected function setCharset () {
			
			mysqli_query ($this->db_id, 'SET NAMES "'.$this->config['collate'].'"');
			mysqli_options ($this->db_id, MYSQLI_SET_CHARSET_NAME, $this->config['collate']);
			
		}
		
		protected function setReportsType () {
			
			if ($this->error_level == 1)
				mysqli_report (MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
			elseif ($this->error_level == 2)
				mysqli_report (MYSQLI_REPORT_ALL);
			
		}
		
		function doQuery ($query = [], $options = []) {
			return mysqli_query ($this->db_id, $query);
		}
		
		protected function getAssoc ($query_id, $id) {
			return mysqli_fetch_assoc ($query_id);
		}
		
		protected function getRow ($query_id) {
			return mysqli_fetch_row ($query_id);
		}
		
		protected function getArray ($query_id) {
			return mysqli_fetch_array ($query_id);
		}
		
		function insert_id () {
			return mysqli_insert_id ($this->db_id);
		}
		
		protected function getFields ($query_id) {
			return mysqli_fetch_field ($query_id);
		}
		
		function safesql ($source) {if (is_array ($source)) throw new \Exception2 ($source);
			return mysqli_real_escape_string ($this->db_id, $source);
		}
		
		protected function doFree ($query_id) {
			@mysqli_free_result ($query_id);
		}
		
		function close () {
			@mysqli_close ($this->db_id);
		}
		
		function __destruct () {
			mysqli_report (MYSQLI_REPORT_OFF);
		}
		
		protected function rowsNum ($query_id) {
			return mysqli_num_rows ($query_id);
		}
		
		function ping (): bool {
			return ($this->errorCode () != 2006);
		}
		
	}