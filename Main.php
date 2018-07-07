<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */

	public function __construct() 
	{
		parent::__construct();

// Load form helper library
		$this->load->helper('form');

// Load form validation library
		$this->load->library('form_validation');

		$this->load->helper('url');
// Load session library
		$this->load->library('session');


		$this->load->library('email');
// Load database
// // Load database
		$this->load->model('login_database');
// 		$this->load->model('menu');
	}



	public function index()
	{		
		$this->load->view('landingpage');

	}

	public function login()
	{
		if(isset($this->session->userdata['logged_in']))
		{
			$this->checklogin();
		}

		else
		{
			$this->load->view('login');
		}


	}


	public function register()
	{

		if(isset($this->session->userdata['logged_in']))
		{
			$this->checklogin();
		}
		$this->load->view('register');

	}



	

	public function geo1()
	{

		$distance = array('email' =>$_POST['email'],'destination' =>$_POST['destination'],'distance' => $_POST['distance'] );
		// print_r($distance);
		$this->db->insert('distance', $distance);  
	}


	public function checklogin()
	{
		$pdata = $this->session->userdata('logged_in');
// $createdby =  $_SESSION[logged_in['name']];                              
		if($pdata['rank']=='admin')
		{
			$this->load->view('admin/admin');
		}

		elseif ($pdata['rank']=='superadmin')
		{
			
			$this->load->view('super/super');


		}

		elseif($pdata['rank']=='student')
		{
			

			$fillform=$this->login_database->checkformfill1($pdata['email']);
			if ($fillform) {
				# code...
				$status1 = array('status' => "entered" ,'fillform' => $fillform);

			}
			else
			{
				$status1 = array('status' => "not_entered", 'fillform' => $fillform);

			}

			$this->load->view('student/student',$status1);



		}
		else
			$this->load->view('login');
	}

	

	function send_mail()
	{    

		$register=$this->input->post('register');
		
		if (isset($register)){
			$this->load->model('login_database');

			$data = array(
				'username' => $this->input->post('username'),
				'password' => $this->input->post('password'),
				'email' => $this->input->post('email')

				);
//check

			$redirect7 = (isset($_REQUEST['redirect'])) ? $_REQUEST['redirect'] :"main/register";

			$result = $this->login_database->register($data);

			if ($result == false)
			{


				for ($i = 0; $i < 60; $i++) {

					$arr[$i]=$i;
				}

				shuffle($arr);
				$pass="";
				$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
				for ($i = 0; $i < 15; $i++) {
					$n = $arr[$i];
					$pass.= $alphabet[$n];
				}


				$name=$data['username'];
				$email=$data['email'];
				$token=$pass;
				$password=$data['password'];

				$this->load->library('email'); 
				$this->email->from('info.shivam.gupta1997@gmail.com', 'Register for exam');


				$this->email->to($email,$name);
				$this->email->subject('Register for exam');

				$message ='<html><body>';
				$message.='<img src="http://4.bp.blogspot.com/-Y3ghZ5jMmIQ/WMYp0ODnGNI/AAAAAAAACBY/bdzU-OYtI5UMvuTYGFcFO6usbakrOuipQCK4B/s1600/SIH_SmallBanner.png">';
				$message.="<br><br><p><h2>User Name:-<strong>".$name."</strong></h2></p><p> <h2>Password   :<strong>".$password."</strong></h2></p><p>click the link below for activation&nbsp;&nbsp;<a href =\"https://192.168.137.157/hackathon/main/verify/".$token."\" taget=\"_blank\">click here </a></p>";
// $message.='<a href="#"><button>Login</button></a>';
				$message.='</body></html>';

				$this->email->message($message); 

				try{

					$this->email->send();

					$data_insert = array(
						'username' => $name,
						'password' => $password,
						'rank' => "student",
						'email' =>$email,
						'status' => '0',
						'token' => $token
						);

					$this->login_database->insert($data_insert);
					
					$err = array('error' =>"*Successfully Registered Kindly Verify Your Email Id" ,'color' =>"green" );
					$this->load->view('register',$err);
				}
				catch(Exception $e){
					echo $e->getMessage();

//mail not send 

				}
			}

			else
			{

				$err = array('error' =>"*Already Registered Kindly Log In" ,'color' =>"red" );
				$this->load->view('login',$err);
			}	
		}
		else
		{

//already registered
  // $this->load->view('alreadyregister');
			$err = array('error' =>"*Kindly Fill The Entries" ,'color' =>"red" );
			$this->load->view('register',$err);
		}	
	}

	public function verify($token)
	{



		$result=$this->login_database->verify($token);
		if($result)
		{
			$this->login_database->status($result[0]['username']);
			

			$err = array('error' =>"*Verified" ,'color' =>"green" );
			$this->load->view('login',$err);

		}
		else
		{ 
			$err = array('error' =>"*Unknown Verify Link " ,'color' =>"red" );
			$this->load->view('register',$err);
		}
	}



	public function auth()
	{
		if(isset($this->session->userdata['logged_in']))
		{


			$this->checklogin();


		}
		else
		{
			$login=$this->input->post('login');

			if (isset($login)){
				$this->form_validation->set_rules('username', 'Username', 'trim|required|xss_clean');
				$this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');

				if(isset($this->session->userdata['logged_in']))
				{


					$this->checklogin();


				}


				else {
					$data = array(
						'username' => $this->input->post('username'),
						'password' => $this->input->post('password')
						);
//check


				// $redirect7 = (isset($_REQUEST['redirect'])) ? $_REQUEST['redirect'] :
				// "main/login";

					$this->load->model('login_database');
					$result = $this->login_database->login($data);
					if ($result == TRUE) {

						$username = $this->input->post('username');
						$result = $this->login_database->read_user_information($username);

						if ($result != false) {
							$session_data = array(
								'username' => $result[0]->username,
								'rank' => $result[0]->rank,
								'email' => $result[0]->email
								);
// Add user data in session
							$this->session->set_userdata('logged_in', $session_data);

							$this->checklogin();
						}
					} 

					else {
						$err = array('error' =>"*Either username or password is wrong or email not verified" ,'color' =>"red" );
						$this->load->view('login',$err);
					}
				}

			}
			else
			{
				$err = array('error' =>"*Kindly Fill The Entries" ,'color' =>"red" );
				$this->load->view('login',$err);
			}
		}
	}

