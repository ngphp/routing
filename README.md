# NextPHP Data Package

The NextPHP Data package is a powerful tool for PHP developers, providing ORM capabilities and direct SQL query execution. It simplifies database interactions by allowing developers to work with objects instead of raw SQL queries. With support for attributes to define entities and relationships, NextPHP Data ensures a clean and efficient codebase.

## Features

- ORM with attribute-based entity definitions
- Direct SQL query capabilities
- Transactional support
- Relationship handling (OneToMany, ManyToOne, ManytoMany etc.)
- Easy integration with existing projects

## Installation

### Installing via Composer

To install the NextPHP Data package, you need to add it to your project using Composer.

```bash
composer require nextphp/data
```


# Example Project using NextPHP Data

This is an example project demonstrating the usage of the NextPHP Data package, which includes ORM and direct SQL query capabilities.

## Basic Usage
### Defining Entities
Entities represent the tables in your database. Use attributes to define the properties and their types.

## Usage

### Using Entity

```php
<?php
namespace Example;

use NextPHP\Data\Persistence\Column;
use NextPHP\Data\Persistence\Entity;
use NextPHP\Data\Persistence\GeneratedValue;
use NextPHP\Data\Persistence\Id;
use NextPHP\Data\Persistence\Table;

#[Entity(name: "users")]
class User
{
    #[Id]
    #[Column('INT AUTO_INCREMENT PRIMARY KEY', false)]
    public int $id;

    #[Column('VARCHAR(255)', false)]
    public string $name;

    #[Column('VARCHAR(255)', false)]
    public string $email;

    #[Column('VARCHAR(255)', false)]
    public string $password;

    // getters and setters
}
```

### Advanced Entity Usage
Relationships OneToMany and ManyToOne
Define relationships using attributes.

```php
<?php
namespace Example;

use NextPHP\Data\Persistence\Column;
use NextPHP\Data\Persistence\Entity;
use NextPHP\Data\Persistence\GeneratedValue;
use NextPHP\Data\Persistence\Id;
use NextPHP\Data\Persistence\Table;

#[Entity(name: "users")]
class Post
{
    #[Id]
    #[Column('INT AUTO_INCREMENT PRIMARY KEY', false)]
    public int $id;

    #[Column('VARCHAR(255)', false)]
    public string $title;

    #[Column('TEXT', false)]
    public string $content;

    #[Column('INT', false)]
    public int $user_id;

    // example for ManyToMany, OneToMany etc.
    #[ManyToOne(targetEntity: User::class, inversedBy: 'posts')]
    private User $user;

    // getters and setters
}
```

### Using Repository
Repositories handle database operations for entities. Extend ***BaseRepository*** and specify the entity class.

```php
<?php
namespace Example;

use NextPHP\Data\BaseRepository;

#[Repository(entityClass: User::class)]
class UserRepository extends BaseRepository
{
    // No need for constructor
}
```

### Service Layer
Services provide business logic and interact with repositories.

```php
<?php
namespace Example;

#[Service(description: 'User management service')]
class UserService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    #[Transactional]
    public function registerUser(array $userData): User
    {
        $user = new User();
        $user->name = $userData['name'];
        $user->email = $userData['email'];
        $user->password = password_hash($userData['password'], PASSWORD_DEFAULT);

        $userArray = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $user->password,
        ];

        $this->userRepository->save($userArray);

        return $user;
    }

    public function getAllUsers(): array
    {
        return $this->userRepository->findAll();
    }

    public function getUserById(int $id): ?User
    {
        $userArray = $this->userRepository->find($id);
        if (!$userArray) {
            return null;
        }

        $user = new User();
        $user->id = $userArray['id'];
        $user->name = $userArray['name'];
        $user->email = $userArray['email'];
        $user->password = $userArray['password'] ?? '';

        return $user;
    }

    public function updateUser(int $id, array $data): ?User
    {
        $user = $this->getUserById($id);
        if (!$user) {
            return null;
        }

        foreach ($data as $key => $value) {
            if (property_exists($user, $key)) {
                $user->$key = $value;
            }
        }

        $userArray = get_object_vars($user);
        $this->userRepository->update($id, $userArray);

        return $user;
    }

    public function deleteUser(int $id): bool
    {
        $user = $this->getUserById($id);
        if (!$user) {
            return false;
        }

        $this->userRepository->delete($id);

        return true;
    }
}

```

### Example Project
Example for your Project Structure

```php

Example Project
Project Structure

example/
├── src/
│   ├── Entity/User.php
│   ├── Repository/UserRepository.php
│   ├── Service/UserService.php
├── composer.json
└── README.md
