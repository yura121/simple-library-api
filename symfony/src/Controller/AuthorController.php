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
class AuthorController extends Controller
{
    /**
     * @FOSRest\Get("/author/books")
     * @param Request $request
     * @return View
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAuthorBooksAction(Request $request)
    {
        $authorFullName = $request->get('author_full_name');

        $sql = "
SELECT
    b.title,
    (SELECT num FROM isbn WHERE book_id = b.id ORDER BY created_at ASC LIMIT 1) AS main_isbn,
    b.created_at AS time_added
FROM
    book b
    INNER JOIN author__product ap ON ap.product_id = b.id AND ap.product_type = :product_type
    INNER JOIN author a on ap.author_id = a.id
WHERE a.name = :author_full_name
ORDER BY b.title ASC
";
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $connection = $em->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindValue('product_type', AbstractEntity::PRODUCT_TYPE__BOOK, PDO::PARAM_INT);
        $statement->bindValue('author_full_name', $authorFullName);
        $statement->execute();
        $results = $statement->fetchAll();

        return View::create($results, Response::HTTP_OK, []);
    }
}
