<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(
        PostRepository $postRepository,
        CategoryRepository $categoryRepository
    ): Response {
        $featuredPosts = $postRepository->findHomepageFeatured(4);
        $popularPosts = $postRepository->findMostPopular(5);
        $latestPosts = $postRepository->findLatestPublished(4);
        $editorPickedPosts = $postRepository->findEditorPickedFallback(5);

        $categorySliderCategory = $categoryRepository->findCategoryWithMostPublishedPosts();
        $categorySliderPosts = $categorySliderCategory
            ? $postRepository->findPopularPublishedByCategory($categorySliderCategory, 5)
            : [];

        return $this->render('home/index.html.twig', [
            'featuredPosts' => $featuredPosts,
            'popularPosts' => $popularPosts,
            'latestPosts' => $latestPosts,
            'editorPickedPosts' => $editorPickedPosts,
            'categorySliderCategory' => $categorySliderCategory,
            'categorySliderPosts' => $categorySliderPosts,
        ]);
    }
}
