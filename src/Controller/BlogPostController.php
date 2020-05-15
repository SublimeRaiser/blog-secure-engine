<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\BlogPost;
use App\Repository\AuthorRepository;
use App\Repository\BlogPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogPostController extends AbstractController
{
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
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('blog_post/index.html.twig', [
            'blogPosts' => $this->blogPostRepo->findAll(),
        ]);
    }
}
