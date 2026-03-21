<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostFormType;
use App\Repository\PostRepository;
use App\Service\PostCoverImageProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/account/posts')]
#[IsGranted('ROLE_EDITOR')]
final class AccountPostController extends AbstractController
{
    #[Route('/create', name: 'app_account_post_create')]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        PostRepository $postRepository,
        PostCoverImageProcessor $postCoverImageProcessor
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $post = new Post();
        $post->setAuthor($user);
        $post->setStatus(Post::STATUS_PENDING);

        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$post->getCategory()) {
                $form->get('category')->addError(new FormError('Выберите категорию.'));
            }

            $content = trim(strip_tags((string) $post->getContent()));

            if ($content === '') {
                $form->get('content')->addError(new FormError('Введите текст статьи.'));
            }

            if ($form->isValid()) {
                $baseSlug = strtolower($slugger->slug((string) $post->getTitle())->toString());

                if ($baseSlug === '') {
                    $baseSlug = 'post';
                }

                $slug = $baseSlug;
                $counter = 1;

                while ($postRepository->findOneBy(['slug' => $slug]) !== null) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }

                $post->setSlug($slug);

                $entityManager->persist($post);
                $entityManager->flush();

                if ($post->getImage()) {
                    $absolutePath = $this->getParameter('kernel.project_dir') . '/public/uploads/posts/' . $post->getImage();

                    if (file_exists($absolutePath)) {
                        $postCoverImageProcessor->process($absolutePath);
                    }
                }

                $this->addFlash('success', 'Статья успешно отправлена на модерацию.');

                return $this->redirectToRoute('app_account', [
                    'tab' => 'posts',
                ]);
            }
        }

        return $this->render('account/post/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_account_post_edit', requirements: ['id' => '\d+'])]
    public function edit(
        Post $post,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if ($post->getAuthor() !== $user) {
            throw $this->createAccessDeniedException('Вы не можете редактировать эту публикацию.');
        }

        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$post->getCategory()) {
                $form->get('category')->addError(new FormError('Выберите категорию.'));
            }

            $content = trim(strip_tags((string) $post->getContent()));

            if ($content === '') {
                $form->get('content')->addError(new FormError('Введите текст статьи.'));
            }

            if ($form->isValid()) {
                $entityManager->flush();

                $this->addFlash('success', 'Публикация успешно обновлена.');

                return $this->redirectToRoute('app_account', [
                    'tab' => 'posts',
                ]);
            }
        }

        return $this->render('account/post/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }
}
