<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Service\Friend\Status;
use App\Service\User\Gender;
use App\Service\User\Role;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Index('handle_user_name', ['first_name', 'last_name'])]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: Role::class)]
    private ?Role $role = null;

    #[ORM\Column]
    private ?string $password = null;

    #[SerializedName('first_name')]
    #[ORM\Column(name: 'first_name', length: 255)]
    #[Assert\NotBlank]
    private ?string $firstName = null;

    #[SerializedName('last_name')]
    #[ORM\Column(name: 'last_name', length: 255)]
    #[Assert\NotBlank]
    private ?string $lastName = null;

    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    #[SerializedName('birth_date')]
    #[ORM\Column(name: 'birth_date', type: Types::DATE_IMMUTABLE)]
    #[Assert\NotBlank]
    #[Assert\Type(DateTimeImmutable::class)]
    private ?DateTimeImmutable $birthDate = null;

    #[ORM\Column(type: Types::STRING, length: 1, enumType: Gender::class)]
    private ?Gender $gender = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $biography = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city = null;

    /**
     * @var Collection<int, Friend>
     */
    #[ORM\OneToMany(targetEntity: Friend::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $friends;

    /**
     * @var Collection<int, Friend>
     */
    #[ORM\OneToMany(targetEntity: Friend::class, mappedBy: 'friend', orphanRemoval: true)]
    private Collection $friendedBy;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $posts;

    /**
     * @var Collection<int, Dialog>
     */
    #[ORM\OneToMany(targetEntity: Dialog::class, mappedBy: 'participant1', orphanRemoval: true)]
    private Collection $dialogsAsParticipant1;

    /**
     * @var Collection<int, Dialog>
     */
    #[ORM\OneToMany(targetEntity: Dialog::class, mappedBy: 'participant2', orphanRemoval: true)]
    private Collection $dialogsAsParticipant2;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'sender', orphanRemoval: true)]
    private Collection $messages;

    function __construct()
    {
        $this->id = Uuid::v7();
        $this->friends = new ArrayCollection();
        $this->friendedBy = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->dialogsAsParticipant1 = new ArrayCollection();
        $this->dialogsAsParticipant2 = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->id->toString();
    }

    /**
     * @see UserInterface
     *
     * @return string[]
     */
    public function getRoles(): array
    {
        $roles = $this->role->value;

        return [$roles];
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function setRole(Role $role): static
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getBirthDate(): ?DateTimeImmutable
    {
        return $this->birthDate;
    }

    public function setBirthDate(DateTimeImmutable $birthDate): static
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getGender(): ?Gender
    {
        return $this->gender;
    }

    public function setGender(Gender $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setBiography(?string $biography): static
    {
        $this->biography = $biography;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getFriends(): Collection
    {
        return $this->friends;
    }

    public function addFriend(Friend $friend): static
    {
        if (!$this->friends->contains($friend)) {
            $this->friends->add($friend);
            $friend->setUser($this);
        }

        return $this;
    }

    public function removeFriend(Friend $friend): static
    {
        $this->friends->removeElement($friend);

        return $this;
    }

    public function getFriendedBy(): Collection
    {
        return $this->friendedBy;
    }

    public function addFriendedBy(Friend $friendedBy): static
    {
        if (!$this->friendedBy->contains($friendedBy)) {
            $this->friendedBy->add($friendedBy);
            $friendedBy->setFriend($this);
        }

        return $this;
    }

    public function removeFriendedBy(Friend $friendedBy): static
    {
        $this->friendedBy->removeElement($friendedBy);

        return $this;
    }

    public function getAcceptedFriends(): array
    {
        $friends = [];

        foreach ($this->friends as $friendship) {
            if ($friendship->getStatus() === Status::Accepted) {
                $friends[] = $friendship->getFriend();
            }
        }

        foreach ($this->friendedBy as $friendship) {
            if ($friendship->getStatus() === Status::Accepted) {
                $friends[] = $friendship->getUser();
            }
        }

        return array_unique($friends);
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setUser($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        $this->posts->removeElement($post);

        return $this;
    }

    /**
     * @return Collection<int, Dialog>
     */
    public function getDialogsAsParticipant1(): Collection
    {
        return $this->dialogsAsParticipant1;
    }

    public function addDialogsAsParticipant1(Dialog $dialogsAsParticipant1): static
    {
        if (!$this->dialogsAsParticipant1->contains($dialogsAsParticipant1)) {
            $this->dialogsAsParticipant1->add($dialogsAsParticipant1);
            $dialogsAsParticipant1->setParticipant1($this);
        }

        return $this;
    }

    public function removeDialogsAsParticipant1(Dialog $dialogsAsParticipant1): static
    {
        $this->dialogsAsParticipant1->removeElement($dialogsAsParticipant1);

        return $this;
    }

    /**
     * @return Collection<int, Dialog>
     */
    public function getDialogsAsParticipant2(): Collection
    {
        return $this->dialogsAsParticipant2;
    }

    public function addDialogsAsParticipant2(Dialog $dialogsAsParticipant2): static
    {
        if (!$this->dialogsAsParticipant2->contains($dialogsAsParticipant2)) {
            $this->dialogsAsParticipant2->add($dialogsAsParticipant2);
            $dialogsAsParticipant2->setParticipant2($this);
        }

        return $this;
    }

    public function removeDialogsAsParticipant2(Dialog $dialogsAsParticipant2): static
    {
        $this->dialogsAsParticipant2->removeElement($dialogsAsParticipant2);

        return $this;
    }

    public function getDialogs(): Collection
    {
        return new ArrayCollection(
            array_merge(
                $this->dialogsAsParticipant1->toArray(),
                $this->dialogsAsParticipant2->toArray()
            )
        );
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
            $message->setSender($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        $this->messages->removeElement($message);

        return $this;
    }
}
