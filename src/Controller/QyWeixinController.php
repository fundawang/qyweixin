<?php
/**
 * @file
 * Contains \Drupal\qyweixin\Controller\QyWeixinController.
 */

namespace Drupal\qyweixin\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller routines for qyweixin routes.
 */
class QyWeixinController extends ControllerBase {
	public function defaultResponse(Request $request) {
		if($request->getMethod==Request::METHOD_GET) {
			// We just skip the verify step with echostr returned at mean time, because we will be using same url /qyweixin as unified entry for all agents.
			if(!empty($request->get('msg_signature')) && !empty($request->get('timestamp')) && !empty($request->get('nonce')) && !empty($request->get('echostr')))
				return new Response($request->get('echostr'));
			else return new Response('', Response::HTTP_FORBIDDEN);
		} else 
			return new Response('', Response::HTTP_FORBIDDEN);
	}
}

?>
