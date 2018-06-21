<?php
/**
 * Created by PhpStorm.
 * User: assaf.berkovitz
 * Date: 18-Jun-18
 * Time: 10:02 PM
 */


// src/AppBundle/Controller/StoriesController.php
namespace App\Controller;

use App\Entity\Story;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class StoriesController extends Controller
{
    public function post(Request $request)
    {
        return $this->scrapeUrl($request);
    }

    public function get($id){
        $story = $this->getDoctrine()->getRepository(Story::class)->findOneById($id);
        if (!is_null($story)){
            return new JsonResponse(array
                (
                    "open_graph_tags" => json_decode($story->getOgt(),true),
                    "scrape_status" => "done",
                    "updated_time" => $story->getLastScrapedAt()->format('c'),
                    "id" => $story->getId()
                )
            );
        }
        else{
            return new Response("id doesn't exists");
        }
    }

    /**
     * @param Request $request
     * @return Response
     */
    protected function scrapeUrl(Request $request){
        $url = $request->query->get('url');
        if (!$url)
            return new Response('missing URL parameter');
        $url = urldecode($url);
        $pageHTML = $this->getPageHtml($url);
        if (is_null($pageHTML))
        {
            return new Response('Site is not reachable.');
        }
        $canUrl = $this->getCanonicalUrl($pageHTML);
        // Canonical URL should be set in og:URL. if it missing we can't guaranty uniqueness.
        if (is_null($canUrl))
        {
            return new Response('Site is missing canonical URL');
        }
        $ogMetaTags = $this->getOgMeta($pageHTML);

        // check if URL already exists
        $story = $this->getDoctrine()->getRepository(Story::class)->findOneByUrl($canUrl);
        if (!is_null($story)){
            return new Response($story->getId());
        }

        //URL doesn't exists. Need to create new story in DB
        $story = new Story();
        $story->setURL($canUrl);
        $story->setOgt(json_encode($ogMetaTags));
        $dateTime = new \DateTime();
        $story->setLastScrapedAt($dateTime);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($story);
        $entityManager->flush();
        return new Response($story->getId());
    }

    /**
     * @param string $html the  site page HTML
     * @return null
     */
    protected function getCanonicalUrl($html){
        $doc = new \DOMDocument();
        @$doc->loadHTML($html);
        $links = $doc->getElementsByTagName('link');
        foreach ($links as $link) {
            $rel = $link->getAttribute('rel');
            if ($rel == 'canonical'){
                return $link->getAttribute('href');
            }
        }
        return null;
    }

    /**
     * @param string $url requested site
     * @return mixed
     */
    protected function getPageHTML($url){
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $html = curl_exec($ch);
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200){
            return null;
        }
        return $html;
    }

    /**
     * @param string $html the site HTML
     * @return array
     */
    private function getOgMeta($html){
        $doc = new \DOMDocument();
        @$doc->loadHTML($html);
        $metas = $doc->getElementsByTagName('meta');
        $rmetas = array();
        foreach ($metas as $meta) {
            $property = $meta->getAttribute('property');
            if ($property && strpos($property, 'og:') === 0 ) {
                $content = $meta->getAttribute('content');
                $rmetas[$property] = $content;
            }
        }
        return $rmetas;
    }
}