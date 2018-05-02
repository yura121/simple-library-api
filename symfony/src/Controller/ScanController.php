<?php

namespace App\Controller;

use App\AbstractEntity;
use App\Entity\Author;
use App\Entity\AuthorProduct;
use App\Entity\Book;
use App\Entity\Isbn;
use App\ValidationException;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as FOSRest;

/**
 * @Route("/")
 */
class ScanController extends Controller
{
    /**
     * Process data from scanner
     * @FOSRest\Post("/scan")
     * @param Request $request
     * @return View
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function postScanAction(Request $request)
    {
        $post = $request->request;
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $hasError = false;
        $em->getConnection()->beginTransaction();
        try {
            $title = $post->get('title');
            $year = $post->get('year');
            $authorFullName = $post->get('author_full_name');
            $isbnNum = $post->get('isbn');

            // find or create Book
            $book = $em->getRepository(Book::class)
                ->findOneBy(['title' => $title, 'year' => $year]);
            if (empty($book)) {
                $book = new Book();
                $book->setTitle($title)
                    ->setYear($year);
            }

            // find or create Author
            $author = $em->getRepository(Author::class)
                ->findOneBy(['name' => $authorFullName]);
            if (empty($author)) {
                $author = new Author();
                $author->setName($authorFullName);
            }

            // find Isbn
            /** @var Isbn $isbn */
            $isbn = $em->getRepository(Isbn::class)
                ->findOneBy(['num' => $isbnNum]);

            // check ISBN
            $isAnotherBookIsbn = false;
            if (!empty($isbn)) {
                $isbnBook = $isbn->getBook();
                if (!empty($isbnBook)) {
                    $isAnotherBookIsbn = $isbnBook->getId() != $book->getId();
                }
            }
            if ($isAnotherBookIsbn) {
                throw new ValidationException('ISBN is already mapped to another book');
            }

            // append ISBN to Book if valid and not exists
            $isbnAdded = false;
            if (empty($isbn)) {
                $isbn = new Isbn();
                $isbn->setNum($isbnNum)
                    ->setBook($book);
                $isbnAdded = true;
            }

            $em->persist($author);
            $em->persist($book);
            $em->persist($isbn);
            $em->flush();

            // find relation between author and book
            $authorProduct = $em->getRepository(AuthorProduct::class)
                ->findOneBy([
                    'authorId' => $author->getId(),
                    'productType' => AbstractEntity::PRODUCT_TYPE__BOOK,
                    'productId' => $book->getId(),
                ]);

            if (!empty($authorProduct) && !$isbnAdded) {
                throw new ValidationException('Already exists');
            }

            if (empty($authorProduct)) {
                $authorProduct = new AuthorProduct();
                $authorProduct->setAuthorId($author->getId());
                $authorProduct->setProductId($book->getId());
                $authorProduct->setProductType(AbstractEntity::PRODUCT_TYPE__BOOK);
                $em->persist($authorProduct);
                $em->flush();
            }

            $em->getConnection()->commit();
            $message = "ok";
        } catch (ValidationException $e) {
            $em->getConnection()->rollBack();
            $message = $e->getMessage();
            $hasError = true;
        } catch (\Exception $e) {
            $em->getConnection()->rollBack();
            $message = "Unknown error";
            $hasError = true;
            //throw $e;
        }

        $responseCode = $hasError
            ? $this->container->getParameter('app.led.red')
            : $this->container->getParameter('app.led.green');

        return View::create($message, $responseCode, []);
    }
}
