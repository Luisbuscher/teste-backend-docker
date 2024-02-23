<?php

namespace Contatoseguro\TesteBackend\Controller;

use Contatoseguro\TesteBackend\Model\Product;
use Contatoseguro\TesteBackend\Service\CategoryService;
use Contatoseguro\TesteBackend\Service\ProductService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProductController
{
    private ProductService $service;
    private CategoryService $categoryService;

    public function __construct()
    {
        $this->service = new ProductService();
        $this->categoryService = new CategoryService();
    }

    public function getAll(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $adminUserId = $request->getHeader('admin_user_id')[0];

        $stm = $this->service->getAll($adminUserId);
        $response->getBody()->write(json_encode($stm->fetchAll()));
        return $response->withStatus(200);
    }

    // CHAMADA DA FUNCAO PARA FILTRAR OS PRODUTOS POR ATIVOS E INATIVOS
    public function getAllFilter(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $adminUserId = $request->getHeader('admin_user_id')[0];

        $stm = $this->service->getAllFilter($adminUserId, $args['filter']);
        $response->getBody()->write(json_encode($stm->fetchAll()));
        return $response->withStatus(200);
    }

    // CHAMADA DA FUNCAO PARA FILTRAR OS PRODUTOS POR ATIVOS E INATIVOS
    public function getCategory(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $adminUserId = $request->getHeader('admin_user_id')[0];

        $stm = $this->service->getCategory($adminUserId, $args['category']);
        $response->getBody()->write(json_encode($stm->fetchAll()));
        return $response->withStatus(200);
    }

    // CHAMADA DA FUNCAO PARA FILTRAR OS PRODUTOS POR CATEGORIAS
    public function getFilterCategory(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $adminUserId = $request->getHeader('admin_user_id')[0];

        $stm = $this->service->getAllFilter($adminUserId, $args['filter']);
        $response->getBody()->write(json_encode($stm->fetchAll()));
        return $response->withStatus(200);
    }

    public function getOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $stm = $this->service->getOne($args['id']);
        $product = Product::hydrateByFetch($stm->fetch());

        $adminUserId = $request->getHeader('admin_user_id')[0];

        $productCategories = $this->categoryService->getProductCategory($product->id)->fetchAll(); // Obter todas as categorias associadas ao produto

        if (!empty($productCategories)) {
            $categories = []; // Array para armazenar as categorias
            foreach ($productCategories as $productCategory) {
                $fetchedCategory = $this->categoryService->getOne($adminUserId, $productCategory->id)->fetch();
                if ($fetchedCategory) {
                    echo "TESTE CATEGORIA $fetchedCategory->title"; // Isso pode ser removido, eu fiz apenas para depuração
                    // Adicionar a categoria ao array de categorias
                    $categories[] = $fetchedCategory->title;
                }
            }
            // Agora adiiona todas as categorias ao produto
            $product->setCategory($categories);
        }

        $response->getBody()->write(json_encode($product));
        return $response->withStatus(200);
    }

    public function insertOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $body = $request->getParsedBody();
        $adminUserId = $request->getHeader('admin_user_id')[0];

        if ($this->service->insertOne($body, $adminUserId)) {
            return $response->withStatus(200);
        } else {
            return $response->withStatus(404);
        }
    }

    public function updateOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $body = $request->getParsedBody();
        $adminUserId = $request->getHeader('admin_user_id')[0];

        if ($this->service->updateOne($args['id'], $body, $adminUserId)) {
            return $response->withStatus(200);
        } else {
            return $response->withStatus(404);
        }
    }

    public function deleteOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $adminUserId = $request->getHeader('admin_user_id')[0];

        if ($this->service->deleteOne($args['id'], $adminUserId)) {
            return $response->withStatus(200);
        } else {
            return $response->withStatus(404);
        }
    }
}
