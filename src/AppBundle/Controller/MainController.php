<?php
namespace AppBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Swift_Image;
use Swift_Attachment;
use AppBundle\Entity\Location;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
class MainController extends Controller
{
    public function activationSwitchAction()
    {
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager();
        $level = "";
        //if get post info
        if (isset($_POST['activation'])) {
            if($_POST['activation']=='deactivate'){
                $user->setArmed(false);
                $user->setLat(null);
                $user->setLng(null);
                $message = array("Bike Locker Successfully UnAchieved");
            }else{
                $user->setArmed(true);
                $lastShow = $this->getLocation(1, 'DESC');
                if (isset($lastShow[0])) {
                    $lastShow = $lastShow[0];
                    $user->setLat($lastShow->getLat());
                    $user->setLng($lastShow->getLng());
                    $message = array("Bike Locker Successfully Achieved");
                } else {
                    $message = array('Bike Locker Achieved Failed', 'Please install the Bike Tracker and wait for GPS signal first');
                    $user->setArmed(false);
                    $user->setLat(null);
                    $user->setLng(null);
                    $level = "error";
                }
            }
            $em->flush();
        }

    //return info
        if($user->getArmed()){
            $activation='activated';
            if(empty($level)){$level = "on";}
        }else{
            $activation='deactivated';
            if(empty($level)){$level = "off";}
        }

        $response=new JsonResponse();
        $response->setData(
          array(
              'response'=>'OK',
              'message'=>'',
              'activation'=>$activation,
              'level'=>$level
          )
        );
        return $response;
    }

    public function activeAction()
    {
    ///code from homeAction
        //Get Location Data form Database
        $products=$this->getLocation(20);

        //Calculate distance between two point
        $GeoCalculate = $this->get('geo.calculation');
        $diff=$GeoCalculate->calculation($products);

    ////end code from homeAction
        $this->processCommand($message,$level);

        $state=$this->isuseractive();

        return $this->render(':ees:active.html.twig',
            array('messages'=>$message,
                'userState'=>$state,
                'level'=>$level,
                'tableHeadings'=>array("ID","Address","timestamp","Ddistance","Dtime","Dspeed"),
                'products' => $products,
                'diff'=>$diff,
            )
        );
    }


    public function welcomeAction()
    {
        return $this->render(
            '::index.html.twig',
            array()
        );
    }


    public function settingAction(Request $request)
    {
       $user= $this->getuser();

        //build form
        $defaults= array(
            'sensitivity' =>$user->getMaxDistance(),
            'email'=>$user->getEmail(),

        );

        $form = $this->createFormBuilder($defaults)
            ->add('email', EmailType::class)
            ->add('sensitivity', IntegerType::class)
            ->getForm();

        //process form
        $request = Request::createFromGlobals();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $user->setEmail($data['email']);
            $user->setMaxDistance($data['sensitivity']);
            $em=$this->getDoctrine()->getManager();
            $em->flush();



            /*$em = $this->getDoctrine()->getManager();
            $qb = $em->createQueryBuilder();
            $q = $qb->update('AppBundle:User', 'u')
                ->set('u.maxDistance',$data['sensitivity'])
                ->set('u.email',$data['email'] )
                ->where('u.id = ?3')
                //->setParameter(1, $data['sensitivity'])
                //->setParameter(2, $data['email'])
                ->setParameter(3, $data['email'])
                ->getQuery();
            $p = $q->execute();*/
            // ... perform some action, such as saving the data to the database

            $response = new RedirectResponse($this->generateUrl('homepage'));
            $response->prepare($request);

            return $response->send();
        }

        //.....
        $state=$this->isuseractive();
        return $this->render(':ees:setting.html.twig', array(
            'form' => $form->createView(),
            'userState'=>$state
        ));
    }

    public function lostBikeAction()
    {

    }
