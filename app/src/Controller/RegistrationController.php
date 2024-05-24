<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\UserAuthenticator;
use App\Services\JWTService;
use App\Services\SendEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager, JWTService $jwt, SendEmailService $mail): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            //header
            $header = [
                'type' => 'JWT',
                'alg' => 'HS256',
            ];

            //payload
            $payload = [
                'user_id' => $user->getId()
            ];

            //Generate the token
            $token = $jwt->generate($header,$payload, $this->getParameter('app.jwtsecret'));

            
            // send email
            $mail->send(
                'no-reply@symfblog.com',
                $user->getEmail(),
                'Activation de votre compte Symfblog',
                'register',
                compact('user','token')
            );

            $this->addFlash('success','Vous êtes inscrit ! Veuillez cliquer sur le lien reçu par mail pour confirmer votre adresse e-mail.');

            return $security->login($user, UserAuthenticator::class, 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verif/{token}', name: 'verify_user')]
    public function verifUser($token, JWTService $jwt, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        //verify if the token is valid
        if($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))){
            //token is valid
            // we retrieve datas (payload)
            $payload = $jwt->getPayload($token);
            
            //we get the user
            $user = $userRepository->find($payload['user_id']);

            //verifying we have a user and he's not already activated
            if($user && !$user->isVerified()){
                $user->setIsVerified(true);
                $em->flush();

                $this->addFlash('success','Utilisateur activé');
                return $this->redirectToRoute('app_main');
            }
        }

        $this->addFlash('danger','Le token est invalide ou a expiré');
        return $this->redirectToRoute('app_login');
    }


}
