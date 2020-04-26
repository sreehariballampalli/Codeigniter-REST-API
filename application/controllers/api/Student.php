<?php

require APPPATH . 'libraries/REST_Controller.php';

class Student extends REST_Controller
{

  public function __construct()
  {
    parent::__construct();
    //load database
    $this->load->database();
    $this->load->model(array("api/student_model"));
    $this->load->library(array("form_validation"));
    $this->load->helper("security");
  }

  /*
    INSERT: POST REQUEST TYPE
    UPDATE: PUT REQUEST TYPE
    DELETE: DELETE REQUEST TYPE
    LIST: Get REQUEST TYPE
  */

  // POST: <project_url>/index.php/student
  public function index_post()
  {
    // insert data method
    // print_r($this->input->post());die;
    // collecting form data inputs
    $firstname = $this->security->xss_clean($this->input->post("firstname"));
    $lastname = $this->security->xss_clean($this->input->post("lastname"));
    $email = $this->security->xss_clean($this->input->post("email"));
    $mobile = $this->security->xss_clean($this->input->post("mobile"));
    $dob = $this->security->xss_clean($this->input->post("dob"));
    $city = $this->security->xss_clean($this->input->post("city"));
    $password = $this->security->xss_clean($this->input->post("password"));
    $cpassword = $this->security->xss_clean($this->input->post("cpassword"));

    // form validation for inputs
    $this->form_validation->set_rules("firstname", "FirstName", "required");
    $this->form_validation->set_rules("lastname", "LastName", "required");
    $this->form_validation->set_rules("email", "Email", "required|valid_email|is_unique[tbl_students.email]");
    $this->form_validation->set_rules("mobile", "Mobile", "required");
    $this->form_validation->set_rules("dob", "Dob", "required");
    $this->form_validation->set_rules("city", "City", "required");
    $this->form_validation->set_rules("password", "Password", "required");
    $this->form_validation->set_rules("cpassword", "Cpassword", "required");

    // checking form submittion have any error or not
    $user = $this->student_model->get_unique_rec($email);
    if (count($user) > 0) {
      $this->response(array(
        "status" => 0,
        "message" => "Email already exist"
      ), REST_Controller::HTTP_OK);
    } else if ($this->form_validation->run() === FALSE) {
      // we have some errors
      $this->response(array(
        "status" => 0,
        "message" => "All fields are needed"
      ), REST_Controller::HTTP_METHOD_NOT_ALLOWED);
    } else {
      if (
        !empty($firstname) && !empty($lastname) && !empty($email) && !empty($mobile) && !empty($dob)
        && !empty($city) && !empty($password) && !empty($cpassword)
      ) {
        // password and cpassword mismatch
        if ($password !== $cpassword) {
          $this->response(array(
            "status" => 0,
            "message" => "Password and confirm password are mismatched"
          ), REST_Controller::HTTP_METHOD_NOT_ALLOWED);
        } else {
          // all values are available/ rules are true
          $student = array(
            "firstname" => $firstname,
            "lastname" => $lastname,
            "email" => $email,
            "mobile" => $mobile,
            "dob" => $dob,
            "city" => $city,
            "password" => md5($password),
            "cpassword" =>  md5($cpassword),
          );
          // save data after all validations true
          if ($this->student_model->insert_student($student)) {
            $user = $this->student_model->get_unique_rec($student['email']);
            if (count($user) > 0) {
              $this->response(array(
                "status" => 1,
                "message" => "Student has been created",
                "data" => $user
              ), REST_Controller::HTTP_OK);
            } else {
              $this->response(array(
                "status" => 0,
                "message" => "Some thing went wrongt"
              ), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }
          } else {
            $this->response(array(
              "status" => 0,
              "message" => "Failed to create student"
            ), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
          }
        }
      } else {
        // we have some empty field
        $this->response(array(
          "status" => 0,
          "message" => "All fields are needed"
        ), REST_Controller::HTTP_METHOD_NOT_ALLOWED);
      }
    }

    /*$data = json_decode(file_get_contents("php://input"));
    $firstname = isset($data->firstname) ? $data->firstname : "";
    $lastname = isset($data->lastname) ? $data->lastname : "";
    $email = isset($data->email) ? $data->email : "";
    $mobile = isset($data->mobile) ? $data->mobile : "";
    $dob = isset($data->dob) ? $data->dob : "";*/
  }

  // PUT: <project_url>/index.php/student
  public function index_put()
  {
    // updating data method
    //echo "This is PUT Method";
    $data = json_decode(file_get_contents("php://input"));
    if (
      isset($data->id) && isset($data->firstname) && isset($data->lastname) && isset($data->email) && isset($data->mobile) && isset($data->dob) &&
      isset($data->city) && isset($data->password) && isset($data->cpassword)
    ) {
      // password and cpassword mismatch
      if ($data->password !== $data->cpassword) {
        $this->response(array(
          "status" => 0,
          "message" => "Password and confirm password are mismatched"
        ), REST_Controller::HTTP_METHOD_NOT_ALLOWED);
      } else {
        $student_id = $data->id;
        $student_info = array(
          "firstname" => $data->firstname,
          "lastname" => $data->lastname,
          "email" => $data->email,
          "mobile" => $data->mobile,
          "dob" => $data->dob,
          "city" => $data->city,
          "password" =>  md5($data->password),
          "cpassword" =>  md5($data->cpassword),
        );
        if ($this->student_model->update_student_information($student_id, $student_info)) {
          $user = $this->student_model->get_unique_rec($student_info['email']);
          if (count($user) > 0) {
            $this->response(array(
              "status" => 1,
              "message" => "Student data updated successfully",
              "data" => $user
            ), REST_Controller::HTTP_OK);
          } else {
            $this->response(array(
              "status" => 0,
              "message" => "Some thing went wrongt"
            ), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
          }
        } else {
          $this->response(array(
            "status" => 0,
            "messsage" => "Failed to update student data"
          ), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
      }
    } else {
      $this->response(array(
        "status" => 0,
        "message" => "All fields are needed"
      ), REST_Controller::HTTP_METHOD_NOT_ALLOWED);
    }
  }

  // DELETE: <project_url>/index.php/student
  public function index_delete()
  {
    // delete data method
    $data = json_decode(file_get_contents("php://input"));
    $student_id = $this->security->xss_clean($data->student_id);

    if ($this->student_model->delete_student($student_id)) {
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

  // GET: <project_url>/index.php/student
  public function index_get()
  {
    // list data method
    //echo "This is GET Method";
    // SELECT * from tbl_students;
    $students = $this->student_model->get_students();
    //print_r($query->result());
    if (count($students) > 0) {
      $this->response(array(
        "status" => 1,
        "message" => "Students found",
        "data" => $students
      ), REST_Controller::HTTP_OK);
    } else {
      $this->response(array(
        "status" => 0,
        "message" => "No Students found",
        "data" => $students
      ), REST_Controller::HTTP_METHOD_NOT_ALLOWED);
    }
  }

  //  user login
  public function login_post()
  {
    // Get the post data 
    $email = $this->post('email');
    $password = $this->post('password');

    // Validate the post data
    if (!empty($email) && !empty($password)) {

      // Check if any user exists with the given credentials
      // $con['returnType'] = 'single';
      $con = array(
        'email' => $email,
        'password' => md5($password),
        'status' => 1
      );
      // $user = $this->user->getRows($con);
      $students = $this->student_model->get_login_user($con);
      if (count($students) > 0) {
        $this->response(array(
          "status" => 1,
          "message" => "Students found",
          "data" => $students
        ), REST_Controller::HTTP_OK);
      } else {
        // Set the response and exit
        //BAD_REQUEST (400) being the HTTP response code
        $this->response("Wrong email or password.", REST_Controller::HTTP_BAD_REQUEST);
      }
    } else {
      // Set the response and exit
      $this->response("Provide email and password.", REST_Controller::HTTP_BAD_REQUEST);
    }
  }
}
