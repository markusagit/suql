<?php
require ('SQLHandler.php');

class SuQLHandler extends SQLHandler
{
	private $stringBuffer1;
	private $stringBuffer2;
	private $canonicalQuery;
	private $osuql = [
		'queries' => []
	];
	private $query;
	private $table;

	const LEFT_JOIN = '<--';
	const RIGHT_JOIN = '-->';
	const INNER_JOIN = '<-->';

	function __construct() {
		$this->stringBuffer1 = '';
		$this->stringBuffer2 = '';
		$this->canonicalQuery = ['select' => [], 'from' => null, 'where' => [], 'join' => []];
		$this->query = 'main';
		$this->table = null;
		$this->osuql['queries'][$this->query] = $this->canonicalQuery;
	}

	public function output() {
		return $this->osuql;
	}

	public function TM_GO_0($ch) {
		$this->query = 'main';
		$this->table = null;
	}

	public function TM_STAY_table_alias($ch) {
		$this->stringBuffer1 .= $ch;
	}

	public function TM_GO_new_table_alias($ch) {
		$this->query = $this->stringBuffer1;
		$this->stringBuffer1 = '';
		$this->osuql['queries'][$this->query] = $this->canonicalQuery;
	}

	public function TM_GO_select($ch) {
		$this->stringBuffer1 .= $ch;
	}

	public function TM_STAY_select($ch) {
		$this->stringBuffer1 .= $ch;
	}

	public function TM_GO_new_select($ch) {
		$this->osuql['queries'][$this->query]['from'] = $this->stringBuffer1;
		$this->table = $this->stringBuffer1;
		$this->stringBuffer1 = '';
	}

	public function TM_GO_field($ch) {
		$this->stringBuffer1 .= $ch;
	}

	public function TM_STAY_field($ch) {
		$this->stringBuffer1 .= $ch;
	}

	public function TM_GO_new_field($ch) {
		$this->osuql['queries'][$this->query]['select'][$this->buildField($this->table, $this->stringBuffer1)] = $this->stringBuffer2;
		$this->stringBuffer1 = '';
		$this->stringBuffer2 = '';
	}

	public function TM_GO_select_end($ch) {
		if ($this->stringBuffer1)
			$this->osuql['queries'][$this->query]['select'][$this->buildField($this->table, $this->stringBuffer1)] = $this->stringBuffer2;
		$this->stringBuffer1 = '';
		$this->stringBuffer2 = '';
	}

	public function TM_GO_field_alias($ch) {
		$this->stringBuffer2 .= $ch;
	}

	public function TM_STAY_field_alias($ch) {
		$this->stringBuffer2 .= $ch;
	}

	public function TM_STAY_where_clause($ch) {
		$this->stringBuffer1 .= $ch;
	}

	public function TM_GO_where_clause_end($ch) {
		$this->osuql['queries'][$this->query]['where'][] = $this->stringBuffer1;
		$this->stringBuffer1 = '';
	}

	public function TM_STAY_join_clause($ch) {
		$this->stringBuffer1 .= $ch;
	}

	public function TM_GO_join_clause_end($ch) {

	}

	public function TM_GO_joined_select($ch) {
		$this->stringBuffer2 .= $ch;
	}

	public function TM_STAY_joined_select($ch) {
		$this->stringBuffer2 .= $ch;
	}

	public function TM_GO_new_joined_select($ch) {
		$this->osuql['queries'][$this->query]['join'][] = [
			'table' => $this->stringBuffer2,
			'on' => $this->stringBuffer1,
		];
		$this->table = $this->stringBuffer2;
		$this->stringBuffer1 = '';
		$this->stringBuffer2 = '';
	}
}
