<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
class Post
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_REJECTED = 'rejected';

    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_DRAFT;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Введите заголовок статьи.')]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    private ?Category $category = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $author = null;

    #[ORM\OneToMany(mappedBy: 'post', targetEntity: Comment::class, orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $comments;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $imageAttribution = null;

    #[Vich\UploadableField(mapping: 'post_images', fileNameProperty: 'image')]
    private ?File $imageFile = null;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if ($imageFile !== null) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    public function getImageAttribution(): ?string
    {
        return $this->imageAttribution;
    }

    public function setImageAttribution(?string $imageAttribution): static
    {
        $this->imageAttribution = $imageAttribution;

        return $this;
    }

    public function getImageAttributionLabel(): ?string
    {
        return match ($this->imageAttribution) {
            'author' => 'Изображение создано автором: ' . $this->getAuthorName(),
            'chatgpt' => 'Обложка: ChatGPT',
            'midjourney' => 'Обложка: Midjourney',
            'dalle' => 'Обложка: DALL·E',
            'flux' => 'Обложка: Flux',
            'stable_diffusion' => 'Обложка: Stable Diffusion',
            'unsplash' => 'Источник изображения: Unsplash',
            'pexels' => 'Источник изображения: Pexels',
            'pixabay' => 'Источник изображения: Pixabay',
            'other' => 'Автор не указал источник изображения',
            default => null,
        };
    }

    private function getAuthorName(): string
    {
        if (!$this->author) {
            return 'Автор';
        }

        if (method_exists($this->author, 'getName') && $this->author->getName()) {
            return $this->author->getName();
        }

        if (method_exists($this->author, 'getUserIdentifier')) {
            return $this->author->getUserIdentifier();
        }

        if (method_exists($this->author, 'getEmail')) {
            return $this->author->getEmail();
        }

        return 'Автор';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function __toString(): string
    {
        return $this->title ?: 'Пост';
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $allowedStatuses = [
            self::STATUS_DRAFT,
            self::STATUS_PENDING,
            self::STATUS_PUBLISHED,
            self::STATUS_REJECTED,
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            throw new \InvalidArgumentException('Недопустимый статус поста.');
        }

        $this->status = $status;

        return $this;
    }

    private function slugify(string $text): string
    {
        $translit = [
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ё' => 'e',
            'ж' => 'zh',
            'з' => 'z',
            'и' => 'i',
            'й' => 'y',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'h',
            'ц' => 'c',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'sch',
            'ъ' => '',
            'ы' => 'y',
            'ь' => '',
            'э' => 'e',
            'ю' => 'yu',
            'я' => 'ya',
        ];

        $text = mb_strtolower($text);
        $text = strtr($text, $translit);
        $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
        $text = preg_replace('/-+/', '-', $text);

        return trim($text, '-');
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        if (!$this->createdAt) {
            $this->createdAt = new \DateTimeImmutable();
        }

        $this->updatedAt = new \DateTimeImmutable();

        if ((!$this->slug || $this->slug === '') && $this->title) {
            $this->slug = $this->slugify($this->title);
        }
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();

        if ((!$this->slug || $this->slug === '') && $this->title) {
            $this->slug = $this->slugify($this->title);
        }
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        $this->comments->removeElement($comment);

        return $this;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Черновик',
            self::STATUS_PENDING => 'На модерации',
            self::STATUS_PUBLISHED => 'Опубликовано',
            self::STATUS_REJECTED => 'Отклонено',
            default => 'Неизвестно',
        };
    }
}
