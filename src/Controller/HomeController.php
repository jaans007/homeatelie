<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(PostRepository $posts): Response
    {
        return $this->render('home/index.html.twig', [
            'posts' => $posts->findBy([], ['createdAt' => 'DESC'], 12),
        ]);
    }
}
