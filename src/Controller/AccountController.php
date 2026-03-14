<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    #[IsGranted('ROLE_USER')]
    public function index(PostRepository $postRepository): Response
    {
        $user = $this->getUser();

        $posts = $postRepository->findBy(
            ['author' => $user],
            ['id' => 'DESC']
        );

        return $this->render('account/index.html.twig', [
            'user' => $user,
            'posts' => $posts,
        ]);
    }
}
