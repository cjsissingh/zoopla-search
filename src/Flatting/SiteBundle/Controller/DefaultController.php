<?php

namespace Flatting\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Guzzle\Http\Client;

class DefaultController extends Controller
{

    /**
     * @Route("/search")
     * @Template()
     */
    public function searchAction()
    {
        $listings = $this->get('flatting.zoopla.search_provider')->search();

        return array('listings' => $listings);
    }
}
