<?php
//MUST BE FIXED with!!!!! =>
      /*->andWhere([ 'or',
				             ['between', 'book_from_unix', strtotime($first1), strtotime($last1) ],
							 ['between', 'book_to_unix',   strtotime($first1), strtotime($last1) ]
				 ])*/
//Booking 1 room
//More OOP complicated version of BookingCPH, to view the same but in procedure style, view controllers/BookingCphController_RESERVED_PROCEDURE_STYLE
namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;

use app\models\BookingCph; //table BookingCph, SQL table{booking_cph}



class BookingCphController extends Controller
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
                    throw new \yii\web\NotFoundHttpException("Only logged users are permitted!!!");
                 },
				 //END To show message to unlogged users. Without this unlogged users will be just redirected to login page
				 
				//following actions are available to logged users only 
                'only' => ['index', ''], 
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
					
					
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
                'class' => 'yii\web\ErrorAction',  //predifined error handler, comment if want to use my personal
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

	
	
	
	
	
 
 

    public function actionIndex() //uses web/js/booking_cph.js!!!! //Includes adding to SQL from form
 // **************************************************************************************
 // **************************************************************************************
 // **                                                                                  **
 // **                                                                                  **
    {
	 date_default_timezone_set('UTC');  //use ('UTC') to avoid -1 day result    //('europe/kiev')	('UTC')
		
	 $model = new BookingCph(); //model of SQL table{booking_cph},  models/BookingCph. Used pass to view to create a form.
	 
	 //just for test, gets all record from SQL table{booking_cph}
	 //$all_records = BookingCph::find()->where(['book_user' => Yii::$app->user->identity->username])->all();
	 
	 
	 $model->book_user = Yii::$app->user->identity->username; //fill the field "Who is booking" default value
	 
	 
	 //SAVING THE FORM TO DB 
	 //if u filled in the form to book a new date range for a new guest
	 //if ($model->load(\Yii::$app->request->post()) && $model->save()) {
	 if ($model->load(\Yii::$app->request->post())) {
		 
		 //check if selected in form dates are not booked yet(not yet in DB BOOKINGCPH) //MUST FIX HERE!!!!!!!!!!!!!!!!!!!
		 $checkIf_free = BookingCph::find() 
		          ->where(['book_user' => Yii::$app->user->identity->username]) //if this line uncommented, each user has its own private booking(many users-> each user has own private booking appartment, other users cannot book it). Comment this if u want that booking is general, ie many users->one booking appartment(many users can book 1 general appartment) 
		          ->andWhere(['between', 'book_from_unix', strtotime($model->book_from), strtotime($model->book_to) ])  //strtotime("12-Aug-2019") returns unixstamp
				  ->orWhere (['between', 'book_to_unix',   strtotime($model->book_from), strtotime($model->book_to) ])  //(MARGIN MONTHS fix, when booking include margin months, i.e 28 Aug - 3 Sept) //strtotime("12-Aug-2019") returns unixstamp
				  ->orWhere (['and', ['<','book_from_unix',strtotime($model->book_from)], ['>', 'book_to_unix', strtotime($model->book_to) ] ]) //Fix situation when in DB we have 21-28Aug and user's input is 22-23Aug
				  /*->where(['>=',    'book_from_unix', strtotime($model->book_from) ]) //where DB book_from_unix bigger than strtotime($model->book_from)
                  ->andWhere(['<=', 'book_from_unix', strtotime($model->book_to) ])
				  ->andWhere(['<=', 'book_to_unix',   strtotime($model->book_to) ])
				   ->andWhere(['>=','book_to_unix',   strtotime($model->book_to) ])
				   */
				  ->all(); 
		 
		 if(empty($checkIf_free)){ //i.e if these form dates are not in DB, meaning free
		     if($model->save()){
                 \Yii::$app->session->setFlash("successX", "Successfully booked with guest <i class='fa fa-address-book-o' style='font-size:1.2em;'></i> <b> $model->book_guest</b>");
			     return $this->refresh(); //prevent  F5  resending	
             } else {
				 //will fire if model validation fails
		        \Yii::$app->session->setFlash('failedX', 'Booking Failed, Please click  <button data-target="#rbacAdd" data-toggle="collapse">&darr;&darr;</button> button to see details. &nbsp;&nbsp;<i class="fa fa-asterisk fa-spin" style="font-size:24px"></i>');
				//\Yii::$app->session->setFlash('failedX', null);
				//return $this->refresh(); //prevent  F5  resending	
				
                            
			 } 
		 } else {
			 \Yii::$app->session->setFlash('failedX', 'Booking Failed, These dates are already taken. Please click  <button data-target="#rbacAdd" data-toggle="collapse">NEW BOOKING</button> button to change dates &nbsp;&nbsp; <i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>');
		 }
         	 
	 }
	 
	 
	 





        
        return $this->render('index' , [
            'model' => $model,  //form model of models/BookingCph
			/*'current' => $current, // Act Record only- for current month summary
            'current1' => $current1, // Act Record only- for Previous month summary-> created dynamiclyy in for loop
            'current2' => $current2, // Act Record only- for Previous month summary
            'current3' => $current3, // Act Record only- for Previous month summary
            'current4' => $current4, 
            'current5' => $current5, 
           //'current6' => $current6, 
		    'all_records' => $all_records //just for test, gets all record from SQL table{booking_cph}
			*/
        ]);
    }
    
	// **                                                                                  **
    // **************************************************************************************
    // **************************************************************************************
	
	
	
	
	
	
	
	
	//function that works with ajax sent from js/booking_cph.js -> function get_6_month()
	//gets data for all 6 months
    // **************************************************************************************
    // **************************************************************************************
    // **                                                                                  **
    // ** 
    public function actionAjax_get_6_month() 
    {	
       $model = new BookingCph(); 
	   
	   date_default_timezone_set('UTC');  //use ('UTC') to avoid -1 day result    //('europe/kiev')	('UTC')
	   error_reporting(E_ALL & ~E_NOTICE); //JUST TO FIX 000wen HOSTING, Hosting wants this only for Ajax Actions!!!!!!!!!!!!!!!
	   
	   $array_All_Month = array();//will store all 6 month data
	   $array_All_Unix = array();//will store all Unix start & stop time for all 6 month, will be in format [[CurrentStart, Currentend], [start-1, end-1], [start-2, end-2]]
       $array_All_sqlData = array();//will store all sql results
	   $array_All_CountDays = array();//will store counts of booked days for every month (for badges)
	   
	   
	   for ($i = 0; $i < 9; $i++){
		   
		   //gets next 9 months list & adds it to array $array_All_Month, i.e $array_All_Month = ["Nov 2019", "Dec 2019", etc] 
		   $thisMonth = $model->get_All_Next_Months_List($i);  //this methods return=> array(Jul 2019, Jul) //pass {$i} as arg is a must
		   array_push($array_All_Month, $thisMonth[0]); // $thisMonth[0] => $PrevMonth . " " . $PrevYear; //i.e Jul 2019
		   
		   $y = $model->correctYear($thisMonth[1], $i);  //@param string $thisMonth[1], e.g $PrevMonth = 'Jan'// $thisMonth[1] => $PrevMonth, i.e 'Jul' //this methods returns=> array('may'=> 6, 'yearX'=> 2019)
		   
		   //gets next 9 months UnixTime & adds it to array $array_All_Unix, i.e $array_All_Unix =[[CurrentStart, Currentend], [start-1, end-1], [start-2, end-2]]
		   $unix = $model->get_Next_Months_Unix($y['may'], $y['yearX']); //$y['may']=> index of month, i.e if Jul returns => 6 //this methods returns=> array('array_tempo'=> [UnixStart, UnixEnd], 'first1'=> '2019-05-01', 'last1'=> '2019-05-31')
		   array_push($array_All_Unix, $unix['array_tempo']); //will store all Unix start & stop time for all 6 month, will be in format [[CurrentStart, Currentend], [start-1, end-1], [start-2, end-2]]
		   
		   //gets next 9 months booked dates info & adds them to array $array_All_sqlData, i.e $array_All_sqlData = [[{book_id: 55, book_user: "Dima", book_from: "2019-12-27", book_to: "2019-12-28"], [], []];
		   $sqlResult = $model->findBooked_Dates_In_Month($i, $unix['first1'], $unix['last1']); //Find SQL data for a specific NEXT month//$unix['first1']=> gets the first day of the current month, i.e "2019-05-01" //this methods returns=> ${'monthData'.$i}
		   array_push($array_All_sqlData, $sqlResult);//Find SQL data for a specific NEXT month (+ this current month in first iteration) (from 6-months range) one by one in a loop
		   
		   //count the quantity of booked days for every next 9 months & adds them to array $array_All_CountDays, i.e $array_All_CountDays = [0,2,4,0]
		   $badgesCount = $model->getCount_OfBooked_Days_For_Badges_and_FixMargins($sqlResult, $unix['first1'], $unix['last1']);
		   array_push($array_All_CountDays, $badgesCount);//will store counts of booked days for every month (for badges)
	   }
	 

		//RETURN JSON DATA
		  // Specify what data to echo with JSON, ajax usese this JSOn data to form the answer and html() it, it appears in JS consol.log(res)
         \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;  
          return [
             'result_status' => "OK", // return ajx status
             'code' => 100,	 
			 'allMonths' => $array_All_Month, //$r[0]['array_All_Month'], //.$array_All_Month, //["Nov 2019", "Dec 2019", etc] 
			 //'firstDay' => $r[1]['first'], //.$first,  =>empty as NOT USED(???)
			 //'dayofweekFirst' => $r[2]['weekdays']['dayofweek_first'], //.$weekdays[$dayofweek_first], //returns Sun, Mond, Tue.... 	=>empty as NOT USED(???)
			 'array_All_sqlData' => $array_All_sqlData, //$r[4]['array_All_sqlData'], // $array_All_sqlData,    //will all sql DB booking RESULTS,
			 'array_All_Unix' => $array_All_Unix, // $r[5]['array_All_Unix'], //$array_All_Unix,     //will store all Unix start & stop time for all 6 month, will be in format [[CurrentStart, Currentend], [start-1, end-1], [start-2, end-2]]
			 'array_All_CountDays' => $array_All_CountDays //$r[6] //$array_All_CountDays //will store counts of booked days for every month (for badges)
          				 
          ]; 
	}
    
	// **                                                                                  **
    // **************************************************************************************
    // **************************************************************************************


	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//function that works with ajax sent from js/booking_cph.js -> function get_1_single_month(thisX)
	//gets data from SQL for 1 single clicked month and build a calendar
	//DOESNOT RETURN JSON, IT OUTPUTS THE WHOLE READY CALENDAR
    // **************************************************************************************
    // **************************************************************************************
    // **                                                                                  **
    // **                                                                                  **
	 public function actionAjax_get_1_month() //ajax
     {
		 
		 //Since Yii 2.0.14 you cannot echo in controller. Response must be returned:
		 
		 //ini_set('display_errors', 1);
         //ini_set('display_startup_errors', 1);
		 error_reporting(E_ALL & ~E_NOTICE); //JUST TO FIX 000wen HOSTING, Hosting wants this only for Ajax Actions!!!!!!!!!!!!!!!
		 
		 $array_1_Month_days = array();//will store 1 month data days, ie [5,6,7,8]
		 $array_allGuests = array();//will store all guests in relevant order according to values in $array_1_Month_days , ie [name, name]
		 $MonthList= array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"); //General array for all click actions
		 
		 //guest list for $generalBookingInfo
		 //Forming here column names(like <TH>) for $guestList table, i.e(guest/start/end/duration/delete)
		 $guestList = "<div class='row border guestList'>" .  //div wrapper
		                 "<div class='col-sm-3 col-xs-2 bg-primary colX'>Guest </div>" . 
		                 "<div class='col-sm-3 col-xs-3 bg-primary colX'>From  </div>" . 
					     "<div class='col-sm-3 col-xs-3 bg-primary colX'>To    </div>" . 
					     "<div class='col-sm-2 col-xs-2 bg-primary colX'>Duration</div>" .
					     "<div class='col-sm-1 col-xs-2 bg-primary colX'>Delete  </div>" .
					   "</div>";
		 $overallBookedDays; //all amount of days booked in this month
		 $text;
		 
		 $start = (int)$_POST['serverFirstDayUnix']; //1561939200 - 1st July;  //var is from ajax, 1st day of the month in Unix 
		 $end = (int)$_POST['serverLastDayUnix']; //1564531200 - 31 July; //var is from ajax, the last day in this month, i.e amount of days in this month, i.e 31
		 
		 //SQL ActiveRecord:find all this 1 single month data
		 $thisMonthData = BookingCph::find() ->orderBy ('book_id DESC')  /*->limit('5')*/ 
		                ->where([ 'book_user' => Yii::$app->user->identity->username, /* 'mydb_id'=>1*/])   //if this line uncommented, each user has its own private booking(many users-> each user has own private booking appartment, other users cannot book it). Comment this if u want that booking is general, ie many users->one booking appartment(many users can book 1 general appartment) 
						->andWhere(['between', 'book_from_unix', $start, $end  ])   /*->andFilterWhere(['like', 'supp_date', $PrevMonth])  ->andFilterWhere(['like', 'supp_date', $PrevYear])*/    
						->orWhere (['between', 'book_to_unix',   $start, $end ])  //Start MARGIN MONTHS fix, when booking include margin months, i.e 28 Aug - 3 Sept)**********
						->all(); 
	     
		 $text = "<br><br><br><br><div><h2>" .date("F-Y", $start) . "</h2> <p><span class='example-taken'></span> - means booked dates</p></div><br>"; //header: month-year //returns July 2019 + color sample explain
		 $text.="<table class='table table-bordered'>";
		 $text.= "<tr><th> Mon </th><th> Tue </th><th> Wed </th><th> Thu </th><th> Fri </th><th> Sat </th><th> Sun </th></tr>";
		 
		
		
		
		
		
		
		 //complete $array_1_Month_days with booked days in this month, i,e [7,8,9,12,13]
		 if($thisMonthData){
		     foreach ($thisMonthData as $a)
			 
		     {
			    //Start MARGIN MONTHS fix, when booking include margin months, i.e 28 Aug - 3 Sept)*********************   
					//fix for 1nd margin month, i.e for {28 Aug-31 Aug}  from (28 Aug - 3 Sept) (i.e we take only 28 Aug - 31 Aug) 
				    if($a->book_to_unix > (int)$_POST['serverLastDayUnix']){ //if last booked day UnixStamp in this month is bigger than this month last day UnixStamp (i.e it means that this current loop booking is margin & last date of it ends in the next month )
					    //$temp = date("Y-m-d", $_POST['serverLastDayUnix']); //gets i.e 2019-07-31 (y-m-d), gets from Unix last day in this month, i.e 2019-07-31
					    $startDate = explode("-", $a->book_from); //i.e 2019-07-04 (y-m-d) split to [2019, 07, 04] //$startDate is a first booked day in DB for this month
						//$startDate = explode("-", $temp); //i.e 2019-07-01 (y-m-d) split to [2019, 07, 01] //$startDate is a first day for this month ;
						$diff = ((int)$_POST['serverLastDayUnix'] - $a->book_from_unix )/60/60/24; //i.e This month last day minus this loop DB booked start day
					}
				
				    //fix for 2nd margin month, i.e for {1 Sept-3 Sept}  from (28 Aug - 3 Sept) (i.e we take only 1 Sept - 3 Sept) 
				    else if($a->book_from_unix < (int)$_POST['serverFirstDayUnix']){    //if 1st booked day UnixStamp in this month is smaller than this month 1st day UnixStamp (i.e it means that this current loop booking is margin & start date of it begun in past month )
					    $temp = date("Y-m-d", $_POST['serverFirstDayUnix']); //gets i.e 2019-07-01 (y-m-d), gets from the fist day in this month, i.e 2019-07-01
					    $startDate = explode("-", $temp); //i.e 2019-07-01 (y-m-d) split to [2019, 07, 01] //$startDate is a first day for this month ;
						$diff = ($a->book_to_unix - (int)$_POST['serverFirstDayUnix'])/60/60/24; ////i.e This loop DB booked end day minus This month fisrt day 
					
                    //if booking is normal, withou margin month, i.e 12 Aug - 25 aug					
					} else {
						$startDate = explode("-", $a->book_from); //i.e 2019-07-04 (y-m-d) split to [2019, 07, 04] //$startDate is a first booked day in DB for this month
			            $diff = ( $a->book_to_unix - $a->book_from_unix)/60/60/24; // . "<br>";  //$diff = number of booked days in this month, i.e 17 (end - start)
					}
				//END MARGIN MONTHS fix, when booking include margin months, i.e 28 Aug - 3 Sept)**************************

				
				
			    
				
				
			
			    //complete $array_1_Month_days with booked days in this month, i,e [7,8,9,12,13]
			    for($i = 0; $i < $diff+1; $i++){
			       $d = (int)$startDate[2]++; //(int) is used to remove 0 if any, then do ++
			       array_push($array_1_Month_days, $d ); //adds to array booked days, i.e [7,8,9,12,13]
				   array_push/*array_unshift*/($array_allGuests, $a->book_guest); //adds to array a guest name to display as titile in calnedar on hover //use array_unshift() to add to begging(not end) of array
		        } 
				
			
			    //generating guest list var $guestList  for $generalBookingInfo, i.e(guest/start/end/duration/delete)
			    $singleGuestDuration = (( $a->book_to_unix - $a->book_from_unix)/60/60/24) + 1; //amount of booked days for every guest
			    $guestList.= "<div class='row border guestList'>" . 
				                 "<div class='col-sm-3 col-xs-2 colX'><i class='fa fa-calendar-check-o'></i>" . $a->book_guest . "</div>" . //guest
        			             "<div class='col-sm-3 col-xs-3 colX'>" . $a->book_from  .  "</div>" . //from
						         "<div class='col-sm-3 col-xs-3 colX'>" . $a->book_to    . "</div>"  . //to
						         "<div class='col-sm-2 col-xs-2 colX'>" . $singleGuestDuration . "</div>" . //duration
						         "<div class='col-sm-1 col-xs-2 colX deleteBooking iphoneX' id='" . $a->book_id . "'> <i class='fa fa-cut' style='color:red;'></i></div>" .  //Delete icon
							  "</div>";
			    $overallBookedDays+= (( $a->book_to_unix - $a->book_from_unix)/60/60/24) + 1; //all amount of days booked in this month
			
		     }
		 
		  }
		 
		 
		 //$guestList.= "</div>";//close div wrapper
		 
		 $dayofweek = /*"first day is " .*/ (int)date('w', $start); //returns the numeric equivalent of weekday of the 1st day of the month, i.e 1. 1 means Monday (first days of Loop month is Monday)
		 $dayofweek = (($dayofweek + 6) % 7) + 1; //Mega Fix of Sundays, as Sundays in php are represented as {0}, and with this fix Sundays will be {7}
		 //array_push($array_1_Month_days, $dayofweek ); 
		 
		 //just form $text to output, as we cane return array
		 /*foreach($array_1_Month_days as $x){
			 $text.= $x . "<br>";
		 }*/
		 
		 
		 //Var with general info, ie "In June u have 2 guests. Overal amount of days are 22."
		 $generalBookingInfo = "<br><h3>In <b>" . date("F", $start).  //i.e June*
		                       "</b> the amount of guests you have: <i class='fa fa-calendar-check-o'></i><b>&nbsp;" . count($thisMonthData) . "</b>. <br><br>" .
		                       "Overall amount of booked days are: <i class='fa fa-area-chart'></i>" . $overallBookedDays;
							   
		 $generalBookingInfo.= "<hr><p><b>Guest list:</b></p>" . $guestList;
		 
		 
		 
		 //START BUILDING A CALENDAR-------------------------------------------------
		 $breakCount = 0; //var to detect when to use a new line in table, i.e add <td>
		 $lastDayNormal = date("F-Y-d", $end);// gets the last day in normal format fromUnix, ie Jule-2019-31
		 $lastDay = explode("-", $lastDayNormal);//gets the last day in this month, i.e 31
		 $guestX = 0; //iterator to use in $array_allGuests
		 
		 //building blanks days, it is engaged only in case the 1st day of month(1) is not the first day of the week, i.e not Monday
		 for($i = 1; $i < $dayofweek; $i++){  //$dayofweek is the 1st week day of the month, i.e 1. 1 means Monday
			 $text.= "<td class='blank'>  </td>"; //Y
			 $breakCount++;
		 }
		 
		 //building the calendar with free/taken days
		 for($j = 1 /*$dayofweek*/; $j < (int)$lastDay[2]+1 /*count($array_1_Month_days)*/; $j++){  //$lastDay[2]+1 is a quantity of days in this month //$array_1_Month_days-> is an array with booked days in this month, i,e [7,8,9,12,13]
			 
			 //var to detect when to use a new line in table, i.e add <td>
			 if($breakCount%7 == 0){$text.= "<tr>";}
			 $breakCount++;
			 
			
			 if (in_array($j, $array_1_Month_days)){ //if iterator in array $array_1_Month_days, i.e this day is booked
			   $text.= "<td class='taken iphoneX' onClick='' title='already booked for " . $array_allGuests[$guestX]  ."'>" . $j . "</td>";  //title "booked for Guest name"
               $guestX++;			   
			 } else {
				 if ($j < 10){$v = "0" . $j;} else {$v = $j;} //if less than 10, add a zero, ie 09
				 $day = date("Y-m", $start) . "-" . $v; //construct this date in format 2011-12-31, to use in data-dayZ[] in js/booking_cph.js
				 $text.= "<td class='free iphoneX' onClick='' title='start booking' data-dayZ='".$day."'> " . $j . "</td>";
			 } 
		 }
		 
		 //building the calendar with dates, from last booked day till the last day of the month
		 /*
		 $lastDayNormal = date("F-Y-d", $end);// gets the last day in normal format fromUnix, ie Jule-2019-31
		 $lastDay = explode("-", $lastDayNormal);//gets the last day in this month, i.e 31
		 
		 $iter1 =  end($array_1_Month_days);  $iter1 = (int)$iter1;
		 $iter2 =  $iter1 + ((int)$lastDay[2] - $iter1);
		 
		 for($z = $iter1; $z < $iter2 ; $z++){ //end($array_1_Month_days) => last array el
		     if($breakCount%7 == 0){$text.= "<tr>";}
			 $breakCount++;
			 $text.= "<td class='free'>$z A</td>"; 
		 }
		 */
		 //END BUILDING A CALENDAR-------------------------------------------
		 
		 
		 
		 $text.= "</table><hr><hr>" . $generalBookingInfo . "<h4>Booked days array=>" .
		          implode("-", $array_1_Month_days) ."</h4>"; //implode("-", $array_1_Month_days)-> just to display array with booked days, i.e [4,5,6,18,19]
		 
		 return $text;
		 //return "OK <br>";
	 }
		
	// **                                                                                  **
    // **************************************************************************************
    // **************************************************************************************	
		
		
		
	
	
	
	
	
	
	
	
	//action to delete  a single booking. Comes as ajax from js/booking_cph.js-> run_ajax_to_delete_this_booking(passedID). Triggered in $(document).on("click", '.deleteBooking', function()
	// **************************************************************************************
    // **************************************************************************************
    // **                                                                                  **
    // **                                                                                  **
	 public function actionAjax_delete_1_booking() //ajax
     {
		$status = "Pending"; 
	    $thisMonthData = BookingCph::find() 
		     -> where([ 'book_id' => $_POST['serverBookingID']])  
			 ->where([ 'book_user' => Yii::$app->user->identity->username])
			 -> one() 
			 -> delete();  
			 
        if($thisMonthData){
			$status = "Deleted Successfully " . $_POST['serverBookingID'];
		} else {
			$status = "Failed deleting";
		}	
        //RETURN JSON DATA
		 // Specify what data to echo with JSON, ajax usese this JSOn data to form the answer and html() it, it appears in JS consol.log(res)
         \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;  
          return [
             'result_status' => "OK", // return ajx status
             'delete_status' => $status,		 
          ]; 		
	 }
	
    		
	// **                                                                                  **
    // **************************************************************************************
    // **************************************************************************************	
		
		

}
