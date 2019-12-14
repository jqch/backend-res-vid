<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\JsonResponse;
use BackendBundle\Entity\User;
use BackendBundle\Entity\Video;

class VideoController extends Controller
{
    public function newAction(Request $request)
    {
    	$helpers = $this->get("app.helpers");

        $hash = $request->get('authorization', null);
        $authCheck = $helpers->authCheck($hash);

        if ($authCheck) {
        	$identity = $helpers->authCheck($hash,true);

        	$json = $request->get('json', null);
        	if ($json != null) {
        		
	        	$params = json_decode($json);

	        	$createdAt = new \DateTime('now');
	        	$updatedAt = new \DateTime('now');
	        	$image = null;
	        	$video_path = null;

	        	$user_id = ($identity->sub != null)? $identity->sub : null;
	        	$title = (isset($params->title)) ? $params->title : null;
	        	$description = (isset($params->description)) ? $params->description : null;
	        	$status = (isset($params->status)) ? $params->status : null;

	        	if ($user_id != null && $title != null) {
	        		$em = $this->getDoctrine()->getManager();

	        		$user = $em->getRepository('BackendBundle:User')->find($user_id);

	        		$video = new Video();
	        		$video->setUser($user);
	        		$video->setTitle($title);
	        		$video->setDescription($description);
	        		$video->setStatus($status);
	        		$video->setCreatedAt($createdAt);
	        		$video->setUpdatedAt($updatedAt);

	        		$em->persist($video);
	        		$em->flush();

	        		// $video = $em->getRepository('BackendBundle:Video')->findOneBy(array(
           //              "user"      => $user,
           //              "title"     => $title,
           //              "status"    => $status,
           //              "createdAt" => $createdAt
	        		// ));

	        		$data = array(
	        		    "status" => "success",
	        		    "code" => 200,
	        		    "data" => $video
	        		);
	        	}else{
	        		$data = array(
	        		    "status" => "error",
	        		    "code"   => 500,
	        		    "msg"    => "Video not created !!"
	        		);
	        	}

        	}else{
        		$data = array(
        		    "status" => "error",
        		    "code"   => 500,
        		    "msg"    => "Video not ccreated, params failed"
        		);
        	}


        }else{
        	$data = array(
        	    "status" => "error",
        	    "code"   => 500,
        	    "msg"    => "Authorization not valid"
        	);
        }

        return $helpers->json($data);
    }
}