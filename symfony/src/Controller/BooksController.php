<?php

namespace App\Controller;

use App\AbstractEntity;
use Doctrine\ORM\EntityManager;
use PDO;
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
class BooksController extends Controller
{
    /**
     * @FOSRest\Get("/books/authors/top/{count}")
     * @param int $count
     * @return View
     */
    public function getTopAuthorsAction(int $count)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $connection = $em->getConnection();

        $sql = "
SELECT
    a.full_name AS author_full_name,
    COUNT(ap.id) AS books_count
FROM
    author a
    INNER JOIN author_product ap ON ap.author_id = a.id AND ap.product_type = :product_type
GROUP BY
    a.id
ORDER BY
    books_count DESC,
    author_full_name ASC
LIMIT
    :count
";
        try {
            $statement = $connection->prepare($sql);
            $statement->bindValue('product_type', AbstractEntity::PRODUCT_TYPE__BOOK, PDO::PARAM_INT);
            $statement->bindValue('count', $count, PDO::PARAM_INT);
            $statement->execute();
            $response = $statement->fetchAll();
            $responseCode = Response::HTTP_OK;
        } catch (\Exception $e) {
            $response = 'Unknown error';
            $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return View::create($response, $responseCode);
    }
}
