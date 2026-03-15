<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AccountAvatarController extends AbstractController
{
    #[Route('/account/avatar/upload', name: 'app_account_avatar_upload', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function upload(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Пользователь не авторизован.',
            ], 403);
        }

        if (!$this->isCsrfTokenValid('upload_avatar', (string) $request->request->get('_token'))) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Неверный CSRF-токен.',
            ], 400);
        }

        $avatarData = (string) $request->request->get('avatar');

        if ($avatarData === '') {
            return new JsonResponse([
                'success' => false,
                'message' => 'Изображение не передано.',
            ], 400);
        }

        if (!preg_match('/^data:image\/(\w+);base64,/', $avatarData, $matches)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Некорректный формат изображения.',
            ], 400);
        }

        $avatarData = substr($avatarData, strpos($avatarData, ',') + 1);
        $binary = base64_decode($avatarData, true);

        if ($binary === false) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Не удалось обработать изображение.',
            ], 400);
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/avatars';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $fileName = 'avatar_' . $user->getId() . '_' . time() . '.webp';
        $filePath = $uploadDir . '/' . $fileName;

        if (file_put_contents($filePath, $binary) === false) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Не удалось сохранить файл.',
            ], 500);
        }

        if (method_exists($user, 'getAvatar') && $user->getAvatar()) {
            $oldPath = $uploadDir . '/' . $user->getAvatar();

            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        if (!method_exists($user, 'setAvatar')) {
            return new JsonResponse([
                'success' => false,
                'message' => 'В User отсутствует метод setAvatar().',
            ], 500);
        }

        $user->setAvatar($fileName);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Аватар успешно обновлён.',
            'avatarUrl' => '/uploads/avatars/' . $fileName,
        ]);
    }
}
