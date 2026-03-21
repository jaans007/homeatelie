<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\PostView;
use App\Entity\User;
use App\Repository\PostViewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PostViewTracker
{
    private const UNIQUE_HOURS = 24;
    private const SESSION_KEY = 'viewed_posts';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PostViewRepository $postViewRepository,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function track(Post $post, ?User $viewer = null): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return;
        }

        if ($this->isBot($request)) {
            return;
        }

        if ($this->isAuthorViewingOwnPost($post, $viewer)) {
            return;
        }

        $session = $request->hasSession() ? $request->getSession() : null;
        $since = new \DateTimeImmutable('-' . self::UNIQUE_HOURS . ' hours');

        if ($viewer instanceof User) {
            $alreadyViewed = $this->postViewRepository->hasRecentView(
                $post,
                $viewer,
                null,
                $since
            );

            if ($alreadyViewed) {
                return;
            }
        } else {
            if ($this->hasRecentGuestViewInSession($post, $session)) {
                return;
            }
        }

        $ip = $request->getClientIp();
        $userAgent = $request->headers->get('User-Agent', '');
        $sessionId = $session?->getId();

        $ipHash = $ip ? hash('sha256', $ip) : null;
        $userAgentHash = $userAgent !== '' ? hash('sha256', $userAgent) : null;

        $postView = new PostView();
        $postView->setPost($post);
        $postView->setUser($viewer);
        $postView->setSessionId($sessionId);
        $postView->setIpHash($ipHash);
        $postView->setUserAgentHash($userAgentHash);
        $postView->setViewedAt(new \DateTimeImmutable());

        $post->incrementViewsCount();
        $post->incrementUniqueViewsCount();

        $this->entityManager->persist($postView);
        $this->entityManager->flush();

        if (!$viewer instanceof User) {
            $this->markGuestViewInSession($post, $session);
        }
    }

    private function hasRecentGuestViewInSession(Post $post, ?SessionInterface $session): bool
    {
        if (!$session instanceof SessionInterface) {
            return false;
        }

        $viewedPosts = $session->get(self::SESSION_KEY, []);
        $postKey = 'post_' . $post->getId();

        if (!isset($viewedPosts[$postKey])) {
            return false;
        }

        $lastViewedAt = (int) $viewedPosts[$postKey];
        $lifetime = self::UNIQUE_HOURS * 3600;

        return (time() - $lastViewedAt) < $lifetime;
    }

    private function markGuestViewInSession(Post $post, ?SessionInterface $session): void
    {
        if (!$session instanceof SessionInterface) {
            return;
        }

        $viewedPosts = $session->get(self::SESSION_KEY, []);
        $postKey = 'post_' . $post->getId();
        $viewedPosts[$postKey] = time();

        $session->set(self::SESSION_KEY, $viewedPosts);
    }

    private function isAuthorViewingOwnPost(Post $post, ?User $viewer): bool
    {
        if (!$viewer instanceof User) {
            return false;
        }

        $author = $post->getAuthor();

        if (!$author) {
            return false;
        }

        return $author->getId() === $viewer->getId();
    }

    private function isBot(Request $request): bool
    {
        $userAgent = mb_strtolower($request->headers->get('User-Agent', ''));

        if ($userAgent === '') {
            return true;
        }

        $botKeywords = [
            'bot',
            'crawl',
            'spider',
            'slurp',
            'bingpreview',
            'facebookexternalhit',
            'monitoring',
            'uptime',
            'crawler',
            'python-requests',
            'curl',
            'wget',
            'headless',
            'scanner',
        ];

        foreach ($botKeywords as $keyword) {
            if (str_contains($userAgent, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
