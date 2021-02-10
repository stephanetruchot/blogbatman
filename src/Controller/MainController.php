<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use App\Entity\Article;
use App\Form\EditPhotoType;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="main_home")
     */
    public function home(): Response
    {

        $articleRepo = $this->getDoctrine()->getRepository(Article::class);

        // Récupération des 3 derniers articles

        $articles = $articleRepo->findBy([], ['publicationDate' => 'DESC'], $this->getParameter('app.article.last_article_number'));


        return $this->render('main/home.html.twig', [
            'articles' => $articles
        ]);
    }


    /**
     * Page de profil
     *
     * @Route("/mon-profil/", name="main_profil")
     * @Security("is_granted('ROLE_USER')")
     */
    public function profil(): Response
    {
        return $this->render('main/profil.html.twig');
    }


    /**
     * Page de modif de la photo de profil
     *
     * @Route("/edit-photo/", name="main_edit_photo")
     * @Security("is_granted('ROLE_USER')")
     */
    public function editPhoto(Request $request): Response
    {

        $form = $this->createForm(EditPhotoType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $photo = $form->get('photo')->getData();

            if($this->getUser()->getPhoto() != null){
                unlink( $this->getParameter('app.user.photo.directory') . $this->getUser()->getPhoto() );
            }

            $newFileName = md5( random_bytes(100) . time() ) . '.' . $photo->guessExtension();


            // Sauvegarde du nom du fichier dans l'utilisateur connecté en BDD
            $this->getUser()->setPhoto($newFileName);
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            // Déplacer le fichier de l'image dans un dossier
            $photo->move(
                $this->getParameter('app.user.photo.directory'),
                $newFileName
            );

            $this->addFlash('success', 'Photo de profil modifiée avec succès !');

            return $this->redirectToRoute('main_profil');

        }



        return $this->render('main/editPhoto.html.twig', [
            'form' => $form->createView()
        ]);
    }

}
