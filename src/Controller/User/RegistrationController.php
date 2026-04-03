<?php

namespace App\Controller\User;

use App\Entity\Condidat;
use App\Entity\Recruiter;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    private const CAPTCHA_KEY = 'registration_captcha';

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        SessionInterface $session,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        if (!$session->has(self::CAPTCHA_KEY)) {
            $session->set(self::CAPTCHA_KEY, $this->generateCode());
        }

        $error   = null;
        $profile = $request->request->get('profile_type') ?: $request->query->get('profile_type') ?: 'CANDIDATE';

        if ($request->isMethod('POST')) {
            $captchaInput = (string) $request->request->get('captcha_input', '');
            $captchaRef   = (string) $session->get(self::CAPTCHA_KEY, '');

            $email     = (string) $request->request->get('email', '');
            $password  = (string) $request->request->get('password', '');
            $firstName = (string) $request->request->get('first_name', '');
            $lastName  = (string) $request->request->get('last_name', '');

            $isCandidate = $profile === 'CANDIDATE';
            $phone = $isCandidate
                ? trim((string) $request->request->get('phone_prefix', '') .' '. (string) $request->request->get('phone', ''))
                : (string) $request->request->get('phone_recruiter', '');

            if (!\in_array($profile, ['CANDIDATE', 'RECRUITER'], true)) {
                $error = 'Choisissez un type de profil.';
            } elseif (\strtoupper($captchaInput) !== \strtoupper($captchaRef)) {
                $error = 'Code captcha invalide.';
                $session->set(self::CAPTCHA_KEY, $this->generateCode());
            } elseif ($email === '' || $password === '' || $firstName === '' || $lastName === '') {
                $error = 'Tous les champs sont obligatoires.';
            } elseif ($em->getRepository(User::class)->findOneBy(['email' => $email])) {
                $error = 'Un compte existe déjà avec cet email.';
            } else {
                try {
        $user = new Users();
                    $user->setEmail($email);
                    $user->setFirstName($firstName ?: null);
                    $user->setLastName($lastName ?: null);
                    $user->setPhone($phone ?: null);
                    $user->setIsActive(true);
                    $user->setRole($profile);
                    $user->setStatus('USER_VERIFIED');
                    $user->setCreatedAt(new \DateTimeImmutable());
                    $user->setUpdatedAt(new \DateTime());
                    $user->setPassword($hasher->hashPassword($user, $password));

                    $em->persist($user);
                    $em->flush();

                    if ($isCandidate) {
                        $c = new Condidat();
                        $c->setUserId($user->getId());
                        $c->setBio(null);
                        $em->persist($c);
                    } else {
                        $r = new Recruiter();
                        $r->setUserId($user->getId());
                        $r->setSecteurActivite('');
                        $r->setEmailContactEntreprise($email);
                        $r->setTelephoneServiceClient($phone ?: '');
                        $r->setSalaires(0);
                        $em->persist($r);
                    }

                    $em->flush();
                    $session->set(self::CAPTCHA_KEY, $this->generateCode());
                    $this->addFlash('success', 'Compte créé. Connectez-vous.');
                    return $this->redirectToRoute('app_login');
                } catch (\Throwable $e) {
                    $error = 'Erreur lors de la création du compte : '.$e->getMessage();
                }
            }
        }

        return $this->render('user/auth/register.html.twig', [
            'captcha' => $session->get(self::CAPTCHA_KEY),
            'profile' => $profile,
            'error'   => $error,
        ]);
    }

    private function generateCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < 6; ++$i) {
            $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        return $code;
    }
}
