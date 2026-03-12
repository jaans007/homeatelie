<?php
namespace App\Controller\Admin;


use App\Controller\Admin\PostCrudController;
use App\Controller\Admin\CategoryCrudController;
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
        return [
            MenuItem::linkToDashboard('Dashboard', 'fa fa-home'),

            MenuItem::section('Содержимое'),
            MenuItem::linkTo(PostCrudController::class, 'Публикации', 'fa fa-file-text'),
            MenuItem::linkTo(CategoryCrudController::class, 'Категории', 'fa fa-tags'),
            MenuItem::linkTo(UserCrudController::class, 'Пользователи', 'fa fa-users'),
        ];
    }
}
