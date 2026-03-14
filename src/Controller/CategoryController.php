<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategoryController extends AbstractController
{
    #[Route('/category/{slug}', name: 'app_category_show')]
    public function show(Category $category, PostRepository $postRepository): Response
    {
        return $this->render('category/show.html.twig', [
            'category' => $category,
            'posts' => $postRepository->findPublishedByCategory($category),
        ]);
    }
}
