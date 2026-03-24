<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\User;
use App\Form\CommentFormType;
use App\Repository\PostRepository;
use App\Service\PostViewTracker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BlogController extends AbstractController
{
    #[Route('/blog', name: 'app_blog')]
    public function index(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('blog/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/blog/{id}/{slug}', name: 'app_blog_show', requirements: ['id' => '\d+'])]
    public function show(
        int $id,
        string $slug,
        Request $request,
        EntityManagerInterface $entityManager,
        PostRepository $postRepository,
        PostViewTracker $postViewTracker
    ): Response {
        $post = $postRepository->find($id);

        if (!$post || $post->getStatus() !== 'published') {
            throw $this->createNotFoundException('Статья не найдена.');
        }

        if ($post->getSlug() !== $slug) {
            return $this->redirectToRoute('app_blog_show', [
                'id' => $post->getId(),
                'slug' => $post->getSlug(),
            ]);
        }

        $user = $this->getUser();
        $postViewTracker->track($post, $user instanceof User ? $user : null);

        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);

        if ($user instanceof User && $form->isSubmitted() && $form->isValid()) {
            $comment->setPost($post);
            $comment->setAuthor($user);
            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setIsApproved(false);

            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Комментарий отправлен на модерацию.');

            return $this->redirectToRoute('app_blog_show', [
                'id' => $post->getId(),
                'slug' => $post->getSlug(),
            ]);
        }

        return $this->render('blog/show.html.twig', [
            'post' => $post,
            'commentForm' => $form->createView(),
        ]);
    }

    #[Route('/blog/trending', name: 'app_blog_trending')]
    public function trending(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findTrendingLast30Days(12);

        return $this->render('blog/trending.html.twig', [
            'posts' => $posts,
        ]);
    }
}
