<?php
require APPPATH . 'libraries/REST_Controller.php';

class News extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        //load database
        $this->load->database();
        $this->load->model(array("api/news_model"));
        $this->load->library(array("form_validation"));
        $this->load->helper("security");
        $this->load->helper('text');
    }

    /*
    INSERT: POST REQUEST TYPE
    UPDATE: PUT REQUEST TYPE
    DELETE: DELETE REQUEST TYPE
    LIST: Get REQUEST TYPE
  */

    // POST: <project_url>/index.php/news
    public function index_post()
    {
        // insert data method
        // print_r($this->input->post());die;
        // collecting form data inputs
        $headline = $this->security->xss_clean($this->input->post("headline"));
        $description = $this->security->xss_clean($this->input->post("description"));
        $category = $this->security->xss_clean($this->input->post("category"));
        $created_by = $this->security->xss_clean($this->input->post("created_by"));
        $status = $this->security->xss_clean($this->input->post("status"));

        $config = array(
            'upload_path' => "./images/api/news",             //path for upload
            'allowed_types' => "gif|jpg|png|jpeg",   //restrict extension
            'max_size' => '100',
            'max_width' => '1024',
            'max_height' => '768',
            'file_name' => 'image_' . date('ymdhis')
        );
        $this->load->library('upload', $config);

        if ($this->upload->do_upload('imageurl')) {
            $data = array('upload_data' => $this->upload->data());
            $img_path = $config['upload_path'] . '/' . $data['upload_data']['orig_name'];
            /* // Write query to store image details of login user { }
            $returndata = array('status' => 0, 'data' => 'user details', 'message' => 'image uploaded successfully');
            $this->set_response($returndata, 200); */
        }
        //$imageurl = $this->security->xss_clean($this->input->post("imageurl"));
        // form validation for inputs
        $this->form_validation->set_rules("headline", "Headline", "required");
        $this->form_validation->set_rules("description", "Description", "required");
        $this->form_validation->set_rules("category", "Category", "required");
        $this->form_validation->set_rules("imageurl", "imageurl");
        $this->form_validation->set_rules("created_by", "Author");
        $this->form_validation->set_rules("imageurl", "imgurl");
        // checking form submittion have any error or not
        if ($this->form_validation->run() === FALSE) {
            // we have some errors
            $this->response(array(
                "status" => 0,
                "message" => "All fields are needed"
            ), REST_Controller::HTTP_METHOD_NOT_ALLOWED);
        } else {
            if (
                !empty($headline) && !empty($description) && !empty($category)
                && !empty($status)
            ) {
                // all values are available/ rules are true
                $news = array(
                    "headline" => $headline,
                    "description" => $description,
                    "category" => $category,
                    "imageurl" => $img_path,
                    "created_by" => $created_by,
                    "status" => $status
                );
                // save data after all validations true
                if ($this->news_model->insert_news($news)) {
                    $this->response(array(
                        "status" => 1,
                        "message" => "News has been created"
                    ), REST_Controller::HTTP_OK);
                } else {
                    $this->response(array(
                        "status" => 0,
                        "message" => "Failed to create news"
                    ), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {
                // we have some empty field
                $this->response(array(
                    "status" => 0,
                    "message" => "All fields are needed"
                ), REST_Controller::HTTP_METHOD_NOT_ALLOWED);
            }
        }
    }


    // DELETE: <project_url>/index.php/news
    public function index_delete()
    {
        // delete data method
        $data = json_decode(file_get_contents("php://input"));
        $student_id = $this->security->xss_clean($data->student_id);

        if ($this->news_model->delete_news($student_id)) {
            // retruns true
            $this->response(array(
                "status" => 1,
                "message" => "Student has been deleted"
            ), REST_Controller::HTTP_OK);
        } else {
            // return false
            $this->response(array(
                "status" => 0,
                "message" => "Failed to delete student"
            ), REST_Controller::HTTP_METHOD_NOT_ALLOWED);
        }
    }

    // GET: <project_url>/index.php/new
    public function index_get()
    {
        $news = $this->news_model->get_news();
        //print_r($query->result());
        if (count($news) > 0) {
            $this->response(array(
                "status" => 1,
                "message" => "News found",
                "data" => $news
            ), REST_Controller::HTTP_OK);
        } else {
            $this->response(array(
                "status" => 0,
                "message" => "No news found",
                "data" => $news
            ), REST_Controller::HTTP_METHOD_NOT_ALLOWED);
        }
    }
}
