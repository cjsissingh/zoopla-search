<?php

namespace Flatting\ZooplaBundle\Provider;

use Guzzle\Http\Client;

class SearchProvider
{

    private $listings = array();

    /**
     * @Route("/search")
     * @Template()
     */
    public function search()
    {
        //?postcode=E1+5LJ&listing_status=rent&maximum_price=1000&minimum_beds=3&radius=2&api_key=hu29vtwe48srv5czujdp5cee
        $client = new Client('http://api.zoopla.co.uk');

        $request = $client->get('/api/v1/property_listings.js');
        $requestQuery = $request->getQuery();
        $requestQuery->set('api_key', 'hu29vtwe48srv5czujdp5cee');
        $requestQuery->set('postcode', 'E1+5LJ');
        $requestQuery->set('listing_status', 'rent');
        $requestQuery->set('radius', '1.5');
        $requestQuery->set('order_by', 'age');
        $requestQuery->set('furnished', 'furnished');

        $requestQuery->set('maximum_price', '600');
        $requestQuery->set('minimum_beds', '3');
        $requestQuery->set('maximum_beds', '3');

        $response = $request->send();

        $data = $response->json();

        $this->processListings($data);

        $requestQuery->set('maximum_price', '800');
        $requestQuery->set('minimum_beds', '4');
        $requestQuery->set('maximum_beds', '4');

        $response = $request->send();

        $data = $response->json();

        $this->processListings($data);



        $requestQuery->set('maximum_price', '1000');
        $requestQuery->set('maximum_beds', '5');
        $requestQuery->set('minimum_beds', '5');

        $response = $request->send();

        $data = $response->json();

        $this->processListings($data);

        $requestQuery->set('maximum_price', '1200');
        $requestQuery->set('maximum_beds', '6');
        $requestQuery->set('minimum_beds', '6');

        $response = $request->send();

        $data = $response->json();

        $this->processListings($data);

        usort($this->listings, function($a, $b){
            $aDate = new \DateTime($a['first_published_date']);
            $bDate = new \DateTime($b['first_published_date']);

            return $aDate < $bDate;
        });

        $this->listings = array_filter($this->listings, function($listing){
            return $listing['average_price_pcm'] < 800 || $listing['average_price_pw'] < 200;
        });


        return $this->listings;
    }

    private function processListings($data)
    {
        foreach($data['listing'] as $listing){
            $listing['average_price_pcm'] = $this->getAveragePricePcm($listing);
            $listing['average_price_pw'] = $this->getAveragePricePw($listing);
            $this->listings[] = $listing;
        }
    }

    private function getAveragePricePcm($listing)
    {
        return $listing['rental_prices']['per_month'] / $listing['num_bedrooms'];
    }

    private function getAveragePricePw($listing)
    {
        return $listing['rental_prices']['per_week'] / $listing['num_bedrooms'];
    }
}
