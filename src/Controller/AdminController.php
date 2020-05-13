<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\BlogPost;
use App\Form\AuthorType;
use App\Form\BlogPostType;
use App\Repository\AuthorRepository;
use App\Repository\BlogPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var SessionInterface */
    private $session;

    /** @var AuthorRepository */
    private $authorRepo;

    /** @var BlogPostRepository */
    private $blogPostRepo;

    /**
     * AdminController constructor.
     * @param EntityManagerInterface $em
     * @param SessionInterface       $session
     */
    public function __construct(EntityManagerInterface $em, SessionInterface $session)
    {
        $this->em           = $em;
        $this->session      = $session;
        $this->authorRepo   = $em->getRepository(Author::class);
        $this->blogPostRepo = $em->getRepository(BlogPost::class);
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function index()
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("/admin/author/new", name="admin_author_new", methods={"GET", "POST"})
     *
     * @param Request $request
     */
    public function createAuthor(Request $request)
    {
        $currentUsername = $this->getUser()->getUsername();
        if ($this->authorRepo->findByUsername($currentUsername)) {
            $this->addFlash('danger', 'Unable to create author, author already exists!');

            return $this->redirectToRoute('homepage');
        }

        $author = new Author();
        $author->setUsername($currentUsername);

        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($author);
            $this->em->flush();

            $this->session->set('user_is_author', true);
            $this->addFlash('success', 'Congrats! You are now an author.');

            return $this->redirectToRoute('homepage');
        }

        return $this->render('admin/author_new.html.twig', [
            'authorForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/blog-post/new", name="admin_blog_post_new", methods={"GET","POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createBlogPost(Request $request)
    {
        $currentUsername = $this->getUser()->getUsername();
        $author          = $this->authorRepo->findByUserName($currentUsername);

        $blogPost = new BlogPost();
        $blogPost->setAuthor($author);

        $form = $this->createForm(BlogPostType::class, $blogPost);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($blogPost);
            $this->em->flush();

            $this->addFlash('sucess', 'Congrats! Your post is created!');

            return $this->redirectToRoute('admin_index');
        }

        return $this->render('admin/blog_post_new.html.twig', [
            'blogPostForm' => $form->createView(),
        ]);
    }
}
