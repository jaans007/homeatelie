<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        PostRepository $postRepository,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Пользователь не найден.');
        }

        $formType = (string) $request->request->get('form_type');

        if ($request->isMethod('POST') && $formType === 'profile') {
            if (!$this->isCsrfTokenValid('account_profile', (string) $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Неверный CSRF токен.');
            }

            $name = trim((string) $request->request->get('name'));
            $bio = trim((string) $request->request->get('bio'));

            if ($name === '') {
                $this->addFlash('danger', 'Имя не может быть пустым.');
            } else {
                $user->setName($name);
                $user->setBio($bio !== '' ? $bio : null);

                $entityManager->flush();

                $this->addFlash('success', 'Профиль успешно обновлён.');

                return $this->redirectToRoute('app_account');
            }
        }

        if ($request->isMethod('POST') && $formType === 'password') {
            if (!$this->isCsrfTokenValid('account_password', (string) $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Неверный CSRF токен.');
            }

            $currentPassword = (string) $request->request->get('current_password');
            $newPassword = (string) $request->request->get('new_password');
            $newPasswordRepeat = (string) $request->request->get('new_password_repeat');

            if ($currentPassword === '' || $newPassword === '' || $newPasswordRepeat === '') {
                $this->addFlash('danger', 'Заполните все поля для смены пароля.');

                return $this->redirectToRoute('app_account', ['tab' => 'security']);
            }

            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('danger', 'Текущий пароль указан неверно.');

                return $this->redirectToRoute('app_account', ['tab' => 'security']);
            }

            if ($newPassword !== $newPasswordRepeat) {
                $this->addFlash('danger', 'Новый пароль и повтор пароля не совпадают.');

                return $this->redirectToRoute('app_account', ['tab' => 'security']);
            }

            if (mb_strlen($newPassword) < 6) {
                $this->addFlash('danger', 'Новый пароль должен быть не короче 6 символов.');

                return $this->redirectToRoute('app_account', ['tab' => 'security']);
            }

            if ($passwordHasher->isPasswordValid($user, $newPassword)) {
                $this->addFlash('danger', 'Новый пароль должен отличаться от текущего.');

                return $this->redirectToRoute('app_account', ['tab' => 'security']);
            }

            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            $entityManager->flush();

            $this->addFlash('success', 'Пароль успешно изменён.');

            return $this->redirectToRoute('app_account', ['tab' => 'security']);
        }

        return $this->render('account/index.html.twig', [
            'user' => $user,
            'posts' => $postRepository->findBy(
                ['author' => $user],
                ['createdAt' => 'DESC']
            ),
            'activeTab' => $request->query->get('tab', 'profile'),
        ]);
    }

    #[Route('/account/avatar/upload', name: 'app_account_avatar_upload', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function uploadAvatar(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json([
                'success' => false,
                'message' => 'Пользователь не найден',
            ], 401);
        }

        if (!$this->isCsrfTokenValid('upload_avatar', (string) $request->request->get('_token'))) {
            return $this->json([
                'success' => false,
                'message' => 'Неверный CSRF токен',
            ], 403);
        }

        $imageData = $request->request->get('avatar');

        if (!$imageData) {
            return $this->json([
                'success' => false,
                'message' => 'Изображение не получено',
            ], 400);
        }

        if (!preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,/', $imageData)) {
            return $this->json([
                'success' => false,
                'message' => 'Неподдерживаемый формат изображения',
            ], 400);
        }

        $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
        $binaryData = base64_decode($base64, true);

        if ($binaryData === false) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка декодирования изображения',
            ], 400);
        }

        if (strlen($binaryData) > 5 * 1024 * 1024) {
            return $this->json([
                'success' => false,
                'message' => 'Изображение слишком большое',
            ], 400);
        }

        $sourceImage = imagecreatefromstring($binaryData);

        if ($sourceImage === false) {
            return $this->json([
                'success' => false,
                'message' => 'Не удалось обработать изображение',
            ], 400);
        }

        $finalSize = 300;
        $finalImage = imagecreatetruecolor($finalSize, $finalSize);

        imagealphablending($finalImage, true);
        imagesavealpha($finalImage, true);

        $width = imagesx($sourceImage);
        $height = imagesy($sourceImage);

        imagecopyresampled(
            $finalImage,
            $sourceImage,
            0,
            0,
            0,
            0,
            $finalSize,
            $finalSize,
            $width,
            $height
        );

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/avatars';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = uniqid('avatar_', true) . '.webp';
        $filePath = $uploadDir . '/' . $filename;

        imagewebp($finalImage, $filePath, 90);

        $oldAvatar = $user->getAvatar();

        if ($oldAvatar) {
            $oldAvatarPath = $uploadDir . '/' . $oldAvatar;

            if (is_file($oldAvatarPath)) {
                unlink($oldAvatarPath);
            }
        }

        $user->setAvatar($filename);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Аватар успешно сохранён',
            'avatarUrl' => '/uploads/avatars/' . $filename,
        ]);
    }
}
