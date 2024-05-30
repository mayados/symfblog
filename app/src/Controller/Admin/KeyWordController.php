<?php

namespace App\Controller\Admin;

use App\Entity\Keyword;
use App\Form\AddKeywordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/keyword', name: 'app_admin_keyword_')]
class KeyWordController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/keyword/index.html.twig', [
            'controller_name' => 'KeyWwordController',
        ]);
    }

    #[Route('/ajouter', name: 'add')]
    public function addKeyword(
        Request $request,
         SluggerInterface $slugger,
         EntityManagerInterface $em
    ): Response
    {

        $keyword = new Keyword();

        $keywordForm = $this->createForm(AddKeywordFormType::class, $keyword);

        $keywordForm->handleRequest($request);

        if($keywordForm->isSubmitted() && $keywordForm->isValid())
        {
            // creation of the slug
            $slug = strtolower($slugger->slug($keyword->getName()));

            $keyword->setSlug($slug);

            $em->persist($keyword);
            $em->flush();

            $this->addFlash('success','le mot clé a été créé');
            return $this->redirectToRoute('app_admin_keyword_index');
            
        }

        return $this->render('admin/keyword/add.html.twig', [
            'keywordForm' => $keywordForm->createView(),
        ]);
    }


    
}
