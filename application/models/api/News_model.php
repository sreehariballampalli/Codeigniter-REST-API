<?php
class News_model extends CI_Model
{

  public function __construct()
  {
    parent::__construct();
    $this->load->database();
  }

  public function get_news()
  {
    $this->db->select("*");
    $this->db->from("tbl_news");
    $query = $this->db->get();
    return $query->result();
  }

  public function insert_news($data = array())
  {
    return $this->db->insert("tbl_news", $data);
  }

  public function delete_news($news_id)
  {
    // delete method
    $this->db->where("id", $news_id);
    return $this->db->delete("tbl_news");
  }

  public function update_news_information($id, $info)
  {
    $this->db->where("id", $id);
    return $this->db->update("tbl_news", $info);
  }

}
