<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\BlogPost;
use App\Repository\AuthorRepository;
use App\Repository\BlogPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogPostController extends AbstractController
{
    /** @var integer */
    const POST_LIMIT = 5;

    /** @var EntityManagerInterface */
    private $em;

    /** @var AuthorRepository */
    private $authorRepo;

    /** @var BlogPostRepository */
    private $blogPostRepo;

    /**
     * BlogPostController constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em           = $em;
        $this->authorRepo   = $em->getRepository(Author::class);
        $this->blogPostRepo = $em->getRepository(BlogPost::class);
    }

    /**
     * @Route("/", name="blog_post_index", methods={"GET"})
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function index(Request $request): Response
    {
        $page = $request->query->get('page', 1);

        return $this->render('blog_post/index.html.twig', [
            'blogPosts'  => $this->blogPostRepo->getPaginatedList($page, self::POST_LIMIT),
            'page'       => $page,
            'totalCount' => $this->blogPostRepo->getTotalCount(),
            'postLimit'  => self::POST_LIMIT,
        ]);
    }

    /**
     * @Route("/{slug}", name="blog_post_show", methods={"GET"})
     *
     * @param string $slug
     *
     * @return Response
     */
    public function show(string $slug): Response
    {
        $blogPost = $this->blogPostRepo->findOneBySlug($slug);
        if (!$blogPost) {
            $this->addFlash('error', 'Unable to find blog post!');

            return $this->redirectToRoute('blog_post_index');
        }

        return $this->render('blog_post/show.html.twig', [
            'blogPost' => $blogPost,
        ]);
    }

    /**
     * @Route("/author/{username}", name="blog_post_author", methods={"GET"})
     *
     * @param string $username
     *
     * @return Response
     */
    public function author(string $username): Response
    {
        $author = $this->authorRepo->findOneByUsername($username);
        if (!$author) {
            $this->addFlash('error', 'Unable to find author!');

            return $this->redirectToRoute('blog_post_index');
        }

        return $this->render('blog_post/author.html.twig', [
            'author' => $author,
        ]);
    }
}
