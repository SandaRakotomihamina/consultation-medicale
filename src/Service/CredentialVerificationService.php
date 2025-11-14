<?php

namespace App\Service;

use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CredentialVerificationService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    /**
     * Verify username and password against the User table in database.
     *
     * @param string $username The username to verify
     * @param string $password The plain text password to verify
     * @return array{success: bool, user: mixed, message: string}
     */
    public function verifyCredentials(string $username, string $password): array
    {
        // Find user by username
        $user = $this->userRepository->findOneBy(['username' => $username]);

        if (!$user) {
            return [
                'success' => false,
                'user' => null,
                'message' => "User '{$username}' not found.",
            ];
        }

        // Verify the password
        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            return [
                'success' => false,
                'user' => null,
                'message' => "Invalid password for user '{$username}'.",
            ];
        }

        // Credentials are valid
        return [
            'success' => true,
            'user' => $user,
            'message' => "User '{$username}' authenticated successfully.",
        ];
    }
}
