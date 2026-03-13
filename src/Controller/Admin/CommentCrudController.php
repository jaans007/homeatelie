<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class CommentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Comment::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Комментарий')
            ->setEntityLabelInPlural('Комментарии')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->hideOnForm();

        yield TextareaField::new('content', 'Текст')
            ->hideOnIndex();

        yield TextareaField::new('content', 'Текст')
            ->onlyOnIndex()
            ->formatValue(function ($value) {
                $text = strip_tags((string) $value);

                return mb_strlen($text) > 80
                    ? mb_substr($text, 0, 80) . '...'
                    : $text;
            });

        yield AssociationField::new('author', 'Автор')
            ->autocomplete();

        yield AssociationField::new('post', 'Пост')
            ->autocomplete();

        yield BooleanField::new('isApproved', 'Одобрен');

        yield DateTimeField::new('createdAt', 'Дата')
            ->hideOnForm();
    }
}
