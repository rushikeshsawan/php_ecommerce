<?php

namespace App\Controllers;

use App\Models\bannerModel;
use App\Models\categoryModel;
use App\Models\featuredCollectionModel;
use App\Models\productModel;
use App\Models\userModel;
use CodeIgniter\Encryption\Encryption;
use Exception;
use Razorpay\Api\Api;

/**
 * Summary of homepageController
 */
class cartController extends BaseController
{
    protected $db;
    protected $session;
    public function __construct()
    {
        $this->db = db_connect();
        $this->session = session();
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
                "quantity" => $quantity
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

    public function checkout()
    {
        
        $cart=$this->session->get('cart');
        $id=[];
        $price=0;
        foreach($cart as $product){
            // $product['id'];
            // $product['quantity'];
            $productModal= new productModel();
            array_push($id,$product['id']);
            // $id .= $product['id'] . ",";
            $result=$productModal->where('id',$product['id'])->first();
            
            // print_r($result['product_price']);
            $price += $result['product_price'] * $product['quantity'];
            // echo "<br>";
        }
        // $id=substr(trim($id), 0, -1);
        $data=[
            "productid"=>$id,
            "productamount"=>$price
        ];
        // echo $price;
        // echo "<br>-------------------------";
        // print_r($id);
        // die();
        return json_encode($data);
        // return view('checkout',["cart"=>$data]);
        if (empty($this->session->get('cart')))
            return "No Product";
        if (!empty($this->session->get('cart')))
            return json_encode($this->session->get('cart'));
        // echo "Given Array is not empty <br>";

    }
    public function checkpaymentsuccess(){
        $transactionid= $this->request->getVar()['transactionid'];
        $key= "rzp_test_kY0fqwqbnc0MN7";
        $secret="zOIiFIciOrNylqJPr6jFu5vI";
        $api =new Api($key,$secret);
        // print_r($api);
        try{
            // pay_LSbA0fPvzeyKtl failed 
            // pay_LSb4cBAfbfBVsE  success
            $payment= $api->payment->fetch('pay_LSb4cBAfbfBVsE');
            if($payment['status']==='authorized'){
                echo "success";
            }else{
                echo "failed";
            }
            print_r($payment);
            die();

        }catch(Exception $e){
            print_r($e);
        }
        die();

    }
}
