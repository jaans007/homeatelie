<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    #[IsGranted('ROLE_USER')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        PostRepository $postRepository
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $tab = $request->query->get('tab', 'profile');

        if ($request->isMethod('POST')) {
            $formType = $request->request->get('form_type');

            if ($formType === 'profile') {
                if (!$this->isCsrfTokenValid('account_profile', (string) $request->request->get('_token'))) {
                    $this->addFlash('danger', 'Неверный CSRF-токен.');

                    return $this->redirectToRoute('app_account', [
                        'tab' => 'profile',
                    ]);
                }

                $name = trim((string) $request->request->get('name'));
                $bio = trim((string) $request->request->get('bio'));

                if ($name === '') {
                    $this->addFlash('danger', 'Имя не может быть пустым.');

                    return $this->redirectToRoute('app_account', [
                        'tab' => 'profile',
                    ]);
                }

                $user->setName($name);
                $user->setBio($bio !== '' ? $bio : null);

                $entityManager->flush();

                $this->addFlash('success', 'Профиль успешно обновлён.');

                return $this->redirectToRoute('app_account', [
                    'tab' => 'profile',
                ]);
            }

            if ($formType === 'password') {
                if (!$this->isCsrfTokenValid('account_password', (string) $request->request->get('_token'))) {
                    $this->addFlash('danger', 'Неверный CSRF-токен.');

                    return $this->redirectToRoute('app_account', [
                        'tab' => 'security',
                    ]);
                }

                $currentPassword = (string) $request->request->get('current_password');
                $newPassword = (string) $request->request->get('new_password');
                $newPasswordRepeat = (string) $request->request->get('new_password_repeat');

                if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                    $this->addFlash('danger', 'Текущий пароль указан неверно.');

                    return $this->redirectToRoute('app_account', [
                        'tab' => 'security',
                    ]);
                }

                if (mb_strlen($newPassword) < 6) {
                    $this->addFlash('danger', 'Новый пароль должен быть не короче 6 символов.');

                    return $this->redirectToRoute('app_account', [
                        'tab' => 'security',
                    ]);
                }

                if ($newPassword !== $newPasswordRepeat) {
                    $this->addFlash('danger', 'Новые пароли не совпадают.');

                    return $this->redirectToRoute('app_account', [
                        'tab' => 'security',
                    ]);
                }

                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));

                $entityManager->flush();

                $this->addFlash('success', 'Пароль успешно изменён.');

                return $this->redirectToRoute('app_account', [
                    'tab' => 'security',
                ]);
            }
        }

        $posts = [];

        if ($tab === 'posts') {
            $posts = $postRepository->findBy(
                ['author' => $user],
                ['createdAt' => 'DESC']
            );
        }

        return $this->render('account/index.html.twig', [
            'user' => $user,
            'activeTab' => $tab,
            'posts' => $posts,
        ]);
    }
}
