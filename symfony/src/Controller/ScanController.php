<?php

namespace App\Controller;

use App\AbstractEntity;
use App\Entity\Author;
use App\Entity\AuthorProduct;
use App\Entity\Book;
use App\Entity\Isbn;
use App\ValidationException;
use App\Validator;
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
     */
    public function postScanAction(Request $request)
    {
        $post = $request->request;
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        AbstractEntity::setEntityManager($em);

        $em->getConnection()->beginTransaction();
        try {
            $validator = new Validator\ScannedData([
                Validator\ScannedData::PARAM__TITLE => $post->get(Validator\ScannedData::PARAM__TITLE),
                Validator\ScannedData::PARAM__YEAR => $post->get(Validator\ScannedData::PARAM__YEAR),
                Validator\ScannedData::PARAM__AUTHOR_FULL_NAME => $post->get(Validator\ScannedData::PARAM__AUTHOR_FULL_NAME),
                Validator\ScannedData::PARAM__ISBN => $post->get(Validator\ScannedData::PARAM__ISBN),
            ]);
            if (!$validator->isValid()) {
                throw new ValidationException('Invalid data');
            }
            $title = $validator->getParam(Validator\ScannedData::PARAM__TITLE);
            $year = $validator->getParam(Validator\ScannedData::PARAM__YEAR);
            $authorFullName = $validator->getParam(Validator\ScannedData::PARAM__AUTHOR_FULL_NAME);
            $isbnNum = $validator->getParam(Validator\ScannedData::PARAM__ISBN);

            // Find or create Book
            $book = AbstractEntity::findOneBy(Book::class, [
                'title_lowercase' => strtolower($title),
                'year' => $year,
            ]);
            if (empty($book)) {
                $book = new Book();
                $book->setTitle($title)
                    ->setYear($year);
            }

            // Find or create Author
            $author = AbstractEntity::findOneBy(Author::class, [
                'full_name_lowercase' => strtolower($authorFullName),
            ]);
            if (empty($author)) {
                $author = new Author();
                $author->setFullName($authorFullName);
            }

            // Find Isbn
            /** @var Isbn $isbn */
            $isbn = $em->getRepository(Isbn::class)
                ->findOneBy(['num' => $isbnNum]);

            // Check Isbn
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

            // Append Isbn to Book if valid and not exists
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

            // Find relation between Author and Book
            $authorProduct = $em->getRepository(AuthorProduct::class)
                ->findOneBy([
                    'authorId' => $author->getId(),
                    'productType' => AbstractEntity::PRODUCT_TYPE__BOOK,
                    'productId' => $book->getId(),
                ]);

            if (!empty($authorProduct) && !$isbnAdded) {
                throw new ValidationException('Already exists');
            }

            // Create relation between Author and Book
            if (empty($authorProduct)) {
                $authorProduct = new AuthorProduct();
                $authorProduct->setAuthorId($author->getId());
                $authorProduct->setProductId($book->getId());
                $authorProduct->setProductType(AbstractEntity::PRODUCT_TYPE__BOOK);
                $em->persist($authorProduct);
                $em->flush();
            }

            $em->getConnection()->commit();
            $response = "ok";
            $responseCode = $this->container->getParameter('app.led.green');

        } catch (ValidationException $e) {
            try {
                $em->getConnection()->rollBack();
                $response = $e->getMessage();
            } catch (\Exception $ee) {
                $response = $ee->getMessage();
            }
            $responseCode = $this->container->getParameter('app.led.red');
        } catch (\Exception $e) {
            try {
                $em->getConnection()->rollBack();
            } catch (\Exception $ee) {
                // ---
            }
            $response = 'Unknown error';
            $responseCode = $this->container->getParameter('app.led.red');
            //throw $e;
        }

        return View::create($response, $responseCode);
    }
}
