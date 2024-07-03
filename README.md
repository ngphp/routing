# NextPHP Data Package

The [NextPHP Data](https://packagist.org/packages/nextphp/data) package is a powerful tool for PHP developers, providing ORM capabilities and direct SQL query execution. It simplifies database interactions by allowing developers to work with objects instead of raw SQL queries. With support for attributes to define entities and relationships, NextPHP Data ensures a clean and efficient codebase.

This package is part of the [NextPHP Framework](https://https://github.com/nextphp-projects/nextphp), a modern and lightweight PHP framework designed for performance and scalability. [NextPHP](https://nextphp.io) aims to provide a comprehensive suite of tools and libraries to streamline the development process.

## Features

- ORM with attribute-based entity definitions
- Direct SQL query capabilities
- Transactional support
- Relationship handling (OneToMany, ManyToOne, etc.)
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

```code
example/
├── src/
│   ├── Entity/User.php
│   ├── Repository/UserRepository.php
│   ├── Service/UserService.php
├── example.php
├── composer.json
└── README.md
```

## Testing

To test the NextPHP Data package, you can create an `index.php` file and use the service layer to perform various CRUD operations. Here is an example of how you can do this:

### Example index or example.php

```php
<?php
require 'vendor/autoload.php';

use Example\UserService;
use Example\UserRepository;
use Example\PostService;
use Example\PostRepository;

// Initialize the repositories
$userRepository = new UserRepository();
$postRepository = new PostRepository();

// Initialize the services
$userService = new UserService($userRepository);
$postService = new PostService($postRepository);

// Example: Register a new user
$newUser = $userService->registerUser([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'secret',
]);

echo "New User Registered: " . $newUser->name . "\n";

// Example: Get all users
$users = $userService->getAllUsers();
echo "All Users:\n";
print_r($users);

// Example: Get a user by ID
$user = $userService->getUserById($newUser->id);
echo "User with ID {$newUser->id}:\n";
print_r($user);

// Example: Update a user
$updatedUser = $userService->updateUser($newUser->id, ['name' => 'Jane Doe']);
echo "Updated User: " . $updatedUser->name . "\n";

// Example: Delete a user
$isDeleted = $userService->deleteUser($newUser->id);
echo "User Deleted: " . ($isDeleted ? 'Yes' : 'No') . "\n";

// Example: Create a new post
$newPost = $postService->createPost([
    'title' => 'Hello World',
    'content' => 'This is my first post!',
    'user_id' => $newUser->id, // Assign the post to the new user
]);

echo "New Post Created: " . $newPost->title . "\n";

// Example: Get all posts
$posts = $postService->getAllPosts();
echo "All Posts:\n";
print_r($posts);

// Example: Get a post by ID
$post = $postService->getPostById($newPost->id);
echo "Post with ID {$newPost->id}:\n";
print_r($post);

// Example: Update a post
$updatedPost = $postService->updatePost($newPost->id, ['title' => 'Updated Title']);
echo "Updated Post: " . $updatedPost->title . "\n";

// Example: Delete a post
$isPostDeleted = $postService->deletePost($newPost->id);
echo "Post Deleted: " . ($isPostDeleted ? 'Yes' : 'No') . "\n";
```

## Contributing

We welcome contributions! Here’s how you can help:

- **Report Issues:** Found a bug? Report it on GitHub.
- **Suggest Features:** Have an idea? Share it with us.
- **Submit Pull Requests:** Improve the codebase.
- **Enhance Documentation:** Help us improve our docs.

For more details, see our [Contribution Guidelines](contribution-guidelines.md).

## Resources

- [Official Website](https://nextphp.io)
- [GitHub Repository](https://github.com/nextphp-projects/nextphp)
- [Documentation](https://github.com/nextphp-projects/nextphp)

## Join Our Community

- **Twitter:** Follow us on [Twitter](https://twitter.com/NextPHPOfficial)
- **Discord:** Join our [Discord](https://discord.gg/nextphp) community.

## Contact Us

- **Email:** support@nextphp.io
- **Forum:** [NextPHP Mastodon](https://mastodon.social/@nextphp)
- **GitHub Issues:** [NextPHP GitHub](https://github.com/nextphp-projects/nextphp/issues)

Thank you for being part of the NextPHP community!

<br><br><hr><br>
    
### FAQ

### Q: How do I define an entity?

A: Use the #[Entity] attribute to define a class as an entity and the #[Entity(name: "table_name")]  OR #[Table(name: "table_name")] attribute to specify the table name.

For more details, see our [FAQ Guidelines](FAQ.md).