// Logout from admin page
	public function logout() {

// Removing session data
		$sess_array = array(
			'username' => ''
			);
		$this->session->unset_userdata('logged_in1', $sess_array);
		$this->session->sess_destroy();
		$data['message_display'] = 'Successfully Logout';
		$head=base_url();
		header("location:". $head);
	}

	public function payment()
	{

		if(isset($this->session->userdata['logged_in']))
		{
			$click=$this->input->post('formsubmit');
			if(isset($click))
			{
				$pdata=$this->session->userdata['logged_in'];
				$data = array(
					'email' => $pdata['email'],
					'first' => $this->input->post('first'),
					'last' => $this->input->post('last'),
					'father' => $this->input->post('father'),
					'gender' => $this->input->post('gender'),
					'dob' => $this->input->post('dob'),
					'street' => $this->input->post('street'),

					'city' => $this->input->post('city'),
					'state' => $this->input->post('state'),

					'pin' => $this->input->post('pin'),
					'pstreet' => $this->input->post('pstreet'),

					'pcity' => $this->input->post('pcity'),
					'pstate' => $this->input->post('pstate'),

					'ppin' => $this->input->post('ppin'),
					'allocationaddress' =>$this->input->post('pallocationaddress'),
					'physically' => $this->input->post('physically'),
					'adhar' => $this->input->post('adhar'),
					'mobile' => $this->input->post('mobile'),
					'allocation' => $this->input->post('allocation'),
					'contribution' => $this->input->post('contribution')
					);
				$this->db->insert('details',$data);

				$result=$this->login_database->read($pdata['email']);

				$data['center']= $this->db->get('center')->result();
				// echo $result[0]['street'];
		// $data['origin']
		// $this->load->view('geo',$data);
// 				$this->load->library('email'); 
// 				$this->email->from('info.shivam.gupta1997@gmail.com', 'Confirmation of filling of application form');


// 				$this->email->to($pdata['email'],$pdata['username']);
// 				$this->email->subject('Confirmation of filling of application form');

// 				$message ='<html><body>';
// 				$message.='<img src="http://4.bp.blogspot.com/-Y3ghZ5jMmIQ/WMYp0ODnGNI/AAAAAAAACBY/bdzU-OYtI5UMvuTYGFcFO6usbakrOuipQCK4B/s1600/SIH_SmallBanner.png">';
// 				$message.="<br><br><p><h2>User Name:-<strong>".$name."</strong></h2></p><p> <h2>Password   :<strong>".$password."</strong></h2></p><p>click the link below for activation&nbsp;&nbsp;<a href =\"https://localhost/hackathon/main/verify/".$token."\" taget=\"_blank\">click here </a></p>";

// 				$message.='</body></html>';

// 				$this->email->message($message); 

// 				try{

// 					$this->email->send();


// 				}
// 				catch(Exception $e){
// 					echo $e->getMessage();

// //mail not send 

// 				}


			}

			$this->load->view('student/payment');
			
		}
		else
		{
			$this->checklogin();

		}
	}

	public function centerallocation()
	{

		if(isset($this->session->userdata['logged_in']))
		{
			$pdata=$this->session->userdata['logged_in'];
			$fillform=$this->login_database->checkformfill($pdata['email']);
			if ($fillform) {
				# code...
				if(($fillform[0]['allocation'] == null) or ($fillform[0]['allocation'] == -1))
				{
					
					$id=-1;
					$this->login_database->pendingallocation($pdata['email'],$id);
				}


				$pdata=$this->session->userdata['logged_in'];
				$data['center']= $this->db->get('center')->result();
				$answer=$this->login_database->citystate($pdata['email']);
				if($answer[0]['allocationaddress']=="current")
				{
					$data['origin']= $answer[0]['city']." ".$answer[0]['state']." ".$answer[0]['pin'];
				}
				else
				{
					$data['origin']= $answer[0]['pcity']." ".$answer[0]['pstate']." ".$answer[0]['ppin'];
				}

				$data['alloc']=$answer[0]['allocation'];


				$do=$this->db->get('details')->result();
				$doo=$this->db->get('center')->result();

				foreach ($do as $f) {
	# code...
					foreach ($doo as $ff) {
	# code...
						if($f->allocation == $ff->id)
						{
							$data['did']=$ff->fulladdress;
						}
					}
				}


				$this->load->view('student/centerallocation',$data);

				
			}

			else
			{
				$fillform=$this->login_database->checkformfill1($pdata['email']);
				if ($fillform) {
				# code...
					$status1 = array('status' => "entered" ,'fillform' => $fillform);

				}
				else
				{
					$status1 = array('status' => "not_entered", 'fillform' => $fillform);

				}

				$this->load->view('student/student',$status1);
				

			}





		}
		else
		{
			$this->checklogin();

		}

	}

	public function admitcard()
	{

		if(isset($this->session->userdata['logged_in']))
		{


			$data['details']=$this->db->get('details')->result();
			$data['center']=$this->db->get('center')->result();

			$this->load->view('student/admitcard',$data);

		}
		else
		{
			$this->checklogin();

		}
	}


	public function geo()
	{


		if(isset($this->session->userdata['logged_in']))
		{
			$pdata=$this->session->userdata['logged_in'];
			$fillform=$this->login_database->checkformfill($pdata['email']);
			if ($fillform) {
				# code...


				$pdata=$this->session->userdata['logged_in'];
				$data['center']= $this->db->get('center')->result();
				$answer=$this->login_database->citystate($pdata['email']);
				if($answer[0]['allocationaddress']=="current")
				{
					$data['origin']= $answer[0]['city']." ".$answer[0]['state']." ".$answer[0]['pin'];
				}
				else
				{
					$data['origin']= $answer[0]['pcity']." ".$answer[0]['pstate']." ".$answer[0]['ppin'];
				}
				$data['alloc']=$answer[0]['allocation'];

				$dis=$this->login_database->finddistance($pdata['email']);
				$data['dis']=$dis;
				$this->load->view('geo',$data);




			}

			else
			{
				$fillform=$this->login_database->checkformfill1($pdata['email']);
				if ($fillform) {
				# code...
					$status1 = array('status' => "entered" ,'fillform' => $fillform);

				}
				else
				{
					$status1 = array('status' => "not_entered", 'fillform' => $fillform);

				}

				$this->load->view('student/student',$status1);

			}





		}
		else
		{
			$this->checklogin();

		}


	}


	public function adminallocation()
	{

		if(isset($this->session->userdata['logged_in']))
		{

			$this->load->view('admin/allocation');
		}
		else
		{
			$this->checklogin();

		}
	}

	public function admindeletecenter()
	{

		if(isset($this->session->userdata['logged_in']))
		{
			$data['center']=$this->db->get('center')->result();
			$this->load->view('admin/admindeletecenter',$data);
		}
		else
		{
			$this->checklogin();

		}
	}

	public function adminaddcenter()
	{

		if(isset($this->session->userdata['logged_in']))
		{
			$this->load->view('admin/adminaddcenter');
		}
		else
		{
			$this->checklogin();

		}
	}



	public function centerstrength()
	{

		if(isset($this->session->userdata['logged_in']))
		{
			$data['center']=$this->db->get('center')->result();
			$this->load->view('admin/centerstrength',$data);
		}
		else
		{
			$this->checklogin();

		}
	}



	public function physically()
	{

		if(isset($this->session->userdata['logged_in']))
		{

			$data['center']=$this->db->get('center')->result();
			$data['details']=$this->db->get('details')->result();

			$this->load->view('admin/physically',$data);
		}
		else
		{
			$this->checklogin();

		}
	}



	public function female()
	{

		if(isset($this->session->userdata['logged_in']))
		{

			$data['center']=$this->db->get('center')->result();
			$data['details']=$this->db->get('details')->result();

			$this->load->view('admin/female',$data);
		}
		else
		{
			$this->checklogin();

		}
	}



	public function male()
	{

		if(isset($this->session->userdata['logged_in']))
		{

			$data['center']=$this->db->get('center')->result();
			$data['details']=$this->db->get('details')->result();

			$this->load->view('admin/male',$data);
		}
		else
		{
			$this->checklogin();

		}
	}




	public function add()
	{

		if(isset($this->session->userdata['logged_in']))
		{
			
			$add=$this->input->post('add');

			if (isset($add)) {

				$data = array(

					'venue' =>$this->input->post('venue') , 
					'city' =>$this->input->post('city') , 
					'state' => $this->input->post('state'), 
					'max' => $this->input->post('max'), 
					'min' => $this->input->post('min'), 
					'convenience'=>$this->input->post('convenience'),
					'modeofexam'=>$this->input->post('modeofexam'),
					'shift'=>$this->input->post('shift'),
					'fulladdress'=>$this->input->post('fulladdress'),

					);

				$this->db->insert('center',$data);
			}




			$data['center']=$this->db->get('center')->result();
			$this->load->view('admin/admindeletecenter',$data);
		}
		else
		{
			$this->checklogin();

		}
	}



	public function delete()
	{

		if(isset($this->session->userdata['logged_in']))
		{
			$deleteid=$this->input->post('delete');
			$updateid=$this->input->post('update');

			if (isset($deleteid)) {
				# code...
				$this->login_database->deleteid($deleteid);
				$this->login_database->deletedistanceid($deleteid);

			}
			
			if (isset($updateid)) {
				# code...
				$max=$this->input->post('max');
				$min=$this->input->post('min');
				$this->login_database->updateid($updateid,$max,$min);
				$this->login_database->deletedistanceid($deleteid);

			}
			
			$data['center']=$this->db->get('center')->result();
			$this->load->view('admin/admindeletecenter',$data);
			
		}
		else
		{
			$this->checklogin();

		}
	}



	public function logicallotment()
	{

		if(isset($this->session->userdata['logged_in']))
		{
			$ds=$this->db->get('details')->result();
			$pu=0;
			foreach ($ds as $dse ) {
		# code...
				if(($dse->allocation == -1 )or ($dse->allocation == null))
					$pu=1;
			}


			if($pu){
				$distance=$this->db->get('distance')->result();
				$details=$this->db->get('details')->result();
				$center=$this->db->get('center')->result();
			// physically
				$flag=0;

				foreach ($details as $k ) {
				# code...
					if ($k->physically == "yes") {
						$flag=0;

						foreach ($distance as $dis) {
						# code...
							if ($dis->email == $k->email) {

								$sortit[$dis->destination]=$dis->distance;
	# code...
							}

						}

						asort($sortit,1);
						foreach ($sortit as $ku =>$va) {

							foreach ($center as $ka)
							{

								if ($ka->id == $ku) 
								{
									$fu=strrpos($va, ',');




									if (($ka->max>$ka->currentstudent )&&(!$fu)) {
									# code...
										$this->login_database->studentcurrent($ka->id);
										$this->login_database->detailsallocation($ka->id,$k->email);
										$flag=1;
										break;
									}
							# code...
								}
						# code...

							}
						# code...
							if ($flag==1) {
								# code...
								break;
							}
						}
					}



				}

			//physically 

				$distance=$this->db->get('distance')->result();
				$details=$this->db->get('details')->result();
				$center=$this->db->get('center')->result();
			// physically

//ladies
				$flag=0;

				foreach ($details as $k ) {
				# code...
					if (($k->physically == "no")&&($k->gender == "female") ) {
						$flag=0;

						foreach ($distance as $dis) {
						# code...
							if ($dis->email == $k->email) {

								$sortit[$dis->destination]=$dis->distance;
	# code...
							}

						}

						asort($sortit,1);
						foreach ($sortit as $ku =>$va) {

							foreach ($center as $ka)
							{

								if ($ka->id == $ku) 
								{
									$fu=strrpos($va, ',');




									if (($ka->max>$ka->currentstudent )&&(!$fu)) {
									# code...
										$this->login_database->studentcurrent($ka->id);
										$this->login_database->detailsallocation($ka->id,$k->email);
										$flag=1;
										break;
									}
							# code...
								}
						# code...

							}
						# code...
							if ($flag==1) {
								# code...
								break;
							}
						}
					}



				}



//ladies		

				$distance=$this->db->get('distance')->result();
				$details=$this->db->get('details')->result();
				$center=$this->db->get('center')->result();
			// physically

//rest
				$flag=0;

				foreach ($details as $k ) {
				# code...
					if (($k->physically == "no")&&($k->gender != "female") ) {

						$flag=0;

						foreach ($distance as $dis) {
						# code...
							if ($dis->email == $k->email) {

								$sortit[$dis->destination]=$dis->distance;
	# code...
							}

						}

						asort($sortit,1);
						print_r($sortit);
						foreach ($sortit as $ku =>$va) {

							foreach ($center as $ka)
							{

								if ($ka->id == $ku) 
								{
									$fu=strrpos($va, ',');




									if (($ka->max>$ka->currentstudent )&&(!$fu)) {
									# code...
										$this->login_database->studentcurrent($ka->id);
										$this->login_database->detailsallocation($ka->id,$k->email);
										$flag=1;
										break;
									}
							# code...
								}
						# code...

							}
						# code...
							if ($flag==1) {
								# code...
								break;
							}
						}
					}



				}


//	rest


			}
			$this->load->view('admin/allocation');

		}
		else
		{
			$this->checklogin();

		}
	}

	public function getca()
	{
		echo json_encode($this->db->get('center')->result_array());
	}

	public function faq()
	{
		$this->load->view('faq');
	}
	public function notice()
	{
		$this->load->view('notice');
	}
	public function aboutus()
	{
		$this->load->view('aboutus');
	}
	public function aboutgis()
	{
		$this->load->view('aboutgis');
	}

}