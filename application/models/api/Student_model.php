<?php

class Student_model extends CI_Model
{

  public function __construct()
  {
    parent::__construct();
    $this->load->database();
  }

  public function get_students()
  {
    $this->db->select("*");
    $this->db->from("tbl_students");
    $query = $this->db->get();
    return $query->result();
  }

  public function insert_student($data = array())
  {
    return $this->db->insert("tbl_students", $data);
  }

  public function delete_student($student_id)
  {
    // delete method
    $this->db->where("id", $student_id);
    return $this->db->delete("tbl_students");
  }

  public function update_student_information($id, $informations)
  {
    $this->db->where("id", $id);
    return $this->db->update("tbl_students", $informations);
  }

  public function get_login_user($params = array())
  {
    $condition = array('email' => $params['email'], 'password' => $params['password'], 'status' => $params['status']);
    $this->db->select("*");
    $this->db->from("tbl_students");
    $this->db->where($condition);
    $query = $this->db->get();
    return $query->result();
  }
// this method checks duplicate email id 
  public function get_unique_rec($email)
  {
    $condition = array('email' => $email);
    $this->db->select("*");
    $this->db->from("tbl_students");
    $this->db->where($condition);
    $query = $this->db->get();
    return $query->result();
  }
}
