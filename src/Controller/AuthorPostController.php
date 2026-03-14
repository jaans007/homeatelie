<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AuthorPostController extends AbstractController
{
    #[Route('/posts/author/{id}/{slug}', name: 'app_author_posts')]
    public function index(User $user, string $slug, PostRepository $postRepository): Response
    {
        if ($user->getSlug() !== $slug) {
            return $this->redirectToRoute('app_author_posts', [
                'id' => $user->getId(),
                'slug' => $user->getSlug(),
            ]);
        }

        $posts = $postRepository->findPublishedByAuthor($user);

        return $this->render('author_post/index.html.twig', [
            'author' => $user,
            'posts' => $posts,
        ]);
    }
}
