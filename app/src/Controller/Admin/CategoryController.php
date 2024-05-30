<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\AddCategoryFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/categorie', name: 'app_admin_category_')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/category/index.html.twig', [
            'controller_name' => 'CategoryController',
        ]);
    }

    #[Route('/ajouter', name: 'add')]
    public function addCategory(
        Request $request,
        SluggerInterface $slugger,
        EntityManagerInterface $em
    ): Response
    {

        $category = new Category();

        $categoryForm = $this->createForm(AddCategoryFormType::class, $category);

        $categoryForm->handleRequest($request);

        if($categoryForm->isSubmitted() && $categoryForm->isValid())
        {

            $slug = strtolower($slugger->slug($category->getName()));
            $category->setSlug($slug);

            $em->persist($category);
            $em->flush();

            $this->addFlash('success','la catégorie a été créée');
            return $this->redirectToRoute('app_admin_category_index');
            
        }

        return $this->render('admin/category/add.html.twig', [
            'categoryForm' => $categoryForm->createView(),
        ]);
    }

}
