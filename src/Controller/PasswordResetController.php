<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/password')]
class PasswordResetController extends AbstractController
{
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userRepository = $userRepository;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/forgot', name: 'password_forgot', methods: ['POST'])]
    public function requestReset(Request $request, EntityManagerInterface $em, MailerInterface $mailer, EmailService $emailService): Response
    {
        $data = json_decode($request->getContent(), true);
        $email    = $data["email"];
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return $this->json(['message' => 'Email introuvable'], Response::HTTP_NOT_FOUND);
        }

        // Générer un token unique et une expiration (exemple avec UUID)
        $token = Uuid::v4()->toRfc4122();
        $user->setToken($token);
        $expiryDate = new \DateTime('+1 hour');
        $user->setTokenExpiryDate(new \DateTimeImmutable($expiryDate->format('Y-m-d H:i:s')));
        $em->persist($user);
        $em->flush();

        // Envoyer un email à l'utilisateur avec un lien de réinitialisation
        $resetLink = $this->generateUrl('password_reset', ['token' => $token], true);
        $to = $user->getEmail();
        $subject = 'Réinitialisation de votre mot de passe';
        $message = "Cliquez sur ce lien pour réinitialiser votre mot de passe : <a href=\"$resetLink\">$resetLink</a>";

        try {
            $emailService->sendEmail($to, $subject, $message, 'ok');
            return new JsonResponse(['message' => 'Email de réinitialisation de mot de passe envoyé'], 200, []);
        } catch (\Exception $e){
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/reset/{token}', name: 'password_reset', methods: ['PATCH'])]
    public function resetPassword(Request $request, string $token, EntityManagerInterface $em): Response
    {
        $user = $this->userRepository->findOneBy(['token' => $token]);

        if (!$user || $user->getTokenExpiryDate() < new \DateTimeImmutable()) {
            return $this->json(['message' => 'Token invalide ou expiré'], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);
        $newPassword   = $data["password"];
        if (!$newPassword) {
            return $this->json(['message' => 'Mot de passe requis'], Response::HTTP_BAD_REQUEST);
        }

        // Encoder le nouveau mot de passe
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $newPassword));
        $user->setToken(null); // Réinitialiser le token pour qu'il ne puisse pas être réutilisé
        $user->setTokenExpiryDate(null);
        $em->persist($user);
        $em->flush();

        return $this->json(['message' => 'Mot de passe réinitialisé avec succès']);
    }
}
