<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentFormType;
use App\Repository\PostRepository;
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

    #[Route('/blog/{slug}', name: 'app_blog_show')]
    public function show(
        Post $post,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);

        if ($this->getUser() && $form->isSubmitted() && $form->isValid()) {
            $comment->setPost($post);
            $comment->setAuthor($this->getUser());
            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setIsApproved(false);

            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Комментарий отправлен на модерацию.');

            return $this->redirectToRoute('app_blog_show', [
                'slug' => $post->getSlug(),
            ]);
        }

        return $this->render('blog/show.html.twig', [
            'post' => $post,
            'commentForm' => $form->createView(),
        ]);
    }
}
