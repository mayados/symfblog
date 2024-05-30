<?php

namespace App\Controller\Profile;

use App\Entity\Post;
use App\Form\AddPostFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/profil/article', name: 'app_profile_post_')]
class PostController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('profile/post/index.html.twig', [
            'controller_name' => 'PostController',
        ]);
    }

    #[Route('/ajouter', name: 'add')]
    public function addPost(
        Request $request,
        SluggerInterface $slugger,
        EntityManagerInterface $em,
        UserRepository $userRepository
    ): Response
    {

        $post = new Post();

        $form = $this->createForm(AddPostFormType::class, $post);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {

            $slug = strtolower($slugger->slug($post->getTitle()));
            $post->setSlug($slug);
            $post->setUser($userRepository->find(1));
            $post->setFeaturedImage('default.webp');

            $em->persist($post);
            $em->flush();

            $this->addFlash('success',"L'article a été créé");
            return $this->redirectToRoute('app_admin_post_index');
            
        }

        return $this->render('admin/post/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
