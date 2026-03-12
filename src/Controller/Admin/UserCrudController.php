<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Пользователь')
            ->setEntityLabelInPlural('Пользователи')
            ->setSearchFields(['id', 'email'])
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->hideOnForm();

        yield EmailField::new('email', 'Email');

        if (Crud::PAGE_INDEX === $pageName || Crud::PAGE_DETAIL === $pageName) {
            yield ArrayField::new('roles', 'Роли');
        } else {
            yield ChoiceField::new('roles', 'Роли')
                ->setChoices([
                    'Пользователь' => 'ROLE_USER',
                    'Редактор' => 'ROLE_EDITOR',
                    'Администратор' => 'ROLE_ADMIN',
                ])
                ->allowMultipleChoices();
        }

        // Если в User есть дополнительные поля, можно добавить их здесь.
        // Например:
        // yield BooleanField::new('isVerified', 'Подтвержден');
        // yield DateTimeField::new('createdAt', 'Создан')->hideOnForm();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // если не хотите удалять пользователей из админки, можно убрать delete:
            ->disable(Action::DELETE);
    }
}

