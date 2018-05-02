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
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getTopAuthorsAction(int $count)
    {
        $sql = "
SELECT a.name AS author_full_name, COUNT(ap.id) AS books_count
FROM
    author a
    INNER JOIN author__product ap ON ap.author_id = a.id AND ap.product_type = :product_type
GROUP BY a.id
ORDER BY books_count DESC, author_full_name ASC
LIMIT :count
";
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $connection = $em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindValue('product_type', AbstractEntity::PRODUCT_TYPE__BOOK, PDO::PARAM_INT);
        $statement->bindValue('count', $count, PDO::PARAM_INT);
        $statement->execute();
        $results = $statement->fetchAll();

        return View::create($results, Response::HTTP_OK, []);
    }
}
