<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        $url = $this->container->get(AdminUrlGenerator::class)
            ->setController(PostCrudController::class)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Панель управления')
            ->setTranslationDomain('EasyAdminBundle')
            ->setLocales(['ru']);
    }

    public function configureMenuItems(): iterable
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        return [
            MenuItem::linkToDashboard('Dashboard', 'fa fa-home'),

            MenuItem::section('Содержимое'),

            MenuItem::linkToUrl(
                'Публикации',
                'fa fa-file-text',
                $adminUrlGenerator->setController(PostCrudController::class)->generateUrl()
            ),

            MenuItem::linkToUrl(
                'Категории',
                'fa fa-tags',
                $adminUrlGenerator->setController(CategoryCrudController::class)->generateUrl()
            ),

            MenuItem::linkToUrl(
                'Комментарии',
                'fa fa-comments',
                $adminUrlGenerator->setController(CommentCrudController::class)->generateUrl()
            ),

            MenuItem::linkToUrl(
                'Пользователи',
                'fa fa-users',
                $adminUrlGenerator->setController(UserCrudController::class)->generateUrl()
            ),
        ];
    }
}
