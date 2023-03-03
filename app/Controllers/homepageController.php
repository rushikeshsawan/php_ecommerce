<?php

namespace App\Controllers;

use App\Models\bannerModel;
use App\Models\categoryModel;
use App\Models\featuredCollectionModel;
use App\Models\userModel;
use CodeIgniter\Encryption\Encryption;

class homepageController extends BaseController
{
    protected $encrypter;

    public function sendemail()
    {
        echo    date("Y-m-d h:i:sa") . "<br>";
        echo    date("Y-m-d h:i:sa", strtotime("+10 minutes"));

        die();
        $to = "sawant.rushikesh10@gmail.com";
        $subject = "OTP for your Forgotten Password";
        $otp = rand(100000, 999999);
        $message = "<b>Following is the OTP for your forgotten password- {$otp}</b>";
        $message .= "<h1>This is headline.</h1>";

        $header = "From:rushikesh.sawant@darwinpgc.com \r\n";
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html\r\n";

        $retval = mail($to, $subject, $message, $header);

        if ($retval == true) {
            echo "Message sent successfully... {$otp}";
        } else {
            echo "Message could not be sent...";
        }
    }

    public function index()
    {

        $userModel = new userModel();
        $homepageController = new homepageController();
        $categorydata = $homepageController->getCategoryData();
        $featuredCollectionModel = $homepageController->getfeaturedCollectionData();
        $bannerModel = $homepageController->getBannerModel();

        return view('index', ['categories' => $categorydata, 'featured' => $featuredCollectionModel, 'bannerModel' => $bannerModel]);
    }

    public function getBannerModel()
    {
        $bannerModel = new bannerModel();
        return $bannerModel->findAll();
    }


    public function getCategoryData()
    {
        $categoryModel = new categoryModel();
        $categoryModel = $categoryModel->findall();
        return $categoryModel;
    }


    public function getfeaturedCollectionData()
    {
        $featuredCollectionModel = new featuredCollectionModel();
        $featuredCollectionModel = $featuredCollectionModel->findall();
        return $featuredCollectionModel;
    }


    public function login()
    {

        $userModel = new userModel();

        $session = session();

        $email = $this->request->getVar()['email'];
        $password = md5($this->request->getVar()['password']);
        $result = $userModel->where('uname', $email)->where('pass', $password)->findAll();
        if (count($result) > 0) {
            // echo "<h1>success</h1>";
            // die();
            $session->set("user_id", $result[0]['id']);
            return redirect()->to("/home");
        } else {
            // echo "<h1>error</h1>";
            // die();
            $session->setFlashdata("error", "<strong>Invalid Credentials!</strong> Please Check Your Email & Password.");
            return redirect()->to('/home');
        }
    }

    public function signup()
    {
        $session = session();
        $userModel = new userModel();
        if ($this->request->getVar()['agree'] == 'on') {
            $firstname = $this->request->getVar()['first-name'];
            $lastname = $this->request->getVar()['last-name'];
            $email = $this->request->getVar()['email'];
            $password = md5($this->request->getVar()['password']);

            $data = [
                "name" => $firstname . " " . $lastname,
                "uname" => $email,
                "pass" => $password
            ];
            if ($userModel->insert($data)) {
                $session->setFlashdata("success", "<strong>Registration Successfull!</strong> Please Login to continue.");
                return redirect()->to("/home");
            } else {
                $session->setFlashdata("error", "<strong>Registration failed!</strong> Please try after sometime.");
                return redirect()->to("/home");
                echo "data not inserted successfully";
            }
            // echo $encrypter->decrypt($encrypter->encrypt($password));
            // die();
        }
    }


    public function logout()
    {
        $session = session();
        $session->destroy();
        return redirect()->to('/home');
    }
}
