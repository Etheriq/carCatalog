<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Marka;
use AppBundle\Entity\Model;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DomCrawler\Crawler;

class DefaultController extends Controller
{

    const AUTO_SITE_URL = "http://www.autonet.ru/auto/ttx";

    /**
     * @Route("/parse", name="parse")
     * @Template()
     */
    public function indexAction()
    {
        $client = new Client();
        $response = $client->get(self::AUTO_SITE_URL);
        $crawler = new Crawler($response->getBody()->getContents(), self::AUTO_SITE_URL);
        $filtered = $crawler->filter('div.brands-block.bt-null ul li a')->each(function (Crawler $node, $i) {

            return ['marka' => $node->text(), "link" => $node->link()->getUri()];
        });

        $result = [];
        foreach ($filtered as $item) {
            $response = $client->get($item['link']);
            $crawler = new Crawler($response->getBody()->getContents(), $item['link']);
            $resultModel = $crawler->filter('div.filter-models.bt-null ul li a')->each(function (Crawler $node, $i) {

                return ['model' => $node->text(), "link" => $node->link()->getUri()];
            });
            $result[] = [
                "marka" => $item['marka'],
                "marka_url" => $item['link'],
                "models" => $resultModel
            ];
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        foreach ($result as $item) {
            $marka = new Marka();
            $marka->setName($item['marka']);
            $marka->setDescription($item['marka_url']);
            $em->persist($marka);

            foreach ($item['models'] as $modelItem) {
                $model = new Model();
                $model->setName($modelItem['model']);
                $model->setDescription($modelItem['link']);

                $em->persist($model);

                $marka->addModel($model);
            }
        }

        $em->flush();

        return ["res" => $result];
    }
}
