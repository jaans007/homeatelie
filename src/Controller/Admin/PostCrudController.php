<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use Vich\UploaderBundle\Form\Type\VichImageType;

class PostCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Post::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Публикация')
            ->setEntityLabelInPlural('Публикации');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('title', 'Заголовок');


        yield SlugField::new('slug', 'ЧПУ')
            ->setTargetFieldName('title')
            ->hideOnIndex()
            ->setFormTypeOption('disabled', $pageName !== Crud::PAGE_NEW);

         // Загрузка изображения через Vich (форма)
        yield Field::new('imageFile', 'Изображение')
            ->setFormType(VichImageType::class)
            ->onlyOnForms();

        yield TextareaField::new('content', 'Содержимое');

        yield AssociationField::new('category', 'Категория');

        yield DateTimeField::new('createdAt', 'Дата публикации')
            ->onlyOnForms();

        yield DateTimeField::new('updatedAt', 'Дата редактирования')
            ->setFormTypeOption('disabled', true)
            ->onlyOnForms();

        // Превью изображения в списке
        yield ImageField::new('image', 'Превью')
            ->setBasePath('/uploads/posts')
            ->onlyOnIndex();
    }
}


