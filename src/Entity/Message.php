<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\Index('idx_message_dialog_composite', ['dialog_id', 'participant1_id'])]
#[ORM\Table(options: ['sharding_key' => 'participant1_id'])]
class Message
{
    #[SerializedName('id')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[SerializedName('dialog_id')]
    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private Dialog $dialog;

    #[SerializedName('sender_id')]
    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private User $sender;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private string $text;

    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
    #[SerializedName('created_at')]
    #[ORM\Column(name: 'created_at')]
    private DateTimeImmutable $createdAt;

    #[SerializedName('participant1_id')]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $participant1;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDialog(): Dialog
    {
        return $this->dialog;
    }

    public function setDialog(Dialog $dialog): static
    {
        $this->dialog = $dialog;

        return $this;
    }

    public function getParticipant1(): User
    {
        return $this->participant1;
    }

    public function setParticipant1(User $participant1): static
    {
        $this->participant1 = $participant1;

        return $this;
    }

    public function getSender(): User
    {
        return $this->sender;
    }

    public function setSender(User $sender): static
    {
        $this->sender = $sender;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
