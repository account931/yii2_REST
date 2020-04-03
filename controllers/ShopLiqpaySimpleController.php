<?php
// LiqPay Shop => Simple version, more simple version of ShopLiqpay but without ajax, just php operating
//Uses DB, Liqpay does not
namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\AuthItem; //table with Rbac roles, NOT USED???
use app\models\LiqPay_2_Simple\InputModel; //model for input in modal window, gets id and quantity of 1 products when user adds it to cart
use app\models\LiqPay_2_Simple\CartInputModel;
use app\models\LiqPay_2_Simple\LiqpayShopSimple; //db table with products


class ShopLiqpaySimpleController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
				
				//To show message to unlogged users. Without this unlogged users will be just redirected to login page
				'denyCallback' => function ($rule, $action) {
                    throw new \yii\web\NotFoundHttpException("Only logged users are permitted(set in behaviors)!!!");
                 },
				 //END To show message to unlogged users. Without this unlogged users will be just redirected to login page
				 
				//following actions are available to logged users only 
                'only' => ['logout',/* 'add-admin',*/], //actionGetToken, actionChangePassword
                'rules' => [
                    [
                        'actions' => ['logout', /*'add-admin',*/ ], //actionGetToken, actionChangePassword
                        'allow' => true,
                        'roles' => ['@'],
                    ],
					
					// RBAC roles: actionAbout is avialable for users with role {adminX}-----
					[
                    'actions' => ['about'],
                    'allow' => true,
                    'roles' => ['adminX'],
                    ],
					//End RBAC roles: actionAbout is avialable for users with role {adminX}-----
					
                ],
            ],
			
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

	
	
	
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
		    //must be commented if want to use person actionError, otherwise errors will be handled with built vendor/yii\web\ErrorAction
            'error' => [
                'class' => 'yii\web\ErrorAction',  //pre-difined error handler, comment if want to use my personal
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

	
	
	

	
	
	
	
    /**
     * Displays Shop_2 Simple homepage.
     *
     * @return string
     */
	     
  // **************************************************************************************
  // **************************************************************************************
  // **                                                                                  **
  // **                                                                                  **
    public function actionIndex()
    {
		$myInputModel = new InputModel(); //model for input in modal window, gets id and quantity of 1 products when user adds it to cart
		$dbProducts = new LiqpayShopSimple(); //db table with products
		
		$allDBProducts = $dbProducts->find()->all();
		
		//!!!! if ($myInputModel->load(\Yii::$app->request->post())) is not used as URL for action is specified directly in fotm action attribute in view
		//if ($myInputModel->load(\Yii::$app->request->post()) ) { echo $myInputModel->yourInputValue;}
		
		
        return $this->render('index', [
		      'myInputModel' => $myInputModel,
			  'allDBProducts' => $allDBProducts
			  ]);
    }

	

	
	/**
     * Adds a product to cart (by form submit from actionIndex.php)
     *
     * 
     */
	     
  // **************************************************************************************
  // **************************************************************************************
  // **                                                                                  **
  // **                                                                                  **
    public function actionAddToCart()
    {
		if(!Yii::$app->request->post('InputModel')){
			throw new \yii\web\NotFoundHttpException("Bad request, You are not expected to enter this page");
		}
			
		
		//Cart exists in format: [id => quantity]
		//echo $_POST['InputModel']['yourInputValue']; //works
		$request = Yii::$app->request->post('InputModel'); //InputModel[yourInputValue];
		
		$itemsQuantity = $request['yourInputValue']; //gets quantity from form $_POST[]
		$productID = $request['productID']; //gets productID (hidden field) from form $_POST[]
		
		
		//find in $_SESSION['productCatalogue'] index the product by id, used in Flash
		 $keyN = array_search($productID , array_keys($_SESSION['productCatalogue'])); //find in $_SESSION['productCatalogue'] index the product by id
		
		
		//echo "Product: " . $productID . " quantity: " . $itemsQuantity;
		
		 if((int)$itemsQuantity == 0){
			if (isset($_SESSION['cart-simple-931t']) && isset($_SESSION['cart-simple-931t'][$productID]) ){//if Session is set and that productID is in it
				$temp = $_SESSION['cart-simple-931t'];//save Session to temp var
				unset($temp[$productID]);
				Yii::$app->session->setFlash('successX', 'Product <b> ' . $_SESSION['productCatalogue'][$keyN]['name'] . ' </b> was deleted from cart');
			} else {}
		} else {
            //session_start();
            if (!isset($_SESSION['cart-simple-931t'])) {//if Session['cart-simple-931t'] does not exist yet
			    $temp = array();
                $temp[$productID] = (int)$itemsQuantity;//в масив заносим количество of products 
            } else {//if if Session['cart-simple-931t'] already contains some products, ie. was prev added to cart
                $temp = $_SESSION['cart-simple-931t'];//save Session to temp var
                if (!array_key_exists($productID, $temp)) {//проверяем есть ли в корзине уже такой товар
                    $temp[$productID] = (int)$itemsQuantity; //в масив заносим количество тавара 1
                } else { //if product was not prev selected (added to cart)
				    $temp[$productID] = (int)$itemsQuantity;
			    }				
            }
			Yii::$app->session->setFlash('successX', 'Product<b> ' . $_SESSION['productCatalogue'][$keyN]['name'] . ' </b>was added to cart');
		}
		
        //$count = count($temp);//count products in cart
        $_SESSION['cart-simple-931t'] = $temp;//write temp var to Cart
       
	   
       return Yii::$app->getResponse()->redirect(['shop-liqpay-simple/index']);
    }

	
	
	
   // **************************************************************************************
  // **************************************************************************************
  // **                                                                                  **
  // **                                                                                  **
    public function actionCart()
    {
		$myInputModel = new CartInputModel();
		
		if ($myInputModel->load(\Yii::$app->request->post())){
			echo "<h2>" .$myInputModel->yourInputValue . "</h2>";
		}
		
		return $this->render('cart', [
		    'myInputModel' => $myInputModel
		]);
	}
	
	
	
}
