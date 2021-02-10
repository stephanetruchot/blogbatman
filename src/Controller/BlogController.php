<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Article;
use App\Form\NewArticleType;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Comment;
use App\Form\CommentType;




    /**
     * Contrôleur de la parties blog du site. Toutes les routes commenceront leur url par /blog et leur nom par "blog_"
     *
     * @Route("/blog", name="blog_")
     */
class BlogController extends AbstractController
{
    /**
     * @Route("/nouvelle-publication/", name="new_publication")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function newPublication(Request $request): Response
    {

        // Création d'un nouvel objet de la classe Article, vide pour le moment
        $newArticle = new Article();

        // Création d'un nouveau formulaire à partir de notre formulaire ArticleType et de notre nouvel article encore vide
        $form = $this->createForm(NewArticleType::class, $newArticle);

        $form->handleRequest($request);


        // Pour savoir si le formulaire a été envoyé, on a accès à cette condition :
        if($form->isSubmitted() && $form->isValid()){

            $newArticle->setPublicationDate( new DateTime() );
            $newArticle->setAuthor( $this->getUser() );

            $em = $this->getDoctrine()->getManager();

            $em->persist($newArticle);

            $em->flush();

            $this->addFlash('success', 'Article publié avec succès !');

            return $this->redirectToRoute('blog_publication_view', [
                'slug' => $newArticle->getSlug()
            ]);
        }


        // Pour que la vue puisse afficher le formulaire, on doit lui envoyer le formulaire généré, avec $form->createView()
        return $this->render('blog/newPublication.html.twig', [
            'form' => $form->createView(),
        ]);

    }


    /**
     * @Route("/publications/listes/", name="publication_list")
     */
    public function publicationList(Request $request, PaginatorInterface $paginator): Response
    {

        // Récupération de la variable $_GET['page']
        // getInt() force la variable à contenir un entier (pour éviter les autres types)
        $requestedPage = $request->query->getInt('page', 1);

        // Si la page demandé est inférieur à 1, erreur 404
        if($requestedPage < 1){
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('SELECT a FROM App\Entity\Article a ORDER BY a.publicationDate DESC');


        $articles = $paginator->paginate(
            $query,
            $requestedPage,
            10,
        );


        return $this->render('blog/publicationList.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/recherche/", name="search")
     */
    public function search(Request $request, PaginatorInterface $paginator): Response
    {

        // Récupération de la variable $_GET['page']
        // getInt() force la variable à contenir un entier (pour éviter les autres types)
        $requestedPage = $request->query->getInt('page', 1);

        // Si la page demandé est inférieur à 1, erreur 404
        if($requestedPage < 1){
            throw new NotFoundHttpException();
        }

        $search = $request->query->get('q');

        $em = $this->getDoctrine()->getManager();

        $query = $em
            ->createQuery('SELECT a FROM App\Entity\Article a WHERE a.title LIKE :search OR a.content LIKE :search ORDER BY a.publicationDate DESC')
            ->setParameters(['search' => '%' . $search . '%'])
        ;


        $articles = $paginator->paginate(
            $query,
            $requestedPage,
            10,
        );

        dump($articles);

        return $this->render('blog/listSearch.html.twig', [
            'articles' => $articles,
        ]);
    }


    /**
     * @Route("/publications/suppression/{id}/", name="publication_delete")
     * @Security("is_granted('ROLE_ADMIN')")
     */

    public function publicationDelete(Article $article, Request $request): Response
    {

        if(!$this->isCsrfTokenValid('blog_publication_delete_' . $article->getId(), $request->query->get('csrf_token') )){
            $this->addFlash('error', 'Token sécurité invalide, veuillez ré-essayer.');
        } else {

        $em = $this->getDoctrine()->getManager();

        $em->remove($article);

        $em->flush();

        $this->addFlash('success', 'La publication a été supprimé avec succès !');

        }

        return $this->redirectToRoute('blog_publication_list');

    }


    /**
     * page admin servant à modifier un article existant via son id passé dans l'URL
     *
     * @Route("/publications/modifier/{id}/", name="publication_edit")
     * @Security("is_granted('ROLE_ADMIN')")
     */

    public function publicationEdit(Article $article, Request $request): Response
    {

        $form = $this->createForm(NewArticleType::class, $article);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $em = $this->getDoctrine()->getManager();

            $em->flush();

            $this->addFlash('success', 'Article modifié avec succès !');

            return $this->redirectToRoute('blog_publication_view', ['slug' => $article->getSlug()]);
        }

        return $this->render('blog/editPublication.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/publications/{slug}/", name="publication_view")
     */

    public function publicationView(Article $article, Request $request): Response
    {

        $newComment = new Comment();

        $form = $this->createForm(CommentType::class, $newComment);

        $form->handleRequest($request);

        // Pour savoir si le formulaire a été envoyé, on a accès à cette condition :
            if($form->isSubmitted() && $form->isValid()){

                $newComment->setPublicationDate( new DateTime() );
                $newComment->setAuthor( $this->getUser() );
                $newComment->setArticle( $article );

                $em = $this->getDoctrine()->getManager();

                $em->persist($newComment);

                $em->flush();

                $this->addFlash('success', 'Commentaire publié avec succès !');

                return $this->redirectToRoute('blog_publication_view', [
                    'slug' => $article->getSlug()
                ]);
            }


        return $this->render('blog/publicationView.html.twig', [
            'article' => $article,
            'form' => $form->createView()
        ]);
    }



    /**
    * @Route("/commantaire/suppression/{id}/", name="comment_delete")
    * @Security("is_granted('ROLE_ADMIN')")
    */
    public function commentDelete(Comment $comment, Request $request): Response
    {

        if(!$this->isCsrfTokenValid('blog_comment_delete_' . $comment->getId(), $request->query->get('csrf_token') )){
            $this->addFlash('error', 'Token sécurité invalide, veuillez ré-essayer.');
        } else {

        $em = $this->getDoctrine()->getManager();

        $em->remove($comment);

        $em->flush();

        $this->addFlash('success', 'Le commentaire a été supprimé avec succès !');

        }

        return $this->redirectToRoute('blog_publication_view', [
            'slug' => $comment->getArticle()->getSlug()
        ]);

    }
}
