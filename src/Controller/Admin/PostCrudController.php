<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bundle\SecurityBundle\Security;
use Vich\UploaderBundle\Form\Type\VichImageType;

class PostCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly Security $security
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Post::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Публикация')
            ->setEntityLabelInPlural('Публикации')
            ->setDefaultSort([
                'createdAt' => 'DESC',
                'id' => 'DESC',
            ]);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('title', 'Заголовок');

        yield SlugField::new('slug', 'ЧПУ')
            ->setTargetFieldName('title')
            ->hideOnIndex()
            ->setFormTypeOption('disabled', $pageName !== Crud::PAGE_NEW);

        yield Field::new('imageFile', 'Изображение')
            ->setFormType(VichImageType::class)
            ->onlyOnForms();

        yield TextareaField::new('content', 'Содержимое');

        yield AssociationField::new('category', 'Категория');

        yield AssociationField::new('author', 'Автор')
            ->hideOnForm();

        yield ChoiceField::new('status', 'Статус')
            ->setChoices([
                'Черновик' => Post::STATUS_DRAFT,
                'На модерации' => Post::STATUS_PENDING,
                'Опубликовано' => Post::STATUS_PUBLISHED,
                'Отклонено' => Post::STATUS_REJECTED,
            ])
            ->renderAsBadges([
                Post::STATUS_DRAFT => 'secondary',
                Post::STATUS_PENDING => 'warning',
                Post::STATUS_PUBLISHED => 'success',
                Post::STATUS_REJECTED => 'danger',
            ]);

        yield DateTimeField::new('createdAt', 'Дата создания')
            ->hideOnForm();

        yield DateTimeField::new('updatedAt', 'Дата редактирования')
            ->setFormTypeOption('disabled', true)
            ->onlyOnForms();

        yield ImageField::new('image', 'Превью')
            ->setBasePath('/uploads/posts')
            ->onlyOnIndex();
    }

    public function createEntity(string $entityFqcn): Post
    {
        $post = new Post();

        $user = $this->security->getUser();
        if ($user) {
            $post->setAuthor($user);
        }

        return $post;
    }
}
