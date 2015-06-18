<?php

namespace AppBundle\Controller;

use GuzzleHttp\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DomCrawler\Crawler;

class DefaultController extends Controller
{
    /**
     * @Route("/parse", name="parse")
     * @Template()
     */
    public function indexAction()
    {
        // create http client instance
        $client = new Client();
        $response = $client->get('http://www.autonet.ru/auto/ttx');

        $crawler = new Crawler($response->getBody()->getContents(), 'http://www.autonet.ru/auto/ttx');

        $filter = $crawler->filter('div.brands-block.bt-null ul li a')->each(function (Crawler $node, $i) {

            return ["d" => $node->link()->getUri(), 't' => $node->text()];
        });

    $a = 1;

        return [];
    }
}
