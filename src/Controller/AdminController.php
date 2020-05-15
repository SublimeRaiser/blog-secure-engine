<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\BlogPost;
use App\Form\AuthorType;
use App\Form\BlogPostType;
use App\Repository\AuthorRepository;
use App\Repository\BlogPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
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
     * @Route("/", name="admin_index", methods={"GET"})
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("/blog-post", name="admin_blog_post_index", methods={"GET"})
     *
     * @return Response
     *
     * @throws NonUniqueResultException
     */
    public function indexBlogPost(): Response
    {
        $currentUsername = $this->getUser()->getUsername();
        $author          = $this->authorRepo->findByUsername($currentUsername);
        $blogPosts       = $this->blogPostRepo->findByAuthor($author);

        return $this->render('admin/blog_post_index.html.twig', [
            'blogPosts' => $blogPosts,
        ]);
    }

    /**
     * @Route("/author/new", name="admin_author_new", methods={"GET", "POST"})
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws NonUniqueResultException
     */
    public function newAuthor(Request $request): Response
    {
        $currentUsername = $this->getUser()->getUsername();
        if ($this->authorRepo->findByUsername($currentUsername)) {
            $this->addFlash('danger', 'Unable to create author, author already exists!');

            return $this->redirectToRoute('blog_post_index');
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

            return $this->redirectToRoute('blog_post_index');
        }

        return $this->render('admin/author_new.html.twig', [
            'authorForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/blog-post/new", name="admin_blog_post_new", methods={"GET","POST"})
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws NonUniqueResultException
     */
    public function newBlogPost(Request $request): Response
    {
        $currentUsername = $this->getUser()->getUsername();
        $author          = $this->authorRepo->findByUsername($currentUsername);

        $blogPost = new BlogPost();
        $blogPost->setAuthor($author);

        $form = $this->createForm(BlogPostType::class, $blogPost);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($blogPost);
            $this->em->flush();

            $this->addFlash('success', 'Congrats! Your post is created!');

            return $this->redirectToRoute('admin_index');
        }

        return $this->render('admin/blog_post_new.html.twig', [
            'blogPostForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/blog-post/{id}", name="admin_blog_post_delete", methods={"GET"})
     *
     * @param BlogPost $blogPost
     *
     * @return RedirectResponse
     *
     * @throws NonUniqueResultException
     */
    public function deleteBlogPost(BlogPost $blogPost): RedirectResponse
    {
        $currentUsername = $this->getUser()->getUsername();
        $author          = $this->authorRepo->findByUsername($currentUsername);

        if ($author !== $blogPost->getAuthor()) {
            $this->addFlash('error', 'Unable to remove blog post!');

            return $this->redirectToRoute('admin_blog_post_index');
        }

        $this->em->remove($blogPost);
        $this->em->flush();

        $this->addFlash('success', 'Blog post was deleted!');

        return $this->redirectToRoute('admin_blog_post_index');
    }
}
