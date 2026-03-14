<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Пользователь не найден.');
        }

        return $this->render('account/index.html.twig', [
            'user' => $user,
            'posts' => $postRepository->findBy(
                ['author' => $user],
                ['createdAt' => 'DESC']
            ),
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
