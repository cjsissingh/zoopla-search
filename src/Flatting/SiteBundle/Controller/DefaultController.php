<?php

namespace Flatting\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Guzzle\Http\Client;

class DefaultController extends Controller
{

    /**
     * @Route("/search")
     * @Template()
     */
    public function searchAction(Request $request)
    {
        $listings = $this->get('flatting.zoopla.search_provider')->search($request->query);

        return array('listings' => $listings);
    }
}
