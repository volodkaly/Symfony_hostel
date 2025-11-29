<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

class UserCrudController extends AbstractCrudController
{
    private UserPasswordHasherInterface $passwordHasher;

    // 1. Впроваджуємо сервіс хешування через конструктор
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            EmailField::new('email'),

            // 2. Налаштовуємо поле пароля
            TextField::new('password')
                ->setFormType(PasswordType::class)
                ->setRequired($pageName === Crud::PAGE_NEW) // Обов'язкове тільки при створенні
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->hashPassword($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    // 4. Перехоплюємо оновлення користувача (UPDATE)
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->hashPassword($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    // Допоміжний метод для хешування
    private function hashPassword($user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // Отримуємо "чистий" пароль, який ввели у форму
        $plainPassword = $user->getPassword();

        // Якщо пароль не змінювали (поле пусте при редагуванні) - нічого не робимо
        if (empty($plainPassword)) {
            return;
        }

        // Хешуємо пароль
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);

        // Записуємо хеш назад у сутність
        $user->setPassword($hashedPassword);
    }
}