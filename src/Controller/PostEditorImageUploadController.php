<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

final class PostEditorImageUploadController extends AbstractController
{
    #[Route('/editor/upload-image', name: 'app_editor_upload_image', methods: ['POST'])]
    #[IsGranted('ROLE_EDITOR')]
    public function __invoke(
        Request $request,
        SluggerInterface $slugger,
    ): JsonResponse {
        $file = $request->files->get('image');

        if (!$file) {
            return $this->json([
                'success' => false,
                'message' => 'Файл не передан.',
            ], 400);
        }

        if (!str_starts_with((string) $file->getMimeType(), 'image/')) {
            return $this->json([
                'success' => false,
                'message' => 'Можно загружать только изображения.',
            ], 400);
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            return $this->json([
                'success' => false,
                'message' => 'Максимальный размер файла — 5 МБ.',
            ], 400);
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        try {
            $file->move(
                $this->getParameter('kernel.project_dir') . '/public/uploads/posts/editor',
                $newFilename
            );
        } catch (FileException $e) {
            return $this->json([
                'success' => false,
                'message' => 'Не удалось загрузить изображение.',
            ], 500);
        }

        return $this->json([
            'success' => true,
            'url' => '/uploads/posts/editor/' . $newFilename,
        ]);
    }
}
