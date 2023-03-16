<?php

namespace App\Controllers;

use App\Models\bannerModel;
use App\Models\categoryModel;
use App\Models\featuredCollectionModel;
use App\Models\productModel;
use App\Models\userModel;
use CodeIgniter\Encryption\Encryption;
use Exception;


/**
 * Summary of homepageController
 */
class homepageController extends BaseController
{
    protected $db;
    protected $session;
    public function __construct()
    {
        $this->db = db_connect();
        $this->session = session();
    }

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

    public function getproductdata()
    {
        $id = $this->request->getVar();
        $userModel = new productModel();
        $data = $userModel->where($id)->find();
        // print_r($data);
        return json_encode($data);
    }

    public function index()
    {

        $userModel = new userModel();
        $homepageController = new homepageController();
        $categorydata = $homepageController->getCategoryData();
        $featuredCollectionModel = $homepageController->getfeaturedCollectionData();
        $bannerModel = $homepageController->getBannerModel();
        $featureProduct = $homepageController->getFeatureProduct();
        return view('index', ['categories' => $categorydata, 'featured' => $featuredCollectionModel, 'bannerModel' => $bannerModel, 'featureProduct' => $featureProduct]);
    }

    public function getFeatureProduct()
    {
        return $res = $this->db->query("SELECT * FROM `product` WHERE status=1 ORDER BY CAST(product_price AS DECIMAL(10,2)) DESC LIMIT 8")->getResultArray();
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
        $isValid = [
            'email' => 'required|valid_email|is_not_unique[admin_login.email]',
            'password' => 'required|min_length[8]'
        ];

        if ($this->validate($isValid)) {

            $email = $this->request->getVar()['email'];
            $password = md5($this->request->getVar()['password']);
            $result = $userModel->where('email', $email)->where('password', $password)->findAll();
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
        } else {
            $session->setFlashdata("error", "<strong>Invalid Credentials!</strong> Please Check Your Email & Password.");
            return redirect()->back()->withInput();
        }
    }

    public function signup()
    {
        $session = session();
        $userModel = new userModel();
        if ($this->request->getVar()['agree'] == 'on') {
            $isValid = [
                'first-name' => 'required|min_length[3]',
                'last-name' => 'required|min_length[3]',
                'eemail' => 'required|valid_email|is_unique[admin_login.email]',
                'ppassword' => 'required|min_length[8]'
            ];
            if ($this->validate($isValid)) {
                $firstname = $this->request->getVar()['first-name'];
                $lastname = $this->request->getVar()['last-name'];
                $email = $this->request->getVar()['eemail'];
                $password = md5($this->request->getVar()['ppassword']);

                $data = [
                    "uname" => $firstname . " " . $lastname,
                    "email" => $email,
                    "password" => $password
                ];
                if ($userModel->insert($data)) {
                    $session->setFlashdata("success", "<strong>Registration Successfull!</strong> Please Login to continue.");
                    return redirect()->to("/home");
                } else {
                    $session->setFlashdata("error", "<strong>Registration failed!</strong> Please try after sometime.");
                    return redirect()->to("/home");
                }
            } else {
                $session->setFlashdata("error", "<strong>Registration failed!</strong> Please Check All the Details.");
                return redirect()->back()->withInput();
            }
        } else {
            $session->setFlashdata("error", "<strong>Registration failed!</strong> Please Check All the Details.");
            return redirect()->back()->withInput();
        }
    }


    public function logout()
    {
        $session = session();
        $session->remove('user_id');
        // $session->destroy();
        return redirect()->to('/home');
    }

    public function checkcarthasid($id)
    {
        $arrayy = $this->session->get('cart');
        $i = 0;
        foreach ($arrayy as $element) {
            // echo "hello hell";
            // print_r($element['id']);
            // die();
            if ($element["id"] == $id) {
                return [
                    "status" => true,
                    "id" => $i
                ];
            }
            $i++;
        }

        return [
            "status" => false,
            "id" => 0
        ];
    }

    public function saveproductcart()
    {
        $isValid = ['id' => 'required|is_natural|integer'];
        if ($this->validate($isValid)) {
            if ($this->request->getVar()['quantity'] && $this->request->getVar()['quantity'] > 0) {

                $quantity = $this->request->getVar()['quantity'];
            } else {
                $quantity = 1;
            }
            $id = $this->request->getVar()['id'];
            $data = [
                "id" => $id,
                "quantity" => 1
            ];
            $dataa = [
                "id" => $id
            ];
            if ($this->session->get('cart')) {
                $homepageController = new homepageController();
                $newdata = $this->session->get('cart');
                if ($homepageController->checkcarthasid($id)['status']) {

                    $newdata[$homepageController->checkcarthasid($id)['id']]['quantity'] = $newdata[$homepageController->checkcarthasid($id)['id']]['quantity'] + $quantity;
                    $this->session->set('cart', $newdata);
                    return true;
                } else {
                    $this->session->push('cart', [$data]);
                    return true;
                }
            } else {
                $this->session->set('cart', [$data]);
            }
        }
    }
    public function getsessioncart()
    {
        return json_encode($this->session->get('cart'));
    }
    public function getCart()
    {
        print_r($this->session->get('cart'));
    }
    public function removeCart()
    {
        $this->session->remove('cart');
    }


    public function decrementproductcart()
    {
        $id = $this->request->getVar()['id'];
        $homepageController = new homepageController();
        $result = $homepageController->checkcarthasid($id);
        if ($result['status']) {
            $newdata = $this->session->get('cart');
            if ($newdata[$result['id']]['quantity'] == 1) {
                unset($newdata[$result['id']]);
                $newdata = array_values($newdata);
                $this->session->set('cart', $newdata);
            } else {
                $newdata[$result['id']]['quantity'] = $newdata[$result['id']]['quantity'] - 1;
                $this->session->set('cart', $newdata);
            }
        } else {
            return;
        }
    }


    public function incrementproductcart()
    {

        $id = $this->request->getVar()['id'];
        $homepageController = new homepageController();
        $result = $homepageController->checkcarthasid($id);
        if ($result['status']) {
            $newdata = $this->session->get('cart');
            $newdata[$result['id']]['quantity'] = $newdata[$result['id']]['quantity'] + 1;
            $this->session->set('cart', $newdata);
        } else {
            return;
        }
    }
    public function deleteproductfromcart()
    {

        $id = $this->request->getVar()['id'];
        $newdata = $this->session->get('cart');
        $homeController = new homepageController();
        $result = $homeController->checkcarthasid($id);
        unset($newdata[$result['id']]);
        $newdata = array_values($newdata);
        $this->session->set('cart', $newdata);
        return 1;
    }

    /**
     * @param mixed $session 
     * @return self
     */
    public function setSession($session): self
    {
        $this->session = $session;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSession()
    {
        return $this->session;
    }
}
