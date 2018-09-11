<?php

declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\Service\Guide\FilterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GuideController extends Controller
{
    /**
     * @var FilterInterface
     */
    private $filter;

    public function __construct(FilterInterface $guideFilter)
    {
        $this->filter = $guideFilter;
    }

    /**
     * @Route("/guide", name="guide")
     */
    public function indexAction(Request $request)
    {
        if ($request->get('budget') <= 99 || $request->get('budget') >= 5000) {
            return new JsonResponse(['Invalid Budget'], 400);
        }

        $response = $this->filter->handle([
            'budget' => $request->get('budget'),
            'days' => $request->get('days'), ]
        );

        return new JsonResponse($response, Response::HTTP_OK);
    }
}
