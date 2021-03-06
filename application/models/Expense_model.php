<?php
  class Expense_model extends CI_Model {
    public function __construct() {
      $this->load->database();
    }

    public function get_expense($month, $year) {
      $this->db->select('expense.id, date_recorded, employee_id, employee.first_name, employee.last_name, amount, receipt_no, details');
      $this->db->from('expense');
      $this->db->join('employee', 'expense.employee_id = employee.id');

      if(!empty($month)) {
        $this->db->where('date_recorded >=', $year.'-'.$month.'-01');
        $this->db->where('date_recorded <=', $year.'-'.$month.'-31');
      }

      $query = $this->db->get();

      return $query->result_array();
    }

    public function get_expense_by_id($id) {
      $this->db->select('*');
      $this->db->from('expense');
      $this->db->where('id', $id);

      $query = $this->db->get();
      return $query->row_array();
    }

    public function get_year($latest = FALSE) {
      if($latest)
        $this->db->select_max('DATE_FORMAT(date_recorded, "%Y")', 'year_number');
      else
        $this->db->select('DATE_FORMAT(date_recorded, "%Y") AS "year_number", MAX(DATE_FORMAT(date_recorded, "%m")) AS "max_month"');
      $this->db->from('expense');
      $this->db->order_by('year_number', 'DESC');

      if(!$latest)
        $this->db->group_by('DATE_FORMAT(date_recorded, "%Y")');

      $query = $this->db->get();
      if($latest)
        return $query->row_array();
      else
        return $query->result_array();
    }

    public function get_month($year, $latest = FALSE) {
      if(empty($year))
        $year = 'default';
      if($latest)
        $this->db->select_max('DATE_FORMAT(date_recorded, "%m")', 'month_number');
      else
        $this->db->select('DISTINCT DATE_FORMAT(date_recorded, "%m") AS "month_number", DATE_FORMAT(date_recorded, "%M") as "month_name"');
      $this->db->from('expense');
      $this->db->where('DATE_FORMAT(date_recorded, "%Y") LIKE', $year);
      $this->db->order_by('month_number', 'ASC');

      $query = $this->db->get();
      if($latest)
        return $query->row_array();
      else
        return $query->result_array();
    }

    public function insert_expense() {
      $data = array(
        'id' => uniqid(),
        'date_recorded' => date('Y-m-d'),
        'amount' => $this->input->post('amount'),
        'employee_id' => $this->session->userdata('id'),
        'receipt_no' => $this->input->post('receipt_no'),
        'details' => $this->input->post('details')
      );

      // return $this->db->set($data)->get_compiled_insert('expense');
      return $this->db->insert('expense', $data);
    }

    public function update() {
      $this->db->reset_query();

      $id = $this->input->post('id');
      $data = array(
        'date_recorded' => $this->input->post('date_recorded'),
        'amount' => $this->input->post('amount'),
        'receipt_no' => $this->input->post('receipt_no'),
        'details' => ucwords($this->input->post('details'))
      );

      $this->db->where('id', $id);
      // return $this->db->set($data)->get_compiled_update('expense');
      return $this->db->update('expense', $data);
    }

    public function delete() {
      return $this->db->delete( 'expense', array('id' => $this->input->post('id')) );
    }

    public function get_report_data() {
      $duration = $this->input->post('duration');
      $year = $this->input->post('year');
      $month = $this->input->post('month');
      $day = $this->input->post('day');

      $this->db->select('CONCAT(employee.first_name, " ", employee.last_name), expense.details, expense.receipt_no, expense.amount, expense.date_recorded');
      $this->db->from('expense');
      $this->db->join('employee', 'employee.id = expense.employee_id');

      if($duration == 'daily')
        $this->db->where('expense.date_recorded', $year.'-'.$month.'-'.$day);
      else if ($duration == 'monthly') {
        $this->db->where('expense.date_recorded BETWEEN "'.$year.'-'.$month.'-01" AND "'.$year.'-'.$month.'-31"');
        $this->db->order_by('expense.date_recorded', 'ASC');
      }

      $query = $this->db->get();
      return $query->result_array();
    }

    public function get_annual_data() {
      $year = $this->input->post('year');

      $this->db->select('DATE_FORMAT(date_recorded, "%m") AS "month", COUNT(amount) AS "count", SUM(amount) AS "sum"');
      $this->db->from('expense');
      $this->db->where('DATE_FORMAT(date_recorded, "%Y") =', $year);
      $this->db->group_by('month');
      $this->db->order_by('month', 'ASC');

      $query = $this->db->get();
      return $query->result_array();
    }
  }
?>