/*
    public function mailAction()
    {
        $lastshow=$this->getLocation(1,'DESC');
        $lastshow=$lastshow[0];

        $user= $this->user(1);
        $this->sendmail($user,$lastshow);


        return $this->render('debug.html.twig',
            array('current' => $lastshow,
                'user'=>$user,)
        );
    }
*/

    public function homeAction(){
        //Get Location Data form Database
        $products=$this->getLocation(20);

        //Calculate distance between two point
        $GeoCalculate = $this->get('geo.calculation');
        $diff=$GeoCalculate->calculation($products);

        $state=$this->isuseractive();
        return $this->render('ees/home.html.twig',[
            'tableHeadings'=>array("ID","Address","timestamp","Ddistance","Dtime","Dspeed"),
            'products' => $products,
            'diff'=>$diff,
            'userState'=>$state
        ]);
    }



    public function addAction()
    {
        //(1)check user
        if (1==1)//isset($_POST['userapikey'])
        {
            $user=$this->addVerify($_POST['key']);//$_POST['key']
            if($user==null){
                throw $this->createNotFoundException("user not found");
            }
        }else{
            throw $this->createNotFoundException(
                "Bad Request");
        }
        //(2)addLcation

        $location=$this->addLocation($user->getId());

        //(3)check if armed
        if($user->getArmed()){
            $GeoCalculate = $this->get('geo.calculation');
            $distanceMoved=$GeoCalculate->GPSToDistance($user->getLat(),$user->getLng(),$_POST['lat'],$_POST['lng']);
                if($distanceMoved>$user->getMaxDistance())
                {
                    $this->sendmail($user,$location,$distanceMoved);
                }
            }


        return new Response('success' );
        /*return $this->render('debug.html.twig',
            array('user' => $user->getLatlng(),
                'location'=>$location->getLatlng(),)
        );*/
    }




    Public function tableAction()
    {
    //Get Location Data form Database
        $products=$this->getLocation();

    //Calculate distance between two point
        $GeoCalculate = $this->get('geo.calculation');
        $diff=$GeoCalculate->calculation($products);

    $state=$this->isuseractive();

        return $this->render('ees/table.html.twig',[
            'tableHeadings'=>array("ID","Address","timestamp","Ddistance","Dtime","Dspeed"),
            'products' => $products,
            'diff'=>$diff,
            'userState'=>$state
        ]);
    }




    public function findAction($id)
    {
        /*$product = $this->getDoctrine()
            ->getRepository('AppBundle:Location')
            ->findBy(
                array('timestamp'),
                array('timestamp'=>'DESC')
                );
        */
        $em = $this->getDoctrine()->getManager();
        $qb=$em->createQueryBuilder();
        $qb->select('l')
            ->from('Location','l')
            ->where ('l.id BETWEEN 5 AND 10')
            ->orderBy('l.id','ASC');
        $q=$qb->getQuery();
        $product=$q->getResult();

        /*
        if ($product) {
            $address=$product[0]->getAddress();
            //$address=$this->reverseGeocoding($product[0]->getLatlng());
        }else{
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        return $this->render('debug.html.twig',[
            //'body'=>''
            'body' =>'The address is '.$address,
            //'body' =>var_dump($data->status)
        ]);
        */
        return $this->render('::debug.html.twig',[
            'product'=>$product
        ]);
    }




    Public function mapAction()
    {
        $products=$this->getLocation();
        $state=$this->isuseractive();
        return $this->render('ees/map.html.twig',[
            'products' => $products,
            'userState'=>$state
        ]);
    }


    public function updateGeoAction()
    {
        $return[0]="init";
        $em = $this->getDoctrine()->getManager();
        $products = $em->getRepository('AppBundle:Location')->findBy(array('address'=>NULL));
        if ($products) {
            foreach($products as $i=>$product) {
                $return[$i]=$this->reverseGeocoding($product->getLatlng());
                $product->setAddress($return[$i]);
                $em->flush();
            }
        }else{
            throw $this->createNotFoundException(
                'All location has address '
            );
        }


        return $this->render('debug.html.twig',
            array(
                "address" => $return));
    }

