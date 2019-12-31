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

    public function editAction(Request $request, $id = null)
    {
    	$helpers = $this->get("app.helpers");

        $hash = $request->get('authorization', null);
        $authCheck = $helpers->authCheck($hash);

        if ($authCheck) {
        	$identity = $helpers->authCheck($hash,true);

        	$json = $request->get('json', null);
        	if ($json != null) {
        		
	        	$params = json_decode($json);

	        	$video_id = $id;

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

	        		$video = $em->getRepository('BackendBundle:Video')->find($video_id);

	        		if (is_object($video) && isset($identity->sub) && $identity->sub == $video->getUser()->getId()) {
	        			$video->setTitle($title);
	        			$video->setDescription($description);
	        			$video->setStatus($status);
	        			$video->setCreatedAt($createdAt);
	        			$video->setUpdatedAt($updatedAt);

	        			$em->persist($video);
	        			$em->flush();

	        			$data = array(
	        			    "status" => "success",
	        			    "code" => 200,
	        			    "msg" => "Video updated success"
	        			);
	        		}else{
	        			$data = array(
	        			    "status" => "error",
	        			    "code" => 400,
	        			    "msg" => "Video updated error, your not owner !!"
	        			);
	        		}
	        	}else{
	        		$data = array(
	        		    "status" => "error",
	        		    "code"   => 500,
	        		    "msg"    => "Video updated error !!"
	        		);
	        	}

        	}else{
        		$data = array(
        		    "status" => "error",
        		    "code"   => 500,
        		    "msg"    => "Video not updated, params failed"
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

    public function uploadAction(Request $request, $id)
    {
    	$helpers = $this->get("app.helpers");

        $hash = $request->get('authorization', null);
        $authCheck = $helpers->authCheck($hash);

        if ($authCheck) {
        	$identity = $helpers->authCheck($hash,true);

        	$video_id = $id;

        	$em = $this->getDoctrine()->getManager();
        	$video = $em->getRepository('BackendBundle:Video')->find($video_id);

        	if (is_object($video) && isset($identity->sub) && $identity->sub == $video->getUser()->getId()) {
        		$file = $request->files->get('image', null);
        		$file_video = $request->files->get('video', null);

        		if ($file != null && !empty($file)) {
        			$ext = $file->guessExtension();

        			if ($ext == "jpg" || $ext == "jpeg" || $ext == "png") {
        				$file_name = time().".".$ext;
        				$path_of_file = "uploads/video_images/video_".$video_id;
        				$file->move($path_of_file, $file_name);

        				$video->setImage($file_name);

        				$em->persist($video);
        				$em->flush();

        				$data = array(
        				    "status" => "success",
        				    "code" => 200,
        				    "msg" => "Image file for video uploaded !!"
        				);
        			}else{
        				$data = array(
        				    "status" => "error",
        				    "code" => 400,
        				    "msg" => "Format image not valid !!"
        				);
        			}

        			
        		}else{
        			if ($file_video != null && !empty($file_video)) {
        				$ext = $file_video->guessExtension();

        				if ($ext == "mp4" || $ext == "avi") {
        					$file_name = time().".".$ext;
	        				$path_of_file = "uploads/video_files/video_".$video_id;
	        				$file_video->move($path_of_file, $file_name);

	        				$video->setVideoPath($file_name);

	        				$em->persist($video);
	        				$em->flush();

	        				$data = array(
	        				    "status" => "success",
	        				    "code" => 200,
	        				    "msg" => "Video file uploaded !!"
	        				);
        				}else{
        					$data = array(
        					    "status" => "error",
        					    "code" => 400,
        					    "msg" => "Format for video not valid !!"
        					);
        				}
        			}
        		}

        	}else{
        		$data = array(
        		    "status" => "error",
        		    "code" => 400,
        		    "msg" => "Video updated error, your not owner !!"
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