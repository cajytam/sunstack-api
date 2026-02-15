<?php

namespace App\Controller\Security;

use App\Entity\User\AuthToken;
use App\Entity\User\User;
use App\Repository\User\AuthTokenRepository;
use App\Repository\User\UserRepository;
use App\Utils\Generator;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api', name: 'app_api_')]
class ApiAuthController extends AbstractController
{
    public function __construct(
        protected readonly UserPasswordHasherInterface $hasher,
        protected RequestStack                         $requestStack,
    )
    {
    }

    #[Route('/login', name: 'login')]
    public function index(
        #[CurrentUser] ?User   $user,
        EntityManagerInterface $manager
    ): Response
    {
        if (null === $user) {
            return $this->json([
                'error' => 'Email et/ou mot de passe incorrect(s)',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (null !== $user->getDeletedAt()) {
            return $this->json([
                'error' => 'Votre compte est désactivé. Merci de contacter un administrateur',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = Generator::generateUniqueToken($user);

        $request = $this->requestStack->getCurrentRequest();

        $authToken = new AuthToken();
        $authToken
            ->setToken($token)
            ->setUser($user)
            ->setMode('authorization')
            ->setIp($request->getClientIp());

        if ($request->headers->has('User-Agent')) {
            $authToken->setUserAgent($request->headers->get('User-Agent'));
        } else if (array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
            $authToken->setUserAgent($_SERVER['HTTP_USER_AGENT']);
        }

//        $manager->persist($authToken);
//        $manager->flush();

        return $this->json([
            'user' => $user->getUserIdentifier(),
            'token' => $token,
            'settings' => $user->getSettings(),
        ]);
    }

    #[Route('/logout', name: 'logout', methods: ['GET'])]
    public function logout()
    {
    }

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(
        #[CurrentUser] ?User $user
    ): Response
    {
        if (null === $user) {
            return $this->json([
                'user' => null,
                'error' => 'Veuillez vous connecter'
            ],
                Response::HTTP_UNAUTHORIZED
            );
        }

        if (null !== $user->getDeletedAt()) {
            return $this->json([
                'user' => null,
                'error' => 'Votre compte est désactivé. Merci de contacter un administrateur',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'id' => $user->getId(),
            'username' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
            'uri' => $user->getUri(),
            'picture' => $user->getPicture(),
            'fullName' => $user->getFullName(),
            'prenom' => $user->getFirstname(),
            'title' => $user->getTitle(),
            'permissions' => $user->getPermissions(),
        ]);
    }

    #[Route('/checkPassword/{plainPassword}', name: 'check_password', methods: ['GET'])]
    public function isPasswordValid(#[CurrentUser] ?User $user, #[\SensitiveParameter] string $plainPassword): Response
    {
        if (null === $user) {
            return $this->json([
                'user' => null,
                'error' => 'Veuillez vous connecter'
            ],
                Response::HTTP_UNAUTHORIZED
            );
        }

        if ($this->hasher->isPasswordValid($user, $plainPassword)) {
            return $this->json([
                'success' => true
            ],
                Response::HTTP_ACCEPTED
            );
        }
        return $this->json([
            'success' => false,
            'message' => "Le mot de passe actuel n'est pas correct"
        ],
            Response::HTTP_UNAUTHORIZED
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    #[Route('/email-reset-password', name: 'email_reset_password', methods: ['POST'])]
    public function resetPasswordSendEmail(
        #[CurrentUser] ?User   $user,
        UserRepository         $userRepository,
        EntityManagerInterface $manager,
        MailerInterface        $mailer,
        Request                $request
    ): Response
    {
        $parameters = json_decode($request->getContent(), true);

        if (!array_key_exists('email', $parameters)) {
            return $this->json([
                'error' => 'Demande de réinitialisation incorrecte'
            ],
                Response::HTTP_UNAUTHORIZED
            );
        }

        if ($user) {
            return $this->json([
                'user' => $user,
                'error' => 'Utilisateur déjà connecté'
            ],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $userFromEmail = $userRepository->findOneBy([
            'email' => $parameters['email']
        ]);

        if (!$userFromEmail) {
            return $this->json([
                'error' => 'Utilisateur introuvable',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (null !== $userFromEmail->getDeletedAt()) {
            return $this->json([
                'error' => 'Ce compte est désactivé',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = Generator::generateUniqueToken($userFromEmail);

        $request = $this->requestStack->getCurrentRequest();

        $authToken = new AuthToken();
        $authToken
            ->setToken($token)
            ->setUser($userFromEmail)
            ->setMode('reset-password')
            ->setIp($request->getClientIp());

        if ($request->headers->has('User-Agent')) {
            $authToken->setUserAgent($request->headers->get('User-Agent'));
        } else if (array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
            $authToken->setUserAgent($_SERVER['HTTP_USER_AGENT']);
        }

        $manager->persist($authToken);
        $manager->flush();

        $htmlBody = $this->renderView(
            'emails/user/reset_password.html',
            [
                'USER_FIRSTNAME' => $userFromEmail->getFirstname() ?: '',
                'PASSWORD_RESET_LINK' => $this->getParameter('crm_url') . 'auth/reset-password/' . $authToken->getToken(),
                'BASE_URL' => $this->getParameter('base_url')
            ]
        );

        $email = (new Email())
            ->from(Address::create('SunStack <sunstack@cajytam.fr>'))
            ->to($userFromEmail->getEmail())
            ->subject('Mot de passe oublié ? Ça arrive 😀')
            ->html($htmlBody);

        $mailer->send($email);

        return $this->json([
            'success' => true
        ]);
    }

    #[Route('/reset-password/{token}', name: 'reset_password', methods: ['GET', 'POST'])]
    public function resetPassword(
        AuthTokenRepository    $authTokenRepository,
        Request                $request,
        EntityManagerInterface $manager,
        string                 $token
    ): Response
    {
        $tokenEntity = $authTokenRepository->findOneBy([
            'token' => $token,
            'mode' => 'reset-password'
        ]);

        $user = $tokenEntity?->getUser();

        if (!$user || $user->getDeletedAt() !== null
            || $tokenEntity->getCreatedAt()->add(\DateInterval::createFromDateString('1 day'))->getTimestamp() < (new \DateTime())->getTimestamp()
        ) {
            return $this->json([
                'message' => 'Token non valide',
                'error' => true,
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($request->getMethod() === 'POST') {
            $parameters = json_decode($request->getContent(), true);

            $tokenEntity = $authTokenRepository->findOneBy([
                'token' => $parameters['token'],
                'mode' => 'reset-password'
            ]);
            $user = $tokenEntity?->getUser();

            if (!$user || $user->getDeletedAt() !== null) {
                return $this->json([
                    'error' => true,
                ], Response::HTTP_UNAUTHORIZED);
            }

            $password = $this->hasher->hashPassword($user, $parameters['plainPassword']);
            $user->setPassword($password);
            $manager->persist($user);

            $manager->remove($tokenEntity);

            $manager->flush();
        }

        return $this->json([
            'success' => true
        ]);
    }
}