//////Function
    public function user($id)
    {

        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->find($id);
        return $user;
    }

    private function getLocation($limit=1000,$order='ASC')
    {
        $User=$this->getUser();

        $repository = $this->getDoctrine()
            ->getRepository('AppBundle:Location');
        // createQueryBuilder automatically selects FROM AppBundle:Product
        // and aliases it to "p"
        $query = $repository->createQueryBuilder('l')
            ->where('l.userId = :userId')
            ->setParameter('userId',$User->getId() )
            ->orderBy('l.id', $order)
            ->setMaxResults($limit)
            ->getQuery();

        //$products=$query->setMaxResults(1)->getOneOrNullResult();
       return $products = $query ->getResult();
    }

    private function addVerify($API){
        $repository = $this->getDoctrine()
            ->getRepository('AppBundle:User');
        // createQueryBuilder automatically selects FROM AppBundle:Product
        // and aliases it to "p"
        $query = $repository->createQueryBuilder('u')
            ->where('u.newLocationKey = :API')
            ->setParameter('API',$API )
            ->setMaxResults(1)
            ->getQuery();

        $user=$query->getOneOrNullResult();
        return($user);
        /*DEBUG
        if($user==null){$abc='null';}else{$abc='yes';}
        return $this->render('::debug.html.twig',
        array('user'=>$user,
        'abc'=>$abc)
        );*/
    }
    private function addLocation($userId)
    {
        if (isset($_POST['lat'],$_POST['lng'])){
            if(is_numeric($_POST['lat'])&is_numeric($_POST['lng'])) {
                $location = new Location();
                $location->setLat($_POST['lat']);
                $location->setLng($_POST['lng']);
                $location->setAlt($_POST['alt']);
                $location->setSpeed($_POST['speed']);
                $location->setBearing($_POST['bearing']);
                $location->currentTime();
                $location->setAddress($this->reverseGeocoding($_POST['lat'].','.$_POST['lng']));
                $location->setUserId($userId);
                $em = $this->getDoctrine()->getManager();

                $em->persist($location);
                $em->flush();
            }else{
                throw $this->createNotFoundException(
                    "data wrong type"
                );
            }
        }else{
            throw $this->createNotFoundException(
                "Missing data"
            );
        }
        return $location;

    }
//temp reverseGeocoding
    public function reverseGeocoding($LatLng)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . $LatLng . '&key='.$this->getParameter('google_api_key').'&result_type=street_address');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json')); // Assuming you're requesting JSON
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response);
        if ($data->status == "OK") {
            return $data->results[0]->formatted_address;
        } else {
            return "NA";
        }
    }

    private function isuseractive(){
        $user=$this->getUser();
        return $user->getArmed();
    }

    public function sendmail($user,$lastshow,$distanceMoved)
    {
        $message = \Swift_Message::newInstance();
        $imageURL=$message->embed(Swift_Image::fromPath('https://maps.googleapis.com/maps/api/staticmap?center='.$user->getLatlng().'&zoom=13&size=1000x1000&maptype=roadmap&markers=color:red%7Clabel:M%7C'.$lastshow->getLatlng().'&markers=color:blue%7Clabel:L%7C'.$user->getLatlng().'&key='.$this->getParameter('google_api_key'), 'image/jpeg')
            ->setFilename(date("D M d, Y G:i").'.jpg')
            ->setDisposition('inline'));
        $message
            ->setSubject('Bike Moved at '.date("Y G:i"))
            ->setFrom('alert@biketracker.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                // app/Resources/views/Emails/registration.html.twig
                    'ees/mail.html.twig',
                    array('current' => $lastshow,
                        'user'=>$user,
                        'imageURL'=>$imageURL,
                        'distanceMoved'=>$distanceMoved,)
                ),
                'text/html'
            )
            /*
             * If you also want to include a plaintext version of the message
            ->addPart(
                $this->renderView(
                    'Emails/registration.txt.twig',
                    array('name' => $name)
                ),
                'text/plain'
            )
            */
        ;
        $this->get('mailer')->send($message);
    }

    public function processCommand(&$message,&$level)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (isset($_GET['command'])) {
            $command = $_GET['command'];

            switch ($command) {
                case "activate":
                    $user->setArmed(true);
                    $lastShow = $this->getLocation(1, 'DESC');
                    if (isset($lastShow[0])) {
                        $lastShow = $lastShow[0];
                        $user->setLat($lastShow->getLat());
                        $user->setLng($lastShow->getLng());
                        $message = array("Bike Locker Successfully Achieved");
                        $level = "success";
                    } else {
                        $message = array('Bike Locker Achieved Failed', 'Please install the Bike Tracker and wait for GPS signal first');
                        $user->setArmed(false);
                        $user->setLat(null);
                        $user->setLng(null);
                        $level = "danger";
                    }
                    break;
                case "deactivate":
                    $user->setArmed(false);
                    $user->setLat(null);
                    $user->setLng(null);
                    $message = array("Bike Locker Successfully UnAchieved");
                    $level = "warning";
                    break;
            }
            $em->flush();
        }
    }

}