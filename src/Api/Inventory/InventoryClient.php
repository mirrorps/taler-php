<?php

namespace Taler\Api\Inventory;

use Taler\Api\Base\AbstractApiClient;
use Taler\Api\Inventory\Actions\GetCategories;
use Taler\Api\Inventory\Actions\GetCategory;
use Taler\Api\Inventory\Actions\CreateCategory;
use Taler\Api\Inventory\Actions\UpdateCategory;
use Taler\Api\Inventory\Actions\DeleteCategory;
use Taler\Api\Inventory\Actions\CreateProduct;
use Taler\Api\Inventory\Actions\UpdateProduct;
use Taler\Api\Inventory\Actions\GetProducts;
use Taler\Api\Inventory\Actions\GetProduct;
use Taler\Api\Inventory\Actions\DeleteProduct;
use Taler\Api\Inventory\Actions\GetPos;
use Taler\Api\Inventory\Dto\CategoryListResponse;
use Taler\Api\Inventory\Dto\CategoryProductList;
use Taler\Api\Inventory\Dto\CategoryCreateRequest;
use Taler\Api\Inventory\Dto\CategoryCreatedResponse;
use Taler\Api\Inventory\Dto\ProductAddDetail;
use Taler\Api\Inventory\Dto\ProductPatchDetail;
use Taler\Api\Inventory\Dto\GetProductsRequest;
use Taler\Api\Inventory\Dto\InventorySummaryResponse;
use Taler\Api\Inventory\Dto\ProductDetail;
use Taler\Api\Inventory\Dto\FullInventoryDetailsResponse;
use Taler\Exception\TalerException;

class InventoryClient extends AbstractApiClient
{
    /**
     * @param array<string, string> $headers Optional request headers
     * @return CategoryListResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function getCategories(array $headers = []): CategoryListResponse|array
    {
        return GetCategories::run($this, $headers);
    }

    /**
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function getCategoriesAsync(array $headers = []): mixed
    {
        return GetCategories::runAsync($this, $headers);
    }

    /**
     * @param int $categoryId
     * @param array<string, string> $headers Optional request headers
     * @return CategoryProductList|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function getCategory(int $categoryId, array $headers = []): CategoryProductList|array
    {
        return GetCategory::run($this, $categoryId, $headers);
    }

    /**
     * @param int $categoryId
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function getCategoryAsync(int $categoryId, array $headers = []): mixed
    {
        return GetCategory::runAsync($this, $categoryId, $headers);
    }

    /**
     * @param CategoryCreateRequest $request
     * @param array<string, string> $headers Optional request headers
     * @return CategoryCreatedResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function createCategory(CategoryCreateRequest $request, array $headers = []): CategoryCreatedResponse|array
    {
        return CreateCategory::run($this, $request, $headers);
    }

    /**
     * @param CategoryCreateRequest $request
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function createCategoryAsync(CategoryCreateRequest $request, array $headers = []): mixed
    {
        return CreateCategory::runAsync($this, $request, $headers);
    }

    /**
     * @param int $categoryId
     * @param CategoryCreateRequest $request
     * @param array<string, string> $headers Optional request headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public function updateCategory(int $categoryId, CategoryCreateRequest $request, array $headers = []): void
    {
        UpdateCategory::run($this, $categoryId, $request, $headers);
    }

    /**
     * @param int $categoryId
     * @param CategoryCreateRequest $request
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function updateCategoryAsync(int $categoryId, CategoryCreateRequest $request, array $headers = []): mixed
    {
        return UpdateCategory::runAsync($this, $categoryId, $request, $headers);
    }

    /**
     * @param int $categoryId
     * @param array<string, string> $headers Optional request headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public function deleteCategory(int $categoryId, array $headers = []): void
    {
        DeleteCategory::run($this, $categoryId, $headers);
    }

    /**
     * @param int $categoryId
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function deleteCategoryAsync(int $categoryId, array $headers = []): mixed
    {
        return DeleteCategory::runAsync($this, $categoryId, $headers);
    }

    /**
     * @param ProductAddDetail $details
     * @param array<string, string> $headers Optional request headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public function createProduct(ProductAddDetail $details, array $headers = []): void
    {
        CreateProduct::run($this, $details, $headers);
    }

    /**
     * @param ProductAddDetail $details
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function createProductAsync(ProductAddDetail $details, array $headers = []): mixed
    {
        return CreateProduct::runAsync($this, $details, $headers);
    }

    /**
     * @param string $productId
     * @param ProductPatchDetail $details
     * @param array<string, string> $headers Optional request headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public function updateProduct(string $productId, ProductPatchDetail $details, array $headers = []): void
    {
        UpdateProduct::run($this, $productId, $details, $headers);
    }

    /**
     * @param string $productId
     * @param ProductPatchDetail $details
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function updateProductAsync(string $productId, ProductPatchDetail $details, array $headers = []): mixed
    {
        return UpdateProduct::runAsync($this, $productId, $details, $headers);
    }

    /**
     * @param GetProductsRequest|null $request
     * @param array<string, string> $headers Optional request headers
     * @return InventorySummaryResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function getProducts(?GetProductsRequest $request = null, array $headers = []): InventorySummaryResponse|array
    {
        return GetProducts::run($this, $request, $headers);
    }

    /**
     * @param GetProductsRequest|null $request
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function getProductsAsync(?GetProductsRequest $request = null, array $headers = []): mixed
    {
        return GetProducts::runAsync($this, $request, $headers);
    }

    /**
     * @param string $productId
     * @param array<string, string> $headers Optional request headers
     * @return ProductDetail|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function getProduct(string $productId, array $headers = []): ProductDetail|array
    {
        return GetProduct::run($this, $productId, $headers);
    }

    /**
     * @param string $productId
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function getProductAsync(string $productId, array $headers = []): mixed
    {
        return GetProduct::runAsync($this, $productId, $headers);
    }

    /**
     * @param string $productId
     * @param array<string, string> $headers Optional request headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public function deleteProduct(string $productId, array $headers = []): void
    {
        DeleteProduct::run($this, $productId, $headers);
    }

    /**
     * @param string $productId
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function deleteProductAsync(string $productId, array $headers = []): mixed
    {
        return DeleteProduct::runAsync($this, $productId, $headers);
    }

    /**
     * @param array<string, string> $headers Optional request headers
     * @return FullInventoryDetailsResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function getPos(array $headers = []): FullInventoryDetailsResponse|array
    {
        return GetPos::run($this, $headers);
    }

    /**
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function getPosAsync(array $headers = []): mixed
    {
        return GetPos::runAsync($this, $headers);
    }
}


