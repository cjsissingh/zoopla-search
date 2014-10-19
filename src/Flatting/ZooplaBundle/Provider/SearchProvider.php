<?php

namespace Flatting\ZooplaBundle\Provider;

use Symfony\Component\HttpFoundation\ParameterBag;

use Guzzle\Http\Client;

class SearchProvider
{

    private $listings = array();
    private $ignoredKeys = array(
                'minimum_beds',
                'maximum_beds',
                'minimum_price',
                'maximum_price'
            );

    /**
     * @Route("/search")
     * @Template()
     */
    public function search(ParameterBag $query)
    {
        //?postcode=E1+5LJ&listing_status=rent&maximum_price=1000&minimum_beds=3&radius=2&api_key=hu29vtwe48srv5czujdp5cee
        $client = new Client('http://api.zoopla.co.uk');

        $request = $client->get('/api/v1/property_listings.js');
        $requestQuery = $request->getQuery();
        $requestQuery->set('api_key', 'hu29vtwe48srv5czujdp5cee');

        foreach($query->all() as $key => $value){
            if(!in_array($key, $this->ignoredKeys)){
                $requestQuery->set($key, $value);
            }
        }

        for($i = $query->get('minimum_beds'); $i <= $query->get('maximum_beds'); $i++){
            $requestQuery->set('maximum_price', $query->get('appr') * $i);
            $requestQuery->set('minimum_beds', $i);
            $requestQuery->set('maximum_beds', $i);

            $response = $request->send();

            $data = $response->json();

            $this->processListings($data);

        }

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
