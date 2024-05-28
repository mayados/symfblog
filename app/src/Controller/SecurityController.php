<?php

namespace App\Controller;

use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\UserRepository;
use App\Services\JWTService;
use App\Services\SendEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/mot-de-passe-oublie', name: 'app_forgotten_password')]
    public function forgottenPassword(
        Request $request,
        UserRepository $userRepository,
        JWTService $jwt,
        SendEmailService $mail
    ): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);

        // we get the datas of the post to manipulate it
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            // the form is sent and valid
            // we are lookng for the user in the database
            $user = $userRepository->findOneByEmail($form->get('email')->getData());

            // We verify if we have a user
            if($user){
                //generate a json web token
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
            
                //generate url to app_reset_password
                $url = $this->generateUrl('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                // send email
                $mail->send(
                    'no-reply@symfblog.com',
                    $user->getEmail(),
                    'Récupération de mot de passe sur le site Symfblog',
                    'password_reset',
                    compact('user','url')
                );
            
                $this->addFlash('success',"E-mail envoyé avec succès");
                return $this->redirectToRoute('app_login');

            }
            // user is null
            $this->addFlash('danger',"Un problème est survenu");
            return $this->redirectToRoute('app_login');

        }

        return $this->render('security/reset_password_request.html.twig', [
            'requestPasswordForm' => $form->createView()
        ]);
    }

    #[Route(path: '/mot-de-passe-oublie/{token}', name: 'app_reset_password')]
    public function resetPassword(
        $token,
        JWTService $jwt,
        UserRepository $userRepository,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $emi
    ): Response
    {

        //verify if the token is valid
        if($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))){
            //token is valid
            // we retrieve datas (payload)
            $payload = $jwt->getPayload($token);
            
            //we get the user
            $user = $userRepository->find($payload['user_id']);

            if($user){
                $form = $this->createForm(ResetPasswordFormType::class);

                $form->handleRequest($request);

                if($form->isSubmitted() && $form->isValid())
                {
                    $user->setPassword(
                        $passwordHasher->hashPassword($user,$form->get('password')->getData())
                    );

                    $emi->flush();

                    $this->addFlash('success',"Mot de passe changé avec succès");
                    return $this->redirectToRoute('app_login');
                }

                return $this->render('security/reset_password.html.twig', [
                    'passwordForm' => $form->createView()
                ]);
            }
        }

        $this->addFlash('danger','Le token est invalide ou a expiré');
        return $this->redirectToRoute('app_login');

    }

}
