<?php

namespace App\Entity;

use App\Repository\DialogRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity(repositoryClass: DialogRepository::class)]
#[ORM\UniqueConstraint(name: 'dialog_participants_unique_idx', columns: ['participant1_id', 'participant2_id'])]
#[ORM\Table(options: ['sharding_key' => 'participant1_id'])]
class Dialog
{
    #[SerializedName('id')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[SerializedName('participant1_id')]
    #[ORM\ManyToOne(inversedBy: 'dialogsAsParticipant1')]
    #[ORM\JoinColumn(nullable: false, columnDefinition: "INT AUTO_INCREMENT")]
    private User $participant1;

    #[SerializedName('participant2_id')]
    #[ORM\ManyToOne(inversedBy: 'dialogsAsParticipant2')]
    #[ORM\JoinColumn(nullable: false)]
    private User $participant2;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'dialog', orphanRemoval: true)]
    private Collection $messages;

    public function __construct(User $user1, User $user2)
    {
        // Сортируем участников для избежания дубликатов
        [$this->participant1, $this->participant2] =
            $user1->getId() < $user2->getId()
                ? [$user1, $user2]
                : [$user2, $user1];

        $this->messages = new ArrayCollection();
    }

    public function hasParticipant(User $user): bool
    {
        return $user === $this->participant1 || $user === $this->participant2;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getParticipant2(): User
    {
        return $this->participant2;
    }

    public function setParticipant2(User $participant2): static
    {
        $this->participant2 = $participant2;

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setDialog($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        $this->messages->removeElement($message);

        return $this;
    }
}
