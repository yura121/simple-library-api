<?php

namespace App\Controller;

use App\AbstractEntity;
use App\Validator;
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
     */
    public function getAuthorBooksAction(Request $request)
    {
        $validator = new Validator\SearchParams([
            Validator\SearchParams::PARAM__AUTHOR_FULL_NAME => $request->get(Validator\SearchParams::PARAM__AUTHOR_FULL_NAME),
        ]);
        if (!$validator->isValid()) {
            return View::create('Invalid data', Response::HTTP_BAD_REQUEST);
        }

        $authorFullName = $validator->getParam(Validator\SearchParams::PARAM__AUTHOR_FULL_NAME);

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $connection = $em->getConnection();

        $sql = "
SELECT
    b.title,
    (SELECT num FROM isbn WHERE book_id = b.id ORDER BY created_at ASC LIMIT 1) AS main_isbn,
    b.created_at AS time_added
FROM
    book b
    INNER JOIN author_product ap ON ap.product_id = b.id AND ap.product_type = :product_type
    INNER JOIN author a on ap.author_id = a.id
WHERE
    a.full_name_lowercase = :author_full_name
ORDER BY
    b.title ASC
";
        try {
            $statement = $connection->prepare($sql);
            $statement->bindValue('product_type', AbstractEntity::PRODUCT_TYPE__BOOK, PDO::PARAM_INT);
            $statement->bindValue('author_full_name', strtolower($authorFullName));
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
